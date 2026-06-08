<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientReferral extends Model
{
    use HasFactory;

    protected $table = 'patient_referrals';

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'visit_id',
        'referral_hospital_id',
        'referral_department_id',
        'letter_heading',
        'letter_template',
        'additional_notes',
        'letter_closing',
        'referral_date',
        'created_by',
        'status',
    ];

    protected $casts = [
        'referral_date' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'id');
    }

    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id', 'id');
    }

    public function hospital()
    {
        return $this->belongsTo(ReferralHospital::class, 'referral_hospital_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(ReferralDepartment::class, 'referral_department_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
