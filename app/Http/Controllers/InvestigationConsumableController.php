<?php

namespace App\Http\Controllers;

use App\Models\MedicalService;
use App\Models\InvestigationConsumable;
use App\Models\InvestigationConsumption;
use App\Models\Medication;
use App\Models\ServiceCategory;
use App\Models\StoreLocation;
use App\Models\StoreLocationStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * InvestigationConsumableController
 * 
 * This controller manages the ASSIGNMENT of consumables to medical_services.
 * It handles the requirements phase - defining what consumables are needed.
 * 
 * SYSTEM DESIGN - Two Table Approach:
 * 
 * 1. investigation_consumables (THIS CONTROLLER):
 *    - Purpose: Define REQUIREMENTS - what consumables are needed for each investigation
 *    - Fields: medical_service_id, medication_id, quantity_required, is_optional, notes
 *    - Usage: Stock validation before accepting medical_services into lab
 * 
 * 2. investigation_consumptions (SEPARATE CONTROLLER):
 *    - Purpose: Track ACTUAL CONSUMPTION when procedures are performed
 *    - Fields: medical_service_id, medication_id, batch_number, quantity_used, consumed_by, etc.
 *    - Usage: Record actual usage, update stock levels, cost tracking
 * 
 * WORKFLOW:
 * 1. Assignment Phase (THIS CONTROLLER):
 *    - Laboratory staff define what consumables are needed for investigation types
 *    - System checks stock availability against requirements
 *    - medical_services can only proceed to lab if all required consumables are available
 * 
 * 2. Consumption Phase (SEPARATE CONTROLLER):
 *    - When investigation is performed, actual consumption is recorded
 *    - Stock levels are updated based on actual usage
 *    - Costs and batch tracking are maintained
 */
