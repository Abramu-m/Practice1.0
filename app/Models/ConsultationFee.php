<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationFee extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_category_id',
        'visit_type_id',
        'cash_amount',
        'covered_amount',
        'description',
        'status',
        'created_by'
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
        'covered_amount' => 'decimal:2',
        'status' => 'integer',
    ];

    /**
     * Get the doctor associated with the consultation fee.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the patient category associated with the consultation fee.
     */
    public function patientCategory()
    {
        return $this->belongsTo(PatientCategory::class, 'patient_category_id');
    }

    /**
     * Get the visit type associated with the consultation fee.
     */
    public function visitType()
    {
        return $this->belongsTo(VisitType::class, 'visit_type_id');
    }

    /**
     * Get the user who created the consultation fee record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active consultation fees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the fee for a specific doctor, patient category, and visit type.
     */
    public static function getFee($doctorId, $patientCategoryId, $visitTypeId)
    {
        return self::where('doctor_id', $doctorId)
                   ->where('patient_category_id', $patientCategoryId)
                   ->where('visit_type_id', $visitTypeId)
                   ->where('status', 1)
                   ->first();
    }
}
