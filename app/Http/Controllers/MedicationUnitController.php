<?php

namespace App\Http\Controllers;

use App\Models\MedicationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicationUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = MedicationUnit::get();
        return view('medication-units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medication-units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'required|string|max:50|unique:medication_units,unit_code',
            'unit_symbol' => 'nullable|string|max:20',
            'unit_type' => 'required|in:' . implode(',', MedicationUnit::getUnitTypes()),
            'conversion_factor' => 'nullable|numeric|min:0',
            'base_unit_id' => 'nullable|exists:medication_units,id',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        try {
            $unit = MedicationUnit::create([
                'unit_name' => $request->unit_name,
                'unit_code' => $request->unit_code,
                'unit_symbol' => $request->unit_symbol,
                'unit_type' => $request->unit_type,
                'conversion_factor' => $request->conversion_factor,
                'base_unit_id' => $request->base_unit_id,
                'display_order' => $request->display_order ?? 1,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('medication-units.index')
                ->with('success', 'Medication unit created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating medication unit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating medication unit. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationUnit $medicationUnit)
    {
        return view('medication-units.show', compact('medicationUnit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicationUnit $medicationUnit)
    {
        $baseUnits = MedicationUnit::active()
            ->where('id', '!=', $medicationUnit->id)
            ->get();
            
        return view('medication-units.edit', compact('medicationUnit', 'baseUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationUnit $medicationUnit)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'required|string|max:50|unique:medication_units,unit_code,' . $medicationUnit->id,
            'unit_symbol' => 'nullable|string|max:20',
            'unit_type' => 'required|in:' . implode(',', MedicationUnit::getUnitTypes()),
            'conversion_factor' => 'nullable|numeric|min:0',
            'base_unit_id' => 'nullable|exists:medication_units,id',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        try {
            $medicationUnit->update([
                'unit_name' => $request->unit_name,
                'unit_code' => $request->unit_code,
                'unit_symbol' => $request->unit_symbol,
                'unit_type' => $request->unit_type,
                'conversion_factor' => $request->conversion_factor,
                'base_unit_id' => $request->base_unit_id,
                'display_order' => $request->display_order ?? 1,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('medication-units.index')
                ->with('success', 'Medication unit updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating medication unit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating medication unit. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationUnit $medicationUnit)
    {
        try {
            // Check if unit is being used in medications or prescriptions
            if ($medicationUnit->medications()->exists()) {
                return redirect()->route('medication-units.index')
                    ->with('error', 'Cannot delete medication unit that is being used in medications.');
            }

            $medicationUnit->delete();
            return redirect()->route('medication-units.index')
                ->with('success', 'Medication unit deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting medication unit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting medication unit. Please try again.');
        }
    }

    /**
     * Toggle the active status of the medication unit.
     */
    public function toggleStatus(MedicationUnit $medicationUnit)
    {
        try {
            $medicationUnit->update(['is_active' => !$medicationUnit->is_active]);
            
            $status = $medicationUnit->is_active ? 'activated' : 'deactivated';
            return redirect()->route('medication-units.index')
                ->with('success', "Medication unit {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Error toggling medication unit status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating medication unit status. Please try again.');
        }
    }

    /**
     * Get base units for AJAX requests.
     */
    public function getBaseUnits()
    {
        $baseUnits = MedicationUnit::active()->get();
        return response()->json($baseUnits);
    }

    /**
     * Get dispensing units for AJAX requests.
     */
    public function getDispensingUnits()
    {
        $dispensingUnits = MedicationUnit::active()->get();
        return response()->json($dispensingUnits);
    }
}
