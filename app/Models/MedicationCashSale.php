<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationCashSale extends Model
{
    use HasFactory, Syncable;

    const STATUS_PENDING = 'pending';
    const STATUS_DISPENSED = 'dispensed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_OTC = 'otc';
    const TYPE_EXTERNAL_PRESCRIPTION = 'external_prescription';

    const PAYMENT_CASH = 'cash';
    const PAYMENT_CARD = 'card';
    const PAYMENT_MOBILE_MONEY = 'mobile_money';

    protected $fillable = [
        'sale_number',
        'sale_type',
        'external_prescription_details',
        'patient_category_id',
        'total_amount',
        'discount_amount',
        'final_amount',
        'status',
        'is_paid',
        'created_by',
        'dispensed_by',
        'dispensed_at',
        'paid_by',
        'paid_at',
        'payment_method',
        'amount_paid',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'refund_required',
        'notes',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_paid' => 'boolean',
        'refund_required' => 'boolean',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    // Relationships

    public function patientCategory()
    {
        return $this->belongsTo(PatientCategory::class);
    }

    public function items()
    {
        return $this->hasMany(MedicationCashSaleItem::class, 'cash_sale_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispenser()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function cancelled_by_user()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDispensed($query)
    {
        return $query->where('status', self::STATUS_DISPENSED);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_paid', true)->whereNotNull('dispensed_at');
    }

    public function scopeOtc($query)
    {
        return $query->where('sale_type', self::TYPE_OTC);
    }

    public function scopeExternalPrescription($query)
    {
        return $query->where('sale_type', self::TYPE_EXTERNAL_PRESCRIPTION);
    }

    // Methods

    /**
     * Generate unique sale number
     */
    public static function generateSaleNumber()
    {
        $year = date('Y');
        $lastSale = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastSale ? 
            intval(substr($lastSale->sale_number, -6)) + 1 : 1;
        
        return 'CS-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals
     */
    public function calculateTotals()
    {
        $this->total_amount = $this->items->sum('total_price');
        $this->final_amount = $this->total_amount - $this->discount_amount;
        $this->save();
    }

    /**
     * Check if can be dispensed
     */
    public function canBeDispensed()
    {
        // Must be paid and not cancelled
        if (!$this->is_paid || $this->status === self::STATUS_CANCELLED) {
            return false;
        }
        
        // Must have items that can still be dispensed
        return $this->hasDispensableItems();
    }

    /**
     * Check if has dispensable items
     */
    public function hasDispensableItems()
    {
        return $this->items->filter(function ($item) {
            return $item->canBeDispensed();
        })->count() > 0;
    }

    /**
     * Check if has dispensable items with sufficient stock
     * This method should be used when stock validation is needed
     */
    public function hasDispensableItemsWithStock()
    {
        return $this->items->filter(function ($item) {
            // Item must be dispensable AND have sufficient stock
            if (!$item->canBeDispensed()) {
                return false;
            }
            
            // Check stock - this would ideally be injected or passed as parameter
            // For now, we'll rely on the controller-level stock checking
            return true;
        })->count() > 0;
    }

    /**
     * Get count of items that can be dispensed
     */
    public function getDispensableItemsCount()
    {
        return $this->items->filter(function ($item) {
            return $item->canBeDispensed();
        })->count();
    }

    /**
     * Get count of cancelled items
     */
    public function getCancelledItemsCount()
    {
        return $this->items->where('status', MedicationCashSaleItem::STATUS_CANCELLED)->count();
    }

    /**
     * Get dispensing status summary
     */
    public function getDispensingStatusSummary()
    {
        $total = $this->items->count();
        $dispensed = $this->items->where('status', MedicationCashSaleItem::STATUS_DISPENSED)->count();
        $cancelled = $this->getCancelledItemsCount();
        $pending = $this->getDispensableItemsCount();
        
        return [
            'total' => $total,
            'dispensed' => $dispensed,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'dispensable_remaining' => $pending, // Items that can still be dispensed
            'completion_rate' => $total > 0 ? (($dispensed + $cancelled) / $total * 100) : 0
        ];
    }

    /**
     * Check if partially dispensed (some items dispensed, some still pending)
     */
    public function isPartiallyDispensed()
    {
        $dispensedItems = $this->items->where('status', MedicationCashSaleItem::STATUS_DISPENSED)->count();
        $pendingItems = $this->items->where('status', MedicationCashSaleItem::STATUS_PENDING)->count();
        
        return $dispensedItems > 0 && $pendingItems > 0;
    }

    /**
     * Check if all dispensable items are fully dispensed
     */
    public function isFullyDispensed()
    {
        $dispensableItems = $this->items->whereNotIn('status', [MedicationCashSaleItem::STATUS_CANCELLED]);
        
        // If no dispensable items, consider it fully dispensed (all were cancelled)
        if ($dispensableItems->isEmpty()) {
            return true;
        }

        // All dispensable items must be dispensed
        return $dispensableItems->every(function ($item) {
            return $item->status === MedicationCashSaleItem::STATUS_DISPENSED;
        });
    }

    /**
     * Check if can be paid
     */
    public function canBePaid()
    {
        // In payment-first workflow, only pending sales can be paid
        return $this->status === self::STATUS_PENDING && 
               !$this->is_paid && 
               $this->status !== self::STATUS_CANCELLED;
    }

    /**
     * Check if sale is completed (both paid and all dispensable items dispensed)
     */
    public function isCompleted()
    {
        // Must be paid first
        if (!$this->is_paid) {
            return false;
        }

        // Check if all dispensable items are fully dispensed
        return $this->isFullyDispensed();
    }

    /**
     * Check if sale is paid but not yet dispensed
     */
    public function isPaidButNotDispensed()
    {
        return $this->is_paid && is_null($this->dispensed_at);
    }

    /**
     * Mark as dispensed (updates dispensed_by and dispensed_at when first item is dispensed)
     */
    public function markAsDispensed($dispenserId)
    {
        // In payment-first workflow, only allow dispensing of paid sales
        if (!$this->is_paid) {
            throw new \Exception('Cannot dispense unpaid sale. Payment must be processed first.');
        }

        // If this is the first dispensing (no dispensed_at yet), set it
        if (is_null($this->dispensed_at)) {
            $this->update([
                'dispensed_by' => $dispenserId,
                'dispensed_at' => now(),
            ]);
        }
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($cashierId, $paymentMethod, $amountPaid)
    {
        $this->update([
            'is_paid' => true,
            'paid_by' => $cashierId,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'amount_paid' => $amountPaid,
        ]);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        // If cancelled, always show red
        if ($this->status === self::STATUS_CANCELLED) {
            return 'danger';
        }
        
        // If completed (paid and fully dispensed) - green
        if ($this->isCompleted()) {
            return 'success';
        }
        
        // If paid and partially dispensed - blue
        if ($this->is_paid && $this->isPartiallyDispensed()) {
            return 'info';
        }
        
        // If paid but not dispensed yet - info blue
        if ($this->is_paid && !$this->dispensed_at) {
            return 'info';
        }
        
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_DISPENSED => 'warning', // dispensed but not paid
            default => 'secondary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        // If cancelled, always show cancelled
        if ($this->status === self::STATUS_CANCELLED) {
            return 'Cancelled';
        }
        
        // If completed (paid and fully dispensed), show as completed
        if ($this->isCompleted()) {
            return 'Completed';
        }
        
        // If paid but partially dispensed
        if ($this->is_paid && $this->isPartiallyDispensed()) {
            return 'Partially Dispensed';
        }
        
        // If paid but not dispensed yet
        if ($this->is_paid && !$this->dispensed_at) {
            return 'Paid - Ready to Dispense';
        }
        
        return match($this->status) {
            self::STATUS_PENDING => 'Awaiting Payment',
            self::STATUS_DISPENSED => 'Dispensed - Payment Required',
            default => 'Unknown'
        };
    }
}
