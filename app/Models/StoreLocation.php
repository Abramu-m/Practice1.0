<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    use HasFactory;

    protected $table = 'store_locations';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'manager_name',
        'manager_contact',
        'can_request',
        'can_issue',
        'can_receive',
        'requires_approval',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'can_request' => 'boolean',
        'can_issue' => 'boolean',
        'can_receive' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    
    /**
     * Get parent location
     */
    public function parent()
    {
        return $this->belongsTo(StoreLocation::class, 'parent_id');
    }

    /**
     * Get child locations
     */
    public function children()
    {
        return $this->hasMany(StoreLocation::class, 'parent_id');
    }

    /**
     * Get all descendant locations
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get requisitions from this location
     */
    public function requisitions()
    {
        return $this->hasMany(StoreRequisition::class, 'requesting_location_id');
    }

    /**
     * Get stock items in this location
     */
    public function stockItems()
    {
        return $this->hasMany(StoreLocationStock::class, 'location_id');
    }

    // Scopes
    
    /**
     * Scope for active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for main stores
     */
    public function scopeMainStore($query)
    {
        return $query->where('type', 'main_store');
    }

    /**
     * Scope for sub stores
     */
    public function scopeSubStore($query)
    {
        return $query->where('type', 'sub_store');
    }

    /**
     * Scope for root locations (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Methods
    
    /**
     * Get current stock for a specific item
     */
    public function getCurrentStock($itemType, $itemId)
    {
        return $this->stockItems()
                    ->where('medication_id', $itemId)
                    ->sum('quantity');
    }

    /**
     * Get all medications in this location
     */
    public function getMedications()
    {
        return $this->stockItems()
                    ->with('medication')
                    ->get()
                    ->pluck('medication')
                    ->unique('id');
    }

    /**
     * Get all consumables in this location using unified medication system
     */
    public function getConsumables()
    {
        return $this->stockItems()
                    ->with(['medication' => function ($query) {
                        $query->whereHas('storeCategory', function ($q) {
                            $q->where('description', 'Consumables');
                        });
                    }])
                    ->get()
                    ->pluck('medication')
                    ->filter() // Remove null values where medication doesn't match category
                    ->unique('id');
    }

    /**
     * Get location hierarchy path
     */
    public function getHierarchyPath()
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->code . ')';
    }

    /**
     * Check if location has stock
     */
    public function hasStock()
    {
        return $this->stockItems()->where('quantity', '>', 0)->exists();
    }
}
