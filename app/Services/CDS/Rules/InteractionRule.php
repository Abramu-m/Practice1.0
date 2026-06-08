<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsDosageLimit;
use App\Models\CdsRule;
use App\Models\Medication;
use App\Models\Prescription;
use Illuminate\Support\Collection;

class InteractionRule implements CdsRuleInterface
{
    private ?CdsRule $ruleConfig = null;

    public function setRuleConfiguration(CdsRule $rule): void
    {
        $this->ruleConfig = $rule;
    }

    public function getRequiredContext(): array
    {
        return ['patient_id', 'order.medication_id'];
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
        if (!config('cds.features.medication.interactions', true)) {
            return null;
        }

        $order = $context['order'] ?? [];
        $patientId = $context['patient_id'] ?? null;
        $medicationId = (int) ($order['medication_id'] ?? 0);

        if (!$patientId || $medicationId <= 0) {
            return null;
        }

        $prescribedMedication = Medication::with('drugClasses')->find($medicationId);
        if (!$prescribedMedication) {
            return null;
        }

        $existingPrescriptions = Prescription::query()
            ->with(['medication.drugClasses'])
            ->where('patient_id', $patientId)
            ->whereIn('status', [
                Prescription::STATUS_PRESCRIBED,
                Prescription::STATUS_PREPARED,
                Prescription::STATUS_DISPENSED,
            ])
            ->when(isset($order['prescription_id']), function ($q) use ($order) {
                $q->where('id', '!=', $order['prescription_id']);
            })
            ->get();

        $existingMedications = $existingPrescriptions
            ->pluck('medication')
            ->filter()
            ->unique('id')
            ->values();

        if ($existingMedications->isEmpty()) {
            return null;
        }

        $allMedicationIds = $existingMedications->pluck('id')->push($prescribedMedication->id)->unique()->values();
        $limitsByMedicationId = CdsDosageLimit::query()
            ->whereIn('medication_id', $allMedicationIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('medication_id');

        // Direction A: new medication's configured interactions against existing meds.
        $newMedicationLimit = $limitsByMedicationId->get($prescribedMedication->id);
        if ($newMedicationLimit && is_array($newMedicationLimit->interactions)) {
            foreach ($newMedicationLimit->interactions as $interaction) {
                $target = $this->parseTarget($interaction);
                if (!$target) {
                    continue;
                }

                $matchedMedication = $existingMedications->first(fn ($m) => $this->matchesTarget($target, $m));
                if ($matchedMedication) {
                    return $this->buildAlert(
                        $prescribedMedication,
                        $matchedMedication,
                        $interaction,
                        $target,
                        'configured_on_prescribed_medication'
                    );
                }
            }
        }

        // Direction B: existing medications' interactions against newly prescribed med.
        foreach ($existingMedications as $existingMedication) {
            $existingLimit = $limitsByMedicationId->get($existingMedication->id);
            if (!$existingLimit || !is_array($existingLimit->interactions)) {
                continue;
            }

            foreach ($existingLimit->interactions as $interaction) {
                $target = $this->parseTarget($interaction);
                if (!$target) {
                    continue;
                }

                if ($this->matchesTarget($target, $prescribedMedication)) {
                    return $this->buildAlert(
                        $prescribedMedication,
                        $existingMedication,
                        $interaction,
                        $target,
                        'configured_on_existing_medication'
                    );
                }
            }
        }

        return null;
    }

    private function parseTarget(array $interaction): ?array
    {
        $rawType = strtolower((string) ($interaction['type'] ?? ''));
        $rawId = (string) ($interaction['id'] ?? '');

        if ($rawId === '') {
            return null;
        }

        if (str_starts_with($rawId, 'class:')) {
            $id = (int) substr($rawId, 6);
            return $id > 0 ? ['type' => 'class', 'id' => $id] : null;
        }

        if (str_starts_with($rawId, 'med:')) {
            $id = (int) substr($rawId, 4);
            return $id > 0 ? ['type' => 'medication', 'id' => $id] : null;
        }

        $id = (int) $rawId;
        if ($id <= 0) {
            return null;
        }

        $type = in_array($rawType, ['class', 'drug_class'], true)
            ? 'class'
            : 'medication';

        return ['type' => $type, 'id' => $id];
    }

    private function matchesTarget(array $target, Medication $medication): bool
    {
        if ($target['type'] === 'medication') {
            return (int) $medication->id === (int) $target['id'];
        }

        if ($target['type'] === 'class') {
            return $medication->drugClasses->contains(fn ($dc) => (int) $dc->id === (int) $target['id']);
        }

        return false;
    }

    private function buildAlert(
        Medication $prescribedMedication,
        Medication $matchedMedication,
        array $interaction,
        array $target,
        string $matchDirection
    ): array {
        $configuredSeverity = strtolower((string) ($interaction['severity'] ?? 'caution'));
        $severity = match ($configuredSeverity) {
            'contraindicated' => 'critical',
            'avoid' => 'high',
            'monitor' => 'info',
            default => 'warning',
        };

        $prescribedName = $prescribedMedication->generic_name ?: $prescribedMedication->brand_name ?: ('Medication #' . $prescribedMedication->id);
        $matchedName = $matchedMedication->generic_name ?: $matchedMedication->brand_name ?: ('Medication #' . $matchedMedication->id);
        $label = (string) ($interaction['label'] ?? '');
        $targetText = $label !== '' ? $label : ($target['type'] === 'class' ? ('drug class #' . $target['id']) : ('medication #' . $target['id']));

        return [
            'rule_key' => 'drug_interaction_detected',
            'rule_version' => '1.0',
            'severity' => $severity,
            'message' => sprintf('Potential drug interaction: %s with %s.', $prescribedName, $matchedName),
            'rationale' => sprintf(
                'Interaction rule matched target %s (configured severity: %s). New order: %s. Existing active medication: %s.',
                $targetText,
                $configuredSeverity,
                $prescribedName,
                $matchedName
            ),
            'payload' => [
                'interaction_severity' => $configuredSeverity,
                'match_direction' => $matchDirection,
                'target_type' => $target['type'],
                'target_id' => $target['id'],
                'target_label' => $label,
                'prescribed_medication_id' => $prescribedMedication->id,
                'matched_medication_id' => $matchedMedication->id,
            ],
        ];
    }
}
