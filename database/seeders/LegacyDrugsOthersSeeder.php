<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyDrugsOthersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Other medications - suppositories, powders, special preparations (dtype = 22 and others)
        $otherDrugs = [
            [
                'did' => 270,
                'ddescription' => 'Albendazole powder 200mg (Zentel) ',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 271,
                'ddescription' => 'Benzyl benzoate',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 272,
                'ddescription' => 'Calcium Gluconate powder',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 273,
                'ddescription' => 'Clotrimazole pessaries 500 mg',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 16,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 274,
                'ddescription' => 'Glycerine suppository pediatric ',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 275,
                'ddescription' => 'Glycerine suppository adults',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 276,
                'ddescription' => 'Iron + Vit C powder (sachet)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 277,
                'ddescription' => 'Iron plus folic powder',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 38,
                'ducost' => 650,
                'dosage' => null
            ],
            [
                'did' => 278,
                'ddescription' => 'Magnesium sulphate powder (MgSO4)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 5,
                'ducost' => 1050,
                'dosage' => null
            ],
            [
                'did' => 279,
                'ddescription' => 'Metronidazole pessaries',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 37,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 280,
                'ddescription' => 'Oral Rehydration salt (ORS) sachets 1 litre',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 280,
                'ducost' => 220,
                'dosage' => null
            ],
            [
                'did' => 281,
                'ddescription' => 'Sodium bicarbonate powder',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 15,
                'ducost' => 700,
                'dosage' => null
            ],
            [
                'did' => 282,
                'ddescription' => 'Sulphur powder ',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 695,
                'ddescription' => 'Metronidazole 500mg pessaries',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 696,
                'ddescription' => 'Intrasite gel (wounds dressing gel) 15g',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 697,
                'ddescription' => 'Moist wound care pads (small)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 698,
                'ddescription' => 'Moist wound care pads (medium)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 699,
                'ddescription' => 'Moist wound care pads (large)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 700,
                'ddescription' => 'Surgical scrub (Betadine scrub) 500ml',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 701,
                'ddescription' => 'Povidone iodine solution 10% 100ml',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 702,
                'ddescription' => 'Chlorhexidine 0.5% in 70% alcohol 500ml',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 703,
                'ddescription' => 'Hand sanitizer gel 500ml',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 704,
                'ddescription' => 'Surgical spirit 70% 500ml',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 705,
                'ddescription' => 'Wound care honey gel 25g',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 706,
                'ddescription' => 'Silver sulfadiazine cream 1% 50g',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 707,
                'ddescription' => 'Zinc oxide tape 2.5cm x 5m',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 708,
                'ddescription' => 'Elastic bandage 7.5cm x 4m',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 709,
                'ddescription' => 'Cotton gauze bandage 5cm x 4m',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 710,
                'ddescription' => 'Sterile gauze pads 5cm x 5cm (pack of 10)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 711,
                'ddescription' => 'Non-adherent dressing pads 10cm x 10cm',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 712,
                'ddescription' => 'Adhesive plasters assorted sizes (box)',
                'dtype' => 22,
                'dunit' => 22,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ]
        ];

        // Insert medications based on legacy drugs data
        foreach ($otherDrugs as $drug) {
            // Extract generic name and strength from description
            $genericName = $this->extractGenericName($drug['ddescription']);
            $strength = $this->extractStrength($drug['ddescription']);
            
            // Get formulation ID based on preparation type
            $formulation_id = $this->getFormulationId($drug['ddescription']);
            
            // Get dispensing unit ID based on preparation type
            $dispensing_unit_id = $this->getDispensingUnit($drug['ddescription']);
            
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
                'reorder_level' => max(5, $drug['dqty'] * 0.15), // 15% of current stock as reorder level, min 5
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

        $this->command->info('Successfully migrated ' . count($otherDrugs) . ' other medications from legacy drugs table.');
    }

    /**
     * Extract generic name from drug description
     */
    private function extractGenericName($description)
    {
        // Remove formulation-specific terms and dosage information
        $name = preg_replace('/\s*(powder|pessaries|pessary|suppository|sachet|sachets|gel|solution|scrub|sanitizer|spirit|tape|bandage|pads|pad|dressing)\s*/i', '', $description);
        $name = preg_replace('/\s*\d+([.,]\d+)?\s*(mg|mcg|g|ml|cm|m|%)\b.*$/i', '', $name);
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        $name = preg_replace('/\s*\d+\s*(ml|g|cm|m)\s*/i', '', $name);
        $name = preg_replace('/\s*(pack\s+of\s+\d+|box|small|medium|large|adults?|pediatric)\s*/i', '', $name);
        $name = trim($name);
        
        // Clean up common combinations
        $name = str_replace([' + ', '+', ' plus '], ' / ', $name);
        
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
            // Exclude dosage, formulation, and size information
            if (!preg_match('/\d+(mg|mcg|g|ml|cm|%)/i', $brandName) && 
                !preg_match('/(powder|sachet|pack|gel|solution|small|medium|large)/i', $brandName)) {
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
        
        // Look for dosage in mg, mcg, g
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        // Look for volume in ml
        if (preg_match('/(\d+([.,]\d+)?)\s*ml\b/i', $description, $matches)) {
            return $matches[1] . ' ml';
        }
        
        // Look for ORS sachets (special case)
        if (preg_match('/(\d+)\s*litre/i', $description, $matches)) {
            return $matches[1] . ' litre';
        }
        
        return null;
    }

    /**
     * Get formulation ID based on preparation type
     */
    private function getFormulationId($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'powder') !== false || strpos($description, 'sachets') !== false) {
            return 11; // Powder
        } elseif (strpos($description, 'pessaries') !== false || strpos($description, 'pessary') !== false) {
            return 15; // Suppository (closest to pessary)
        } elseif (strpos($description, 'suppository') !== false) {
            return 15; // Suppository
        } elseif (strpos($description, 'gel') !== false) {
            return 8; // Gel
        } elseif (strpos($description, 'solution') !== false || strpos($description, 'spirit') !== false) {
            return 12; // Solution
        } elseif (strpos($description, 'bandage') !== false || strpos($description, 'tape') !== false) {
            return 11; // Powder (for medical supplies, use powder as generic)
        } elseif (strpos($description, 'pads') !== false || strpos($description, 'pad') !== false || strpos($description, 'dressing') !== false) {
            return 11; // Powder (for medical supplies)
        } else {
            return 11; // Default to Powder for other preparations
        }
    }

    /**
     * Get dispensing unit based on preparation type
     */
    private function getDispensingUnit($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'ml') !== false) {
            return 5; // Milliliter
        } elseif (strpos($description, 'gram') !== false || strpos($description, ' g ') !== false || preg_match('/\d+g\b/', $description)) {
            return 2; // Gram
        } elseif (strpos($description, 'sachets') !== false || strpos($description, 'sachet') !== false) {
            return 12; // Sachet
        } elseif (strpos($description, 'pessaries') !== false || strpos($description, 'pessary') !== false || 
                  strpos($description, 'suppository') !== false) {
            return 8; // Tablet (as piece equivalent)
        } elseif (strpos($description, 'bandage') !== false || strpos($description, 'tape') !== false || 
                  strpos($description, 'pack') !== false || strpos($description, 'box') !== false) {
            return 8; // Tablet (as piece equivalent)
        } else {
            return 8; // Default to Tablet (as piece equivalent)
        }
    }

    /**
     * Determine if medication requires prescription
     */
    private function requiresPrescription($description)
    {
        $prescriptionRequired = [
            'antibiotics', 'antibiotic', 'metronidazole', 'clotrimazole',
            'steroids', 'steroid', 'silver sulfadiazine',
            'antifungal', 'albendazole',
            'controlled', 'prescription'
        ];

        $nonPrescription = [
            'oral rehydration', 'ors', 'iron', 'calcium', 'vitamin',
            'magnesium', 'sodium bicarbonate', 'glycerine', 'sulphur',
            'bandage', 'gauze', 'tape', 'dressing', 'plaster',
            'sanitizer', 'spirit', 'wound care'
        ];

        $description = strtolower($description);
        
        // Check non-prescription items first
        foreach ($nonPrescription as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return false;
            }
        }
        
        // Check prescription-required items
        foreach ($prescriptionRequired as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return true;
            }
        }

        return false; // Default to non-prescription for medical supplies
    }

    /**
     * Determine if medication is a controlled substance
     */
    private function isControlledSubstance($description)
    {
        // Most medical supplies and non-drug preparations are not controlled
        return false;
    }

    /**
     * Determine storage conditions for medication
     */
    private function getStorageConditions($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'pessaries') !== false || strpos($description, 'suppository') !== false) {
            return 'Store in refrigerator (2-8°C)';
        } elseif (strpos($description, 'powder') !== false || strpos($description, 'sachets') !== false) {
            return 'Store in dry place, protect from moisture';
        } elseif (strpos($description, 'gel') !== false || strpos($description, 'solution') !== false) {
            return 'Store at room temperature, protect from light';
        } else {
            return 'Store at room temperature in dry place';
        }
    }
}
