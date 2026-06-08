<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NhifClaimDisease extends Model
{
    use HasFactory;

    protected $fillable = [
        'nhif_claim_id',
        'folio_disease_id',
        'disease_code',
        'disease_name',
        'icd_diagnosis_id',
        'remarks',
    ];

    /**
     * Relationship with NHIF Claim
     */
    public function nhifClaim()
    {
        return $this->belongsTo(NhifClaim::class);
    }

    /**
     * Relationship with ICD Diagnosis
     */
    public function icdDiagnosis()
    {
        return $this->belongsTo(IcdDiagnosis::class);
    }
}
