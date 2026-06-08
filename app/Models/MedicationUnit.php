<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationUnit extends Model
{
    use HasFactory;

    protected $table = 'medication_units';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'unit_name',
        'unit_code',
        'unit_symbol',
        'description',
        'unit_type',
        'base_conversion_factor',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_conversion_factor' => 'decimal:4',
        'display_order' => 'integer'
    ];

    /**
     * Get medications using this unit
     */
    public function medications()
    {
        return $this->hasMany(Medication::class, 'dispensing_unit_id');
    }

    /**
     * Check if unit is active
     */
    public function isActive()
    {
        return $this->is_active === true;
    }

    /**
     * Get display name with symbol
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->unit_name;
        if ($this->unit_symbol) {
            $name .= ' (' . $this->unit_symbol . ')';
        }
        return $name;
    }

    /**
     * Scope for active units
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }

    /**
     * Scope by unit type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('unit_type', $type);
    }

    /**
     * Get unit type options
     */
    public static function getUnitTypeOptions()
    {
        return [
            'dosage' => 'Dosage',
            'volume' => 'Volume',
            'weight' => 'Weight',
            'form' => 'Form',
            'other' => 'Other'
        ];
    }

    /**
     * Get unit types as array for validation
     */
    public static function getUnitTypes()
    {
        return array_keys(self::getUnitTypeOptions());
    }
}
