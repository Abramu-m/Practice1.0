<?php

namespace App\Services\CDS;

use App\Models\CdsAlert;

class CdsAlertService
{
    public function create(array $data): CdsAlert
    {
    return CdsAlert::create([
            'patient_id' => $data['patient_id'] ?? null,
            'visit_id' => $data['visit_id'] ?? null,
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'rule_key' => $data['rule_key'] ?? 'unknown',
            'rule_version' => $data['rule_version'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'message' => $data['message'] ?? '',
            'rationale' => $data['rationale'] ?? null,
            'payload' => $data['payload'] ?? null,
            'status' => $data['status'] ?? 'open',
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
            'resolved_at' => $data['resolved_at'] ?? null,
        ]);
    }

    public function forVisit(int $visitId)
    {
        return CdsAlert::where('visit_id', $visitId)->where('status', 'open')->orderByDesc('id')->get();
    }
}
