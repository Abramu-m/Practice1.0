<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicationFormulation;

class MedicationFormulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if formulations already exist to prevent duplicates
        if (MedicationFormulation::count() > 0) {
            $this->command->info('Medication formulations already exist. Skipping seeder.');
            return;
        }

        $formulations = [
            'Tablet',
            'Capsule',
            'Syrup',
            'Suspension',
            'Injection',
            'Cream',
            'Ointment',
            'Gel',
            'Drops',
            'Inhaler',
            'Powder',
            'Solution',
            'Lotion',
            'Patch',
            'Suppository',
            'Spray',
            'Granules',
            'Elixir',
            'Emulsion',
            'Foam'
        ];

        foreach ($formulations as $formulation) {
            MedicationFormulation::firstOrCreate(
                ['description' => $formulation],
                ['is_active' => true]
            );
        }

        $this->command->info('Medication formulations seeded successfully.');
    }
}
