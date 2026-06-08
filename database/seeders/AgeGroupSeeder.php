<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgeGroupSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('age_groups')->exists()) {
            return;
        }

        DB::table('age_groups')->insert([
            ['label' => '< 1 month',   'min_days' => 0,     'max_days' => 30,    'min_years' => 0,    'max_years' => 0.08,  'sort_order' => 1, 'is_active' => true],
            ['label' => '1-12 months', 'min_days' => 31,    'max_days' => 365,   'min_years' => 0.08, 'max_years' => 1,     'sort_order' => 2, 'is_active' => true],
            ['label' => '1-4 years',   'min_days' => 366,   'max_days' => 1825,  'min_years' => 1,    'max_years' => 5,     'sort_order' => 3, 'is_active' => true],
            ['label' => '5-59 years',  'min_days' => 1826,  'max_days' => 21900, 'min_years' => 5,    'max_years' => 60,    'sort_order' => 4, 'is_active' => true],
            ['label' => '60+ years',   'min_days' => 21901, 'max_days' => 999999,'min_years' => 60,   'max_years' => 999,   'sort_order' => 5, 'is_active' => true],
        ]);
    }
}
