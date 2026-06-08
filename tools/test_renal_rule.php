<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Patient: Margaret (CDS-TEST-003), elderly female, DOB 1956-04-30
$patient = \App\Models\Patient::where('card_number', 'CDS-TEST-003')->first();
if (!$patient) {
    echo "PATIENT NOT FOUND\n";
    exit(1);
}

// Compute age from date_of_birth
$age = $patient->date_of_birth
    ? \Carbon\Carbon::parse($patient->date_of_birth)->age
    : 68;

// Get weight from vitals (or fallback)
$vitals = \App\Models\VitalSigns::where('patient_id', $patient->id)->latest('recorded_at')->first();
$weight = optional($vitals)->weight ?? 60;

echo "Patient: {$patient->first_name} {$patient->last_name}, Age: {$age}, Weight: {$weight}, Gender: {$patient->gender}\n";

// Find metformin in formulary (column is generic_name)
$med = \App\Models\Medication::where('generic_name', 'like', '%metformin%')->first();
echo "Medication: " . ($med ? "{$med->generic_name} #{$med->id}" : "NOT FOUND") . "\n";

$creatUmol = 412;
$creatMgDl = round($creatUmol / 88.4, 3);
$egfr = \App\Services\CDS\Calculators\EgfrCalculator::cockcroftGault(
    (float)$age,
    (float)$weight,
    $creatMgDl,
    $patient->gender ?? 'female'
);
echo "Creatinine: {$creatMgDl} mg/dL | eGFR: " . round($egfr, 1) . " mL/min\n";

$context = [
    'patient_id'     => $patient->id,
    'patient_age'    => $age,
    'patient_weight' => $weight,
    'patient_gender' => $patient->gender,
    'patient'        => [
        'id'                 => $patient->id,
        'age'                => $age,
        'gender'             => $patient->gender,
        'weight'             => $weight,
        'height'             => optional($vitals)->height,
        'allergies'          => [],
        'creatinine_umol_l'  => $creatUmol,
        'creatinine_mg_dl'   => $creatMgDl,
        'egfr'               => $egfr,
    ],
    'order'    => ['medication_id' => $med?->id, 'medication_name' => 'Metformin', 'dosage' => '500mg'],
    'visit_id' => 0,
];

echo "\n--- Testing RenalDoseRule (Metformin + eGFR " . round($egfr, 1) . ") ---\n";
$renalRule = new \App\Services\CDS\Rules\RenalDoseRule();
$result    = $renalRule->evaluate($context);
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";

echo "\n--- Testing RenalDoseRule (Tramadol + eGFR " . round($egfr, 1) . ") ---\n";
$context['order'] = ['medication_id' => null, 'medication_name' => 'Tramadol', 'dosage' => '50mg'];
$result2 = $renalRule->evaluate($context);
echo json_encode($result2, JSON_PRETTY_PRINT) . "\n";

echo "\n--- Testing DoseRangeRule (Paracetamol 1500 mg adult) ---\n";
$context['patient']['egfr'] = null; // not needed for dose range
$context['order'] = ['medication_id' => null, 'medication_name' => 'Paracetamol', 'dosage' => '1500mg'];
$doseRule = new \App\Services\CDS\Rules\DoseRangeRule();
$result3  = $doseRule->evaluate($context);
echo json_encode($result3, JSON_PRETTY_PRINT) . "\n";
