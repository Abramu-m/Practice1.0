<?php

namespace App\Services;

use App\Models\BloodTransfusionReportRow;
use App\Models\Investigation;
use Carbon\Carbon;

class LabBloodTransfusionReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $rows = BloodTransfusionReportRow::orderBy('sort_order')->get()->keyBy('row_key');
        $counts = $rows->map(fn($row) => $this->countByServiceIds($row->service_ids ?? []));
        $grandTotal = $counts->sum();

        return [
            'facility'     => $this->getFacilityInfo(),
            'rows'         => $rows,
            'counts'       => $counts->toArray(),
            'grand_total'  => $grandTotal,
            'generated_at' => Carbon::now(),
        ];
    }

    private function countByServiceIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return Investigation::join('patient_visits', 'investigations.visit_id', '=', 'patient_visits.id')
            ->whereIn('investigations.medical_service_id', $ids)
            ->whereBetween('patient_visits.visit_date', [$this->startDate, $this->endDate])
            ->count();
    }
}
