<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StoreUnit;

class StoreUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Store units (large quantities)
            [
                'name' => 'Box',
                'code' => 'BOX',
                'description' => 'Standard packaging box for bulk items',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Carton',
                'code' => 'CARTON',
                'description' => 'Large carton for multiple boxes',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Case',
                'code' => 'CASE',
                'description' => 'Case packaging for bulk storage',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Crate',
                'code' => 'CRATE',
                'description' => 'Large crate for bulk items',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Pallet',
                'code' => 'PALLET',
                'description' => 'Pallet for very large quantities',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Gallon',
                'code' => 'GAL',
                'description' => 'Gallon container for liquids',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Liter Bottle',
                'code' => 'L_BOTTLE',
                'description' => 'Large liter bottle',
                'type' => 'store',
                'is_active' => true,
            ],

            // Dispensing units (individual/small quantities)
            [
                'name' => 'Tablet',
                'code' => 'TAB',
                'description' => 'Individual tablet',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Capsule',
                'code' => 'CAP',
                'description' => 'Individual capsule',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Ampoule',
                'code' => 'AMP',
                'description' => 'Individual ampoule',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Vial',
                'code' => 'VIAL',
                'description' => 'Individual vial',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Syringe',
                'code' => 'SYR',
                'description' => 'Individual syringe',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Milliliter',
                'code' => 'ML',
                'description' => 'Milliliter for liquid medications',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Gram',
                'code' => 'G',
                'description' => 'Gram for solid medications',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Milligram',
                'code' => 'MG',
                'description' => 'Milligram for precise dosing',
                'type' => 'dispensing',
                'is_active' => true,
            ],

            // Both store and dispensing
            [
                'name' => 'Bottle',
                'code' => 'BOTTLE',
                'description' => 'Bottle for various medications',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Tube',
                'code' => 'TUBE',
                'description' => 'Tube for creams and ointments',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Sachet',
                'code' => 'SACHET',
                'description' => 'Sachet for powders and liquids',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Jar',
                'code' => 'JAR',
                'description' => 'Jar for creams and powders',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Pack',
                'code' => 'PACK',
                'description' => 'Generic pack unit',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Strip',
                'code' => 'STRIP',
                'description' => 'Strip of tablets/capsules',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Blister',
                'code' => 'BLISTER',
                'description' => 'Blister pack',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Kit',
                'code' => 'KIT',
                'description' => 'Medical kit or set',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Unit',
                'code' => 'UNIT',
                'description' => 'Generic unit',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Piece',
                'code' => 'PC',
                'description' => 'Individual piece',
                'type' => 'both',
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            StoreUnit::create($unit);
        }
    }
}
