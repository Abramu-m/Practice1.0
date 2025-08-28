<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRequisitionItem extends Model
{
    use HasFactory;

    protected $table = 'store_requisition_items';

    protected $fillable = [
        'requisition_id',
        'item_type',
        'item_id',
        'requested_quantity',
        'approved_quantity',
        'issued_quantity',
        'unit_cost',
        'total_cost',
        'status',
        'remarks'
    ];

    protected $casts = [
        'requested_quantity' => 'integer',
        'approved_quantity' => 'integer',
        'issued_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    // Relationships
    
    /**
     * Get the requisition this item belongs to
     */
    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'requisition_id');
    }

    /**
     * Get the item (polymorphic relationship)
     */
    public function item()
    {
        return $this->morphTo();
    }

    /**
     * Get medication if item is a medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class, 'item_id');
    }
    
    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved items
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for issued items
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope for medication items
     */
    public function scopeMedications($query)
    {
        return $query->where('item_type', 'medication');
    }

    /**
     * Scope for consumable items
     */
    public function scopeConsumables($query)
    {
        return $query->where('item_type', 'consumable');
    }

    // Methods
    
    /**
     * Get the item name
     */
    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->name : 'Unknown Item';
    }

    /**
     * Get the item code
     */
    public function getItemCodeAttribute()
    {
        return $this->item ? $this->item->code : 'N/A';
    }

    /**
     * Get the item unit
     */
    public function getItemUnitAttribute()
    {
        return $this->item && $this->item->unit ? $this->item->unit->name : 'N/A';
    }

    /**
     * Calculate total cost based on approved quantity
     */
    public function calculateTotalCost()
    {
        $this->total_cost = $this->approved_quantity * $this->unit_cost;
        return $this->total_cost;
    }

    /**
     * Get pending quantity (approved - issued)
     */
    public function getPendingQuantityAttribute()
    {
        return $this->approved_quantity - $this->issued_quantity;
    }

    /**
     * Check if item is fully issued
     */
    public function isFullyIssued()
    {
        return $this->issued_quantity >= $this->approved_quantity;
    }

    /**
     * Check if item is partially issued
     */
    public function isPartiallyIssued()
    {
        return $this->issued_quantity > 0 && $this->issued_quantity < $this->approved_quantity;
    }

    /**
     * Update status based on issued quantity
     */
    public function updateStatus()
    {
        if ($this->issued_quantity >= $this->approved_quantity) {
            $this->status = 'issued';
        } elseif ($this->issued_quantity > 0) {
            $this->status = 'partially_issued';
        } elseif ($this->approved_quantity > 0) {
            $this->status = 'approved';
        } else {
            $this->status = 'pending';
        }
        
        $this->save();
    }
}
