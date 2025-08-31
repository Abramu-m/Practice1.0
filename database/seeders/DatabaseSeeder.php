<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add seeders here in the order you want them to run
        $this->call([
            MtuhaDiagnosesSeeder::class,
            // Icd10Seeder::class, // enable if you want ICD-10 seeded here
        ]);
    }
}
