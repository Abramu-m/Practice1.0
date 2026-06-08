<?php

namespace App\Helpers;

use App\Models\AgeGroup;
use Carbon\Carbon;

class ReportAggregationHelper
{
    /**
     * Aggregate data by age groups and gender
     * Returns array: [age_group_label] => ['male' => count, 'female' => count, 'total' => count]
     */
    public static function aggregateByAgeAndGender($query, $dateFieldName = 'visit_date', $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query = $query->whereBetween($dateFieldName, [$startDate, $endDate]);
        }

        $results = $query->get();

        $aggregated = [];

        foreach ($results as $record) {
            // Get patient gender
            $gender = strtolower($record->patientInfo?->gender ?? 'unknown');
            if ($gender === 'male' || $gender === 'm' || $gender === 1) {
                $genderKey = 'male';
            } elseif ($gender === 'female' || $gender === 'f' || $gender === 2) {
                $genderKey = 'female';
            } else {
                continue; // Skip unknown gender
            }

            // Get age group
            $dob = $record->patientInfo?->date_of_birth;
            $ageGroup = AgeGroup::findByDateOfBirth($dob);

            if (!$ageGroup) {
                continue;
            }

            $groupLabel = $ageGroup->label;

            if (!isset($aggregated[$groupLabel])) {
                $aggregated[$groupLabel] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                    'sort_order' => $ageGroup->sort_order,
                ];
            }

            $aggregated[$groupLabel][$genderKey]++;
            $aggregated[$groupLabel]['total']++;
        }

        // Sort by original sort_order
        usort($aggregated, function ($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return $aggregated;
    }

    /**
     * Get age group statistics for a date range
     */
    public static function getAgeGroupStats($patients, $startDate = null, $endDate = null)
    {
        $stats = [];

        foreach ($patients as $patient) {
            // Filter by date if provided
            if ($startDate && $endDate) {
                $visits = $patient->visits()
                    ->whereBetween('visit_date', [$startDate, $endDate])
                    ->get();
            } else {
                $visits = $patient->visits;
            }

            if ($visits->isEmpty()) {
                continue;
            }

            $gender = strtolower($patient->gender);
            $genderKey = in_array($gender, ['male', 'm', '1']) ? 'male' : 'female';

            $ageGroup = AgeGroup::findByDateOfBirth($patient->date_of_birth);
            if (!$ageGroup) {
                continue;
            }

            $groupLabel = $ageGroup->label;

            if (!isset($stats[$groupLabel])) {
                $stats[$groupLabel] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0,
                    'sort_order' => $ageGroup->sort_order,
                ];
            }

            $stats[$groupLabel][$genderKey]++;
            $stats[$groupLabel]['total']++;
        }

        // Sort by original sort_order
        usort($stats, function ($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return $stats;
    }

    /**
     * Format date range for display
     */
    public static function formatDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->format('d M Y');
        $end = Carbon::parse($endDate)->format('d M Y');
        return "{$start} - {$end}";
    }

    /**
     * Get week number and date range
     */
    public static function getWeekInfo($date)
    {
        $date = Carbon::parse($date);
        $weekNumber = $date->weekOfYear;
        $weekStart = $date->startOfWeek();
        $weekEnd = $date->endOfWeek();

        return [
            'week_number' => $weekNumber,
            'start_date' => $weekStart,
            'end_date' => $weekEnd,
            'formatted' => "Week {$weekNumber} ({$weekStart->format('d M')} - {$weekEnd->format('d M Y')})",
        ];
    }

    /**
     * Calculate totals from aggregated data
     */
    public static function calculateTotals($aggregatedData)
    {
        $totals = [
            'male' => 0,
            'female' => 0,
            'total' => 0,
        ];

        foreach ($aggregatedData as $group) {
            $totals['male'] += $group['male'] ?? 0;
            $totals['female'] += $group['female'] ?? 0;
            $totals['total'] += $group['total'] ?? 0;
        }

        return $totals;
    }
}
