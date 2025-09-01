<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClinicalController;
use App\Http\Controllers\Icd10Controller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Medical Services Search (public access for frontend)
Route::get('/medical-services/search', [ClinicalController::class, 'searchMedicalServices']);
Route::get('/medical-services/{serviceId}/form-check', [ClinicalController::class, 'checkServiceFormRequirements']);

// Public MTUHA API for Select2
Route::get('/mtuha/search', [Icd10Controller::class, 'mtuhaSearch']);
Route::get('/mtuha/{id}', [Icd10Controller::class, 'mtuhaGet'])->where('id', '[0-9]+');

// Investigation Forms (public access for AJAX loading)
Route::get('/investigation-form/{formType}', function($formType) {
    try {
        // Get consultation ID from request or session
        $consultationId = request('consultation_id');
        
        if (!$consultationId) {
            // Fallback: use a default/example data for preview
            $visit = (object) [
                'id' => 'PREVIEW',
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i'),
                'created_at' => now(),
                'patientInfo' => (object) [
                    'id' => 'DEMO',
                    'first_name' => 'Amina',
                    'last_name' => 'Hassan',
                    'full_name' => 'Amina Hassan',
                    'date_of_birth' => '1985-03-15',
                    'age' => '40y 04m 10d',
                    'gender' => 'Female',
                    'address' => 'Kivukoni Street, Dar es Salaam',
                    'phone_number' => '+255 756 123 456',
                    'email' => 'amina.hassan@email.com',
                    'ctc_number' => 'CTC001789',
                    'patient_id' => 'P-2025-789'
                ],
                'doctor' => (object) [
                    'id' => 'DEMO',
                    'name' => 'Dr. Abramu Mibaraka',
                    'full_name' => 'Dr. Abramu Mibaraka',
                    'title' => 'Medical Officer',
                    'specialization' => 'General Medicine',
                    'license_number' => 'MD-12345',
                    'phone' => '+255 784 567 890'
                ],
                // Provide doctorInfo for views expecting the relation-style property
                'doctorInfo' => (object) [
                    'id' => 'DEMO',
                    'first_name' => 'Abramu',
                    'last_name' => 'Mibaraka',
                    'title' => 'Medical Officer',
                    'name' => 'Dr. Abramu Mibaraka'
                ],
                'facility' => (object) [
                    'id' => 'DEMO',
                    'name' => config('app.clinic_name', 'Sokoine Regional Hospital'),
                    'full_name' => config('app.clinic_name', 'Sokoine Regional Hospital'),
                    'address' => config('app.clinic_address', 'P.O. Box 1011, Lindi'),
                    'phone' => config('app.clinic_phone', '+255 23 202 0301'),
                    'email' => config('app.clinic_email', 'info@sokoineregional.go.tz'),
                    'region' => 'Lindi',
                    'district' => 'Lindi Urban',
                    'facility_code' => 'SRH001'
                ],
                'laboratory' => (object) [
                    'serial_number' => 'LAB-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'technician' => 'Charles Omary',
                    'reviewer' => 'Rebecca Korduni'
                ]
            ];
        } else {
            // Fetch real consultation data
            $consultation = \App\Models\Consultation::with([
                'patient', 
                'doctor', 
                'patientVisit'
            ])->findOrFail($consultationId);
            
            $patient = $consultation->patient;
            $doctor = $consultation->doctor;
            $patientVisit = $consultation->patientVisit;
            
            // Calculate age from date of birth
            $age = '';
            if ($patient->date_of_birth) {
                $birthDate = \Carbon\Carbon::parse($patient->date_of_birth);
                $now = \Carbon\Carbon::now();
                $years = $now->diffInYears($birthDate);
                $months = $now->copy()->subYears($years)->diffInMonths($birthDate);
                $days = $now->copy()->subYears($years)->subMonths($months)->diffInDays($birthDate);
                $age = "{$years}y {$months}m {$days}d";
            }
            
            $visit = (object) [
                'id' => $consultation->id,
                'date' => $consultation->consultation_date ?? $consultation->created_at->format('Y-m-d'),
                'time' => $consultation->created_at->format('H:i'),
                'created_at' => $consultation->created_at,
                'patientInfo' => (object) [
                    'id' => $patient->id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'full_name' => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
                    'date_of_birth' => $patient->date_of_birth,
                    'age' => $age,
                    'gender' => ucfirst($patient->gender ?? ''),
                    'address' => $patient->residence ?? '',
                    'phone_number' => $patient->contact ?? '',
                    'email' => '', // Not typically stored in patient model
                    'ctc_number' => $patient->card_number ?? '',
                    'patient_id' => $patient->id
                ],
                'doctor' => (object) [
                    'id' => $doctor->id ?? '',
                    'name' => ($doctor->title ?? 'Dr.') . ' ' . trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')),
                    'full_name' => ($doctor->title ?? 'Dr.') . ' ' . trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')),
                    'title' => $doctor->title ?? 'Medical Officer',
                    'specialization' => $doctor->specialization ?? 'General Medicine',
                    'license_number' => $doctor->license_number ?? '',
                    'phone' => $doctor->contact ?? ''
                ],
                // Also include doctorInfo for compatibility with views
                'doctorInfo' => $doctor,
                'facility' => (object) [
                    'id' => 1,
                    'name' => config('app.clinic_name', 'Sokoine Regional Hospital'),
                    'full_name' => config('app.clinic_name', 'Sokoine Regional Hospital'),
                    'address' => config('app.clinic_address', 'P.O. Box 1011, Lindi'),
                    'phone' => config('app.clinic_phone', '+255 23 202 0301'),
                    'email' => config('app.clinic_email', 'info@sokoineregional.go.tz'),
                    'region' => 'Lindi',
                    'district' => 'Lindi Urban',
                    'facility_code' => 'SRH001'
                ],
                'laboratory' => (object) [
                    'serial_number' => 'LAB-' . date('Y') . '-' . str_pad($consultation->id, 4, '0', STR_PAD_LEFT),
                    'technician' => 'Charles Omary',
                    'reviewer' => 'Rebecca Korduni'
                ]
            ];
        }
        
        $viewPath = "consultations.partials.investigation_forms.{$formType}";
        
        if (view()->exists($viewPath)) {
            return view($viewPath, compact('visit'))->render();
        } else {
            return response()->json(['error' => 'Form not found', 'viewPath' => $viewPath], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to load form', 'message' => $e->getMessage()], 500);
    }
});

// Clinical API Routes
Route::group(['prefix' => 'clinical', 'middleware' => 'auth:sanctum'], function () {
    
    // Dashboard and Overview
    Route::get('/dashboard', [ClinicalController::class, 'dashboard']);
    Route::get('/form-data', [ClinicalController::class, 'getFormData']);
    
    // Consultation Management
    Route::post('/consultations', [ClinicalController::class, 'startConsultation']);
    Route::get('/consultations/{consultationId}', [ClinicalController::class, 'getConsultation']);
    
    // Vital Signs
    Route::post('/consultations/{consultationId}/vital-signs', [ClinicalController::class, 'recordVitalSigns']);
    
    // Prescriptions
    Route::post('/consultations/{consultationId}/prescriptions', [ClinicalController::class, 'createPrescription']);
    Route::patch('/prescriptions/{prescriptionId}/status', [ClinicalController::class, 'updatePrescriptionStatus']);
    
    // Investigations
    Route::post('/consultations/{consultationId}/investigations', [ClinicalController::class, 'orderInvestigation']);
    
    // Medical Services
    Route::get('/medical-services/search', [ClinicalController::class, 'searchMedicalServices']);
    Route::get('/medical-services/{serviceId}/form-check', [ClinicalController::class, 'checkServiceFormRequirements']);
});

// Result Templates (public access for lab result forms)
Route::get('/result-template/{templateName}', function($templateName) {
    try {
        // Get investigation ID from request
        $investigationId = request('investigation_id');
        $testMedicalServiceId = request('test_medical_service_id'); // For testing purposes
        
        if (!$investigationId) {
            return response()->json(['error' => 'Investigation ID is required'], 400);
        }
        
        // Get the investigation with patient and consultation data
        $investigation = \App\Models\Investigation::with(['patient', 'consultation.patientVisit', 'medicalService'])
            ->findOrFail($investigationId);
        
        // Override medical service for testing if specified
        if ($testMedicalServiceId) {
            $testMedicalService = \App\Models\MedicalService::find($testMedicalServiceId);
            if ($testMedicalService) {
                $investigation->medicalService = $testMedicalService;
            }
        }
        
        // Get the visit through the consultation
        $visit = $investigation->consultation->patientVisit ?? null;
        
        // Map template names to view files
        $templateMap = [
            'tb' => 'lab.result_templates.tb',
            'general_lab' => 'lab.result_templates.general',
            'cd4' => 'lab.result_templates.cd4',
            'simple_lab' => 'lab.result_templates.simple',
            'blood_count' => 'lab.result_templates.general',
            'chemistry' => 'lab.result_templates.general',
        ];
        
        // Check if template exists
        if (!isset($templateMap[$templateName])) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        
        $viewPath = $templateMap[$templateName];
        
        // Check if view file exists
        if (!\Illuminate\Support\Facades\View::exists($viewPath)) {
            return response()->json(['error' => 'Template file not found'], 404);
        }
        
        // Render the template with investigation data
        $html = view($viewPath, [
            'visit' => $visit,
            'investigation' => $investigation,
            'patient' => $investigation->patient,
            'medicalService' => $investigation->medicalService,
            'consultation' => $investigation->consultation,
        ])->render();
        
        return response($html)->header('Content-Type', 'text/html');
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'Investigation not found'], 404);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Result template error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load template'], 500);
    }
});
