<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsRule;
use App\Models\CdsDosageLimit;
use App\Models\Medication;
use Illuminate\Support\Facades\DB;

class DoseRangeRule implements CdsRuleInterface
{
    private ?CdsRule $ruleConfig = null;

    public function setRuleConfiguration(CdsRule $rule): void
    {
        $this->ruleConfig = $rule;
    }

    public function getRequiredContext(): array
    {
        return ['patient_id', 'order.medication_name', 'order.dosage'];
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
        if (!config('cds.features.medication.dose_range', true)) {
            return null;
        }

        $order      = $context['order'] ?? [];
        $medName    = strtolower(trim($order['medication_name'] ?? ''));
        $dosageText = (string) ($order['dosage'] ?? '');

        // Resolve frequency: prefer direct dose_frequency integer, else look up by frequency_id
        if (isset($order['dose_frequency'])) {
            $frequency = (int) $order['dose_frequency'];
        } elseif (!empty($order['frequency_id'])) {
            $freq = DB::table('medication_frequencies')->where('id', $order['frequency_id'])->first();
            $frequency = $freq ? (int) $freq->times_per_day : null;
        } else {
            $frequency = null;
        }

        // Resolve duration: prefer dose_duration, else duration_days from prescription
        $duration = isset($order['dose_duration'])
            ? (int) $order['dose_duration']
            : (isset($order['duration_days']) ? (int) $order['duration_days'] : null);

        if ($medName === '' || $dosageText === '') {
            return null;
        }

        $orderedDose = $this->parseDose($dosageText);
        if ($orderedDose === null) {
            return null;
        }

        // Resolve medication from medications table
        $medication = Medication::whereRaw('LOWER(generic_name) = ?', [$medName])
            ->orWhereRaw('LOWER(brand_name) = ?', [$medName])
            ->first();

        if (!$medication) {
            return null;
        }

        // Load the active dosage limit for this medication
        $limit = CdsDosageLimit::where('medication_id', $medication->id)
            ->where('is_active', true)
            ->first();

        if (!$limit) {
            return null;
        }

        $patientAge    = $context['patient_age'] ?? ($context['patient']['age'] ?? null);
        $patientWeight = $context['patient_weight'] ?? ($context['patient']['weight'] ?? null);
        $patientCtx    = ['patient' => ['age' => $patientAge, 'weight' => $patientWeight]];

        // â”€â”€ 1. Paediatric limit (children â‰¤ 12 y) â”€â”€
        if ($patientAge !== null && $patientAge <= 12 && $patientWeight && $limit->max_single_dose_children) {
            $maxPerKg = $this->parseDose($limit->max_single_dose_children);
            if ($maxPerKg !== null) {
                $maxByWeight = $maxPerKg * (float) $patientWeight;
                if ($orderedDose > $maxByWeight) {
                    return [
                        'rule_key'     => 'pediatric_dose_exceeded',
                        'rule_version' => '4.0',
                        'severity'     => 'warning',
                        'message'      => ucwords($medication->name) . ': paediatric dose exceeds weight-based maximum',
                        'rationale'    => sprintf(
                            'For a child of %.1f kg, the max single dose is %s/kg Ã— %.1f kg = %.2f. Ordered: %.2f.',
                            (float) $patientWeight,
                            $limit->max_single_dose_children,
                            (float) $patientWeight,
                            $maxByWeight,
                            $orderedDose
                        ),
                        'payload' => [
                            'medication'          => $medName,
                            'ordered_dose'        => $orderedDose,
                            'max_pediatric_dose'  => $maxByWeight,
                            'per_kg_limit'        => $limit->max_single_dose_children,
                            'patient_weight_kg'   => (float) $patientWeight,
                            'limit_id'            => $limit->id,
                        ],
                    ];
                }
            }
        }

        // â”€â”€ 2. Adult max-single-dose limit â”€â”€
        if ($limit->max_single_dose_adults && (!$patientAge || $patientAge > 12)) {
            $maxAdult = $this->parseDose($limit->max_single_dose_adults);
            if ($maxAdult !== null && $orderedDose > $maxAdult) {
                return [
                    'rule_key'     => 'dose_range_exceeded',
                    'rule_version' => '4.0',
                    'severity'     => 'warning',
                    'message'      => ucwords($medication->name) . ": dose exceeds maximum ({$limit->max_single_dose_adults} per dose)",
                    'rationale'    => sprintf(
                        'Ordered dose of %.2f exceeds the maximum safe single dose of %s.',
                        $orderedDose,
                        $limit->max_single_dose_adults
                    ),
                    'payload' => [
                        'medication'     => $medName,
                        'ordered_dose'   => $orderedDose,
                        'max_single'     => $limit->max_single_dose_adults,
                        'limit_id'       => $limit->id,
                    ],
                ];
            }
        }

        // ── 3. Adult max-daily-dose limit (requires frequency) ──
        $isAdult = !$patientAge || $patientAge > 12;
        if ($isAdult && $frequency && $limit->max_daily_dose_adults) {
            $maxDaily  = $this->parseDose($limit->max_daily_dose_adults);
            $dailyDose = $orderedDose * $frequency;
            if ($maxDaily !== null && $dailyDose > $maxDaily) {
                return [
                    'rule_key'     => 'daily_dose_exceeded',
                    'rule_version' => '4.0',
                    'severity'     => 'warning',
                    'message'      => ucwords($medication->name) . ": daily dose exceeds maximum ({$limit->max_daily_dose_adults}/day)",
                    'rationale'    => sprintf(
                        '%.2f mg × %d doses/day = %.2f mg/day exceeds the maximum of %s/day.',
                        $orderedDose, $frequency, $dailyDose, $limit->max_daily_dose_adults
                    ),
                    'payload' => [
                        'medication'   => $medName,
                        'single_dose'  => $orderedDose,
                        'frequency'    => $frequency,
                        'daily_dose'   => $dailyDose,
                        'max_daily'    => $limit->max_daily_dose_adults,
                        'limit_id'     => $limit->id,
                    ],
                ];
            }
        }

        // ── 4. Duration limit ──
        if ($duration && $limit->max_duration_adults) {
            $maxDays = (int) filter_var($limit->max_duration_adults, FILTER_SANITIZE_NUMBER_INT);
            if ($maxDays > 0 && $duration > $maxDays) {
                return [
                    'rule_key'     => 'duration_exceeded',
                    'rule_version' => '4.0',
                    'severity'     => 'warning',
                    'message'      => ucwords($medication->name) . ": prescribed duration exceeds maximum ({$limit->max_duration_adults} days)",
                    'rationale'    => sprintf(
                        'Prescribed duration of %d days exceeds the maximum safe duration of %d days.',
                        $duration, $maxDays
                    ),
                    'payload' => [
                        'medication'      => $medName,
                        'prescribed_days' => $duration,
                        'max_days'        => $maxDays,
                        'limit_id'        => $limit->id,
                    ],
                ];
            }
        }

        return null;
    }

    /**
     * Parse a dose string and return the numeric value.
     *
     * Handles:
     *   "500 mg"   → 500   (ordered dose with explicit unit)
     *   "1.5 g"    → 1500  (grams normalised to mg)
     *   "1000"     → 1000  (bare number stored in limit — medication's own unit)
     *   "15/kg"    → 15    (per-kg limit stored as bare number)
     */
    private function parseDose(string $dose): ?float
    {
        $dose = trim($dose);
        if ($dose === '') {
            return null;
        }

        // Number with an explicit unit (ordered dosage from prescription)
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(mg|g|ml|mcg|iu|units?)/i', $dose, $m)) {
            $value = (float) $m[1];
            if (strtolower($m[2]) === 'g') {
                $value *= 1000.0; // grams → mg
            }
            return $value;
        }

        // Bare number — limit stored without unit; medication's own dispensing unit applies
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(\/kg)?$/i', $dose, $m)) {
            return (float) $m[1];
        }

        return null;
    }
}
