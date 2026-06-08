<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class STDSTIReportService extends BaseReportService
{
    /**
     * Build STD/STI monthly report
     */
    public function buildReport()
    {
        $baseData = $this->getBaseReportData();

        // ICD codes for STIs (A50-A64)
        $stiIcdCodes = DB::table('icd_10')
            ->where('is_active', true)
            ->where(function ($query) {
                for ($i = 50; $i <= 64; $i++) {
                    $query->orWhere('code', 'LIKE', 'A' . $i . '%');
                }
            })
            ->pluck('code')
            ->toArray();

        // Get STI diagnoses
        $stiDiagnoses = DB::table('icd_diagnoses as id')
            ->join('consultations as c', 'c.id', '=', 'id.consultation_id')
            ->join('patient_visits as pv', 'pv.id', '=', 'c.visit_id')
            ->join('patients as p', 'p.id', '=', 'pv.patient')
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereIn('id.icd_code', $stiIcdCodes)
            ->select('pv.id', 'id.icd_code', 'id.description', 'p.gender', 'p.date_of_birth', 'pv.visit_date')
            ->distinct()
            ->get();

        // Group by specific STI type
        $stiByType = [];
        $totalAggregation = [];

        foreach ($stiDiagnoses as $record) {
            $gender = strtolower($record->gender);
            $genderKey = in_array($gender, ['male', 'm', '1']) ? 'male' : 'female';

            $ageGroup = \App\Models\AgeGroup::findByDateOfBirth($record->date_of_birth);
            if (!$ageGroup) {
                continue;
            }

            $groupLabel = $ageGroup->label;
            $stiType = $record->description ?? $record->icd_code;

            // Add to type-specific aggregation
            if (!isset($stiByType[$stiType])) {
                $stiByType[$stiType] = [];
            }
            if (!isset($stiByType[$stiType][$groupLabel])) {
                $stiByType[$stiType][$groupLabel] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                ];
            }
            $stiByType[$stiType][$groupLabel][$genderKey]++;
            $stiByType[$stiType][$groupLabel]['total']++;

            // Add to total aggregation
            if (!isset($totalAggregation[$groupLabel])) {
                $totalAggregation[$groupLabel] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                ];
            }
            $totalAggregation[$groupLabel][$genderKey]++;
            $totalAggregation[$groupLabel]['total']++;
        }

        $overallTotals = $this->aggregationHelper::calculateTotals($totalAggregation);

        return array_merge($baseData, [
            'report_type' => 'std_sti_monthly',
            'title' => 'STD/STI Monthly Report',
            'total_cases' => count($stiDiagnoses),
            'by_gender' => [
                'male' => $overallTotals['male'] ?? 0,
                'female' => $overallTotals['female'] ?? 0,
            ],
            'by_sti_type' => array_map(function ($stiName, $data) {
                return [
                    'sti_name' => $stiName,
                    'cases' => $this->buildAgeGenderMatrix($data),
                    'totals' => $this->aggregationHelper::calculateTotals($data),
                ];
            }, array_keys($stiByType), $stiByType),
            'overall' => [
                'by_age_gender' => $this->buildAgeGenderMatrix($totalAggregation),
                'totals' => $overallTotals,
            ],
        ]);
    }
}
