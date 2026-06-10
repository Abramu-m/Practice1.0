<?php

namespace App\Services;

use App\Models\ParasitologyReportRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabParasitologyReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $rows = ParasitologyReportRow::orderBy('sort_order')->get()->keyBy('row_key');

        $totals = [];
        $positives = [];

        foreach ($rows as $key => $row) {
            if ($row->is_section_header) {
                $totals[$key] = null;
                $positives[$key] = null;
                continue;
            }

            $totals[$key]    = $this->countTotal($row);
            $positives[$key] = $this->countPositive($row);
        }

        $grandTotal = 0;
        foreach ($rows as $key => $row) {
            if (!$row->is_section_header && $row->shows_total) {
                $grandTotal += $totals[$key] ?? 0;
            }
        }

        return [
            'facility'     => $this->getFacilityInfo(),
            'rows'         => $rows,
            'totals'       => $totals,
            'positives'    => $positives,
            'grand_total'  => $grandTotal,
            'generated_at' => Carbon::now(),
        ];
    }

    private function countTotal(ParasitologyReportRow $row): ?int
    {
        if (!$row->shows_total) {
            return null;
        }

        $ids = $row->service_ids ?? [];
        if (empty($ids)) {
            return 0;
        }

        return DB::table('investigations')
            ->join('patient_visits', 'investigations.visit_id', '=', 'patient_visits.id')
            ->whereIn('investigations.medical_service_id', $ids)
            ->whereBetween('patient_visits.visit_date', [$this->startDate, $this->endDate])
            ->distinct()
            ->count('investigations.id');
    }

    private function countPositive(ParasitologyReportRow $row): ?int
    {
        if (!$row->shows_positive) {
            return null;
        }

        $ids = $row->service_ids ?? [];
        if (empty($ids) || empty($row->positive_statuses)) {
            return null;
        }

        $query = DB::table('investigation_template_results as itr')
            ->join('investigations as i', 'itr.investigation_id', '=', 'i.id')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate]);

        if ($row->required_template_name) {
            $query->where('itr.template_name', $row->required_template_name);
        }

        $statuses = $row->positive_statuses;

        $query->where(function ($q) use ($row, $statuses) {
            foreach ($statuses as $i => $st) {
                $method = $i === 0 ? 'where' : 'orWhere';
                if ($row->param_name) {
                    $q->{$method . 'Raw'}(
                        "JSON_CONTAINS(JSON_EXTRACT(itr.form_data, '$.parameters'), JSON_OBJECT('parameter_name', ?, 'status', ?))",
                        [$row->param_name, $st]
                    );
                } else {
                    $q->{$method . 'Raw'}(
                        "JSON_SEARCH(JSON_EXTRACT(itr.form_data, '$.parameters'), 'one', ?, NULL, '\$[*].status') IS NOT NULL",
                        [$st]
                    );
                }
            }
        });

        return $query->distinct()->count('i.id');
    }
}
