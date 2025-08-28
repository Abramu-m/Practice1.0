<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsultationFee;
use App\Models\Doctor;
use App\Models\PatientCategory;
use App\Models\VisitType;

class ConsultationFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $doctors = Doctor::all();
        $patientCategories = PatientCategory::all();
        $visitTypes = VisitType::all();

        if ($doctors->isEmpty() || $patientCategories->isEmpty() || $visitTypes->isEmpty()) {
            $this->command->warn('Please ensure you have doctors, patient categories, and visit types before running this seeder.');
            return;
        }

        // Sample consultation fees
        $consultationFees = [
            // General Practitioner fees
            [
                'doctor_specialization' => 'General Practice',
                'patient_category' => 'General',
                'visit_type' => 'Consultation',
                'fee_amount' => 50.00,
                'description' => 'Standard general consultation fee'
            ],
            [
                'doctor_specialization' => 'General Practice', 
                'patient_category' => 'NHIF',
                'visit_type' => 'Consultation',
                'fee_amount' => 30.00,
                'description' => 'NHIF subsidized consultation fee'
            ],
            [
                'doctor_specialization' => 'General Practice',
                'patient_category' => 'Private',
                'visit_type' => 'Consultation',
                'fee_amount' => 75.00,
                'description' => 'Private consultation fee'
            ],
            // Specialist fees
            [
                'doctor_specialization' => 'Specialist',
                'patient_category' => 'General',
                'visit_type' => 'Consultation',
                'fee_amount' => 100.00,
                'description' => 'Specialist consultation fee'
            ],
            [
                'doctor_specialization' => 'Specialist',
                'patient_category' => 'NHIF',
                'visit_type' => 'Consultation',
                'fee_amount' => 60.00,
                'description' => 'NHIF specialist consultation fee'
            ],
            // Follow-up fees
            [
                'doctor_specialization' => 'General Practice',
                'patient_category' => 'General',
                'visit_type' => 'Follow-up',
                'fee_amount' => 35.00,
                'description' => 'Follow-up consultation fee'
            ],
        ];

        foreach ($consultationFees as $feeData) {
            // Find doctor with matching specialization (approximate match)
            $doctor = $doctors->filter(function ($doc) use ($feeData) {
                return stripos($doc->specialization, $feeData['doctor_specialization']) !== false ||
                       stripos($doc->designation->description ?? '', $feeData['doctor_specialization']) !== false;
            })->first();

            // If no matching doctor found, use the first available doctor
            if (!$doctor) {
                $doctor = $doctors->first();
            }

            // Find patient category with matching description
            $category = $patientCategories->filter(function ($cat) use ($feeData) {
                return stripos($cat->description, $feeData['patient_category']) !== false;
            })->first();

            // If no matching category found, use the first available category
            if (!$category) {
                $category = $patientCategories->first();
            }

            // Find visit type with matching description
            $visitType = $visitTypes->filter(function ($vt) use ($feeData) {
                return stripos($vt->description, $feeData['visit_type']) !== false;
            })->first();

            // If no matching visit type found, use the first available visit type
            if (!$visitType) {
                $visitType = $visitTypes->first();
            }

            // Check if this combination already exists
            $existing = ConsultationFee::where('doctor_id', $doctor->doctor_id)
                                     ->where('patient_category_id', $category->id)
                                     ->where('visit_type_id', $visitType->id)
                                     ->first();

            if (!$existing) {
                ConsultationFee::create([
                    'doctor_id' => $doctor->doctor_id,
                    'patient_category_id' => $category->id,
                    'visit_type_id' => $visitType->id,
                    'fee_amount' => $feeData['fee_amount'],
                    'description' => $feeData['description'],
                    'status' => 1,
                    'created_by' => 1 // Assuming admin user ID is 1
                ]);

                $this->command->info("Created consultation fee: {$doctor->user->name} - {$category->description} - {$visitType->description} - \${$feeData['fee_amount']}");
            } else {
                $this->command->warn("Consultation fee already exists: {$doctor->user->name} - {$category->description} - {$visitType->description}");
            }
        }

        $this->command->info('Consultation fee seeding completed!');
    }
}
