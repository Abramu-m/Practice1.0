<?php

namespace App\Listeners;

use App\Events\MedicationPrescribed;
use App\Services\CDS\CdsEngine;

class DispatchCdsChecks
{
    public function __construct(private CdsEngine $engine)
    {
    }

    public function handle($event): void
    {
        if ($event instanceof MedicationPrescribed) {
            $this->engine->check('medication_prescribe', [
                'patient_id' => $event->patientId,
                'visit_id' => $event->visitId,
                'order' => $event->order,
            ]);
        }
        // Extend with other event types as they are added
    }
}
