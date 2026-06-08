<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LabReportService extends BaseReportService
{
    /**
     * Build monthly lab reports summary
     */
    public function buildReport()
    {
        $baseData = $this->getBaseReportData();

        // Get all investigations in date range grouped by service
        $labTests = DB::table('investigations as inv')
            ->join('patient_visits as pv', 'pv.id', '=', 'inv.visit_id')
            ->join('patients as p', 'p.id', '=', 'pv.patient')
            ->join('medical_services as ms', 'ms.id', '=', 'inv.medical_service_id')
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereNull('inv.cancelled_at')
            ->select(
                'ms.id',
                'ms.name as test_name',
                DB::raw('COUNT(*) as total_tests'),
                DB::raw('SUM(CASE WHEN inv.status = "resulted" THEN 1 ELSE 0 END) as completed_tests'),
                DB::raw('SUM(CASE WHEN inv.status = "resulted" THEN 0 ELSE 1 END) as pending_tests')
            )
            ->groupBy('ms.id', 'ms.name')
            ->orderBy('total_tests', 'DESC')
            ->get();

        // Get tests by service category
        $testsByCategory = DB::table('investigations as inv')
            ->join('patient_visits as pv', 'pv.id', '=', 'inv.visit_id')
            ->join('medical_services as ms', 'ms.id', '=', 'inv.medical_service_id')
            ->join('service_categories as sc', 'sc.id', '=', 'ms.service_category_id')
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereNull('inv.cancelled_at')
            ->select(
                'sc.name as category',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('sc.name')
            ->orderBy('count', 'DESC')
            ->get();

        // Get test statistics by status
        $totalTests = $labTests->sum('total_tests');
        $completedTests = $labTests->sum('completed_tests');
        $pendingTests = $labTests->sum('pending_tests');

        return array_merge($baseData, [
            'report_type' => 'monthly_lab_reports',
            'title' => 'Monthly Lab Reports',
            'total_tests' => $totalTests,
            'completed_tests' => $completedTests,
            'pending_tests' => $pendingTests,
            'completion_rate' => $totalTests > 0 ? round(($completedTests / $totalTests) * 100, 2) : 0,
            'by_test_type' => $labTests->map(function ($test) {
                return [
                    'test_name' => $test->test_name,
                    'total_tests' => $test->total_tests,
                    'completed_tests' => $test->completed_tests,
                    'pending_tests' => $test->pending_tests,
                    'completion_rate' => $test->total_tests > 0 ? round(($test->completed_tests / $test->total_tests) * 100, 2) : 0,
                ];
            })->toArray(),
            'by_category' => $testsByCategory->map(function ($cat) {
                return [
                    'category' => $cat->category,
                    'count' => $cat->count,
                ];
            })->toArray(),
        ]);
    }
}
