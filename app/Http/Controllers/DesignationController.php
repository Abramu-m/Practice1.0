<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Designation::query();

        // Search functionality
        if ($search = request()->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('designation_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination (10 per page)
        $designations = $query->orderBy('created_at', 'desc')->paginate(10);

        $nhifItemCodes = \App\Models\NhifTariff::forFacility(config('nhif.facility_code'))
            ->nonRestricted()
            ->pluck('item_code')
            ->unique()
            ->all();

        return view('designations.index', compact('designations', 'nhifItemCodes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('designations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'designation_code' => 'required|string|max:20|unique:designations,designation_code',
            'description' => 'required|string|max:100|unique:designations,description',
            'status' => 'boolean',
        ]);

        Designation::create($request->all());

        return redirect()->route('designations.index')
                         ->with('success', 'Designation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Designation $designation)
    {
        return view('designations.show', compact('designation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Designation $designation)
    {
        return view('designations.edit', compact('designation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'designation_code' => 'required|string|max:20|unique:designations,designation_code,' . $designation->id,
            'description' => 'required|string|max:100|unique:designations,description,' . $designation->id,
            'status' => 'boolean',
        ]);

        $designation->update($request->all());

        return redirect()->route('designations.index')
                         ->with('success', 'Designation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Designation $designation)
    {
        $designation->delete();

        return redirect()->route('designations.index')
                         ->with('success', 'Designation deleted successfully.');
    }
}
