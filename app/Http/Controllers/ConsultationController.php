<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\VitalSigns;
use App\Models\SystemicExamination;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\Medication;
use App\Models\MedicalService;
use App\Models\AdministrationRoute;
use App\Models\MedicationFrequency;
use App\Models\ServiceCategory;
use App\Models\PastMedicalHistory;
use App\Models\IcdDiagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\MedicationPrescribed;
use App\Services\CDS\CdsAlertService;
use Illuminate\Support\Str;

class ConsultationController extends Controller
{
    /**
     * Display consultation interface for a patient visit
     */
    public function show($visitId)
    {
        $visit = PatientVisit::with([
            'patientInfo.patientCategory',
            'patientInfo.pastMedicalHistory',
            'patientInfo.allergies',
            'doctorInfo.user',
            'doctorInfo.designationInfo',
            'visitType'
        ])->findOrFail($visitId);

        // Security check: Only allow access if user is admin or the assigned doctor
        $user = Auth::user();
        if (!($user->is_admin || $user->is_super)) {
            if ($user->role === 'doctor' && $user->doctor) {
                $currentDoctorId = $user->doctor->doctor_id ?? null;
                if ($currentDoctorId != $visit->doctor) {
                    return redirect()->route('patient_visits.index')
                        ->with('error', 'You can only access consultations for patients assigned to you.');
                }
            } else {
                return redirect()->route('patient_visits.index')
                    ->with('error', 'You do not have permission to access this consultation.');
            }
        }

        // Check if visit is in treatment status (status 1) or waiting (status 0)
        if (!in_array($visit->visit_status, [0, 1])) {
            return redirect()->route('patient_visits.edit', $visitId)
                ->with('error', 'Patient must be waiting or in treatment status for consultation.');
        }

        // Note: Visit status is automatically updated to "in treatment" by ConsultationStatusObserver
        // when doctor enters meaningful consultation data

        // Get or create consultation for this visit
        $consultation = Consultation::where('patient_id', $visit->patient)
            ->where('visit_id', $visit->id)
            ->first();

        if (!$consultation) {
            $consultation = Consultation::create([
                'patient_id' => $visit->patient,
                'visit_id' => $visit->id,
                'doctor_id' => $visit->doctor,
                'consultation_date' => $visit->visit_date,
                'status' => 'active'
            ]);
        } 

        // Get latest vital signs
        $vitals = $consultation->vitals()->latest('id')->first();

        // Get systemic examinations
        $examinations = SystemicExamination::where('consultation_id', $consultation->id)
            ->where('status', 'active')
            ->get();

        // Get prescriptions
        $prescriptions = $consultation->prescriptions()
            ->with(['medication', 'administrationRoute', 'frequency'])
            ->where('status', '!=', 'cancelled')
            ->get();

        // Get investigations - include both consultation and visit-level investigations
        $investigations = Investigation::with(['medicalService.serviceCategory', 'medicalService.currentPricing' => function($query) use ($visit) {
                $patientCategoryId = $visit->patientInfo->patient_category_id ?? null;
                if ($patientCategoryId) {
                    $query->where('patient_category_id', $patientCategoryId);
                }
            }, 'patient', 'doctor', 'results', 'templateResults' => function($query) {
                $query->with('reportedBy')->orderBy('reported_at', 'desc');
            }])
            ->where('patient_id', $visit->patient)
            ->where(function($query) use ($consultation, $visit) {
                // Include investigations linked to this consultation
                $query->where('consultation_id', $consultation->id)
                      // OR include investigations linked to this visit but without a consultation (lab-only)
                      ->orWhere(function($q) use ($visit) {
                          $q->where('visit_id', $visit->id)
                            ->whereNull('consultation_id');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get dropdown data using improved models
        // Note: medications, services, and icd10Codes now load via AJAX to improve performance
        $routes = AdministrationRoute::where('is_active', true)->orderBy('route_name')->get();
        $frequencies = MedicationFrequency::where('is_active', true)->orderBy('frequency_name')->get();
        $serviceCategories = ServiceCategory::active()->orderBy('name')->get();
        
        // Get patient's past medical history
        $pastMedicalHistory = $visit->patientInfo->pastMedicalHistory;

        // Pre-compute drug allergy summary (business logic moved from Blade)
        $activeDrugAllergies = optional($visit->patientInfo->allergies)->where('is_active', true) ?? collect();
        $drugAllergyCount = $activeDrugAllergies->count();
        $drugAllergyOverflow = 0;
        $drugAllergySummary = null; // null signifies none
        if ($drugAllergyCount > 0) {
            $displayAllergies = $activeDrugAllergies->sortBy('substance_name')->values();
            $drugAllergyOverflow = max(0, $drugAllergyCount - 4);
            $drugAllergySummary = $displayAllergies->take(4)->map(function ($a) {
                return $a->substance_name . ($a->severity ? '(' . ucfirst($a->severity) . ')' : '');
            })->implode(', ');
        }
        $otherAllergiesRaw = $pastMedicalHistory->allergies ?? null;
        $otherAllergiesSummary = $otherAllergiesRaw ? Str::limit($otherAllergiesRaw, 30) : null;

        // Get ICD diagnoses for the consultation
        $icd_diagnoses = $consultation->icdDiagnoses;

        // Get test results from investigations with results
        $testResults = collect();
        
        // Add results from investigations
        foreach ($investigations as $investigation) {
            if ($investigation->templateResults && $investigation->templateResults->count() > 0) {
                // Get the latest final result, or the most recent result if no final result exists
                $latestResult = $investigation->templateResults
                    ->where('form_status', 'final')
                    ->sortByDesc('reported_at')
                    ->first();
                
                if (!$latestResult) {
                    $latestResult = $investigation->templateResults
                        ->sortByDesc('reported_at')
                        ->first();
                }
                
                if ($latestResult) {
                    $testResults->push((object)[
                        'investigation_id' => $investigation->id,
                        'investigation' => $investigation,
                        'template_result' => $latestResult,
                        'test_name' => $investigation->medicalService->name ?? 'Unknown Test',
                        'template_name' => $latestResult->template_name,
                        'form_data' => $latestResult->form_data,
                        'form_status' => $latestResult->form_status,
                        'reported_at' => $latestResult->reported_at,
                        'reported_by' => $latestResult->reportedBy->name ?? 'Unknown',
                        'is_simple' => in_array($latestResult->template_name, ['simple', 'blood_count', 'chemistry']),
                        'is_manual' => false
                    ]);
                }
            }
        }
        
        // Add manual test results
        $manualResults = \App\Models\InvestigationTemplateResult::with('reportedBy')
            ->where('investigation_id', null)
            ->whereJsonContains('form_data->consultation_id', (string)$consultation->id)
            ->orderBy('reported_at', 'desc')
            ->get();
            
        foreach ($manualResults as $manualResult) {
            $testResults->push((object)[
                'investigation_id' => null,
                'investigation' => null,
                'template_result' => $manualResult,
                'test_name' => $manualResult->form_data['test_name'] ?? 'Manual Test Result',
                'template_name' => $manualResult->template_name,
                'form_data' => $manualResult->form_data,
                'form_status' => $manualResult->form_status,
                'reported_at' => $manualResult->reported_at,
                'reported_by' => $manualResult->reportedBy->name ?? 'Unknown',
                'is_simple' => true, // Manual results are always displayed as simple
                'is_manual' => true
            ]);
        }

        // Load any open CDS alerts for this visit
        $cdsAlerts = app(\App\Services\CDS\CdsAlertService::class)->forVisit($visit->id);

        return view('consultations.show', compact(
            'visit',
            'consultation', 
            'vitals',
            'examinations',
            'prescriptions',
            'investigations',
            'routes', 
            'frequencies',
            'serviceCategories',
            'pastMedicalHistory',
            'icd_diagnoses',
            'testResults',
            'cdsAlerts',
            'activeDrugAllergies',
            'drugAllergySummary',
            'drugAllergyOverflow',
            'otherAllergiesSummary'
        ));
    }

    /**
     * Update consultation details
     */
    public function update(Request $request, $consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);

        $request->validate([
            'history_of_present_illness' => 'nullable|string',
            'provisional_diagnosis' => 'nullable|string',
            'final_diagnosis' => 'nullable|string',
            'remarks' => 'nullable|string',
            'followup_date' => 'nullable|date|after:today',
            'followup_instructions' => 'nullable|string'
        ]);

        // Update consultation details, only the fields that are provided
        $fields = [
            'history_of_present_illness',
            'provisional_diagnosis',
            'final_diagnosis',
            'remarks',
            'followup_date',
            'followup_instructions'
        ];

        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
            $data[$field] = $request->$field;
            }
        }

        if (!empty($data)) {
            $consultation->update($data);
        }
        
        //get the updated consultation data
        $consultation->load(['patient', 'doctor', 'visit']);

        return response()->json([
            'success' => true,
            'message' => 'Consultation updated successfully.',
            'consultation' => $consultation
        ]);
    }

    /**
     * Update diagnosis details
     */
    public function updateDiagnosis(Request $request, $consultationId)
    {
        $request->validate([
            'provisional_diagnosis' => 'nullable|string',
            'final_diagnosis' => 'nullable|string',
        ]);
        
        $consultation = Consultation::findOrFail($consultationId);
        
        $consultation->update([
            'provisional_diagnosis' => $request->provisional_diagnosis,
            'final_diagnosis' => $request->final_diagnosis,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Diagnosis updated successfully.',
            'data' => $consultation
        ]);
    }

    /**
     * Store vital signs
     */
    public function storeVitals(Request $request, $consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);

        $request->validate([
            'pulse_rate' => 'nullable|numeric|min:30|max:200',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|numeric|min:10|max:60',
            'weight' => 'nullable|numeric|min:1|max:300',
            'height' => 'nullable|numeric|min:30|max:250',
            'systolic_pressure' => 'nullable|numeric|min:60|max:250',
            'diastolic_pressure' => 'nullable|numeric|min:30|max:150',
            'oxygen_saturation' => 'nullable|numeric|min:70|max:100',
            'muac' => 'nullable|string|max:10',
            'ofc' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Using improved VitalSigns model - BMI calculated automatically
        if (!$consultation || !$consultation->id) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found or invalid. Please refresh the page and try again.'
            ], 400);
        }
        
