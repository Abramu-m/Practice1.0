<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreRequisition;
use App\Models\StoreLocation;
use App\Models\Medication;
use App\Models\StoreStockMovement;
use App\Services\StoreRequisitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreRequisitionController extends Controller
{
    protected $requisitionService;

    public function __construct(StoreRequisitionService $requisitionService)
    {
        $this->requisitionService = $requisitionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage = $request->get('per_page', 15);

        // Base query with role-based filtering
        $query = StoreRequisition::with([
            'requestingLocation', 
            'requestedBy', 
            'approvedBy', 
            'issuedBy'
        ])->withCount('items');

        // Apply role-based location filtering
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);
        if (!in_array('all', $allowedLocationTypes)) {
            $query->whereHas('requestingLocation', function($q) use ($allowedLocationTypes) {
                $q->whereIn('type', $allowedLocationTypes);
            });
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('requisition_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('requestingLocation', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply priority filter
        if ($priority) {
            $query->where('priority', $priority);
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('requisition_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('requisition_date', '<=', $dateTo);
        }

        // Get paginated results
        $requisitions = $query->orderBy('requisition_date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate($perPage);

        // Get role-specific summary statistics
        $statistics = $this->getRoleSpecificRequisitionStats($user, $allowedLocationTypes);

        // Get locations for filter dropdown - filtered by role
        $locationsQuery = StoreLocation::where('is_active', true);
        if (!in_array('all', $allowedLocationTypes)) {
            $locationsQuery->whereIn('type', $allowedLocationTypes);
        }
        $locations = $locationsQuery->orderBy('name')->get();

        return view('store.requisitions.index', compact(
            'requisitions',
            'statistics',
            'locations',
            'search',
            'status',
            'priority',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);
        
        // Filter locations by role
        $locationsQuery = StoreLocation::where('is_active', true);
        if (!in_array('all', $allowedLocationTypes)) {
            $locationsQuery->whereIn('type', $allowedLocationTypes);
        }
        $locations = $locationsQuery->orderBy('name')->get();
        $medications = Medication::with('dispensingUnit')
            ->where('is_active', true)
            ->orderBy('generic_name')
            ->get()
            ->map(function ($m) {
                $arr = $m->toArray();
                $arr['unit'] = $m->dispensingUnit ? $m->dispensingUnit->unit_name : null;
                return $arr;
            });
        
        return view('store.requisitions.create', compact('locations', 'medications'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);

        $request->validate([
            'requesting_location_id' => 'required|exists:store_locations,id',
            'priority' => 'required|in:normal,urgent,emergency',
            'purpose' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:medications,id',
            'items.*.requested_quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.justification' => 'nullable|string|max:500',
            'action' => 'required|in:draft,submit'
        ]);

        // Validate that the user can access the requested location
        if (!in_array('all', $allowedLocationTypes)) {
            $requestedLocation = StoreLocation::find($request->requesting_location_id);
            if (!$requestedLocation || !in_array($requestedLocation->type, $allowedLocationTypes)) {
                return back()->withErrors(['requesting_location_id' => 'You do not have permission to create requisitions for this location.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Create requisition
            $requisition = $this->requisitionService->createRequisition(
                $request->requesting_location_id,
                1, // Default issuing location (main store)
                Auth::id(),
                $request->items,
                $request->priority,
                $request->purpose
            );

            // If action is submit, automatically submit the requisition
            if ($request->action === 'submit') {
                $this->requisitionService->submitRequisition($requisition);
            }

            DB::commit();

            $message = $request->action === 'submit' 
                ? 'Requisition created and submitted successfully.' 
                : 'Requisition created as draft successfully.';

            return redirect()->route('store.requisitions.show', $requisition)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating requisition: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create requisition: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreRequisition $requisition)
    {
        $requisition->load([
            'requestingLocation',
            'issuingLocation',
            'requestedBy',
            'approvedBy',
            'issuedBy',
            'items.medication'
        ]);

        // Get stock movements if requisition is issued
        $stockMovements = collect();
        if ($requisition->status === 'issued') {
            $stockMovements = StoreStockMovement::where('reference_id', $requisition->id)
                ->where('transaction_type', 'requisition')
                ->with(['medication', 'fromLocation', 'toLocation'])
                ->orderBy('movement_date', 'desc')
                ->get();
        }

        return view('store.requisitions.show', compact('requisition', 'stockMovements'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreRequisition $requisition)
    {
        if ($requisition->status !== 'draft') {
            return redirect()->route('store.requisitions.show', $requisition)
                ->with('error', 'Only draft requisitions can be edited.');
        }

        $user = Auth::user();
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);
        
        // Filter locations by role
        $locationsQuery = StoreLocation::where('is_active', true);
        if (!in_array('all', $allowedLocationTypes)) {
            $locationsQuery->whereIn('type', $allowedLocationTypes);
        }
        $locations = $locationsQuery->orderBy('name')->get();
        $medications = Medication::with('dispensingUnit')
            ->where('is_active', true)
            ->orderBy('generic_name')
            ->get()
            ->map(function ($m) {
                $arr = $m->toArray();
                $arr['unit'] = $m->dispensingUnit ? $m->dispensingUnit->unit_name : null;
                return $arr;
            });
        
        $requisition->load('items.medication');
        
        return view('store.requisitions.edit', compact('requisition', 'locations', 'medications'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreRequisition $requisition)
    {
        if ($requisition->status !== 'draft') {
            return redirect()->route('store.requisitions.show', $requisition)
                ->with('error', 'Only draft requisitions can be updated.');
        }

        $user = Auth::user();
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);

        $request->validate([
            'requesting_location_id' => 'required|exists:store_locations,id',
            'priority' => 'required|in:normal,urgent,emergency',
            'purpose' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:medications,id',
            'items.*.requested_quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.justification' => 'nullable|string|max:500',
            'action' => 'required|in:draft,submit'
        ]);

        // Validate that the user can access the requested location
        if (!in_array('all', $allowedLocationTypes)) {
            $requestedLocation = StoreLocation::find($request->requesting_location_id);
            if (!$requestedLocation || !in_array($requestedLocation->type, $allowedLocationTypes)) {
                return back()->withErrors(['requesting_location_id' => 'You do not have permission to create requisitions for this location.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Update basic requisition info
            $requisition->update([
                'requesting_location_id' => $request->requesting_location_id,
                'priority' => $request->priority,
                'purpose' => $request->purpose,
            ]);

            // Delete existing items and recreate them
            $requisition->items()->delete();

            // Calculate total estimated cost
            $totalEstimatedCost = 0;
            
            foreach ($request->items as $item) {
                $itemCost = $item['requested_quantity'] * $item['unit_cost'];
                $totalEstimatedCost += $itemCost;

                $requisition->items()->create([
                    'item_type' => 'medication',
                    'item_id' => $item['item_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $itemCost,
                    'justification' => $item['justification'] ?? null
                ]);
            }

            // Update total estimated cost
            $requisition->update(['total_estimated_cost' => $totalEstimatedCost]);

            // If action is submit, automatically submit the requisition
            if ($request->action === 'submit') {
                $this->requisitionService->submitRequisition($requisition);
            }

            DB::commit();

            $message = $request->action === 'submit' 
                ? 'Requisition updated and submitted successfully.' 
                : 'Requisition updated successfully.';

            return redirect()->route('store.requisitions.show', $requisition)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating requisition: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update requisition: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Submit a draft requisition
     */
    public function submit(StoreRequisition $requisition)
    {
        try {
            $this->requisitionService->submitRequisition($requisition);
            
            return redirect()->route('store.requisitions.show', $requisition)
                ->with('success', 'Requisition submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve a submitted requisition
     */
    public function verify(StoreRequisition $requisition)
    {
        try {
            $result = $this->requisitionService->verifyRequisition($requisition);
            
            $message = $result['can_fulfill'] 
                ? 'Requisition approved successfully. All items are available.' 
                : 'Requisition partially approved. Some items have insufficient stock.';
            
            return redirect()->route('store.requisitions.show', $requisition)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Issue an approved requisition
     */
    public function issue(StoreRequisition $requisition)
    {
        try {
            $this->requisitionService->issueRequisition($requisition, Auth::id());
            
            return redirect()->route('store.requisitions.show', $requisition)
                ->with('success', 'Requisition issued successfully. Items have been transferred to the requesting location.');
        } catch (\Exception $e) {
            Log::error('Error issuing requisition: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to issue requisition: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel a requisition
     */
    public function cancel(StoreRequisition $requisition)
    {
        if (in_array($requisition->status, ['issued', 'cancelled'])) {
            return back()->withErrors(['error' => 'Cannot cancel this requisition.']);
        }

        $requisition->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now()
        ]);

        return redirect()->route('store.requisitions.show', $requisition)
            ->with('success', 'Requisition cancelled successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreRequisition $requisition)
    {
        $user = Auth::user();

        // Only allow deletion of draft requisitions for regular users
        if ($requisition->status !== 'draft') {
            // Admins and super admins can delete draft or cancelled requisitions
            if ($user->is_admin || $user->is_super) {
                if (!in_array($requisition->status, ['draft', 'cancelled'])) {
                    return back()->withErrors(['error' => 'Only draft or cancelled requisitions can be deleted.']);
                }
            } else {
                return back()->withErrors(['error' => 'Only draft requisitions can be deleted.']);
            }
        }

        DB::beginTransaction();

        try {
            // Delete items first
            $requisition->items()->delete();
            
            // Delete requisition
            $requisition->delete();

            DB::commit();

            return redirect()->route('store.requisitions.index')
                ->with('success', 'Requisition deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete requisition: ' . $e->getMessage()]);
        }
    }

    /**
     * Get allowed location types based on user role and admin status
     */
    private function getAllowedLocationTypes($user)
    {
        // Super admin and admin users have access to all locations
        if ($user->is_super || $user->is_admin) {
            return ['all'];
        }

        // Regular users based on their role
        $roleLocationMap = [
            'nurse' => ['nursing'],
            'lab_technician' => ['laboratory'],
            'pharmacist' => ['dispensing'],
            'doctor' => ['radiology'],
            'radiologist' => ['radiology'],
        ];

        return $roleLocationMap[$user->role] ?? ['store'];
    }

    /**
     * Get role-specific requisition statistics
     */
    private function getRoleSpecificRequisitionStats($user, $locationTypes)
    {
        $baseQuery = StoreRequisition::query();
        
        if (!in_array('all', $locationTypes)) {
            $baseQuery->whereHas('requestingLocation', function($q) use ($locationTypes) {
                $q->whereIn('type', $locationTypes);
            });
        }

        return [
            'total_requisitions' => (clone $baseQuery)->count(),
            'pending_requisitions' => (clone $baseQuery)->whereIn('status', ['submitted', 'approved', 'partially_approved'])->count(),
            'issued_requisitions' => (clone $baseQuery)->where('status', 'issued')
                ->whereDate('issued_at', today())
                ->count(),
            'total_value' => (clone $baseQuery)->where('status', 'issued')->sum('total_estimated_cost'),
        ];
    }
}