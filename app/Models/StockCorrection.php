<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCorrection extends Model
{
    protected $fillable = [
        'medication_id',
        'location_id',
        'correction_type',
        'field_corrected',
        'old_value',
        'new_value',
        'difference',
        'reason',
        'notes',
        'corrected_by',
        'correction_date',
        'status',
    ];

    protected $casts = [
        'old_value'       => 'decimal:4',
        'new_value'       => 'decimal:4',
        'difference'      => 'decimal:4',
        'correction_date' => 'datetime',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
