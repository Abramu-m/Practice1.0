<?php

namespace App\Http\Controllers;

use App\Models\SampleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleTypeController extends Controller
{
    /**
     * Display a listing of sample types
     */
    public function index(Request $request)
    {
        $query = SampleType::query();

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('requires_fasting')) {
            $query->where('requires_fasting', $request->requires_fasting === 'yes');
        }

        $sampleTypes = $query->orderBy('name')
                            ->get();

        return view('sample_types.index', compact('sampleTypes'));
    }

    /**
     * Show the form for creating a new sample type
     */
    public function create()
    {
        return view('sample_types.create');
    }

    /**
     * Store a newly created sample type
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:sample_types,code',
            'description' => 'nullable|string|max:1000',
            'container_type' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:20',
            'volume_ml' => 'nullable|numeric|min:0',
            'collection_instructions' => 'nullable|string|max:2000',
            'storage_requirements' => 'nullable|string|max:1000',
            'stability_hours' => 'nullable|integer|min:0',
            'requires_fasting' => 'boolean',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $sampleType = SampleType::create($request->all());

            DB::commit();

            return redirect()->route('sample_types.index')
                ->with('success', 'Sample type created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create sample type: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sample type
     */
    public function show(SampleType $sampleType)
    {
        $sampleType->load('medicalServices');
        
        return view('sample_types.show', compact('sampleType'));
    }

    /**
     * Show the form for editing the specified sample type
     */
    public function edit(SampleType $sampleType)
    {
        return view('sample_types.edit', compact('sampleType'));
    }

    /**
     * Update the specified sample type
     */
    public function update(Request $request, SampleType $sampleType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:sample_types,code,' . $sampleType->id,
            'description' => 'nullable|string|max:1000',
            'container_type' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:20',
            'volume_ml' => 'nullable|numeric|min:0',
            'collection_instructions' => 'nullable|string|max:2000',
            'storage_requirements' => 'nullable|string|max:1000',
            'stability_hours' => 'nullable|integer|min:0',
            'requires_fasting' => 'boolean',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $sampleType->update($request->all());

            DB::commit();

            return redirect()->route('sample_types.index')
                ->with('success', 'Sample type updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update sample type: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sample type
     */
    public function destroy(SampleType $sampleType)
    {
        try {
            // Check if sample type has any medical services
            if ($sampleType->medicalServices()->exists()) {
                return back()->with('error', 'Cannot delete sample type with associated medical services');
            }

            $sampleType->delete();

            return redirect()->route('sample_types.index')
                ->with('success', 'Sample type deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete sample type: ' . $e->getMessage());
        }
    }

    /**
     * Toggle sample type status
     */
    public function toggleStatus(SampleType $sampleType)
    {
        try {
            $sampleType->update(['is_active' => !$sampleType->is_active]);

            $status = $sampleType->is_active ? 'activated' : 'deactivated';
            
            return back()->with('success', "Sample type {$status} successfully");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update sample type status: ' . $e->getMessage());
        }
    }
}
