<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalService;
use App\Models\ServiceCategory;

class MedicalServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create service categories first
        $laboratory = ServiceCategory::firstOrCreate(
            ['name' => 'Laboratory'],
            [
                'description' => 'Laboratory investigations and tests',
                'is_active' => true
            ]
        );

        $radiology = ServiceCategory::firstOrCreate(
            ['name' => 'Radiology'],
            [
                'description' => 'Imaging and radiology services',
                'is_active' => true
            ]
        );

        $cardiology = ServiceCategory::firstOrCreate(
            ['name' => 'Cardiology'],
            [
                'description' => 'Cardiac investigations and monitoring',
                'is_active' => true
            ]
        );

        $gynecology = ServiceCategory::firstOrCreate(
            ['name' => 'Gynecology'],
            [
                'description' => 'Gynecological and fertility services',
                'is_active' => true
            ]
        );

        // Create medical services
        $services = [
            // Laboratory Services
            [
                'service_category_id' => $laboratory->id,
                'name' => 'Complete Blood Count (CBC)',
                'code' => 'LAB001',
                'description' => 'Full blood count including hemoglobin, white cells, platelets',
                'price' => 25000.00,
                'insurance_covered_amount' => 20000.00,
                'requires_sample' => true,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => true
            ],
            [
                'service_category_id' => $laboratory->id,
                'name' => 'Blood Glucose Random',
                'code' => 'LAB002',
                'description' => 'Random blood glucose measurement',
                'price' => 8000.00,
                'insurance_covered_amount' => 6000.00,
                'requires_sample' => true,
                'sample_type' => 'Blood (Fluoride)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No fasting required for random glucose',
                'is_active' => true
            ],
            [
                'service_category_id' => $laboratory->id,
                'name' => 'Urine Analysis',
                'code' => 'LAB003',
                'description' => 'Complete urine examination including microscopy',
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => true,
                'sample_type' => 'Urine (Mid-stream)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Collect mid-stream urine in sterile container',
                'is_active' => true
            ],
            [
                'service_category_id' => $laboratory->id,
                'name' => 'Malaria Test (RDT)',
                'code' => 'LAB004',
                'description' => 'Rapid diagnostic test for malaria parasites',
                'price' => 12000.00,
                'insurance_covered_amount' => 10000.00,
                'requires_sample' => true,
                'sample_type' => 'Blood (Capillary)',
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'No preparation required',
                'is_active' => true
            ],

            // Radiology Services
            [
                'service_category_id' => $radiology->id,
                'name' => 'Chest X-Ray PA',
                'code' => 'RAD001',
                'description' => 'Chest X-ray postero-anterior view',
                'price' => 35000.00,
                'insurance_covered_amount' => 30000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Remove all metal objects from chest area',
                'is_active' => true
            ],
            [
                'service_category_id' => $radiology->id,
                'name' => 'Pelvic Ultrasound',
                'code' => 'RAD002',
                'description' => 'Transabdominal pelvic ultrasound examination',
                'price' => 45000.00,
                'insurance_covered_amount' => 35000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Full bladder required - drink 1L water 1 hour before exam',
                'is_active' => true
            ],
            [
                'service_category_id' => $radiology->id,
                'name' => 'Abdominal Ultrasound',
                'code' => 'RAD003',
                'description' => 'Complete abdominal ultrasound examination',
                'price' => 50000.00,
                'insurance_covered_amount' => 40000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Fasting for 8 hours, full bladder',
                'is_active' => true
            ],

            // Cardiology Services
            [
                'service_category_id' => $cardiology->id,
                'name' => 'ECG (12-Lead)',
                'code' => 'CAR001',
                'description' => '12-lead electrocardiogram',
                'price' => 20000.00,
                'insurance_covered_amount' => 15000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Remove jewelry and metal objects from chest',
                'is_active' => true
            ],
            [
                'service_category_id' => $cardiology->id,
                'name' => 'Echocardiogram',
                'code' => 'CAR002',
                'description' => 'Cardiac ultrasound examination',
                'price' => 80000.00,
                'insurance_covered_amount' => 60000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => true
            ],

            // Specialized Services
            [
                'service_category_id' => $gynecology->id,
                'name' => 'Fertility Assessment',
                'code' => 'GYN001',
                'description' => 'Comprehensive fertility evaluation questionnaire and examination',
                'price' => 75000.00,
                'insurance_covered_amount' => 50000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 24,
                'preparation_instructions' => 'Bring previous test results and menstrual calendar',
                'is_active' => true
            ],
            [
                'service_category_id' => $cardiology->id,
                'name' => 'Bedrest Observation',
                'code' => 'CAR003',
                'description' => 'Continuous vital signs monitoring and observation',
                'price' => 30000.00,
                'insurance_covered_amount' => 25000.00,
                'requires_sample' => false,
                'sample_type' => null,
                'turnaround_time_hours' => 8,
                'preparation_instructions' => 'Patient should be comfortable for extended monitoring',
                'is_active' => true
            ]
        ];

        foreach ($services as $serviceData) {
            MedicalService::firstOrCreate(
                ['code' => $serviceData['code']],
                $serviceData
            );
        }

        $this->command->info('Medical services seeded successfully!');
    }
}
