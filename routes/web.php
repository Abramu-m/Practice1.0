<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PatientCategoryController;
use App\Http\Controllers\VisitTypeController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientVisitController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ConsultationFeeController;
use App\Http\Controllers\MedicalServiceController;
use App\Http\Controllers\SampleTypeController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\VitalsController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InvestigationFormController;
use App\Http\Controllers\Api\ClinicalController as ApiClinicalController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\Hr\EmployeeController as HrEmployeeController;
use App\Http\Controllers\Hr\EmployeeSalaryComponentController;
use App\Http\Controllers\Hr\HrDashboardController;
use App\Http\Controllers\Hr\PayeTaxBandController;
use App\Http\Controllers\Hr\SalaryPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ================================
// PUBLIC ROUTES
// ================================

Route::get('/', [AuthenticatedSessionController::class, 'create']);

// Temporary auto-login route for testing (remove in production)
Route::get('/auto-login', function () {
    $user = \App\Models\User::where('email', 'mabramu94@gmail.com')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/dashboard');
    }
    return 'Admin user not found';
});

Route::get('/offline', function () {
    return view('offline');
});

// ================================
// BASIC AUTH ROUTES
// ================================

Route::middleware('auth')->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Signature management
    Route::get('/profile/signature', [ProfileController::class, 'editSignature'])->name('profile.signature.edit');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');
    Route::delete('/profile/signature', [ProfileController::class, 'destroySignature'])->name('profile.signature.destroy');

    // Verification notice
    Route::get('/verification/notice', function () {
        return view('verification.notice');
    })->name('custom.verification.notice');
});

// Settings - admins only
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/facility', [App\Http\Controllers\SettingsController::class, 'updateFacility'])->name('settings.facility.update');
    Route::put('/settings/report-config', [App\Http\Controllers\SettingsController::class, 'updateReportConfig'])->name('settings.report-config.update');
    Route::get('/settings/reports/malaria-vipimo', [App\Http\Controllers\SettingsController::class, 'malariaVipimoSettings'])->name('settings.reports.malaria-vipimo');
    Route::put('/settings/reports/malaria-vipimo', [App\Http\Controllers\SettingsController::class, 'updateMalariaVipimoSettings'])->name('settings.reports.malaria-vipimo.update');
    Route::get('/settings/reports/alu-monthly', [App\Http\Controllers\SettingsController::class, 'aluReportSettings'])->name('settings.reports.alu-monthly');
    Route::put('/settings/reports/alu-monthly', [App\Http\Controllers\SettingsController::class, 'updateAluReportSettings'])->name('settings.reports.alu-monthly.update');
});

// Work email (webmail) - any authenticated user with a verified facility-domain email
Route::middleware(['auth', 'email_access'])->prefix('email')->name('email.')->group(function () {
    Route::get('/', [App\Http\Controllers\EmailController::class, 'index'])->name('index');
    Route::post('/connect', [App\Http\Controllers\EmailController::class, 'connect'])->name('connect');
    Route::post('/disconnect', [App\Http\Controllers\EmailController::class, 'disconnect'])->name('disconnect');
    Route::get('/compose', [App\Http\Controllers\EmailController::class, 'compose'])->name('compose');
    Route::post('/send', [App\Http\Controllers\EmailController::class, 'send'])->name('send');
    Route::get('/message/{uid}', [App\Http\Controllers\EmailController::class, 'show'])->name('show');
    Route::get('/message/{uid}/attachment', [App\Http\Controllers\EmailController::class, 'attachment'])->name('attachment');
});

// Simple authenticated pages: Help, facility setup, nav view
Route::middleware('auth')->group(function () {
    Route::get('/facility-setup-required', function () {
        return view('facility.setup-required');
    })->name('facility.setup-required');

    Route::get('/help', function () {
        return view('help.index');
    })->name('help.index');

    Route::post('/nav-view/{view}', [App\Http\Controllers\NavViewController::class, 'switch'])
        ->whereIn('view', ['role', 'admin'])
        ->name('nav_view.switch');
});

