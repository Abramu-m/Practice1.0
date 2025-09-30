<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$visit = \App\Models\PatientVisit::first();

if ($visit) {
    echo "Visit ID: {$visit->id}, Patient ID: {$visit->patient}\n";
    echo "URL: http://localhost:8001/consultations/{$visit->id}\n";
    
    // Check if this patient has allergies
    $allergies = \App\Models\Allergy::where('patient_id', $visit->patient)->where('is_active', true)->get();
    echo "Patient allergies: {$allergies->count()}\n";
    foreach ($allergies as $allergy) {
        echo "- {$allergy->substance_name} ({$allergy->severity})\n";
    }
} else {
    echo "No visits found\n";
}