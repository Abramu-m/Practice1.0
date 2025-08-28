<?php

use Illuminate\Database\Seeder;
use App\Models\StoreUnit;

class StoreUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Kilogram',
                'code' => 'KG',
                'description' => 'Unit for measuring weight in kilograms',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Gram',
                'code' => 'G',
                'description' => 'Unit for measuring weight in grams',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Milligram',
                'code' => 'MG',
                'description' => 'Unit for measuring small quantities in milligrams',
                'type' => 'dispensing',
                'is_active' => true,
            ],
            [
                'name' => 'Liter',
                'code' => 'L',
                'description' => 'Unit for measuring volume in liters',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Milliliter',
                'code' => 'ML',
                'description' => 'Unit for measuring volume in milliliters',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Tablet',
                'code' => 'TAB',
                'description' => 'Unit for counting tablets',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Capsule',
                'code' => 'CAP',
                'description' => 'Unit for counting capsules',
                'type' => 'both',
                'is_active' => true,
            ],
            [
                'name' => 'Bottle',
                'code' => 'BTL',
                'description' => 'Unit for counting bottles',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Box',
                'code' => 'BOX',
                'description' => 'Unit for counting boxes',
                'type' => 'store',
                'is_active' => true,
            ],
            [
                'name' => 'Pack',
                'code' => 'PACK',
                'description' => 'Unit for counting packs',
                'type' => 'store',
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            StoreUnit::firstOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
