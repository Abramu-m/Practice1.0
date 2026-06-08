<?php

namespace App\Services\CDS\Rules;

use App\Models\Allergy;
use App\Models\Medication;

class AllergyRule
{
    /**
     * Evaluate allergy conflicts.
     *
     * Priority:
     *  1. Exact FK match: allergy.medication_id === prescribed medication_id
     *  2. Cross-class match: allergy.medication_id's drug class vs. prescribed medication's class (if available)
     *  3. Name substring match: allergy.substance_name vs. medication generic/brand name (fallback)
     */
    public function evaluate(array $context): ?array
    {
        $order     = $context['order'] ?? [];
        $patientId = $context['patient_id'] ?? null;
        $medId     = $order['medication_id'] ?? null;

        if (!$patientId || !$medId) {
            return null;
        }

        $med = Medication::find($medId);
        if (!$med) {
            return null;
        }

        $allergies = Allergy::where('patient_id', $patientId)->where('is_active', true)->get();
        if ($allergies->isEmpty()) {
            return null;
        }

        $medName      = strtolower(trim($med->generic_name ?: $med->brand_name ?: ''));
        $medDrugClass = strtolower(trim($med->drug_class ?? ''));

        foreach ($allergies as $a) {
            $matchType = null;

            // --- 1. Exact FK match ---
            if ($a->medication_id && $a->medication_id === (int) $medId) {
                $matchType = 'exact_medication_match';
            }

            // --- 2. Drug-class cross-reactivity (if the allergy links to a medication with same class) ---
            if (!$matchType && $a->medication_id && $medDrugClass !== '') {
                $allergyMed = Medication::find($a->medication_id);
                if ($allergyMed && strtolower(trim($allergyMed->drug_class ?? '')) === $medDrugClass) {
                    $matchType = 'drug_class_cross_reactivity';
                }
            }

            // --- 3. Name substring fallback ---
            if (!$matchType && $medName !== '') {
                $sub = strtolower(trim($a->substance_name ?? ''));
                if ($sub !== '' && (str_contains($medName, $sub) || str_contains($sub, $medName))) {
                    $matchType = 'name_match';
                }
            }

            if ($matchType) {
                $matchDescription = match ($matchType) {
                    'exact_medication_match'       => 'Patient has a recorded allergy to this exact medication.',
                    'drug_class_cross_reactivity'  => 'Patient has an allergy to a related drug in the same class (' . ($med->drug_class ?? 'unknown') . ').',
                    default                        => 'Allergy recorded to a substance matching this medication by name.',
                };

                return [
                    'rule_key'     => 'drug_allergy_conflict',
                    'rule_version' => '2.0',
                    'severity'     => 'critical',
                    'message'      => $matchDescription,
                    'rationale'    => sprintf(
                        'Allergy to "%s" (severity: %s, reaction: %s). Prescribed: %s. Match type: %s.',
                        $a->substance_name,
                        $a->severity ?? 'unknown',
                        $a->reaction ?? 'not recorded',
                        $med->generic_name ?? $med->brand_name ?? 'Unknown',
                        $matchType
                    ),
                    'payload' => [
                        'allergy_id'    => $a->id,
                        'medication_id' => $med->id,
                        'match_type'    => $matchType,
                    ],
                ];
            }
        }

        return null;
    }
}
