<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyDrugsTopicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Topical medications - creams, ointments, gels (dtype = 24)
        $topicalDrugs = [
            [
                'did' => 238,
                'ddescription' => 'Antifungal ointment 15 g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 7,
                'ducost' => 1450,
                'dosage' => null
            ],
            [
                'did' => 239,
                'ddescription' => 'Benzylbenzoate cream 20% (BBE cream)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 17,
                'ducost' => 1100,
                'dosage' => null
            ],
            [
                'did' => 240,
                'ddescription' => 'Betamethasone valerate ointment 0.1%',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 2,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 241,
                'ddescription' => 'Calamine ointment',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 242,
                'ddescription' => 'Clobetasol propionate ointment ',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 243,
                'ddescription' => 'Clotrimazole cream 1% in 15g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 41,
                'ducost' => 950,
                'dosage' => null
            ],
            [
                'did' => 244,
                'ddescription' => 'Compound Zinc ointment (CZO)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 34,
                'ducost' => 650,
                'dosage' => null
            ],
            [
                'did' => 245,
                'ddescription' => 'Desonide cream 0.05%',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 246,
                'ddescription' => 'Dexamethasone + antibiotic ointment 15g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 18,
                'ducost' => 1400,
                'dosage' => null
            ],
            [
                'did' => 247,
                'ddescription' => 'Dexamethasone cream',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 248,
                'ddescription' => 'Diclofenac gel 1% in 10 g or 20 g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 51,
                'ducost' => 1750,
                'dosage' => null
            ],
            [
                'did' => 249,
                'ddescription' => 'Diphenhyramine ointment',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 250,
                'ddescription' => 'Econazole nitrate 1% cream (Pevaryl) 15g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 251,
                'ddescription' => 'Fucidic acid + Betamethasone 15g (Fucid-B) cream',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 32,
                'ducost' => 1800,
                'dosage' => null
            ],
            [
                'did' => 252,
                'ddescription' => 'Fucidic acid 2% cream 15g (Fucidin)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 43,
                'ducost' => 1750,
                'dosage' => null
            ],
            [
                'did' => 253,
                'ddescription' => 'Fusidic acid ointment 2%',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 254,
                'ddescription' => 'Gentamicin cream 15g tube',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 14,
                'ducost' => 1150,
                'dosage' => null
            ],
            [
                'did' => 255,
                'ddescription' => 'Hydrocortisone ointment 0.5% tube 15g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 21,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 256,
                'ddescription' => 'Ketoconazole cream 15g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 57,
                'ducost' => 1050,
                'dosage' => null
            ],
            [
                'did' => 257,
                'ddescription' => 'Mercurochrome ointment',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 258,
                'ddescription' => 'Miconazole cream 2% in 15g tube',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 45,
                'ducost' => 850,
                'dosage' => null
            ],
            [
                'did' => 259,
                'ddescription' => 'Mupirocin ointment 2%',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 260,
                'ddescription' => 'Nystatin ointment ',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 56,
                'ducost' => 850,
                'dosage' => null
            ],
            [
                'did' => 261,
                'ddescription' => 'Permethrin cream 1% or 5%',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 262,
                'ddescription' => 'Petroleum Jelly (Vaseline) 50 g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 41,
                'ducost' => 700,
                'dosage' => null
            ],
            [
                'did' => 263,
                'ddescription' => 'Terbinafine cream 1% (15g)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 22,
                'ducost' => 2550,
                'dosage' => null
            ],
            [
                'did' => 264,
                'ddescription' => 'Tetracycline ointment 3% 15g or 30g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 18,
                'ducost' => 950,
                'dosage' => null
            ],
            [
                'did' => 265,
                'ddescription' => 'Triamcinolone ointment 0.1%',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 266,
                'ddescription' => 'Triamcinolone + Gentamicin ointment (Genticyn cream)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 2,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 267,
                'ddescription' => 'Triple action ointment',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 268,
                'ddescription' => 'Whitfield ointment (Salicylic+Benzoic acid)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 50,
                'ducost' => 750,
                'dosage' => null
            ],
            [
                'did' => 269,
                'ddescription' => 'Zinc oxide ointment 50 g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 16,
                'ducost' => 700,
                'dosage' => null
            ],
            [
                'did' => 679,
                'ddescription' => 'Betamethasone + Gentamicin cream 15g (Diclon-G)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 15,
                'ducost' => 1750,
                'dosage' => null
            ],
            [
                'did' => 680,
                'ddescription' => 'Desoximetasone 0.25% cream 15g (Topicort)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 20,
                'ducost' => 3900,
                'dosage' => null
            ],
            [
                'did' => 683,
                'ddescription' => 'Triamcinolone Acetonide 0.05% cream 15g',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 684,
                'ddescription' => 'Clobetasol + Gentamicin cream 15g (Daivobet)',
                'dtype' => 24,
                'dunit' => 24,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ]
        ];

        // Insert medications based on legacy drugs data
        foreach ($topicalDrugs as $drug) {
            // Extract generic name and strength from description
            $genericName = $this->extractGenericName($drug['ddescription']);
            $strength = $this->extractStrength($drug['ddescription']);
            
            // Get formulation ID based on topical type
            $formulation_id = $this->getFormulationId($drug['ddescription']);
            
            // Get dispensing unit ID (grams for topical preparations)
            $dispensing_unit_id = 2; // Gram
            
            // Determine category (all medications go to general medicines category)
            $category_id = 1; // Assuming category 1 is for general medicines
            
            $medicationData = [
                'generic_name' => $genericName,
                'brand_name' => $this->extractBrandName($drug['ddescription']),
                'strength' => $strength,
                'formulation_id' => $formulation_id,
                'dispensing_unit_id' => $dispensing_unit_id,
                'description' => $drug['ddescription'],
                'category_id' => $category_id,
                'stock_quantity' => $drug['dqty'],
                'reorder_level' => max(5, $drug['dqty'] * 0.1), // 10% of current stock as reorder level, min 5
                'maximum_stock_level' => $drug['dqty'] * 2, // Double current stock as maximum
                'requires_prescription' => $this->requiresPrescription($drug['ddescription']),
                'is_controlled' => $this->isControlledSubstance($drug['ddescription']),
                'storage_conditions' => $this->getStorageConditions($drug['ddescription']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ];

            DB::table('medications')->insert($medicationData);
        }

        $this->command->info('Successfully migrated ' . count($topicalDrugs) . ' topical medications from legacy drugs table.');
    }

    /**
     * Extract generic name from drug description
     */
    private function extractGenericName($description)
    {
        // Remove topical-specific terms and dosage information
        $name = preg_replace('/\s*(cream|ointment|gel|lotion|jelly)\s*/i', '', $description);
        $name = preg_replace('/\s*\d+([.,]\d+)?\s*(mg|mcg|g|ml|%)\b.*$/i', '', $name);
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        $name = preg_replace('/\s*\d+\s*g\s*/i', '', $name);
        $name = trim($name);
        
        // Clean up common combinations
        $name = str_replace([' + ', '+'], ' / ', $name);
        
        return $name ?: 'Unknown';
    }

    /**
     * Extract brand name from description if present
     */
    private function extractBrandName($description)
    {
        // Look for brand names in parentheses
        if (preg_match('/\(([A-Za-z][A-Za-z0-9\-\s]+)\)/', $description, $matches)) {
            $brandName = trim($matches[1]);
            // Exclude dosage and formulation information
            if (!preg_match('/\d+(mg|mcg|g|ml|%)/i', $brandName) && 
                !preg_match('/(cream|ointment|gel|tube|lotion)/i', $brandName)) {
                return $brandName;
            }
        }
        
        return null;
    }

    /**
     * Extract strength from drug description
     */
    private function extractStrength($description)
    {
        // Look for percentage concentrations
        if (preg_match('/(\d+([.,]\d+)?)\s*%/', $description, $matches)) {
            return $matches[1] . '%';
        }
        
        // Look for mg/g or similar concentrations
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g)\/(\d+)\s*g/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]) . '/' . $matches[4] . 'g';
        }
        
        // Look for general dosage
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|ml)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        // Look for combination strengths
        if (preg_match('/(\d+\/\d+([.,]\d+)?)\s*(mg|mcg|g)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        return null;
    }

    /**
     * Get formulation ID based on topical type
     */
    private function getFormulationId($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'cream') !== false) {
            return 6; // Cream
        } elseif (strpos($description, 'ointment') !== false) {
            return 7; // Ointment
        } elseif (strpos($description, 'gel') !== false) {
            return 8; // Gel
        } elseif (strpos($description, 'lotion') !== false) {
            return 13; // Lotion
        } elseif (strpos($description, 'jelly') !== false || strpos($description, 'vaseline') !== false) {
            return 7; // Ointment (closest to jelly)
        } else {
            return 6; // Default to Cream
        }
    }

    /**
     * Determine if medication requires prescription
     */
    private function requiresPrescription($description)
    {
        $prescriptionRequired = [
            'antibiotics', 'antibiotic', 'gentamicin', 'tetracycline', 'mupirocin',
            'fucidic acid', 'fucidin',
            'steroids', 'steroid', 'dexamethasone', 'betamethasone', 'hydrocortisone',
            'triamcinolone', 'clobetasol', 'desonide', 'desoximetasone',
            'antifungal', 'ketoconazole', 'terbinafine', 'econazole',
            'controlled', 'prescription'
        ];

        $description = strtolower($description);
        foreach ($prescriptionRequired as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return true;
            }
        }

        return false; // Default to non-prescription
    }

    /**
     * Determine if medication is a controlled substance
     */
    private function isControlledSubstance($description)
    {
        $controlledSubstances = [
            'tramadol', 'morphine', 'codeine', 'diazepam', 'lorazepam'
        ];

        $description = strtolower($description);
        foreach ($controlledSubstances as $substance) {
            if (strpos($description, $substance) !== false) {
                return true;
            }
        }

        return false; // Topical preparations rarely controlled
    }

    /**
     * Determine storage conditions for medication
     */
    private function getStorageConditions($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'cream') !== false || strpos($description, 'ointment') !== false) {
            return 'Store at room temperature, protect from heat';
        } elseif (strpos($description, 'gel') !== false) {
            return 'Store at room temperature, do not freeze';
        } else {
            return 'Store at room temperature';
        }
    }
}
