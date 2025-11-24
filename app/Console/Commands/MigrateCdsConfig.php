<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CdsRuleCategory;
use App\Models\CdsRuleType;
use App\Models\CdsMedicationPolicy;
use App\Models\CdsDosageLimit;
use App\Models\CdsRule;

class MigrateCdsConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cds:migrate-config {--force : Force migration even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate CDS configuration from config file to database';

    /**
     * Execute the console command.
     */
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

    private function createDefaultRules()
    {
        $this->info('Creating default rules...');
        
        // Enable active medication rule types with basic rules
        $ruleTypes = CdsRuleType::where('is_active', true)->get();
        
        foreach ($ruleTypes as $ruleType) {
            $existingRule = CdsRule::where('rule_type_id', $ruleType->id)->first();
            
            if (!$existingRule) {
                CdsRule::create([
                    'rule_type_id' => $ruleType->id,
                    'name' => "Default {$ruleType->display_name}",
                    'description' => "Automatically created rule for {$ruleType->display_name}",
                    'priority' => $this->getDefaultPriority($ruleType->name),
                    'severity' => $this->getDefaultSeverity($ruleType->name),
                    'is_active' => true,
                    'created_by' => 1
                ]);
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

    private function extractGenericNames(string $medicationName, array $policy): array
    {
        $genericMap = [
            'paracetamol' => ['acetaminophen', 'APAP'],
            'ibuprofen' => [],
            'aspirin' => ['acetylsalicylic acid', 'ASA']
        ];

        return $genericMap[$medicationName] ?? [];
    }

    private function inferTherapeuticClass(string $medicationName): string
    {
        $classMap = [
            'paracetamol' => 'Analgesic/Antipyretic',
            'ibuprofen' => 'NSAID',
            'aspirin' => 'NSAID/Antiplatelet'
        ];

        return $classMap[$medicationName] ?? 'Unknown';
    }

    private function getDefaultPriority(string $ruleType): int
    {
        $priorityMap = [
            'allergy' => 10,
            'dose_range' => 8,
            'duplicate' => 7,
            'interactions' => 9,
            'formulary' => 5
        ];

        return $priorityMap[$ruleType] ?? 5;
    }

    private function getDefaultSeverity(string $ruleType): string
    {
        $severityMap = [
            'allergy' => 'critical',
            'dose_range' => 'warning',
            'duplicate' => 'warning',
            'interactions' => 'critical',
            'formulary' => 'info'
        ];

        return $severityMap[$ruleType] ?? 'warning';
    }
}
