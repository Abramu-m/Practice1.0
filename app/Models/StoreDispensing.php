<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StoreDispensing extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispensing_number',
        'patient_id',
        'dispensed_by',
        'dispensing_date',
        'total_amount',
        'payment_status',
        'payment_method',
        'paid_at',
        'insurance_provider',
        'insurance_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'dispensing_date' => 'datetime',
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoreDispensingItem::class, 'dispensing_id');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeInsurance($query)
    {
        return $query->where('payment_status', 'insurance');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('dispensing_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('dispensing_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('dispensing_date', now()->month)
                    ->whereYear('dispensing_date', now()->year);
    }

    public function calculateTotal(): float
    {
        return $this->items()->sum(DB::raw('quantity_dispensed * unit_price'));
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isInsurance(): bool
    {
        return $this->payment_status === 'insurance';
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedDispensingDateAttribute(): string
    {
        return $this->dispensing_date->format('M d, Y');
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'paid' => 'success',
            'insurance' => 'info',
        ];

        return $badges[$this->payment_status] ?? 'secondary';
    }
}
