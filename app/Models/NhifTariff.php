<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NhifTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_code',
        'item_code',
        'item_name',
        'package_id',
        'scheme_id',
        'unit_price',
        'is_restricted',
        'is_excluded',
        'excluded_for_products',
        'last_updated',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_restricted' => 'boolean',
        'is_excluded' => 'boolean',
        'last_updated' => 'datetime',
        'excluded_for_products' => 'array',
    ];

    /**
     * Check if service is available for a specific product
     */
    public function isAvailableForProduct(string $productCode): bool
    {
        if (!$this->is_excluded) {
            return true;
        }

        if (!$this->excluded_for_products) {
            return true;
        }

        return !in_array($productCode, $this->excluded_for_products);
    }

    /**
     * Scope to filter by facility code
     */
    public function scopeForFacility($query, string $facilityCode)
    {
        return $query->where('facility_code', $facilityCode);
    }

    /**
     * Scope to filter by scheme ID
     */
    public function scopeForScheme($query, int $schemeId)
    {
        return $query->where('scheme_id', $schemeId);
    }

    /**
     * Scope to filter non-restricted items
     */
    public function scopeNonRestricted($query)
    {
        return $query->where('is_restricted', false);
    }
}
