<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNoteItem extends Model
{
    use HasFactory;

    protected $table = 'goods_received_note_items';

    protected $fillable = [
        'grn_id',
        'item_id',
        'store_unit_id',
        'dispensing_unit_id',
        'conversion_factor',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'received_quantity',
        'unit_cost',
        'total_cost',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'net_amount',
        'store_quantity',
        'store_unit_cost',
        'notes'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_quantity' => 'decimal:2',
        'conversion_factor' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'store_quantity' => 'decimal:2',
        'store_unit_cost' => 'decimal:4'
    ];

    // Item type constants
    const ITEM_TYPE_MEDICATION = 'medication';
    const ITEM_TYPE_CONSUMABLE = 'consumable';

    // Relationships

    /**
     * Get the goods received note
     */
    public function goodsReceivedNote()
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    /**
     * Get the store location (DEPRECATED - field removed from database)
     */
    public function storeLocation()
    {
        // This relationship is deprecated as store_location_id was removed
        return null;
    }

    /**
     * Get the item (medication or consumable)
     */
    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    /**
     * Get the medication (assuming all items are medications for now)
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class, 'item_id');
    }

    /**
     * Get the consumable (for future use)
     */
    public function consumable()
    {
        // Return a proper relationship that will never match anything
        // This prevents the addEagerConstraints error while keeping the interface consistent
        return $this->belongsTo(Medication::class, 'item_id')->whereRaw('1 = 0');
    }

    /**
     * Get the store unit
     */
    public function storeUnit()
    {
        return $this->belongsTo(StoreUnit::class, 'store_unit_id');
    }

    /**
     * Get the dispensing unit
     */
    public function dispensingUnit()
    {
        return $this->belongsTo(StoreUnit::class, 'dispensing_unit_id');
    }

    // Scopes

    /**
     * Scope to filter by item type
     */
    public function scopeByItemType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    /**
     * Scope to get medications only
     */
    public function scopeMedications($query)
    {
        return $query->where('item_type', self::ITEM_TYPE_MEDICATION);
    }

    /**
     * Scope to get consumables only
     */
    public function scopeConsumables($query)
    {
        return $query->where('item_type', self::ITEM_TYPE_CONSUMABLE);
    }

    /**
     * Scope to filter by expiry date
     */
    public function scopeExpiringBefore($query, $date)
    {
        return $query->where('expiry_date', '<=', $date);
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
     * Get display name for the item
     */
    public function getItemNameAttribute()
    {
        if ($this->item_type === self::ITEM_TYPE_MEDICATION) {
            return $this->medication?->name ?? 'Unknown Medication';
        }
        
        // For consumables, we'll need to implement when consumables model exists
        return 'Consumable Item';
    }

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
            case 'expiring_soon':
                return 'warning';
            case 'expiring_warning':
                return 'info';
            default:
                return 'success';
        }
    }

    /**
     * Calculate total value after discounts and taxes
     */
    public function getTotalValueAttribute()
    {
        $subtotal = $this->received_quantity * $this->unit_cost;
        $afterDiscount = $subtotal - $this->discount_amount;
        $afterTax = $afterDiscount + $this->tax_amount;
        
        return $afterTax;
    }

    /**
     * Check if item is expiring within given days
     */
    public function isExpiringWithin($days)
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days && $this->expiry_date->isFuture();
    }

    /**
     * Check if item is expired
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Calculate dispensing quantity from store quantity
     */
    public function getDispensingQuantityAttribute()
    {
        return $this->received_quantity * $this->conversion_factor;
    }

    /**
     * Calculate store quantity from dispensing quantity
     */
    public function calculateStoreQuantityFromDispensing($dispensingQuantity)
    {
        if ($this->conversion_factor == 0) {
            return 0;
        }
        return $dispensingQuantity / $this->conversion_factor;
    }

    /**
     * Get the unit conversion display text
     */
    public function getConversionDisplayAttribute()
    {
        if (!$this->storeUnit || !$this->dispensingUnit) {
            return 'No conversion';
        }
        
        return "1 {$this->storeUnit->name} = {$this->conversion_factor} {$this->dispensingUnit->name}";
    }

    /**
     * Check if units are properly configured
     */
    public function hasValidUnits()
    {
        return $this->store_unit_id && $this->dispensing_unit_id && $this->conversion_factor > 0;
    }

    // Methods for medication ledger integration

    /**
     * Create medication ledger entry from this GRN item
     */
    public function createLedgerEntry()
    {
        if ($this->item_type !== self::ITEM_TYPE_MEDICATION) {
            return null;
        }

        return MedicationLedger::create([
            'medication_id' => $this->item_id,
            'grn_id' => $this->grn_id,
            'batch_number' => $this->batch_number,
            'manufacture_date' => $this->manufacture_date,
            'expiry_date' => $this->expiry_date,
            'unit_cost' => $this->unit_cost,
            'quantity_received' => $this->received_quantity,
            'quantity_current' => $this->received_quantity,
            'quantity_dispensed' => 0,
            'quantity_adjusted' => 0,
            'quantity_damaged' => 0,
            'quantity_expired' => 0,
            'status' => MedicationLedger::STATUS_ACTIVE,
            'supplier_batch_number' => $this->batch_number,
            'location_id' => $this->store_location_id,
            'notes' => $this->notes
        ]);
    }
}