// ================================
// ADMIN ROUTES
// ================================

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class, 'facility_setup'])->group(function () {
    // User verification management
    Route::get('/users/pending-verification', [UserController::class, 'pendingVerification'])
        ->name('users.pending-verification');
    Route::patch('/users/{id}/verify', [UserController::class, 'verify'])
        ->name('users.verify');
    Route::patch('/users/{id}/unverify', [UserController::class, 'unverify'])
        ->name('users.unverify');
    Route::patch('/users/{id}/activate', [UserController::class, 'activate'])
        ->name('users.activate');
    Route::post('/users/bulk-verify', [UserController::class, 'bulkVerify'])
        ->name('users.bulk-verify');
    
    // Admin-triggered password reset for users
    Route::get('/users/password/reset', [UserController::class, 'passwordResetForm'])
        ->name('users.password.reset');
    Route::post('/users/password/reset', [UserController::class, 'sendPasswordResetLink'])
        ->name('users.password.send-reset');

    // Admin direct password reset (set a new password for a user)
    Route::get('/users/{id}/reset-password', [UserController::class, 'adminResetForm'])
        ->name('users.reset-password');
    Route::post('/users/{id}/reset-password', [UserController::class, 'adminResetPassword'])
        ->name('users.reset-password.post');

    // Work email assignment & verification (admin assigns mailbox + credentials to a user)
    Route::get('/users/email-verification', [UserController::class, 'emailVerificationIndex'])
        ->name('users.email-verification.index');
    Route::get('/users/{id}/email-verification', [UserController::class, 'editEmailVerification'])
        ->name('users.email-verification.edit');
    Route::post('/users/{id}/email-verification', [UserController::class, 'updateEmailVerification'])
        ->name('users.email-verification.update');
    Route::delete('/users/{id}/email-verification', [UserController::class, 'destroyEmailVerification'])
        ->name('users.email-verification.destroy');
    
    Route::get('investigation-forms/{form}/preview', function ($form) {
        $view = 'lab.forms.' . $form;
        if (!view()->exists($view)) {
            return response('<div class="alert alert-info">No preview available for this form.</div>', 200)
                ->header('Content-Type', 'text/html');
        }

        $visit = (object) [
            'id'         => 'PREVIEW',
            'date'       => now()->format('Y-m-d'),
            'time'       => now()->format('H:i'),
            'created_at' => now(),
            'department' => 'OPD',
            'patientInfo' => (object) [
                'first_name'   => 'Preview',
                'last_name'    => 'Patient',
                'full_name'    => 'Preview Patient',
                'date_of_birth'=> '1985-03-15',
                'age'          => '40y 00m 00d',
                'gender'       => 'Female',
                'address'      => 'Example Street, Dar es Salaam',
                'phone_number' => '+255 700 000 000',
                'ctc_number'   => 'CTC-PREVIEW',
                'file_number'  => 'P-PREVIEW',
                'id'           => 0,
            ],
            'doctorInfo' => (object) [
                'id'         => 0,
                'first_name' => 'Preview',
                'last_name'  => 'Doctor',
                'title'      => 'Dr.',
                'user'       => (object) ['name' => 'Dr. Preview Doctor'],
            ],
            'doctor' => (object) [
                'name'      => 'Dr. Preview Doctor',
                'full_name' => 'Dr. Preview Doctor',
                'title'     => 'Medical Officer',
            ],
            'facility' => (object) [
                'name'      => config('app.clinic_name', 'Medical Facility'),
                'full_name' => config('app.clinic_name', 'Medical Facility'),
                'address'   => config('app.clinic_address', ''),
                'phone'     => config('app.clinic_phone', ''),
            ],
            'laboratory' => (object) [
                'serial_number' => 'LAB-PREVIEW',
                'technician'    => 'Lab Technician',
                'reviewer'      => 'Supervisor',
            ],
        ];

        try {
            $html = view($view, compact('visit'))->render();
        } catch (\Throwable $e) {
            $html = '<div class="alert alert-danger">Failed to render preview: ' . e($e->getMessage()) . '</div>';
        }
        return response($html, 200)->header('Content-Type', 'text/html');
    })->name('investigation-forms.preview');

    Route::resource('investigation-forms', InvestigationFormController::class);

    // ================================
    // ADMIN REPORTS ROUTES
    // ================================

    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        // Reports Dashboard
        Route::get('/', [AdminReportController::class, 'index'])
            ->name('index');

        // Monthly Reports
        Route::get('/malaria-vipimo', [AdminReportController::class, 'malariaVipimo'])
            ->name('malaria-vipimo');

        Route::get('/std-sti-monthly', [AdminReportController::class, 'stdStiMonthly'])
            ->name('std-sti-monthly');

        Route::get('/medicines-monthly', [AdminReportController::class, 'medicinesMonthly'])
            ->name('medicines-monthly');

        Route::get('/tracer-medicines', [AdminReportController::class, 'tracerMedicines'])
            ->name('tracer-medicines');

        // Weekly Reports
        Route::get('/idsr-weekly', [AdminReportController::class, 'idsrWeekly'])
            ->name('idsr-weekly');

        Route::get('/malaria-weekly-surveillance', [AdminReportController::class, 'malariaWeeklySurveillance'])
            ->name('malaria-weekly-surveillance');

        // On-Demand Reports
        Route::get('/low-stock-medicines', [AdminReportController::class, 'lowStockMedicines'])
            ->name('low-stock-medicines');

        // Additional Monthly Reports
        Route::get('/dtc-monthly', [AdminReportController::class, 'dtcMonthly'])
            ->name('dtc-monthly');

        Route::get('/monthly-lab-reports', [AdminReportController::class, 'monthlyLabReports'])
            ->name('monthly-lab-reports');

        // Lab Report Sub-types
        Route::get('/lab-hematology', [AdminReportController::class, 'labHematology'])
            ->name('lab-hematology');

        Route::get('/lab-blood-transfusion', [AdminReportController::class, 'labBloodTransfusion'])
            ->name('lab-blood-transfusion');

        Route::get('/lab-clinical-chemistry', [AdminReportController::class, 'labClinicalChemistry'])
            ->name('lab-clinical-chemistry');

        Route::get('/lab-microbiology', [AdminReportController::class, 'labMicrobiology'])
            ->name('lab-microbiology');

        Route::get('/lab-serology', [AdminReportController::class, 'labSerology'])
            ->name('lab-serology');

        Route::get('/lab-parasitology', [AdminReportController::class, 'labParasitology'])
            ->name('lab-parasitology');

        Route::get('/alu-monthly', [AdminReportController::class, 'aluMonthly'])
            ->name('alu-monthly');
    });

    // Lab Report Setup
    Route::get('/admin/lab-settings/blood-transfusion', [\App\Http\Controllers\BloodTransfusionReportSetupController::class, 'index'])
        ->name('admin.lab-settings.blood-transfusion.index');
    Route::put('/admin/lab-settings/blood-transfusion', [\App\Http\Controllers\BloodTransfusionReportSetupController::class, 'update'])
        ->name('admin.lab-settings.blood-transfusion.update');

    Route::get('/admin/lab-settings/hematology', [\App\Http\Controllers\HematologyReportSetupController::class, 'index'])
        ->name('admin.lab-settings.hematology.index');
    Route::put('/admin/lab-settings/hematology', [\App\Http\Controllers\HematologyReportSetupController::class, 'update'])
        ->name('admin.lab-settings.hematology.update');

    Route::get('/admin/lab-settings/clinical-chemistry', [\App\Http\Controllers\ClinicalChemistryReportSetupController::class, 'index'])
        ->name('admin.lab-settings.clinical-chemistry.index');
    Route::put('/admin/lab-settings/clinical-chemistry', [\App\Http\Controllers\ClinicalChemistryReportSetupController::class, 'update'])
        ->name('admin.lab-settings.clinical-chemistry.update');

    Route::get('/admin/lab-settings/serology', [\App\Http\Controllers\SerologyReportSetupController::class, 'index'])
        ->name('admin.lab-settings.serology.index');
    Route::put('/admin/lab-settings/serology', [\App\Http\Controllers\SerologyReportSetupController::class, 'update'])
        ->name('admin.lab-settings.serology.update');

    Route::get('/admin/lab-settings/parasitology', [\App\Http\Controllers\ParasitologyReportSetupController::class, 'index'])
        ->name('admin.lab-settings.parasitology.index');
    Route::put('/admin/lab-settings/parasitology', [\App\Http\Controllers\ParasitologyReportSetupController::class, 'update'])
        ->name('admin.lab-settings.parasitology.update');

    Route::get('/admin/lab-settings/microbiology', [\App\Http\Controllers\MicrobiologyReportSetupController::class, 'index'])
        ->name('admin.lab-settings.microbiology.index');
    Route::put('/admin/lab-settings/microbiology', [\App\Http\Controllers\MicrobiologyReportSetupController::class, 'update'])
        ->name('admin.lab-settings.microbiology.update');

    // Pharmacy & Inventory Report Setup
    Route::get('/admin/pharmacy-settings/medicine-dispensing', [\App\Http\Controllers\MedicineDispensingReportSetupController::class, 'index'])
        ->name('admin.pharmacy-settings.medicine-dispensing.index');
    Route::put('/admin/pharmacy-settings/medicine-dispensing', [\App\Http\Controllers\MedicineDispensingReportSetupController::class, 'update'])
        ->name('admin.pharmacy-settings.medicine-dispensing.update');

});

// ================================
// VERIFIED USER ROUTES
// ================================

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsVerified::class, 'facility_setup'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Role-specific Dashboards
    Route::get('/dashboard/doctor', [App\Http\Controllers\DashboardController::class, 'doctorDashboard'])->name('dashboard.doctor');
    Route::get('/dashboard/nurse', [App\Http\Controllers\DashboardController::class, 'nurseDashboard'])->name('dashboard.nurse');
    Route::get('/dashboard/lab-technician', [App\Http\Controllers\DashboardController::class, 'labTechnicianDashboard'])->name('dashboard.lab_technician');
    Route::get('/dashboard/radiologist', [App\Http\Controllers\DashboardController::class, 'radiologistDashboard'])->name('dashboard.radiologist');

    // Dashboard Demo Page
    Route::get('/dashboard-demo', function () {
        return view('dashboard-demo');
    })->name('dashboard.demo');

    // ================================
    // CLINICAL SYSTEM ROUTES
    // ================================

    // Clinical Dashboard
    Route::get('/clinical/dashboard', function () {
        return view('clinical.dashboard');
    })->name('clinical.dashboard');

    // Patient Management
    Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');
