# CDS Database Migration Plan: From Config to Dynamic Rules System

## Overview

This document outlines a comprehensive plan to transform the static CDS (Clinical Decision Support) configuration system into a dynamic, database-driven, user-editable rules engine. The goal is to enable healthcare administrators to create, modify, and manage CDS rules through a web interface without requiring code changes.

## Current System Analysis

### Existing Configuration Structure
```php
// config/cds.php
return [
    'enabled' => env('CDS_ENABLED', true),
    'log_channel' => env('CDS_LOG_CHANNEL', 'cds'),
    'features' => [
        'medication' => [
            'allergy' => true,
            'duplicate' => true,
            'dose_range' => true,
            'formulary' => false,
            'interactions' => false,
        ],
        'guidelines' => [...],
        'diagnostics' => [...],
    ],
    'dose_policies' => [
        'paracetamol' => [
            'max_single_mg' => 1000,
            'max_daily_mg' => 4000,
            'peds_mg_per_kg_dose' => [...],
            'renal' => [...],
        ],
        // ... more medications
    ],
];
```

### Current Implementation Dependencies
- **CdsEngine.php**: Hardcoded rule instantiation
- **DoseRangeRule.php**: Direct config access via `config('cds.dose_policies')`
- **AllergyRule.php**: Feature flags via `config('cds.features.medication.allergy')`
- **DuplicateTherapyRule.php**: Feature flags via `config('cds.features.medication.duplicate')`

---

## Phase 1: Database Schema Design

### 1.1 Core Tables Structure

#### `cds_rule_categories`
**Purpose**: Top-level organization of rule types
```sql
CREATE TABLE cds_rule_categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_active_sort (is_active, sort_order)
);

-- Seed data
INSERT INTO cds_rule_categories (name, display_name, description, sort_order) VALUES
('medication', 'Medication Safety', 'Rules for medication prescribing safety checks', 1),
('guidelines', 'Clinical Guidelines', 'Evidence-based clinical decision support', 2),
('diagnostics', 'Diagnostic Support', 'Laboratory and diagnostic decision support', 3);
```

#### `cds_rule_types`
**Purpose**: Specific rule types within each category
```sql
CREATE TABLE cds_rule_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    handler_class VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES cds_rule_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_name (category_id, name),
    INDEX idx_active_category (is_active, category_id)
);

-- Seed data
INSERT INTO cds_rule_types (category_id, name, display_name, handler_class, sort_order) VALUES
(1, 'allergy', 'Allergy Checking', 'App\\Services\\CDS\\Rules\\AllergyRule', 1),
(1, 'duplicate', 'Duplicate Therapy', 'App\\Services\\CDS\\Rules\\DuplicateTherapyRule', 2),
(1, 'dose_range', 'Dose Range Checking', 'App\\Services\\CDS\\Rules\\DoseRangeRule', 3),
(1, 'formulary', 'Formulary Compliance', 'App\\Services\\CDS\\Rules\\FormularyRule', 4),
(1, 'interactions', 'Drug Interactions', 'App\\Services\\CDS\\Rules\\InteractionRule', 5);
```

#### `cds_rules`
**Purpose**: Individual rule instances
```sql
CREATE TABLE cds_rules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rule_type_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    priority TINYINT UNSIGNED DEFAULT 5 COMMENT '1=Low, 5=Medium, 10=Critical',
    severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (rule_type_id) REFERENCES cds_rule_types(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active_priority (is_active, priority DESC),
    INDEX idx_rule_type (rule_type_id),
    INDEX idx_deleted (deleted_at)
);
```

### 1.2 Flexible Rule Configuration Tables

#### `cds_rule_conditions`
**Purpose**: Define when rules should trigger
```sql
CREATE TABLE cds_rule_conditions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rule_id BIGINT UNSIGNED NOT NULL,
    field_name VARCHAR(100) NOT NULL COMMENT 'medication_name, patient_age, etc.',
    operator ENUM('equals', 'not_equals', 'contains', 'not_contains', 'greater_than', 'less_than', 'greater_equal', 'less_equal', 'in', 'not_in', 'regex') NOT NULL,
    value TEXT NOT NULL,
    value_type ENUM('string', 'integer', 'float', 'boolean', 'array', 'json') DEFAULT 'string',
    logical_operator ENUM('AND', 'OR') DEFAULT 'AND',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (rule_id) REFERENCES cds_rules(id) ON DELETE CASCADE,
    INDEX idx_rule_active (rule_id, is_active),
    INDEX idx_field_name (field_name)
);
```

