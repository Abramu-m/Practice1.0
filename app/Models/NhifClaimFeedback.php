<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhifClaimFeedback extends Model
{
    protected $table = 'nhif_claim_feedback';

    protected $fillable = [
        'nhif_claim_id',
        'submission_no',
        'date_submitted',
        'claim_year',
        'claim_month',
        'folio_no',
        'card_no',
        'authorization_no',
        'amount_claimed',
        'remarks',
        'nhif_response',
    ];

    protected $casts = [
        'date_submitted' => 'datetime',
        'nhif_response' => 'array',
    ];

    public function claim()
    {
        return $this->belongsTo(NhifClaim::class, 'nhif_claim_id');
    }
}
