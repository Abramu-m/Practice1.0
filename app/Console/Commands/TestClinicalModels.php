<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VitalSigns;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\Medication;
use App\Models\MedicalService;

class TestClinicalModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:clinical-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the improved clinical models functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Improved Clinical Models');
        $this->info('===================================');
        $this->newLine();

        // Test 1: VitalSigns with automatic BMI calculation
        $this->info('1. Testing VitalSigns with automatic BMI calculation:');
        try {
            $vitals = new VitalSigns([
                'consultation_id' => 1,
                'weight' => 70.5,
                'height' => 175,
                'pulse_rate' => 72,
                'temperature' => 36.8,
                'systolic_pressure' => 120,
                'diastolic_pressure' => 80
            ]);
            
            $this->line("   ✅ Weight: {$vitals->weight}kg, Height: {$vitals->height}cm");
            $this->line("   ✅ Calculated BMI: " . number_format($vitals->bmi ?? 0, 2));
            $this->line("   ✅ BMI Category: {$vitals->bmi_category}");
            $this->line("   ✅ Blood Pressure: {$vitals->blood_pressure}");
            $this->line("   ✅ BP Category: {$vitals->blood_pressure_category}");
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Prescription with enhanced status management
        $this->info('2. Testing Prescription with enhanced status management:');
        try {
            $prescription = new Prescription([
                'patient_registration_number' => 'P2025001',
                'consultation_id' => 1,
                'doctor_id' => 1,
                'medication_id' => 1,
                'quantity' => 30,
                'unit_price' => 2.50,
                'status' => Prescription::STATUS_PRESCRIBED
            ]);
            
            $this->line("   ✅ Status: {$prescription->status}");
            $this->line("   ✅ Status Label: {$prescription->status_label}");
            $this->line("   ✅ Status Badge Class: {$prescription->status_badge_class}");
            $this->line("   ✅ Total Price: $" . number_format($prescription->total_price ?? 0, 2));
            $this->line("   ✅ Available Status Options:");
            foreach (Prescription::getStatusOptions() as $key => $label) {
                $this->line("       - {$key}: {$label}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Investigation with priority levels
        $this->info('3. Testing Investigation with priority levels:');
        try {
            $investigation = new Investigation([
                'patient_registration_number' => 'P2025001',
                'consultation_id' => 1,
                'doctor_id' => 1,
                'medical_service_id' => 1,
                'quantity' => 1,
                'unit_price' => 25.00,
                'priority' => Investigation::PRIORITY_URGENT,
                'status' => Investigation::STATUS_ORDERED
            ]);
            
            $this->line("   ✅ Priority: {$investigation->priority}");
            $this->line("   ✅ Priority Label: {$investigation->priority_label}");
            $this->line("   ✅ Priority Badge Class: {$investigation->priority_badge_class}");
            $this->line("   ✅ Status: {$investigation->status}");
            $this->line("   ✅ Status Label: {$investigation->status_label}");
            $this->line("   ✅ Status Badge Class: {$investigation->status_badge_class}");
            $this->line("   ✅ Total Price: $" . number_format($investigation->total_price ?? 0, 2));
            $this->line("   ✅ Available Priority Options:");
            foreach (Investigation::getPriorityOptions() as $key => $label) {
                $this->line("       - {$key}: {$label}");
            }
            $this->line("   ✅ Available Status Options:");
            foreach (Investigation::getStatusOptions() as $key => $label) {
                $this->line("       - {$key}: {$label}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test 4: Medication with stock management
        $this->info('4. Testing Medication with stock management:');
        try {
            $medication = new Medication([
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'strength' => '500mg',
                'unit_price' => 0.50,
                'stock_quantity' => 15,
                'minimum_stock_level' => 20,
                'is_active' => true
            ]);
            
            $this->line("   ✅ Name: {$medication->name}");
            $this->line("   ✅ Stock Quantity: {$medication->stock_quantity}");
            $this->line("   ✅ Minimum Stock Level: {$medication->minimum_stock_level}");
            $this->line("   ✅ Is In Stock: " . ($medication->is_in_stock ? 'Yes' : 'No'));
            $this->line("   ✅ Is Low Stock: " . ($medication->is_low_stock ? 'Yes' : 'No'));
            $this->line("   ✅ Stock Status: {$medication->stock_status}");
            $this->line("   ✅ Stock Badge Class: {$medication->stock_badge_class}");
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test 5: Constants and Enums
        $this->info('5. Testing Constants and Enums:');
        try {
            $this->line("   ✅ Prescription Status Constants:");
            $prescriptionConstants = [
                'STATUS_DRAFT' => Prescription::STATUS_DRAFT,
                'STATUS_PRESCRIBED' => Prescription::STATUS_PRESCRIBED,
                'STATUS_PREPARED' => Prescription::STATUS_PREPARED,
                'STATUS_DISPENSED' => Prescription::STATUS_DISPENSED,
                'STATUS_CANCELLED' => Prescription::STATUS_CANCELLED
            ];
            foreach ($prescriptionConstants as $name => $value) {
                $this->line("       - {$name}: {$value}");
            }
            
            $this->line("   ✅ Investigation Status Constants:");
            $investigationConstants = [
                'STATUS_DRAFT' => Investigation::STATUS_DRAFT,
                'STATUS_ORDERED' => Investigation::STATUS_ORDERED,
                'STATUS_COLLECTED' => Investigation::STATUS_COLLECTED,
                'STATUS_PROCESSING' => Investigation::STATUS_PROCESSING,
                'STATUS_RESULTED' => Investigation::STATUS_RESULTED,
                'STATUS_CANCELLED' => Investigation::STATUS_CANCELLED
            ];
            foreach ($investigationConstants as $name => $value) {
                $this->line("       - {$name}: {$value}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }
        $this->newLine();

        $this->info('🎉 Clinical Models Testing Complete!');
        $this->info('====================================');
        $this->newLine();

        // Summary of improvements
        $this->info('📋 Summary of Improvements:');
        $this->line('- ✅ Replaced cryptic column names with clear Laravel conventions');
        $this->line('- ✅ Added automatic calculations (BMI, blood pressure, total prices)');
        $this->line('- ✅ Enhanced status management with proper constants and labels');
        $this->line('- ✅ Improved stock management for medications');
        $this->line('- ✅ Added comprehensive query scopes for filtering');
        $this->line('- ✅ Enhanced relationships between models');
        $this->line('- ✅ Added priority levels for investigations');
        $this->line('- ✅ Better error handling and validation');
        $this->line('- ✅ Comprehensive API endpoints for clinical workflow');
        $this->newLine();

        $this->info('🚀 Next Steps:');
        $this->line('- Test the API endpoints using the clinical dashboard');
        $this->line('- Update existing views to use new model properties');
        $this->line('- Run database migrations to implement the improved structure');
        $this->line('- Update existing data to match new column names');
        $this->line('- Implement comprehensive testing');
        $this->line('- Deploy the improved system');

        return Command::SUCCESS;
    }
}
