<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CdsRuleCategory;
use App\Models\CdsRuleType;
use App\Models\CdsRule;
use App\Models\CdsMedicationPolicy;
use App\Models\CdsDosageLimit;
use App\Services\CDS\CdsRuleCache;

class TestCdsImplementation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cds:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the CDS implementation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Testing CDS Database Implementation...');
        $this->newLine();

        // Test Categories
        $categoriesCount = CdsRuleCategory::count();
        $this->info("✅ CDS Rule Categories: {$categoriesCount}");
        
        foreach (CdsRuleCategory::all() as $category) {
            $this->line("  - {$category->display_name} ({$category->name})");
        }
        $this->newLine();

        // Test Rule Types
        $ruleTypesCount = CdsRuleType::count();
        $this->info("✅ CDS Rule Types: {$ruleTypesCount}");
        
        foreach (CdsRuleType::with('category')->get() as $ruleType) {
            $status = $ruleType->is_active ? '🟢' : '🔴';
            $this->line("  {$status} {$ruleType->display_name} ({$ruleType->category->name}.{$ruleType->name})");
        }
        $this->newLine();

        // Test Rules
        $rulesCount = CdsRule::count();
        $this->info("✅ CDS Rules: {$rulesCount}");
        
        foreach (CdsRule::with('ruleType')->get() as $rule) {
            $status = $rule->is_active ? '🟢' : '🔴';
            $priority = str_repeat('⭐', min(3, $rule->priority / 3));
            $this->line("  {$status} {$rule->name} - Priority: {$rule->priority} {$priority} ({$rule->severity})");
        }
        $this->newLine();

        // Test Medication Policies
        $medicationPoliciesCount = CdsMedicationPolicy::count();
        $this->info("✅ Medication Policies: {$medicationPoliciesCount}");
        
        foreach (CdsMedicationPolicy::with('dosageLimits')->get() as $policy) {
            $limitsCount = $policy->dosageLimits->count();
            $this->line("  - {$policy->medication_name} ({$policy->therapeutic_class}) - {$limitsCount} limits");
        }
        $this->newLine();

        // Test Cache Service
        $this->info("🔧 Testing Cache Service...");
        try {
            $cache = app(CdsRuleCache::class);
            $allergyRules = $cache->getActiveRulesByType('allergy');
            $this->info("✅ Cache Service: Found {$allergyRules->count()} allergy rules");
        } catch (\Exception $e) {
            $this->error("❌ Cache Service Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test Rule Evaluation
        $this->info("🔧 Testing Rule Evaluation...");
        try {
            $testContext = [
                'patient_id' => 1,
                'visit_id' => 1,
                'order' => [
                    'medication_name' => 'paracetamol',
                    'dosage' => '1500mg',
                    'medication_id' => 1
                ]
            ];

            // Test dose range rule with database
            $medicationPolicy = CdsMedicationPolicy::findByMedicationName('paracetamol');
            if ($medicationPolicy) {
                $this->info("✅ Found medication policy for paracetamol");
                $limits = $medicationPolicy->dosageLimits()->where('limit_type', 'max_single')->first();
                if ($limits) {
                    $this->info("✅ Found dosage limit: {$limits->value_mg}mg");
                    if (1500 > $limits->value_mg) {
                        $this->info("✅ Would trigger dose range alert (1500mg > {$limits->value_mg}mg)");
                    } else {
                        $this->info("ℹ️  Dose within safe limits");
                    }
                }
            } else {
                $this->warn("⚠️  No medication policy found for paracetamol");
            }

        } catch (\Exception $e) {
            $this->error("❌ Rule Evaluation Error: " . $e->getMessage());
        }
        $this->newLine();

        $this->info("🎉 CDS Implementation Test Completed!");
        
        return 0;
    }
}
