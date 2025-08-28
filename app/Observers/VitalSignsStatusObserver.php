<?php

namespace App\Observers;

use App\Models\VitalSigns;
use App\Models\PatientVisit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VitalSignsStatusObserver
{
    /**
     * Handle vital signs creation
     * NOTE: Vital signs by nurses/triage should NOT trigger status change to "in treatment"
     * This is specifically excluded per requirements
     */
    public function created(VitalSigns $vitalSigns): void
    {
        // Explicitly do nothing for vital signs
        // This observer exists to document that vital signs should NOT trigger status changes
        Log::info("VitalSigns recorded - NO status change triggered (as per requirements)", [
            'vital_signs_id' => $vitalSigns->id,
            'visit_id' => $vitalSigns->visit_id,
            'consultation_id' => $vitalSigns->consultation_id,
            'recorded_by' => $vitalSigns->recorded_by,
            'note' => 'Vital signs by nurses/triage do not count as in treatment'
        ]);
    }

    /**
     * Handle vital signs updates
     * NOTE: Vital signs updates should NOT trigger status change to "in treatment"
     */
    public function updated(VitalSigns $vitalSigns): void
    {
        // Explicitly do nothing for vital signs updates
        Log::info("VitalSigns updated - NO status change triggered (as per requirements)", [
            'vital_signs_id' => $vitalSigns->id,
            'visit_id' => $vitalSigns->visit_id,
            'consultation_id' => $vitalSigns->consultation_id,
            'recorded_by' => $vitalSigns->recorded_by
        ]);
    }
}
