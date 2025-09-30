<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AllergyController extends Controller
{
    /**
     * List allergies for a patient
     */
    public function index(Patient $patient)
    {
        $allergies = $patient->allergies()->orderByDesc('id')->get();
        return response()->json(['data' => $allergies]);
    }

    /**
     * Store a new allergy
     */
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'substance_name' => 'required|string|max:255',
            'reaction' => 'required_if:severity,severe|nullable|string|max:255',
            'severity' => 'nullable|in:mild,moderate,severe',
            'is_active' => 'sometimes|boolean'
        ]);
        $data['patient_id'] = $patient->id;
        $data['recorded_at'] = now();

        // Prevent duplicate active allergy for same substance
        if (!isset($data['is_active']) || $data['is_active'] === true) {
            $exists = \App\Models\Allergy::where('patient_id', $patient->id)
                ->where('substance_name', $data['substance_name'])
                ->where('is_active', true)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active allergy for this substance already exists.'
                ], 409);
            }
        }
        $allergy = Allergy::create($data);
        return response()->json(['success' => true, 'message' => 'Allergy recorded.', 'data' => $allergy], 201);
    }

    /**
     * Update an allergy
     */
    public function update(Request $request, Allergy $allergy)
    {
        $data = $request->validate([
            'substance_name' => 'required|string|max:255',
            'reaction' => 'required_if:severity,severe|nullable|string|max:255',
            'severity' => 'nullable|in:mild,moderate,severe',
            'is_active' => 'sometimes|boolean'
        ]);

        // Duplicate guard if reactivating or staying active and substance changed
        $targetActive = $data['is_active'] ?? $allergy->is_active;
        if ($targetActive) {
            $exists = \App\Models\Allergy::where('patient_id', $allergy->patient_id)
                ->where('substance_name', $data['substance_name'])
                ->where('is_active', true)
                ->where('id', '!=', $allergy->id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another active allergy for this substance already exists.'
                ], 409);
            }
        }
        $allergy->update($data);
        return response()->json(['success' => true, 'message' => 'Allergy updated.', 'data' => $allergy]);
    }

    /**
     * Deactivate (soft) an allergy (toggle is_active)
     */
    public function deactivate(Allergy $allergy)
    {
        $allergy->delete();
        return response()->json(['success' => true, 'message' => 'Allergy removed.']);
    }

    /**
     * Delete an allergy (hard delete if created in error)
     */
    public function destroy(Allergy $allergy)
    {
        $allergy->delete();
        return response()->json(['success' => true, 'message' => 'Allergy removed.']);
    }
}
