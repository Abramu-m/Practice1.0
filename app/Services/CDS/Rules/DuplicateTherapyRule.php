<?php

namespace App\Services\CDS\Rules;

use App\Models\Prescription;
use App\Models\Medication;
use Illuminate\Support\Facades\DB;

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

        // Optional: duplicate by ATC class if mapping tables exist
        try {
            if ($medicationName !== '' && DB::getSchemaBuilder()->hasTable('drug_atc_map') && DB::getSchemaBuilder()->hasTable('atc_codes') && DB::getSchemaBuilder()->hasTable('medications')) {
                // Find ATC for the ordered medication by name
                $map = DB::table('drug_atc_map')
                    ->join('atc_codes', 'drug_atc_map.atc_code_id', '=', 'atc_codes.id')
                    ->whereRaw('LOWER(drug_atc_map.medication_name) = ?', [$medicationName])
                    ->select('atc_codes.id as atc_id', 'atc_codes.code as atc_code')
                    ->first();
                if ($map) {
                    // Get all medication name aliases under this ATC
                    $aliases = DB::table('drug_atc_map')
                        ->where('atc_code_id', $map->atc_id)
                        ->pluck('medication_name')
                        ->map(function ($n) { return strtolower(trim($n)); })
                        ->all();

                    if (!empty($aliases)) {
                        // Look for existing active prescriptions for meds whose generic or brand match an alias
                        $existingAtc = Prescription::query()
                            ->join('medications', 'prescriptions.medication_id', '=', 'medications.id')
                            ->where('prescriptions.patient_id', $patientId)
                            ->whereIn('prescriptions.status', [Prescription::STATUS_PRESCRIBED, Prescription::STATUS_PREPARED])
                            ->when(isset($order['prescription_id']), function ($q) use ($order) {
                                $q->where('prescriptions.id', '!=', $order['prescription_id']);
                            })
                            ->where(function ($q) use ($aliases) {
                                $q->whereIn(DB::raw('LOWER(medications.generic_name)'), $aliases)
                                  ->orWhereIn(DB::raw('LOWER(medications.brand_name)'), $aliases);
                            })
                            ->select('prescriptions.*')
                            ->latest('prescriptions.id')
                            ->first();

                        if ($existingAtc) {
                            return [
                                'rule_key' => 'duplicate_therapy_atc_class',
                                'rule_version' => '1.0',
                                'severity' => 'high',
                                'message' => 'Duplicate therapy detected: medication in same ATC class is already active.',
                                'rationale' => sprintf(
                                    'Existing prescription #%d in ATC class %s appears active for this patient.',
                                    $existingAtc->id,
                                    $map->atc_code
                                ),
                                'payload' => [
                                    'existing_prescription_id' => $existingAtc->id,
                                    'atc_code' => $map->atc_code,
                                    'medication_id' => $medicationId,
                                ],
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Ignore mapping errors; ATC enhancement is optional
        }

        return null;
    }
}
