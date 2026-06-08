<?php

namespace App\Services;

use App\Helpers\ReportAggregationHelper;
use App\Models\AgeGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

abstract class BaseReportService
{
    protected $startDate;
    protected $endDate;
    protected $ageGroups;
    protected $aggregationHelper;

    public function __construct()
    {
        $this->aggregationHelper = new ReportAggregationHelper();
        $this->ageGroups = AgeGroup::active()->get();
    }

    /**
     * Set date range for report
     */
    public function setDateRange($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        return $this;
    }

    /**
     * Set dates from year and month (for monthly reports)
     */
    public function setMonthlyDates($year, $month)
    {
        $this->startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $this->endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
        return $this;
    }

    /**
     * Set dates from year and week (for weekly reports)
     */
    public function setWeeklyDates($year, $week)
    {
        $this->startDate = Carbon::now()
            ->setISODate($year, $week)
            ->startOfWeek();
        $this->endDate = $this->startDate->copy()->endOfWeek();
        return $this;
    }

    /**
     * Get facility info (from config or database)
     */
    protected function getFacilityInfo()
    {
        $f = \App\Models\Facility::current();
        return [
            'name'     => $f->name,
            'region'   => $f->region,
            'district' => $f->district,
            'address'  => $f->address,
            'phone'    => $f->phone,
            'email'    => $f->email,
        ];
    }

    /**
     * Get standard report data structure
     */
    protected function getBaseReportData()
    {
        return [
            'facility' => $this->getFacilityInfo(),
            'date_range' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'formatted' => $this->aggregationHelper::formatDateRange($this->startDate, $this->endDate),
            ],
            'generated_at' => now(),
            'generated_by' => auth()->user()?->name ?? 'System',
        ];
    }

    /**
     * Aggregate visits by age and gender for the date range
     */
    protected function aggregateVisitsByAgeAndGender($query)
    {
        $query = $query->whereBetween('visit_date', [$this->startDate, $this->endDate]);
        return $this->aggregationHelper::aggregateByAgeAndGender(
            $query->with('patientInfo'),
            'visit_date'
        );
    }

    /**
     * Get total patient visits in date range
     */
    protected function getTotalVisits()
    {
        return DB::table('patient_visits')
            ->whereBetween('visit_date', [$this->startDate, $this->endDate])
            ->count();
    }

    /**
     * Get distinct patients in date range
     */
    protected function getTotalPatients()
    {
        return DB::table('patient_visits')
            ->whereBetween('visit_date', [$this->startDate, $this->endDate])
            ->distinct()
            ->count('patient');
    }

    /**
     * Build a structured age/gender matrix for tables
     */
    protected function buildAgeGenderMatrix($data)
    {
        $matrix = [];

        foreach ($this->ageGroups as $group) {
            $groupData = $data[$group->label] ?? ['male' => 0, 'female' => 0, 'total' => 0];
            $matrix[] = [
                'age_group' => $group->label,
                'male' => $groupData['male'] ?? 0,
                'female' => $groupData['female'] ?? 0,
                'total' => $groupData['total'] ?? 0,
            ];
        }

        return $matrix;
    }

    /**
     * Abstract method: each service implements this to build their specific report
     */
    abstract public function buildReport();
}
