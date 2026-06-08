<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NhifClaimItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nhif_claim_id',
        'folio_item_id',
        'item_code',
        'item_name',
        'other_details',
        'item_quantity',
        'unit_price',
        'amount_claimed',
        'approval_ref_no',
        'medical_service_id',
        'medication_id',
    ];

    protected $casts = [
        'item_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount_claimed' => 'decimal:2',
    ];

    /**
     * Relationship with NHIF Claim
     */
    public function nhifClaim()
    {
        return $this->belongsTo(NhifClaim::class);
    }

    /**
     * Relationship with Medical Service (if applicable)
     */
    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
    }

    /**
     * Relationship with Medication (if applicable)
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
