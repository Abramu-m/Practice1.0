<?php

namespace App\Services;

use App\Models\IdSRCategory;
use Illuminate\Support\Facades\DB;

class IdSRReportService extends BaseReportService
{
    /**
     * Build IDSR weekly report
     */
    public function buildReport()
    {
        $baseData = $this->getBaseReportData();

        // Get active IDSR categories
        $categories = IdSRCategory::active()->get();

        $diseaseData = [];

        foreach ($categories as $category) {
            $icdCodes = $category->getIcdCodesArray();

            // Get consultations with IDSR category diagnoses
            $consultations = DB::table('icd_diagnoses as id')
                ->join('consultations as c', 'c.id', '=', 'id.consultation_id')
                ->join('patient_visits as pv', 'pv.id', '=', 'c.visit_id')
                ->join('patients as p', 'p.id', '=', 'pv.patient')
                ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
                ->where(function ($query) use ($icdCodes) {
                    if (!empty($icdCodes)) {
                        $query->whereIn('id.icd_code', $icdCodes);
                    } else {
                        // Fallback: search by MTUHA category name
                        $query->whereRaw('1 = 0');
                    }
                })
                ->select('pv.id', 'p.gender', 'p.date_of_birth', 'pv.visit_date')
                ->distinct()
                ->get();

            // Aggregate by age and gender
            $aggregation = [];
            foreach ($consultations as $record) {
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

            $diseaseData[$category->name] = [
                'total_cases' => count($consultations),
                'by_age_gender' => $this->buildAgeGenderMatrix($aggregation),
                'totals' => $this->aggregationHelper::calculateTotals($aggregation),
            ];
        }

        return array_merge($baseData, [
            'report_type' => 'idsr_weekly',
            'title' => 'IDSR Weekly Disease Report',
            'week_info' => $this->getWeekInfo(),
            'diseases' => $diseaseData,
        ]);
    }

    /**
     * Get week information
     */
    private function getWeekInfo()
    {
        $weekNumber = $this->startDate->weekOfYear;
        return [
            'week_number' => $weekNumber,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
            'formatted' => "Week {$weekNumber} ({$this->startDate->format('d M')} - {$this->endDate->format('d M Y')})",
        ];
    }
}