#### `cds_rule_parameters`
**Purpose**: Store rule-specific configuration parameters
```sql
CREATE TABLE cds_rule_parameters (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rule_id BIGINT UNSIGNED NOT NULL,
    parameter_name VARCHAR(100) NOT NULL,
    parameter_value JSON NOT NULL,
    parameter_type ENUM('dosage_limit', 'age_range', 'weight_range', 'renal_adjustment', 'hepatic_adjustment', 'general') DEFAULT 'general',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (rule_id) REFERENCES cds_rules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rule_parameter (rule_id, parameter_name),
    INDEX idx_parameter_type (parameter_type)
);
```

### 1.3 Medication-Specific Tables

#### `cds_medication_policies`
**Purpose**: Replaces the dose_policies configuration
```sql
CREATE TABLE cds_medication_policies (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    medication_name VARCHAR(200) NOT NULL,
    generic_names JSON COMMENT 'Array of alternative/generic names',
    brand_names JSON COMMENT 'Array of brand names',
    therapeutic_class VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_medication_name (medication_name),
    INDEX idx_active (is_active),
    INDEX idx_therapeutic_class (therapeutic_class)
);

-- Seed data from current config
INSERT INTO cds_medication_policies (medication_name, generic_names, therapeutic_class) VALUES
('paracetamol', '["acetaminophen", "APAP"]', 'Analgesic/Antipyretic'),
('ibuprofen', '["advil", "motrin"]', 'NSAID');
```

#### `cds_dosage_limits`
**Purpose**: Flexible dosage rules per medication
```sql
CREATE TABLE cds_dosage_limits (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    medication_policy_id BIGINT UNSIGNED NOT NULL,
    limit_type ENUM('max_single', 'max_daily', 'pediatric_per_kg', 'renal_adjustment', 'hepatic_adjustment') NOT NULL,
    value_mg DECIMAL(10,3),
    mg_per_kg DECIMAL(6,3) COMMENT 'For pediatric dosing',
    age_min_years DECIMAL(4,1) DEFAULT 0,
    age_max_years DECIMAL(4,1) DEFAULT 150,
    weight_min_kg DECIMAL(5,1),
    weight_max_kg DECIMAL(5,1),
    special_conditions JSON COMMENT 'eGFR ranges, hepatic function, etc.',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (medication_policy_id) REFERENCES cds_medication_policies(id) ON DELETE CASCADE,
    INDEX idx_medication_type (medication_policy_id, limit_type),
    INDEX idx_active (is_active)
);

-- Seed data for paracetamol (medication_policy_id = 1)
INSERT INTO cds_dosage_limits (medication_policy_id, limit_type, value_mg, age_min_years, age_max_years) VALUES
(1, 'max_single', 1000.000, 18, 150),
(1, 'max_daily', 4000.000, 18, 150);

INSERT INTO cds_dosage_limits (medication_policy_id, limit_type, mg_per_kg, value_mg, age_min_years, age_max_years) VALUES
(1, 'pediatric_per_kg', 15.000, 1000.000, 0, 12);

-- Renal adjustments for paracetamol
INSERT INTO cds_dosage_limits (medication_policy_id, limit_type, value_mg, special_conditions) VALUES
(1, 'renal_adjustment', 2000.000, '{"egfr_max": 30}'),
(1, 'renal_adjustment', 1500.000, '{"egfr_max": 15}');
```

---

## Phase 2: Model Architecture

### 2.1 Eloquent Models with Relationships

#### `CdsRuleCategory` Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
```

#### `CdsRuleType` Model
```php
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
```

#### `CdsRule` Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
```

#### `CdsMedicationPolicy` Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CdsMedicationPolicy extends Model
{
    protected $fillable = [
        'medication_name', 'generic_names', 'brand_names', 
        'therapeutic_class', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'generic_names' => 'array',
        'brand_names' => 'array',
        'is_active' => 'boolean',
    ];

    public function dosageLimits(): HasMany
    {
        return $this->hasMany(CdsDosageLimit::class, 'medication_policy_id');
    }

    public function activeDosageLimits(): HasMany
    {
        return $this->dosageLimits()->where('is_active', true);
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

    // Helper method to match medication name
    public static function findByMedicationName(string $medicationName): ?self
    {
        $name = strtolower(trim($medicationName));
        
        return static::active()
            ->where(function ($query) use ($name) {
                $query->whereRaw('LOWER(medication_name) LIKE ?', ["%{$name}%"])
                      ->orWhereRaw('JSON_SEARCH(LOWER(generic_names), "one", ?) IS NOT NULL', ["%{$name}%"])
                      ->orWhereRaw('JSON_SEARCH(LOWER(brand_names), "one", ?) IS NOT NULL', ["%{$name}%"]);
            })
            ->first();
    }

    // Get all names (medication + generic + brand)
    public function getAllNames(): array
    {
        $names = [$this->medication_name];
        
        if ($this->generic_names) {
            $names = array_merge($names, $this->generic_names);
        }
        
        if ($this->brand_names) {
            $names = array_merge($names, $this->brand_names);
        }
        
        return array_unique(array_filter($names));
    }
}
```

### 2.2 Model Features Implementation

#### Audit Trails
```php
// Add to all CDS models
protected static function booted()
{
    static::creating(function ($model) {
        if (auth()->check()) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        }
    });

    static::updating(function ($model) {
        if (auth()->check()) {
            $model->updated_by = auth()->id();
        }
    });
}
```

#### Caching Layer
```php
// CdsRuleCache Service
<?php

