<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CdsDosageLimit extends Model
{
    protected $fillable = [
        'medication_policy_id', 'limit_type', 'value_mg', 'mg_per_kg',
        'age_min_years', 'age_max_years', 'weight_min_kg', 'weight_max_kg',
        'special_conditions', 'is_active'
    ];

    protected $casts = [
        'medication_policy_id' => 'integer',
        'value_mg' => 'decimal:3',
        'mg_per_kg' => 'decimal:3',
        'age_min_years' => 'decimal:1',
        'age_max_years' => 'decimal:1',
        'weight_min_kg' => 'decimal:1',
        'weight_max_kg' => 'decimal:1',
        'special_conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function medicationPolicy(): BelongsTo
    {
        return $this->belongsTo(CdsMedicationPolicy::class, 'medication_policy_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('limit_type', $type);
    }

    // Check if this limit applies to given patient context
    public function appliesTo(array $context): bool
    {
        $patientAge = data_get($context, 'patient.age');
        $patientWeight = data_get($context, 'patient.weight');
        
        // Check age range
        if ($patientAge !== null) {
            if ($patientAge < $this->age_min_years || $patientAge > $this->age_max_years) {
                return false;
            }
        }
        
        // Check weight range
        if ($patientWeight !== null && $this->weight_min_kg && $this->weight_max_kg) {
            if ($patientWeight < $this->weight_min_kg || $patientWeight > $this->weight_max_kg) {
                return false;
            }
        }
        
        // Check special conditions (e.g., renal function)
        if ($this->special_conditions) {
            return $this->evaluateSpecialConditions($context);
        }
        
        return true;
    }
    
    private function evaluateSpecialConditions(array $context): bool
    {
        $conditions = $this->special_conditions;
        
        // Check eGFR if specified
        if (isset($conditions['egfr_max'])) {
            $patientEgfr = data_get($context, 'patient.egfr');
            if ($patientEgfr === null || $patientEgfr > $conditions['egfr_max']) {
                return false;
            }
        }
        
        if (isset($conditions['egfr_min'])) {
            $patientEgfr = data_get($context, 'patient.egfr');
            if ($patientEgfr === null || $patientEgfr < $conditions['egfr_min']) {
                return false;
            }
        }
        
        return true;
    }
}
