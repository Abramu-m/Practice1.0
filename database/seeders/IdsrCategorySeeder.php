<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdsrCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('idsr_categories')->exists()) {
            return;
        }

        DB::table('idsr_categories')->insert([
            ['name' => 'Malaria',                    'description' => 'Malaria cases',                          'sort_order' => 1,  'is_active' => true],
            ['name' => 'Typhoid',                    'description' => 'Typhoid fever',                          'sort_order' => 2,  'is_active' => true],
            ['name' => 'Cholera',                    'description' => 'Cholera cases',                          'sort_order' => 3,  'is_active' => true],
            ['name' => 'Dysentery',                  'description' => 'Bloody diarrhea',                        'sort_order' => 4,  'is_active' => true],
            ['name' => 'Acute Respiratory Infection','description' => 'ARI cases',                              'sort_order' => 5,  'is_active' => true],
            ['name' => 'Meningitis',                 'description' => 'Meningitis cases',                       'sort_order' => 6,  'is_active' => true],
            ['name' => 'Measles',                    'description' => 'Measles cases',                          'sort_order' => 7,  'is_active' => true],
            ['name' => 'Neonatal Tetanus',           'description' => 'Tetanus in newborns',                    'sort_order' => 8,  'is_active' => true],
            ['name' => 'STI',                        'description' => 'Sexually transmitted infections',        'sort_order' => 9,  'is_active' => true],
            ['name' => 'Pertussis',                  'description' => 'Whooping cough',                         'sort_order' => 10, 'is_active' => true],
        ]);
    }
}
