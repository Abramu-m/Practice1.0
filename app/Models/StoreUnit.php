<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreUnit extends Model
{
    use HasFactory;

    protected $table = 'store_units';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Type constants
    const TYPE_STORE = 'store';
    const TYPE_DISPENSING = 'dispensing';
    const TYPE_BOTH = 'both';

    // Relationships

    /**
     * Get GRN items that use this as store unit
     */
    public function grnItemsAsStore()
    {
        return $this->hasMany(GoodsReceivedNoteItem::class, 'store_unit_id');
    }

    /**
     * Get GRN items that use this as dispensing unit
     */
    public function grnItemsAsDispensing()
    {
        return $this->hasMany(GoodsReceivedNoteItem::class, 'dispensing_unit_id');
    }

    /**
     * Get medications that use this unit
     */
    public function medications()
    {
        return $this->belongsToMany(Medication::class, 'goods_received_note_items', 'store_unit_id', 'item_id');
    }

    // Scopes

    /**
     * Scope for active units
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for store units
     */
    public function scopeStore($query)
    {
        return $query->whereIn('type', [self::TYPE_STORE, self::TYPE_BOTH]);
    }

    /**
     * Scope for dispensing units
     */
    public function scopeDispensing($query)
    {
        return $query->whereIn('type', [self::TYPE_DISPENSING, self::TYPE_BOTH]);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators

    /**
     * Get the display name with code
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->code . ')';
    }

    /**
     * Check if unit can be used for storing
     */
    public function canBeUsedForStore()
    {
        return in_array($this->type, [self::TYPE_STORE, self::TYPE_BOTH]);
    }

    /**
     * Check if unit can be used for dispensing
     */
    public function canBeUsedForDispensing()
    {
        return in_array($this->type, [self::TYPE_DISPENSING, self::TYPE_BOTH]);
    }

    // Static methods

    /**
     * Get type options for forms
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_STORE => 'Store Only',
            self::TYPE_DISPENSING => 'Dispensing Only',
            self::TYPE_BOTH => 'Both Store & Dispensing'
        ];
    }

    /**
     * Get all active store units
     */
    public static function getStoreUnits()
    {
        return static::active()->store()->orderBy('name')->get();
    }

    /**
     * Get all active dispensing units
     */
    public static function getDispensingUnits()
    {
        return static::active()->dispensing()->orderBy('name')->get();
    }
}
