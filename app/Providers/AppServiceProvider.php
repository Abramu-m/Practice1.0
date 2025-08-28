<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\PatientVisit;
use App\Models\Investigation;
use App\Models\Prescription;
use App\Models\ConsultationFee;
use App\Models\Consultation;
use App\Models\SystemicExamination;
use App\Models\VitalSigns;
use App\Models\IcdDiagnosis;
use App\Models\PastMedicalHistory;
use App\Models\StoreLocation;
use App\Observers\PatientVisitObserver;
use App\Observers\InvestigationFinancialObserver;
use App\Observers\MedicationDispensingObserver;
use App\Observers\ConsultationFeeObserver;
use App\Observers\ConsultationStatusObserver;
use App\Observers\PrescriptionStatusObserver;
use App\Observers\InvestigationStatusObserver;
use App\Observers\SystemicExaminationStatusObserver;
use App\Observers\VitalSignsStatusObserver;
use App\Observers\IcdDiagnosisStatusObserver;
use App\Observers\PastMedicalHistoryStatusObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route model binding for store locations
        Route::model('store_location', StoreLocation::class);
        
        // Register observers for automatic financial transaction generation
        PatientVisit::observe(PatientVisitObserver::class);
        Investigation::observe(InvestigationFinancialObserver::class);
        Prescription::observe(MedicationDispensingObserver::class);
        ConsultationFee::observe(ConsultationFeeObserver::class);
        
        // Register observers for automatic patient visit status updates
        // These observers change visit status from "waiting" to "in treatment" when doctors save consultation data
        Consultation::observe(ConsultationStatusObserver::class);
        Prescription::observe(PrescriptionStatusObserver::class);
        Investigation::observe(InvestigationStatusObserver::class);
        SystemicExamination::observe(SystemicExaminationStatusObserver::class);
        IcdDiagnosis::observe(IcdDiagnosisStatusObserver::class);
        PastMedicalHistory::observe(PastMedicalHistoryStatusObserver::class);
        
        // VitalSigns observer - explicitly does NOT trigger status changes (nurses/triage vitals don't count as treatment)
        VitalSigns::observe(VitalSignsStatusObserver::class);
    }
}
