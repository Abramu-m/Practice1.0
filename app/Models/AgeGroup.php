<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    protected $table = 'age_groups';

    protected $fillable = [
        'label',
        'min_days',
        'max_days',
        'min_years',
        'max_years',
        'description',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'min_days' => 'integer',
        'max_days' => 'integer',
        'min_years' => 'float',
        'max_years' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];

    public $timestamps = false;

    /**
     * Scope: Active age groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Check if age in days falls in this group
     */
    public function containsAgeInDays($ageInDays)
    {
        return $ageInDays >= $this->min_days && $ageInDays <= $this->max_days;
    }

    /**
     * Check if age in years falls in this group
     */
    public function containsAgeInYears($ageInYears)
    {
        return $ageInYears >= $this->min_years && $ageInYears < $this->max_years;
    }

    /**
     * Get age group for given date of birth
     */
    public static function findByDateOfBirth($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $ageInDays = now()->diffInDays($dateOfBirth);

        return self::active()
            ->where('min_days', '<=', $ageInDays)
            ->where('max_days', '>=', $ageInDays)
            ->first();
    }
}
