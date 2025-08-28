<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicationUnit;

class MedicationUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Weight units
            [
                'unit_name' => 'Milligram',
                'unit_code' => 'MG',
                'unit_symbol' => 'mg',
                'unit_type' => 'weight',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Gram',
                'unit_code' => 'G',
                'unit_symbol' => 'g',
                'unit_type' => 'weight',
                'conversion_factor' => 0.001,
                'base_unit_id' => 1, // Milligram
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Kilogram',
                'unit_code' => 'KG',
                'unit_symbol' => 'kg',
                'unit_type' => 'weight',
                'conversion_factor' => 0.000001,
                'base_unit_id' => 1, // Milligram
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Microgram',
                'unit_code' => 'MCG',
                'unit_symbol' => 'mcg',
                'unit_type' => 'weight',
                'conversion_factor' => 1000,
                'base_unit_id' => 1, // Milligram
                'display_order' => 4,
                'is_active' => true,
            ],
            
            // Volume units
            [
                'unit_name' => 'Milliliter',
                'unit_code' => 'ML',
                'unit_symbol' => 'ml',
                'unit_type' => 'volume',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Liter',
                'unit_code' => 'L',
                'unit_symbol' => 'L',
                'unit_type' => 'volume',
                'conversion_factor' => 0.001,
                'base_unit_id' => 5, // Milliliter
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Fluid Ounce',
                'unit_code' => 'FL_OZ',
                'unit_symbol' => 'fl oz',
                'unit_type' => 'volume',
                'conversion_factor' => 0.033814,
                'base_unit_id' => 5, // Milliliter
                'display_order' => 7,
                'is_active' => true,
            ],
            
            // Dosage forms
            [
                'unit_name' => 'Tablet',
                'unit_code' => 'TAB',
                'unit_symbol' => 'tab',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Capsule',
                'unit_code' => 'CAP',
                'unit_symbol' => 'cap',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 9,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Vial',
                'unit_code' => 'VIAL',
                'unit_symbol' => 'vial',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 10,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Ampoule',
                'unit_code' => 'AMP',
                'unit_symbol' => 'amp',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 11,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Sachet',
                'unit_code' => 'SACHET',
                'unit_symbol' => 'sachet',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 12,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Bottle',
                'unit_code' => 'BOTTLE',
                'unit_symbol' => 'bottle',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 13,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Tube',
                'unit_code' => 'TUBE',
                'unit_symbol' => 'tube',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 14,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Patch',
                'unit_code' => 'PATCH',
                'unit_symbol' => 'patch',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 15,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Inhaler',
                'unit_code' => 'INH',
                'unit_symbol' => 'inhaler',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 16,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Syringe',
                'unit_code' => 'SYR',
                'unit_symbol' => 'syringe',
                'unit_type' => 'form',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 17,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Drops',
                'unit_code' => 'DROP',
                'unit_symbol' => 'drops',
                'unit_type' => 'dosage',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 18,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Puff',
                'unit_code' => 'PUFF',
                'unit_symbol' => 'puff',
                'unit_type' => 'dosage',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 19,
                'is_active' => true,
            ],
            [
                'unit_name' => 'Unit',
                'unit_code' => 'UNIT',
                'unit_symbol' => 'unit',
                'unit_type' => 'dosage',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 20,
                'is_active' => true,
            ],
            [
                'unit_name' => 'International Unit',
                'unit_code' => 'IU',
                'unit_symbol' => 'IU',
                'unit_type' => 'dosage',
                'conversion_factor' => null,
                'base_unit_id' => null,
                'display_order' => 21,
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            MedicationUnit::create($unit);
        }
    }
}
