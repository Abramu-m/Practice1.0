<?php

namespace App\Services\CDS;

use Illuminate\Support\Facades\Log;

class CdsEngine
{
    public function __construct(private CdsAlertService $alerts)
    {
    }
    /**
     * Run CDS checks for a given trigger.
     * @param string $trigger e.g., 'medication_prescribe'
     * @param array $context keyed array containing patient_id, visit_id, order, etc.
     */
    public function check(string $trigger, array $context): void
    {
        // Simple router for rules; expand as needed.
        if ($trigger === 'medication_prescribe') {
            $this->runMedicationRules($context);
        }
    }

    private function runMedicationRules(array $context): void
    {
        $rules = [
            new Rules\AllergyRule(),
            new Rules\DuplicateTherapyRule(),
            new Rules\DoseRangeRule(),
        ];

        foreach ($rules as $rule) {
            try {
                $result = $rule->evaluate($context);
                if ($result) {
                    // Persist and log the alert
                    $payload = array_merge($result, [
                        'patient_id' => $context['patient_id'] ?? null,
                        'visit_id' => $context['visit_id'] ?? null,
                        'subject_type' => 'prescription',
                        'subject_id' => $context['order']['prescription_id'] ?? null,
                        'status' => 'open',
                    ]);
                    $alert = $this->alerts->create($payload);
                    Log::channel(config('cds.log_channel', 'single'))
                        ->info('CDS alert', ['id' => $alert->id] + $result);
                }
            } catch (\Throwable $e) {
                Log::channel(config('cds.log_channel', 'single'))
                    ->error('CDS rule error: '.$e->getMessage(), [
                        'rule' => get_class($rule),
                    ]);
            }
        }
    }
}
