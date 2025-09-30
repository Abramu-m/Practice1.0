<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$patientId = 3; // From the visit we found
$visitId = 32;

echo "=== Setting up CDS Test for Existing Visit ===\n";
echo "Patient ID: $patientId\n";
echo "Visit ID: $visitId\n";

// Clear existing allergies and CDS alerts for this patient
\App\Models\Allergy::where('patient_id', $patientId)->delete();
\App\Models\CdsAlert::where('patient_id', $patientId)->delete();

// Create allergy to Penicillin
$allergy = \App\Models\Allergy::create([
    'patient_id' => $patientId,
    'substance_name' => 'Penicillin',
    'severity' => 'high',
    'reaction' => 'Severe allergic reaction, anaphylaxis risk',
    'is_active' => true,
    'created_by' => 1,
    'updated_by' => 1
]);

echo "Created allergy: {$allergy->substance_name} (severity: {$allergy->severity})\n";

// Find or create Penicillin medication
$medication = \App\Models\Medication::where('generic_name', 'like', '%Penicillin%')->first();
echo "Penicillin medication ID: {$medication->id} - {$medication->generic_name}\n";

echo "\n=== Ready for Testing ===\n";
echo "Visit URL: http://localhost:8001/consultations/$visitId\n";
echo "Patient has allergy to: Penicillin (high severity)\n";
echo "Try prescribing: {$medication->generic_name} (ID: {$medication->id})\n";
echo "Expected: CDS alert should appear when prescription is saved\n";