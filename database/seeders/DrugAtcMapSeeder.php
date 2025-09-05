<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrugAtcMapSeeder extends Seeder
{
    public function run(): void
    {
        // Map common medications to their ATC codes
        $mappings = [
            ['medication_name' => 'paracetamol', 'code' => 'N02BE01'],
            ['medication_name' => 'acetaminophen', 'code' => 'N02BE01'],
            ['medication_name' => 'ibuprofen', 'code' => 'M01AE01'],
        ];

        $codes = DB::table('atc_codes')->pluck('id', 'code');

        foreach ($mappings as $row) {
            $codeId = $codes[$row['code']] ?? null;
            if (!$codeId) continue;
            DB::table('drug_atc_map')->updateOrInsert(
                ['medication_name' => $row['medication_name']],
                ['atc_code_id' => $codeId, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
