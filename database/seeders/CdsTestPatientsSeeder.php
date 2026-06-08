<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Allergy;
use App\Models\PastMedicalHistory;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\VitalSigns;
use App\Models\PatientVisit;
use App\Models\Medication;
use App\Models\MedicalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CdsTestPatientsSeeder extends Seeder
{
    /**
     * Seed three CDS test patients:
     *  1. Child  – 10 yrs, penicillin allergy
     *  2. Adult  – 30 yrs, sulfa allergy, hypertension
     *  3. Elderly – 70 yrs, renal failure, multiple co-morbidities
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            $this->doSeed();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function doSeed(): void
    {
        // Pre-load medication IDs keyed by generic_name for quick lookup.
        // Falls back to null (substance_name still stored as text) if not found.
        $medMap = Medication::pluck('id', 'generic_name');

        /** Helper: look up medication_id by generic_name */
        $medId = fn(string $name): ?int => $medMap->get($name) ? (int) $medMap->get($name) : null;

        // ------------------------------------------------------------------ //
        // Patient 1 – Child (10 years old)
        // ------------------------------------------------------------------ //
        $child = Patient::updateOrCreate(
            ['card_number' => 'CDS-TEST-001'],
            [
                'first_name'    => 'Amina',
                'middle_name'   => 'Test',
                'last_name'     => 'Child',
                'date_of_birth' => Carbon::now()->subYears(10)->format('Y-m-d'),
                'gender'        => 'Female',
                'contact'       => '0700000001',
                'residence'     => 'Test Town',
                'occupation'    => 'Student',
                'patient_category' => 1,
                'created_by'    => 1,
                'status'        => 'active',
            ]
        );

        // Remove any old generic-name entries before seeding real drug names
        Allergy::where('patient_id', $child->id)->where('substance_name', 'Penicillin')->delete();

        Allergy::updateOrCreate(
            ['patient_id' => $child->id, 'substance_name' => 'Benzyl Penicillin'],
            [
                'medication_id' => $medId('Benzyl Penicillin'),
                'reaction'    => 'Anaphylaxis – severe breathing difficulty and urticaria',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(6),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $child->id, 'substance_name' => 'Amoxicillin'],
            [
                'medication_id' => $medId('Amoxicillin'),
                'reaction'    => 'Rash and facial swelling (cross-reactive with penicillin)',
                'severity'    => 'moderate',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(4),
            ]
        );

        PastMedicalHistory::updateOrCreate(
            ['patient_id' => $child->id],
            [
                'allergies'           => 'Benzyl Penicillin (anaphylaxis), Amoxicillin (rash)',  
                'chronic_conditions'  => 'Asthma (mild intermittent)',
                'previous_surgeries'  => 'None',
                'family_history'      => 'Father: asthma. Mother: eczema.',
                'social_history'      => 'Primary school pupil, lives with both parents',
                'smoking_status'      => 'non_smoker',
                'alcohol_use'         => 'none',
                'current_medications' => 'Salbutamol inhaler 100 mcg PRN',
                'immunization_history'=> 'Up to date (BCG, DPT, MMR, OPV)',
            ]
        );

        $this->seedVitals($child->id, [
            'weight' => 32, 'height' => 138, 'bmi' => 16.8,
            'systolic_bp' => 100, 'diastolic_bp' => 65,
            'pulse_rate' => 90, 'temperature' => 36.8,
            'respiratory_rate' => 22, 'oxygen_saturation' => 99,
        ]);

        $this->seedInvestigations($child->id, [
            ['name' => 'Full Blood Count',      'result' => 'WBC 9.2, Hb 11.4, Plt 310'],
            ['name' => 'Malaria RDT',            'result' => 'Positive (P. falciparum)'],
            ['name' => 'Blood Group & Crossmatch','result' => 'O Positive'],
        ]);

        // Active prescriptions – used to trigger duplicate-therapy CDS alerts
        $this->seedPrescriptions($child->id, $medId, [
            // Salbutamol inhaler (ongoing asthma relief)
            ['name' => 'Salbutamol',                        'dosage' => '100 mcg',  'instructions' => '2 puffs PRN, max 4×/day'],
            // Artemether/Lumefantrine for current malaria (18-tab pack for a 10-yr-old)
            ['name' => 'Artemether / Lumefantrine 18 tabs', 'dosage' => '20/120 mg', 'instructions' => 'BD × 3 days'],
        ]);

        // ------------------------------------------------------------------ //
        // Patient 2 – Adult (30 years old)
        // ------------------------------------------------------------------ //
        $adult = Patient::updateOrCreate(
            ['card_number' => 'CDS-TEST-002'],
            [
                'first_name'    => 'John',
                'middle_name'   => 'Test',
                'last_name'     => 'Adult',
                'date_of_birth' => Carbon::now()->subYears(30)->format('Y-m-d'),
                'gender'        => 'Male',
                'contact'       => '0700000002',
                'residence'     => 'Test Town',
                'occupation'    => 'Teacher',
                'patient_category' => 1,
                'created_by'    => 1,
                'status'        => 'active',
            ]
        );

        // Remove old class-name entry; replace with real DB drug name
        Allergy::where('patient_id', $adult->id)->where('substance_name', 'Sulfonamides')->delete();

        Allergy::updateOrCreate(
            ['patient_id' => $adult->id, 'substance_name' => 'Cotrimoxazole'],
            [
                'medication_id' => $medId('Cotrimoxazole'),
                'reaction'    => 'Stevens-Johnson Syndrome',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $adult->id, 'substance_name' => 'Aspirin'],
            [
                'medication_id' => $medId('Aspirin'),
                'reaction'    => 'Bronchospasm and nasal polyps',
                'severity'    => 'moderate',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(8),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $adult->id, 'substance_name' => 'Ibuprofen'],
            [
                'medication_id' => $medId('Ibuprofen'),
                'reaction'    => 'Urticaria and angioedema',
                'severity'    => 'moderate',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(3),
            ]
        );

        PastMedicalHistory::updateOrCreate(
            ['patient_id' => $adult->id],
            [
                'allergies'           => 'Cotrimoxazole (SJS), Aspirin (bronchospasm), Ibuprofen (urticaria)',
                'chronic_conditions'  => 'Hypertension (Stage 1), Type 2 Diabetes Mellitus',
                'previous_surgeries'  => 'Appendectomy (2018)',
                'family_history'      => 'Father: hypertension and stroke. Mother: diabetes.',
                'social_history'      => 'Married, non-smoker, occasional alcohol',
                'smoking_status'      => 'non_smoker',
                'alcohol_use'         => 'occasional',
                'current_medications' => 'Amlodipine 5 mg OD, Metformin 500 mg BD',
                'immunization_history'=> 'Hepatitis B (complete), Tetanus (2020)',
            ]
        );

        $this->seedVitals($adult->id, [
            'weight' => 78, 'height' => 175, 'bmi' => 25.5,
            'systolic_bp' => 148, 'diastolic_bp' => 94,
            'pulse_rate' => 82, 'temperature' => 37.0,
            'respiratory_rate' => 16, 'oxygen_saturation' => 98,
        ]);

        $this->seedInvestigations($adult->id, [
            ['name' => 'Fasting Blood Sugar',       'result' => '9.4 mmol/L (elevated)'],
            ['name' => 'HbA1c',                      'result' => '8.2% (poor control)'],
            ['name' => 'Lipid Profile',              'result' => 'Total Chol 6.1, LDL 4.0, HDL 0.9, TG 2.8'],
            ['name' => 'Urea & Electrolytes',        'result' => 'Na 138, K 4.1, Urea 6.0, Creatinine 95 μmol/L – eGFR 82'],
            ['name' => 'Urine Microalbumin',         'result' => '42 mg/g Cr (microalbuminuria)'],
        ]);

        // Active prescriptions – used to trigger duplicate-therapy CDS alerts
        $this->seedPrescriptions($adult->id, $medId, [
            // Hypertension management
            ['name' => 'Amlodipine',   'dosage' => '5 mg',   'instructions' => 'OD'],
            // Diabetes management
            ['name' => 'Metformin',    'dosage' => '500 mg', 'instructions' => 'BD with food'],
        ]);

        // ------------------------------------------------------------------ //
        // Patient 3 – Elderly (70 years old, chronic renal failure)
        // ------------------------------------------------------------------ //
        $elderly = Patient::updateOrCreate(
            ['card_number' => 'CDS-TEST-003'],
            [
                'first_name'    => 'Margaret',
                'middle_name'   => 'Test',
                'last_name'     => 'Elderly',
                'date_of_birth' => Carbon::now()->subYears(70)->format('Y-m-d'),
                'gender'        => 'Female',
                'contact'       => '0700000003',
                'residence'     => 'Test Town',
                'occupation'    => 'Retired',
                'patient_category' => 1,
                'created_by'    => 1,
                'status'        => 'active',
            ]
        );

        // Remove old class/generic entries; replace with real DB drug names
        Allergy::where('patient_id', $elderly->id)
            ->whereIn('substance_name', ['NSAIDs', 'Codeine'])
            ->delete();

        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Metformin'],
            [
                'medication_id' => $medId('Metformin'),
                'reaction'    => 'Lactic acidosis risk – contraindicated in CKD',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYears(2),
            ]
        );
        // NSAIDs replaced with specific real drugs from the formulary
        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Diclofenac'],
            [
                'medication_id' => $medId('Diclofenac'),
                'reaction'    => 'Acute kidney injury on chronic kidney disease',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Naproxen'],
            [
                'medication_id' => $medId('Naproxen'),
                'reaction'    => 'Acute kidney injury on chronic kidney disease',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Ibuprofen'],
            [
                'medication_id' => $medId('Ibuprofen'),
                'reaction'    => 'Acute kidney injury on chronic kidney disease',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        // Codeine replaced with Tramadol (opioid present in formulary)
        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Tramadol'],
            [
                'medication_id' => $medId('Tramadol'),
                'reaction'    => 'Respiratory depression – opioid accumulation in renal failure',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(10),
            ]
        );

        PastMedicalHistory::updateOrCreate(
            ['patient_id' => $elderly->id],
            [
                'allergies'           => 'Metformin (lactic acidosis), Diclofenac/Naproxen/Ibuprofen (AKI), Tramadol (resp. depression)',
                'chronic_conditions'  => 'Chronic Kidney Disease Stage 4 (eGFR 22), Hypertension, Congestive Heart Failure (EF 35%), Type 2 Diabetes (insulin-dependent), Osteoporosis, Anaemia of CKD',
                'previous_surgeries'  => 'Left hip replacement (2019), Cataract surgery bilateral (2021)',
                'family_history'      => 'Hypertension and cardiovascular disease (multiple family members)',
                'social_history'      => 'Widowed, lives alone, receives domiciliary care twice weekly',
                'smoking_status'      => 'former_smoker',
                'alcohol_use'         => 'none',
                'current_medications' => 'Amlodipine 5 mg OD, Furosemide 40 mg OD, Erythropoietin 4000 IU SC weekly, Calcium carbonate 500 mg TDS, Insulin glargine 20 units ON, Aspirin 75 mg OD, Bisoprolol 2.5 mg OD',
                'immunization_history'=> 'Influenza (annual), Pneumococcal (2022)',
                'reproductive_history'=> 'Post-menopausal (since age 52). G3P3.',
            ]
        );

        $this->seedVitals($elderly->id, [
            'weight' => 58, 'height' => 158, 'bmi' => 23.2,
            'systolic_bp' => 162, 'diastolic_bp' => 98,
            'pulse_rate' => 74, 'temperature' => 36.4,
            'respiratory_rate' => 18, 'oxygen_saturation' => 95,
        ]);

        $this->seedInvestigations($elderly->id, [
            ['name' => 'Urea & Electrolytes', 'result' => 'Na 135, K 5.6 (HIGH), Urea 28.4 (HIGH), Creatinine 412 μmol/L (HIGH) – eGFR 22 mL/min/1.73m²'],
            ['name' => 'Full Blood Count',    'result' => 'WBC 7.2, Hb 8.6 (LOW – anaemia of CKD), Plt 188'],
            ['name' => 'Fasting Blood Sugar', 'result' => '12.1 mmol/L (elevated)'],
            ['name' => 'HbA1c',              'result' => '9.1% (poor control)'],
            ['name' => 'BNP',                'result' => '1480 pg/mL (severely elevated – heart failure)'],
            ['name' => 'Chest X-Ray',        'result' => 'Cardiomegaly with bilateral pleural effusions'],
            ['name' => 'ECG',                'result' => 'Sinus rhythm, LVH pattern, peaked T waves (hyperkalaemia)'],
            ['name' => 'Urine Microalbumin', 'result' => '>300 mg/g Cr (macroalbuminuria – nephrotic range)'],
        ]);

        // Active prescriptions – used to trigger duplicate-therapy CDS alerts
        $this->seedPrescriptions($elderly->id, $medId, [
            // Heart failure + hypertension
            ['name' => 'Amlodipine',   'dosage' => '5 mg',    'instructions' => 'OD'],
            ['name' => 'Furosemide',   'dosage' => '40 mg',   'instructions' => 'OD morning'],
            ['name' => 'Bisoprolol',   'dosage' => '2.5 mg',  'instructions' => 'OD'],
            // Antiplatelet
            ['name' => 'Aspirin',      'dosage' => '75 mg',   'instructions' => 'OD with food'],
            // CKD / anaemia support
            ['name' => 'Calcium carbonate', 'dosage' => '500 mg', 'instructions' => 'TDS with meals'],
        ]);

        $this->command->info('CDS Test Patients seeded successfully.');
    } // end doSeed()

    // ------------------------------------------------------------------ //
    // Helpers
    // ------------------------------------------------------------------ //

    private function seedVitals(int $patientId, array $data): void
    {
        VitalSigns::updateOrCreate(
            ['patient_id' => $patientId, 'visit_id' => 0, 'consultation_id' => 0],
            array_merge($data, [
                'recorded_by' => 1,
                'recorded_at' => Carbon::now()->subDays(3),
                'status'      => 1,
            ])
        );
    }

    /**
     * Upsert active prescriptions for a test patient.
     * Uses DB::table to bypass financial NOT NULL columns irrelevant to CDS testing.
     */
    private function seedPrescriptions(int $patientId, callable $medId, array $drugs): void
    {
        foreach ($drugs as $drug) {
            $mid = $medId($drug['name']);

            // Skip if medication not found in this installation's formulary
            if ($mid === null) {
                $this->command->warn("  Skipping prescription: '{$drug['name']}' not found in medications table.");
                continue;
            }

            // Delete any previous test-seeded prescription for this patient+medication
            // (leave real ones untouched by scoping to doctor_id = 0)
            DB::table('prescriptions')
                ->where('patient_id', $patientId)
                ->where('medication_id', $mid)
                ->where('doctor_id', 0)
                ->delete();

            DB::table('prescriptions')->insert([
                'patient_id'            => $patientId,
                'consultation_id'       => 0,
                'doctor_id'             => 0,   // sentinel: seeded by CDS test seeder
                'medication_id'         => $mid,
                'dosage'                => $drug['dosage'],
                'administration_route_id' => 0, // sentinel (no FK enforced with FK_CHECKS=0)
                'frequency_id'          => 0,   // sentinel
                'duration_days'         => 30,
                'instructions'          => $drug['instructions'] ?? '',
                'status'                => 'prescribed',
                'unit_price'            => 0,
                'total_price'           => 0,
                'quantity'              => 1,
                'quantity_dispensed'    => 0,
                'is_paid'               => 0,
                'prescribed_at'         => Carbon::now()->subDays(7),
                'created_at'            => Carbon::now(),
                'updated_at'            => Carbon::now(),
            ]);
        }
    }

    private function seedInvestigations(int $patientId, array $tests): void
    {
        foreach ($tests as $test) {
            // Use DB::table to avoid model fillable / NOT NULL constraints on
            // financial columns (unit_price, etc.) that are irrelevant for test data.
            $exists = DB::table('investigations')
                ->where('patient_id', $patientId)
                ->where('notes', $test['name'])
                ->exists();

            if (!$exists) {
                DB::table('investigations')->insert([
                    'patient_id'         => $patientId,
                    'medical_service_id' => 0,
                    'notes'              => $test['name'],
                    'status'             => 'resulted',
                    'unit_price'         => 0,
                    'total_price'        => 0,
                    'quantity'           => 1,
                    'clinical_data' => json_encode([
                        'test_name' => $test['name'],
                        'result'    => $test['result'],
                    ]),
                    'ordered_at'  => Carbon::now()->subDays(5),
                    'resulted_at' => Carbon::now()->subDays(3),
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]);
            } else {
                DB::table('investigations')
                    ->where('patient_id', $patientId)
                    ->where('notes', $test['name'])
                    ->update([
                        'clinical_data' => json_encode([
                            'test_name' => $test['name'],
                            'result'    => $test['result'],
                        ]),
                        'updated_at' => Carbon::now(),
                    ]);
            }
        }
    }
}
