<?php

namespace App\Services;

use App\Models\IdsrDiagnosis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IdSRReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $diagnoses = IdsrDiagnosis::orderBy('id')->pluck('name', 'id')->all();

        $empty = [
            'u5_m' => 0, 'u5_f' => 0, 'u5_t' => 0,
            '5p_m' => 0, '5p_f' => 0, '5p_t' => 0,
            'tot_m' => 0, 'tot_f' => 0, 'tot_t' => 0,
        ];

        $weeklyCases = $this->queryCases($this->startDate, $this->endDate);
        $ytdCases    = $this->queryCases($this->startDate->copy()->startOfYear(), $this->endDate);

        $diseases = [];
        foreach ($diagnoses as $id => $name) {
            $diseases[$id] = [
                'name'              => $name,
                'weekly_cases'      => $weeklyCases[$id] ?? $empty,
                'weekly_deaths'     => $empty,
                'cumulative_cases'  => $this->asThreeCols($ytdCases[$id] ?? $empty),
                'cumulative_deaths' => ['m' => null, 'f' => null, 't' => null],
            ];
        }

        return array_merge($this->getBaseReportData(), [
            'week_info' => $this->getWeekInfo(),
            'diseases'  => $diseases,
        ]);
    }

    private function queryCases(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('idsr_icd_mapping as m')
            ->join('icd_diagnoses as d',   'd.icd_code',  '=', 'm.icd_code')
            ->join('consultations as c',   'c.id',        '=', 'd.consultation_id')
            ->join('patient_visits as pv', 'pv.id',       '=', 'c.visit_id')
            ->join('patients as p',        'p.id',        '=', 'pv.patient')
            ->whereBetween('pv.visit_date', [$start, $end])
            ->whereNotNull('p.date_of_birth')
            ->selectRaw("
                m.idsr_diagnosis_id,
                IF(DATEDIFF(pv.visit_date, p.date_of_birth) < 1825, 'u5', '5p') AS age_bucket,
                p.gender,
                COUNT(DISTINCT pv.id) AS cnt
            ")
            ->groupBy('m.idsr_diagnosis_id', 'age_bucket', 'p.gender')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $id  = $row->idsr_diagnosis_id;
            $ab  = $row->age_bucket;
            $g   = strtolower($row->gender) === 'male' ? 'm' : 'f';
            $cnt = (int) $row->cnt;

            if (!isset($result[$id])) {
                $result[$id] = [
                    'u5_m' => 0, 'u5_f' => 0, 'u5_t' => 0,
                    '5p_m' => 0, '5p_f' => 0, '5p_t' => 0,
                    'tot_m' => 0, 'tot_f' => 0, 'tot_t' => 0,
                ];
            }

            $result[$id]["{$ab}_{$g}"] += $cnt;
            $result[$id]["{$ab}_t"]    += $cnt;
            $result[$id]["tot_{$g}"]   += $cnt;
            $result[$id]['tot_t']      += $cnt;
        }

        return $result;
    }

    private function asThreeCols(array $data): array
    {
        return ['m' => $data['tot_m'], 'f' => $data['tot_f'], 't' => $data['tot_t']];
    }
}
