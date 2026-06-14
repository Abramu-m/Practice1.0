<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of service categories
     */
    public function index(Request $request)
    {
        $query = ServiceCategory::withCount('medicalServices');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('name')
                           ->get();

        return view('service_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new service category
     */
    public function create()
    {
        return view('service_categories.create');
    }

    /**
     * Store a newly created service category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $category = ServiceCategory::create($request->all());

            DB::commit();

            return redirect()->route('service_categories.index')
                ->with('success', 'Service category created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create service category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified service category
     */
    public function show(ServiceCategory $serviceCategory)
    {
        $serviceCategory->load(['medicalServices' => function($query) {
            $query->orderBy('name');
        }]);
        
        return view('service_categories.show', compact('serviceCategory'));
    }

    /**
     * Show the form for editing the specified service category
     */
    public function edit(ServiceCategory $serviceCategory)
    {
        return view('service_categories.edit', compact('serviceCategory'));
    }

    /**
     * Update the specified service category
     */
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name,' . $serviceCategory->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $serviceCategory->update($request->all());

            DB::commit();

            return redirect()->route('service_categories.index')
                ->with('success', 'Service category updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update service category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified service category
     */
    public function destroy(ServiceCategory $serviceCategory)
    {
        try {
            // Check if category has any medical services
            if ($serviceCategory->medicalServices()->exists()) {
                return back()->with('error', 'Cannot delete category with associated medical services');
            }

            $serviceCategory->delete();

            return redirect()->route('service_categories.index')
                ->with('success', 'Service category deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete service category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle service category status
     */
    public function toggleStatus(ServiceCategory $serviceCategory)
    {
        try {
            $serviceCategory->update(['is_active' => !$serviceCategory->is_active]);

            $status = $serviceCategory->is_active ? 'activated' : 'deactivated';
            
            return back()->with('success', "Service category {$status} successfully");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update service category status: ' . $e->getMessage());
        }
    }
}
