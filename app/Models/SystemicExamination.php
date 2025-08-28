<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemicExamination extends Model
{
    use HasFactory;

    protected $table = 'systemic_examinations';
    
    protected $fillable = [
        'consultation_id',
        'visit_id',
        'patient_id',
        'examination_type',
        'general_findings',
        'cardiovascular_system',
        'respiratory_system',
        'gastrointestinal_system',
        'nervous_system',
        'musculoskeletal_system',
        'genitourinary_system',
        'endocrine_system',
        'skin_examination',
        'psychiatric_assessment',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'examination_date' => 'date'
    ];

    /**
     * Get the consultation that owns the examination
     */
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the patient visit that owns the examination
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Get the patient that owns the examination
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope a query to only include active examinations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get formatted examination summary
     */
    public function getSummaryAttribute()
    {
        $systems = [
            'Cardiovascular' => $this->cardiovascular_system,
            'Respiratory' => $this->respiratory_system,
            'GI' => $this->gastrointestinal_system,
            'Nervous' => $this->nervous_system,
            'Musculoskeletal' => $this->musculoskeletal_system,
            'GU' => $this->genitourinary_system,
            'Endocrine' => $this->endocrine_system,
            'Skin' => $this->skin_examination,
            'Psychiatric' => $this->psychiatric_assessment
        ];

        $findings = array_filter($systems, function($value) {
            return !empty(trim($value));
        });

        return count($findings) . ' system(s) examined';
    }
}
