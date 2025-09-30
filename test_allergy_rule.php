<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing CDS Allergy Rule ===\n";

$patient = \App\Models\Patient::find(2);
$medication = \App\Models\Medication::find(18);

if (!$patient || !$medication) {
    echo "Patient or medication not found\n";
    exit(1);
}

echo "Patient: {$patient->first_name} {$patient->last_name} (ID: {$patient->id})\n";
echo "Medication: {$medication->generic_name} (ID: {$medication->id})\n";

// Get patient allergies
$allergies = \App\Models\Allergy::where('patient_id', $patient->id)->where('is_active', true)->get();
echo "Patient allergies:\n";
foreach ($allergies as $allergy) {
    echo "- {$allergy->substance_name} ({$allergy->severity})\n";
}

// Test the allergy rule directly
$allergyRule = new \App\Services\CDS\Rules\AllergyRule();
$context = [
    'patient_id' => $patient->id,
    'visit_id' => 1,
    'order' => [
        'prescription_id' => 999,
        'medication_id' => $medication->id,
        'medication_name' => $medication->generic_name,
        'dosage' => '500mg',
    ]
];

echo "\nTesting allergy rule with context:\n";
echo "- Patient ID: {$context['patient_id']}\n";
echo "- Medication ID: {$context['order']['medication_id']}\n";
echo "- Medication name: {$context['order']['medication_name']}\n";

$result = $allergyRule->evaluate($context);

if ($result) {
    echo "\n✓ ALLERGY CONFLICT DETECTED!\n";
    echo "Rule key: {$result['rule_key']}\n";
    echo "Severity: {$result['severity']}\n";
    echo "Message: {$result['message']}\n";
    echo "Rationale: {$result['rationale']}\n";
} else {
    echo "\n✗ No allergy conflict detected\n";
    
    // Debug: check if names match
    $medName = strtolower(trim($medication->generic_name ?: $medication->brand_name ?: ''));
    echo "Debug - Medication name (processed): '$medName'\n";
    
    foreach ($allergies as $allergy) {
        $sub = strtolower(trim($allergy->substance_name ?? ''));
        echo "Debug - Allergy substance (processed): '$sub'\n";
        echo "Debug - Contains check: " . (str_contains($medName, $sub) ? 'YES' : 'NO') . "\n";
        echo "Debug - Reverse contains: " . (str_contains($sub, $medName) ? 'YES' : 'NO') . "\n";
    }
}

// Now test the full CDS engine
echo "\n=== Testing Full CDS Engine ===\n";
$engine = app(\App\Services\CDS\CdsEngine::class);
$engine->check('medication_prescribe', $context);

$alerts = \App\Models\CdsAlert::where('patient_id', $patient->id)->get();
echo "CDS Alerts created: {$alerts->count()}\n";
foreach ($alerts as $alert) {
    echo "- {$alert->rule_key}: {$alert->message}\n";
}

echo "\n=== Test Complete ===\n";