<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationRun extends Model
{
    protected $fillable = [
        'run_type',
        'triggered_by',
        'status',
        'total_medications_checked',
        'discrepancies_found',
        'corrections_applied',
        'duration_seconds',
        'notes',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