Route::get('medications/search', [\App\Http\Controllers\MedicationController::class, 'search'])->name('medications.search');
    Route::resource('patients', PatientController::class);
    Route::get('patients/visits/{visit}/investigations-partial', [PatientController::class, 'getVisitInvestigationsPartial'])->name('patients.visit_investigations_partial');
    Route::resource('patient_categories', PatientCategoryController::class);
    Route::resource('patient_visits', PatientVisitController::class)->names('patient_visits');
    
    // Ready Investigation Results View
    Route::get('readyInvResults', [PatientVisitController::class, 'readyInvResults'])->name('readyInvResults');
    Route::post('/patient-visits/{patient_visit}/toggle-informed', [PatientVisitController::class, 'toggleInformed'])
        ->name('patient_visits.toggle_informed');

    // Print results routes
    Route::get('/patient-visits/{visit}/print-results', [PatientVisitController::class, 'printResults'])->name('patient_visits.print_results');
    Route::get('/patient-visits/{visit}/results-details', [PatientVisitController::class, 'resultsDetails'])->name('patient_visits.results_details');
    Route::post('/print-multiple-results', [PatientVisitController::class, 'printMultipleResults'])->name('print_multiple_results');

    // Visit results PDF (used by complex results modal)
    Route::get('lab/visit-results/{visit}/pdf', [App\Http\Controllers\LabController::class, 'downloadVisitResultsPdf'])
        ->name('lab.visit_results_pdf');

    // NHIF Integration
    Route::prefix('nhif')->name('nhif.')->group(function () {
        Route::get('/verify', [App\Http\Controllers\NhifController::class, 'verifyView'])->name('verify');
        Route::get('/tariffs', [App\Http\Controllers\NhifController::class, 'tariffsView'])->name('tariffs');
        Route::get('/claims', [App\Http\Controllers\NhifController::class, 'claimsView'])->name('claims');
        Route::get('/reports', [App\Http\Controllers\NhifController::class, 'reportsView'])->name('reports');
        
        // API Endpoints
        Route::post('/verify-member', [App\Http\Controllers\NhifController::class, 'verifyMember'])->name('verify-member');
        Route::post('/get-card-details', [App\Http\Controllers\NhifController::class, 'getCardDetails'])->name('get-card-details');
        Route::post('/authorize', [App\Http\Controllers\NhifController::class, 'authorize'])->name('authorize');
        Route::post('/sync-tariffs', [App\Http\Controllers\NhifController::class, 'syncTariffs'])->name('sync-tariffs');
        Route::post('/submit-claim', [App\Http\Controllers\NhifController::class, 'submitClaim'])->name('submit-claim');
        Route::post('/generate-report', [App\Http\Controllers\NhifController::class, 'generateReport'])->name('generate-report');
        Route::post('/quick-report', [App\Http\Controllers\NhifController::class, 'quickReport'])->name('quick-report');
        
        // Export routes
        Route::get('/export-tariffs', [App\Http\Controllers\NhifController::class, 'exportTariffs'])->name('export-tariffs');
        Route::get('/export-claims', [App\Http\Controllers\NhifController::class, 'exportClaims'])->name('export-claims');

        //Claim management
        Route::get('/preview-claim/{visit}', [App\Http\Controllers\NhifController::class, 'previewClaim'])->name('preview-claim');
        Route::post('/create-claim/', [App\Http\Controllers\NhifController::class, 'createClaim'])->name('create-claim');
        Route::post('/submit-batch', [App\Http\Controllers\NhifController::class, 'submitBatch'])->name('submit-batch');
        Route::get('/claims/{claim}', [App\Http\Controllers\NhifController::class, 'viewClaim'])->name('view-claim');
        Route::delete('/claims/{claim}', [App\Http\Controllers\NhifController::class, 'deleteClaim'])->name('delete-claim');

        // Batch management
        Route::get('/claim-batches', [App\Http\Controllers\NhifClaimBatchController::class, 'index'])->name('claim-batches.index');
        Route::get('/claim-batches/{batch}', [App\Http\Controllers\NhifClaimBatchController::class, 'show'])->name('claim-batches.show');
        Route::delete('/claim-batches/{batch}', [App\Http\Controllers\NhifClaimBatchController::class, 'destroy'])->name('claim-batches.destroy');

        // NHIF Service Mapping
        Route::get('/service-mapping', [App\Http\Controllers\MedicalServiceInsuranceMapController::class, 'nhifIndex'])->name('service-mapping');

        // NHIF Medication Mapping
        Route::get('/medication-mapping', [App\Http\Controllers\MedicationInsuranceMapController::class, 'nhifIndex'])->name('medication-mapping');

        // Integration Settings
        Route::get('/settings', [App\Http\Controllers\NhifController::class, 'settingsView'])->name('settings');
        Route::put('/settings', [App\Http\Controllers\NhifController::class, 'updateSettings'])->name('settings.update');

    });

    // Staff Management
    Route::resource('users', UserController::class)->names('users');
    Route::resource('doctors', DoctorController::class)->names('doctors');
    Route::resource('designations', DesignationController::class)->names('designations');

    // Clinical Configuration
    Route::resource('visit_types', VisitTypeController::class)->names('visit_types');
    Route::resource('consultation_fees', ConsultationFeeController::class)->names('consultation_fees');
    Route::get('consultation-fees/get-fee', [ConsultationFeeController::class, 'getFee'])
        ->name('consultation_fees.get_fee');

    // Medical Services
    Route::get('medical-services/pricing', [MedicalServiceController::class, 'pricing'])->name('medical_services.pricing');
    Route::patch('medical-services/{medicalService}/price', [MedicalServiceController::class, 'updatePrice'])->name('medical_services.update-price');
    Route::resource('medical_services', MedicalServiceController::class)->names('medical_services');
    Route::patch('medical-services/{medical_service}/toggle-status', [MedicalServiceController::class, 'toggleStatus'])
        ->name('medical_services.toggle-status');

    // Insurance Pricing Maps
    Route::resource('medication-insurance-map', App\Http\Controllers\MedicationInsuranceMapController::class);
    Route::resource('medical-service-insurance-map', App\Http\Controllers\MedicalServiceInsuranceMapController::class);

    // Tariff lookup (AJAX for insurance item code Select2)
    Route::get('tariff-lookup/search', [App\Http\Controllers\TariffLookupController::class, 'search'])
        ->name('tariff-lookup.search');
    Route::get('tariff-lookup/item', [App\Http\Controllers\TariffLookupController::class, 'item'])
        ->name('tariff-lookup.item');

    Route::resource('sample_types', SampleTypeController::class)->names('sample_types');
    Route::patch('sample-types/{sample_type}/toggle-status', [SampleTypeController::class, 'toggleStatus'])
        ->name('sample_types.toggle-status');
    
    Route::resource('service_categories', ServiceCategoryController::class)->names('service_categories');
    Route::patch('service-categories/{service_category}/toggle-status', [ServiceCategoryController::class, 'toggleStatus'])
        ->name('service_categories.toggle-status');
    
    // Result Templates Management
    Route::resource('result-templates', \App\Http\Controllers\ResultTemplateController::class)->names('result-templates');
    Route::patch('result-templates/{result_template}/toggle-status', [\App\Http\Controllers\ResultTemplateController::class, 'toggleStatus'])
        ->name('result-templates.toggle-status');
    // Preview route for rendering template partials in a modal (AJAX)
    Route::get('result-templates/{result_template}/preview', [\App\Http\Controllers\ResultTemplateController::class, 'preview'])
        ->name('result-templates.preview');
    // Results preview route — shows mock finalized-results display (AJAX)
    Route::get('result-templates/{result_template}/results-preview', [\App\Http\Controllers\ResultTemplateController::class, 'resultsPreview'])
        ->name('result-templates.results-preview');
    Route::get('api/result-templates/by-category', [\App\Http\Controllers\ResultTemplateController::class, 'getByServiceCategory'])
        ->name('api.result-templates.by-category');

    // Investigation Management
    Route::resource('investigations', App\Http\Controllers\InvestigationController::class);
    Route::patch('/investigations/{investigation}/status', [App\Http\Controllers\InvestigationController::class, 'updateStatus'])
        ->name('investigations.update-status');
    Route::patch('/investigations/{investigation}/cancel', [App\Http\Controllers\InvestigationController::class, 'cancel'])
        ->name('investigations.cancel');
    Route::get('/investigations-statistics', [App\Http\Controllers\InvestigationController::class, 'statistics'])
        ->name('investigations.statistics');

    // Investigation Form Records (view & print saved form data)
    Route::get('/investigation-form-records', [\App\Http\Controllers\InvestigationFormRecordController::class, 'index'])
        ->name('investigation-form-records.index');
    Route::get('/investigation-form-records/{id}', [\App\Http\Controllers\InvestigationFormRecordController::class, 'show'])
        ->name('investigation-form-records.show');

    // Patient Visit API endpoints
    Route::get('/patient-visits/{visit}/category', [App\Http\Controllers\PatientVisitController::class, 'getVisitCategory']);
    Route::get('/patient-visits/{visit}/investigations-partial', [App\Http\Controllers\PatientVisitController::class, 'getInvestigationsPartial'])
        ->name('patient_visits.investigations_partial');

    // CDS Alerts: acknowledge/override/dismiss
    Route::post('/cds-alerts/{alert}/ack', [App\Http\Controllers\CdsAlertController::class, 'acknowledge'])->name('cds.alerts.ack');
    Route::get('/api/patients/{patient}/check-active-visit', [App\Http\Controllers\PatientVisitController::class, 'checkPatientActiveVisitApi'])
        ->name('patients.check_active_visit');

    // Medical Service Consumable Template Management (Lab System)
    Route::prefix('lab/service-consumables')->name('lab.service-consumables.')->group(function () {
        Route::get('/', [App\Http\Controllers\InvestigationConsumableController::class, 'index'])
            ->name('index');
        Route::get('/items', [App\Http\Controllers\InvestigationConsumableController::class, 'getItems'])
            ->name('items');
        Route::get('/stock-summary', [App\Http\Controllers\InvestigationConsumableController::class, 'stockSummary'])
            ->name('stock-summary');
        Route::post('/bulk-check', [App\Http\Controllers\InvestigationConsumableController::class, 'bulkCheck'])
            ->name('bulk-check');
    });

    // Medical Service Consumable Template Management - Individual Service Routes
    Route::prefix('lab/medical-services/{medicalService}/consumables')->name('lab.service-consumables.individual.')->group(function () {
        Route::get('/', [App\Http\Controllers\InvestigationConsumableController::class, 'show'])
            ->name('show');
        Route::post('/', [App\Http\Controllers\InvestigationConsumableController::class, 'store'])
            ->name('store');
        Route::get('/{consumable}/edit', [App\Http\Controllers\InvestigationConsumableController::class, 'edit'])
            ->name('edit');
        Route::put('/{consumable}', [App\Http\Controllers\InvestigationConsumableController::class, 'update'])
            ->name('update');
        Route::delete('/{consumable}', [App\Http\Controllers\InvestigationConsumableController::class, 'destroy'])
            ->name('destroy');
        Route::delete('/', [App\Http\Controllers\InvestigationConsumableController::class, 'clear'])
            ->name('clear');
        Route::get('/check-stock', [App\Http\Controllers\InvestigationConsumableController::class, 'checkStock'])
            ->name('check-stock');
        Route::get('/{consumable}/check-stock', [App\Http\Controllers\InvestigationConsumableController::class, 'checkSingleStock'])
            ->name('check-single-stock');
    });

    // ================================
    // CONSULTATION SYSTEM
    // ================================

    // Main consultation routes
    Route::get('consultations/{visitId}', [ConsultationController::class, 'show'])
        ->name('consultations.show');
    Route::post('consultations/{consultation}/referrals', [ConsultationController::class, 'storeReferralLetter'])
        ->name('consultations.referrals.store');
    Route::get('consultations/{consultation}/case-summary-pdf', [ConsultationController::class, 'generateCaseSummaryPdf'])
        ->name('consultations.case_summary_pdf');
    Route::put('consultations/{consultationId}', [ConsultationController::class, 'update'])
        ->name('consultations.update');

    // Vitals management
    Route::post('consultations/{consultationId}/vitals', [ConsultationController::class, 'storeVitals'])
        ->name('consultations.store_vitals');
    Route::post('consultations/{consultationId}/quick-vitals', [ConsultationController::class, 'storeQuickVitals'])
        ->name('consultations.quick-vitals');

    // Examinations
    Route::post('consultations/{consultationId}/examinations', [ConsultationController::class, 'storeExamination'])
        ->name('consultations.store_examination');
    Route::get('consultations/examinations/{examinationId}', [ConsultationController::class, 'getExamination'])
        ->name('consultations.get_examination');
    Route::put('consultations/examinations/{examinationId}', [ConsultationController::class, 'updateExamination'])
        ->name('consultations.update_examination');
    // Delete systemic examination
    Route::delete('consultations/examinations/{examinationId}', [ConsultationController::class, 'deleteExamination'])
        ->name('consultations.delete_examination');

    // AJAX partial for examinations list
    Route::get('consultations/{consultationId}/examinations-partial', [ConsultationController::class, 'getExaminationsPartial'])
        ->name('consultations.examinations_partial');

    // Visit-based examination routes (for modal)
    Route::get('patient-visits/{visitId}/examinations', [ConsultationController::class, 'getExaminationsByVisit'])
        ->name('patient-visits.examinations');
    Route::post('patient-visits/{visitId}/examinations', [ConsultationController::class, 'storeExaminationByVisit'])
        ->name('patient-visits.store_examination');

    // Prescriptions
    Route::post('consultations/{consultationId}/prescriptions', [ConsultationController::class, 'storePrescription'])
        ->name('consultations.store_prescription');
    Route::get('consultations/{consultationId}/prescriptions-partial', [ConsultationController::class, 'getPrescriptionsPartial'])
        ->name('consultations.prescriptions_partial');
    Route::get('consultations/{consultationId}/prescriptions-partial-html', [ConsultationController::class, 'getPrescriptionsPartialHtml'])
        ->name('consultations.prescriptions_partial_html');
    // Prescriptions PDF
    Route::get('consultations/{consultation}/prescriptions-pdf', [ConsultationController::class, 'downloadPrescriptionsPdf'])
        ->name('consultations.prescriptions_pdf');

    // Investigations
    Route::post('consultations/{consultationId}/investigations', [ConsultationController::class, 'storeInvestigation'])
        ->name('consultations.store_investigation');
    Route::get('consultations/{consultationId}/investigations-partial', [ConsultationController::class, 'getInvestigationsPartial'])
        ->name('consultations.investigations_partial');
    Route::delete('consultations/investigations/{investigationId}', [ConsultationController::class, 'removeInvestigation'])
        ->name('consultations.remove_investigation');

    // ICD10 Diagnoses
    Route::put('/consultations/{consultation}/diagnosis', [ConsultationController::class, 'updateDiagnosis']);
    Route::post('/consultations/{consultation}/icd-diagnoses', [ConsultationController::class, 'addIcdDiagnosis']);
    Route::get('/consultations/{consultation}/icd-diagnoses', [ConsultationController::class, 'getIcdDiagnoses']);
    Route::delete('/consultations/icd-diagnoses/{icdDiagnosis}', [ConsultationController::class, 'removeIcdDiagnosis']);

    // Past Medical History
    Route::get('patients/{patient}/medical-history', [ConsultationController::class, 'getPatientMedicalHistory'])
        ->name('patients.medical-history.show');
    Route::post('past-medical-history', [ConsultationController::class, 'storePastMedicalHistory'])
        ->name('past-medical-history.store');

    // CDS Alerts
    Route::post('consultations/{consultationId}/cds-alerts/{alertId}/ack', [ConsultationController::class, 'acknowledgeCdsAlert'])
        ->name('consultations.cds_alerts.acknowledge');

    // Allergies (structured)
    Route::get('patients/{patient}/allergies', [AllergyController::class, 'index'])->name('patients.allergies.index');
    Route::post('patients/{patient}/allergies', [AllergyController::class, 'store'])->name('patients.allergies.store');
    Route::put('allergies/{allergy}', [AllergyController::class, 'update'])->name('allergies.update');
    Route::post('allergies/{allergy}/deactivate', [AllergyController::class, 'deactivate'])->name('allergies.deactivate');
    Route::delete('allergies/{allergy}', [AllergyController::class, 'destroy'])->name('allergies.destroy');

    // Test Results (manual entry) removed: results are managed in the Lab module.

    // Support data routes
    Route::get('drugs/{drugId}/details', [ConsultationController::class, 'getDrugDetails'])
        ->name('drugs.details');
    Route::get('services/{serviceId}/details', [ConsultationController::class, 'getServiceDetails'])
        ->name('services.details');

    // MTUHA monthly report (legacy port)
    Route::get('/reports/mtuha', [App\Http\Controllers\MtuhaReportController::class, 'select'])
        ->name('reports.mtuha.select');
    Route::match(['get','post'], '/reports/mtuha/month', [App\Http\Controllers\MtuhaReportController::class, 'month'])
        ->name('reports.mtuha.month');

    // System Logs (admin only)
    Route::get('/system/logs', [App\Http\Controllers\SystemLogController::class, 'index'])
        ->middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)
        ->name('system.logs.index');
    Route::post('/system/logs/clear', [App\Http\Controllers\SystemLogController::class, 'clear'])
        ->middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)
        ->name('system.logs.clear');

    // ================================
    // VITALS MANAGEMENT
    // ================================

    Route::get('vitals', [VitalsController::class, 'index'])->name('vitals.index');
    Route::get('vitals/{visitId}', [VitalsController::class, 'show'])->name('vitals.show');
    Route::post('vitals/{visitId}', [VitalsController::class, 'store'])->name('vitals.store');
    Route::put('vitals/{vitalsId}', [VitalsController::class, 'update'])->name('vitals.update');
    Route::get('vitals-statistics', [VitalsController::class, 'statistics'])->name('vitals.statistics');
    // Vitals modal endpoints
    Route::get('vitals/visit/{visitId}/current', [VitalsController::class, 'getCurrentVitals'])->name('vitals.current');
    Route::get('vitals/visit/{visitId}/history', [VitalsController::class, 'getVitalsHistory'])->name('vitals.history');

    // ================================
    // MEDICATION SYSTEM
    // ================================

    // Core medication management (moved after medication.php include to avoid route conflicts)
    
    // Medication reports
    Route::get('/medications/reports/low-stock', [App\Http\Controllers\MedicationController::class, 'lowStock'])
        ->name('medications.low-stock');
    Route::get('/medications/reports/expired', [App\Http\Controllers\MedicationController::class, 'expired'])
        ->name('medications.expired');
    Route::get('/medications/reports/expiring-soon', [App\Http\Controllers\MedicationController::class, 'expiringSoon'])
        ->name('medications.expiring-soon');

    // Medication support data
    Route::resource('medication-frequencies', App\Http\Controllers\MedicationFrequencyController::class);

    // ================================
    // CASH SALES - MEDICATION
    // ================================

    Route::get('medication-cash-sales/get-pricing', [App\Http\Controllers\MedicationCashSaleController::class, 'getMedicationPricing'])
        ->name('medication-cash-sales.get-pricing');
    Route::resource('medication-cash-sales', App\Http\Controllers\MedicationCashSaleController::class);
    // Route alias for pharmacy cash sales (for compatibility with existing nav links)
    Route::get('pharmacy_cash_sales', [App\Http\Controllers\MedicationCashSaleController::class, 'index'])
        ->name('pharmacy-cash-sales.index');
    Route::post('medication-cash-sales/{medicationCashSale}/dispense', [App\Http\Controllers\MedicationCashSaleController::class, 'dispense'])
        ->name('medication-cash-sales.dispense');
    Route::post('medication-cash-sales/{medicationCashSale}/process-payment', [App\Http\Controllers\MedicationCashSaleController::class, 'processPayment'])
        ->name('medication-cash-sales.process-payment');
    Route::post('medication-cash-sales/{medicationCashSale}/cancel', [App\Http\Controllers\MedicationCashSaleController::class, 'cancel'])
        ->name('medication-cash-sales.cancel');
    Route::post('medication-cash-sales/{medicationCashSale}/cancel-paid', [App\Http\Controllers\MedicationCashSaleController::class, 'cancelPaid'])
        ->name('medication-cash-sales.cancel-paid');
    Route::post('medication-cash-sale-items/{item}/dispense', [App\Http\Controllers\MedicationCashSaleController::class, 'dispenseItem'])
        ->name('medication-cash-sales.dispense-item');
    Route::post('medication-cash-sale-items/{item}/cancel', [App\Http\Controllers\MedicationCashSaleController::class, 'cancelItem'])
        ->name('medication-cash-sales.cancel-item');
    Route::post('/medication-frequencies/{medicationFrequency}/toggle-status', [App\Http\Controllers\MedicationFrequencyController::class, 'toggleStatus'])
        ->name('medication-frequencies.toggle-status');
    
    Route::resource('medication-units', App\Http\Controllers\MedicationUnitController::class);
    Route::post('/medication-units/{medicationUnit}/toggle-status', [App\Http\Controllers\MedicationUnitController::class, 'toggleStatus'])
        ->name('medication-units.toggle-status');
    Route::get('/medication-units/api/base-units', [App\Http\Controllers\MedicationUnitController::class, 'getBaseUnits'])
        ->name('medication-units.api.base-units');
    Route::get('/medication-units/api/dispensing-units', [App\Http\Controllers\MedicationUnitController::class, 'getDispensingUnits'])
        ->name('medication-units.api.dispensing-units');
    
    Route::resource('administration-routes', App\Http\Controllers\AdministrationRouteController::class);
    Route::post('/administration-routes/{administrationRoute}/toggle-status', [App\Http\Controllers\AdministrationRouteController::class, 'toggleStatus'])
        ->name('administration-routes.toggle-status');

    // Prescription management
    Route::resource('prescriptions', App\Http\Controllers\PrescriptionController::class)->only(['show', 'edit', 'update', 'destroy']);
    Route::post('prescriptions/{prescription}/dispense', [App\Http\Controllers\PrescriptionController::class, 'dispense'])
        ->name('prescriptions.dispense');
    Route::patch('prescriptions/{prescription}/status', [App\Http\Controllers\PrescriptionController::class, 'updateStatus'])
        ->name('prescriptions.update-status');

    // ================================
    // PHARMACIST MANAGEMENT
    // ================================
    
    // Pharmacist dashboard and prescription management
    Route::get('/pharmacist/dashboard', [App\Http\Controllers\PharmacistController::class, 'dashboard'])
        ->name('pharmacist.dashboard');
    Route::get('/pharmacist/prescriptions', [App\Http\Controllers\PharmacistController::class, 'prescriptions'])
        ->name('pharmacist.prescriptions.index');
    Route::get('/pharmacist/prescriptions/visit/{visit}', [App\Http\Controllers\PharmacistController::class, 'showPrescription'])
        ->name('pharmacist.prescriptions.show');
    Route::post('/pharmacist/prescriptions/{prescription}/dispense', [App\Http\Controllers\PharmacistController::class, 'dispensePrescription'])
        ->name('pharmacist.prescriptions.dispense');
    Route::post('/pharmacist/prescriptions/{prescription}/unavailable', [App\Http\Controllers\PharmacistController::class, 'markUnavailable'])
        ->name('pharmacist.prescriptions.unavailable');
    Route::get('/pharmacist/data', [App\Http\Controllers\PharmacistController::class, 'getData'])
        ->name('pharmacist.data');
    Route::get('/pharmacist/requisitions/open', [App\Http\Controllers\PharmacistController::class, 'getOpenRequisitions'])
        ->name('pharmacist.requisitions.open');
    Route::post('/pharmacist/requisitions/{requisition}/add-item', [App\Http\Controllers\PharmacistController::class, 'addItemToRequisition'])
        ->name('pharmacist.requisitions.add-item');
    Route::post('/pharmacist/requisitions/new-with-item', [App\Http\Controllers\PharmacistController::class, 'createRequisitionWithItem'])
        ->name('pharmacist.requisitions.new-with-item');

    // ================================
    // STORE MANAGEMENT SYSTEM
    // ================================

    // Store categories
    Route::resource('store-categories', App\Http\Controllers\Store\StoreCategoryController::class);

    // Store locations
    Route::resource('store-locations', App\Http\Controllers\Store\StoreLocationController::class);
    Route::post('/store-locations/{location}/toggle-status', [App\Http\Controllers\Store\StoreLocationController::class, 'toggleStatus'])
        ->name('store-locations.toggle-status');
    Route::get('/store-locations/api/list', [App\Http\Controllers\Store\StoreLocationController::class, 'apiList'])
        ->name('store-locations.api.list');
    Route::get('/store-locations/api/tree', [App\Http\Controllers\Store\StoreLocationController::class, 'apiTree'])
        ->name('store-locations.api.tree');

    // Store locations stock management
    Route::resource('store-locations-stock', App\Http\Controllers\Store\StoreLocationStockController::class);
    Route::get('/store-locations-stock/{stock}/history', [App\Http\Controllers\Store\StoreLocationStockController::class, 'history'])
        ->name('store-locations-stock.history');
    Route::post('/store-locations-stock/{stock}/adjust', [App\Http\Controllers\Store\StoreLocationStockController::class, 'adjust'])
        ->name('store-locations-stock.adjust');

    // Store stock movements
    Route::resource('store-stock-movements', App\Http\Controllers\Store\StoreStockMovementController::class);
    Route::post('/store-stock-movements/{movement}/reverse', [App\Http\Controllers\Store\StoreStockMovementController::class, 'reverse'])
        ->name('store-stock-movements.reverse');
    Route::get('/store-stock-movements/export', [App\Http\Controllers\Store\StoreStockMovementController::class, 'export'])
        ->name('store-stock-movements.export');

    
    // Store reports index (unified reports dashboard)
    Route::get('/store/reports', function () {
        return redirect()->route('medications.reports.index');
    })->name('store.reports.index');

    // ================================
    // LAB PERSONNEL MANAGEMENT ROUTES
    // ================================
    
    // Lab Dashboard - Patient Visits with Lab Investigations
    Route::get('/lab/visits', [App\Http\Controllers\LabController::class, 'index'])
        ->name('lab.visits.index');
    
    // Lab Investigations for a specific visit
    Route::get('/lab/visits/{visitId}/investigations', [App\Http\Controllers\LabController::class, 'showVisitInvestigations'])
        ->name('lab.visits.investigations');
    
    // Lab Results Management
    Route::get('/lab/investigations/{investigationId}/results', [App\Http\Controllers\LabController::class, 'showResultForm'])
        ->name('lab.results.form');
    Route::post('/lab/investigations/{investigationId}/results', [App\Http\Controllers\LabController::class, 'storeResults'])
        ->name('lab.results.store');
    Route::get('/lab/investigations/{investigationId}/view-results', [App\Http\Controllers\LabController::class, 'viewInvestigationResults'])
        ->name('lab.investigations.view-results');
    Route::get('/lab/template-results/{resultId}', [App\Http\Controllers\LabController::class, 'viewTemplateResult'])
        ->name('lab.template-results.view');
    Route::get('/lab/template-results/{resultId}/modal', [App\Http\Controllers\LabController::class, 'viewTemplateResultModal'])
        ->name('lab.template-results.modal');
    Route::get('/lab/visit-results/{visitId}/modal', [App\Http\Controllers\LabController::class, 'viewVisitResultsModal'])
        ->name('lab.investigations.results-visit-modal');
    
    // Investigation Status Updates
    Route::patch('/lab/investigations/{investigationId}/status', [App\Http\Controllers\LabController::class, 'updateStatus'])
        ->name('lab.investigations.update-status');
    
    // Check stock for investigation
    Route::get('/lab/investigations/{investigationId}/check-stock', [App\Http\Controllers\LabController::class, 'checkInvestigationStock'])
        ->name('lab.investigations.check-stock');
    
    // Lab Statistics
    Route::get('/lab/statistics', [App\Http\Controllers\LabController::class, 'getStatistics'])
        ->name('lab.statistics');

    // ================================
    // CASHIER MANAGEMENT ROUTES
    // ================================

    // Cashier Dashboard - Patient Visits with Investigations and Prescriptions
    Route::get('/cashier', [App\Http\Controllers\CashierController::class, 'index'])
        ->name('cashier.index');
    
    // Investigation Management for Cashier
    Route::get('/cashier/visits/{visitId}/investigations', [App\Http\Controllers\CashierController::class, 'showInvestigations'])
        ->name('cashier.investigations.show');
    Route::match(['patch', 'post'], '/cashier/investigations/{investigationId}/payment', [App\Http\Controllers\CashierController::class, 'updateInvestigationPayment'])
        ->name('cashier.investigations.payment');
    Route::post('/cashier/investigations/bulk-update', [App\Http\Controllers\CashierController::class, 'bulkUpdateInvestigations'])
        ->name('cashier.investigations.bulk-update');
    
    // Prescription Management for Cashier
    Route::get('/cashier/visits/{visitId}/prescriptions', [App\Http\Controllers\CashierController::class, 'showPrescriptions'])
        ->name('cashier.prescriptions.show');
    Route::match(['patch', 'post'], '/cashier/prescriptions/{prescriptionId}/payment', [App\Http\Controllers\CashierController::class, 'updatePrescriptionPayment'])
        ->name('cashier.prescriptions.payment');
    Route::post('/cashier/prescriptions/bulk-update', [App\Http\Controllers\CashierController::class, 'bulkUpdatePrescriptions'])
        ->name('cashier.prescriptions.bulk-update');

    // ================================
    // FINANCIAL SYSTEM ROUTES
    // ================================

    // Financial Dashboard
    Route::get('/financial/dashboard', [App\Http\Controllers\FinancialDashboardController::class, 'index'])
        ->name('financial.dashboard');
    Route::get('/financial/data', [App\Http\Controllers\FinancialDashboardController::class, 'getData'])
        ->name('financial.data');

    // Financial Transactions
    Route::get('/financial/transactions', [App\Http\Controllers\FinancialTransactionController::class, 'index'])
        ->name('financial.transactions.index');
    Route::get('/financial/transactions/create', [App\Http\Controllers\FinancialTransactionController::class, 'create'])
        ->name('financial.transactions.create');
    Route::get('/financial/transactions/export', [App\Http\Controllers\FinancialTransactionController::class, 'export'])
        ->name('financial.transactions.export');
    Route::post('/financial/transactions', [App\Http\Controllers\FinancialTransactionController::class, 'store'])
        ->name('financial.transactions.store');
    Route::get('/financial/transactions/{transaction}', [App\Http\Controllers\FinancialTransactionController::class, 'show'])
        ->name('financial.transactions.show');
    Route::get('/financial/transactions/{transaction}/edit', [App\Http\Controllers\FinancialTransactionController::class, 'edit'])
        ->name('financial.transactions.edit');
    Route::put('/financial/transactions/{transaction}', [App\Http\Controllers\FinancialTransactionController::class, 'update'])
        ->name('financial.transactions.update');
    Route::delete('/financial/transactions/{transaction}', [App\Http\Controllers\FinancialTransactionController::class, 'destroy'])
        ->name('financial.transactions.destroy');
    Route::post('/financial/transactions/{transaction}/approve', [App\Http\Controllers\FinancialTransactionController::class, 'approve'])
        ->name('financial.transactions.approve');
    Route::post('/financial/transactions/{transaction}/cancel', [App\Http\Controllers\FinancialTransactionController::class, 'cancel'])
        ->name('financial.transactions.cancel');

    // Receipt Management
    Route::get('/financial/receipts', [App\Http\Controllers\Financial\ReceiptController::class, 'index'])
        ->name('financial.receipts.index');
    Route::get('/financial/receipts/{transaction}/generate', [App\Http\Controllers\Financial\ReceiptController::class, 'generateReceipt'])
        ->name('financial.receipts.generate');
    Route::get('/financial/receipts/{transaction}/preview', [App\Http\Controllers\Financial\ReceiptController::class, 'previewReceipt'])
        ->name('financial.receipts.preview');
    Route::get('/financial/receipts/{transaction}/print', [App\Http\Controllers\Financial\ReceiptController::class, 'printReceipt'])
        ->name('financial.receipts.print');
    Route::post('/financial/receipts/{transaction}/email', [App\Http\Controllers\Financial\ReceiptController::class, 'emailReceipt'])
        ->name('financial.receipts.email');
    Route::get('/financial/receipts/patient/{patient}/statement', [App\Http\Controllers\Financial\ReceiptController::class, 'generatePatientStatement'])
        ->name('financial.receipts.patient.statement');
    Route::get('/financial/receipts/daily-summary', [App\Http\Controllers\Financial\ReceiptController::class, 'viewDailySummary'])
        ->name('financial.receipts.daily.summary');
    Route::post('/financial/receipts/daily-summary', [App\Http\Controllers\Financial\ReceiptController::class, 'generateDailySummary'])
        ->name('financial.receipts.daily.summary.post');
    Route::post('/financial/receipts/bulk-generate', [App\Http\Controllers\Financial\ReceiptController::class, 'bulkGenerateReceipts'])
        ->name('financial.receipts.bulk.generate');
    Route::get('/api/patients/{patient}/transactions', [App\Http\Controllers\Financial\ReceiptController::class, 'getPatientTransactions'])
        ->name('api.patients.transactions');

    // ================================
    // MEDICATION MANAGEMENT SYSTEM
    // ================================
    
    // Include medication management routes (includes dashboard, stock, consumption, reconciliation, etc.)
    require __DIR__.'/medication.php';
    
    // Core medication resource routes
    Route::get('/medications/pricing', [App\Http\Controllers\MedicationController::class, 'pricing'])->name('medications.pricing');
    Route::patch('/medications/{medication}/price', [App\Http\Controllers\MedicationController::class, 'updatePrice'])->name('medications.update-price');
    Route::get('/medications/tracer', [App\Http\Controllers\MedicationController::class, 'tracerIndex'])->name('medications.tracer');
    Route::patch('/medications/{medication}/tracer', [App\Http\Controllers\MedicationController::class, 'toggleTracer'])->name('medications.toggle-tracer');
    Route::resource('medications', App\Http\Controllers\MedicationController::class);
    Route::post('/medications/{medication}/toggle-status', [App\Http\Controllers\MedicationController::class, 'toggleStatus'])
        ->name('medications.toggle-status');

