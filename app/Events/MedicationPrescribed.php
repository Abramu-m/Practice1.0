<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicationPrescribed
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

    /**
     * Medication order payload (drug_key, dose, route, frequency, atc_code, etc.).
     */
    public array $order;

    public function __construct($patientId, array $order, $visitId = null)
    {
        $this->patientId = $patientId;
        $this->order = $order;
        $this->visitId = $visitId;
    }
}