namespace App\Services\CDS;

use Illuminate\Support\Facades\Cache;
use App\Models\CdsRule;

class CdsRuleCache
{
    const CACHE_TTL = 3600; // 1 hour
    const CACHE_KEY_PREFIX = 'cds_rules_';

    public function getActiveRulesByType(string $ruleType): Collection
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $ruleType;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($ruleType) {
            return CdsRule::whereHas('ruleType', function ($query) use ($ruleType) {
                $query->where('name', $ruleType);
            })
            ->with(['conditions', 'parameters', 'ruleType'])
            ->active()
            ->byPriority()
            ->get();
        });
    }

    public function clearRuleCache(): void
    {
        $keys = Cache::getRedis()->keys(self::CACHE_KEY_PREFIX . '*');
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    public function clearRuleTypeCache(string $ruleType): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $ruleType);
    }
}
```

---

## Phase 3: Rule Engine Refactoring

### 3.1 Dynamic Rule Loading

#### Enhanced CdsEngine
```php
<?php

namespace App\Services\CDS;

use App\Models\CdsRule;
use App\Services\CDS\CdsRuleCache;
use Illuminate\Support\Facades\Log;

class CdsEngine
{
    public function __construct(
        private CdsAlertService $alerts,
        private CdsRuleCache $ruleCache
    ) {}

    /**
     * Run CDS checks for a given trigger with database-driven rules
     */
    public function check(string $trigger, array $context): void
    {
        if ($trigger === 'medication_prescribe') {
            $this->runMedicationRules($context);
        }
    }

    private function runMedicationRules(array $context): void
    {
        $medicationRuleTypes = ['allergy', 'duplicate', 'dose_range', 'formulary', 'interactions'];
        
        foreach ($medicationRuleTypes as $ruleType) {
            try {
                $rules = $this->ruleCache->getActiveRulesByType($ruleType);
                
                foreach ($rules as $rule) {
                    if ($rule->matchesContext($context)) {
                        $this->executeRule($rule, $context);
                    }
                }
            } catch (\Exception $e) {
                Log::channel(config('cds.log_channel', 'single'))
                    ->error("Failed to execute {$ruleType} rules", [
                        'error' => $e->getMessage(),
                        'context' => $context
                    ]);
            }
        }
    }

    private function executeRule(CdsRule $rule, array $context): void
    {
        try {
            $handler = $rule->ruleType->getHandlerInstance();
            
            // Inject rule configuration into handler
            if (method_exists($handler, 'setRuleConfiguration')) {
                $handler->setRuleConfiguration($rule);
            }
            
            $result = $handler->evaluate($context);
            
            if ($result) {
                // Override severity from database if configured
                if ($rule->severity) {
                    $result['severity'] = $rule->severity;
                }
                
                $this->alerts->createAlert($result);
                
                Log::channel(config('cds.log_channel', 'single'))
                    ->info('CDS Alert generated', [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                        'alert' => $result
                    ]);
            }
        } catch (\Exception $e) {
            Log::channel(config('cds.log_channel', 'single'))
                ->error("Failed to execute rule", [
                    'rule_id' => $rule->id,
                    'error' => $e->getMessage(),
                    'context' => $context
                ]);
        }
    }
}
```

### 3.2 Updated Rule Interface

#### Enhanced Rule Interface
```php
<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsRule;

interface CdsRuleInterface
{
    /**
     * Evaluate the rule against given context
     */
    public function evaluate(array $context): ?array;
    
    /**
     * Get required context keys for this rule
     */
    public function getRequiredContext(): array;
    
    /**
     * Validate rule configuration parameters
     */
    public function validateConfiguration(array $parameters): bool;
    
    /**
     * Set rule configuration from database
     */
    public function setRuleConfiguration(CdsRule $rule): void;
    
