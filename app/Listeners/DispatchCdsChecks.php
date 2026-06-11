<?php

namespace App\Listeners;

use App\Events\LabResultRecorded;
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
        } elseif ($event instanceof LabResultRecorded) {
            $this->engine->check('lab_result', [
                'patient_id' => $event->patientId,
                'visit_id' => $event->visitId,
                'investigation' => array_merge($event->investigationMeta, ['id' => $event->investigationId]),
                'result' => $event->result,
                'subject_type' => 'investigation',
                'subject_id' => $event->investigationId,
            ]);
        }
        // Extend with other event types as they are added
    }
}
