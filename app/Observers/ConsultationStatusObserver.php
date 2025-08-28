<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\SystemicExamination;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;

class ConsultationStatusObserver
{
    /**
     * Handle consultation updates to change patient visit status to "in treatment"
     */
    public function updated(Consultation $consultation): void
    {
        // Only trigger status change if important consultation fields are being updated by doctor
        $doctorFields = [
            'history_of_present_illness',
            'provisional_diagnosis', 
            'final_diagnosis',
            'remarks',
            'followup_instructions'
        ];

        if ($consultation->isDirty($doctorFields)) {
            $this->updateVisitStatusToInTreatment($consultation);
        }
    }

    /**
     * Update patient visit status to "in treatment" when doctor input is saved
     */
    private function updateVisitStatusToInTreatment(Consultation $consultation): void
    {
        try {
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
                    'patient_id' => $visit->patient,
                    'previous_status' => 0,
                    'new_status' => 1,
                    'trigger' => 'consultation_update'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating patient visit status in ConsultationStatusObserver", [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
