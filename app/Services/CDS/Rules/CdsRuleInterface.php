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