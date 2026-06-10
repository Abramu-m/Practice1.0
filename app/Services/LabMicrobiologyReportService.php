<?php

namespace App\Services;

use App\Models\MicrobiologyReportRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabMicrobiologyReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $rows = MicrobiologyReportRow::orderBy('sort_order')->get()->keyBy('row_key');

        $totals    = [];
        $positives = [];

        foreach ($rows as $key => $row) {
            $totals[$key]    = $row->show_total ? $this->countTotal($row) : null;
            $positives[$key] = $row->show_positive ? $this->countPositive($row) : null;
        }

        $grandTotal = 0;
        foreach ($rows as $key => $row) {
            if ($row->include_in_grand_total && $totals[$key] !== null) {
                $grandTotal += $totals[$key];
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

    private function countTotal(MicrobiologyReportRow $row): int
    {
        $ids = $row->service_ids ?? [];
        if (empty($ids)) return 0;

        return DB::table('investigations as i')
            ->join('patient_visits as pv', 'i.visit_id', '=', 'pv.id')
            ->whereIn('i.medical_service_id', $ids)
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->distinct()
            ->count('i.id');
    }

    private function countPositive(MicrobiologyReportRow $row): int
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

        if ($row->json_path) {
            $paths  = explode(',', $row->json_path);
            $values = $row->json_path_values ?? [];
            if (empty($values)) return 0;

            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $query->where(function ($q) use ($paths, $values, $placeholders) {
                foreach ($paths as $i => $path) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method . 'Raw'}(
                        "JSON_UNQUOTE(JSON_EXTRACT(itr.form_data, ?)) IN ({$placeholders})",
                        array_merge([$path], $values)
                    );
                }
            });
        } elseif ($row->param_name) {
            $field  = $row->match_field ?: 'status';
            $values = $row->match_values ?? [];
            if (empty($values)) return 0;

            $query->where(function ($q) use ($row, $field, $values) {
                foreach ($values as $i => $val) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method . 'Raw'}(
                        "JSON_CONTAINS(JSON_EXTRACT(itr.form_data, '$.parameters'), JSON_OBJECT('parameter_name', ?, ?, ?))",
                        [$row->param_name, $field, $val]
                    );
                }
            });
        } else {
            $values = $row->match_values ?? [];
            if (empty($values)) return 0;

            $query->where(function ($q) use ($values) {
                foreach ($values as $i => $val) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method . 'Raw'}(
                        "JSON_SEARCH(JSON_EXTRACT(itr.form_data, '$.parameters'), 'one', ?, NULL, '\$[*].status') IS NOT NULL",
                        [$val]
                    );
                }
            });
        }

        return $query->distinct()->count('i.id');
    }
}
