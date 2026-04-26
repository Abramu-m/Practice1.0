<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class GeneralExpense extends Model
{
    protected $fillable = [
        'expense_number',
        'expense_date',
        'transaction_type',
        'expense_category',
        'expense_subcategory',
        'description',
        'amount',
        'paid_to',
        'payee_contact',
        'payment_method',
        'payment_reference',
        'receipt_number',
        'patient_id',
        'visit_id',
        'medication_id',
        'staff_id',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'receipt_path',
        'notes'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Auto-generate expense number on creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateExpenseNumber();
            }
            
            if (empty($expense->requested_by)) {
                $expense->requested_by = Auth::id();
            }
            
            if (empty($expense->expense_date)) {
                $expense->expense_date = today();
            }
        });
    }

    /**
     * Generate unique expense number
     */
    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $date = now()->format('Ymd');
        
        // Get the last expense number for today
        $lastExpense = self::where('expense_number', 'like', $prefix . $date . '%')
            ->orderBy('expense_number', 'desc')
            ->first();
        
        if ($lastExpense) {
            $lastNumber = (int) substr($lastExpense->expense_number, -4);
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

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('expense_category', $category);
    }

    /**
     * Expense categories
     */
    public static function getCategories(): array
    {
        return [
            'administrative' => 'Administrative',
            'medical_supplies' => 'Medical Supplies',
            'equipment_maintenance' => 'Equipment & Maintenance',
            'staff_related' => 'Staff Related',
            'building_infrastructure' => 'Building & Infrastructure',
            'professional_services' => 'Professional Services',
            'marketing_communication' => 'Marketing & Communication',
            'utilities' => 'Utilities',
            'transport' => 'Transport',
            'other' => 'Other'
        ];
    }

    /**
     * Get subcategories for a category
     */
    public static function getSubcategories(string $category): array
    {
        $subcategories = [
            'administrative' => ['Office Supplies', 'Stationery', 'Communication', 'Printing'],
            'medical_supplies' => ['Consumables', 'Medical Equipment', 'Laboratory Supplies'],
            'equipment_maintenance' => ['Repairs', 'Maintenance', 'Upgrades', 'New Equipment'],
            'staff_related' => ['Allowances', 'Training', 'Uniforms', 'Benefits'],
            'building_infrastructure' => ['Rent', 'Repairs', 'Improvements', 'Security'],
            'professional_services' => ['Legal', 'Accounting', 'Consulting', 'IT Services'],
            'marketing_communication' => ['Advertising', 'Website', 'Brochures', 'Events'],
            'utilities' => ['Electricity', 'Water', 'Internet', 'Phone'],
            'transport' => ['Fuel', 'Vehicle Maintenance', 'Transport Allowance'],
            'other' => ['Miscellaneous']
        ];

        return $subcategories[$category] ?? [];
    }

    /**
     * Approve the expense
     */
    public function approve(User $approver): bool
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now()
        ]);

        // Create financial transaction
        FinancialTransaction::create([
            'transaction_type' => $this->transaction_type,
            'category' => 'general_expense',
            'subcategory' => $this->expense_category,
            'amount' => $this->amount,
            'description' => $this->description,
            'source_type' => 'general_expense',
            'source_id' => $this->id,
            'patient_id' => $this->patient_id,
            'visit_id' => $this->visit_id,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'created_by' => $this->requested_by,
            'approved_by' => $approver->id,
            'approved_at' => now()
        ]);

        return true;
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Tsh' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-secondary',
            'pending_approval' => 'bg-warning',
            'approved' => 'bg-info',
            'paid' => 'bg-success',
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
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }
}
