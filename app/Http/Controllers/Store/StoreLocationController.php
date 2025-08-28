<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = StoreLocation::orderBy('sort_order')->orderBy('name')->get();
        
        return view('store_locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('store_locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:store_locations,name',
            'code' => 'required|string|max:255|unique:store_locations,code',
            'type' => 'required|in:store,dispensing,radiology,laboratory,nursing',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'manager_contact' => 'nullable|string|max:255',
            'can_request' => 'boolean',
            'can_issue' => 'boolean',
            'can_receive' => 'boolean',
            'requires_approval' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        $location = StoreLocation::create([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'description' => $request->description,
            'manager_name' => $request->manager_name,
            'manager_contact' => $request->manager_contact,
            'can_request' => $request->boolean('can_request', true),
            'can_issue' => $request->boolean('can_issue', false),
            'can_receive' => $request->boolean('can_receive', false),
            'requires_approval' => $request->boolean('requires_approval', true),
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->status === 'active'
        ]);

        return redirect()->route('store-locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreLocation $location)
    {
        // For now, just return basic location info
        // Stock functionality will be added later when we implement the collect button
        $locationItems = $location->stockItems; // Get stock items for this location
        
        return view('store_locations.show', compact(
            'location',
            'locationItems'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreLocation $location)
    {
        return view('store_locations.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreLocation $location)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:store_locations,name,' . $location->id,
            'code' => 'required|string|max:255|unique:store_locations,code,' . $location->id,
            'type' => 'required|in:store,dispensing,radiology,laboratory,nursing',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'manager_contact' => 'nullable|string|max:255',
            'can_request' => 'boolean',
            'can_issue' => 'boolean',
            'can_receive' => 'boolean',
            'requires_approval' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        $location->update([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'description' => $request->description,
            'manager_name' => $request->manager_name,
            'manager_contact' => $request->manager_contact,
            'can_request' => $request->boolean('can_request'),
            'can_issue' => $request->boolean('can_issue'),
            'can_receive' => $request->boolean('can_receive'),
            'requires_approval' => $request->boolean('requires_approval'),
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->status === 'active'
        ]);

        return redirect()->route('store-locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreLocation $location)
    {
        // Check if location has stock
        if ($location->stockItems()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete location with stock.']);
        }

        // Check if location has requisitions
        if ($location->requisitions()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete location with requisitions.']);
        }

        $location->delete();

        return redirect()->route('store-locations.index')
            ->with('success', 'Location deleted successfully.');
    }

    /**
     * Toggle location status
     */
    public function toggleStatus(StoreLocation $location)
    {
        $location->update(['is_active' => !$location->is_active]);
        
        $status = $location->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Location {$status} successfully.");
    }

    /**
     * Get locations for API/AJAX
     */
    public function getLocations(Request $request)
    {
        $query = StoreLocation::active();
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        
        $locations = $query->orderBy('name')->get();
        
        return response()->json($locations);
    }

    /**
     * Get location hierarchy
     */
    public function getHierarchy()
    {
        $locations = StoreLocation::active()
            ->whereNull('parent_id')
            ->with('descendants')
            ->orderBy('name')
            ->get();
        
        return response()->json($locations);
    }

    /**
     * Get stock by location
     */
    public function getStock(StoreLocation $location, Request $request)
    {
        $query = $location->stockItems()->with('medication');
        
        if ($request->has('expired')) {
            if ($request->expired == 'true') {
                $query->where('expiry_date', '<', now());
            } else {
                $query->where('expiry_date', '>=', now());
            }
        }
        
        if ($request->has('low_stock')) {
            $query->where('quantity', '<=', 10); // Or some threshold
        }
        
        $stock = $query->orderBy('expiry_date')->get();
        
        return response()->json($stock);
    }
}