// ================================
// API ROUTES (PUBLIC/AJAX)
// ================================

// Medical services API
Route::get('/api/medical-services/search', [ApiClinicalController::class, 'searchMedicalServices']);
Route::get('/api/medical-services/{serviceId}/form-check', [ApiClinicalController::class, 'checkServiceFormRequirements']);

// ICD10 API
Route::get('/api/icd10/search', [App\Http\Controllers\Icd10Controller::class, 'search']);

// MSD code library API (medication item-code search/attach)
Route::get('/api/msd-codes/search', [App\Http\Controllers\MsdCodeController::class, 'search']);

// Lab code library API (LOINC / SNOMED search/attach)
Route::get('/api/lab-codes/search', [App\Http\Controllers\LabCodeController::class, 'search']);

// Medications API
Route::get('/api/medications/search', [App\Http\Controllers\MedicationSearchController::class, 'search']);

// ICD10 web UI for assigning/editing mtuha diagnosis mappings
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsVerified::class, 'facility_setup'])->group(function () {
    Route::get('/icd10', [App\Http\Controllers\Icd10Controller::class, 'index'])->name('icd10.index');
    Route::patch('/icd10/{id}', [App\Http\Controllers\Icd10Controller::class, 'update'])->name('icd10.update');

    // Coding-standard libraries: MSD item codes, LOINC / SNOMED CT lab codes
    Route::get('/msd-codes', [App\Http\Controllers\MsdCodeController::class, 'index'])->name('msd-codes.index');
    Route::get('/lab-codes', [App\Http\Controllers\LabCodeController::class, 'index'])->name('lab-codes.index');

    // Phase 6.2b — Sync conflicts review queue
    Route::get('/admin/sync/conflicts', [App\Http\Controllers\Admin\SyncConflictController::class, 'index'])->name('admin.sync.conflicts.index');
    Route::post('/admin/sync/conflicts/{id}/resolve', [App\Http\Controllers\Admin\SyncConflictController::class, 'resolve'])->name('admin.sync.conflicts.resolve');
});

