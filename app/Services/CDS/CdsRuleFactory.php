<?php

namespace App\Services\CDS;

use App\Models\CdsRuleType;
use App\Services\CDS\Rules\CdsRuleInterface;
use Illuminate\Support\Collection;

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