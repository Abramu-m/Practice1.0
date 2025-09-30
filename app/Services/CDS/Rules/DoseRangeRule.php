<?php

namespace App\Services\CDS\Rules;

use App\Models\Patient;
use App\Models\VitalSigns;
use App\Models\MedicationFrequency;
use Illuminate\Support\Carbon;

class DoseRangeRule
{
    /**
     * Minimal dose range safety check.
     * - Uses config('cds.dose_policies') for known meds.
     * - Checks single-dose max; optional pediatric mg/kg per dose if age/weight available.
     *
     * Expected context keys (best-effort):
     *   patient_id (int), visit_id (int|null), order => [ medication_name (string), dosage (string) ]
     */
    public function evaluate(array $context): ?array
    {
        if (!config('cds.features.medication.dose_range', false)) {
            return null;
        }

        $order = $context['order'] ?? [];
        $medName = strtolower(trim($order['medication_name'] ?? ''));
        $dosageText = (string)($order['dosage'] ?? '');
        if ($medName === '' || $dosageText === '') {
            return null; // not enough info
        }

        // Match policy by simple contains of key in med name (e.g., 'paracetamol')
        $policies = (array) config('cds.dose_policies', []);
        $policyKey = null;
        foreach ($policies as $key => $_) {
            if (str_contains($medName, strtolower($key))) {
                $policyKey = $key;
                break;
            }
        }
        if (!$policyKey) {
            return null; // no policy available for this med
        }
        $policy = $policies[$policyKey];

    $mgPerDose = $this->parseDoseToMg($dosageText);
        if ($mgPerDose === null) {
            return null; // cannot safely evaluate
        }

        $patientId = $context['patient_id'] ?? null;
        $visitId = $context['visit_id'] ?? null;

        // Determine age (years) and weight (kg) if available
        $ageYears = null; $weightKg = null; $sex = null;
        if ($patientId) {
            $patient = Patient::find($patientId);
            if ($patient) {
                $sex = $patient->gender ?? null;
                if (!empty($patient->date_of_birth)) {
                    try {
                        $ageYears = Carbon::parse($patient->date_of_birth)->age;
                    } catch (\Throwable) {
                        $ageYears = null;
                    }
                }
            }
        }
        if ($visitId) {
            $vitals = VitalSigns::where('visit_id', $visitId)->latest('id')->first();
            if ($vitals && $vitals->weight) {
                $weightKg = (float)$vitals->weight;
            }
        }

        $alerts = [];

        // Determine daily doses using frequency if provided
        $timesPerDay = 1;
        $freqId = $order['frequency_id'] ?? null;
        if ($freqId) {
            $freq = MedicationFrequency::find($freqId);
            if ($freq && $freq->times_per_day) {
                $timesPerDay = max(1, (int)$freq->times_per_day);
            } elseif ($freq && $freq->interval_hours) {
                $tph = 24 / max(1, (int)$freq->interval_hours);
                $timesPerDay = (int) round($tph);
                $timesPerDay = max(1, $timesPerDay);
            }
        }
        $dailyMg = $mgPerDose * $timesPerDay;

        // Check absolute per-dose max
        if (isset($policy['max_single_mg']) && is_numeric($policy['max_single_mg'])) {
            $maxSingle = (float)$policy['max_single_mg'];
            if ($mgPerDose > $maxSingle) {
                $severity = $mgPerDose > ($maxSingle * 1.5) ? 'critical' : 'high';
                $alerts[] = [
                    'rule_key' => 'dose_range',
                    'rule_version' => '1.0',
                    'severity' => $severity,
                    'message' => ucfirst($policyKey) . ' dose exceeds maximum per dose',
                    'rationale' => sprintf(
                        'Ordered %s (≈ %.0f mg) exceeds per-dose max of %.0f mg.',
                        $dosageText,
                        $mgPerDose,
                        $maxSingle
                    ),
                    'payload' => [
                        'medication' => $medName,
                        'dose_mg' => $mgPerDose,
                        'max_single_mg' => $maxSingle,
                    ],
                ];
            }
        }

        // Check daily max if policy provided
        if (isset($policy['max_daily_mg']) && is_numeric($policy['max_daily_mg'])) {
            $maxDaily = (float)$policy['max_daily_mg'];
            if ($dailyMg > $maxDaily) {
                $severity = $dailyMg > ($maxDaily * 1.25) ? 'critical' : 'high';
                $alerts[] = [
                    'rule_key' => 'dose_range_daily',
                    'rule_version' => '1.0',
                    'severity' => $severity,
                    'message' => ucfirst($policyKey) . ' exceeds maximum daily dose',
                    'rationale' => sprintf(
                        'Ordered ~%.0f mg/day (%.0f mg x %d/day) exceeds daily max of %.0f mg.',
                        $dailyMg,
                        $mgPerDose,
                        $timesPerDay,
                        $maxDaily
                    ),
                    'payload' => [
                        'medication' => $medName,
                        'daily_mg' => $dailyMg,
                        'max_daily_mg' => $maxDaily,
                        'times_per_day' => $timesPerDay,
                    ],
                ];
            }
        }

        // Pediatric per-dose mg/kg check (if age/weight available and policy defined)
        if ($ageYears !== null && $weightKg !== null && isset($policy['peds_mg_per_kg_dose'])) {
            $perKgConfig = $policy['peds_mg_per_kg_dose'];
            $isStructured = is_array($perKgConfig);
            $perKgDose = $isStructured ? (float)($perKgConfig['mg_per_kg'] ?? 0) : (float)$perKgConfig;
            $minAge = $isStructured ? (int)($perKgConfig['min_age_years'] ?? 0) : 0;
            $maxAge = $isStructured ? (int)($perKgConfig['max_age_years'] ?? 12) : (int)($policy['peds_age_years'] ?? 12);
            $maxSinglePeds = $isStructured && isset($perKgConfig['max_single_mg']) ? (float)$perKgConfig['max_single_mg'] : null;
            if ($perKgDose > 0 && $ageYears >= $minAge && $ageYears < $maxAge) {
                $recommended = $perKgDose * $weightKg;
                if ($maxSinglePeds !== null) {
                    $recommended = min($recommended, $maxSinglePeds);
                }
                $tolerance = (float)($policy['tolerance'] ?? 0.2); // 20% default
                $limit = $recommended * (1 + $tolerance);
                if ($mgPerDose > $limit) {
                    $overPct = round((($mgPerDose - $recommended) / max($recommended, 1)) * 100);
                    $severity = $mgPerDose > ($recommended * 1.5) ? 'critical' : 'high';
                    $alerts[] = [
                        'rule_key' => 'dose_range',
                        'rule_version' => '1.0',
                        'severity' => $severity,
                        'message' => 'Pediatric dose exceeds recommended mg/kg per dose',
                        'rationale' => sprintf(
                            'Age %dy, weight %.1f kg. Ordered %s (≈ %.0f mg) vs recommended ~%.0f mg (%.0f mg/kg). ~%d%% above.',
                            $ageYears,
                            $weightKg,
                            $dosageText,
                            $mgPerDose,
                            $recommended,
                            $perKgDose,
                            $overPct
                        ),
                        'payload' => [
                            'medication' => $medName,
                            'dose_mg' => $mgPerDose,
                            'peds_mg_per_kg_dose' => $perKgDose,
                            'weight_kg' => $weightKg,
                            'age_years' => $ageYears,
                        ],
                    ];
                }
            }
        }

        // Optional renal adjustment if egfr present in context and policy defines renal caps
        $egfr = isset($context['egfr']) && is_numeric($context['egfr']) ? (float)$context['egfr'] : null;
        if ($egfr !== null && isset($policy['renal']) && is_array($policy['renal'])) {
            // Example: apply stricter daily max when egfr below thresholds
            $renal = $policy['renal'];
            // Find the most restrictive cap for this egfr
            $cap = null;
            foreach ($renal as $rule) {
                if (!isset($rule['egfr_max']) || !isset($rule['max_daily_mg'])) continue;
                if ($egfr <= (float)$rule['egfr_max']) {
                    $cap = $cap === null ? (float)$rule['max_daily_mg'] : min($cap, (float)$rule['max_daily_mg']);
                }
            }
            if ($cap !== null && $dailyMg > $cap) {
                $alerts[] = [
                    'rule_key' => 'dose_range_renal',
                    'rule_version' => '1.0',
                    'severity' => 'high',
                    'message' => 'Dose exceeds renal-adjusted daily maximum',
                    'rationale' => sprintf(
                        'eGFR %.1f. Ordered ~%.0f mg/day exceeds renal-adjusted max of %.0f mg/day.',
                        $egfr,
                        $dailyMg,
                        $cap
                    ),
                    'payload' => [
                        'medication' => $medName,
                        'daily_mg' => $dailyMg,
                        'renal_cap_daily_mg' => $cap,
                        'egfr' => $egfr,
                    ],
                ];
            }
        }

        // Return the highest-severity alert if any
        if (!empty($alerts)) {
            usort($alerts, function ($a, $b) {
                $sev = ['info' => 0, 'low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
                return ($sev[$b['severity']] ?? 0) <=> ($sev[$a['severity']] ?? 0);
            });
            return $alerts[0];
        }

        return null;
    }

    /**
     * Parse a dosage string into mg. Supports forms like "500 mg", "1 g".
     * Returns null when parsing is not possible.
     */
    /**
     * Parse a dosage string into mg. Supports forms like "500 mg", "1 g", "1/2 mg", "½ mg", "1 milligram", etc.
     * Returns null when parsing is not possible.
     */
    private function parseDoseToMg(string $dosage): ?float
    {
        $t = strtolower(trim($dosage));
        // Match patterns like "1/2 mg", "½ mg", "0.5 mg", "1 mg", "1 milligram", "2 grams", etc.
        // Also support mixed numbers like "1 1/2 mg"
        $unitPatterns = [
            'mg' => ['mg', 'milligram', 'milligrams'],
            'g' => ['g', 'gram', 'grams'],
            'mcg' => ['mcg', 'microgram', 'micrograms', 'μg', 'µg'],
        ];
        // Build a regex for all units
        $allUnits = [];
        foreach ($unitPatterns as $uarr) {
            $allUnits = array_merge($allUnits, $uarr);
        }
        $unitRegex = implode('|', array_map('preg_quote', $allUnits));
        // Regex: [number|fraction|vulgar fraction] [unit]
        if (preg_match('/^\s*([0-9]+(?:\.[0-9]+)?|[0-9]+\s+[0-9]+\/[0-9]+|[0-9]+\/[0-9]+|[¼½¾⅓⅔⅕⅖⅗⅘⅙⅚⅛⅜⅝⅞])\s*(' . $unitRegex . ')\b/i', $t, $m)) {
            $numStr = trim($m[1]);
            $unitStr = strtolower($m[2]);
            $val = $this->parseFractionalNumber($numStr);
            // Normalize unit to canonical
            $unit = null;
            foreach ($unitPatterns as $canon => $variants) {
                if (in_array($unitStr, $variants, true)) {
                    $unit = $canon;
                    break;
                }
            }
            if ($unit === null) {
                return null;
            }
            return match ($unit) {
                'mg' => $val,
                'g' => $val * 1000.0,
                'mcg' => $val / 1000.0,
                default => null,
            };
        }
        return null;
    }

    /**
     * Parse a string representing a number, fraction, or vulgar fraction to float.
     * Supports "1", "0.5", "1/2", "1 1/2", "½", etc.
     */
    private function parseFractionalNumber(string $str): float
    {
        $str = trim($str);
        // Map vulgar fractions to float
        $vulgarMap = [
            '¼' => 0.25, '½' => 0.5, '¾' => 0.75,
            '⅓' => 1/3, '⅔' => 2/3,
            '⅕' => 0.2, '⅖' => 0.4, '⅗' => 0.6, '⅘' => 0.8,
            '⅙' => 1/6, '⅚' => 5/6,
            '⅛' => 0.125, '⅜' => 0.375, '⅝' => 0.625, '⅞' => 0.875,
        ];
        if (isset($vulgarMap[$str])) {
            return $vulgarMap[$str];
        }
        // Mixed number: "1 1/2"
        if (preg_match('/^([0-9]+)\s+([0-9]+)\/([0-9]+)$/', $str, $m)) {
            return (float)$m[1] + ((float)$m[2] / (float)$m[3]);
        }
        // Simple fraction: "1/2"
        if (preg_match('/^([0-9]+)\/([0-9]+)$/', $str, $m)) {
            return (float)$m[1] / (float)$m[2];
        }
        // Decimal or integer
        return (float)$str;
    }
}
