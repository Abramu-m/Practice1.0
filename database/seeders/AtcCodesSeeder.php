<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtcCodesSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            ['code' => 'N02BE01', 'name' => 'Paracetamol', 'level' => '5'],
            ['code' => 'M01AE01', 'name' => 'Ibuprofen', 'level' => '5'],
            // Add more as needed
        ];

        foreach ($codes as $code) {
            DB::table('atc_codes')->updateOrInsert(
                ['code' => $code['code']],
                ['name' => $code['name'], 'level' => $code['level'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
