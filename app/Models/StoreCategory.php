<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;

    protected $table = 'store_categories';

    protected $fillable = [
        'description'
    ];

    protected $casts = [
        // No additional casts needed for simplified schema
    ];

    // Relationships
    
    /**
     * Get medications in this category
     */
    public function medications()
    {
        return $this->hasMany(Medication::class, 'category_id');
    }

    /**
     * Get all store items in this category (unified approach)
     */
    public function storeItems()
    {
        return $this->hasMany(Medication::class, 'category_id');
    }

    // Scopes
    
    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('description');
    }

    // Accessors & Mutators
    
    /**
     * Get the total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->medications()->count();
    }
}
