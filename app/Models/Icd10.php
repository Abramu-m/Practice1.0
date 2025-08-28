<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10 extends Model
{
    protected $table = 'icd_10';
    
    protected $fillable = [
        'code',
        'description',
        'category',
        'subcategory',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get active ICD-10 codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Search ICD-10 codes by code or description
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('code', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Get codes by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted code and description
     */
    public function getFormattedAttribute()
    {
        return $this->code . ' - ' . $this->description;
    }
}
