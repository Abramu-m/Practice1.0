<?php

namespace App\Http\Controllers;

use App\Models\AdministrationRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdministrationRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routes = AdministrationRoute::all();
        return view('administration-routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('administration-routes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable'
        ]);

        try {
            $route = AdministrationRoute::create([
                'route_name' => $request->route_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active')
            ]);

            return redirect()->route('administration-routes.index')
                ->with('success', 'Administration route created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating administration route: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating administration route. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AdministrationRoute $administrationRoute)
    {
        return view('administration-routes.show', compact('administrationRoute'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdministrationRoute $administrationRoute)
    {
        return view('administration-routes.edit', compact('administrationRoute'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdministrationRoute $administrationRoute)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable'
        ]);

        try {
            $administrationRoute->update([
                'route_name' => $request->route_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active')
            ]);

            return redirect()->route('administration-routes.index')
                ->with('success', 'Administration route updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating administration route: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating administration route. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdministrationRoute $administrationRoute)
    {
        try {
            // Check if route is being used in prescriptions
            if ($administrationRoute->prescriptions()->exists()) {
                return redirect()->route('administration-routes.index')
                    ->with('error', 'Cannot delete administration route that is being used in prescriptions.');
            }

            $administrationRoute->delete();
            return redirect()->route('administration-routes.index')
                ->with('success', 'Administration route deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting administration route: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting administration route. Please try again.');
        }
    }

    /**
     * Toggle the active status of the administration route.
     */
    public function toggleStatus(AdministrationRoute $administrationRoute)
    {
        try {
            $administrationRoute->update(['is_active' => !$administrationRoute->is_active]);
            
            $status = $administrationRoute->is_active ? 'activated' : 'deactivated';
            return redirect()->route('administration-routes.index')
                ->with('success', "Administration route {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Error toggling administration route status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating administration route status. Please try again.');
        }
    }
}
