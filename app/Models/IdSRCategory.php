<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdSRCategory extends Model
{
    protected $table = 'idsr_categories';

    protected $fillable = [
        'name',
        'description',
        'icd_codes',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public $timestamps = false;

    /**
     * Scope: Active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get ICD codes as array
     */
    public function getIcdCodesArray()
    {
        if (!$this->icd_codes) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $this->icd_codes)));
    }
}
