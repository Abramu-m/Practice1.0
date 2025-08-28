<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyDrugsLiquidsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Liquid medications - syrups, suspensions, drops (dtype = 25)
        $liquidDrugs = [
            [
                'did' => 182,
                'ddescription' => 'Amoxicilline+Clavulanic Acid Syrup',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 183,
                'ddescription' => 'Amoxicilline Syrup 125mg/5ml in 100 ml ',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 27,
                'ducost' => 990,
                'dosage' => null
            ],
            [
                'did' => 184,
                'ddescription' => 'Ampicillin + Cloxacillin Syrup 100 ml (Ampiclox) 125 mg/125 mg per 5 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 21,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 185,
                'ddescription' => 'Antacid liquid preparation (Magnesium/Mg Susp) 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 253,
                'ducost' => 1200,
                'dosage' => null
            ],
            [
                'did' => 186,
                'ddescription' => 'BBE emulsion 25% (Benzyl benzoic emulsion)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 10,
                'ducost' => 880,
                'dosage' => null
            ],
            [
                'did' => 187,
                'ddescription' => 'Belladonna mixture 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 18,
                'ducost' => 1000,
                'dosage' => null
            ],
            [
                'did' => 188,
                'ddescription' => 'Betamethasone + Gentamicin eye drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 189,
                'ddescription' => 'Betamethasone eye ear drops 0,5%',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 190,
                'ddescription' => 'Calamine lotion',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 9,
                'ducost' => 880,
                'dosage' => null
            ],
            [
                'did' => 191,
                'ddescription' => 'Cefadroxil Syrup',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 192,
                'ddescription' => 'Cefixime Syrup',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 193,
                'ddescription' => 'Cefuroxime Syrup',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 194,
                'ddescription' => 'Cephalexine Syrup 100 ml (125mg per 5 ml)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 27,
                'ducost' => 2000,
                'dosage' => null
            ],
            [
                'did' => 195,
                'ddescription' => 'Cetirizine Syrup 5mg/ml in 60 ml ',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 2,
                'ducost' => 1000,
                'dosage' => null
            ],
            [
                'did' => 196,
                'ddescription' => 'Chloramphenicol EYE drops 5 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 16,
                'ducost' => 450,
                'dosage' => null
            ],
            [
                'did' => 197,
                'ddescription' => 'Chlorhexidine Mouthwash 0.2 % (Clenora) 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 198,
                'ddescription' => 'Chlorinated lime 100 ml (Eusol)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 10,
                'ducost' => 350,
                'dosage' => null
            ],
            [
                'did' => 199,
                'ddescription' => 'Ciprofloxacine eye/ ear drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 17,
                'ducost' => 900,
                'dosage' => null
            ],
            [
                'did' => 200,
                'ddescription' => 'Clotrimazole ear drops 1%',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 201,
                'ddescription' => 'Cotrimoxazole  suspension 240 mg/5 ml in 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 8,
                'ducost' => 1050,
                'dosage' => null
            ],
            [
                'did' => 203,
                'ddescription' => 'Cromoglycate eye drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 11,
                'ducost' => 2250,
                'dosage' => null
            ],
            [
                'did' => 204,
                'ddescription' => 'Dexamethasone-Neomycin eye/ear drops 10ml (DexaNeo)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 39,
                'ducost' => 900,
                'dosage' => null
            ],
            [
                'did' => 205,
                'ddescription' => 'Dexamethasone eye drops 5 mls',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 1000,
                'dosage' => null
            ],
            [
                'did' => 206,
                'ddescription' => 'Dexamethasone Gentamicin eye/ear drops 5 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 1000,
                'dosage' => null
            ],
            [
                'did' => 207,
                'ddescription' => 'Ephedrine nasal drops (adults)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 6,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 208,
                'ddescription' => 'Ephedrine nasal drops (pediatric)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 3,
                'ducost' => 1650,
                'dosage' => null
            ],
            [
                'did' => 209,
                'ddescription' => 'Erythromycine Syrup 100 ml (125 mg per 5 ml)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 21,
                'ducost' => 1200,
                'dosage' => null
            ],
            [
                'did' => 212,
                'ddescription' => 'GV paint 30 ml (Gentian Violet)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 5,
                'ducost' => 850,
                'dosage' => null
            ],
            [
                'did' => 213,
                'ddescription' => 'Gentamicin Eye/Ear drops 5 mls',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 49,
                'ducost' => 490,
                'dosage' => null
            ],
            [
                'did' => 214,
                'ddescription' => 'Glycerine phenol ear drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 215,
                'ddescription' => 'H2O2  100 ml 3 % (Mouth wash Hydrogen Peroxide)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 10,
                'ducost' => 450,
                'dosage' => null
            ],
            [
                'did' => 216,
                'ddescription' => 'H2O2  100 ml 6 % (Hydrogen Peroxide)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 11,
                'ducost' => 450,
                'dosage' => null
            ],
            [
                'did' => 217,
                'ddescription' => 'H2O2 - ear drops (Hydrogen Peroxide)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 218,
                'ddescription' => 'Hydrocortisone eye drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 2800,
                'dosage' => null
            ],
            [
                'did' => 220,
                'ddescription' => 'Iodine Tincture solution',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 950,
                'dosage' => null
            ],
            [
                'did' => 224,
                'ddescription' => 'Mebendazole susp. 100mg/5mls',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 225,
                'ddescription' => 'Multivitamine Syrup',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 44,
                'ducost' => 1150,
                'dosage' => null
            ],
            [
                'did' => 226,
                'ddescription' => 'Nystatin suspension 1,000,000 IU/ml in 30 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 29,
                'ducost' => 1300,
                'dosage' => null
            ],
            [
                'did' => 227,
                'ddescription' => 'Paracetamol (PCM/PCA) Syrup 125 mg/5 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 98,
                'ducost' => 1200,
                'dosage' => null
            ],
            [
                'did' => 228,
                'ddescription' => 'Paraffin liquid 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 229,
                'ddescription' => 'Penicillin Syrup 125mg/5ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 230,
                'ddescription' => 'Pilocarpine eye drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 231,
                'ddescription' => 'Podophyllin topical solution ',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 232,
                'ddescription' => 'Potassium Permanganate (PPM) 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 107,
                'ducost' => 700,
                'dosage' => null
            ],
            [
                'did' => 233,
                'ddescription' => 'Prednisolone eye drops',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 234,
                'ddescription' => 'Salicyic acid + Sulphur',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 235,
                'ddescription' => 'Salicyic acid topical solution 5 %',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 236,
                'ddescription' => 'Spirit methylated 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 213,
                'ducost' => 600,
                'dosage' => null
            ],
            [
                'did' => 237,
                'ddescription' => 'Timolol eye drops 0,25% or 0,5%  ',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 30,
                'ducost' => 1800,
                'dosage' => null
            ],
            [
                'did' => 678,
                'ddescription' => ' Antacid suspension (Viscid/Sugar free) 100 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 15,
                'ducost' => 1450,
                'dosage' => null
            ],
            [
                'did' => 681,
                'ddescription' => 'Giemsa stain (Tulip) 500 ml',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 1,
                'ducost' => 25000,
                'dosage' => null
            ],
            [
                'did' => 682,
                'ddescription' => 'Calcimax suspension (Calcium, Magnesium, Zinc, Vit D3)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 14,
                'ducost' => 4400,
                'dosage' => null
            ],
            [
                'did' => 692,
                'ddescription' => 'Junior Care Cough Syrup',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 693,
                'ddescription' => 'Azithromycin Syrup 30 ml (200 mg per 5 ml)',
                'dtype' => 25,
                'dunit' => 25,
                'dqty' => 4,
                'ducost' => 2200,
                'dosage' => null
            ]
        ];

        // Insert medications based on legacy drugs data
        foreach ($liquidDrugs as $drug) {
            // Extract generic name and strength from description
            $genericName = $this->extractGenericName($drug['ddescription']);
            $strength = $this->extractStrength($drug['ddescription']);
            
            // Get formulation ID based on liquid type
            $formulation_id = $this->getFormulationId($drug['ddescription']);
            
            // Get dispensing unit ID (milliliters for liquids)
            $dispensing_unit_id = 5; // Milliliter
            
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

        $this->command->info('Successfully migrated ' . count($liquidDrugs) . ' liquid medications from legacy drugs table.');
    }

    /**
     * Extract generic name from drug description
     */
    private function extractGenericName($description)
    {
        // Remove liquid-specific terms and dosage information
        $name = preg_replace('/\s*(syrup|suspension|drops|solution|emulsion|lotion|mixture)\s*/i', '', $description);
        $name = preg_replace('/\s*\d+([.,]\d+)?\s*(mg|mcg|g|ml|iu|%)\b.*$/i', '', $name);
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        $name = preg_replace('/\s*\d+\s*ml\s*/i', '', $name);
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
        // Look for dosage patterns including per ml concentrations
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|iu)\/(\d+)\s*ml/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]) . '/' . $matches[4] . 'ml';
        }
        
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|ml|iu|%)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        // Look for combination strengths
        if (preg_match('/(\d+\/\d+([.,]\d+)?)\s*(mg|mcg|g|ml)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        return null;
    }

    /**
     * Get formulation ID based on liquid type
     */
    private function getFormulationId($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'syrup') !== false) {
            return 3; // Syrup
        } elseif (strpos($description, 'suspension') !== false) {
            return 4; // Suspension
        } elseif (strpos($description, 'drops') !== false) {
            return 9; // Drops
        } elseif (strpos($description, 'solution') !== false) {
            return 12; // Solution
        } elseif (strpos($description, 'lotion') !== false) {
            return 13; // Lotion
        } elseif (strpos($description, 'emulsion') !== false) {
            return 19; // Emulsion
        } else {
            return 3; // Default to Syrup
        }
    }

    /**
     * Determine if medication requires prescription
     */
    private function requiresPrescription($description)
    {
        $prescriptionRequired = [
            'antibiotics', 'antibiotic', 'amoxicillin', 'ciprofloxacin', 'erythromycin',
            'cephalexin', 'penicillin', 'azithromycin', 'cefadroxil', 'cefixime',
            'cotrimoxazole', 'chloramphenicol',
            'steroids', 'steroid', 'dexamethasone', 'betamethasone', 'prednisolone',
            'hydrocortisone',
            'controlled', 'tramadol', 'morphine', 'codeine',
            'prescription', 'eye drops', 'ear drops'
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
            'tramadol', 'morphine', 'codeine', 'diazepam', 'lorazepam',
            'midazolam', 'ketamine', 'fentanyl', 'pethidine'
        ];

        $description = strtolower($description);
        foreach ($controlledSubstances as $substance) {
            if (strpos($description, $substance) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine storage conditions for medication
     */
    private function getStorageConditions($description)
    {
        $description = strtolower($description);
        
        if (strpos($description, 'eye drops') !== false || 
            strpos($description, 'ear drops') !== false ||
            strpos($description, 'suspension') !== false) {
            return 'Store in refrigerator (2-8°C)';
        } elseif (strpos($description, 'syrup') !== false) {
            return 'Store at room temperature, protect from light';
        } elseif (strpos($description, 'solution') !== false ||
                  strpos($description, 'emulsion') !== false) {
            return 'Store at room temperature, shake before use';
        } else {
            return 'Store at room temperature, protect from moisture';
        }
    }
}
