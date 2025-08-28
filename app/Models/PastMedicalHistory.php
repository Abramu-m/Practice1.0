<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PastMedicalHistory extends Model
{
    protected $table = 'past_medical_history';
    
    protected $fillable = [
        'patient_id',
        'allergies',
        'chronic_conditions', 
        'previous_surgeries',
        'family_history',
        'social_history',
        'smoking_status',
        'alcohol_use',
        'current_medications',
        'immunization_history',
        'reproductive_history',
        'occupational_history'
    ];

    /**
     * Get the patient that owns the medical history.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Check if patient has any allergies
     */
    public function hasAllergies()
    {
        return !empty($this->allergies);
    }

    /**
     * Check if patient has chronic conditions
     */
    public function hasChronicConditions()
    {
        return !empty($this->chronic_conditions);
    }

    /**
     * Get smoking status badge class
     */
    public function getSmokingStatusBadgeAttribute()
    {
        switch ($this->smoking_status) {
            case 'non_smoker':
                return 'success';
            case 'former_smoker':
                return 'warning';
            case 'current_smoker':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get alcohol use badge class
     */
    public function getAlcoholUseBadgeAttribute()
    {
        switch ($this->alcohol_use) {
            case 'none':
                return 'success';
            case 'occasional':
                return 'info';
            case 'moderate':
                return 'warning';
            case 'heavy':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}
