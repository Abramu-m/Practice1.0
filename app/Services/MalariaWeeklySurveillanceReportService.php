<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MalariaWeeklySurveillanceReportService extends BaseReportService
{
    public function buildReport(): array
    {
        $baseData = $this->getBaseReportData();

        $mrdtId = (int) SystemSetting::get('malaria_mrdt_service_id', 0);
        $bsId   = (int) SystemSetting::get('malaria_bs_service_id', 0);

        $mrdtService = $mrdtId ? DB::table('medical_services')->where('id', $mrdtId)->value('name') : null;
        $bsService   = $bsId   ? DB::table('medical_services')->where('id', $bsId)->value('name')   : null;

        $days = [];
        $cursor = $this->startDate->copy()->startOfDay();
        while ($cursor->lte($this->endDate)) {
            $days[$cursor->format('Y-m-d')] = [
                'date'            => $cursor->format('Y-m-d'),
                'day_name'        => $cursor->format('l'),
                'tested_under5'   => 0,
                'tested_5plus'    => 0,
                'positive_under5' => 0,
                'positive_5plus'  => 0,
                'clinical_under5' => 0,
                'clinical_5plus'  => 0,
            ];
            $cursor->addDay();
        }

        if ($mrdtId || $bsId) {
            $serviceIds = array_values(array_filter([$mrdtId, $bsId]));

            $rows = DB::table('investigations as inv')
                ->join('patient_visits as pv', 'pv.id', '=', 'inv.visit_id')
                ->join('patients as p', 'p.id', '=', 'pv.patient')
                ->leftJoin(DB::raw("(SELECT t1.investigation_id, t1.form_data
                        FROM investigation_template_results t1
                        INNER JOIN (SELECT investigation_id, MAX(id) as max_id
                                    FROM investigation_template_results
                                    WHERE form_status = 'final'
                                    GROUP BY investigation_id) t2
                        ON t1.id = t2.max_id) as itr"), 'itr.investigation_id', '=', 'inv.id')
                ->whereIn('inv.medical_service_id', $serviceIds)
                ->whereNull('inv.cancelled_at')
                ->whereBetween('inv.ordered_at', [$this->startDate, $this->endDate])
                ->select('inv.medical_service_id', 'inv.ordered_at', 'p.date_of_birth', 'itr.form_data')
                ->get();

            foreach ($rows as $row) {
                if (!$row->date_of_birth || !$row->ordered_at) {
                    continue;
                }

                $orderedAt = Carbon::parse($row->ordered_at);
                $dayKey = $orderedAt->format('Y-m-d');
                if (!isset($days[$dayKey])) {
                    continue;
                }

                $ageKey = Carbon::parse($row->date_of_birth)->diffInDays($orderedAt) < 1825 ? 'under5' : '5plus';
                $days[$dayKey]["tested_{$ageKey}"]++;

                $formData = [];
                if ($row->form_data) {
                    $formData = is_array($row->form_data) ? $row->form_data : (json_decode($row->form_data, true) ?? []);
                }

                $result = $row->medical_service_id == $mrdtId
                    ? MalariaVipimoReportService::classifyMrdtResult($formData)
                    : MalariaVipimoReportService::classifyBsResult($formData);

                if ($result === 'positive') {
                    $days[$dayKey]["positive_{$ageKey}"]++;
                }
            }
        }

        $totals = [
            'tested_under5'   => 0,
            'tested_5plus'    => 0,
            'positive_under5' => 0,
            'positive_5plus'  => 0,
            'clinical_under5' => 0,
            'clinical_5plus'  => 0,
        ];
        foreach ($days as $day) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $day[$key];
            }
        }

        return array_merge($baseData, [
            'week_info'    => $this->getWeekInfo(),
            'days'         => $days,
            'totals'       => $totals,
            'mrdt_service' => $mrdtService,
            'bs_service'   => $bsService,
        ]);
    }
}
