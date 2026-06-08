<?php

namespace App\Services\CDS\Rules;

use App\Models\Prescription;
use App\Models\Medication;

class DuplicateTherapyRule
{
    public function evaluate(array $context): ?array
    {
        $order = $context['order'] ?? [];
        $patientId = $context['patient_id'] ?? null;
        $medicationId = $order['medication_id'] ?? null;
        $medicationName = strtolower(trim($order['medication_name'] ?? ''));

        // Fallback: derive medication name from DB if not supplied
        if ($medicationName === '' && $medicationId) {
            $med = Medication::find($medicationId);
            if ($med) {
                $medicationName = strtolower(trim($med->generic_name ?: $med->brand_name ?: ''));
            }
        }

        if (!$patientId || !$medicationId) {
            return null;
        }

    // Look for an existing active prescription of the same medication
        $existing = Prescription::query()
            ->where('patient_id', $patientId)
            ->where('medication_id', $medicationId)
            ->whereIn('status', [
                Prescription::STATUS_PRESCRIBED,
                Prescription::STATUS_PREPARED,
            ])
            ->when(isset($order['prescription_id']), function ($q) use ($order) {
                // exclude the current record if present
                $q->where('id', '!=', $order['prescription_id']);
            })
            ->latest('id')
            ->first();

        if ($existing) {
            return [
                'rule_key' => 'duplicate_therapy_same_medication',
                'rule_version' => '1.0',
                'severity' => 'high',
                'message' => 'Duplicate therapy detected: same medication is already active.',
                'rationale' => sprintf(
                    'Existing prescription #%d with status %s appears active for this patient.',
                    $existing->id,
                    $existing->status
                ),
                'payload' => [
                    'existing_prescription_id' => $existing->id,
                    'medication_id' => $medicationId,
                ],
            ];
        }

        return null;
    }
}
