<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CdsRuleParameter extends Model
{
    protected $fillable = [
        'rule_id', 'parameter_name', 'parameter_value', 'parameter_type', 'is_active'
    ];

    protected $casts = [
        'rule_id' => 'integer',
        'parameter_value' => 'array',
        'is_active' => 'boolean',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CdsRule::class, 'rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('parameter_type', $type);
    }
}
