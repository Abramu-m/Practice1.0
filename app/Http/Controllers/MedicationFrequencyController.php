<?php

namespace App\Http\Controllers;

use App\Models\MedicationFrequency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicationFrequencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $frequencies = MedicationFrequency::get();
        return view('medication-frequencies.index', compact('frequencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medication-frequencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'frequency_name' => 'required|string|max:255',
            'frequency_code' => 'required|string|max:50|unique:medication_frequencies,frequency_code',
            'administration_times' => 'required|array',
            'administration_times.*' => 'required|string',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        try {
            $frequency = MedicationFrequency::create([
                'frequency_name' => $request->frequency_name,
                'frequency_code' => $request->frequency_code,
                'administration_times' => $request->administration_times,
                'display_order' => $request->display_order ?? 1,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('medication-frequencies.index')
                ->with('success', 'Medication frequency created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating medication frequency: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating medication frequency. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationFrequency $medicationFrequency)
    {
        return view('medication-frequencies.show', compact('medicationFrequency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicationFrequency $medicationFrequency)
    {
        return view('medication-frequencies.edit', compact('medicationFrequency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationFrequency $medicationFrequency)
    {
        $request->validate([
            'frequency_name' => 'required|string|max:255',
            'frequency_code' => 'required|string|max:50|unique:medication_frequencies,frequency_code,' . $medicationFrequency->id,
            'administration_times' => 'required|array',
            'administration_times.*' => 'required|string',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        try {
            $medicationFrequency->update([
                'frequency_name' => $request->frequency_name,
                'frequency_code' => $request->frequency_code,
                'administration_times' => $request->administration_times,
                'display_order' => $request->display_order ?? 1,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('medication-frequencies.index')
                ->with('success', 'Medication frequency updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating medication frequency: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating medication frequency. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationFrequency $medicationFrequency)
    {
        try {
            // Check if frequency is being used in prescriptions
            if ($medicationFrequency->prescriptions()->exists()) {
                return redirect()->route('medication-frequencies.index')
                    ->with('error', 'Cannot delete medication frequency that is being used in prescriptions.');
            }

            $medicationFrequency->delete();
            return redirect()->route('medication-frequencies.index')
                ->with('success', 'Medication frequency deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting medication frequency: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting medication frequency. Please try again.');
        }
    }

    /**
     * Toggle the active status of the medication frequency.
     */
    public function toggleStatus(MedicationFrequency $medicationFrequency)
    {
        try {
            $medicationFrequency->update(['is_active' => !$medicationFrequency->is_active]);
            
            $status = $medicationFrequency->is_active ? 'activated' : 'deactivated';
            return redirect()->route('medication-frequencies.index')
                ->with('success', "Medication frequency {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Error toggling medication frequency status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating medication frequency status. Please try again.');
        }
    }
}