// Medication API (auth required)
    Route::get('/medications/api/list', [App\Http\Controllers\MedicationController::class, 'apiList'])
        ->name('medications.api.list');

// Medication API routes - needed for AJAX calls within authenticated pages
Route::get('/medications/stock/items/medications', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getMedications'])
    ->name('medications.stock.items.medications.public');
Route::get('/medications/stock/items/{type}', [App\Http\Controllers\Store\GoodsReceivedNoteController::class, 'getItemsByType'])
    ->name('medications.stock.items.by-type.public');

// Include store requisitions routes
require __DIR__.'/requisitions.php';

// CDS Testing routes
require __DIR__.'/test-cds.php';

// Prescription management routes (auth required) - Already handled by resource route above
// Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureUserIsVerified::class])->group(function () {
//     Route::get('/prescriptions/{prescriptionId}/edit', [App\Http\Controllers\PrescriptionController::class, 'edit'])
//         ->name('prescriptions.edit');
//     Route::patch('/prescriptions/{prescriptionId}', [App\Http\Controllers\PrescriptionController::class, 'update'])
//         ->name('prescriptions.update');
//     Route::delete('/prescriptions/{prescriptionId}', [App\Http\Controllers\PrescriptionController::class, 'destroy'])
//         ->name('prescriptions.destroy');
// });

// CDS Administration Routes
Route::middleware(['auth', 'verified', 'facility_setup'])->prefix('admin/cds')->name('admin.cds.')->group(function () {
    // CDS Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\CdsRuleController::class, 'dashboard'])
        ->name('dashboard');
    
    // Test Patients (dummy data for CDS rule testing)
    Route::get('/test-patients', [App\Http\Controllers\Admin\CdsTestPatientsController::class, 'index'])
        ->name('test-patients.index');
    Route::post('/test-patients/reseed', [App\Http\Controllers\Admin\CdsTestPatientsController::class, 'reseed'])
        ->name('test-patients.reseed');
    Route::post('/test-patients/{patient}', [App\Http\Controllers\Admin\CdsTestPatientsController::class, 'update'])
        ->name('test-patients.update');

    // Lab Critical Value rule builder: parameter list for a picked medical service
    Route::get('/rules/lab-parameters', [App\Http\Controllers\Admin\CdsRuleController::class, 'labParameters'])
        ->name('rules.lab-parameters');

    // CDS Rules Management
    Route::resource('rules', App\Http\Controllers\Admin\CdsRuleController::class);
    
    // Additional Rule Actions
    Route::get('/rules/{rule}/test', [App\Http\Controllers\Admin\CdsRuleController::class, 'test'])
        ->name('rules.test');
    Route::post('/rules/{rule}/run-test', [App\Http\Controllers\Admin\CdsRuleController::class, 'testRule'])
        ->name('rules.run-test');
    Route::post('/rules/{rule}/toggle', [App\Http\Controllers\Admin\CdsRuleController::class, 'toggle'])
        ->name('rules.toggle');
    Route::post('/rules/{rule}/duplicate', [App\Http\Controllers\Admin\CdsRuleController::class, 'duplicate'])
        ->name('rules.duplicate');
    Route::get('/rules/{rule}/export', [App\Http\Controllers\Admin\CdsRuleController::class, 'export'])
        ->name('rules.export');
    
    // Medication Policies / Dosage Limits
    Route::get('/medication-policies', [App\Http\Controllers\Admin\CdsRuleController::class, 'medicationPoliciesIndex'])
        ->name('medication-policies.index');
    Route::post('/medication-policies/limits', [App\Http\Controllers\Admin\CdsRuleController::class, 'dosageLimitsStore'])
        ->name('dosage-limits.store');
    Route::put('/medication-policies/limits/{limit}', [App\Http\Controllers\Admin\CdsRuleController::class, 'dosageLimitsUpdate'])
        ->name('dosage-limits.update');
    Route::delete('/medication-policies/limits/{limit}', [App\Http\Controllers\Admin\CdsRuleController::class, 'dosageLimitsDestroy'])
        ->name('dosage-limits.destroy');
    // Search helpers for dosage limit form
    Route::get('/medication-policies/search/drug-classes', [App\Http\Controllers\Admin\CdsRuleController::class, 'searchDrugClasses'])
        ->name('dosage-limits.search-drug-classes');
    Route::get('/medication-policies/search/medications', [App\Http\Controllers\Admin\CdsRuleController::class, 'searchMedicationsSimple'])
        ->name('dosage-limits.search-medications');

    // Medication Safety sub-pages
    Route::get('/drug-interactions', [App\Http\Controllers\Admin\CdsRuleController::class, 'drugInteractionsIndex'])
        ->name('drug-interactions.index');
    Route::get('/allergy-checks', [App\Http\Controllers\Admin\CdsRuleController::class, 'allergyChecksIndex'])
        ->name('allergy-checks.index');
    Route::get('/dose-range-rules', [App\Http\Controllers\Admin\CdsRuleController::class, 'doseRangeRulesIndex'])
        ->name('dose-range-rules.index');
    
    // Rule Categories
    Route::get('/categories', [App\Http\Controllers\Admin\CdsRuleController::class, 'categoriesIndex'])
        ->name('categories.index');
    Route::get('/categories/{category}', [App\Http\Controllers\Admin\CdsRuleController::class, 'categoriesShow'])
        ->name('categories.show');
    
    // Rule Types
    Route::get('/types', [App\Http\Controllers\Admin\CdsRuleController::class, 'typesIndex'])
        ->name('types.index');
    Route::get('/types/{ruleType}', [App\Http\Controllers\Admin\CdsRuleController::class, 'typesShow'])
        ->name('types.show');
});

});

