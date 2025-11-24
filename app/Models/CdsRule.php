<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class CdsRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rule_type_id', 'name', 'description', 'priority', 
        'severity', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'rule_type_id' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function ruleType(): BelongsTo
    {
        return $this->belongsTo(CdsRuleType::class, 'rule_type_id');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(CdsRuleCondition::class, 'rule_id');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(CdsRuleParameter::class, 'rule_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query, $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    public function scopeBySeverity($query, $severity = null)
    {
        return $severity ? $query->where('severity', $severity) : $query;
    }

    // Helper method to get parameter value
    public function getParameter(string $name, $default = null)
    {
        $parameter = $this->parameters()->where('parameter_name', $name)->first();
        return $parameter ? $parameter->parameter_value : $default;
    }

    // Helper method to check if rule conditions match context
    public function matchesContext(array $context): bool
    {
        $conditions = $this->conditions()->where('is_active', true)->get();
        
        if ($conditions->isEmpty()) {
            return true; // No conditions = always match
        }

        $matches = [];
        $currentLogicalOperator = 'AND';

        foreach ($conditions as $condition) {
            $fieldValue = data_get($context, $condition->field_name);
            $conditionValue = $condition->getTypedValue();
            
            $match = $this->evaluateCondition(
                $fieldValue, 
                $condition->operator, 
                $conditionValue
            );
            
            if ($condition->logical_operator === 'OR' && !empty($matches)) {
                // Evaluate previous AND group
                $andResult = !in_array(false, $matches, true);
                $matches = [$andResult, $match];
                $currentLogicalOperator = 'OR';
            } else {
                $matches[] = $match;
            }
        }

        // Final evaluation
        return $currentLogicalOperator === 'OR' ? 
            in_array(true, $matches, true) : 
            !in_array(false, $matches, true);
    }

    private function evaluateCondition($fieldValue, $operator, $conditionValue): bool
    {
        switch ($operator) {
            case 'equals': return $fieldValue == $conditionValue;
            case 'not_equals': return $fieldValue != $conditionValue;
            case 'contains': return str_contains(strtolower($fieldValue), strtolower($conditionValue));
            case 'not_contains': return !str_contains(strtolower($fieldValue), strtolower($conditionValue));
            case 'greater_than': return $fieldValue > $conditionValue;
            case 'less_than': return $fieldValue < $conditionValue;
            case 'greater_equal': return $fieldValue >= $conditionValue;
            case 'less_equal': return $fieldValue <= $conditionValue;
            case 'in': return in_array($fieldValue, (array)$conditionValue);
            case 'not_in': return !in_array($fieldValue, (array)$conditionValue);
            case 'regex': return preg_match($conditionValue, $fieldValue);
            default: return false;
        }
    }
}
