<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyDrugsInjectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Injectable medications (dtype = 26)
        $injectionDrugs = [
            [
                'did' => 161,
                'ddescription' => 'Furosemide (Frusemide) inj. 20 mg/2 ml IV/IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 80,
                'ducost' => 450,
                'dosage' => null
            ],
            [
                'did' => 162,
                'ddescription' => 'Adrenaline inj.',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 163,
                'ddescription' => 'Aminophylline inj. 250 mg/10 ml IV/IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 0,
                'ducost' => 1,
                'dosage' => null
            ],
            [
                'did' => 164,
                'ddescription' => 'Ampicilline inj. 500 mg',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 45,
                'ducost' => 880,
                'dosage' => null
            ],
            [
                'did' => 165,
                'ddescription' => 'Artemeter inj. 80 mg/1 ml IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 6,
                'ducost' => 3300,
                'dosage' => null
            ],
            [
                'did' => 166,
                'ddescription' => 'Ceftriaxone inj. 1 g',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 6,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 167,
                'ddescription' => 'Ceftriaxone inj. 500 mg',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 168,
                'ddescription' => 'Dexamethasone inj. 4 mg/ml in 1 ml',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 90,
                'ducost' => 1350,
                'dosage' => null
            ],
            [
                'did' => 169,
                'ddescription' => 'Diclofenac inj. 75 mg/3 ml IV/IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 8,
                'ducost' => 155,
                'dosage' => null
            ],
            [
                'did' => 170,
                'ddescription' => 'Gentamicin inj. 40 mg/ml in 2 ml',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 171,
                'ddescription' => 'Hydrocortisone inj. 100 mg/Vial IM/slow IV',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 14,
                'ducost' => 1000,
                'dosage' => null
            ],
            [
                'did' => 172,
                'ddescription' => 'Hyoscine (Buscopan) inj. 20 mg/ 2 ml IV/IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 5,
                'ducost' => 950,
                'dosage' => null
            ],
            [
                'did' => 173,
                'ddescription' => 'Promethazine inj. 50 mg/2 ml IV/IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 15,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 174,
                'ddescription' => 'Quinine inj. 300 mg/ml in 2 ml',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 175,
                'ddescription' => 'Tramadol inj. 100 mg/2 ml IV/IM',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 8,
                'ducost' => 800,
                'dosage' => null
            ],
            [
                'did' => 176,
                'ddescription' => 'Triamcinolone inj. 40 mg',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 2,
                'ducost' => 3410,
                'dosage' => null
            ],
            [
                'did' => 177,
                'ddescription' => 'Vitamine K injection',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 178,
                'ddescription' => 'Water for injection 10 ml',
                'dtype' => 26,
                'dunit' => 26,
                'dqty' => 103,
                'ducost' => 90,
                'dosage' => null
            ],
            [
                'did' => 223,
                'ddescription' => 'Lignocaine inj. 2% w/v 30 ml IM',
                'dtype' => 23,
                'dunit' => 23,
                'dqty' => 2,
                'ducost' => 2000,
                'dosage' => null
            ]
        ];

        // Insert medications based on legacy drugs data
        foreach ($injectionDrugs as $drug) {
            // Extract generic name and strength from description
            $genericName = $this->extractGenericName($drug['ddescription']);
            $strength = $this->extractStrength($drug['ddescription']);
            
            // Get formulation ID (injections)
            $formulation_id = 5; // Injection
            
            // Get dispensing unit ID (ampoules/vials)
            $dispensing_unit_id = 11; // Ampoule
            
            // Determine category (all medications go to general medicines category)
            $category_id = 1; // Assuming category 1 is for general medicines
            
            $medicationData = [
                'generic_name' => $genericName,
                'brand_name' => null, // Not specified in legacy data
                'strength' => $strength,
                'formulation_id' => $formulation_id,
                'dispensing_unit_id' => $dispensing_unit_id,
                'description' => $drug['ddescription'],
                'category_id' => $category_id,
                'stock_quantity' => $drug['dqty'],
                'reorder_level' => max(5, $drug['dqty'] * 0.1), // 10% of current stock as reorder level, min 5
                'maximum_stock_level' => $drug['dqty'] * 2, // Double current stock as maximum for injectables
                'requires_prescription' => true, // All injectables require prescription
                'is_controlled' => $this->isControlledSubstance($drug['ddescription']),
                'storage_conditions' => 'Store in refrigerator (2-8°C), protect from light',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ];

            DB::table('medications')->insert($medicationData);
        }

        $this->command->info('Successfully migrated ' . count($injectionDrugs) . ' injectable medications from legacy drugs table.');
    }

    /**
     * Extract generic name from drug description
     */
    private function extractGenericName($description)
    {
        // Remove injection-specific terms and dosage information
        $name = preg_replace('/\s*(inj\.|injection)\s*/i', '', $description);
        $name = preg_replace('/\s*\d+([.,]\d+)?\s*(mg|mcg|g|ml|iu|mu)\b.*$/i', '', $name);
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        $name = preg_replace('/\s*(IV|IM|IV\/IM)\s*/i', '', $name);
        $name = trim($name);
        
        // Clean up common combinations
        $name = str_replace([' + ', '+'], ' / ', $name);
        
        return $name ?: 'Unknown';
    }

    /**
     * Extract strength from drug description
     */
    private function extractStrength($description)
    {
        // Look for dosage patterns including injection volumes
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|iu|mu)\/(\d+)\s*ml/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]) . '/' . $matches[4] . 'ml';
        }
        
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|ml|iu|mu)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        // Look for combination strengths
        if (preg_match('/(\d+\/\d+([.,]\d+)?)\s*(mg|mcg|g|ml)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        return null;
    }

    /**
     * Determine if medication is a controlled substance
     */
    private function isControlledSubstance($description)
    {
        $controlledSubstances = [
            'tramadol', 'morphine', 'codeine', 'diazepam', 'lorazepam',
            'midazolam', 'ketamine', 'fentanyl', 'pethidine', 'propofol'
        ];

        $description = strtolower($description);
        foreach ($controlledSubstances as $substance) {
            if (strpos($description, $substance) !== false) {
                return true;
            }
        }

        return false;
    }
}
