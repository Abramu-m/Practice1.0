<?php

use Illuminate\Support\Facades\Route;

// CDS Testing Routes
Route::get('/test-cds', function() {
    // Test if we can create a CDS alert manually
    $alert = app(\App\Services\CDS\CdsAlertService::class)->create([
        'patient_id' => 1,
        'visit_id' => 1,
        'subject_type' => 'prescription',
        'subject_id' => null,
        'rule_key' => 'test_alert',
        'rule_version' => '1.0',
        'severity' => 'critical',
        'message' => 'Test alert message',
        'rationale' => 'This is a test alert to verify CDS system is working',
        'payload' => ['test' => true],
        'status' => 'open',
    ]);

    return response()->json([
        'message' => 'Test alert created',
        'alert' => $alert
    ]);
})->name('test.cds.create');

Route::get('/test-cds-event', function() {
    // Test if we can trigger the event and see if listeners work
    event(new \App\Events\MedicationPrescribed(
        1, // patient_id
        [
            'prescription_id' => 1,
            'medication_id' => 1,
            'medication_name' => 'Test Medication',
            'dosage' => '10mg',
            'administration_route_id' => 1,
            'frequency_id' => 1,
            'duration_days' => 7,
            'quantity' => 10,
            'unit_price' => 5.00,
        ],
        1 // visit_id
    ));

    return response()->json([
        'message' => 'Event dispatched',
        'alerts_count' => \App\Models\CdsAlert::count()
    ]);
})->name('test.cds.event');

Route::get('/test-cds-allergy', function() {
    // Create test data: patient with allergy to 'Penicillin'
    $patient = \App\Models\Patient::first();
    if (!$patient) {
        return response()->json(['error' => 'No patient found']);
    }
    
    // Create allergy record
    $allergy = \App\Models\Allergy::create([
        'patient_id' => $patient->id,
        'substance_name' => 'Penicillin',
        'severity' => 'high',
        'is_active' => true,
        'created_by' => 1,
        'updated_by' => 1
    ]);
    
    // Create or find a medication containing 'Penicillin'
    $medication = \App\Models\Medication::where('generic_name', 'like', '%Penicillin%')
        ->orWhere('brand_name', 'like', '%Penicillin%')
        ->first();
        
    if (!$medication) {
        $medication = \App\Models\Medication::create([
            'generic_name' => 'Penicillin V',
            'brand_name' => 'Pen V K',
            'strength' => '250mg',
            'formulation' => 'Tablet',
            'unit_price' => 5.00,
            'stock_quantity' => 100,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
    
    // Test the CDS system by dispatching event with conflicting medication
    event(new \App\Events\MedicationPrescribed(
        $patient->id,
        [
            'prescription_id' => 999,
            'medication_id' => $medication->id,
            'medication_name' => $medication->generic_name,
            'dosage' => '250mg',
            'administration_route_id' => 1,
            'frequency_id' => 1,
            'duration_days' => 7,
            'quantity' => 10,
            'unit_price' => 5.00,
        ],
        1 // visit_id
    ));
    
    $alerts = \App\Models\CdsAlert::where('patient_id', $patient->id)->get();
    
    return response()->json([
        'message' => 'Allergy conflict test completed',
        'patient_id' => $patient->id,
        'allergy_created' => $allergy->toArray(),
        'medication_used' => $medication->toArray(),
        'alerts_found' => $alerts->toArray(),
        'total_alerts' => $alerts->count()
    ]);
})->name('test.cds.allergy');

Route::get('/test-cds-duplicate', function() {
    // Test duplicate therapy rule
    $patient = \App\Models\Patient::first();
    if (!$patient) {
        return response()->json(['error' => 'No patient found']);
    }
    
    $medication = \App\Models\Medication::first();
    if (!$medication) {
        return response()->json(['error' => 'No medication found']);
    }
    
    // Create an existing active prescription
    $existingPrescription = \App\Models\Prescription::create([
        'patient_id' => $patient->id,
        'consultation_id' => 1,
        'doctor_id' => 1,
        'medication_id' => $medication->id,
        'dosage' => '500mg',
        'administration_route_id' => 1,
        'frequency_id' => 1,
        'duration_days' => 7,
        'quantity' => 14,
        'unit_price' => 5.00,
        'total_price' => 70.00,
        'status' => 'prescribed',
        'prescribed_at' => now(),
    ]);
    
    // Now try to prescribe the same medication (should trigger duplicate therapy alert)
    event(new \App\Events\MedicationPrescribed(
        $patient->id,
        [
            'prescription_id' => 998,
            'medication_id' => $medication->id,
            'medication_name' => $medication->generic_name,
            'dosage' => '500mg',
            'administration_route_id' => 1,
            'frequency_id' => 1,
            'duration_days' => 10,
            'quantity' => 20,
            'unit_price' => 5.00,
        ],
        1 // visit_id
    ));
    
    $alerts = \App\Models\CdsAlert::where('patient_id', $patient->id)->get();
    
    return response()->json([
        'message' => 'Duplicate therapy test completed',
        'patient_id' => $patient->id,
        'existing_prescription' => $existingPrescription->toArray(),
        'medication_used' => $medication->toArray(),
        'alerts_found' => $alerts->toArray(),
        'total_alerts' => $alerts->count()
    ]);
})->name('test.cds.duplicate');

Route::get('/test-cds-manual', function() {
    // Manually trigger CDS engine without events to test rules directly
    $patient = \App\Models\Patient::first();
    $medication = \App\Models\Medication::first();
    
    if (!$patient || !$medication) {
        return response()->json(['error' => 'Missing patient or medication']);
    }
    
    // Test the CDS engine directly
    $engine = app(\App\Services\CDS\CdsEngine::class);
    
    $context = [
        'patient_id' => $patient->id,
        'visit_id' => 1,
        'order' => [
            'prescription_id' => 997,
            'medication_id' => $medication->id,
            'medication_name' => $medication->generic_name,
            'dosage' => '250mg',
            'administration_route_id' => 1,
            'frequency_id' => 1,
            'duration_days' => 7,
            'quantity' => 10,
            'unit_price' => 5.00,
        ]
    ];
    
    // Call CDS engine directly
    $engine->check('medication_prescribe', $context);
    
    $alerts = \App\Models\CdsAlert::where('patient_id', $patient->id)->get();
    
    return response()->json([
        'message' => 'Manual CDS engine test completed',
        'patient_id' => $patient->id,
        'medication_used' => $medication->toArray(),
        'context' => $context,
        'alerts_found' => $alerts->toArray(),
        'total_alerts' => $alerts->count(),
        'log_info' => 'Check Laravel logs for detailed CDS execution info'
    ]);
})->name('test.cds.manual');