// ================================
// HUMAN RESOURCES (HR) ROUTES
// ================================

Route::middleware(['auth', 'hr', 'facility_setup'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', [HrDashboardController::class, 'index'])->name('dashboard');

    Route::resource('employees', HrEmployeeController::class)->except(['destroy']);
    Route::post('employees/{employee}/toggle-status', [HrEmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

    Route::resource('employees.salary-components', EmployeeSalaryComponentController::class)
        ->shallow()->except(['index', 'show', 'create', 'edit']);

    Route::resource('salary-payments', SalaryPaymentController::class)->except(['destroy']);
    Route::post('salary-payments/generate', [SalaryPaymentController::class, 'generate'])->name('salary-payments.generate');
    Route::post('salary-payments/{salaryPayment}/recalculate-paye', [SalaryPaymentController::class, 'recalculatePaye'])->name('salary-payments.recalculate-paye');
    Route::post('salary-payments/{salaryPayment}/approve', [SalaryPaymentController::class, 'approve'])->name('salary-payments.approve');
    Route::post('salary-payments/{salaryPayment}/pay', [SalaryPaymentController::class, 'pay'])->name('salary-payments.pay');
    Route::post('salary-payments/{salaryPayment}/cancel', [SalaryPaymentController::class, 'cancel'])->name('salary-payments.cancel');
    Route::get('salary-payments/{salaryPayment}/payslip', [SalaryPaymentController::class, 'payslip'])->name('salary-payments.payslip');

    // PAYE band settings - admin only (tax configuration)
    Route::middleware('admin')->prefix('settings')->name('settings.')->group(function () {
        Route::resource('paye-bands', PayeTaxBandController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';