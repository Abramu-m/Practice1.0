<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsRule;
use App\Models\CdsDosageLimit;
use App\Models\Medication;
use App\Services\CDS\Calculators\EgfrCalculator;

/**
 * RenalDoseRule â€” checks whether the prescribed medication requires dose
 * reduction or is contraindicated based on the patient's renal function.
 *
 * Reads `renal_function_adults` / `renal_function_children` JSON columns from
 * the `cds_dosage_limits` table for the matched medication.
 *
 * Expected JSON shape for renal_function_*:
 *   {
 *     "creatinine": {"operator": ">", "value": 120, "unit": "umol/l"},
 *     "egfr":       {"operator": "<", "value": 30},
 *     "urea":       {"operator": ">", "value": 8,   "unit": "mmol/l"},
 *     "action":     "avoid" | "reduce",
 *     "max_daily":  "500 mg"   // only meaningful when action = "reduce"
 *   }
 */
class RenalDoseRule implements CdsRuleInterface
{
    private ?CdsRule $ruleConfig = null;

    public function setRuleConfiguration(CdsRule $rule): void
    {
        $this->ruleConfig = $rule;
    }

    public function getRequiredContext(): array
    {
        return ['patient_id', 'order.medication_name'];
    }

    public function validateConfiguration(array $parameters): bool
    {
        return true;
    }

    public function getDefaultParameters(): array
    {
        return [];
    }

    public function evaluate(array $context): ?array
    {
        if (!config('cds.features.medication.renal_dose', true)) {
            return null;
        }

        $order   = $context['order'] ?? [];
        $medName = strtolower(trim($order['medication_name'] ?? ''));

        if ($medName === '') {
            return null;
        }

        // 1. Find the medication in the medications table
        $medication = Medication::whereRaw('LOWER(name) = ?', [$medName])
            ->orWhereRaw('LOWER(generic_name) = ?', [$medName])
            ->first();

        if (!$medication) {
            return null;
        }

        // 2. Load the active dosage limit for this medication
        $limit = CdsDosageLimit::where('medication_id', $medication->id)
            ->where('is_active', true)
            ->first();

        if (!$limit) {
            return null;
        }

        // 3. Determine which renal function block to use
        $patientAge = $context['patient_age'] ?? ($context['patient']['age'] ?? null);
        $renalData  = ($patientAge !== null && $patientAge <= 12)
            ? $limit->renal_function_children
            : $limit->renal_function_adults;

        if (empty($renalData)) {
            return null;
        }

        // 4. Resolve patient's eGFR
        $egfr = $this->resolveEgfr($context);
        if ($egfr === null) {
            return null;
        }

        // 5. Evaluate the eGFR threshold from the JSON
        $egfrRule = $renalData['egfr'] ?? null;
        if (!$egfrRule) {
            return null;
        }

        $threshold = (float) ($egfrRule['value'] ?? 0);
        $operator  = $egfrRule['operator'] ?? '<';

        $triggered = match ($operator) {
            '<'  => $egfr < $threshold,
            '<=' => $egfr <= $threshold,
            '>'  => $egfr > $threshold,
            '>=' => $egfr >= $threshold,
            default => false,
        };

        if (!$triggered) {
            return null;
        }

        $action   = $renalData['action'] ?? 'reduce';
        $isAvoid  = $action === 'avoid';

        if ($isAvoid) {
            return [
                'rule_key'     => 'renal_dose_contraindicated',
                'rule_version' => '2.0',
                'severity'     => 'critical',
                'message'      => ucwords($medication->name)
                    . ' is contraindicated in renal impairment'
                    . ' (eGFR ' . number_format($egfr, 1) . ' mL/min)',
                'rationale'    => sprintf(
                    '%s is contraindicated when eGFR %s %.0f mL/min. '
                    . 'Patient eGFR: %.1f mL/min. Do not prescribe.',
                    ucwords($medication->name),
                    $operator,
                    $threshold,
                    $egfr
                ),
                'payload' => [
                    'medication'     => $medName,
                    'patient_egfr'   => round($egfr, 1),
                    'egfr_threshold' => $threshold,
                    'action'         => 'avoid',
                    'limit_id'       => $limit->id,
                ],
            ];
        }

        return [
            'rule_key'     => 'renal_dose_adjustment_required',
            'rule_version' => '2.0',
            'severity'     => 'warning',
            'message'      => ucwords($medication->name)
                . ': dose reduction required in renal impairment'
                . ' (eGFR ' . number_format($egfr, 1) . ' mL/min)',
            'rationale'    => sprintf(
                'Patient eGFR is %.1f mL/min (%s threshold of %.0f mL/min). '
                . 'Review dosage and reduce accordingly.',
                $egfr,
                $operator,
                $threshold
            ),
            'payload' => [
                'medication'     => $medName,
                'patient_egfr'   => round($egfr, 1),
                'egfr_threshold' => $threshold,
                'max_daily'      => $renalData['max_daily'] ?? null,
                'action'         => 'reduce',
                'limit_id'       => $limit->id,
            ],
        ];
    }

    private function resolveEgfr(array $context): ?float
    {
        if (isset($context['patient']['egfr'])) {
            return (float) $context['patient']['egfr'];
        }

        $age            = $context['patient_age']    ?? ($context['patient']['age']    ?? null);
        $weight         = $context['patient_weight'] ?? ($context['patient']['weight'] ?? null);
        $gender         = $context['patient_gender'] ?? ($context['patient']['gender'] ?? 'male');
        $creatinineUmol = $context['patient']['creatinine_umol_l'] ?? null;
        $creatinineMgDl = $context['patient']['creatinine_mg_dl']  ?? null;

        if ($creatinineUmol !== null && $creatinineMgDl === null) {
            $creatinineMgDl = (float) $creatinineUmol / 88.4;
        }

        if (!$age || !$weight || !$creatinineMgDl) {
            return null;
        }

        return EgfrCalculator::cockcroftGault(
            (float) $age,
            (float) $weight,
            (float) $creatinineMgDl,
            $gender
        );
    }
}
