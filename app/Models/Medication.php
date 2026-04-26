<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $table = 'medications'; // Better than 'drugs'
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'generic_name',
        'brand_name',
        'strength',
        'formulation_id',
        'dispensing_unit_id',
        'description',
        'stock_quantity',
        'minimum_stock_level',
        'is_active',
        'category_id',
        'reorder_level',
        'maximum_stock_level',
        'requires_prescription',
        'is_controlled',
        'storage_conditions'
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'minimum_stock_level' => 'integer',
        'reorder_level' => 'integer',
        'maximum_stock_level' => 'integer',
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean',
        'is_controlled' => 'boolean'
    ];

    // Status constants
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    /**
     * Get prescriptions for this medication
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get store category
     */
    public function storeCategory()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id');
    }

    /**
     * Get medication formulation
     */
    public function formulation()
    {
        return $this->belongsTo(MedicationFormulation::class, 'formulation_id');
    }

    /**
     * Get dispensing unit
     */
    public function dispensingUnit()
    {
        return $this->belongsTo(MedicationUnit::class, 'dispensing_unit_id');
    }

    /**
     * Get requisition items for this medication
     */
    public function requisitionItems()
    {
        return $this->morphMany(StoreRequisitionItem::class, 'item');
    }

    /**
     * Get medication pricing records
     */
    public function pricing()
    {
        return $this->hasMany(MedicationPricing::class);
    }

    /**
     * Get medication ledger entries (batches)
     */
    public function ledgerEntries()
    {
        return $this->hasMany(MedicationLedger::class);
    }

    /**
     * Get active ledger entries
     */
    public function activeBatches()
    {
        return $this->hasMany(MedicationLedger::class)->active();
    }

    /**
     * All store location stock rows for this medication
     */
    public function locationStocks()
    {
        return $this->hasMany(StoreLocationStock::class);
    }

    /**
     * Active (non-depleted) store location stock rows
     */
    public function activeLocationStocks()
    {
        return $this->hasMany(StoreLocationStock::class)
            ->where('status', StoreLocationStock::STATUS_ACTIVE);
    }

    /**
     * Active stock rows at a specific location (matched by name pattern)
     */
    public function stockAtLocation(string $locationName)
    {
        return $this->hasMany(StoreLocationStock::class)
            ->where('status', StoreLocationStock::STATUS_ACTIVE)
            ->whereHas('location', fn($q) => $q->where('name', 'like', "%{$locationName}%"));
    }

    /**
     * Sum of active stock across all locations
     */
    public function getTotalStock(): float|int
    {
        return $this->activeLocationStocks()->sum('quantity');
    }

    /**
     * Sum of active stock at a specific location (matched by name pattern)
     */
    public function getTotalStockAt(string $locationName): float|int
    {
        return $this->hasMany(StoreLocationStock::class)
            ->where('status', StoreLocationStock::STATUS_ACTIVE)
            ->join('store_locations', 'store_locations_stock.location_id', '=', 'store_locations.id')
            ->where('store_locations.name', 'like', "%{$locationName}%")
            ->sum('store_locations_stock.quantity');
    }

    /**
     * Get price for specific patient category
     */
    public function getPriceForCategory($categoryId)
    {
        return $this->pricing()
                    ->active()
                    ->current()
                    ->forMedicationAndCategory($this->id, $categoryId)
                    ->first();
    }

    /**
     * Check if medication is in stock
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if medication is low on stock
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Check if medication is expired (based on batches)
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->activeBatches()->where('expiry_date', '<', now()->toDateString())->exists();
    }

    /**
     * Check if medication is expiring soon (within 30 days)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->activeBatches()->where('expiry_date', '<=', now()->addDays(30)->toDateString())->exists();
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->is_expired) return 'Expired';
        if ($this->is_expiring_soon) return 'Expiring Soon';
        if (!$this->is_in_stock) return 'Out of Stock';
        if ($this->is_low_stock) return 'Low Stock';
        return 'In Stock';
    }

    /**
     * Get stock status badge class
     */
    public function getStockBadgeClassAttribute(): string
    {
        switch ($this->stock_status) {
            case 'Expired': return 'badge-danger';
            case 'Expiring Soon': return 'badge-warning';
            case 'Out of Stock': return 'badge-danger';
            case 'Low Stock': return 'badge-warning';
            case 'In Stock': return 'badge-success';
            default: return 'badge-secondary';
        }
    }

    /**
     * Scope for active medications
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**

     * Scope for medications in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope for low stock medications
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= reorder_level');
    }

    /**
     * Scope for expired medications (based on batches)
     */
    public function scopeExpired($query)
    {
        return $query->whereHas('activeBatches', function ($q) {
            $q->where('expiry_date', '<', now()->toDateString());
        });
    }

    /**
     * Scope for medications expiring soon
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereHas('activeBatches', function ($q) {
            $q->where('expiry_date', '<=', now()->addDays(30)->toDateString());
        });
    }

    /**
     * Get current stock from all active batches
     */
    public function getCurrentStockFromBatches()
    {
        return $this->activeBatches()->sum('quantity_received');
    }

    /**
     * Get earliest expiry date from active batches
     */
    public function getEarliestExpiryDate()
    {
        return $this->activeBatches()->min('expiry_date');
    }

    /**
     * Get average unit cost from active batches
     */
    public function getAverageUnitCost()
    {
        return $this->activeBatches()->avg('unit_cost');
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->generic_name;
    }

    /**
     * Get name attribute (alias for display_name)
     */
    public function getNameAttribute(): string
    {
        return $this->generic_name;
    }

    /** */
    public function isMedication(): bool
    {
        return $this->category_id == 1;
    }

     public function isConsumable(): bool
    {
        return $this->category_id == 2;
    }   

    /**
     * Get total stock quantity in Main Pharmacy
     */
    public function getTotalStockInMainPharmacy(): float|int
    {
        return $this->getTotalStockAt('Main Pharmacy');
    }

    /**
     * Get total stock quantity in Lab
     */
    public function getTotalStockInLab(): float|int
    {
        return $this->getTotalStockAt('Lab');
    }

    /**
     * Get total stock quantity in Ward
     */
    public function getTotalStockInWard(): float|int
    {
        return $this->getTotalStockAt('Ward');
    }
}
