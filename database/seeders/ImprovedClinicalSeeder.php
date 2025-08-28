<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImprovedClinicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Service Categories
        $serviceCategories = [
            ['name' => 'Laboratory Tests', 'description' => 'Blood tests, urine tests, and other lab investigations'],
            ['name' => 'Radiology', 'description' => 'X-rays, CT scans, MRI, ultrasound'],
            ['name' => 'Cardiology', 'description' => 'ECG, Echo, stress tests'],
            ['name' => 'Procedures', 'description' => 'Minor surgical procedures'],
            ['name' => 'Consultation', 'description' => 'Medical consultations and reviews'],
        ];

        foreach ($serviceCategories as $category) {
            DB::table('service_categories')->insertOrIgnore($category + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Seed Administration Routes
        $routes = [
            ['name' => 'Oral', 'abbreviation' => 'PO', 'description' => 'By mouth'],
            ['name' => 'Intravenous', 'abbreviation' => 'IV', 'description' => 'Into a vein'],
            ['name' => 'Intramuscular', 'abbreviation' => 'IM', 'description' => 'Into muscle'],
            ['name' => 'Subcutaneous', 'abbreviation' => 'SC', 'description' => 'Under the skin'],
            ['name' => 'Topical', 'abbreviation' => 'TOP', 'description' => 'Applied to skin'],
            ['name' => 'Inhaled', 'abbreviation' => 'INH', 'description' => 'Breathed in'],
            ['name' => 'Rectal', 'abbreviation' => 'PR', 'description' => 'Via rectum'],
            ['name' => 'Sublingual', 'abbreviation' => 'SL', 'description' => 'Under the tongue'],
        ];

        foreach ($routes as $route) {
            DB::table('administration_routes')->insertOrIgnore($route + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Seed Medication Frequencies
        $frequencies = [
            ['name' => 'Once daily', 'abbreviation' => 'OD', 'times_per_day' => 1, 'description' => 'Once per day'],
            ['name' => 'Twice daily', 'abbreviation' => 'BD', 'times_per_day' => 2, 'description' => 'Twice per day'],
            ['name' => 'Three times daily', 'abbreviation' => 'TDS', 'times_per_day' => 3, 'description' => 'Three times per day'],
            ['name' => 'Four times daily', 'abbreviation' => 'QDS', 'times_per_day' => 4, 'description' => 'Four times per day'],
            ['name' => 'Every 4 hours', 'abbreviation' => 'Q4H', 'times_per_day' => 6, 'description' => 'Every 4 hours'],
            ['name' => 'Every 6 hours', 'abbreviation' => 'Q6H', 'times_per_day' => 4, 'description' => 'Every 6 hours'],
            ['name' => 'Every 8 hours', 'abbreviation' => 'Q8H', 'times_per_day' => 3, 'description' => 'Every 8 hours'],
            ['name' => 'Every 12 hours', 'abbreviation' => 'Q12H', 'times_per_day' => 2, 'description' => 'Every 12 hours'],
            ['name' => 'As needed', 'abbreviation' => 'PRN', 'times_per_day' => 0, 'description' => 'As required/needed'],
            ['name' => 'At bedtime', 'abbreviation' => 'HS', 'times_per_day' => 1, 'description' => 'At bedtime'],
        ];

        foreach ($frequencies as $frequency) {
            DB::table('medication_frequencies')->insertOrIgnore($frequency + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Seed Sample Medications
        $medications = [
            [
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'brand_name' => 'Panadol',
                'strength' => '500mg',
                'formulation' => 'Tablet',
                'description' => 'Pain reliever and fever reducer',
                'unit_price' => 0.50,
                'stock_quantity' => 1000,
                'minimum_stock_level' => 100,
            ],
            [
                'name' => 'Amoxicillin 250mg',
                'generic_name' => 'Amoxicillin',
                'brand_name' => 'Amoxil',
                'strength' => '250mg',
                'formulation' => 'Capsule',
                'description' => 'Antibiotic for bacterial infections',
                'unit_price' => 1.20,
                'stock_quantity' => 500,
                'minimum_stock_level' => 50,
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Brufen',
                'strength' => '400mg',
                'formulation' => 'Tablet',
                'description' => 'Anti-inflammatory and pain reliever',
                'unit_price' => 0.80,
                'stock_quantity' => 800,
                'minimum_stock_level' => 80,
            ],
            [
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole',
                'brand_name' => 'Losec',
                'strength' => '20mg',
                'formulation' => 'Capsule',
                'description' => 'Proton pump inhibitor for acid reflux',
                'unit_price' => 2.50,
                'stock_quantity' => 300,
                'minimum_stock_level' => 30,
            ],
            [
                'name' => 'Salbutamol Inhaler',
                'generic_name' => 'Salbutamol',
                'brand_name' => 'Ventolin',
                'strength' => '100mcg/dose',
                'formulation' => 'Inhaler',
                'description' => 'Bronchodilator for asthma',
                'unit_price' => 15.00,
                'stock_quantity' => 50,
                'minimum_stock_level' => 10,
            ],
        ];

        foreach ($medications as $medication) {
            DB::table('medications')->insertOrIgnore($medication + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Seed Sample Medical Services
        $labCategoryId = DB::table('service_categories')->where('name', 'Laboratory Tests')->value('id');
        $radiologyCategoryId = DB::table('service_categories')->where('name', 'Radiology')->value('id');
        $cardiologyCategoryId = DB::table('service_categories')->where('name', 'Cardiology')->value('id');

        $medicalServices = [
            [
                'name' => 'Full Blood Count',
                'code' => 'FBC001',
                'description' => 'Complete blood count with differential',
                'service_category_id' => $labCategoryId,
                'price' => 25.00,
                'requires_sample' => true,
                'sample_type' => 'Blood',
                'turnaround_time_hours' => 4,
            ],
            [
                'name' => 'Blood Sugar (Random)',
                'code' => 'BS001',
                'description' => 'Random blood glucose test',
                'service_category_id' => $labCategoryId,
                'price' => 15.00,
                'requires_sample' => true,
                'sample_type' => 'Blood',
                'turnaround_time_hours' => 2,
            ],
            [
                'name' => 'Urine Analysis',
                'code' => 'UA001',
                'description' => 'Complete urine analysis',
                'service_category_id' => $labCategoryId,
                'price' => 20.00,
                'requires_sample' => true,
                'sample_type' => 'Urine',
                'turnaround_time_hours' => 2,
            ],
            [
                'name' => 'Chest X-Ray',
                'code' => 'CXR001',
                'description' => 'Chest X-ray PA view',
                'service_category_id' => $radiologyCategoryId,
                'price' => 40.00,
                'requires_sample' => false,
                'turnaround_time_hours' => 1,
            ],
            [
                'name' => 'ECG',
                'code' => 'ECG001',
                'description' => '12-lead electrocardiogram',
                'service_category_id' => $cardiologyCategoryId,
                'price' => 30.00,
                'requires_sample' => false,
                'turnaround_time_hours' => 1,
            ],
            [
                'name' => 'Lipid Profile',
                'code' => 'LP001',
                'description' => 'Complete lipid panel',
                'service_category_id' => $labCategoryId,
                'price' => 35.00,
                'requires_sample' => true,
                'sample_type' => 'Blood',
                'turnaround_time_hours' => 6,
                'preparation_instructions' => 'Fasting for 12 hours required',
            ],
            [
                'name' => 'Liver Function Tests',
                'code' => 'LFT001',
                'description' => 'Liver enzyme panel',
                'service_category_id' => $labCategoryId,
                'price' => 45.00,
                'requires_sample' => true,
                'sample_type' => 'Blood',
                'turnaround_time_hours' => 4,
            ],
        ];

        foreach ($medicalServices as $service) {
            DB::table('medical_services')->insertOrIgnore($service + ['created_at' => now(), 'updated_at' => now()]);
        }

        $this->command->info('Improved clinical data seeded successfully!');
    }
}
