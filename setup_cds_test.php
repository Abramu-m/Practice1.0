<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Setting up CDS Test Data ===\n";

// Get first patient
$patient = \App\Models\Patient::first();
if (!$patient) {
    echo "No patients found in system\n";
    exit(1);
}

echo "Patient ID: {$patient->id}, Name: {$patient->first_name} {$patient->last_name}\n";

// Clear existing allergies for this patient
\App\Models\Allergy::where('patient_id', $patient->id)->delete();
echo "Cleared existing allergies for patient\n";

// Create allergy to Penicillin
$allergy = \App\Models\Allergy::create([
    'patient_id' => $patient->id,
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
if (!$medication) {
    $medication = \App\Models\Medication::create([
        'generic_name' => 'Amoxicillin/Clavulanate (Penicillin derivative)',
        'brand_name' => 'Augmentin',
        'strength' => '875mg/125mg',
        'formulation' => 'Tablet',
        'unit_price' => 15.00,
        'stock_quantity' => 50,
        'is_active' => true,
        'created_by' => 1,
        'updated_by' => 1
    ]);
    echo "Created medication: {$medication->generic_name}\n";
} else {
    echo "Found existing medication: {$medication->generic_name}\n";
}

// Clear existing CDS alerts for this patient
\App\Models\CdsAlert::where('patient_id', $patient->id)->delete();
echo "Cleared existing CDS alerts for patient\n";

echo "\n=== Test Setup Complete ===\n";
echo "Patient ID: {$patient->id}\n";
echo "Patient Name: {$patient->first_name} {$patient->last_name}\n";
echo "Allergy: {$allergy->substance_name} ({$allergy->severity})\n";
echo "Medication: {$medication->generic_name} (ID: {$medication->id})\n";
echo "\nNow try to prescribe the Penicillin-containing medication to this patient in the consultation interface.\n";