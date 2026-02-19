<?php

namespace App\Http\Controllers;

use App\Models\StoreUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class StoreUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StoreUnit::query();
            
            return DataTables::of($query)
                ->addColumn('code_display', function ($unit) {
                    return '<span class="badge badge-secondary">' . e($unit->code) . '</span>';
                })
                ->addColumn('type_display', function ($unit) {
                    if ($unit->type === 'store') {
                        return '<span class="badge badge-info">Store Only</span>';
                    } elseif ($unit->type === 'dispensing') {
                        return '<span class="badge badge-warning">Dispensing Only</span>';
                    } else {
                        return '<span class="badge badge-success">Both</span>';
                    }
                })
                ->addColumn('description_display', function ($unit) {
                    if ($unit->description) {
                        return e(\Illuminate\Support\Str::limit($unit->description, 50));
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('status_display', function ($unit) {
                    $btnClass = $unit->is_active ? 'btn-success' : 'btn-secondary';
                    $statusText = $unit->is_active ? 'Active' : 'Inactive';
                    return '<form action="' . route('store-units.toggle-status', $unit) . '" method="POST" style="display: inline-block;">' .
                           csrf_field() .
                           '<button type="submit" class="btn btn-sm ' . $btnClass . '">' .
                           $statusText .
                           '</button></form>';
                })
                ->addColumn('actions', function ($unit) {
                    $viewBtn = '<a href="' . route('store-units.show', $unit) . '" class="btn btn-info btn-sm">' .
                               '<i class="fas fa-eye"></i></a>';
                    $editBtn = '<a href="' . route('store-units.edit', $unit) . '" class="btn btn-warning btn-sm">' .
                               '<i class="fas fa-edit"></i></a>';
                    $deleteBtn = '<form action="' . route('store-units.destroy', $unit) . '" method="POST" style="display: inline-block;">' .
                                 csrf_field() .
                                 method_field('DELETE') .
                                 '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this unit?\')">' .
                                 '<i class="fas fa-trash"></i></button></form>';
                    
                    return '<div class="btn-group" role="group">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['code_display', 'type_display', 'description_display', 'status_display', 'actions'])
                ->make(true);
        }
        
        return view('store-units.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('store-units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:store_units,name',
            'code' => 'required|string|max:255|unique:store_units,code',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(StoreUnit::getTypeOptions())),
            'is_active' => 'boolean'
        ]);

        try {
            $unit = StoreUnit::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'type' => $request->type,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('store-units.index')
                ->with('success', 'Store unit created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating store unit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating store unit. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreUnit $storeUnit)
    {
        return view('store-units.show', compact('storeUnit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreUnit $storeUnit)
    {
        return view('store-units.edit', compact('storeUnit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreUnit $storeUnit)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:store_units,name,' . $storeUnit->id,
            'code' => 'required|string|max:255|unique:store_units,code,' . $storeUnit->id,
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(StoreUnit::getTypeOptions())),
            'is_active' => 'boolean'
        ]);

        try {
            $storeUnit->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'type' => $request->type,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('store-units.index')
                ->with('success', 'Store unit updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating store unit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating store unit. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreUnit $storeUnit)
    {
        try {
            // Check if unit is being used in GRN items
            if ($storeUnit->grnItemsAsStore()->exists() || $storeUnit->grnItemsAsDispensing()->exists()) {
                return redirect()->route('store-units.index')
                    ->with('error', 'Cannot delete store unit that is being used in goods received notes.');
            }

            $storeUnit->delete();
            return redirect()->route('store-units.index')
                ->with('success', 'Store unit deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting store unit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting store unit. Please try again.');
        }
    }

    /**
     * Toggle the active status of the store unit.
     */
    public function toggleStatus(StoreUnit $storeUnit)
    {
        try {
            $storeUnit->update(['is_active' => !$storeUnit->is_active]);
            
            $status = $storeUnit->is_active ? 'activated' : 'deactivated';
            return redirect()->route('store-units.index')
                ->with('success', "Store unit {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Error toggling store unit status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating store unit status. Please try again.');
        }
    }

    /**
     * Get store units for AJAX requests.
     */
    public function getUnits(Request $request)
    {
        $query = StoreUnit::active();
        
        if ($request->has('type')) {
            if ($request->type === 'store') {
                $query->store();
            } elseif ($request->type === 'dispensing') {
                $query->dispensing();
            }
        }
        
        $units = $query->orderBy('name')->get(['id', 'name', 'code']);
        
        return response()->json($units->map(function($unit) {
            return [
                'id' => $unit->id,
                'display_name' => $unit->display_name
            ];
        }));
    }
}
