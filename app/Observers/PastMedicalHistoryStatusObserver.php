<?php

namespace App\Observers;

use App\Models\PastMedicalHistory;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;

class PastMedicalHistoryStatusObserver
{
    /**
     * Handle medical history creation to change patient visit status to "in treatment"
     */
    public function created(PastMedicalHistory $medicalHistory): void
    {
        $this->updateVisitStatusToInTreatment($medicalHistory);
    }

    /**
     * Handle medical history updates to change patient visit status to "in treatment"
     */
    public function updated(PastMedicalHistory $medicalHistory): void
    {
        // Check if meaningful medical history fields were updated by doctor
        $doctorFields = [
            'allergies',
            'chronic_conditions',
            'previous_surgeries',
            'family_history',
            'social_history',
            'occupational_history',
            'smoking_status',
            'alcohol_use',
            'current_medications',
            'immunization_history',
            'reproductive_history'
        ];

        if ($medicalHistory->isDirty($doctorFields)) {
            $this->updateVisitStatusToInTreatment($medicalHistory);
        }
    }

    /**
     * Update patient visit status to "in treatment" when medical history is documented by doctor
     */
    private function updateVisitStatusToInTreatment(PastMedicalHistory $medicalHistory): void
    {
        try {
            // Find the current active consultation for this patient
            $patient = $medicalHistory->patient;
            if (!$patient) {
                Log::warning("No patient found for medical history ID: {$medicalHistory->id}");
                return;
            }

            // Find active patient visit using the patient model method
            $visit = $patient->active_visit;

            if (!$visit) {
                Log::info("No active patient visit found for patient during medical history update", [
                    'patient_id' => $patient->id,
                    'medical_history_id' => $medicalHistory->id
                ]);
                return;
            }

            // Only update if currently waiting (status 0)
            if ($visit->visit_status == 0) {
                $visit->update([
                    'visit_status' => 1, // In Treatment
                    'consulted_at' => now()
                ]);

                Log::info("Patient visit status updated to 'In Treatment'", [
                    'visit_id' => $visit->id,
                    'medical_history_id' => $medicalHistory->id,
                    'patient_id' => $visit->patient,
                    'previous_status' => 0,
                    'new_status' => 1,
                    'trigger' => 'medical_history_update'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating patient visit status in PastMedicalHistoryStatusObserver", [
                'medical_history_id' => $medicalHistory->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
