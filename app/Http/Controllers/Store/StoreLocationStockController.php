<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreLocationStock;
use App\Models\StoreLocation;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StoreLocationStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = StoreLocationStock::with(['location', 'medication']);

        // Apply role-based location filtering
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);
        if (!in_array('all', $allowedLocationTypes)) {
            $query->whereHas('location', function($q) use ($allowedLocationTypes) {
                $q->whereIn('type', $allowedLocationTypes);
            });
        }

        // Apply filters
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('medication_id')) {
            $query->where('medication_id', $request->medication_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('expiry_status')) {
            $today = now();
            switch ($request->expiry_status) {
                case 'expired':
                    $query->where('expiry_date', '<', $today);
                    break;
                case 'expiring_soon':
                    $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)]);
                    break;
                case 'valid':
                    $query->where('expiry_date', '>', $today->copy()->addDays(30));
                    break;
            }
        }

        $stockEntries = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get data for filters - filtered by role
        $locationsQuery = StoreLocation::where('is_active', true);
        if (!in_array('all', $allowedLocationTypes)) {
            $locationsQuery->whereIn('type', $allowedLocationTypes);
        }
        $locations = $locationsQuery->orderBy('name')->get();
        $medications = Medication::orderBy('generic_name')->get();

        // Calculate role-specific statistics
        $stats = $this->getRoleSpecificStats($user, $allowedLocationTypes);

        return view('store-locations-stock.index', compact('stockEntries', 'locations', 'medications', 'stats'));
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
     * Get role-specific statistics
     */
    private function getRoleSpecificStats($user, $locationTypes)
    {
        $baseQuery = StoreLocationStock::query();
        
        if (!in_array('all', $locationTypes)) {
            $baseQuery->whereHas('location', function($q) use ($locationTypes) {
                $q->whereIn('type', $locationTypes);
            });
        }

        return [
            'my_locations' => StoreLocation::when(!in_array('all', $locationTypes), function($q) use ($locationTypes) {
                $q->whereIn('type', $locationTypes);
            })->where('is_active', true)->count(),
            'total_stock_items' => $baseQuery->count(),
            'expiring_soon' => (clone $baseQuery)->whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
            'critical_stock' => (clone $baseQuery)->where('quantity', '<=', 5)->count(),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $allowedLocationTypes = $this->getAllowedLocationTypes($user);
        
        $locationsQuery = StoreLocation::where('is_active', true);
        if (!in_array('all', $allowedLocationTypes)) {
            $locationsQuery->whereIn('type', $allowedLocationTypes);
        }
        $locations = $locationsQuery->orderBy('name')->get();
        $medications = Medication::orderBy('generic_name')->get();

        return view('store-locations-stock.create', compact('locations', 'medications'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:store_locations,id',
            'medication_id' => 'required|exists:medications,id',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'required|date|after:today',
            'quantity' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,depleted',
        ]);

        StoreLocationStock::create($request->all());

        return redirect()->route('store-locations-stock.index')
            ->with('success', 'Stock entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreLocationStock $storeLocationStock)
    {
        $storeLocationStock->load(['location', 'medication']);
        
        return view('store-locations-stock.show', compact('storeLocationStock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreLocationStock $storeLocationStock)
    {
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
        $medications = Medication::orderBy('generic_name')->get();

        return view('store-locations-stock.edit', compact('storeLocationStock', 'locations', 'medications'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreLocationStock $storeLocationStock)
    {
        $request->validate([
            'location_id' => 'required|exists:store_locations,id',
            'medication_id' => 'required|exists:medications,id',
            'batch_number' => 'required|string|max:255',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'quantity' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,depleted',
        ]);

        $storeLocationStock->update($request->all());

        return redirect()->route('store-locations-stock.index')
            ->with('success', 'Stock entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreLocationStock $storeLocationStock)
    {
        $storeLocationStock->delete();

        return redirect()->route('store-locations-stock.index')
            ->with('success', 'Stock entry deleted successfully.');
    }

    /**
     * Get movement history for a stock entry.
     */
    public function history(StoreLocationStock $stock)
    {
        // This would typically return movement history
        // For now, return a simple response
        return response()->json([
            'message' => 'Movement history feature will be implemented',
            'stock_id' => $stock->id
        ]);
    }

    /**
     * Adjust stock quantity.
     */
    public function adjust(Request $request, StoreLocationStock $stock)
    {
        $request->validate([
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $oldQuantity = $stock->quantity;
            
            if ($request->adjustment_type === 'increase') {
                $stock->quantity += $request->quantity;
            } else {
                $stock->quantity = max(0, $stock->quantity - $request->quantity);
            }

            $stock->save();

            // Here you would typically create a stock movement record
            // StoreStockMovement::create([...]);

            DB::commit();

            return redirect()->route('store-locations-stock.index')
                ->with('success', 'Stock adjusted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }
}
