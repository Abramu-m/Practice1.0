<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\MedicationFormulation;
use App\Models\MedicationUnit;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationController extends Controller
{
    /**
     * Display a listing of medications.
     */
    public function index(Request $request)
    {
        $query = Medication::with(['storeCategory', 'formulation', 'dispensingUnit']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('generic_name', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Stock status filter
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereRaw('stock_quantity <= reorder_level');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
                case 'expired':
                    // Use ledger entries to check for expired batches
                    $query->whereHas('ledgerEntries', function ($q) {
                        $q->where('expiry_date', '<', now());
                    });
                    break;
                case 'expiring_soon':
                    // Use ledger entries to check for expiring batches
                    $query->whereHas('ledgerEntries', function ($q) {
                        $q->whereBetween('expiry_date', [now(), now()->addDays(30)]);
                    });
                    break;
            }
        }

        $medications = $query->orderBy('generic_name')->paginate(25);
        $categories = StoreCategory::orderBy('description')->get();

        return view('medications.index', compact('medications', 'categories'));
    }

    /**
     * Show the form for creating a new medication.
     */
    public function create()
    {
        $categories = StoreCategory::orderBy('description')->get();
        $formulations = MedicationFormulation::active()->orderBy('description')->get();
        $dispensingUnits = MedicationUnit::active()->get();
        
        return view('medications.create', compact('categories', 'formulations', 'dispensingUnits'));
    }

    /**
     * Store a newly created medication in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'formulation_id' => 'nullable|exists:medication_formulations,id',
            'dispensing_unit_id' => 'nullable|exists:medication_units,id',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:store_categories,id',
            'reorder_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'storage_conditions' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            // Set default stock quantity to 0 since it will be managed through GRN
            $validated['stock_quantity'] = 0;
            
            Medication::create($validated);
            
            DB::commit();
            
            return redirect()->route('medications.index')
                ->with('success', 'Medication created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating medication: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified medication.
     */
    public function show(Medication $medication)
    {
        $medication->load(['storeCategory', 'formulation', 'dispensingUnit', 'prescriptions', 'ledgerEntries']);
        
        return view('medications.show', compact('medication'));
    }

    /**
     * Show the form for editing the specified medication.
     */
    public function edit(Medication $medication)
    {
        $categories = StoreCategory::orderBy('description')->get();
        $formulations = MedicationFormulation::active()->orderBy('description')->get();
        $dispensingUnits = MedicationUnit::active()->get();
        
        return view('medications.edit', compact('medication', 'categories', 'formulations', 'dispensingUnits'));
    }

    /**
     * Update the specified medication in storage.
     */
    public function update(Request $request, Medication $medication)
    {
        $validated = $request->validate([
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'formulation_id' => 'nullable|exists:medication_formulations,id',
            'dispensing_unit_id' => 'nullable|exists:medication_units,id',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:store_categories,id',
            'reorder_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'storage_conditions' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        try {
            DB::beginTransaction();
            
            $medication->update($validated);
            
            DB::commit();
            
            return redirect()->route('medications.index')
                ->with('success', 'Medication updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating medication: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified medication from storage.
     */
    public function destroy(Medication $medication)
    {
        try {
            DB::beginTransaction();
            
            $medication->delete();
            
            DB::commit();
            
            return redirect()->route('medications.index')
                ->with('success', 'Medication deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting medication: ' . $e->getMessage());
        }
    }

    /**
     * Toggle medication active status.
     */
    public function toggleStatus(Medication $medication)
    {
        try {
            $medication->update(['is_active' => !$medication->is_active]);
            
            $status = $medication->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', "Medication {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating medication status: ' . $e->getMessage());
        }
    }

    /**
     * Get medications for API requests.
     */
    public function apiList(Request $request)
    {
        $query = Medication::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('generic_name', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        $medications = $query->take(10)->get();

        return response()->json($medications);
    }

    /**
     * Get low stock medications.
     */
    public function lowStock()
    {
        $medications = Medication::lowStock()
            ->with(['storeCategory'])
            ->orderBy('generic_name')
            ->get();

        return view('medications.low_stock', compact('medications'));
    }

    /**
     * Get expired medications.
     */
    public function expired()
    {
        $medications = Medication::expired()
            ->with(['storeCategory', 'ledgerEntries'])
            ->orderBy('generic_name')
            ->get();

        return view('medications.expired', compact('medications'));
    }

    /**
     * Get medications expiring soon.
     */
    public function expiringSoon()
    {
        $medications = Medication::expiringSoon()
            ->with(['storeCategory', 'ledgerEntries'])
            ->orderBy('generic_name')
            ->get();

        return view('medications.expiring_soon', compact('medications'));
    }

    /**
     * Display items by category
     */
    public function indexByCategory(Request $request, $categoryId)
    {
        $category = StoreCategory::findOrFail($categoryId);
        $categories = StoreCategory::orderBy('description')->get();
        
        $query = Medication::byCategory($categoryId)->with(['storeCategory']);

        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('generic_name', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', (bool) $request->status);
        }

        if ($request->has('stock_status') && $request->stock_status) {
            if ($request->stock_status === 'low_stock') {
                $query->where('stock_quantity', '<=', DB::raw('reorder_level'));
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('stock_quantity', 0);
            }
        }

        $items = $query->orderBy('generic_name')->paginate(50);

        return view('medications.index_by_category', compact('items', 'category', 'categories'));
    }

    /**
     * API endpoint to get items by category
     */
    public function apiByCategory(Request $request, $categoryId)
    {
        $query = Medication::byCategory($categoryId)->active()->with(['storeCategory']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('generic_name', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        $items = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->generic_name ?: $item->brand_name,
                    'category' => $item->storeCategory->description ?? 'N/A',
                    'stock_quantity' => $item->stock_quantity,
                    'stock_status' => $item->stock_status,
                    'is_low_stock' => $item->is_low_stock,
                ];
            })
        ]);
    }

    /**
     * API endpoint to get stock information for a specific item
     */
    public function apiStock(Request $request, Medication $medication)
    {
        $stockData = [
            'id' => $medication->id,
            'name' => $medication->generic_name ?: $medication->brand_name,
            'category' => $medication->storeCategory->description ?? null,
            'current_stock' => $medication->stock_quantity,
            'reorder_level' => $medication->reorder_level,
            'maximum_stock_level' => $medication->maximum_stock_level,
            'stock_status' => $medication->stock_status,
            'is_low_stock' => $medication->is_low_stock,
            'is_in_stock' => $medication->is_in_stock,
        ];

        // Get batch information from ledger entries
        $batches = $medication->ledgerEntries()->where('status', 'active')->get();
        if ($batches->count() > 0) {
            $stockData['batches'] = $batches->map(function ($batch) {
                return [
                    'batch_number' => $batch->batch_number,
                    'quantity' => $batch->quantity_received,
                    'expiry_date' => $batch->expiry_date,
                    'unit_cost' => $batch->unit_cost,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $stockData
        ]);
    }

    /**
     * API endpoint to get batch information for a specific item
     */
    public function apiBatches(Request $request, Medication $medication)
    {
        $batches = $medication->ledgerEntries()
            ->where('status', 'active')
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity_current' => $batch->quantity_received,
                    'expiry_date' => $batch->expiry_date,
                    'unit_cost' => $batch->unit_cost,
                    'is_expired' => $batch->is_expired,
                    'is_expiring_soon' => $batch->is_expiring_soon,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $batches
        ]);
    }

    /**
     * API endpoint to check if items need reordering
     */
    public function apiCheckReorder(Request $request)
    {
        $lowStockItems = Medication::lowStock()
            ->active()
            ->with(['storeCategory'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->generic_name ?: $item->brand_name,
                    'category' => $item->storeCategory->description ?? 'N/A',
                    'current_stock' => $item->stock_quantity,
                    'reorder_level' => $item->reorder_level,
                    'recommended_order_quantity' => max(
                        $item->maximum_stock_level - $item->stock_quantity,
                        $item->reorder_level
                    ),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $lowStockItems,
            'count' => $lowStockItems->count()
        ]);
    }
}
