<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyDrugsCapsulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Capsules and other formulations (dtype = 2)
        $capsuleDrugs = [
            [
                'did' => 238,
                'ddescription' => 'Amoxicilline 250 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 74000,
                'ducost' => 39.5,
                'dosage' => null
            ],
            [
                'did' => 239,
                'ddescription' => 'Ampiclox caps. 250 mg/250 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 1800,
                'ducost' => 88,
                'dosage' => null
            ],
            [
                'did' => 240,
                'ddescription' => 'Azithromycin 250 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 166.67,
                'dosage' => null
            ],
            [
                'did' => 241,
                'ddescription' => 'Azithromycin 500 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 270,
                'ducost' => 566.67,
                'dosage' => null
            ],
            [
                'did' => 242,
                'ddescription' => 'Cefaclor 500 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 243,
                'ddescription' => 'Cefadroxil 500 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 939,
                'ducost' => 290,
                'dosage' => null
            ],
            [
                'did' => 244,
                'ddescription' => 'Cefixime 400 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 250,
                'ducost' => 400,
                'dosage' => null
            ],
            [
                'did' => 245,
                'ddescription' => 'Cephalexine 250 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 1800,
                'ducost' => 88,
                'dosage' => null
            ],
            [
                'did' => 246,
                'ddescription' => 'Doxycycline 100 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 9300,
                'ducost' => 60,
                'dosage' => null
            ],
            [
                'did' => 247,
                'ddescription' => 'Ferrous and Folic Acid 200/5 mg (Hemovit) Capsules',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 11580,
                'ducost' => 110,
                'dosage' => null
            ],
            [
                'did' => 248,
                'ddescription' => 'Flucloxacilline + Amoxycilline 250/250mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 249,
                'ddescription' => 'Tetracycline capsules (TCL) 250 mg',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 100,
                'ducost' => 55,
                'dosage' => null
            ],
            [
                'did' => 505,
                'ddescription' => 'V2 Plus (vitamins + minerals + Phospholipids + Ginseng)',
                'dtype' => 2,
                'dunit' => 2,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 532,
                'ddescription' => 'Amlodipine 5 mg + Losartan 50 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 534,
                'ddescription' => 'Artemether + Lumefantrine (ALU) 18 tabs',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 1000,
                'dosage' => null
            ],
            [
                'did' => 535,
                'ddescription' => 'Artemether + Lumefantrine (ALU) 12 tabs',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 700,
                'dosage' => null
            ],
            [
                'did' => 536,
                'ddescription' => 'Artemether + Lumefantrine (ALU) 6 tabs',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 350,
                'dosage' => null
            ],
            [
                'did' => 507,
                'ddescription' => 'Paracetamol 500 mg + Caffeine 65 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 559,
                'ddescription' => 'Paracetamol (PCM/PCA) sustained release 1000 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 670,
                'ddescription' => 'Telmisartan 20 mg ',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 671,
                'ddescription' => 'Esomeprazole 20 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 672,
                'ddescription' => 'Neurostrong (vit B1, B6. B12)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 230,
                'ducost' => 356.67,
                'dosage' => null
            ],
            [
                'did' => 673,
                'ddescription' => 'Glucosamine 1000 mg + Chondroitin 800 mg (Novaflex)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 80,
                'ducost' => 483.33,
                'dosage' => null
            ],
            [
                'did' => 676,
                'ddescription' => 'Rifampicin 75 mg, Isoniazide  150 mg, Pyrazinamide 150 mg (RHZ)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 336,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 677,
                'ddescription' => 'Ethambutol 100 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 300,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 680,
                'ddescription' => 'Cefixime 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 0,
                'ducost' => 400,
                'dosage' => null
            ],
            [
                'did' => 683,
                'ddescription' => 'Fluconazole 200 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 14,
                'ducost' => 0,
                'dosage' => null
            ],
            [
                'did' => 685,
                'ddescription' => 'Moxifloxacin 400 mg',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 40,
                'ducost' => 900,
                'dosage' => null
            ],
            [
                'did' => 689,
                'ddescription' => 'Paracetamol 200 mg, Aspirin 300 mg, Caffeine 50 mg (Cafenol)',
                'dtype' => 1,
                'dunit' => 1,
                'dqty' => 685,
                'ducost' => 0,
                'dosage' => null
            ]
        ];

        // Insert medications based on legacy drugs data
        foreach ($capsuleDrugs as $drug) {
            // Extract generic name and strength from description
            $genericName = $this->extractGenericName($drug['ddescription']);
            $strength = $this->extractStrength($drug['ddescription']);
            
            // Get formulation ID (capsules and tablets)
            $formulation_id = $this->getFormulationId($drug['dtype']);
            
            // Get dispensing unit ID
            $dispensing_unit_id = $this->getDispensingUnitId($drug['dunit']);
            
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
                'reorder_level' => max(10, $drug['dqty'] * 0.1), // 10% of current stock as reorder level
                'maximum_stock_level' => $drug['dqty'] * 3, // Triple current stock as maximum
                'requires_prescription' => $this->requiresPrescription($drug['ddescription']),
                'is_controlled' => $this->isControlledSubstance($drug['ddescription']),
                'storage_conditions' => $this->getStorageConditions($drug['ddescription']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ];

            DB::table('medications')->insert($medicationData);
        }

        $this->command->info('Successfully migrated ' . count($capsuleDrugs) . ' capsule medications from legacy drugs table.');
    }

    /**
     * Extract generic name from drug description
     */
    private function extractGenericName($description)
    {
        // Remove dosage information and brand names in parentheses
        $name = preg_replace('/\s*\d+([.,]\d+)?\s*(mg|mcg|g|ml|iu|mu)\b.*$/i', '', $description);
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
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
        // Look for dosage patterns
        if (preg_match('/(\d+([.,]\d+)?)\s*(mg|mcg|g|ml|iu|mu)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        // Look for combination strengths like 50/500 mg
        if (preg_match('/(\d+\/\d+([.,]\d+)?)\s*(mg|mcg|g|ml)\b/i', $description, $matches)) {
            return $matches[1] . ' ' . strtolower($matches[3]);
        }
        
        return null;
    }

    /**
     * Get formulation ID based on drug type
     */
    private function getFormulationId($dtype)
    {
        switch ($dtype) {
            case 1: return 1; // Tablet
            case 2: return 2; // Capsule  
            case 25: return 3; // Syrup/Liquid
            case 26: return 5; // Injection
            case 24: return 7; // Cream/Ointment
            case 22: return 10; // Other/Inhaler
            default: return 1; // Default to Tablet
        }
    }

    /**
     * Get dispensing unit ID based on drug unit
     */
    private function getDispensingUnitId($dunit)
    {
        switch ($dunit) {
            case 1: return 8; // Tablet
            case 2: return 9; // Capsule
            case 25: return 5; // Milliliter (for syrups)
            case 26: return 10; // Ampoule (for injections)
            case 24: return 14; // Tube (for creams)
            case 22: return 16; // Inhaler
            case 30: return 15; // Patch/Suppository
            default: return 8; // Default to Tablet
        }
    }

    /**
     * Determine if medication requires prescription
     */
    private function requiresPrescription($description)
    {
        $prescriptionRequired = [
            'antibiotics', 'antibiotic', 'amoxicillin', 'ciprofloxacin', 'erythromycin',
            'metronidazole', 'doxycycline', 'cephalexin', 'penicillin', 'norfloxacin',
            'azithromycin', 'cefaclor', 'cefadroxil', 'cefixime', 'tetracycline',
            'controlled', 'diazepam', 'tramadol', 'morphine', 'codeine',
            'antipsychotic', 'haloperidol', 'chlorpromazine',
            'cardiovascular', 'digoxin', 'atenolol', 'amlodipine', 'lisinopril',
            'diabetes', 'metformin', 'glibenclamide', 'insulin'
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
            'midazolam', 'ketamine', 'fentanyl', 'pethidine', 'alprazolam'
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
        
        if (strpos($description, 'capsule') !== false) {
            return 'Store at room temperature, protect from moisture';
        } else {
            return 'Store at room temperature';
        }
    }
}
