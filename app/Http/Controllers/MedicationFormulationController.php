<?php

namespace App\Http\Controllers;

use App\Models\MedicationFormulation;
use Illuminate\Http\Request;

class MedicationFormulationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formulations = MedicationFormulation::withCount('medications')
                                            ->orderBy('description')
                                            ->get();
        
        return view('medications.formulations.index', compact('formulations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medications.formulations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:medication_formulations,description',
            'is_active' => 'boolean'
        ]);

        MedicationFormulation::create([
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true
        ]);

        return redirect()->route('medications.formulations.index')
                        ->with('success', 'Medication formulation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationFormulation $formulation)
    {
        $formulation->load(['medications' => function($query) {
            $query->orderBy('generic_name');
        }]);

        return view('medications.formulations.show', compact('formulation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicationFormulation $formulation)
    {
        return view('medications.formulations.edit', compact('formulation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationFormulation $formulation)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:medication_formulations,description,' . $formulation->id,
            'is_active' => 'boolean'
        ]);

        $formulation->update([
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : false
        ]);

        return redirect()->route('medications.formulations.index')
                        ->with('success', 'Medication formulation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationFormulation $formulation)
    {
        // Check if formulation is being used by any medications
        if ($formulation->isInUse()) {
            return redirect()->route('medications.formulations.index')
                            ->with('error', 'Cannot delete formulation. It is being used by one or more medications.');
        }

        $formulation->delete();

        return redirect()->route('medications.formulations.index')
                        ->with('success', 'Medication formulation deleted successfully.');
    }

    /**
     * Get active formulations for AJAX requests
     */
    public function getActiveFormulations()
    {
        $formulations = MedicationFormulation::active()
                                           ->orderBy('description')
                                           ->get(['id', 'description']);
        
        return response()->json($formulations);
    }
}
