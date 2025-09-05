<?php

namespace App\Services\CDS\Rules;

use App\Models\Allergy;
use App\Models\Medication;

class AllergyRule
{
    /**
     * Evaluate allergy conflicts: naive substring match of prescribed medication name against recorded allergy substances.
     */
    public function evaluate(array $context): ?array
    {
        $order = $context['order'] ?? [];
        $patientId = $context['patient_id'] ?? null;
        $medicationId = $order['medication_id'] ?? null;

        if (!$patientId || !$medicationId) {
            return null;
        }

        $med = Medication::find($medicationId);
        if (!$med) {
            return null;
        }

        $medName = strtolower(trim(($med->generic_name ?: $med->brand_name ?: '')));
        if ($medName === '') {
            return null;
        }

        $allergies = Allergy::where('patient_id', $patientId)->where('is_active', true)->get();
        if ($allergies->isEmpty()) {
            return null;
        }

        foreach ($allergies as $a) {
            $sub = strtolower(trim($a->substance_name ?? ''));
            if ($sub !== '' && (str_contains($medName, $sub) || str_contains($sub, $medName))) {
                return [
                    'rule_key' => 'drug_allergy_conflict',
                    'rule_version' => '1.0',
                    'severity' => 'critical',
                    'message' => 'Allergy conflict: patient has a recorded allergy matching this medication.',
                    'rationale' => sprintf('Allergy recorded to "%s" (severity: %s). Medication: %s.', $a->substance_name, $a->severity ?? 'unknown', $med->generic_name ?? $med->brand_name ?? 'Unknown'),
                    'payload' => [
                        'allergy_id' => $a->id,
                        'medication_id' => $med->id,
                    ],
                ];
            }
        }

        return null;
    }
}
