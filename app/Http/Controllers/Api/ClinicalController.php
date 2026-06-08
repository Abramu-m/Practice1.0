<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\VitalSigns;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\Medication;
use App\Models\MedicalService;
use App\Models\AdministrationRoute;
use App\Models\MedicationFrequency;
use App\Models\ServiceCategory;
use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ClinicalController extends Controller
{
    /**
     * Get consultation dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $doctorId = $request->input('doctor_id', Auth::id());
        
        $data = [
            'active_consultations' => Consultation::where('doctor_id', $doctorId)
                ->where('status', 'active')
                ->count(),
            
            'pending_prescriptions' => Prescription::where('doctor_id', $doctorId)
                ->pending()
                ->count(),
                
            'pending_investigations' => Investigation::where('doctor_id', $doctorId)
                ->whereIn('status', ['ordered', 'collected'])
                ->count(),
                
            'patients_today' => Consultation::where('doctor_id', $doctorId)
                ->whereDate('consultation_date', today())
                ->count(),
                
            'recent_consultations' => Consultation::with(['patient', 'vitalSigns'])
                ->where('doctor_id', $doctorId)
                ->latest('consultation_date')
                ->limit(10)
                ->get(),
                
            'low_stock_medications' => Medication::lowStock()->active()->limit(10)->get(),
            
            'urgent_investigations' => Investigation::urgent()
                ->whereIn('status', ['ordered', 'collected'])
                ->with(['patient', 'medicalService'])
                ->limit(10)
                ->get()
        ];
        
        return response()->json($data);
    }

    /**
     * Start a new consultation
     */
    public function startConsultation(Request $request): JsonResponse
    {
        $request->validate([
            'patient_registration_number' => 'required|string',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'visit_id' => 'nullable|exists:patient_visits,id',
            'chief_complaint' => 'nullable|string'
        ]);

        try {
            $consultation = Consultation::create([
                'patient_registration_number' => $request->patient_registration_number,
                'doctor_id' => $request->doctor_id,
                'visit_id' => $request->visit_id,
                'chief_complaint' => $request->chief_complaint,
                'consultation_date' => now(),
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consultation started successfully',
                'consultation' => $consultation->load(['patient', 'doctor'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start consultation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record vital signs
     */
    public function recordVitalSigns(Request $request, $consultationId): JsonResponse
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
            'ofc' => 'nullable|string|max:10'
        ]);

        try {
            $vitals = VitalSigns::create([
                'consultation_id' => $consultation->id,
                'pulse_rate' => $request->pulse_rate,
                'temperature' => $request->temperature,
                'respiratory_rate' => $request->respiratory_rate,
                'weight' => $request->weight,
                'height' => $request->height,
                'systolic_pressure' => $request->systolic_pressure,
                'diastolic_pressure' => $request->diastolic_pressure,
                'oxygen_saturation' => $request->oxygen_saturation,
                'muac' => $request->muac,
                'ofc' => $request->ofc,
                'status' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vital signs recorded successfully',
                'vitals' => $vitals,
                'calculated_values' => [
                    'bmi' => $vitals->bmi,
                    'bmi_category' => $vitals->bmi_category,
                    'blood_pressure_category' => $vitals->blood_pressure_category,
                    'blood_pressure' => $vitals->blood_pressure
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record vital signs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create prescription
     */
    public function createPrescription(Request $request, $consultationId): JsonResponse
    {
        $consultation = Consultation::findOrFail($consultationId);

        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'dosage' => 'required|string|max:100',
            'administration_route_id' => 'required|exists:administration_routes,id',
            'frequency_id' => 'required|exists:medication_frequencies,id',
            'duration_days' => 'required|numeric|min:1|max:365',
            'quantity' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $medication = Medication::findOrFail($request->medication_id);

            // Check stock availability
            if (!$medication->is_in_stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Medication is out of stock',
                    'stock_status' => $medication->stock_status
                ], 400);
            }

            $prescription = Prescription::create([
                'patient_registration_number' => $consultation->patient_registration_number,
                'consultation_id' => $consultation->id,
                'doctor_id' => $consultation->doctor_id,
                'medication_id' => $request->medication_id,
                'dosage' => $request->dosage,
                'administration_route_id' => $request->administration_route_id,
                'frequency_id' => $request->frequency_id,
                'duration_days' => $request->duration_days,
                'quantity' => $request->quantity,
                'unit_price' => $medication->unit_price,
                'instructions' => $request->instructions,
                'notes' => $request->notes,
                'status' => Prescription::STATUS_PRESCRIBED,
                'prescribed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prescription created successfully',
                'prescription' => $prescription->load([
                    'medication', 
                    'administrationRoute', 
                    'frequency'
                ]),
                'calculated_values' => [
                    'total_price' => $prescription->total_price,
                    'status_label' => $prescription->status_label,
                    'status_badge_class' => $prescription->status_badge_class
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create prescription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Order investigation
     */
    public function orderInvestigation(Request $request, $consultationId): JsonResponse
    {
        $consultation = Consultation::findOrFail($consultationId);

        $request->validate([
            'medical_service_id' => 'required|exists:medical_services,id',
            'quantity' => 'required|numeric|min:1',
            'priority' => 'required|in:routine,urgent,stat',
            'notes' => 'nullable|string'
        ]);

        try {
            $service = MedicalService::findOrFail($request->medical_service_id);

            $investigation = Investigation::create([
                'patient_registration_number' => $consultation->patient_registration_number,
                'consultation_id' => $consultation->id,
                'doctor_id' => $consultation->doctor_id,
                'medical_service_id' => $request->medical_service_id,
                'quantity' => $request->quantity,
                'unit_price' => $service->price,
                'insurance_covered_amount' => $service->insurance_covered_amount,
                'priority' => $request->priority,
                'notes' => $request->notes,
                'status' => Investigation::STATUS_ORDERED,
                'ordered_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Investigation ordered successfully',
                'investigation' => $investigation->load('medicalService.serviceCategory'),
                'calculated_values' => [
                    'total_price' => $investigation->total_price,
                    'effective_price' => $service->effective_price,
                    'status_label' => $investigation->status_label ?? 'Ordered'
                ],
                'service_info' => [
                    'requires_sample' => $service->requires_sample,
                    'sample_type' => $service->sample_type,
                    'turnaround_time' => $service->turnaround_time_readable,
                    'preparation_instructions' => $service->preparation_instructions
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to order investigation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get consultation details with all related data
     */
    public function getConsultation($consultationId): JsonResponse
    {
        try {
            $consultation = Consultation::with([
                'patient',
                'doctor',
                'vitalSigns' => function($query) {
                    $query->latest();
                },
                'prescriptions.medication',
                'prescriptions.administrationRoute',
                'prescriptions.frequency',
                'investigations.medicalService.serviceCategory'
            ])->findOrFail($consultationId);

            // Get latest vital signs with calculated values
            $latestVitals = $consultation->vitalSigns->first();
            if ($latestVitals) {
                $latestVitals->calculated_values = [
                    'bmi_category' => $latestVitals->bmi_category,
                    'blood_pressure_category' => $latestVitals->blood_pressure_category
                ];
            }

            // Add calculated values to prescriptions
            $consultation->prescriptions->each(function($prescription) {
                $prescription->calculated_values = [
                    'status_label' => $prescription->status_label,
                    'status_badge_class' => $prescription->status_badge_class
                ];
            });

            return response()->json([
                'success' => true,
                'consultation' => $consultation,
                'summary' => [
                    'total_prescriptions' => $consultation->prescriptions->count(),
                    'total_investigations' => $consultation->investigations->count(),
                    'pending_prescriptions' => $consultation->prescriptions->where('status', '!=', Prescription::STATUS_DISPENSED)->count(),
                    'completed_investigations' => $consultation->investigations->where('status', Investigation::STATUS_RESULTED)->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get dropdown data for forms
     */
    public function getFormData(): JsonResponse
    {
        try {
            $data = [
                'medications' => Medication::active()->inStock()->select('id', 'name', 'generic_name', 'strength', 'unit_price', 'stock_quantity')->get(),
                'administration_routes' => AdministrationRoute::active()->select('id', 'name', 'abbreviation')->get(),
                'medication_frequencies' => MedicationFrequency::active()->select('id', 'name', 'abbreviation', 'times_per_day')->get(),
                'medical_services' => MedicalService::active()->with('serviceCategory:id,name')->select('id', 'name', 'code', 'price', 'service_category_id', 'requires_sample', 'sample_type')->get(),
                'service_categories' => ServiceCategory::active()->select('id', 'name')->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus(Request $request, $prescriptionId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,prescribed,prepared,dispensed,cancelled'
        ]);

        try {
            $prescription = Prescription::findOrFail($prescriptionId);
            
            $updateData = ['status' => $request->status];
            
            // Set timestamp based on status
            switch ($request->status) {
                case Prescription::STATUS_PREPARED:
                    $updateData['prepared_at'] = now();
                    $updateData['prepared_by'] = Auth::id();
                    break;
                case Prescription::STATUS_DISPENSED:
                    $updateData['dispensed_at'] = now();
                    $updateData['dispensed_by'] = Auth::id();
                    break;
            }
            
            $prescription->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Prescription status updated successfully',
                'prescription' => $prescription->load(['medication', 'administrationRoute', 'frequency']),
                'status_info' => [
                    'status_label' => $prescription->status_label,
                    'status_badge_class' => $prescription->status_badge_class
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prescription status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update investigation status
     */
    public function updateInvestigationStatus(Request $request, $investigationId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,ordered,collected,processing,resulted,cancelled'
        ]);

        try {
            $investigation = Investigation::findOrFail($investigationId);
            
            $updateData = ['status' => $request->status];
            
            // Set timestamp based on status
            switch ($request->status) {
                case Investigation::STATUS_COLLECTED:
                    $updateData['collected_at'] = now();
                    $updateData['collected_by'] = Auth::id();
                    break;
                case Investigation::STATUS_RESULTED:
                    $updateData['resulted_at'] = now();
                    $updateData['resulted_by'] = Auth::id();
                    break;
            }
            
            $investigation->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Investigation status updated successfully',
                'investigation' => $investigation->load('medicalService.serviceCategory')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update investigation status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search medical services
     */
    public function searchMedicalServices(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query');
            $limit = $request->get('limit', 10);
            $patientCategoryId = $request->get('patient_category_id');
            $labOnly = $request->get('lab_only', false);
            
            if (!$query || strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query must be at least 2 characters long'
                ], 400);
            }
            
            $servicesQuery = MedicalService::with(['serviceCategory'])
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->where('is_active', true);
            
            // Filter for lab services only if requested
            if ($labOnly) {
                $servicesQuery->whereHas('serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%lab%')
                      ->orWhere('name', 'LIKE', '%laboratory%')
                      ->orWhere('name', 'LIKE', '%pathology%')
                      ->orWhere('name', 'LIKE', '%diagnostic%');
                });
            }
                
            $servicesQuery->orderBy('name')->limit($limit);
            
            $services = $servicesQuery->get()->map(function($service) use ($patientCategoryId) {
                $pricing = $service->pricing($patientCategoryId);
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'code' => $service->code,
                    'description' => $service->description,
                    'category' => $service->serviceCategory->name ?? null,
                    'requires_sample' => $service->requires_sample,
                    'sample_type' => $service->sample_type,
                    'requires_form' => $service->requires_form,
                    'form_type' => $service->form_type,
                    'cash' =>  $pricing['cash_amount'] ?? null,
                    'covered' =>  $pricing['insurance_covered_amount'] ?? null,
                    'has_pricing' => $pricing !== null
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search medical services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a medical service requires a form
     */
    public function checkServiceFormRequirements($serviceId): JsonResponse
    {
        try {
            $service = MedicalService::find($serviceId);
            
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Medical service not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'requires_form' => (bool)$service->requires_form,
                'form_type' => $service->form_type ?: null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check service form requirements',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
