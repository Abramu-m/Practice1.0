<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medication_id',
        'quantity_prescribed',
        'quantity_dispensed',
        'dosage_instructions',
        'frequency',
        'duration_days',
        'location_stock_id',
        'grn_item_id',
        'unit_cost',
        'dispensed_by',
        'dispensed_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
        'quantity_prescribed' => 'decimal:2',
        'quantity_dispensed' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'duration_days' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIALLY_DISPENSED = 'partially_dispensed';
    const STATUS_FULLY_DISPENSED = 'fully_dispensed';
    const STATUS_CANCELLED = 'cancelled';

    // Frequency constants
    const FREQUENCY_ONCE_DAILY = 'once_daily';
    const FREQUENCY_TWICE_DAILY = 'twice_daily';
    const FREQUENCY_THREE_TIMES_DAILY = 'three_times_daily';
    const FREQUENCY_FOUR_TIMES_DAILY = 'four_times_daily';
    const FREQUENCY_EVERY_6_HOURS = 'every_6_hours';
    const FREQUENCY_EVERY_8_HOURS = 'every_8_hours';
    const FREQUENCY_EVERY_12_HOURS = 'every_12_hours';
    const FREQUENCY_AS_NEEDED = 'as_needed';

    // Relationships

    /**
     * Get the prescription
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the location stock
     */
    public function locationStock()
    {
        return $this->belongsTo(StoreLocationStock::class);
    }

    /**
     * Get the GRN item
     */
    public function grnItem()
    {
        return $this->belongsTo(GoodsReceivedNoteItem::class, 'grn_item_id');
    }

    /**
     * Get the user who dispensed the medication
     */
    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    // Scopes

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get dispensed items
     */
    public function scopeDispensed($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PARTIALLY_DISPENSED,
            self::STATUS_FULLY_DISPENSED
        ]);
    }

    /**
     * Scope to get fully dispensed items
     */
    public function scopeFullyDispensed($query)
    {
        return $query->where('status', self::STATUS_FULLY_DISPENSED);
    }

    /**
     * Scope to get partially dispensed items
     */
    public function scopePartiallyDispensed($query)
    {
        return $query->where('status', self::STATUS_PARTIALLY_DISPENSED);
    }

    /**
     * Scope to filter by medication
     */
    public function scopeByMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    /**
     * Scope to filter by prescription
     */
    public function scopeByPrescription($query, $prescriptionId)
    {
        return $query->where('prescription_id', $prescriptionId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('dispensed_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by dispensed user
     */
    public function scopeByDispensedBy($query, $userId)
    {
        return $query->where('dispensed_by', $userId);
    }

    // Accessor methods

    /**
     * Get remaining quantity to dispense
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity_prescribed - $this->quantity_dispensed;
    }

    /**
     * Get dispensing progress percentage
     */
    public function getDispensingProgressAttribute()
    {
        if ($this->quantity_prescribed == 0) {
            return 0;
        }
        
        return round(($this->quantity_dispensed / $this->quantity_prescribed) * 100, 2);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PARTIALLY_DISPENSED => 'Partially Dispensed',
            self::STATUS_FULLY_DISPENSED => 'Fully Dispensed',
            self::STATUS_CANCELLED => 'Cancelled'
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get frequency display name
     */
    public function getFrequencyDisplayAttribute()
    {
        $frequencies = [
            self::FREQUENCY_ONCE_DAILY => 'Once Daily',
            self::FREQUENCY_TWICE_DAILY => 'Twice Daily',
            self::FREQUENCY_THREE_TIMES_DAILY => 'Three Times Daily',
            self::FREQUENCY_FOUR_TIMES_DAILY => 'Four Times Daily',
            self::FREQUENCY_EVERY_6_HOURS => 'Every 6 Hours',
            self::FREQUENCY_EVERY_8_HOURS => 'Every 8 Hours',
            self::FREQUENCY_EVERY_12_HOURS => 'Every 12 Hours',
            self::FREQUENCY_AS_NEEDED => 'As Needed'
        ];

        return $frequencies[$this->frequency] ?? 'Unknown';
    }

    /**
     * Get total cost of prescribed quantity
     */
    public function getTotalPrescribedCostAttribute()
    {
        return $this->quantity_prescribed * $this->unit_cost;
    }

    /**
     * Get total cost of dispensed quantity
     */
    public function getTotalDispensedCostAttribute()
    {
        return $this->quantity_dispensed * $this->unit_cost;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_PARTIALLY_DISPENSED:
                return 'info';
            case self::STATUS_FULLY_DISPENSED:
                return 'success';
            case self::STATUS_CANCELLED:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get complete dosage instructions
     */
    public function getCompleteDosageAttribute()
    {
        $parts = [];
        
        if ($this->dosage_instructions) {
            $parts[] = $this->dosage_instructions;
        }
        
        if ($this->frequency) {
            $parts[] = $this->frequency_display;
        }
        
        if ($this->duration_days) {
            $parts[] = "for {$this->duration_days} days";
        }
        
        return implode(', ', $parts);
    }

    // Helper methods

    /**
     * Check if item is fully dispensed
     */
    public function isFullyDispensed()
    {
        return $this->status === self::STATUS_FULLY_DISPENSED;
    }

    /**
     * Check if item is partially dispensed
     */
    public function isPartiallyDispensed()
    {
        return $this->status === self::STATUS_PARTIALLY_DISPENSED;
    }

    /**
     * Check if item is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if item is cancelled
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if item can be dispensed
     */
    public function canBeDispensed()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PARTIALLY_DISPENSED]);
    }

    /**
     * Dispense medication
     */
    public function dispense($quantity, $locationStock, $dispensedBy, $notes = null)
    {
        if (!$this->canBeDispensed()) {
            throw new \Exception('Item cannot be dispensed in current status');
        }

        if ($quantity > $this->remaining_quantity) {
            throw new \Exception('Cannot dispense more than remaining quantity');
        }

        $this->quantity_dispensed += $quantity;
        $this->location_stock_id = $locationStock->id;
        $this->grn_item_id = $locationStock->grn_item_id;
        $this->unit_cost = $locationStock->unit_cost;
        $this->dispensed_by = $dispensedBy;
        $this->dispensed_at = now();
        
        if ($notes) {
            $this->notes = $this->notes ? $this->notes . "\n" . $notes : $notes;
        }

        // Update status based on dispensed quantity
        if ($this->quantity_dispensed >= $this->quantity_prescribed) {
            $this->status = self::STATUS_FULLY_DISPENSED;
        } else {
            $this->status = self::STATUS_PARTIALLY_DISPENSED;
        }

        return $this->save();
    }

    /**
     * Cancel prescription item
     */
    public function cancel($reason = null)
    {
        $this->status = self::STATUS_CANCELLED;
        
        if ($reason) {
            $this->notes = $this->notes ? $this->notes . "\nCancelled: " . $reason : "Cancelled: " . $reason;
        }
        
        return $this->save();
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PARTIALLY_DISPENSED => 'Partially Dispensed',
            self::STATUS_FULLY_DISPENSED => 'Fully Dispensed',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }

    /**
     * Get all available frequencies
     */
    public static function getFrequencies()
    {
        return [
            self::FREQUENCY_ONCE_DAILY => 'Once Daily',
            self::FREQUENCY_TWICE_DAILY => 'Twice Daily',
            self::FREQUENCY_THREE_TIMES_DAILY => 'Three Times Daily',
            self::FREQUENCY_FOUR_TIMES_DAILY => 'Four Times Daily',
            self::FREQUENCY_EVERY_6_HOURS => 'Every 6 Hours',
            self::FREQUENCY_EVERY_8_HOURS => 'Every 8 Hours',
            self::FREQUENCY_EVERY_12_HOURS => 'Every 12 Hours',
            self::FREQUENCY_AS_NEEDED => 'As Needed'
        ];
    }

    /**
     * Get dispensing statistics for a period
     */
    public static function getDispensingStats($startDate, $endDate)
    {
        return [
            'total_items' => self::whereBetween('created_at', [$startDate, $endDate])->count(),
            'dispensed_items' => self::dispensed()->whereBetween('dispensed_at', [$startDate, $endDate])->count(),
            'pending_items' => self::pending()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_prescribed_value' => self::whereBetween('created_at', [$startDate, $endDate])->sum('total_prescribed_cost'),
            'total_dispensed_value' => self::dispensed()->whereBetween('dispensed_at', [$startDate, $endDate])->sum('total_dispensed_cost'),
        ];
    }
}
