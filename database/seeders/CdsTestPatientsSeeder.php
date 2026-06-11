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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CdsTestPatientsSeeder extends Seeder
{
    /**
     * Medication IDs in this installation's formulary, keyed by a descriptive
     * slug. Looked up by exact generic_name match against medications table
     * (generic_name includes the dosage, e.g. "Amlodipine 5 mg") so the bare
     * class names used previously (e.g. "Amlodipine") never matched.
     *
     *   id=11  Amlodipine 5 mg
     *   id=20  Acetylsalicylic acid 75 mg (ASA 75/Aspirin junior)
     *   id=22  Atenolol 25 mg            (substitute: Bisoprolol not in formulary)
     *   id=31  Calcium Carbonate 420 mg
     *   id=45  Cotrimoxazole 480 mg (Trimethoprim 80 mg, Sulphamethoxazole 400 mg)
     *   id=48  Diclofenac 50 mg
     *   id=66  Furosemide (Frusemide) 40 mg
     *   id=78  Ibuprofen 200 mg
     *   id=105 Metformin 500 mg
     *   id=112 Naproxen 500 mg
     *   id=125 Penicillin 250 mg (Pen V) (substitute: Benzyl Penicillin not in formulary)
     *   id=151 Tramadol 50 mg
     *   id=256 Salbutamol inhaler
     *   id=534 Artemether + Lumefantrine (ALU) 18 tabs
     *   id=757 Amoxicillin Dispersible Tablets 250 mg
     */
    private const MED = [
        'amlodipine'   => 11,
        'aspirin'      => 20,
        'atenolol'     => 22,
        'calcium_carb' => 31,
        'cotrimoxazole'=> 45,
        'diclofenac'   => 48,
        'furosemide'   => 66,
        'ibuprofen'    => 78,
        'metformin'    => 105,
        'naproxen'     => 112,
        'penicillin'   => 125,
        'tramadol'     => 151,
        'salbutamol'   => 256,
        'artemether_lumefantrine' => 534,
        'amoxicillin'  => 757,
    ];

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
                'medication_id' => self::MED['penicillin'],
                'reaction'    => 'Anaphylaxis – severe breathing difficulty and urticaria',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(6),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $child->id, 'substance_name' => 'Amoxicillin'],
            [
                'medication_id' => self::MED['amoxicillin'],
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
        $this->seedPrescriptions($child->id, [
            // Salbutamol inhaler (ongoing asthma relief)
            ['med' => self::MED['salbutamol'], 'dosage' => '100 mcg', 'instructions' => '2 puffs PRN, max 4×/day'],
            // Artemether/Lumefantrine for current malaria (18-tab pack for a 10-yr-old)
            ['med' => self::MED['artemether_lumefantrine'], 'dosage' => '20/120 mg', 'instructions' => 'BD × 3 days'],
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
                'medication_id' => self::MED['cotrimoxazole'],
                'reaction'    => 'Stevens-Johnson Syndrome',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $adult->id, 'substance_name' => 'Aspirin'],
            [
                'medication_id' => self::MED['aspirin'],
                'reaction'    => 'Bronchospasm and nasal polyps',
                'severity'    => 'moderate',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subMonths(8),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $adult->id, 'substance_name' => 'Ibuprofen'],
            [
                'medication_id' => self::MED['ibuprofen'],
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
        $this->seedPrescriptions($adult->id, [
            // Hypertension management
            ['med' => self::MED['amlodipine'], 'dosage' => '5 mg', 'instructions' => 'OD'],
            // Diabetes management
            ['med' => self::MED['metformin'], 'dosage' => '500 mg', 'instructions' => 'BD with food'],
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
                'medication_id' => self::MED['metformin'],
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
                'medication_id' => self::MED['diclofenac'],
                'reaction'    => 'Acute kidney injury on chronic kidney disease',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Naproxen'],
            [
                'medication_id' => self::MED['naproxen'],
                'reaction'    => 'Acute kidney injury on chronic kidney disease',
                'severity'    => 'severe',
                'is_active'   => true,
                'recorded_at' => Carbon::now()->subYear(),
            ]
        );
        Allergy::updateOrCreate(
            ['patient_id' => $elderly->id, 'substance_name' => 'Ibuprofen'],
            [
                'medication_id' => self::MED['ibuprofen'],
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
                'medication_id' => self::MED['tramadol'],
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
        $this->seedPrescriptions($elderly->id, [
            // Heart failure + hypertension
            ['med' => self::MED['amlodipine'], 'dosage' => '5 mg', 'instructions' => 'OD'],
            ['med' => self::MED['furosemide'], 'dosage' => '40 mg', 'instructions' => 'OD morning'],
            // Bisoprolol not in formulary; Atenolol 25 mg used as the beta-blocker substitute
            ['med' => self::MED['atenolol'], 'dosage' => '25 mg', 'instructions' => 'OD'],
            // Antiplatelet
            ['med' => self::MED['aspirin'], 'dosage' => '75 mg', 'instructions' => 'OD with food'],
            // CKD / anaemia support
            ['med' => self::MED['calcium_carb'], 'dosage' => '420 mg', 'instructions' => 'TDS with meals'],
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
    private function seedPrescriptions(int $patientId, array $drugs): void
    {
        foreach ($drugs as $drug) {
            $mid = $drug['med'];

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
                'insurance_covered_amount'            => 0,
                'cash_amount'           => 0,
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
                    'insurance_covered_amount'         => 0,
                    'cash_amount'        => 0,
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