    /**
     * Get default parameters for rule creation
     */
    public function getDefaultParameters(): array;
}
```

#### Updated DoseRangeRule
```php
<?php

namespace App\Services\CDS\Rules;

use App\Models\CdsRule;
use App\Models\CdsMedicationPolicy;
use App\Models\Patient;

class DoseRangeRule implements CdsRuleInterface
{
    private ?CdsRule $ruleConfig = null;

    public function setRuleConfiguration(CdsRule $rule): void
    {
        $this->ruleConfig = $rule;
    }

    public function evaluate(array $context): ?array
    {
        // Check if dose range checking is enabled (backward compatibility)
        if (!config('cds.features.medication.dose_range', true) && !$this->ruleConfig) {
            return null;
        }

        $order = $context['order'] ?? [];
        $medName = strtolower(trim($order['medication_name'] ?? ''));
        $dosageText = (string)($order['dosage'] ?? '');
        
        if ($medName === '' || $dosageText === '') {
            return null;
        }

        // Find medication policy in database
        $medicationPolicy = CdsMedicationPolicy::findByMedicationName($medName);
        
        if (!$medicationPolicy) {
            // Fallback to config if no database policy found (backward compatibility)
            return $this->evaluateFromConfig($context);
        }

        return $this->evaluateFromDatabase($context, $medicationPolicy);
    }

    private function evaluateFromDatabase(array $context, CdsMedicationPolicy $policy): ?array
    {
        $order = $context['order'];
        $dosageText = $order['dosage'];
        $mgPerDose = $this->parseDoseToMg($dosageText);
        
        if ($mgPerDose === null) {
            return null;
        }

        // Check single dose limits
        $singleLimits = $policy->dosageLimits()
            ->where('limit_type', 'max_single')
            ->where('is_active', true)
            ->get();

        foreach ($singleLimits as $limit) {
            if ($this->isLimitApplicable($limit, $context)) {
                if ($mgPerDose > $limit->value_mg) {
                    return [
                        'type' => 'dose_range',
                        'severity' => $this->ruleConfig?->severity ?? 'warning',
                        'message' => "Dose {$mgPerDose}mg exceeds maximum single dose of {$limit->value_mg}mg for {$policy->medication_name}",
                        'recommendation' => "Consider reducing dose to ≤{$limit->value_mg}mg",
                        'medication_name' => $order['medication_name'],
                        'prescribed_dose' => $mgPerDose,
                        'max_dose' => $limit->value_mg,
                        'rule_id' => $this->ruleConfig?->id,
                        'patient_id' => $context['patient_id'] ?? null,
                        'visit_id' => $context['visit_id'] ?? null,
                    ];
                }
            }
        }

        // Check pediatric dosing if applicable
        if ($this->isPatientPediatric($context)) {
            return $this->checkPediatricDosing($context, $policy, $mgPerDose);
        }

        // Check renal adjustments
        return $this->checkRenalAdjustments($context, $policy, $mgPerDose);
    }

    private function isLimitApplicable($limit, array $context): bool
    {
        $patientAge = $this->getPatientAge($context);
        
        if ($patientAge !== null) {
            if ($patientAge < $limit->age_min_years || $patientAge > $limit->age_max_years) {
                return false;
            }
        }

        $patientWeight = $this->getPatientWeight($context);
        
        if ($patientWeight !== null && $limit->weight_min_kg && $limit->weight_max_kg) {
            if ($patientWeight < $limit->weight_min_kg || $patientWeight > $limit->weight_max_kg) {
                return false;
            }
        }

        return true;
    }

    private function evaluateFromConfig(array $context): ?array
    {
        // Backward compatibility: use original config-based logic
        $policies = (array) config('cds.dose_policies', []);
        // ... existing config-based implementation ...
    }

    public function getRequiredContext(): array
    {
        return ['patient_id', 'order.medication_name', 'order.dosage'];
    }

    public function validateConfiguration(array $parameters): bool
    {
        // Validate that parameters contain required dosage limit configurations
        return isset($parameters['medication_policies']) || 
               isset($parameters['fallback_to_config']);
    }

    public function getDefaultParameters(): array
    {
        return [
            'check_single_dose' => true,
            'check_daily_dose' => true,
            'check_pediatric_dosing' => true,
            'check_renal_adjustments' => true,
            'fallback_to_config' => true,
        ];
    }

    // ... helper methods remain the same ...
}
```

### 3.3 Rule Factory Pattern

```php
<?php

namespace App\Services\CDS;

use App\Models\CdsRuleType;
use App\Services\CDS\Rules\CdsRuleInterface;

