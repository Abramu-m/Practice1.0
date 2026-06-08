<?php

namespace App\Services;

use App\Models\Investigation;
use Carbon\Carbon;
use DB;

class LabBloodTransfusionReportService extends BaseReportService
{
    /**
     * Build blood transfusion lab report
     */
    public function buildReport(): array
    {
        $investigations = $this->getBloodTransfusionInvestigations();

        return [
            'facility' => $this->getFacilityInfo(),
            'month_year' => $this->date_from->format('M Y'),
            'total_tests' => $investigations->count(),
            'completed_tests' => $investigations->whereNotNull('result_value')->count(),
            'pending_tests' => $investigations->whereNull('result_value')->count(),
            'completion_rate' => $this->calculateCompletionRate(
                $investigations->count(),
                $investigations->whereNotNull('result_value')->count()
            ),
            'investigations' => $investigations->map(function ($inv) {
                return [
                    'test_name' => $inv->medicalService->name ?? 'Unknown',
                    'patient_id' => $inv->patient_id,
                    'visit_date' => $inv->visit_date->format('d-m-Y'),
                    'status' => $inv->status ?? 'Pending',
                    'result_value' => $inv->result_value,
                    'result_unit' => $inv->result_unit,
                ];
            })->toArray(),
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * Get blood transfusion investigations
     */
    private function getBloodTransfusionInvestigations()
    {
        return Investigation::join('patient_visits', 'investigations.visit_id', '=', 'patient_visits.id')
            ->join('medical_services', 'investigations.medical_service_id', '=', 'medical_services.id')
            ->join('service_categories', 'medical_services.service_category_id', '=', 'service_categories.id')
            ->whereBetween('patient_visits.created_at', [$this->date_from, $this->date_to])
            ->where(function ($query) {
                $query->where('service_categories.name', 'LIKE', '%Blood Transfusion%')
                    ->orWhere('service_categories.name', 'LIKE', '%Transfusion%')
                    ->orWhere('medical_services.name', 'LIKE', '%Blood%');
            })
            ->select('investigations.*')
            ->with('medicalService')
            ->orderBy('investigations.created_at', 'DESC')
            ->get();
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate(int $total, int $completed): int
    {
        if ($total === 0) {
            return 0;
        }
        return round(($completed / $total) * 100);
    }
}
