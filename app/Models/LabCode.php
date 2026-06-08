<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabCode extends Model
{
    protected $fillable = [
        'coding_system',
        'code',
        'display_name',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active lab codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Restrict to LOINC codes
     */
    public function scopeLoinc($query)
    {
        return $query->where('coding_system', 'loinc');
    }

    /**
     * Restrict to SNOMED CT codes
     */
    public function scopeSnomed($query)
    {
        return $query->where('coding_system', 'snomed');
    }

    /**
     * Search lab codes by code or display name
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'LIKE', "%{$term}%")
              ->orWhere('display_name', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Get formatted code and display name
     */
    public function getFormattedAttribute()
    {
        return $this->code . ' - ' . $this->display_name;
    }
}
