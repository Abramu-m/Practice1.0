<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CdsRuleCondition extends Model
{
    protected $fillable = [
        'rule_id', 'field_name', 'operator', 'value', 'value_type',
        'logical_operator', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'rule_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CdsRule::class, 'rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Get value with proper type casting
    public function getTypedValue()
    {
        switch ($this->value_type) {
            case 'integer':
                return (int) $this->value;
            case 'float':
                return (float) $this->value;
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            case 'array':
            case 'json':
                return json_decode($this->value, true);
            case 'string':
            default:
                return $this->value;
        }
    }
}
