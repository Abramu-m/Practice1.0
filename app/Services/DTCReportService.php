<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DTCReportService extends BaseReportService
{
    /**
     * Build DTC (Diarrhea Treatment Center) monthly report
     */
    public function buildReport()
    {
        $baseData = $this->getBaseReportData();

        // Get diarrhea diagnoses (ICD codes for diarrhea: A00-A09)
        $diarrheaIcdCodes = DB::table('icd_10')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('code', 'LIKE', 'A0%')
                    ->orWhere('code', 'LIKE', 'A1%')
                    ->orWhere('code', 'LIKE', 'A2%')
                    ->orWhere('code', 'LIKE', 'A3%')
                    ->orWhere('code', 'LIKE', 'A4%')
                    ->orWhere('code', 'LIKE', 'A5%')
                    ->orWhere('code', 'LIKE', 'A6%')
                    ->orWhere('code', 'LIKE', 'A7%')
                    ->orWhere('code', 'LIKE', 'A8%')
                    ->orWhere('code', 'LIKE', 'A9%');
            })
            ->pluck('code')
            ->toArray();

        // Get consultations with diarrhea diagnosis in date range
        $diarrheaCases = DB::table('icd_diagnoses as id')
            ->join('consultations as c', 'c.id', '=', 'id.consultation_id')
            ->join('patient_visits as pv', 'pv.id', '=', 'c.visit_id')
            ->join('patients as p', 'p.id', '=', 'pv.patient')
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereIn('id.icd_code', $diarrheaIcdCodes)
            ->select('pv.id', 'p.gender', 'p.date_of_birth', 'pv.visit_date')
            ->distinct()
            ->get();

        // Aggregate by age and gender
        $aggregation = [];
        foreach ($diarrheaCases as $record) {
            $gender = strtolower($record->gender);
            $genderKey = in_array($gender, ['male', 'm', '1']) ? 'male' : 'female';

            $ageGroup = \App\Models\AgeGroup::findByDateOfBirth($record->date_of_birth);
            if (!$ageGroup) {
                continue;
            }

            $groupLabel = $ageGroup->label;

            if (!isset($aggregation[$groupLabel])) {
                $aggregation[$groupLabel] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                ];
            }

            $aggregation[$groupLabel][$genderKey]++;
            $aggregation[$groupLabel]['total']++;
        }

        return array_merge($baseData, [
            'report_type' => 'dtc_monthly',
            'title' => 'DTC (Diarrhea Treatment Center) Monthly Report',
            'total_cases' => count($diarrheaCases),
            'by_age_gender' => $this->buildAgeGenderMatrix($aggregation),
            'totals' => $this->aggregationHelper::calculateTotals($aggregation),
            'month' => $this->startDate->format('F'),
            'year' => $this->startDate->year,
        ]);
    }
}
