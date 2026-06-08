<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;

class PrescriptionStatusObserver
{
    /**
     * Handle prescription creation to change patient visit status to "in treatment"
     */
    public function created(Prescription $prescription): void
    {
        $this->updateVisitStatusToInTreatment($prescription);
    }

    /**
     * Handle prescription updates that might indicate doctor activity
     */
    public function updated(Prescription $prescription): void
    {
        // Only trigger on meaningful doctor updates (not just status changes by pharmacy)
        $doctorFields = [
            'medication_id',
            'dosage', 
            'quantity',
            'duration_days',
            'instructions',
            'administration_route_id',
            'frequency_id'
        ];

        if ($prescription->isDirty($doctorFields)) {
            $this->updateVisitStatusToInTreatment($prescription);
        }
    }

    /**
     * Update patient visit status to "in treatment" when prescription is created/updated by doctor
     */
    private function updateVisitStatusToInTreatment(Prescription $prescription): void
    {
        try {
            // Get consultation and then visit
            $consultation = $prescription->consultation;
            if (!$consultation) {
                Log::warning("No consultation found for prescription ID: {$prescription->id}");
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
                    'prescription_id' => $prescription->id,
                    'patient_id' => $visit->patient,
                    'previous_status' => 0,
                    'new_status' => 1,
                    'trigger' => 'prescription_action'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating patient visit status in PrescriptionStatusObserver", [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
