<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class FinancialTransaction extends Model
{
    use Syncable;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'transaction_type',
        'category',
        'subcategory',
        'amount',
        'description',
        'source_type',
        'source_id',
        'patient_id',
        'visit_id',
        'payment_method',
        'payment_reference',
        'insurance_covered_amount',
        'patient_paid_amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'insurance_covered_amount' => 'decimal:2',
        'patient_paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Auto-generate transaction number on creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = self::generateTransactionNumber();
            }
            
            if (empty($transaction->created_by)) {
                $transaction->created_by = Auth::id();
            }
            
            if (empty($transaction->transaction_date)) {
                $transaction->transaction_date = now();
            }
        });
    }

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = now()->format('Ymd');
        
        // Get the last transaction number for today
        $lastTransaction = self::where('transaction_number', 'like', $prefix . $date . '%')
            ->orderBy('transaction_number', 'desc')
            ->first();
        
        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeIncome($query)
    {
        return $query->where('transaction_type', 'income')->where('status', 'completed');
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', 'expense')->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Static methods for quick calculations
     */
    public static function getTodayIncome(): float
    {
        return self::income()->today()->sum('amount');
    }

    public static function getTodayExpenses(): float
    {
        return self::expense()->today()->sum('amount');
    }

    public static function getTodayNetBalance(): float
    {
        return self::getTodayIncome() - self::getTodayExpenses();
    }

    public static function getMonthlyIncome(): float
    {
        return self::income()->thisMonth()->sum('amount');
    }

    public static function getMonthlyExpenses(): float
    {
        return self::expense()->thisMonth()->sum('amount');
    }

    /**
     * Create transaction from patient visit payment
     */
    public static function createFromVisitPayment(PatientVisit $visit): ?self
    {
        $totalAmount = $visit->amount_cash + $visit->amount_covered;
        
        if ($totalAmount <= 0) {
            return null;
        }

        return self::create([
            'transaction_type' => 'income',
            'category' => 'consultation',
            'subcategory' => 'consultation_fee',
            'amount' => $totalAmount,
            'description' => "Consultation fee for patient visit #{$visit->id}",
            'source_type' => 'consultation',
            'source_id' => $visit->id,
            'patient_id' => $visit->patient,
            'visit_id' => $visit->id,
            'payment_method' => $visit->amount_covered > 0 ? 'insurance' : 'cash',
            'patient_paid_amount' => $visit->amount_cash,
            'insurance_covered_amount' => $visit->amount_covered,
        ]);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Tsh' . number_format($this->amount, 2);
    }

    /**
     * Get transaction type badge class
     */
    public function getTransactionTypeBadgeAttribute(): string
    {
        return $this->transaction_type === 'income' ? 'bg-success' : 'bg-danger';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'refunded' => 'bg-info',
            default => 'bg-secondary'
        };
    }
}
