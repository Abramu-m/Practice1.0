<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsRule;

class DoseRangeRule implements CdsRuleInterface
{
    private ?CdsRule $ruleConfig = null;

    public function setRuleConfiguration(CdsRule $rule): void
    {
        $this->ruleConfig = $rule;
    }

    public function getRequiredContext(): array
    {
        return ["patient_id", "order.medication_name", "order.dosage"];
    }

    public function validateConfiguration(array $parameters): bool
    {
        return true;
    }

    public function getDefaultParameters(): array
    {
        return ["fallback_to_config" => true];
    }

    public function evaluate(array $context): ?array
    {
        if (!config("cds.features.medication.dose_range", true)) {
            return null;
        }

        $order = $context["order"] ?? [];
        $medName = strtolower(trim($order["medication_name"] ?? ""));
        $dosageText = (string)($order["dosage"] ?? "");
        
        if ($medName === "" || $dosageText === "") {
            return null;
        }

        // Simple config-based check for now
        $policies = (array) config("cds.dose_policies", []);
        $policyKey = null;
        foreach ($policies as $key => $_) {
            if (str_contains($medName, strtolower($key))) {
                $policyKey = $key;
                break;
            }
        }
        if (!$policyKey) {
            return null;
        }
        $policy = $policies[$policyKey];

        $mgPerDose = $this->parseDoseToMg($dosageText);
        if ($mgPerDose === null) {
            return null;
        }

        if (isset($policy["max_single_mg"]) && is_numeric($policy["max_single_mg"])) {
            $maxSingle = (float)$policy["max_single_mg"];
            if ($mgPerDose > $maxSingle) {
                return [
                    "rule_key" => "dose_range",
                    "rule_version" => "2.0",
                    "severity" => "warning",
                    "message" => ucfirst($policyKey) . " dose exceeds maximum per dose",
                    "rationale" => "Ordered dose exceeds safe limits",
                    "payload" => [
                        "medication" => $medName,
                        "dose_mg" => $mgPerDose,
                        "max_single_mg" => $maxSingle,
                    ],
                ];
            }
        }

        return null;
    }

    private function parseDoseToMg(string $dosage): ?float
    {
        $t = strtolower(trim($dosage));
        
        if (preg_match("/([0-9]+(?:\.[0-9]+)?)\s*mg/i", $t, $m)) {
            return (float)$m[1];
        }
        
        if (preg_match("/([0-9]+(?:\.[0-9]+)?)\s*g(?:ram)?/i", $t, $m)) {
            return (float)$m[1] * 1000.0;
        }
        
        return null;
    }
}
