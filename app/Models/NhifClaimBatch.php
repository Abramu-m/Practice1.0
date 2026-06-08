<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhifClaimBatch extends Model
{
    use HasFactory;

    // Explicitly defining the snake_case table name
    protected $table = 'nhif_claim_batches';

    protected $fillable = [
        'claim_no',
        'claim_year',
        'claim_month',
        'number_of_folios',
        'amount_claimed',
        'status',
    ];

    protected $casts = [
        'claim_year' => 'integer',
        'claim_month' => 'integer',
        'number_of_folios' => 'integer',
        'amount_claimed' => 'decimal:2',
    ];

    /**
     * Relationship with NhifClaim
     */
    public function claims()
    {
        return $this->hasMany(NhifClaim::class, 'nhif_claim_batch_id');
    }

    /**
     * Accessor to get the claim month name
     */
    public function getClaimMonthNameAttribute()
    {
        return date("F", mktime(0, 0, 0, $this->claim_month, 10));
    }

    /**
     * Accessor to get the claim year and month in "MMM-YYYY" format
     */
    public function getClaimYearMonthAttribute()
    {
        return date("M-Y", mktime(0, 0, 0, $this->claim_month, 10, $this->claim_year));
    }
}