class CdsRuleFactory
{
    private static array $ruleInstances = [];

    public static function createRule(CdsRuleType $ruleType): CdsRuleInterface
    {
        $className = $ruleType->handler_class;
        
        if (!isset(self::$ruleInstances[$className])) {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException("Rule class {$className} not found");
            }
            
            $instance = app($className);
            
            if (!$instance instanceof CdsRuleInterface) {
                throw new \InvalidArgumentException("Rule class {$className} must implement CdsRuleInterface");
            }
            
            self::$ruleInstances[$className] = $instance;
        }
        
        return self::$ruleInstances[$className];
    }

    public static function getRuleTypes(): Collection
    {
        return CdsRuleType::with('category')
            ->active()
            ->get()
            ->mapWithKeys(function ($ruleType) {
                return [$ruleType->name => $ruleType];
            });
    }

    public static function validateRuleHandler(string $className): bool
    {
        return class_exists($className) && 
               in_array(CdsRuleInterface::class, class_implements($className));
    }
}
```

---

## Phase 4: Migration Strategy

### 4.1 Artisan Commands

#### Config Migration Command
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CdsRuleCategory;
use App\Models\CdsRuleType;
use App\Models\CdsMedicationPolicy;
use App\Models\CdsDosageLimit;

class MigrateCdsConfig extends Command
{
    protected $signature = 'cds:migrate-config {--force : Force migration even if data exists}';
    protected $description = 'Migrate CDS configuration from config file to database';

    public function handle()
    {
        $this->info('Starting CDS configuration migration...');

        try {
            DB::beginTransaction();

            $this->migrateFeatureFlags();
            $this->migrateDosePolicies();
            $this->createDefaultRules();

            DB::commit();
            $this->info('CDS configuration migration completed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function migrateFeatureFlags()
    {
        $this->info('Migrating feature flags...');
        
        $features = config('cds.features', []);
        
        foreach ($features as $categoryName => $categoryFeatures) {
            $category = CdsRuleCategory::firstOrCreate(
                ['name' => $categoryName],
                [
                    'display_name' => ucfirst($categoryName),
                    'description' => "Migrated from config: {$categoryName}",
                    'is_active' => true
                ]
            );

            foreach ($categoryFeatures as $featureName => $isEnabled) {
                $handlerClass = $this->getHandlerClass($categoryName, $featureName);
                
                CdsRuleType::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $featureName],
                    [
                        'display_name' => ucwords(str_replace('_', ' ', $featureName)),
                        'description' => "Migrated from config: {$categoryName}.{$featureName}",
                        'handler_class' => $handlerClass,
                        'is_active' => $isEnabled
                    ]
                );
            }
        }
    }

    private function migrateDosePolicies()
    {
        $this->info('Migrating dose policies...');
        
        $dosePolicies = config('cds.dose_policies', []);
        
        foreach ($dosePolicies as $medicationName => $policy) {
            $medicationPolicy = CdsMedicationPolicy::firstOrCreate(
                ['medication_name' => $medicationName],
                [
                    'generic_names' => $this->extractGenericNames($medicationName, $policy),
                    'therapeutic_class' => $this->inferTherapeuticClass($medicationName),
                    'is_active' => true,
                    'created_by' => 1 // System user
                ]
            );

            $this->migrateDosageLimits($medicationPolicy, $policy);
        }
    }

    private function migrateDosageLimits(CdsMedicationPolicy $medication, array $policy)
    {
        // Max single dose
        if (isset($policy['max_single_mg'])) {
            CdsDosageLimit::firstOrCreate(
                [
                    'medication_policy_id' => $medication->id,
                    'limit_type' => 'max_single'
                ],
                [
                    'value_mg' => $policy['max_single_mg'],
                    'age_min_years' => 18,
                    'age_max_years' => 150,
                    'is_active' => true
                ]
            );
        }

        // Max daily dose
        if (isset($policy['max_daily_mg'])) {
            CdsDosageLimit::firstOrCreate(
                [
                    'medication_policy_id' => $medication->id,
                    'limit_type' => 'max_daily'
                ],
                [
                    'value_mg' => $policy['max_daily_mg'],
                    'age_min_years' => 18,
                    'age_max_years' => 150,
                    'is_active' => true
                ]
            );
        }

        // Pediatric dosing
        if (isset($policy['peds_mg_per_kg_dose'])) {
            $peds = $policy['peds_mg_per_kg_dose'];
            CdsDosageLimit::firstOrCreate(
                [
                    'medication_policy_id' => $medication->id,
                    'limit_type' => 'pediatric_per_kg'
                ],
                [
                    'mg_per_kg' => $peds['mg_per_kg'],
                    'value_mg' => $peds['max_single_mg'] ?? null,
                    'age_min_years' => $peds['min_age_years'] ?? 0,
                    'age_max_years' => $peds['max_age_years'] ?? 12,
                    'is_active' => true
                ]
            );
        }

        // Renal adjustments
        if (isset($policy['renal'])) {
            foreach ($policy['renal'] as $renalRule) {
                CdsDosageLimit::firstOrCreate(
                    [
                        'medication_policy_id' => $medication->id,
                        'limit_type' => 'renal_adjustment',
                        'special_conditions->egfr_max' => $renalRule['egfr_max']
                    ],
                    [
                        'value_mg' => $renalRule['max_daily_mg'],
                        'special_conditions' => ['egfr_max' => $renalRule['egfr_max']],
                        'is_active' => true
                    ]
                );
            }
        }
    }

    private function getHandlerClass(string $category, string $feature): string
    {
        $classMap = [
            'medication.allergy' => 'App\\Services\\CDS\\Rules\\AllergyRule',
            'medication.duplicate' => 'App\\Services\\CDS\\Rules\\DuplicateTherapyRule',
            'medication.dose_range' => 'App\\Services\\CDS\\Rules\\DoseRangeRule',
            'medication.formulary' => 'App\\Services\\CDS\\Rules\\FormularyRule',
            'medication.interactions' => 'App\\Services\\CDS\\Rules\\InteractionRule',
        ];

        return $classMap["{$category}.{$feature}"] ?? 'App\\Services\\CDS\\Rules\\GenericRule';
    }

    // ... helper methods ...
}
```

