<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'patient_id',
        'visit_id',
        'total_amount',
        'cash_amount',
        'insurance_amount',
        'consultation_fee',
        'investigation_fees',
        'medication_fees',
        'other_fees',
        'payment_method',
        'payment_reference',
        'insurance_scheme',
        'insurance_number',
        'authorization_number',
        'status',
        'printed_at',
        'printed_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'created_by'
    ];

    protected $casts = [
        'receipt_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'consultation_fee' => 'decimal:2',
        'investigation_fees' => 'decimal:2',
        'medication_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'printed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Auto-generate receipt number on creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = self::generateReceiptNumber();
            }
            
            if (empty($receipt->created_by)) {
                $receipt->created_by = Auth::id();
            }
            
            if (empty($receipt->receipt_date)) {
                $receipt->receipt_date = now();
            }
        });
    }

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber(): string
    {
        $prefix = 'RCP';
        $date = now()->format('Ymd');
        
        // Get the last receipt number for today
        $lastReceipt = self::where('receipt_number', 'like', $prefix . $date . '%')
            ->orderBy('receipt_number', 'desc')
            ->first();
        
        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->receipt_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('receipt_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('receipt_date', now()->month)
                    ->whereYear('receipt_date', now()->year);
    }

    public function scopePrinted($query)
    {
        return $query->where('status', 'printed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Create receipt from patient visit
     */
    public static function createFromVisit(PatientVisit $visit): self
    {
        return self::create([
            'patient_id' => $visit->patient,
            'visit_id' => $visit->id,
            'total_amount' => $visit->amount_cash + $visit->amount_covered,
            'cash_amount' => $visit->amount_cash,
            'insurance_amount' => $visit->amount_covered,
            'consultation_fee' => $visit->amount_cash + $visit->amount_covered,
            'payment_method' => $visit->amount_covered > 0 ? 'insurance' : 'cash',
            'insurance_scheme' => $visit->visitCategory->description ?? null,
            'insurance_number' => $visit->nhif_reference_no,
            'authorization_number' => $visit->authorization_no,
        ]);
    }

    /**
     * Mark receipt as printed
     */
    public function markAsPrinted(User $printer = null): bool
    {
        return $this->update([
            'status' => 'printed',
            'printed_at' => now(),
            'printed_by' => $printer?->id ?? Auth::id()
        ]);
    }

    /**
     * Cancel receipt
     */
    public function cancel(string $reason, User $canceller = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $canceller?->id ?? Auth::id(),
            'cancellation_reason' => $reason
        ]);
    }

    /**
     * Get formatted total amount with currency
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Tsh' . number_format($this->total_amount, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-secondary',
            'printed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'printed' => 'Printed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    /**
     * Check if receipt can be printed
     */
    public function canBePrinted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if receipt can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'printed']);
    }
}
