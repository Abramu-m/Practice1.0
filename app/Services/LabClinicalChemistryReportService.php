<?php

namespace App\Services;

use App\Models\ClinicalChemistryReportRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabClinicalChemistryReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $rows = ClinicalChemistryReportRow::orderBy('sort_order')->get()->keyBy('row_key');

        $totals = [];
        $lows   = [];
        $highs  = [];

        foreach ($rows as $key => $row) {
            if ($row->is_section_header) {
                $totals[$key] = null;
                $lows[$key]   = null;
                $highs[$key]  = null;
                continue;
            }

            $totals[$key] = $this->countTotal($row);

            if ($row->track_low_high) {
                $lows[$key]  = $this->countWithStatus($row, 'low');
                $highs[$key] = $this->countWithStatus($row, 'high');
            } else {
                $lows[$key]  = null;
                $highs[$key] = null;
            }
        }

        $grandTotal = array_sum(array_filter($totals, fn($v) => $v !== null));

        return [
            'facility'     => $this->getFacilityInfo(),
            'rows'         => $rows,
            'totals'       => $totals,
            'lows'         => $lows,
            'highs'        => $highs,
            'grand_total'  => $grandTotal,
            'generated_at' => Carbon::now(),
        ];
    }

    private function countTotal(ClinicalChemistryReportRow $row): int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids)) return 0;

        return DB::table('investigations')
            ->join('patient_visits', 'investigations.visit_id', '=', 'patient_visits.id')
            ->whereIn('investigations.medical_service_id', $ids)
            ->whereBetween('patient_visits.visit_date', [$this->startDate, $this->endDate])
            ->count();
    }

    private function countWithStatus(ClinicalChemistryReportRow $row, string $status): int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids)) return 0;

        if ($row->abnormal_as_high && $status === 'low') {
            return 0;
        }

        $statuses = [$status];
        if ($row->abnormal_as_high && $status === 'high') {
            $statuses[] = 'abnormal';
        }

        $query = DB::table('investigation_template_results as itr')
            ->join('investigations as i', 'itr.investigation_id', '=', 'i.id')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate]);

        if ($row->required_template_name) {
            $query->where('itr.template_name', $row->required_template_name);
        }

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
