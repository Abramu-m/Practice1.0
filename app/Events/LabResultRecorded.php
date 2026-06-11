<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LabResultRecorded
{
    use Dispatchable, SerializesModels;

    /**
     * The patient identifier.
     * @var int|string|null
     */
    public $patientId;

    /**
     * The optional visit identifier.
     * @var int|string|null
     */
    public $visitId;

    public int $investigationId;

    /**
     * Normalized result data, e.g. ['parameters' => ['hemoglobin' => 6.2, ...]].
     */
    public array $result;

    /**
     * Investigation/service metadata: medical_service_id, medical_service_name, unit, min_value, max_value.
     */
    public array $investigationMeta;

    public function __construct($patientId, $visitId, int $investigationId, array $result, array $investigationMeta)
    {
        $this->patientId = $patientId;
        $this->visitId = $visitId;
        $this->investigationId = $investigationId;
        $this->result = $result;
        $this->investigationMeta = $investigationMeta;
    }
}
