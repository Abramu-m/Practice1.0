<?php

namespace App\Observers;

use App\Models\IcdDiagnosis;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;

class IcdDiagnosisStatusObserver
{
    /**
     * Handle ICD diagnosis creation to change patient visit status to "in treatment"
     */
    public function created(IcdDiagnosis $icdDiagnosis): void
    {
        $this->updateVisitStatusToInTreatment($icdDiagnosis);
    }

    /**
     * Handle ICD diagnosis updates
     */
    public function updated(IcdDiagnosis $icdDiagnosis): void
    {
        // Any update to ICD diagnosis indicates doctor activity
        $this->updateVisitStatusToInTreatment($icdDiagnosis);
    }

    /**
     * Update patient visit status to "in treatment" when ICD diagnosis is added/updated by doctor
     */
    private function updateVisitStatusToInTreatment(IcdDiagnosis $icdDiagnosis): void
    {
        try {
            // Get consultation and then visit
            $consultation = $icdDiagnosis->consultation;
            if (!$consultation) {
                Log::warning("No consultation found for ICD diagnosis ID: {$icdDiagnosis->id}");
                return;
            }

            $visit = $consultation->patientVisit;
            if (!$visit) {
                Log::warning("No patient visit found for consultation ID: {$consultation->id}");
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
                    'consultation_id' => $consultation->id,
                    'icd_diagnosis_id' => $icdDiagnosis->id,
                    'patient_id' => $visit->patient,
                    'previous_status' => 0,
                    'new_status' => 1,
                    'trigger' => 'icd_diagnosis'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating patient visit status in IcdDiagnosisStatusObserver", [
                'icd_diagnosis_id' => $icdDiagnosis->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