class InvestigationConsumableController extends Controller
{
    /**
     * Display the main consumable assignment page - shows medical services and their consumable requirements
     */
    public function index(Request $request)
    {
        try {
            $query = MedicalService::with(['serviceCategory', 'consumableRequirements.medication']);

            // Apply filters
            if ($request->filled('category_id')) {
                $query->where('service_category_id', $request->category_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('serviceCategory', function ($category) use ($search) {
                          $category->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $medicalServices = $query->orderBy('name')->paginate(20);

            // Get filter options
            $categories = ServiceCategory::orderBy('name')->get();
            
            return view('lab.investigation_consumables.index', compact('medicalServices', 'categories'));
        } catch (\Exception $e) {
            Log::error('Error in InvestigationConsumableController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Show consumable template management for a specific medical service
     */
    public function show(MedicalService $medicalService)
    {
        $medicalService->load([
            'serviceCategory',
            'consumableRequirements.medication'
        ]);

        // Get assigned consumables for this medical service
        $assignedConsumables = InvestigationConsumable::where('medical_service_id', $medicalService->id)
            ->where('is_active', true)
            ->with(['medication'])
            ->orderBy('is_optional', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available medications for dropdown
        $medications = Medication::where('is_active', true)
            ->orderBy('generic_name')
            ->get();

        // Handle AJAX request for table refresh
        if (request()->ajax() && request()->has('refresh')) {
            $html = view('lab.investigation_consumables.partials.consumables-table', compact('assignedConsumables'))->render();
            return response()->json(['html' => $html]);
        }

        return view('lab.investigation_consumables.show', compact(
            'medicalService',
            'assignedConsumables',
            'medications'
        ));
    }

    /**
     * Store a new consumable requirement for a medical service
     */
    public function store(Request $request, MedicalService $medicalService)
    {
        $validator = Validator::make($request->all(), [
            'medication_id' => 'required|exists:medications,id',
            'quantity_required' => 'required|numeric|min:0.01',
            'is_optional' => 'boolean',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Check if this item is already assigned to this medical service
            $existingAssignment = InvestigationConsumable::where('medical_service_id', $medicalService->id)
                ->where('medication_id', $request->medication_id)
                ->where('is_active', true)
                ->first();

            if ($existingAssignment) {
                $message = 'This item is already assigned to this medical service';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                }
                return redirect()->back()->withErrors(['medication_id' => $message]);
            }

            $assignment = InvestigationConsumable::create([
                'medical_service_id' => $medicalService->id,
                'medication_id' => $request->medication_id,
                'quantity_required' => $request->quantity_required,
                'is_optional' => $request->boolean('is_optional', false),
                'notes' => $request->notes,
                'is_active' => true
            ]);

            $successMessage = 'Consumable requirement added successfully';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $assignment->load('medication')
                ]);
            }

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Error creating consumable requirement: ' . $e->getMessage());
            $errorMessage = 'Failed to add consumable requirement: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->withErrors(['general' => $errorMessage]);
        }
    }

    /**
     * Show the form for editing a specific consumable requirement
     */
    public function edit(MedicalService $medicalService, InvestigationConsumable $consumable)
    {
        // Ensure the consumable belongs to this medical service
        if ($consumable->medical_service_id !== $medicalService->id) {
            abort(404);
        }

        $consumable->load('medication');
        $medications = Medication::where('is_active', true)->orderBy('generic_name')->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'consumable' => $consumable,
                    'medications' => $medications
                ]
            ]);
        }

        return view('lab.investigation_consumables.edit', compact(
            'medicalService',
            'consumable',
            'medications'
        ));
    }

    /**
     * Update a specific consumable requirement
     */
    public function update(Request $request, MedicalService $medicalService, InvestigationConsumable $consumable)
    {
        // Ensure the consumable belongs to this medical service
        if ($consumable->medical_service_id !== $medicalService->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'medication_id' => 'required|exists:medications,id',
            'quantity_required' => 'required|numeric|min:0.01',
            'is_optional' => 'boolean',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Check if this item is already assigned to this medical service (excluding current record)
            $existingAssignment = InvestigationConsumable::where('medical_service_id', $medicalService->id)
                ->where('medication_id', $request->medication_id)
                ->where('id', '!=', $consumable->id)
                ->where('is_active', true)
                ->first();

            if ($existingAssignment) {
                $message = 'This item is already assigned to this medical service';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                }
                return redirect()->back()->withErrors(['medication_id' => $message]);
            }

            $consumable->update([
                'medication_id' => $request->medication_id,
                'quantity_required' => $request->quantity_required,
                'is_optional' => $request->boolean('is_optional', false),
                'notes' => $request->notes,
            ]);

            $successMessage = 'Consumable requirement updated successfully';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $consumable->load('medication')
                ]);
            }

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Error updating consumable requirement: ' . $e->getMessage());
            $errorMessage = 'Failed to update consumable requirement: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->withErrors(['general' => $errorMessage]);
        }
    }

    /**
     * Remove a specific consumable requirement
     */
    public function destroy(MedicalService $medicalService, InvestigationConsumable $consumable)
    {
        // Ensure the consumable belongs to this medical service
        if ($consumable->medical_service_id !== $medicalService->id) {
            abort(404);
        }

        try {
            // Soft delete by setting is_active to false
            $consumable->update(['is_active' => false]);

            $successMessage = 'Consumable requirement removed successfully';
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage
                ]);
            }

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Error removing consumable requirement: ' . $e->getMessage());
            $errorMessage = 'Failed to remove consumable requirement: ' . $e->getMessage();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->withErrors(['general' => $errorMessage]);
        }
    }

    /**
     * Clear all consumable requirements for a medical service
     */
    public function clear(MedicalService $medicalService)
    {
        try {
            InvestigationConsumable::where('medical_service_id', $medicalService->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $successMessage = 'All consumable requirements cleared successfully';
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage
                ]);
            }

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Error clearing consumable requirements: ' . $e->getMessage());
            $errorMessage = 'Failed to clear consumable requirements: ' . $e->getMessage();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->withErrors(['general' => $errorMessage]);
        }
    }

    /**
     * Get items for AJAX dropdown
     */
    public function getItems(Request $request)
    {
        $medications = Medication::select('id', 'name', 'type')
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $medications
        ]);
    }

    /**
     * Check stock availability for a medical service's consumables
     */
    public function checkStock(MedicalService $medicalService, Request $request)
    {
        $locationId = $request->input('location_id');
        
        $consumables = InvestigationConsumable::where('medical_service_id', $medicalService->id)
            ->where('is_active', true)
            ->with('medication')
            ->get();

        $stockCheck = [];
        $allAvailable = true;

        foreach ($consumables as $consumable) {
            $stockQuery = StoreLocationStock::where('medication_id', $consumable->medication_id)
                ->where('status', 'active')
                ->where('quantity', '>', 0);

            if ($locationId) {
                $stockQuery->where('location_id', $locationId);
            }

            $totalStock = $stockQuery->sum('quantity');
            $isAvailable = $totalStock >= $consumable->quantity_required;

            if (!$isAvailable && !$consumable->is_optional) {
                $allAvailable = false;
            }

            $stockCheck[] = [
                'medication_id' => $consumable->medication_id,
                'medication_name' => $consumable->medication->name,
                'required_quantity' => $consumable->quantity_required,
                'available_quantity' => $totalStock,
                'is_available' => $isAvailable,
                'is_optional' => $consumable->is_optional
            ];
        }
        
        return response()->json([
            'success' => true,
            'can_proceed' => $allAvailable,
            'data' => $stockCheck
        ]);
    }

    /**
     * Get consumable availability at specific location
     */
    public function getConsumableAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:medications,id',
            'location_id' => 'nullable|exists:store_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $itemId = $request->item_id;
        $locationId = $request->location_id;

        $stockQuery = StoreLocationStock::where('medication_id', $itemId)
            ->where('status', 'active')
            ->where('quantity', '>', 0);

        if ($locationId) {
            $stockQuery->where('location_id', $locationId);
        }

        $stocks = $stockQuery->with('storeLocation')->get();
        $totalStock = $stocks->sum('quantity');

        $stockDetails = $stocks->map(function ($stock) {
            return [
                'location_id' => $stock->location_id,
                'location_name' => $stock->storeLocation->name ?? 'Unknown',
                'quantity' => $stock->quantity,
                'batch_number' => $stock->batch_number,
                'expiry_date' => $stock->expiry_date,
                'unit_cost' => $stock->unit_cost
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_available' => $totalStock,
                'stock_details' => $stockDetails
            ]
        ]);
    }

    /**
     * Bulk check stock for multiple medical services
     */
    public function bulkCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medical_service_ids' => 'required|array',
            'medical_service_ids.*' => 'exists:medical_services,id',
            'location_id' => 'nullable|exists:store_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $locationId = $request->location_id;
        $results = [];

        foreach ($request->medical_service_ids as $serviceId) {
            $medicalService = MedicalService::find($serviceId);
            // Get stock check data for this service
            $consumables = InvestigationConsumable::where('medical_service_id', $serviceId)
                ->where('is_active', true)
                ->with('medication')
                ->get();

            $stockCheck = [];
            $allAvailable = true;

            foreach ($consumables as $consumable) {
                $stockQuery = StoreLocationStock::where('medication_id', $consumable->medication_id)
                    ->where('status', 'active')
                    ->where('quantity', '>', 0);

                if ($locationId) {
                    $stockQuery->where('location_id', $locationId);
                }

                $totalStock = $stockQuery->sum('quantity');
                $isAvailable = $totalStock >= $consumable->quantity_required;

                if (!$isAvailable && !$consumable->is_optional) {
                    $allAvailable = false;
                }
            }

            $results[$serviceId] = [
                'can_proceed' => $allAvailable,
                'consumables_count' => $consumables->count()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Get consumables for a specific medical service (AJAX)
     */
    public function getMedicalServiceConsumables(MedicalService $medicalService)
    {
        $consumables = InvestigationConsumable::where('medical_service_id', $medicalService->id)
            ->where('is_active', true)
            ->with('medication')
            ->get()
            ->map(function ($consumable) {
                return [
                    'id' => $consumable->id,
                    'medication_id' => $consumable->medication_id,
                    'medication_name' => $consumable->medication->name,
                    'quantity_required' => $consumable->quantity_required,
                    'is_optional' => $consumable->is_optional,
                    'notes' => $consumable->notes
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $consumables
        ]);
    }

    /**
     * Remove a consumable assignment
     */
    public function removeConsumable(InvestigationConsumable $consumable)
    {
        try {
            $investigationId = $consumable->investigation_id;
            $consumable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Consumable assignment removed successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing consumable assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove consumable assignment.'
            ], 500);
        }
    }

    /**
     * Bulk check stock for multiple investigations
     */
    public function bulkStockCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'investigation_ids' => 'required|array',
            'investigation_ids.*' => 'exists:investigations,id',
            'location_id' => 'nullable|exists:store_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $locationId = $request->location_id;
        $results = [];

        foreach ($request->investigation_ids as $investigationId) {
            $stockCheck = InvestigationConsumable::checkInvestigationStock($investigationId, $locationId);
            $results[$investigationId] = $stockCheck;
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Get stock summary for lab dashboard
     */
    public function stockSummary(Request $request)
    {
        $locationId = $request->input('location_id');

        // Get low stock items (consumables that are running low)
        $lowStockQuery = StoreLocationStock::with(['medication', 'storeLocation'])
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->whereHas('medication', function($query) {
                $query->whereColumn('store_locations_stock.quantity', '<=', 'medications.reorder_level');
            });

        if ($locationId) {
            $lowStockQuery->where('location_id', $locationId);
        }

        $lowStockItems = $lowStockQuery->get();

        // Get expired items
        $expiredQuery = StoreLocationStock::with(['medication', 'storeLocation'])
            ->where('status', 'active')
            ->where('expiry_date', '<', now())
            ->where('quantity', '>', 0);

        if ($locationId) {
            $expiredQuery->where('location_id', $locationId);
        }

        $expiredItems = $expiredQuery->get();

        // Get services with missing consumables
        $servicesWithIssues = MedicalService::whereHas('consumableRequirements', function ($query) use ($locationId) {
            $query->where('is_active', true)
                ->whereHas('medication', function ($medQuery) use ($locationId) {
                    $stockQuery = $medQuery->whereHas('storeLocationStocks', function ($stockQuery) use ($locationId) {
                        $stockQuery->where('status', 'active')
                            ->where('quantity', '>', 0);
                        if ($locationId) {
                            $stockQuery->where('location_id', $locationId);
                        }
                    }, '<', 1); // Services with consumables that have no stock
                });
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'low_stock_count' => $lowStockItems->count(),
                'expired_items_count' => $expiredItems->count(),
                'services_with_stock_issues' => $servicesWithIssues,
                'low_stock_items' => $lowStockItems->take(10),
                'expired_items' => $expiredItems->take(10)
            ]
        ]);
    }

    /**
     * Get template consumables for a medical service
     * Used when creating new investigations to auto-assign consumables
     */
    public function getTemplateConsumables(int $medicalServiceId): array
    {
        $consumables = InvestigationConsumable::where('medical_service_id', $medicalServiceId)
            ->where('is_active', true)
            ->with('medication')
            ->get();

        return $consumables->map(function ($consumable) {
            return [
                'medication_id' => $consumable->medication_id,
                'medication_name' => $consumable->medication->name,
                'quantity_required' => $consumable->quantity_required,
                'is_optional' => $consumable->is_optional,
                'notes' => $consumable->notes
            ];
        })->toArray();
    }
}
