<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NhifClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'nhif_claim_batch_id',
        'folio_id',
        'claim_year',
        'claim_month',
        'folio_no',
        'serial_no',
        'card_no',
        'patient_id',
        'patient_visit_id',
        'authorization_no',
        'attendance_date',
        'patient_type_code',
        'date_admitted',
        'date_discharged',
        'practitioner_no',
        'total_amount_claimed',
        'claim_status',
        'submission_date',
        'response_data',
        'submitted_by',
        'facility_code',
    ];

    protected $casts = [
        'nhif_claim_batch_id' => 'integer',
        'attendance_date' => 'date',
        'date_admitted' => 'date',
        'date_discharged' => 'date',
        'submission_date' => 'datetime',
        'response_data' => 'json',
        'total_amount_claimed' => 'decimal:2',
    ];

    /**
     * Relationship with Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relationship with Patient Visit
     */
    public function patientVisit()
    {
        return $this->belongsTo(PatientVisit::class);
    }

    /**
     * Relationship with User (who submitted)
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relationship with Claim Items
     */
    public function claimItems()
    {
        return $this->hasMany(NhifClaimItem::class);
    }

    /**
     * Relationship with Claim Diseases
     */
    public function claimDiseases()
    {
        return $this->hasMany(NhifClaimDisease::class);
    }

    /**
     * Check if claim is submitted
     */
    public function isSubmitted(): bool
    {
        return !is_null($this->submission_date);
    }

    /**
     * Check if claim is approved
     */
    public function isApproved(): bool
    {
        return $this->claim_status === 'approved';
    }

        /**
        * Check if claim is rejected
        */
    public function isRejected(): bool
    {
        return $this->claim_status === 'rejected';
    }

     /**
     * Check if claim is pending
     */
    public function isPending(): bool
    {
        return $this->claim_status === 'pending';
    }

      /**
     * Relationship with Claim Errors
     */
    public function claimErrors()
    {
        return $this->hasMany(NhifClaimError::class, 'nhif_claim_id');
    }

      /**
     * Relationship with Clain Feedback
     */
    public function claimFeedback()
    {
        return $this->hasMany(NhifClaimFeedback::class, 'nhif_claim_id');
    }
}
