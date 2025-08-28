<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MedicationLedger extends Model
{
    use HasFactory;

    protected $table = 'medication_ledger';

    protected $fillable = [
        'medication_id',
        'grn_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'unit_cost',
        'quantity_received',
        'status',
        'location_id',
        'notes'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
        'quantity_received' => 'integer'
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_EXHAUSTED = 'exhausted';
    const STATUS_DAMAGED = 'damaged';

    // Relationships
    
    /**
     * Get the medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the GRN
     */
    public function grn()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    /**
     * Get the GRN Item that created this ledger entry
     */
    public function grnItem()
    {
        return $this->hasOne(GoodsReceivedNoteItem::class, 'grn_id', 'grn_id')
                    ->where('item_id', $this->medication_id)
                    ->where('batch_number', $this->batch_number);
    }

    /**
     * Get the storage location
     */
    public function location()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    // Scopes
    
    /**
     * Scope for active batches
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for expired batches
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                     ->orWhere('expiry_date', '<', now());
    }

    /**
     * Scope for expiring soon (within 30 days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '>=', now())
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for available stock
     */
    public function scopeAvailable($query)
    {
        return $query->where('quantity_received', '>', 0)
                     ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for specific medication
     */
    public function scopeForMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    // Methods
    
    /**
     * Check if batch is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if batch is expiring soon
     */
    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30;
    }

    /**
     * Get available quantity
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity_received;
    }

    /**
     * Check if batch has stock
     */
    public function getHasStockAttribute()
    {
        return $this->quantity_received > 0;
    }

    /**
     * Get batch status color for UI
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return $this->is_expiring_soon ? 'warning' : 'success';
            case self::STATUS_EXPIRED:
                return 'danger';
            case self::STATUS_EXHAUSTED:
                return 'secondary';
            case self::STATUS_DAMAGED:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Reduce quantity (for dispensing/usage)
     */
    public function reduceQuantity($quantity)
    {
        if ($this->quantity_received >= $quantity) {
            $this->quantity_received -= $quantity;
            
            if ($this->quantity_received <= 0) {
                $this->status = self::STATUS_EXHAUSTED;
            }
            
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Adjust quantity (positive or negative)
     */
    public function adjustQuantity($quantity, $reason = null)
    {
        $this->quantity_received += $quantity;
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Adjustment: $reason";
        }
        
        if ($this->quantity_received <= 0) {
            $this->status = self::STATUS_EXHAUSTED;
        }
        
        $this->save();
    }

    /**
     * Mark batch as damaged
     */
    public function markDamaged($reason = null)
    {
        $this->status = self::STATUS_DAMAGED;
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Damaged: $reason";
        }
        
        $this->save();
    }

    /**
     * Auto-update expired batches
     */
    public static function updateExpiredBatches()
    {
        return self::where('expiry_date', '<', now())
                   ->where('status', self::STATUS_ACTIVE)
                   ->update([
                       'status' => self::STATUS_EXPIRED
                   ]);
    }
}