### 4.2 Database Seeders

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CdsRuleCategory;
use App\Models\CdsRuleType;
use App\Models\CdsRule;

class CdsSystemSeeder extends Seeder
{
    public function run()
    {
        $this->seedCategories();
        $this->seedRuleTypes();
        $this->seedDefaultRules();
    }

    private function seedCategories()
    {
        $categories = [
            ['name' => 'medication', 'display_name' => 'Medication Safety', 'sort_order' => 1],
            ['name' => 'guidelines', 'display_name' => 'Clinical Guidelines', 'sort_order' => 2],
            ['name' => 'diagnostics', 'display_name' => 'Diagnostic Support', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            CdsRuleCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }

    private function seedRuleTypes()
    {
        $medicationCategory = CdsRuleCategory::where('name', 'medication')->first();
        
        $ruleTypes = [
            [
                'name' => 'allergy',
                'display_name' => 'Allergy Checking',
                'handler_class' => 'App\\Services\\CDS\\Rules\\AllergyRule',
                'sort_order' => 1
            ],
            [
                'name' => 'duplicate',
                'display_name' => 'Duplicate Therapy',
                'handler_class' => 'App\\Services\\CDS\\Rules\\DuplicateTherapyRule',
                'sort_order' => 2
            ],
            [
                'name' => 'dose_range',
                'display_name' => 'Dose Range Checking',
                'handler_class' => 'App\\Services\\CDS\\Rules\\DoseRangeRule',
                'sort_order' => 3
            ],
        ];

        foreach ($ruleTypes as $ruleType) {
            CdsRuleType::firstOrCreate(
                ['category_id' => $medicationCategory->id, 'name' => $ruleType['name']],
                array_merge($ruleType, ['category_id' => $medicationCategory->id])
            );
        }
    }

    private function seedDefaultRules()
    {
        // Create basic allergy checking rule
        $allergyRuleType = CdsRuleType::where('name', 'allergy')->first();
        
        CdsRule::firstOrCreate(
            ['rule_type_id' => $allergyRuleType->id, 'name' => 'Basic Allergy Check'],
            [
                'description' => 'Check for known patient allergies before prescribing',
                'priority' => 10,
                'severity' => 'critical',
                'is_active' => true,
                'created_by' => 1
            ]
        );
    }
}
```

---

## Phase 5: User Interface Development

### 5.1 Rule Management Controllers

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdsRule;
use App\Models\CdsRuleType;
use Illuminate\Http\Request;

class CdsRuleController extends Controller
{
    public function index()
    {
        $rules = CdsRule::with(['ruleType.category', 'creator'])
            ->paginate(20);

        return view('admin.cds.rules.index', compact('rules'));
    }

    public function create()
    {
        $ruleTypes = CdsRuleType::with('category')->active()->get();
        
        return view('admin.cds.rules.create', compact('ruleTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rule_type_id' => 'required|exists:cds_rule_types,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'priority' => 'required|integer|between:1,10',
            'severity' => 'required|in:info,warning,critical',
            'conditions' => 'array',
            'conditions.*.field_name' => 'required|string',
            'conditions.*.operator' => 'required|string',
            'conditions.*.value' => 'required',
            'parameters' => 'array',
            'parameters.*.parameter_name' => 'required|string',
            'parameters.*.parameter_value' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $rule = CdsRule::create($validated);

            // Create conditions
            if ($request->has('conditions')) {
                foreach ($request->conditions as $condition) {
                    $rule->conditions()->create($condition);
                }
            }

            // Create parameters
            if ($request->has('parameters')) {
                foreach ($request->parameters as $parameter) {
                    $rule->parameters()->create($parameter);
                }
            }

            DB::commit();

            // Clear rule cache
            app(CdsRuleCache::class)->clearRuleTypeCache(
                $rule->ruleType->name
            );

            return redirect()
                ->route('admin.cds.rules.index')
                ->with('success', 'CDS Rule created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create rule: ' . $e->getMessage()]);
        }
    }

    public function edit(CdsRule $rule)
    {
        $rule->load(['conditions', 'parameters', 'ruleType.category']);
        $ruleTypes = CdsRuleType::with('category')->active()->get();
        
        return view('admin.cds.rules.edit', compact('rule', 'ruleTypes'));
    }

    public function testRule(Request $request, CdsRule $rule)
    {
        $testContext = $request->validate([
            'patient_id' => 'required|integer',
            'visit_id' => 'nullable|integer',
            'order' => 'required|array',
            'order.medication_name' => 'required|string',
            'order.dosage' => 'nullable|string',
        ]);

        try {
            $handler = $rule->ruleType->getHandlerInstance();
            
            if (method_exists($handler, 'setRuleConfiguration')) {
                $handler->setRuleConfiguration($rule);
            }

            $result = $handler->evaluate($testContext);

            return response()->json([
                'success' => true,
                'matches_conditions' => $rule->matchesContext($testContext),
                'rule_result' => $result,
                'test_context' => $testContext
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### 5.2 Blade Templates

#### Rule Index Template
```blade
{{-- resources/views/admin/cds/rules/index.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>CDS Rules Management</h1>
        <a href="{{ route('admin.cds.rules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Rule
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All CDS Rules</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                        <tr>
                            <td>
                                <strong>{{ $rule->name }}</strong>
                                @if($rule->description)
                                    <br><small class="text-muted">{{ Str::limit($rule->description, 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $rule->ruleType->display_name }}</td>
                            <td>{{ $rule->ruleType->category->display_name }}</td>
                            <td>
                                <span class="badge badge-{{ $rule->priority >= 8 ? 'danger' : ($rule->priority >= 5 ? 'warning' : 'info') }}">
                                    {{ $rule->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }}">
                                    {{ ucfirst($rule->severity) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $rule->creator->name ?? 'System' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.cds.rules.edit', $rule) }}" 
                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="testRule({{ $rule->id }})">Test</button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete({{ $rule->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No CDS rules found. <a href="{{ route('admin.cds.rules.create') }}">Create your first rule</a>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $rules->links() }}
        </div>
    </div>
</div>

<!-- Test Rule Modal -->
<div class="modal fade" id="testRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test CDS Rule</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="testRuleForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Patient ID</label>
                                <input type="number" class="form-control" name="patient_id" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Visit ID (optional)</label>
                                <input type="number" class="form-control" name="visit_id">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Medication Name</label>
                        <input type="text" class="form-control" name="order[medication_name]" required>
                    </div>
                    <div class="form-group">
                        <label>Dosage</label>
                        <input type="text" class="form-control" name="order[dosage]" placeholder="e.g., 500mg">
                    </div>
                </form>
                <div id="testResults" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="executeRuleTest()">Run Test</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentRuleId = null;

function testRule(ruleId) {
    currentRuleId = ruleId;
    $('#testRuleModal').modal('show');
    $('#testResults').html('');
}

function executeRuleTest() {
    if (!currentRuleId) return;
    
    const formData = new FormData(document.getElementById('testRuleForm'));
    const data = Object.fromEntries(formData);
    
    // Convert nested form data
    data.order = {
        medication_name: formData.get('order[medication_name]'),
        dosage: formData.get('order[dosage]')
    };
    delete data['order[medication_name]'];
    delete data['order[dosage]'];
    
    fetch(`/admin/cds/rules/${currentRuleId}/test`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        let html = '<div class="alert alert-' + (result.success ? 'success' : 'danger') + '">';
        
        if (result.success) {
            html += '<h6>Test Results:</h6>';
            html += '<p><strong>Conditions Match:</strong> ' + (result.matches_conditions ? 'Yes' : 'No') + '</p>';
            
            if (result.rule_result) {
                html += '<p><strong>Alert Generated:</strong></p>';
                html += '<pre>' + JSON.stringify(result.rule_result, null, 2) + '</pre>';
            } else {
                html += '<p><strong>Alert Generated:</strong> No</p>';
            }
        } else {
            html += '<p><strong>Error:</strong> ' + result.error + '</p>';
        }
        
        html += '</div>';
        document.getElementById('testResults').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('testResults').innerHTML = 
            '<div class="alert alert-danger">Test failed: ' + error.message + '</div>';
    });
}

function confirmDelete(ruleId) {
    if (confirm('Are you sure you want to delete this CDS rule? This action cannot be undone.')) {
        // Implement delete functionality
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/cds/rules/${ruleId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
```

---

## Phase 6: Implementation Timeline

### **Week 1-2: Database Foundation**
- [ ] Create migration files for all CDS tables
- [ ] Build Eloquent models with relationships
- [ ] Create seeders for basic data
- [ ] Write Artisan command for config migration
- [ ] Test database schema thoroughly

### **Week 3-4: Rule Engine Refactoring**
- [ ] Implement CdsRuleInterface for all existing rules
- [ ] Update CdsEngine for database-driven rule loading
- [ ] Create CdsRuleCache service
- [ ] Add backward compatibility layer
- [ ] Write comprehensive tests

### **Week 5-6: User Interface Development**
- [ ] Create admin controllers for rule management
- [ ] Build Blade templates for CRUD operations
- [ ] Implement rule testing interface
- [ ] Add medication policy management
- [ ] Create rule builder wizard

### **Week 7-8: Migration & Testing**
- [ ] Run config migration on staging environment
- [ ] Perform extensive testing with real data
- [ ] Create user documentation
- [ ] Train system administrators
- [ ] Plan production rollout

### **Week 9-10: Deployment & Optimization**
- [ ] Deploy to production with feature flags
- [ ] Monitor system performance
- [ ] Gather user feedback
- [ ] Optimize database queries
- [ ] Plan Phase 2 enhancements

---

## Phase 7: Future Enhancements

### **Advanced Rule Language (Phase 2)**
```javascript
// Visual Rule Builder Interface
WHEN:
  - medication.name CONTAINS "penicillin" 
  AND patient.allergies CONTAINS "penicillin"
THEN:
  - GENERATE ALERT "Severe allergy risk"
  - SET severity = CRITICAL
  - RECOMMEND "Consider alternative antibiotic"
```

### **Machine Learning Integration (Phase 3)**
- **Pattern Recognition**: Analyze alert patterns and outcomes
- **Predictive Modeling**: Predict adverse events before they occur
- **Auto-tuning**: Automatically adjust rule parameters based on effectiveness
- **Outcome Tracking**: Measure clinical impact of CDS interventions

### **External Integration (Phase 4)**
- **Drug Database APIs**: Real-time medication information
- **Clinical Guidelines**: Integration with medical societies
- **EHR Interoperability**: HL7 FHIR rule exchange
- **Regulatory Updates**: Automated rule updates from authorities

---

## Risk Mitigation & Rollback Plan

### **Technical Risks**
1. **Performance Impact**: Implement caching and database optimization
2. **Data Migration Errors**: Thorough testing and backup procedures
3. **Rule Logic Bugs**: Comprehensive testing suite and gradual rollout
4. **User Adoption**: Training programs and intuitive interface design

### **Clinical Risks**
1. **False Alerts**: Extensive rule validation and testing
2. **Missed Alerts**: Backup systems and monitoring
3. **User Override**: Proper audit trails and approval workflows
4. **System Downtime**: Fallback to config-based system

### **Rollback Procedures**
1. **Immediate**: Feature flag to disable database rules
2. **Short-term**: Revert to config-based system
3. **Long-term**: Database restoration from backups
4. **Communication**: Clear escalation procedures and user notifications

---

## Success Metrics

### **Technical Metrics**
- Rule execution time < 100ms per rule
- System uptime > 99.9%
- Rule cache hit rate > 95%
- Database query optimization

### **Clinical Metrics**
- Reduction in medication errors
- Improved alert relevance (fewer false positives)
- Faster rule deployment time
- Increased user satisfaction

### **Operational Metrics**
- Time to create new rules
- Rule maintenance effort
- System administration overhead
- Training requirements

---

This comprehensive plan provides a roadmap for transforming your static CDS configuration into a dynamic, user-manageable system that can evolve with your clinical needs while maintaining the highest standards of patient safety.