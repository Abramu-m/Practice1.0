<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CdsRuleCategory extends Model
{
    protected $fillable = [
        'name', 'display_name', 'description', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function ruleTypes(): HasMany
    {
        return $this->hasMany(CdsRuleType::class, 'category_id');
    }

    public function activeRuleTypes(): HasMany
    {
        return $this->ruleTypes()->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get all rules through rule types
     */
    public function rules(): HasManyThrough
    {
        return $this->hasManyThrough(
            CdsRule::class,           // Final model
            CdsRuleType::class,       // Intermediate model
            'category_id',            // Foreign key on CdsRuleType table
            'rule_type_id',          // Foreign key on CdsRule table
            'id',                     // Local key on CdsRuleCategory table
            'id'                      // Local key on CdsRuleType table
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
