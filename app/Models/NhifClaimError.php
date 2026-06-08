<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhifClaimError extends Model
{
    protected $table = 'nhif_claim_errors';

    protected $fillable = [
        'nhif_claim_id',
        'visit_id',
        'error_message',
        'status',
        'resolution_notes',
    ];

    public function claim()
    {
        return $this->belongsTo(NhifClaim::class, 'nhif_claim_id');
    }
}
