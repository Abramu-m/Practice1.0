<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsRule;

/**
 * Fires for any "Lab Critical Value" rule whose two conditions
 * (investigation.medical_service_id equals X, result.parameters.<key> <op> threshold)
 * have already matched in CdsRule::matchesContext(). This handler only formats the
 * resulting alert.
 */
class LabCriticalValueRule implements CdsRuleInterface
{
    private ?CdsRule $rule = null;

    public function setRuleConfiguration(CdsRule $rule): void
    {
        $this->rule = $rule;
    }

    public function evaluate(array $context): ?array
    {
        $message = $this->rule?->getParameter('alert_message') ?: ($this->rule->name ?? 'Critical lab value detected');
        $recommendation = $this->rule?->getParameter('recommendation') ?: '';
        $summary = $this->buildValueSummary($context);

        $rationale = trim($recommendation . ($summary ? "\n\n" . $summary : ''));

        return [
            'rule_key' => 'lab_critical_value',
            'rule_version' => '1.0',
            'severity' => $this->rule->severity ?? 'critical',
            'message' => $message,
            'rationale' => $rationale,
            'payload' => [
                'investigation_id' => data_get($context, 'investigation.id'),
                'result' => $context['result'] ?? [],
            ],
        ];
    }

    /**
     * Builds a human-readable "Parameter = value unit (threshold: < N unit)" summary
     * from the rule's parameter-threshold condition (field_name = "result.parameters.<key>").
     */
    private function buildValueSummary(array $context): string
    {
        if (!$this->rule) {
            return '';
        }

        $condition = $this->rule->conditions()
            ->where('field_name', 'like', 'result.parameters.%')
            ->orderBy('sort_order')
            ->first();

        if (!$condition) {
            return '';
        }

        $parameterKey = str_replace('result.parameters.', '', $condition->field_name);
        $actualValue = data_get($context, $condition->field_name);
        $unit = data_get($context, 'investigation.unit', '');
        $threshold = $condition->getTypedValue();

        $operatorSymbols = [
            'less_than' => '<',
            'less_equal' => '<=',
            'greater_than' => '>',
            'greater_equal' => '>=',
            'equals' => '=',
            'not_equals' => '!=',
        ];
        $symbol = $operatorSymbols[$condition->operator] ?? $condition->operator;
        $label = ucwords(str_replace('_', ' ', $parameterKey));
        $unitSuffix = $unit ? ' ' . $unit : '';

        return sprintf(
            '%s = %s%s (threshold: %s %s%s)',
            $label,
            $actualValue,
            $unitSuffix,
            $symbol,
            $threshold,
            $unitSuffix
        );
    }

    public function getRequiredContext(): array
    {
        return ['patient_id', 'visit_id', 'investigation', 'result'];
    }

    public function validateConfiguration(array $parameters): bool
    {
        return true;
    }

    public function getDefaultParameters(): array
    {
        return [
            'alert_message' => '',
            'recommendation' => '',
        ];
    }
}
