<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CdsDosageLimit extends Model
{
    protected $fillable = [
        'medication_id',
        'mg_per_kg',
        'age_min_years', 'age_max_years',
        'weight_min_kg', 'weight_max_kg',
        'special_conditions', 'is_active',
        'max_single_dose_adults', 'max_daily_dose_adults', 'max_duration_adults',
        'max_single_dose_children', 'max_daily_dose_children', 'max_duration_children',
        'renal_function_adults', 'renal_function_children',
        'liver_function_adults', 'liver_function_children',
        'lab_results', 'diagnoses', 'interactions',
    ];

    protected $casts = [
        'medication_id'  => 'integer',
        'mg_per_kg'      => 'decimal:3',
        'age_min_years'  => 'decimal:1',
        'age_max_years'  => 'decimal:1',
        'weight_min_kg'  => 'decimal:1',
        'weight_max_kg'  => 'decimal:1',
        'special_conditions'      => 'array',
        'renal_function_adults'   => 'array',
        'renal_function_children' => 'array',
        'liver_function_adults'   => 'array',
        'liver_function_children' => 'array',
        'lab_results' => 'array',
        'diagnoses'   => 'array',
        'interactions' => 'array',
        'is_active'   => 'boolean',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'medication_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check if this limit applies to given patient context (age / weight)
    public function appliesTo(array $context): bool
    {
        $patientAge    = data_get($context, 'patient.age');
        $patientWeight = data_get($context, 'patient.weight');

        if ($patientAge !== null) {
            if ($patientAge < $this->age_min_years || $patientAge > $this->age_max_years) {
                return false;
            }
        }

        if ($patientWeight !== null && $this->weight_min_kg && $this->weight_max_kg) {
            if ($patientWeight < $this->weight_min_kg || $patientWeight > $this->weight_max_kg) {
                return false;
            }
        }

        return true;
    }
}
