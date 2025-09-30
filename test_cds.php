<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CDS System Test ===\n";

// Test 1: Check if CDS Alert model exists and table is accessible
try {
    $alertCount = \App\Models\CdsAlert::count();
    echo "✓ CDS Alert table accessible, current count: $alertCount\n";
} catch (Exception $e) {
    echo "✗ CDS Alert table error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if we can create a CDS alert manually
try {
    $alertService = app(\App\Services\CDS\CdsAlertService::class);
    $alert = $alertService->create([
        'patient_id' => 1,
        'visit_id' => 1,
        'subject_type' => 'prescription',
        'subject_id' => 1,
        'rule_key' => 'test_manual',
        'rule_version' => '1.0',
        'severity' => 'critical',
        'message' => 'Manual test alert',
        'rationale' => 'Testing CDS system manually',
        'payload' => ['test' => true],
        'status' => 'open',
    ]);
    echo "✓ CDS Alert created manually, ID: " . $alert->id . "\n";
} catch (Exception $e) {
    echo "✗ CDS Alert creation error: " . $e->getMessage() . "\n";
}

// Test 3: Check if CDS Engine is working
try {
    $engine = app(\App\Services\CDS\CdsEngine::class);
    echo "✓ CDS Engine instantiated successfully\n";
    
    // Test with dummy context
    $context = [
        'patient_id' => 1,
        'visit_id' => 1,
        'order' => [
            'prescription_id' => 999,
            'medication_id' => 1,
            'medication_name' => 'Test Drug',
            'dosage' => '10mg'
        ]
    ];
    
    $engine->check('medication_prescribe', $context);
    echo "✓ CDS Engine check completed\n";
    
} catch (Exception $e) {
    echo "✗ CDS Engine error: " . $e->getMessage() . "\n";
}

// Test 4: Check event system
try {
    $event = new \App\Events\MedicationPrescribed(1, ['test' => true], 1);
    echo "✓ MedicationPrescribed event created\n";
    
    $listener = new \App\Listeners\DispatchCdsChecks(app(\App\Services\CDS\CdsEngine::class));
    $listener->handle($event);
    echo "✓ CDS Listener executed\n";
    
} catch (Exception $e) {
    echo "✗ Event system error: " . $e->getMessage() . "\n";
}

// Final count
try {
    $finalCount = \App\Models\CdsAlert::count();
    echo "\nFinal CDS Alert count: $finalCount\n";
    
    $recentAlerts = \App\Models\CdsAlert::latest()->limit(5)->get();
    echo "Recent alerts:\n";
    foreach ($recentAlerts as $alert) {
        echo "- ID: {$alert->id}, Rule: {$alert->rule_key}, Severity: {$alert->severity}, Message: {$alert->message}\n";
    }
} catch (Exception $e) {
    echo "✗ Final check error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";