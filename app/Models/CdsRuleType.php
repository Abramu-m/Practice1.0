<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CdsRuleType extends Model
{
    protected $fillable = [
        'category_id', 'name', 'display_name', 'description', 
        'handler_class', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'category_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CdsRuleCategory::class, 'category_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CdsRule::class, 'rule_type_id');
    }

    public function activeRules(): HasMany
    {
        return $this->rules()->where('is_active', true)->orderBy('priority', 'desc');
    }

    public function getHandlerInstance()
    {
        if (class_exists($this->handler_class)) {
            return app($this->handler_class);
        }
        throw new \InvalidArgumentException("Handler class {$this->handler_class} not found");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
