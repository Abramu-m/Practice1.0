<?php

namespace App\Services;

use App\Models\HematologyReportRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabHematologyReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $rows = HematologyReportRow::orderBy('sort_order')->get()->keyBy('row_key');

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

        $grandTotal    = array_sum(array_filter($totals, fn($v) => $v !== null));
        $grandTotalLow  = array_sum(array_filter($lows,   fn($v) => $v !== null));
        $grandTotalHigh = array_sum(array_filter($highs,  fn($v) => $v !== null));

        return [
            'facility'        => $this->getFacilityInfo(),
            'rows'            => $rows,
            'totals'          => $totals,
            'lows'            => $lows,
            'highs'           => $highs,
            'grand_total'     => $grandTotal,
            'grand_total_low'  => $grandTotalLow,
            'grand_total_high' => $grandTotalHigh,
            'generated_at'    => Carbon::now(),
        ];
    }

    private function countTotal(HematologyReportRow $row): int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids)) return 0;

        if ($row->positive_results_only) {
            return $this->countPositiveResults($row, $ids);
        }

        return DB::table('investigations')
            ->join('patient_visits', 'investigations.visit_id', '=', 'patient_visits.id')
            ->whereIn('investigations.medical_service_id', $ids)
            ->whereBetween('patient_visits.visit_date', [$this->startDate, $this->endDate])
            ->count();
    }

    private function countPositiveResults(HematologyReportRow $row, array $ids): int
    {
        $query = DB::table('investigation_template_results as itr')
            ->join('investigations as i', 'itr.investigation_id', '=', 'i.id')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereRaw(
                "JSON_SEARCH(JSON_EXTRACT(itr.form_data, '$.parameters'), 'one', 'Positive', NULL, '\$[*].value') IS NOT NULL"
            );

        if ($row->required_template_name) {
            $query->where('itr.template_name', $row->required_template_name);
        }

        return $query->distinct()->count('i.id');
    }

    private function countWithStatus(HematologyReportRow $row, string $status): int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids)) return 0;

        $query = DB::table('investigation_template_results as itr')
            ->join('investigations as i', 'itr.investigation_id', '=', 'i.id')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate]);

        if ($row->required_template_name) {
            $query->where('itr.template_name', $row->required_template_name);
        }

        if ($row->fbp_param_name) {
            // Match specific parameter name AND status in the JSON parameters array
            $query->whereRaw(
                "JSON_CONTAINS(JSON_EXTRACT(itr.form_data, '$.parameters'), JSON_OBJECT('parameter_name', ?, 'status', ?))",
                [$row->fbp_param_name, $status]
            );
        } else {
            // Any parameter in the result has the given status
            $query->whereRaw(
                "JSON_SEARCH(JSON_EXTRACT(itr.form_data, '$.parameters'), 'one', ?, NULL, '\$[*].status') IS NOT NULL",
                [$status]
            );
        }

        return $query->distinct()->count('i.id');
    }
}
