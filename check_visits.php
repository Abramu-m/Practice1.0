<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$patient = \App\Models\Patient::find(2);
$visits = \App\Models\PatientVisit::where('patient', $patient->id)->get();

echo "Patient visits: " . $visits->count() . "\n";

if ($visits->count() > 0) {
    $visit = $visits->first();
    echo "Visit ID: {$visit->id}, Status: {$visit->visit_status}\n";
    echo "Visit URL: http://localhost:8001/consultations/{$visit->id}\n";
} else {
    echo "No visits found for patient. Create a visit first.\n";
}