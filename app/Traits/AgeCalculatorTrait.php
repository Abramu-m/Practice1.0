<?php

namespace App\Traits;

use Carbon\Carbon;

trait AgeCalculatorTrait
{
    /**
     * Calculate age in days from date of birth
     */
    public function getAgeInDays($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $dob = $dateOfBirth instanceof Carbon
            ? $dateOfBirth
            : Carbon::parse($dateOfBirth);

        return now()->diffInDays($dob);
    }

    /**
     * Calculate age in years from date of birth
     */
    public function getAgeInYears($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $dob = $dateOfBirth instanceof Carbon
            ? $dateOfBirth
            : Carbon::parse($dateOfBirth);

        return now()->diffInYears($dob);
    }

    /**
     * Calculate age in months from date of birth
     */
    public function getAgeInMonths($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $dob = $dateOfBirth instanceof Carbon
            ? $dateOfBirth
            : Carbon::parse($dateOfBirth);

        return now()->diffInMonths($dob);
    }

    /**
     * Get formatted age string (e.g., "2y 5m 3d")
     */
    public function getFormattedAge($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return 'Unknown';
        }

        $dob = $dateOfBirth instanceof Carbon
            ? $dateOfBirth
            : Carbon::parse($dateOfBirth);

        $years = now()->diffInYears($dob);
        $months = now()->copy()->subYears($years)->diffInMonths($dob);
        $days = now()->copy()->subYears($years)->subMonths($months)->diffInDays($dob);

        $parts = [];
        if ($years > 0) $parts[] = "{$years}y";
        if ($months > 0) $parts[] = "{$months}m";
        if ($days > 0) $parts[] = "{$days}d";

        return implode(' ', $parts) ?: '0d';
    }
}
