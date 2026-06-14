<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class SalaryPayment extends Model
{
    use Syncable;

    protected $fillable = [
        'payment_number',
        'employee_id',
        'pay_period_year',
        'pay_period_month',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'net_salary',
        'payment_date',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
        'financial_transaction_id',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }

            if (empty($payment->created_by)) {
                $payment->created_by = Auth::id();
            }
        });
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'SAL';
        $date = now()->format('Ymd');

        $lastPayment = self::where('payment_number', 'like', $prefix . $date . '%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalaryPaymentItem::class);
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('pay_period_year', $year)->where('pay_period_month', $month);
    }

    /**
     * Recompute total_allowances, total_deductions and net_salary from
     * the current set of line items.
     */
    public function recalculateTotals(): void
    {
        $this->total_allowances = $this->items()->allowances()->sum('amount');
        $this->total_deductions = $this->items()->deductions()->sum('amount');
        $this->net_salary = $this->basic_salary + $this->total_allowances - $this->total_deductions;
        $this->save();
    }

    /**
     * Income subject to PAYE: basic salary plus taxable allowances, minus
     * pre-tax deductions (e.g. NSSF contributions).
     */
    public function taxableIncome(): float
    {
        $taxableAllowances = $this->items()->allowances()->where('is_taxable', true)->sum('amount');
        $preTaxDeductions = $this->items()->deductions()->where('is_pre_tax', true)->sum('amount');

        return (float) $this->basic_salary + (float) $taxableAllowances - (float) $preTaxDeductions;
    }

    /**
     * Recompute the PAYE deduction line item based on the current taxable
     * income, replacing any previously computed PAYE item.
     */
    public function recalculatePaye(): void
    {
        $this->items()->statutory()->where('name', 'PAYE')->delete();

        $payeAmount = PayeTaxBand::calculate($this->taxableIncome());

        if ($payeAmount > 0) {
            $this->items()->create([
                'type' => 'deduction',
                'name' => 'PAYE',
                'amount' => $payeAmount,
                'is_taxable' => false,
                'is_pre_tax' => false,
                'is_statutory' => true,
            ]);
        }

        $this->recalculateTotals();
    }

    public function approve(User $by): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $by->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark this payment as paid and create the corresponding expense
     * transaction in Financial Management.
     */
    public function markAsPaid(User $by, array $paymentDetails = []): void
    {
        $employee = $this->employee;
        $period = sprintf('%04d-%02d', $this->pay_period_year, $this->pay_period_month);

        $transaction = FinancialTransaction::create([
            'transaction_type' => 'expense',
            'category' => 'salary',
            'subcategory' => $employee->department,
            'amount' => $this->net_salary,
            'description' => "Salary payment for {$employee->name} - {$period}",
            'source_type' => 'salary_payment',
            'source_id' => $this->id,
            'payment_method' => $paymentDetails['payment_method'] ?? $this->payment_method,
            'payment_reference' => $paymentDetails['payment_reference'] ?? $this->payment_reference,
            'status' => 'completed',
            'created_by' => $by->id,
        ]);

        $this->update([
            'status' => 'paid',
            'payment_date' => $paymentDetails['payment_date'] ?? now(),
            'payment_method' => $paymentDetails['payment_method'] ?? $this->payment_method,
            'payment_reference' => $paymentDetails['payment_reference'] ?? $this->payment_reference,
            'paid_by' => $by->id,
            'paid_at' => now(),
            'financial_transaction_id' => $transaction->id,
        ]);
    }

    /**
     * Cancel this payment. If it was already paid, the linked financial
     * transaction is cancelled too.
     */
    public function cancel(User $by, string $reason): void
    {
        if ($this->financial_transaction_id) {
            $this->financialTransaction?->update([
                'status' => 'cancelled',
                'notes' => $reason,
            ]);
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_by' => $by->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-secondary',
            'approved' => 'bg-info',
            'paid' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getPeriodLabelAttribute(): string
    {
        return \Carbon\Carbon::createFromDate($this->pay_period_year, $this->pay_period_month, 1)->format('F Y');
    }
}
