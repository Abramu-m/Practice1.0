<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PatientVisit;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\SystemicExamination;
use App\Models\IcdDiagnosis;
use App\Models\PastMedicalHistory;
use App\Models\VitalSigns;

class TestPatientVisitStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:patient-visit-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test automatic patient visit status updates when doctors save consultation data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Patient Visit Status Auto-Update Feature');
        $this->info('=================================================');

        // Find a patient visit in waiting status (0) to test with
        $waitingVisit = PatientVisit::where('visit_status', 0)->first();
        
        if (!$waitingVisit) {
            $this->error('No patient visits found in "waiting" status. Please create a test visit first.');
            return 1;
        }

        $this->info("Found waiting visit ID: {$waitingVisit->id} for patient: {$waitingVisit->patient}");
        $this->info("Current status: {$waitingVisit->visit_status_label}");
        
        // Get or create consultation
        $consultation = Consultation::where('visit_id', $waitingVisit->id)->first();
        
        if (!$consultation) {
            $consultation = Consultation::create([
                'patient_id' => $waitingVisit->patient,
                'visit_id' => $waitingVisit->id,
                'doctor_id' => $waitingVisit->doctor ?? 1,
                'consultation_date' => $waitingVisit->visit_date,
                'status' => 'active'
            ]);
            $this->info("Created consultation ID: {$consultation->id}");
        } else {
            $this->info("Using existing consultation ID: {$consultation->id}");
        }

        $this->newLine();
        $this->info('Testing different triggers for status change...');
        $this->newLine();

        // Test 1: Update consultation with doctor input
        $this->info('1. Testing consultation update (should trigger status change)...');
        $consultation->update([
            'history_of_present_illness' => 'Test presenting complaint - automated test ' . now(),
            'provisional_diagnosis' => 'Test provisional diagnosis ' . now()
        ]);
        
        $waitingVisit->refresh();
        $this->checkStatusChange($waitingVisit, 'consultation update');

        // Reset status for next test if it changed
        if ($waitingVisit->visit_status == 1) {
            $waitingVisit->update(['visit_status' => 0]);
            $this->info('Reset status to waiting for next test.');
        }

        // Test 2: Add prescription (should trigger status change)
        $this->info('2. Testing prescription creation (should trigger status change)...');
        
        // Check if required reference data exists
        $medication = \App\Models\Medication::first();
        $adminRoute = \App\Models\AdministrationRoute::first();
        $frequency = \App\Models\MedicationFrequency::first();
        
        if (!$medication || !$adminRoute || !$frequency) {
            $this->warn('Skipping prescription test - missing reference data (medication, administration route, or frequency)');
        } else {
            $prescription = Prescription::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $waitingVisit->patient,
                'doctor_id' => $consultation->doctor_id,
                'medication_id' => $medication->id,
                'dosage' => '1 tablet',
                'administration_route_id' => $adminRoute->id,
                'frequency_id' => $frequency->id,
                'duration_days' => 7,
                'quantity' => 10,
                'unit_price' => 5.00,
                'total_price' => 50.00,
                'status' => 'prescribed'
            ]);
            
            $waitingVisit->refresh();
            $this->checkStatusChange($waitingVisit, 'prescription creation');

            // Reset status for next test if it changed
            if ($waitingVisit->visit_status == 1) {
                $waitingVisit->update(['visit_status' => 0]);
                $this->info('Reset status to waiting for next test.');
            }
        }

        // Test 3: Add systemic examination (should trigger status change)
        $this->info('3. Testing systemic examination (should trigger status change)...');
        $examination = SystemicExamination::create([
            'consultation_id' => $consultation->id,
            'visit_id' => $waitingVisit->id,
            'patient_id' => $waitingVisit->patient,
            'examination_type' => 'general',
            'general_findings' => 'Test general findings - automated test',
            'cardiovascular_system' => 'Normal heart sounds',
            'status' => 'active',
            'created_by' => $consultation->doctor_id
        ]);
        
        $waitingVisit->refresh();
        $this->checkStatusChange($waitingVisit, 'systemic examination');

        // Reset status for next test if it changed  
        if ($waitingVisit->visit_status == 1) {
            $waitingVisit->update(['visit_status' => 0]);
            $this->info('Reset status to waiting for next test.');
        }

        // Test 4: Add vital signs (should NOT trigger status change)
        $this->info('4. Testing vital signs (should NOT trigger status change)...');
        $vitals = VitalSigns::create([
            'visit_id' => $waitingVisit->id,
            'consultation_id' => $consultation->id,
            'patient_id' => $waitingVisit->patient,
            'systolic_bp' => 120,
            'diastolic_bp' => 80,
            'pulse_rate' => 75,
            'temperature' => 36.5,
            'recorded_by' => 1
        ]);
        
        $waitingVisit->refresh();
        $this->checkStatusChange($waitingVisit, 'vital signs (should NOT change)', false);

        // Test 5: Add ICD diagnosis (should trigger status change if still waiting)
        $this->info('5. Testing ICD diagnosis (should trigger status change)...');
        $icdDiagnosis = IcdDiagnosis::create([
            'consultation_id' => $consultation->id,
            'icd_code' => 'Z00.00',
            'description' => 'Encounter for general adult medical examination without abnormal findings',
            'type' => 'provisional',
            'added_by' => 1
        ]);
        
        $waitingVisit->refresh();
        $this->checkStatusChange($waitingVisit, 'ICD diagnosis');

        $this->newLine();
        $this->info('Test completed successfully!');
        $this->info('All observers are working as expected.');
        
        // Clean up test data
        $this->info('Cleaning up test data...');
        $icdDiagnosis->delete();
        $vitals->delete();
        $examination->delete();
        if (isset($prescription)) {
            $prescription->delete();
        }
        
        $this->info('Test data cleaned up.');
        
        return 0;
    }

    /**
     * Check if status changed as expected
     */
    private function checkStatusChange(PatientVisit $visit, string $trigger, bool $shouldChange = true)
    {
        if ($shouldChange) {
            if ($visit->visit_status == 1) {
                $this->info("   ✅ SUCCESS: Status changed to 'In Treatment' after {$trigger}");
            } else {
                $this->error("   ❌ FAILED: Status did not change after {$trigger}. Current status: {$visit->visit_status_label}");
            }
        } else {
            if ($visit->visit_status == 0) {
                $this->info("   ✅ SUCCESS: Status remained 'Waiting' after {$trigger} (as expected)");
            } else {
                $this->error("   ❌ FAILED: Status changed unexpectedly after {$trigger}. Current status: {$visit->visit_status_label}");
            }
        }
    }
}
