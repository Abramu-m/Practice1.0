<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory, Syncable;

    protected $table = 'consultations';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'history_of_present_illness',
        'provisional_diagnosis',
        'final_diagnosis',
        'remarks',
        'followup_date',
        'followup_instructions',
        'consultation_date',
        'status'
    ];

    protected $casts = [
        'followup_date' => 'date'
    ];

    /**
     * Get the patient that owns the consultation
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the doctor that performed the consultation
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the patient visit for this consultation
     */
    public function patientVisit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id', 'id');
    }

    /**
     * Alias for patientVisit relationship for backward compatibility
     */
    public function visit()
    {
        return $this->patientVisit();
    }

    /**
     * Get vital signs for this consultation
     */
    public function vitals()
    {
        return $this->hasMany(VitalSigns::class, 'consultation_id', 'id');
    }

    /**
     * Get examinations for this consultation
     */
    public function examinations()
    {
        return $this->hasMany(SystemicExamination::class, 'consultation_id', 'id');
    }

    /**
     * Get prescriptions for this consultation
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'consultation_id', 'id');
    }

    /**
     * Get investigations for this consultation
     */
    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'consultation_id', 'id');
    }

    /**
     * Get the latest vital signs
     */
    public function getLatestVitalsAttribute()
    {
        return $this->vitals()->latest('id')->first();
    }

    /**
     * Check if consultation is active
     */
    public function isActive()
    {
        return $this->status == 'active';
    }

    /**
     * Get ICD diagnoses for this consultation
     */
    public function icdDiagnoses()
    {
        return $this->hasMany(IcdDiagnosis::class);
    }

    /**
     * Get provisional ICD diagnoses for this consultation
     */
    public function provisionalIcdDiagnoses()
    {
        return $this->hasMany(IcdDiagnosis::class)->where('type', 'provisional');
    }

    /**
     * Get final ICD diagnoses for this consultation
     */
    public function finalIcdDiagnoses()
    {
        return $this->hasMany(IcdDiagnosis::class)->where('type', 'final');
    }
}
