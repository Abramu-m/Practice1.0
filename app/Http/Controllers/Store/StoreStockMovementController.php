<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreStockMovement;
use App\Models\StoreLocation;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StoreStockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StoreStockMovement::with(['storeLocation', 'medication', 'fromLocation', 'toLocation', 'createdBy']);

        // Apply filters
        if ($request->filled('location_id')) {
            $query->where('store_location_id', $request->location_id);
        }

        if ($request->filled('medication_id')) {
            $query->where('item_id', $request->medication_id)
                  ->where('item_type', 'medication');
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        if ($request->filled('reference_number')) {
            $query->where('reference_number', 'like', '%' . $request->reference_number . '%');
        }

        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }

        // Handle export request
        if ($request->has('export') && $request->export === 'excel') {
            return $this->export($request);
        }

        $movements = $query->orderBy('movement_date', 'desc')->paginate(20);

        // Get data for filters
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
        $medications = Medication::orderBy('generic_name')->get();

        // Calculate statistics
        $stats = [
            'total_movements' => StoreStockMovement::count(),
            'movements_today' => StoreStockMovement::whereDate('movement_date', now())->count(),
            'total_value' => StoreStockMovement::sum('total_cost'),
            'pending_transfers' => StoreStockMovement::where('movement_type', 'transfer')
                                                   ->where('transaction_type', 'transfer')
                                                   ->whereDate('movement_date', '>=', now()->subDays(7))
                                                   ->count(),
        ];

        return view('store-stock-movements.index', compact('movements', 'locations', 'medications', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
        $medications = Medication::orderBy('generic_name')->get();

        return view('store-stock-movements.create', compact('locations', 'medications'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:medication,consumable',
            'item_id' => 'required|integer',
            'store_location_id' => 'required|exists:store_locations,id',
            'movement_type' => 'required|in:in,out,transfer,adjustment,waste',
            'transaction_type' => 'required|in:purchase,dispensing,requisition,transfer,adjustment,waste,return,consumption,disposal',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'movement_date' => 'required|date',
            'batch_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['total_cost'] = $data['quantity'] * $data['unit_cost'];
            
            // You would typically calculate balance_before and balance_after here
            $data['balance_before'] = 0; // Calculate from existing stock
            $data['balance_after'] = 0;  // Calculate new balance

            StoreStockMovement::create($data);

            DB::commit();

            return redirect()->route('store-stock-movements.index')
                ->with('success', 'Stock movement created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create movement: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreStockMovement $storeStockMovement)
    {
        $storeStockMovement->load(['storeLocation', 'medication', 'fromLocation', 'toLocation', 'createdBy']);
        
        return view('store-stock-movements.show', compact('storeStockMovement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreStockMovement $storeStockMovement)
    {
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
        $medications = Medication::orderBy('generic_name')->get();

        return view('store-stock-movements.edit', compact('storeStockMovement', 'locations', 'medications'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreStockMovement $storeStockMovement)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        // Only allow updating notes and reference number for audit trail
        $storeStockMovement->update($request->only(['notes', 'reference_number']));

        return redirect()->route('store-stock-movements.index')
            ->with('success', 'Movement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreStockMovement $storeStockMovement)
    {
        // Generally, movements should not be deleted, only reversed
        return redirect()->route('store-stock-movements.index')
            ->with('error', 'Movements cannot be deleted. Use reverse function instead.');
    }

    /**
     * Reverse a stock movement.
     */
    public function reverse(Request $request, StoreStockMovement $movement)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create reverse movement
            $reverseData = [
                'item_type' => $movement->item_type,
                'item_id' => $movement->item_id,
                'store_location_id' => $movement->store_location_id,
                'from_location_id' => $movement->to_location_id,
                'to_location_id' => $movement->from_location_id,
                'movement_type' => $movement->movement_type === 'in' ? 'out' : 'in',
                'transaction_type' => 'adjustment',
                'reference_number' => 'REV-' . $movement->reference_number,
                'batch_number' => $movement->batch_number,
                'quantity' => $movement->quantity,
                'unit_cost' => $movement->unit_cost,
                'total_cost' => $movement->total_cost,
                'movement_date' => now(),
                'balance_before' => $movement->balance_after,
                'balance_after' => $movement->balance_before,
                'notes' => 'Reverse of movement #' . $movement->id . '. Reason: ' . $request->reason,
                'created_by' => Auth::id(),
            ];

            StoreStockMovement::create($reverseData);

            DB::commit();

            return redirect()->route('store-stock-movements.index')
                ->with('success', 'Movement reversed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reverse movement: ' . $e->getMessage());
        }
    }

    /**
     * Export movements to Excel.
     */
    public function export(Request $request)
    {
        // This would typically use Laravel Excel or similar
        // For now, return a simple CSV response
        
        $query = StoreStockMovement::with(['storeLocation', 'medication', 'createdBy']);
        
        // Apply same filters as index
        if ($request->filled('location_id')) {
            $query->where('store_location_id', $request->location_id);
        }
        // ... other filters

        $movements = $query->orderBy('movement_date', 'desc')->get();

        $filename = 'stock_movements_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date', 'Type', 'Transaction', 'Item', 'Location', 
                'Quantity', 'Unit Cost', 'Total Cost', 'Reference', 'Created By'
            ]);

            // CSV data
            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->movement_date->format('Y-m-d H:i'),
                    ucfirst($movement->movement_type),
                    ucfirst(str_replace('_', ' ', $movement->transaction_type)),
                    $movement->medication->generic_name ?? 'Item #' . $movement->item_id,
                    $movement->storeLocation->name ?? 'N/A',
                    $movement->quantity,
                    $movement->unit_cost,
                    $movement->total_cost,
                    $movement->reference_number ?? '',
                    ($movement->createdBy->first_name ?? '') . ' ' . ($movement->createdBy->last_name ?? ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
