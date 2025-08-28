<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocationStock extends Model
{
    use HasFactory;

    protected $table = 'store_locations_stock';

    protected $fillable = [
        'location_id',
        'medication_id',
        'requisition_id',
        'requisition_item_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'quantity',
        'unit_cost',
        'status'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DEPLETED = 'depleted';

    // Relationships

    /**
     * Get the store location
     */
    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    /**
     * Get the medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the requisition
     */
    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class);
    }

    /**
     * Get the requisition item
     */
    public function requisitionItem()
    {
        return $this->belongsTo(StoreRequisitionItem::class);
    }

    // Scopes

    /**
     * Scope to get active stock only
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get expired stock
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere('expiry_date', '<', now());
    }

    /**
     * Scope to get stock expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now())
                    ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get depleted stock
     */
    public function scopeDepleted($query)
    {
        return $query->where('status', self::STATUS_DEPLETED)
                    ->orWhere('quantity', '<=', 0);
    }

    /**
     * Scope to get low stock items
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('quantity', '<=', $threshold)
                    ->where('quantity', '>', 0)
                    ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter by location
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope to filter by medication
     */
    public function scopeByMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    /**
     * Scope to filter by batch number
     */
    public function scopeByBatch($query, $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    // Accessor methods

    /**
     * Get expiry status
     */
    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) {
            return 'no_expiry';
        }

        $now = now();
        $expiryDate = $this->expiry_date;

        if ($expiryDate->isPast()) {
            return 'expired';
        } elseif ($expiryDate->diffInDays($now) <= 7) {
            return 'expiring_critical';
        } elseif ($expiryDate->diffInDays($now) <= 30) {
            return 'expiring_soon';
        } elseif ($expiryDate->diffInDays($now) <= 90) {
            return 'expiring_warning';
        }

        return 'good';
    }

    /**
     * Get expiry status color for UI
     */
    public function getExpiryStatusColorAttribute()
    {
        switch ($this->expiry_status) {
            case 'expired':
                return 'danger';
            case 'expiring_critical':
                return 'danger';
            case 'expiring_soon':
                return 'warning';
            case 'expiring_warning':
                return 'info';
            default:
                return 'success';
        }
    }

    /**
     * Get stock status color for UI
     */
    public function getStockStatusColorAttribute()
    {
        if ($this->quantity <= 0) {
            return 'danger';
        } elseif ($this->quantity <= 10) {
            return 'warning';
        } elseif ($this->quantity <= 50) {
            return 'info';
        }

        return 'success';
    }

    /**
     * Get available quantity for dispensing
     */
    public function getAvailableQuantityAttribute()
    {
        return max(0, $this->quantity);
    }

    // Helper methods

    /**
     * Check if stock is available for given quantity
     */
    public function isAvailable($quantity)
    {
        return $this->status === self::STATUS_ACTIVE 
            && $this->quantity >= $quantity 
            && !$this->isExpired();
    }

    /**
     * Check if stock is expired
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if stock is expiring within given days
     */
    public function isExpiringWithin($days)
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days && $this->expiry_date->isFuture();
    }

    /**
     * Update stock quantity and status
     */
    public function updateQuantity($newQuantity, $reason = null)
    {
        $this->quantity = $newQuantity;
        
        // Update status based on quantity and expiry
        if ($newQuantity <= 0) {
            $this->status = self::STATUS_DEPLETED;
        } elseif ($this->isExpired()) {
            $this->status = self::STATUS_EXPIRED;
        } else {
            $this->status = self::STATUS_ACTIVE;
        }
        
        return $this->save();
    }

    /**
     * Reduce quantity (similar to medication ledger)
     */
    public function reduceQuantity($quantity, $reason = null)
    {
        if ($this->quantity >= $quantity) {
            $this->quantity -= $quantity;
            
            if ($this->quantity <= 0) {
                $this->status = self::STATUS_DEPLETED;
            }
            
            return $this->save();
        }
        return false;
    }

    /**
     * Discard quantity from stock
     */
    public function discard($quantity, $reason = 'expired')
    {
        if ($this->quantity_current < $quantity) {
            throw new \Exception('Cannot discard more than available quantity');
        }

        $this->quantity_current -= $quantity;
        $this->quantity_discarded += $quantity;
        
        return $this->updateQuantity($this->quantity_current, 'discarded');
    }
}
