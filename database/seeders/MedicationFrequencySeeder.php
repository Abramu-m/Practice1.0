<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicationFrequency;

class MedicationFrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frequencies = [
            [
                'frequency_name' => 'Once Daily',
                'frequency_code' => 'OD',
                'administration_times' => ['08:00'],
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Twice Daily',
                'frequency_code' => 'BID',
                'administration_times' => ['08:00', '20:00'],
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Three Times Daily',
                'frequency_code' => 'TID',
                'administration_times' => ['08:00', '14:00', '20:00'],
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Four Times Daily',
                'frequency_code' => 'QID',
                'administration_times' => ['06:00', '12:00', '18:00', '24:00'],
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Every 6 Hours',
                'frequency_code' => 'Q6H',
                'administration_times' => ['06:00', '12:00', '18:00', '24:00'],
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Every 8 Hours',
                'frequency_code' => 'Q8H',
                'administration_times' => ['06:00', '14:00', '22:00'],
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Every 12 Hours',
                'frequency_code' => 'Q12H',
                'administration_times' => ['08:00', '20:00'],
                'display_order' => 7,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'As Needed',
                'frequency_code' => 'PRN',
                'administration_times' => ['08:00'],
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'Before Meals',
                'frequency_code' => 'AC',
                'administration_times' => ['07:30', '12:30', '19:30'],
                'display_order' => 9,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'After Meals',
                'frequency_code' => 'PC',
                'administration_times' => ['08:30', '13:30', '20:30'],
                'display_order' => 10,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'At Bedtime',
                'frequency_code' => 'HS',
                'administration_times' => ['22:00'],
                'display_order' => 11,
                'is_active' => true,
            ],
            [
                'frequency_name' => 'In the Morning',
                'frequency_code' => 'AM',
                'administration_times' => ['08:00'],
                'display_order' => 12,
                'is_active' => true,
            ]
        ];

        foreach ($frequencies as $frequency) {
            MedicationFrequency::create($frequency);
        }
    }
}
