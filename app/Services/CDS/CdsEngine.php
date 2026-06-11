<?php

namespace App\Services\CDS;

use Illuminate\Support\Facades\Log;

class CdsEngine
{
    public function __construct(
        private CdsAlertService $alerts,
        private CdsRuleCache $ruleCache
    ) {
    }

    /**
     * Run CDS checks for a given trigger with database-driven rules.
     * @param string $trigger e.g., 'medication_prescribe'
     * @param array $context keyed array containing patient_id, visit_id, order, etc.
     */
    public function check(string $trigger, array $context): void
    {
        // Simple router for rules; expand as needed.
        if ($trigger === 'medication_prescribe') {
            $this->runMedicationRules($context);
        } elseif ($trigger === 'lab_result') {
            $this->runLabRules($context);
        }
    }

    private function runMedicationRules(array $context): void
    {
        $medicationRuleTypes = ['allergy', 'duplicate', 'dose_range', 'renal_dose', 'formulary', 'interactions'];

        Log::channel(config('cds.log_channel', 'single'))
            ->info('CDS: Running medication rules from database', [
                'patient_id' => $context['patient_id'] ?? null,
                'visit_id' => $context['visit_id'] ?? null,
                'medication_id' => $context['order']['medication_id'] ?? null,
                'rule_types' => $medicationRuleTypes
            ]);

        $context['subject_type'] = 'prescription';
        $context['subject_id'] = $context['order']['prescription_id'] ?? null;

        $this->runRuleTypes($medicationRuleTypes, $context);
    }

    private function runLabRules(array $context): void
    {
        $labRuleTypes = ['lab_critical'];

        Log::channel(config('cds.log_channel', 'single'))
            ->info('CDS: Running lab rules from database', [
                'patient_id' => $context['patient_id'] ?? null,
                'visit_id' => $context['visit_id'] ?? null,
                'medical_service_id' => $context['investigation']['medical_service_id'] ?? null,
                'rule_types' => $labRuleTypes
            ]);

        $this->runRuleTypes($labRuleTypes, $context);
    }

    private function runRuleTypes(array $ruleTypes, array $context): void
    {
        foreach ($ruleTypes as $ruleType) {
            try {
                $rules = $this->ruleCache->getActiveRulesByType($ruleType);

                Log::channel(config('cds.log_channel', 'single'))
                    ->info("CDS: Found {$rules->count()} active rules for type: {$ruleType}");

                foreach ($rules as $rule) {
                    if ($rule->matchesContext($context)) {
                        $this->executeRule($rule, $context);
                    }
                }
            } catch (\Exception $e) {
                Log::channel(config('cds.log_channel', 'single'))
                    ->error("Failed to execute {$ruleType} rules", [
                        'error' => $e->getMessage(),
                        'context' => $context
                    ]);
            }
        }
    }

    private function executeRule($rule, array $context): void
    {
        try {
            Log::channel(config('cds.log_channel', 'single'))
                ->info('CDS: Executing rule', [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'rule_type' => $rule->ruleType->name
                ]);

            $handler = $rule->ruleType->getHandlerInstance();
            
            // Inject rule configuration into handler
            if (method_exists($handler, 'setRuleConfiguration')) {
                $handler->setRuleConfiguration($rule);
            }
            
            $result = $handler->evaluate($context);
            
            if ($result) {
                // Override severity from database if configured
                if ($rule->severity) {
                    $result['severity'] = $rule->severity;
                }

                // Add rule metadata to result
                $result['rule_id'] = $rule->id;
                $result['rule_name'] = $rule->name;
                
                // Persist and log the alert
                $payload = array_merge($result, [
                    'patient_id' => $context['patient_id'] ?? null,
                    'visit_id' => $context['visit_id'] ?? null,
                    'subject_type' => $context['subject_type'] ?? 'prescription',
                    'subject_id' => $context['subject_id'] ?? ($context['order']['prescription_id'] ?? null),
                    'status' => 'open',
                ]);
                
                $alert = $this->alerts->create($payload);
                
                Log::channel(config('cds.log_channel', 'single'))
                    ->info('CDS Alert generated', [
                        'alert_id' => $alert->id,
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                        'alert' => $result
                    ]);
            } else {
                Log::channel(config('cds.log_channel', 'single'))
                    ->info('CDS: Rule passed, no alert needed', [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name
                    ]);
            }
        } catch (\Exception $e) {
            Log::channel(config('cds.log_channel', 'single'))
                ->error("Failed to execute rule", [
                    'rule_id' => $rule->id,
                    'error' => $e->getMessage(),
                    'context' => $context
                ]);
        }
    }
}