        $vitals = VitalSigns::create([
            'consultation_id' => $consultation->id,
            'visit_id' => $consultation->visit_id,
            'patient_id' => $consultation->patient_id,
            'pulse_rate' => $request->pulse_rate,
            'temperature' => $request->temperature,
            'respiratory_rate' => $request->respiratory_rate,
            'weight' => $request->weight,
            'height' => $request->height,
            'systolic_bp' => $request->systolic_pressure,
            'diastolic_bp' => $request->diastolic_pressure,
            'oxygen_saturation' => $request->oxygen_saturation,
            'muac' => $request->muac,
            'ofc' => $request->ofc,
            'notes' => $request->notes,
            'recorded_by' => Auth::id(),
            'recorded_at' => now(),
            'updated_by' => Auth::id(),
            'status' => true
        ]);

        // Update the visit's vital_status to indicate vitals have been taken
        $consultation->visit->update([
            'vital_status' => 1,
            'vitals_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vital signs recorded successfully.',
            'vitals' => $vitals,
            'bmi' => $vitals->bmi,
            'bmi_category' => $vitals->bmi_category,
            'blood_pressure_category' => $vitals->blood_pressure_category
        ]);
    }

    /**
     * Store quick vitals for a consultation
     */
    public function storeQuickVitals(Request $request, $consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        $visit = $consultation->visit;

        // Check if visit exists
        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found for this consultation.'
            ], 400);
        }

        $request->validate([
            'systolic_bp' => 'nullable|numeric|min:0|max:300',
            'diastolic_bp' => 'nullable|numeric|min:0|max:200',
            'pulse_rate' => 'nullable|numeric|min:0|max:200',
            'temperature' => 'nullable|numeric|min:90|max:110',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100'
        ]);
        if ($visit->vitalSigns()->exists()) {
            // Update existing vitals
            $vitals = $visit->vitalSigns()->latest('created_at')->first();
            $vitals->update([
                'systolic_bp' => $request->systolic_bp,
                'diastolic_bp' => $request->diastolic_bp,
                'pulse_rate' => $request->pulse_rate,
                'temperature' => $request->temperature,
                'oxygen_saturation' => $request->oxygen_saturation,
                'recorded_by' => Auth::id(),
                'recorded_at' => now()
            ]);
        }else{
            // Create new vitals
            $vitals = VitalSigns::create([
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient,
                'consultation_id' => $visit->consultation->id,
                'systolic_bp' => $request->systolic_bp,
                'diastolic_bp' => $request->diastolic_bp,
                'pulse_rate' => $request->pulse_rate,
                'temperature' => $request->temperature,
                'oxygen_saturation' => $request->oxygen_saturation,
                'recorded_by' => Auth::id(),
                'recorded_at' => now()
            ]);
        }
        
        // Update the visit's vital_status to indicate vitals have been taken
        $visit->update([
            'vital_status' => 1,
            'vitals_at' => now()
        ]);
        // get the latest vitals
        $vitals = VitalSigns::where('visit_id', $visit->id)->latest('created_at')->first();
        
        //Return the created or updated vitals as JSON, to be used in the frontend
        return response()->json([
            'success' => true,
            'message' => 'Quick vitals recorded successfully.',
            'vitals' => $vitals
        ]);
    }

    /**
     * Discharge a patient (update visit status to discharged)
     */
    public function discharge(Request $request, $consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        $visit = $consultation->visit;

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found for this consultation.'
            ], 400);
        }

        // Update visit status to discharged
        $visit->update([
            'visit_status' => 2, // Discharged
            'resulted_at' => now()
        ]);

        // You might also want to update the consultation status
        $consultation->update([
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient discharged successfully.',
            'visit' => $visit
        ]);
    }

    /**
     * Get examinations for a visit (for modal display)
     */
    public function getExaminationsByVisit($visitId)
    {
        $visit = PatientVisit::findOrFail($visitId);
        
        $examinations = SystemicExamination::where('visit_id', $visitId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'examinations' => $examinations
        ]);
    }

    /**
     * Store examination by visit ID (for modal)
     */
    public function storeExaminationByVisit(Request $request, $visitId)
    {
        $visit = PatientVisit::findOrFail($visitId);
        
        // Get consultation for this visit
        $consultation = Consultation::where('visit_id', $visitId)->firstOrFail();

        $request->validate([
            'examination_type' => 'required|string',
            'general_findings' => 'nullable|string',
            'cardiovascular_system' => 'nullable|string',
            'respiratory_system' => 'nullable|string',
            'gastrointestinal_system' => 'nullable|string',
            'nervous_system' => 'nullable|string',
            'musculoskeletal_system' => 'nullable|string',
            'genitourinary_system' => 'nullable|string',
            'endocrine_system' => 'nullable|string',
            'skin_examination' => 'nullable|string',
            'psychiatric_assessment' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $examination = SystemicExamination::create([
            'consultation_id' => $consultation->id,
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient,
            'examination_type' => $request->examination_type,
            'general_findings' => $request->general_findings,
            'cardiovascular_system' => $request->cardiovascular_system,
            'respiratory_system' => $request->respiratory_system,
            'gastrointestinal_system' => $request->gastrointestinal_system,
            'nervous_system' => $request->nervous_system,
            'musculoskeletal_system' => $request->musculoskeletal_system,
            'genitourinary_system' => $request->genitourinary_system,
            'endocrine_system' => $request->endocrine_system,
            'skin_examination' => $request->skin_examination,
            'psychiatric_assessment' => $request->psychiatric_assessment,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Systemic examination recorded successfully.',
            'examination' => $examination
        ]);
    }

    /**
     * Store systemic examination findings
     */
    public function storeExamination(Request $request, $consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        $visit = $consultation->visit;

        // Check if visit exists
        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found for this consultation.'
            ], 400);
        }

        $request->validate([
            'examination_type' => 'required|string',
            'general_findings' => 'nullable|string',
            'cardiovascular_system' => 'nullable|string',
            'respiratory_system' => 'nullable|string',
            'gastrointestinal_system' => 'nullable|string',
            'nervous_system' => 'nullable|string',
            'musculoskeletal_system' => 'nullable|string',
            'genitourinary_system' => 'nullable|string',
            'endocrine_system' => 'nullable|string',
            'skin_examination' => 'nullable|string',
            'psychiatric_assessment' => 'nullable|string'
        ]);

        $examination = SystemicExamination::create([
            'consultation_id' => $consultation->id,
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient,
            'examination_type' => $request->examination_type,
            'general_findings' => $request->general_findings,
            'cardiovascular_system' => $request->cardiovascular_system,
            'respiratory_system' => $request->respiratory_system,
            'gastrointestinal_system' => $request->gastrointestinal_system,
            'nervous_system' => $request->nervous_system,
            'musculoskeletal_system' => $request->musculoskeletal_system,
            'genitourinary_system' => $request->genitourinary_system,
            'endocrine_system' => $request->endocrine_system,
            'skin_examination' => $request->skin_examination,
            'psychiatric_assessment' => $request->psychiatric_assessment,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Systemic examination findings recorded successfully.',
            'examination' => $examination
        ]);
    }

    /**
     * Get systemic examination for editing
     */
    public function getExamination($examinationId)
    {
        $examination = SystemicExamination::findOrFail($examinationId);

        return response()->json([
            'success' => true,
            'examination' => $examination
        ]);
    }

    /**
     * Update systemic examination
     */
    public function updateExamination(Request $request, $examinationId)
    {
        $examination = SystemicExamination::findOrFail($examinationId);

        $request->validate([
            'examination_type' => 'required|string',
            'general_findings' => 'nullable|string',
            'cardiovascular_system' => 'nullable|string',
            'respiratory_system' => 'nullable|string',
            'gastrointestinal_system' => 'nullable|string',
            'nervous_system' => 'nullable|string',
            'musculoskeletal_system' => 'nullable|string',
            'genitourinary_system' => 'nullable|string',
            'endocrine_system' => 'nullable|string',
            'skin_examination' => 'nullable|string',
            'psychiatric_assessment' => 'nullable|string'
        ]);

        $examination->update([
            'examination_type' => $request->examination_type,
            'general_findings' => $request->general_findings,
            'cardiovascular_system' => $request->cardiovascular_system,
            'respiratory_system' => $request->respiratory_system,
            'gastrointestinal_system' => $request->gastrointestinal_system,
            'nervous_system' => $request->nervous_system,
            'musculoskeletal_system' => $request->musculoskeletal_system,
            'genitourinary_system' => $request->genitourinary_system,
            'endocrine_system' => $request->endocrine_system,
            'skin_examination' => $request->skin_examination,
            'psychiatric_assessment' => $request->psychiatric_assessment,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Systemic examination updated successfully.',
            'examination' => $examination->fresh()
        ]);
    }

    /**
     * Delete a systemic examination
     */
    public function deleteExamination(Request $request, $examinationId)
    {
        $examination = SystemicExamination::findOrFail($examinationId);

        // Soft delete if model uses soft deletes, otherwise perform delete
        try {
            $examination->delete();
            return response()->json([
                'success' => true,
                'message' => 'Systemic examination deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete systemic examination: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete systemic examination.'
            ], 500);
        }
    }

    /**
     * Return examinations partial for a consultation (AJAX)
     */
    public function getExaminationsPartial($consultationId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            $examinations = SystemicExamination::where('consultation_id', $consultation->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $html = view('consultations.partials.examinations', compact('examinations'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('Error fetching examinations partial: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load examinations'], 500);
        }
    }

    /**
     * Store prescription with clinical decision support
     */
    public function storePrescription(Request $request, $consultationId)
    {
        // Try to find by consultation ID first
        $consultation = Consultation::find($consultationId);
        
        // If not found, try to find by visit ID
        if (!$consultation) {
            $consultation = Consultation::where('visit_id', $consultationId)->firstOrFail();
        }

        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'dosage' => 'required|string|max:100',
            'administration_route_id' => 'required|exists:administration_routes,id',
            'frequency_id' => 'required|exists:medication_frequencies,id',
            'duration_days' => 'required|numeric|min:1|max:365',
            'quantity' => 'required|numeric|min:0.1',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'override_alerts' => 'nullable|boolean',
            'override_reason' => 'nullable|string'
        ]);

        $medication = Medication::findOrFail($request->medication_id);
        
        // Get patient's visit and category for pricing
        $visit = $consultation->visit;
        $patientCategoryId = $visit->patientInfo->patient_category_id ?? $visit->visit_category;
        
        // Get current pricing for this medication and patient category
        $currentPricing = \App\Models\MedicationPricing::where('medication_id', $request->medication_id)
            ->where('patient_category_id', $patientCategoryId)
            ->where('is_active', true)
            ->current()
            ->first();
        
        // Use pricing if available, otherwise fall back to medication unit_price
        $unitPrice = $currentPricing ? $currentPricing->selling_price : ($medication->unit_price ?? 0);

        // Using improved Prescription model - total price calculated automatically
        $prescription = Prescription::create([
            'patient_id' => $consultation->patient_id,
            'consultation_id' => $consultation->id,
            'doctor_id' => $consultation->doctor_id,
            'medication_id' => $request->medication_id,
            'dosage' => $request->dosage,
            'administration_route_id' => $request->administration_route_id,
            'frequency_id' => $request->frequency_id,
            'duration_days' => $request->duration_days,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'instructions' => $request->instructions,
            'notes' => $request->notes,
            'status' => Prescription::STATUS_PRESCRIBED,
            'prescribed_at' => now()
        ]);

        // CDS: Dispatch event for medication-related safety checks
        Log::info('CDS: Dispatching MedicationPrescribed event', [
            'patient_id' => $consultation->patient_id,
            'visit_id' => $consultation->visit_id,
            'medication_id' => $request->medication_id,
            'medication_name' => $medication->generic_name ?? $medication->brand_name ?? null
        ]);
        
        event(new MedicationPrescribed(
            $consultation->patient_id,
            [
                'prescription_id' => $prescription->id,
                'medication_id' => $request->medication_id,
                'medication_name' => $medication->generic_name ?? $medication->brand_name ?? null,
                'dosage' => $request->dosage,
                'administration_route_id' => $request->administration_route_id,
                'frequency_id' => $request->frequency_id,
                'duration_days' => $request->duration_days,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
            ],
            $consultation->visit_id
        ));

        // After CDS checks run (synchronously), return updated CDS drawer HTML and alert count
        $cdsAlerts = app(CdsAlertService::class)->forVisit($consultation->visit_id);
        Log::info('CDS: Retrieved alerts for visit', [
            'visit_id' => $consultation->visit_id,
            'alert_count' => $cdsAlerts->count()
        ]);
        
        $cdsDrawerHtml = view('components.cds.drawer', ['alerts' => $cdsAlerts])->render();

        $response = [
            'success' => true,
            'prescription_id' => $prescription->id,
            'cds_alerts_count' => $cdsAlerts->count(),
            'cds_drawer_html' => $cdsDrawerHtml,
            'cds_alerts' => $cdsAlerts->map(function ($a) {
                return [
                    'id' => $a->id,
                    'severity' => $a->severity,
                    'message' => $a->message,
                    'rationale' => $a->rationale,
                ];
            }),
        ];
        
        Log::info('CDS: Returning prescription response', [
            'success' => $response['success'],
            'prescription_id' => $response['prescription_id'],
            'cds_alerts_count' => $response['cds_alerts_count'],
            'cds_drawer_length' => strlen($response['cds_drawer_html']),
        ]);

        return response()->json($response);
    }

    /**
     * Dispense prescription
     */
    public function dispensePrescription(Request $request, $prescriptionId)
    {
        $prescription = Prescription::with(['medication', 'patient', 'consultation'])->findOrFail($prescriptionId);
        
        // Check if prescription is in dispensable status
        if (!in_array($prescription->status, [Prescription::STATUS_PRESCRIBED, Prescription::STATUS_PREPARED])) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription cannot be dispensed in its current status.'
            ], 400);
        }
        
        // Get patient category for pricing validation
        $patientCategoryId = $prescription->patient->patient_category_id;
        
        // Check if we have enough stock in the ledger
        $availableStock = \App\Models\MedicationLedger::where('medication_id', $prescription->medication_id)
            ->where('status', 'available')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '>', now())
            ->sum('quantity_remaining');
        
        if ($availableStock < $prescription->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available: {$availableStock}, Required: {$prescription->quantity}"
            ], 400);
        }
        
        // Dispense using FIFO method
        $remainingQuantity = $prescription->quantity;
        $dispensedBatches = [];
        
        $availableBatches = \App\Models\MedicationLedger::where('medication_id', $prescription->medication_id)
            ->where('status', 'available')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->get();
        
        DB::beginTransaction();
        
        try {
            foreach ($availableBatches as $batch) {
                if ($remainingQuantity <= 0) break;
                
                $dispensedFromBatch = min($remainingQuantity, $batch->quantity_remaining);
                
                // Update batch quantity
                $batch->quantity_remaining -= $dispensedFromBatch;
                if ($batch->quantity_remaining <= 0) {
                    $batch->status = 'depleted';
                }
                $batch->save();
                
                $dispensedBatches[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date,
                    'quantity_dispensed' => $dispensedFromBatch
                ];
                
                $remainingQuantity -= $dispensedFromBatch;
            }
            
            // Update prescription status
            $prescription->status = Prescription::STATUS_DISPENSED;
            $prescription->dispensed_at = now();
            $prescription->dispensed_by = Auth::id();
            $prescription->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Prescription dispensed successfully.',
                'dispensed_batches' => $dispensedBatches,
                'prescription' => $prescription->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error dispensing prescription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus(Request $request, $prescriptionId)
    {
        $prescription = Prescription::findOrFail($prescriptionId);
        
        $request->validate([
            'status' => 'required|in:' . implode(',', [
                Prescription::STATUS_DRAFT,
                Prescription::STATUS_PRESCRIBED,
                Prescription::STATUS_PREPARED,
                Prescription::STATUS_DISPENSED,
                Prescription::STATUS_CANCELLED
            ])
        ]);
        
        $prescription->status = $request->status;
        $prescription->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Prescription status updated successfully.',
            'prescription' => $prescription->fresh()
        ]);
    }
    
    /**
     * Delete prescription
     */
    public function deletePrescription($prescriptionId)
    {
        $prescription = Prescription::findOrFail($prescriptionId);
        
        // Check if prescription can be deleted
        if ($prescription->status === Prescription::STATUS_DISPENSED) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a dispensed prescription.'
            ], 400);
        }
        
        $prescription->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Prescription deleted successfully.'
        ]);
    }

    /**
     * Get investigations partial view (for AJAX updates)
     */
    public function getInvestigationsPartial($consultationId)
    {
        try {
            // Try to find by consultation ID first
            $consultation = Consultation::with('patient', 'visit')->find($consultationId);
            
            // If not found, try to find by visit ID
            if (!$consultation) {
                $consultation = Consultation::with('patient', 'visit')
                    ->where('visit_id', $consultationId)
                    ->firstOrFail();
            }
            
            // Get the patient category for pricing
            $patient = $consultation->patient;
            $patientCategoryId = $patient ? $patient->patient_category_id : null;
            
            // Get investigations for this consultation AND visit (includes lab-only investigations)
            $investigations = Investigation::where(function($query) use ($consultation) {
                    // Include investigations linked to this consultation
                    $query->where('consultation_id', $consultation->id)
                          // OR include investigations linked to this visit but without a consultation (lab-only)
                          ->orWhere(function($q) use ($consultation) {
                              $q->where('visit_id', $consultation->visit_id)
                                ->where('patient_id', $consultation->patient_id)
                                ->whereNull('consultation_id');
                          });
                })
                ->with([
                    'medicalService.serviceCategory', 
                    'medicalService.currentPricing' => function($query) use ($patientCategoryId) {
                        if ($patientCategoryId) {
                            $query->where('patient_category_id', $patientCategoryId);
                        }
                    },
                    'patient', 
                    'doctor'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Render just the investigations part
            $html = view('consultations.partials.investigations', compact('investigations', 'consultation'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $investigations->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching investigations partial', [
                'consultation_id' => $consultationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load investigations',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get prescriptions partial view for AJAX update
     */
    public function getPrescriptionsPartial($consultationId)
    {
        // Try to find by consultation ID first
        $consultation = Consultation::with(['prescriptions.medication', 'prescriptions.frequency', 'prescriptions.administrationRoute'])->find($consultationId);
        
        // If not found, try to find by visit ID
        if (!$consultation) {
            $consultation = Consultation::with(['prescriptions.medication', 'prescriptions.frequency', 'prescriptions.administrationRoute'])
                ->where('visit_id', $consultationId)
                ->firstOrFail();
        }
        
        $prescriptions = $consultation->prescriptions;

        // Check if this is for modal (compact view) or page (full table view)
        $forModal = request()->query('forModal', false);
        $viewPath = $forModal ? 'partials.prescriptions_list' : 'consultations.partials.prescriptions';
        
        $html = view($viewPath, compact('prescriptions'))->render();

        return response()->json([
            'success' => true, 
            'html' => $html,
            'count' => $prescriptions->count()
        ]);
    }

    /**
     * Get prescriptions partial view for simple HTML replacement
     */
    public function getPrescriptionsPartialHtml($consultationId)
    {
        $consultation = Consultation::with(['prescriptions.medication', 'prescriptions.frequency', 'prescriptions.administrationRoute'])->findOrFail($consultationId);
        $prescriptions = $consultation->prescriptions;

        return view('consultations.partials.prescriptions', compact('prescriptions'));
    }

    /**
     * Store investigation order
     */
    public function storeInvestigation(Request $request, $consultationId)
    {
        // Try to find by consultation ID first
        $consultation = Consultation::find($consultationId);
        
        // If not found, try to find by visit ID
        if (!$consultation) {
            $consultation = Consultation::where('visit_id', $consultationId)->firstOrFail();
        }

        $request->validate([
            'medical_service_id' => 'required|exists:medical_services,id',
            'quantity' => 'required|integer|min:1',
            'priority' => 'nullable|string|in:routine,urgent,stat',
            'notes' => 'nullable|string',
            'clinical_data' => 'nullable|string'
        ]);

        $service = MedicalService::with(['currentPricing', 'serviceCategory'])->findOrFail($request->medical_service_id);

        // Check if service requires form and validate clinical data
        if ($service->requires_form && empty($request->clinical_data)) {
            return response()->json([
                'success' => false,
                'message' => 'This service requires additional form data. Please fill in all required fields.',
                'errors' => ['clinical_data' => ['Additional form data is required for this service.']]
            ], 422);
        }

        // Get patient from consultation
        $patient = Patient::findOrFail($consultation->patient_id);

        // Parse clinical data if provided
        $clinicalData = null;
        if ($request->clinical_data) {
            $clinicalData = json_decode($request->clinical_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid clinical data format.',
                    'errors' => ['clinical_data' => ['Clinical data must be valid JSON.']]
                ], 422);
            }
        }

        // Get pricing information - use the same logic as InvestigationController
        $unitPrice = 0.00;
        $insuranceCoveredAmount = 0.00;
        
        // Check if currentPricing exists and has data
        if ($service->currentPricing && $service->currentPricing->count() > 0) {
            $pricing = $service->currentPricing->first();
            $unitPrice = (float) ($pricing->selling_price ?? $pricing->price ?? 0.00);
            $insuranceCoveredAmount = (float) ($pricing->insurance_covered_amount ?? 0.00);
        }
        // Fallback to service cost if no pricing
        elseif (isset($service->cost) && $service->cost > 0) {
            $unitPrice = (float) $service->cost;
        }
        
        $investigation = Investigation::create([
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'doctor_id' => $consultation->doctor_id,
            'medical_service_id' => $request->medical_service_id,
            'visit_id' => $consultation->visit_id, // Add visit_id
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $request->quantity,
            'insurance_covered_amount' => $insuranceCoveredAmount * $request->quantity,
            'priority' => $request->priority ?? 'routine',
            'notes' => $request->notes,
            'clinical_data' => $clinicalData ? json_encode($clinicalData) : null,
            'status' => Investigation::STATUS_ORDERED,
            'ordered_at' => now(),
            'ordered_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Investigation ordered successfully.',
            'investigation' => $investigation->load(['medicalService.serviceCategory', 'patient', 'doctor']),
            'total_price' => $investigation->total_price,
            'requires_sample' => $service->requires_sample,
            'requires_form' => $service->requires_form,
            'form_submitted' => !empty($clinicalData),
            'sample_type' => $service->sample_type,
            'turnaround_time' => $service->turnaround_time_hours . ' hours',
            'status_label' => ucfirst($investigation->status),
            'priority_badge_class' => 'badge-' . ($investigation->priority === 'stat' ? 'danger' : ($investigation->priority === 'urgent' ? 'warning' : 'info'))
        ]);
    }

    /**
     * Get medication details for pricing
     */
    public function getMedicationDetails($medicationId)
    {
        $medication = Medication::find($medicationId);
        
        if (!$medication) {
            return response()->json(['error' => 'Medication not found'], 404);
        }

        return response()->json([
            'id' => $medication->id,
            'name' => $medication->generic_name,
            'generic_name' => $medication->generic_name,
            'strength' => $medication->strength,
            'formulation' => $medication->formulation,
            'unit_price' => $medication->unit_price,
            'stock_quantity' => $medication->stock_quantity,
            'is_in_stock' => $medication->is_in_stock,
            'is_low_stock' => $medication->is_low_stock,
            'stock_status' => $medication->stock_status
        ]);
    }

    /**
     * Get service details for pricing
     */
    public function getServiceDetails($serviceId)
    {
        $service = MedicalService::with('serviceCategory')->find($serviceId);
        
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'code' => $service->code,
            'price' => $service->price,
            'insurance_covered_amount' => $service->insurance_covered_amount,
            'effective_price' => $service->effective_price,
            'requires_sample' => $service->requires_sample,
            'sample_type' => $service->sample_type,
            'turnaround_time' => $service->turnaround_time_readable,
            'preparation_instructions' => $service->preparation_instructions,
            'category' => $service->serviceCategory->name ?? ''
        ]);
    }

    /**
     * Store or update past medical history
     */
    public function storePastMedicalHistory(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'previous_surgeries' => 'nullable|string',
            'family_history' => 'nullable|string',
            'social_history' => 'nullable|string',
            'occupational_history' => 'nullable|string',
            'smoking_status' => 'nullable|in:non_smoker,former_smoker,current_smoker',
            'alcohol_use' => 'nullable|in:none,occasional,moderate,heavy',
            'current_medications' => 'nullable|string',
            'immunization_history' => 'nullable|string',
            'reproductive_history' => 'nullable|string'
        ]);

        // Check if medical history already exists for this patient
        $medicalHistory = PastMedicalHistory::where('patient_id', $request->patient_id)->first();

        if ($medicalHistory) {
            // Update existing record
            $medicalHistory->update([
                'allergies' => $request->allergies,
                'chronic_conditions' => $request->chronic_conditions,
                'previous_surgeries' => $request->previous_surgeries,
                'family_history' => $request->family_history,
                'social_history' => $request->social_history,
                'occupational_history' => $request->occupational_history,
                'smoking_status' => $request->smoking_status,
                'alcohol_use' => $request->alcohol_use,
                'current_medications' => $request->current_medications,
                'immunization_history' => $request->immunization_history,
                'reproductive_history' => $request->reproductive_history,
                'updated_by' => Auth::id()
            ]);
        } else {
            // Create new record
            $medicalHistory = PastMedicalHistory::create([
                'patient_id' => $request->patient_id,
                'allergies' => $request->allergies,
                'chronic_conditions' => $request->chronic_conditions,
                'previous_surgeries' => $request->previous_surgeries,
                'family_history' => $request->family_history,
                'social_history' => $request->social_history,
                'occupational_history' => $request->occupational_history,
                'smoking_status' => $request->smoking_status,
                'alcohol_use' => $request->alcohol_use,
                'current_medications' => $request->current_medications,
                'immunization_history' => $request->immunization_history,
                'reproductive_history' => $request->reproductive_history,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Past medical history saved successfully.',
            'data' => $medicalHistory
        ]);
    }

    /**
     * Get patient's past medical history
     */
    public function getPatientMedicalHistory($patientId)
    {
        $medicalHistory = PastMedicalHistory::where('patient_id', $patientId)->first();

        if (!$medicalHistory) {
            return response()->json([
                'success' => false,
                'message' => 'No medical history found for this patient.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medicalHistory
        ]);
    }

    /**
     * Add ICD-10 diagnosis to consultation
     */
    public function addIcdDiagnosis(Request $request, $consultationId)
    {
        $request->validate([
            'icd_code' => 'required|string|max:10',
            'description' => 'required|string|max:500',
            'type' => 'required|in:provisional,final',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
        ]);
        
        $consultation = Consultation::findOrFail($consultationId);
        
        // Check if this ICD code already exists for this consultation and type
        $existingDiagnosis = $consultation->icdDiagnoses()
            ->where('icd_code', $request->icd_code)
            ->where('type', $request->type)
            ->first();
        
        if ($existingDiagnosis) {
            return response()->json([
                'success' => false,
                'message' => 'This ICD-10 code is already added for this diagnosis type.'
            ], 409);
        }
        
        $icdDiagnosis = $consultation->icdDiagnoses()->create([
            'icd_code' => $request->icd_code,
            'description' => $request->description,
            'type' => $request->type,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'added_by' => Auth::id(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'ICD-10 diagnosis added successfully.',
            'data' => $icdDiagnosis
        ]);
    }

    /**
     * Get ICD diagnoses for a consultation
     */
    public function getIcdDiagnoses($consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        $icdDiagnoses = $consultation->icdDiagnoses()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $icdDiagnoses
        ]);
    }

    /**
     * Remove ICD diagnosis
     */
    public function removeIcdDiagnosis($icdDiagnosisId)
    {
        $icdDiagnosis = IcdDiagnosis::findOrFail($icdDiagnosisId);
        $icdDiagnosis->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'ICD-10 diagnosis removed successfully.'
        ]);
    }

    /**
     * Remove an investigation
     */
    public function removeInvestigation($investigationId)
    {
        try {
            $investigation = Investigation::findOrFail($investigationId);
            
            // Only allow removal if status is 'ordered'
            if ($investigation->status !== 'ordered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove investigation that is already in progress or completed'
                ], 400);
            }
            
            $investigation->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Investigation removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing investigation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove investigation'
            ], 500);
        }
    }

    /**
     * Acknowledge a CDS alert
     */
    public function acknowledgeCdsAlert(Request $request, $consultationId, $alertId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            $alert = \App\Models\CdsAlert::findOrFail($alertId);

            // Validate that this alert belongs to this consultation's patient/visit
            if ($alert->patient_id !== $consultation->patient_id && $alert->visit_id !== $consultation->visit_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alert does not belong to this consultation.'
                ], 403);
            }

            $request->validate([
                'action' => 'required|in:accept,override,dismiss',
                'reason' => 'nullable|string|max:500'
            ]);

            // Update alert status
            $alert->update([
                'status' => 'acknowledged',
                'resolved_at' => now(),
                'resolved_by' => Auth::id()
            ]);

            // Log the acknowledgment action
            \App\Models\CdsAlertAction::create([
                'cds_alert_id' => $alert->id,
                'action' => $request->action,
                'reason' => $request->reason,
                'user_id' => Auth::id()
            ]);

            Log::info('CDS Alert acknowledged', [
                'alert_id' => $alert->id,
                'action' => $request->action,
                'reason' => $request->reason,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert acknowledged successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to acknowledge CDS alert', [
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to acknowledge alert'
            ], 500);
        }
    }

}
