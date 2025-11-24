<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CdsRuleCategory;
use App\Models\CdsRuleType;
use App\Models\CdsRule;
use App\Models\CdsMedicationPolicy;
use App\Models\CdsDosageLimit;

class CdsSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedRuleTypes();
        $this->seedDefaultRules();
        $this->seedMedicationPolicies();
    }

    private function seedCategories()
    {
        $categories = [
            [
                'name' => 'medication',
                'display_name' => 'Medication Safety',
                'description' => 'Rules for medication prescribing safety checks',
                'sort_order' => 1
            ],
            [
                'name' => 'guidelines',
                'display_name' => 'Clinical Guidelines',
                'description' => 'Evidence-based clinical decision support',
                'sort_order' => 2
            ],
            [
                'name' => 'diagnostics',
                'display_name' => 'Diagnostic Support',
                'description' => 'Laboratory and diagnostic decision support',
                'sort_order' => 3
            ],
        ];

        foreach ($categories as $category) {
            CdsRuleCategory::firstOrCreate(
                ['name' => $category['name']], 
                $category
            );
        }
    }

    private function seedRuleTypes()
    {
        $medicationCategory = CdsRuleCategory::where('name', 'medication')->first();
        
        if (!$medicationCategory) {
            return;
        }
        
        $ruleTypes = [
            [
                'name' => 'allergy',
                'display_name' => 'Allergy Checking',
                'description' => 'Check for known patient allergies before prescribing',
                'handler_class' => 'App\\Services\\CDS\\Rules\\AllergyRule',
                'sort_order' => 1
            ],
            [
                'name' => 'duplicate',
                'display_name' => 'Duplicate Therapy',
                'description' => 'Check for duplicate or overlapping medications',
                'handler_class' => 'App\\Services\\CDS\\Rules\\DuplicateTherapyRule',
                'sort_order' => 2
            ],
            [
                'name' => 'dose_range',
                'display_name' => 'Dose Range Checking',
                'description' => 'Validate medication dosages against safe limits',
                'handler_class' => 'App\\Services\\CDS\\Rules\\DoseRangeRule',
                'sort_order' => 3
            ],
            [
                'name' => 'formulary',
                'display_name' => 'Formulary Compliance',
                'description' => 'Check medication availability in formulary',
                'handler_class' => 'App\\Services\\CDS\\Rules\\FormularyRule',
                'sort_order' => 4
            ],
            [
                'name' => 'interactions',
                'display_name' => 'Drug Interactions',
                'description' => 'Check for potential drug-drug interactions',
                'handler_class' => 'App\\Services\\CDS\\Rules\\InteractionRule',
                'sort_order' => 5
            ],
        ];

        foreach ($ruleTypes as $ruleType) {
            CdsRuleType::firstOrCreate(
                [
                    'category_id' => $medicationCategory->id,
                    'name' => $ruleType['name']
                ],
                array_merge($ruleType, ['category_id' => $medicationCategory->id])
            );
        }
    }

    private function seedDefaultRules()
    {
        // Create basic allergy checking rule
        $allergyRuleType = CdsRuleType::where('name', 'allergy')->first();
        
        if ($allergyRuleType) {
            CdsRule::firstOrCreate(
                [
                    'rule_type_id' => $allergyRuleType->id,
                    'name' => 'Basic Allergy Check'
                ],
                [
                    'description' => 'Check for known patient allergies before prescribing',
                    'priority' => 10,
                    'severity' => 'critical',
                    'is_active' => true,
                    'created_by' => 1
                ]
            );
        }

        // Create basic dose range checking rule
        $doseRuleType = CdsRuleType::where('name', 'dose_range')->first();
        
        if ($doseRuleType) {
            CdsRule::firstOrCreate(
                [
                    'rule_type_id' => $doseRuleType->id,
                    'name' => 'Standard Dose Range Check'
                ],
                [
                    'description' => 'Validate medication dosages against established safe limits',
                    'priority' => 8,
                    'severity' => 'warning',
                    'is_active' => true,
                    'created_by' => 1
                ]
            );
        }

        // Create basic duplicate therapy rule
        $duplicateRuleType = CdsRuleType::where('name', 'duplicate')->first();
        
        if ($duplicateRuleType) {
            CdsRule::firstOrCreate(
                [
                    'rule_type_id' => $duplicateRuleType->id,
                    'name' => 'Duplicate Medication Check'
                ],
                [
                    'description' => 'Check for duplicate or overlapping medications',
                    'priority' => 7,
                    'severity' => 'warning',
                    'is_active' => true,
                    'created_by' => 1
                ]
            );
        }
    }

    private function seedMedicationPolicies()
    {
        // Seed common medications from current config
        $medications = [
            [
                'medication_name' => 'paracetamol',
                'generic_names' => ['acetaminophen', 'APAP'],
                'brand_names' => ['Tylenol', 'Panadol'],
                'therapeutic_class' => 'Analgesic/Antipyretic',
                'dosage_limits' => [
                    ['limit_type' => 'max_single', 'value_mg' => 1000, 'age_min_years' => 18, 'age_max_years' => 150],
                    ['limit_type' => 'max_daily', 'value_mg' => 4000, 'age_min_years' => 18, 'age_max_years' => 150],
                    ['limit_type' => 'pediatric_per_kg', 'mg_per_kg' => 15, 'value_mg' => 1000, 'age_min_years' => 0, 'age_max_years' => 12],
                    ['limit_type' => 'renal_adjustment', 'value_mg' => 2000, 'special_conditions' => ['egfr_max' => 30]],
                    ['limit_type' => 'renal_adjustment', 'value_mg' => 1500, 'special_conditions' => ['egfr_max' => 15]],
                ]
            ],
            [
                'medication_name' => 'ibuprofen',
                'generic_names' => [],
                'brand_names' => ['Advil', 'Motrin', 'Nurofen'],
                'therapeutic_class' => 'NSAID',
                'dosage_limits' => [
                    ['limit_type' => 'max_single', 'value_mg' => 800, 'age_min_years' => 18, 'age_max_years' => 150],
                    ['limit_type' => 'max_daily', 'value_mg' => 2400, 'age_min_years' => 18, 'age_max_years' => 150],
                    ['limit_type' => 'pediatric_per_kg', 'mg_per_kg' => 10, 'value_mg' => 400, 'age_min_years' => 6, 'age_max_years' => 12],
                ]
            ],
            [
                'medication_name' => 'aspirin',
                'generic_names' => ['acetylsalicylic acid', 'ASA'],
                'brand_names' => ['Bayer', 'Disprin'],
                'therapeutic_class' => 'NSAID/Antiplatelet',
                'dosage_limits' => [
                    ['limit_type' => 'max_single', 'value_mg' => 1000, 'age_min_years' => 18, 'age_max_years' => 150],
                    ['limit_type' => 'max_daily', 'value_mg' => 4000, 'age_min_years' => 18, 'age_max_years' => 150],
                ]
            ]
        ];

        foreach ($medications as $medData) {
            $medicationPolicy = CdsMedicationPolicy::firstOrCreate(
                ['medication_name' => $medData['medication_name']],
                [
                    'generic_names' => $medData['generic_names'],
                    'brand_names' => $medData['brand_names'],
                    'therapeutic_class' => $medData['therapeutic_class'],
                    'is_active' => true,
                    'created_by' => 1
                ]
            );

            // Create dosage limits
            foreach ($medData['dosage_limits'] as $limitData) {
                CdsDosageLimit::firstOrCreate(
                    [
                        'medication_policy_id' => $medicationPolicy->id,
                        'limit_type' => $limitData['limit_type'],
                    ],
                    array_merge($limitData, [
                        'medication_policy_id' => $medicationPolicy->id,
                        'is_active' => true
                    ])
                );
            }
        }
    }
}
