<?php

namespace App\Observers;

use App\Models\SystemicExamination;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;

class SystemicExaminationStatusObserver
{
    /**
     * Handle systemic examination creation to change patient visit status to "in treatment"
     */
    public function created(SystemicExamination $examination): void
    {
        $this->updateVisitStatusToInTreatment($examination);
    }

    /**
     * Handle systemic examination updates
     */
    public function updated(SystemicExamination $examination): void
    {
        // Any update to examination findings indicates doctor activity
        $this->updateVisitStatusToInTreatment($examination);
    }

    /**
     * Update patient visit status to "in treatment" when examination is performed by doctor
     */
    private function updateVisitStatusToInTreatment(SystemicExamination $examination): void
    {
        try {
            // Get consultation and then visit
            $consultation = $examination->consultation;
            if (!$consultation) {
                Log::warning("No consultation found for examination ID: {$examination->id}");
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
                    'examination_id' => $examination->id,
                    'patient_id' => $visit->patient,
                    'previous_status' => 0,
                    'new_status' => 1,
                    'trigger' => 'systemic_examination'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating patient visit status in SystemicExaminationStatusObserver", [
                'examination_id' => $examination->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
