<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationConsumable extends Model
{
    use HasFactory;

    protected $table = 'investigation_consumables';

    protected $fillable = [
        'medical_service_id',
        'medication_id',
        'quantity_required',
        'is_optional',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships

    /**
     * Get the medical service that this consumable requirement belongs to
     */
    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
    }

    /**
     * Get the medication/consumable item
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
    public function consumable()
    {
        return $this->belongsTo(Medication::class, 'item_id')->where('item_type', 'consumable');
    }

    // Scopes

    /**
     * Scope to get active consumables only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get required consumables only (not optional)
     */
    public function scopeRequired($query)
    {
        return $query->where('is_optional', false);
    }

    /**
     * Scope to get optional consumables only
     */
    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    /**
     * Scope to filter by item type
     */
    public function scopeByItemType($query, $itemType)
    {
        return $query->where('item_type', $itemType);
    }

    // Accessors

    /**
     * Get the item (medication or consumable) based on item_type
     */
    public function getItemAttribute()
    {
        if ($this->item_type === 'medication') {
            return $this->medication;
        } elseif ($this->item_type === 'consumable') {
            return $this->consumable;
        }
        
        return null;
    }

    /**
     * Get the item name
     */
    public function getItemNameAttribute()
    {
        $item = $this->item;
        return $item ? ($item->generic_name ?? $item->name) : 'Unknown Item';
    }

    /**
     * Get the item unit
     */
    public function getItemUnitAttribute()
    {
        $item = $this->item;
        return $item ? ($item->unit ?? '') : '';
    }

    /**
     * Check if sufficient stock is available
     */
    public function checkStockAvailability($locationId = null)
    {
        if (!$this->item) {
            return [
                'available' => false,
                'current_stock' => 0,
                'required' => $this->quantity_required,
                'shortage' => $this->quantity_required,
                'message' => 'Item not found'
            ];
        }

        // Get stock from store_locations_stock table
        $stockQuery = StoreLocationStock::where('medication_id', $this->item_id)
            ->where('status', 'active')
            ->where('quantity_current', '>', 0);

        if ($locationId) {
            $stockQuery->where('location_id', $locationId);
        }

        $totalStock = $stockQuery->sum('quantity_current');
        $available = $totalStock >= $this->quantity_required;
        $shortage = $available ? 0 : ($this->quantity_required - $totalStock);

        return [
            'available' => $available,
            'current_stock' => $totalStock,
            'required' => $this->quantity_required,
            'shortage' => $shortage,
            'message' => $available ? 'Stock available' : "Insufficient stock (need {$shortage} more units)"
        ];
    }

    /**
     * Get stock details by location
     */
    public function getStockByLocation()
    {
        if (!$this->item) {
            return collect();
        }

        return StoreLocationStock::with('storeLocation')
            ->where('medication_id', $this->item_id)
            ->where('status', 'active')
            ->where('quantity_current', '>', 0)
            ->get()
            ->map(function ($stock) {
                return [
                    'location_id' => $stock->location_id,
                    'location_name' => $stock->storeLocation->name ?? 'Unknown Location',
                    'available_quantity' => $stock->quantity_current,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date,
                    'unit_cost' => $stock->unit_cost
                ];
            });
    }

    // Helper Methods

    /**
     * Create consumable assignment for an investigation
     */
    public static function assignToInvestigation($investigationId, $items)
    {
        $assignments = [];
        
        foreach ($items as $item) {
            $assignment = self::create([
                'investigation_id' => $investigationId,
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'quantity_required' => $item['quantity_required'],
                'is_optional' => $item['is_optional'] ?? false,
                'notes' => $item['notes'] ?? null,
                'is_active' => true
            ]);
            
            $assignments[] = $assignment;
        }
        
        return $assignments;
    }

    /**
     * Check all consumables for an investigation
     */
    public static function checkInvestigationStock($investigationId, $locationId = null)
    {
        $consumables = self::active()
            ->where('investigation_id', $investigationId)
            ->get();

        $results = [
            'can_proceed' => true,
            'required_items_available' => true,
            'optional_items_available' => true,
            'total_items' => $consumables->count(),
            'available_items' => 0,
            'shortage_items' => [],
            'details' => []
        ];

        foreach ($consumables as $consumable) {
            $availability = $consumable->checkStockAvailability($locationId);
            
            $results['details'][] = [
                'item_name' => $consumable->item_name,
                'item_type' => $consumable->item_type,
                'quantity_required' => $consumable->quantity_required,
                'is_optional' => $consumable->is_optional,
                'availability' => $availability
            ];

            if ($availability['available']) {
                $results['available_items']++;
            } else {
                if (!$consumable->is_optional) {
                    $results['required_items_available'] = false;
                    $results['can_proceed'] = false;
                } else {
                    $results['optional_items_available'] = false;
                }
                
                $results['shortage_items'][] = [
                    'item_name' => $consumable->item_name,
                    'required' => $consumable->quantity_required,
                    'available' => $availability['current_stock'],
                    'shortage' => $availability['shortage'],
                    'is_optional' => $consumable->is_optional
                ];
            }
        }

        return $results;
    }
}
