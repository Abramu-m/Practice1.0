<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitDiagnosis extends Model
{
    protected $table = 'visit_diagnoses';

    protected $fillable = [
        'visit_id', 'patient_id', 'icd_10_id', 'diagnosis_type', 'clinical_notes', 'sequence', 'is_primary', 'doctor_id'
    ];

    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id', 'id');
    }

    public function icd10()
    {
        return $this->belongsTo(Icd10::class, 'icd_10_id', 'id');
    }
}
