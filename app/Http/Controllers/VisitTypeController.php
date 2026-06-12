<?php

namespace App\Http\Controllers;

use App\Models\PatientCategory;
use App\Models\VisitType;
use Illuminate\Http\Request;

class VisitTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $visitTypes = VisitType::with('patientCategories')->get();
        return view('visit_types.index', compact('visitTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patientCategories = PatientCategory::orderBy('description')->get();
        return view('visit_types.create', compact('patientCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:visit_types,description',
            'nhif_visit_type_code' => 'nullable|integer|min:1|max:255',
            'patient_categories' => 'nullable|array',
            'patient_categories.*' => 'exists:patient_categories,id',
        ]);

        $visitType = VisitType::create($request->only('description', 'nhif_visit_type_code'));
        $visitType->patientCategories()->sync($request->input('patient_categories', []));

        return redirect()->route('visit_types.index')
                         ->with('success', 'Visit Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VisitType $visitType)
    {
        $visitType->load('patientCategories');
        return view('visit_types.show', compact('visitType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VisitType $visitType)
    {
        $visitType->load('patientCategories');
        $patientCategories = PatientCategory::orderBy('description')->get();
        return view('visit_types.edit', compact('visitType', 'patientCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VisitType $visitType)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:visit_types,description,' . $visitType->id,
            'nhif_visit_type_code' => 'nullable|integer|min:1|max:255',
            'patient_categories' => 'nullable|array',
            'patient_categories.*' => 'exists:patient_categories,id',
        ]);

        $visitType->update($request->only('description', 'nhif_visit_type_code'));
        $visitType->patientCategories()->sync($request->input('patient_categories', []));

        return redirect()->route('visit_types.index')
                         ->with('success', 'Visit Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VisitType $visitType)
    {
        $visitType->delete();

        return redirect()->route('visit_types.index')
                         ->with('success', 'Visit Type deleted successfully.');
    }
}
