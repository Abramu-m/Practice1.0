<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsdCode extends Model
{
    protected $fillable = [
        'code',
        'name',
        'unit',
        'category',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active MSD codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Search MSD codes by code or name
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'LIKE', "%{$term}%")
              ->orWhere('name', 'LIKE', "%{$term}%");
        });
    }

    public function medications()
    {
        return $this->hasMany(Medication::class);
    }

    /**
     * Get formatted code and name
     */
    public function getFormattedAttribute()
    {
        return $this->code . ' - ' . $this->name;
    }
}
