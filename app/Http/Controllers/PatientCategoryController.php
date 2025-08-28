<?php

namespace App\Http\Controllers;

use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patientCategories = PatientCategory::query();

        if ($request->has('search')) {
            $patientCategories->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('filter')) {
            $patientCategories->where('is_active', $request->filter);
        }

        if ($request->has('type_filter') && $request->type_filter != '') {
            $patientCategories->where('type', $request->type_filter);
        }

        $patientCategories = $patientCategories->paginate(10);

        return view('patient_categories.index', compact('patientCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('patient_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:50|unique:patient_categories,description',
            'type' => 'required|in:cash,insurance',
            'is_active' => 'boolean',
            'code' => 'nullable|string|max:30|unique:patient_categories,code',
        ]);

        $patientCategory = new PatientCategory();
        $patientCategory->description = $request->description;
        $patientCategory->type = $request->type;
        $patientCategory->is_active = $request->is_active ?? true;
    $patientCategory->code = $request->code ?? null;
        $patientCategory->created_by = Auth::id();
        $patientCategory->save();

        return redirect()->route('patient_categories.index')->with('success', 'Patient category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientCategory $patientCategory)
    {
        return view('patient_categories.show', compact('patientCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientCategory $patientCategory)
    {
        return view('patient_categories.edit', compact('patientCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientCategory $patientCategory)
    {
        $request->validate([
            'description' => 'required|string|max:50|unique:patient_categories,description,' . $patientCategory->id,
            'type' => 'required|in:cash,insurance',
            'is_active' => 'boolean',
            'code' => 'nullable|string|max:30|unique:patient_categories,code,' . $patientCategory->id,
        ]);

        $patientCategory->description = $request->description;
        $patientCategory->type = $request->type;
        $patientCategory->is_active = $request->is_active ?? true;
    $patientCategory->code = $request->code ?? null;
        $patientCategory->created_by = Auth::id();
        $patientCategory->save();

        return redirect()->route('patient_categories.index')->with('success', 'Patient category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientCategory $patientCategory)
    {
        $patientCategory->delete();

        return redirect()->route('patient_categories.index')->with('success', 'Patient category deleted successfully.');
    }
}
