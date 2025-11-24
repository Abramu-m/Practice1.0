<?php

namespace App\Services\CDS;

use Illuminate\Support\Facades\Cache;
use App\Models\CdsRule;
use Illuminate\Support\Collection;

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
        // Clear all rule type caches
        $ruleTypes = ['allergy', 'duplicate', 'dose_range', 'formulary', 'interactions'];
        
        foreach ($ruleTypes as $ruleType) {
            $this->clearRuleTypeCache($ruleType);
        }
    }

    public function clearRuleTypeCache(string $ruleType): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $ruleType);
    }

    public function warmupCache(): void
    {
        $ruleTypes = ['allergy', 'duplicate', 'dose_range', 'formulary', 'interactions'];
        
        foreach ($ruleTypes as $ruleType) {
            $this->getActiveRulesByType($ruleType);
        }
    }
}