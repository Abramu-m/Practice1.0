<?php

namespace App\Observers;

use App\Models\Investigation;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;

class InvestigationStatusObserver
{
    /**
     * Handle investigation creation to change patient visit status to "in treatment"
     */
    public function created(Investigation $investigation): void
    {
        $this->updateVisitStatusToInTreatment($investigation);
    }

    /**
     * Handle investigation updates that might indicate doctor activity
     */
    public function updated(Investigation $investigation): void
    {
        // Only trigger on meaningful doctor updates (not lab status changes)
        $doctorFields = [
            'medical_service_id',
            'quantity',
            'priority',
            'notes',
            'clinical_data'
        ];

        if ($investigation->isDirty($doctorFields)) {
            $this->updateVisitStatusToInTreatment($investigation);
        }
    }

    /**
     * Update patient visit status to "in treatment" when investigation is ordered by doctor
     */
    private function updateVisitStatusToInTreatment(Investigation $investigation): void
    {
        try {
            // Get consultation and then visit
            $consultation = $investigation->consultation;
            if (!$consultation) {
                Log::warning("No consultation found for investigation ID: {$investigation->id}");
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
                    'investigation_id' => $investigation->id,
                    'patient_id' => $visit->patient,
                    'previous_status' => 0,
                    'new_status' => 1,
                    'trigger' => 'investigation_order'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating patient visit status in InvestigationStatusObserver", [
                'investigation_id' => $investigation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
