<?php

namespace App\Services;

use App\Models\SerologyReportRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabSerologyReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $rows = SerologyReportRow::orderBy('sort_order')->get()->keyBy('row_key');

        $totals    = [];
        $positives = [];

        foreach ($rows as $key => $row) {
            $totals[$key]    = $this->countTotal($row);
            $positives[$key] = $this->countPositive($row);
        }

        $grandTotal = array_sum(array_filter($totals, fn ($v) => $v !== null));

        return [
            'facility'     => $this->getFacilityInfo(),
            'rows'         => $rows,
            'totals'       => $totals,
            'positives'    => $positives,
            'grand_total'  => $grandTotal,
            'generated_at' => Carbon::now(),
        ];
    }

    private function countTotal(SerologyReportRow $row): int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids)) return 0;

        $query = DB::table('investigations as i')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate]);

        if ($row->cd4_filter) {
            $query->join('investigation_template_results as itr', 'itr.investigation_id', '=', 'i.id');
            $valueExpr = "CAST(JSON_UNQUOTE(JSON_EXTRACT(itr.form_data, '$.parameters[0].value')) AS DECIMAL(10,2))";
            $query->whereRaw($row->cd4_filter === 'gt_200' ? "{$valueExpr} > 200" : "{$valueExpr} <= 200");
        }

        return $query->distinct()->count('i.id');
    }

    private function countPositive(SerologyReportRow $row): ?int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids) || empty($row->positive_statuses)) return null;

        $query = DB::table('investigation_template_results as itr')
            ->join('investigations as i', 'itr.investigation_id', '=', 'i.id')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate]);

        if ($row->required_template_name) {
            $query->where('itr.template_name', $row->required_template_name);
        }

        $statuses = $row->positive_statuses;
        $query->where(function ($q) use ($statuses) {
            foreach ($statuses as $i => $st) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->{$method . 'Raw'}(
                    "JSON_SEARCH(JSON_EXTRACT(itr.form_data, '$.parameters'), 'one', ?, NULL, '\$[*].status') IS NOT NULL",
                    [$st]
                );
            }
        });

        return $query->distinct()->count('i.id');
    }
}
