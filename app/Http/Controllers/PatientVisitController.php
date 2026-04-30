<?php

namespace App\Http\Controllers;

use App\Models\PatientVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PatientVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get current user and check if they're a doctor
        $user = Auth::user();
        $currentDoctorId = null;
        
        if ($user->role === 'doctor' && $user->doctor) {
            $currentDoctorId = $user->doctor->doctor_id ?? null;
        }
        
        // Get selected patient/doctor for context
        $selectedPatient = null;
        if ($request->has('patient_id') && $request->patient_id) {
            $selectedPatient = \App\Models\Patient::find($request->patient_id);
        }
        
        $selectedDoctor = null;
        if ($request->has('doctor_id') && $request->doctor_id) {
            $selectedDoctor = \App\Models\Doctor::with('user')->find($request->doctor_id);
        }
        
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = PatientVisit::with(['patientInfo.activeVisit.visitType', 'visitCategory', 'visitType', 'doctorInfo.user', 'createdBy']);
            
            // If user is a doctor (not admin), filter to only show their patients
            if ($currentDoctorId && !($user->is_admin || $user->is_super)) {
                $query->where('doctor', $currentDoctorId);
            }

            // Filter by patient if specified
            if ($request->has('patient_id') && $request->patient_id) {
                $query->where('patient', $request->patient_id);
            }
            
            // Filter by doctor if specified
            if ($request->has('doctor_id') && $request->doctor_id) {
                if ($user->is_admin || $user->is_super || $request->doctor_id == $currentDoctorId) {
                    $query->where('doctor', $request->doctor_id);
                }
            }
            
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = trim($request->search['value']);
                        
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('patientInfo', function($patientQuery) use ($search) { 
                                $patientQuery->where('first_name', 'like', "%{$search}%")
                                             ->orWhere('last_name', 'like', "%{$search}%")
                                             ->orWhere('middle_name', 'like', "%{$search}%");
                                             
                                if (preg_match('/MR-\d{4}-(\d+)/', $search, $matches)) {
                                    $patientQuery->orWhere('id', intval($matches[1]));
                                } elseif (is_numeric($search)) {
                                    $patientQuery->orWhere('id', $search);
                                }
                            })
                            ->orWhere('sic_no', 'like', "%{$search}%")
                            ->orWhere('authorization_no', 'like', "%{$search}%")
                            ->orWhere('nhif_reference_no', 'like', "%{$search}%")
                            ->orWhere('id', $search);
                        });
                    }
                })
                ->addColumn('patient_name', function ($visit) {
                    return $visit->patientInfo->full_name ?? 'Unknown';
                })
                ->addColumn('visit_date', function ($visit) {
                    return $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y') : 'N/A';
                })
                ->addColumn('category', function ($visit) {
                    return $visit->visitCategory->description ?? 'N/A';
                })
                ->addColumn('visit_type', function ($visit) {
                    $visitTypeDesc = $visit->visitType->description ?? 'N/A';
                    $badgeClass = 'bg-secondary';
                    
                    switch(strtolower($visitTypeDesc)) {
                        case 'first visit': $badgeClass = 'bg-primary'; break;
                        case 'follow up': $badgeClass = 'bg-success'; break;
                        case 'internal referral': $badgeClass = 'bg-warning'; break;
                        case 'external referral': $badgeClass = 'bg-info'; break;
                        case 'lab only': $badgeClass = 'bg-danger'; break;
                    }
                    
                    return '<span class="badge ' . $badgeClass . '">' . e($visitTypeDesc) . '</span>';
                })
                ->addColumn('doctor_name', function ($visit) {
                    return optional(optional($visit->doctorInfo)->user)->name ?? 'N/A';
                })
                ->addColumn('cash_amount', function ($visit) {
                    return 'Tsh ' . number_format($visit->amount_cash, 2);
                })
                ->addColumn('covered_amount', function ($visit) {
                    return 'Tsh ' . number_format($visit->amount_covered ?? 0, 2);
                })
                ->addColumn('status', function ($visit) {
                    return '<span class="badge ' . $visit->visit_status_badge_class . ' text-black">' . e($visit->visit_status_label) . '</span>';
                })
                ->addColumn('actions', function ($visit) use ($selectedPatient, $user, $currentDoctorId) {
                    return view('patient_visits._actions', compact('visit', 'selectedPatient', 'user', 'currentDoctorId'))->render();
                })
                ->rawColumns(['visit_type', 'status', 'actions'])
                ->make(true);
        }

        return view('patient_visits.index', compact('selectedPatient', 'selectedDoctor'));
    }

    /**
     * Show the form for creating a new visit.
     */
    public function create(Request $request)
    {
        // Get necessary data for the form (no longer loading all patients)
        $patientCategories = \App\Models\PatientCategory::all();
        $doctors = \App\Models\Doctor::where('status', 1)->get();
        $visitTypes = \App\Models\VisitType::all();
        
        // If patient_id is provided, pre-select the patient
        $selectedPatient = null;
        if ($request->has('patient_id')) {
            $selectedPatient = \App\Models\Patient::find($request->patient_id);
            // If patient is found, check if they have an active visit
            if ($selectedPatient) {
                // Check if patient has an active visit using our model method
                if ($selectedPatient->active_visit) {
                    return redirect()->route('patients.show', $selectedPatient->id)
                        ->with('error', 'Cannot create new visit. Patient has an active visit that needs to be completed first.')
                        ->with('active_visit_id', $selectedPatient->active_visit->id);
                }
            } else {
                // If patient not found, redirect back with error
                return redirect()->back()->withErrors(['patient_id' => 'Selected patient does not exist.']);
            }
        }

        //If doctor_id is provided, pre-select the doctor
        $selectedDoctor = null;
        if ($request->has('doctor_id')) {
            $selectedDoctor = \App\Models\Doctor::find($request->doctor_id);
        }
        
        return view('patient_visits.create', compact('patientCategories', 'doctors', 'visitTypes', 'selectedPatient', 'selectedDoctor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // First, check if the patient has an active visit using our helper method
        $activeVisitCheck = $this->checkPatientActiveVisit($request->patient);
        if ($activeVisitCheck['hasActiveVisit']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['patient' => $activeVisitCheck['message']])
                ->with('error', 'Cannot create new visit: ' . $activeVisitCheck['message']);
        }

        // Check if visit type is "Lab Only"
        $visitType = \App\Models\VisitType::find($request->visit_type);
        $isLabOnly = $visitType && stripos($visitType->description, 'lab only') !== false;
        
        // Define validation rules
        $rules = [
            'patient' => 'required|exists:patients,id',
            'visit_date' => 'required|date',
            'visit_category' => 'required|exists:patient_categories,id',
            'visit_type' => 'required|exists:visit_types,id',
            'amount_cash' => 'required|numeric|min:0',
            'amount_covered' => 'nullable|numeric|min:0',
            'sic_no' => 'nullable|string|max:30',
            'authorization_no' => 'nullable|string|max:30',
            'nhif_reference_no' => 'nullable|string|max:30',
            'folio_item_id' => 'nullable|string|max:32',
            'pitc_at' => 'nullable|date',
            'vitals_at' => 'nullable|date',
            'consulted_at' => 'nullable|date',
            'resulted_at' => 'nullable|date',
            'signature' => 'nullable|string',
        ];
        
        // For Lab Only visits, doctor is not required and covered amount should be 0
        if ($isLabOnly) {
            $rules['doctor'] = 'nullable';
            $rules['amount_covered'] = 'nullable|numeric|size:0'; // Must be 0 for lab only
        } else {
            $rules['doctor'] = 'required|exists:doctors,doctor_id'; // Required for non-lab visits
        }
        
        $request->validate($rules);

        $visitData = $request->all();
        
        // For Lab Only visits, ensure no doctor is assigned and covered amount is 0
        if ($isLabOnly) {
            $visitData['doctor'] = null;
            $visitData['amount_covered'] = 0.00;
            
            // Add validation message if user tried to set covered amount for Lab Only
            if ($request->amount_covered > 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['amount_covered' => 'Lab Only visits are cash payments only. Covered amount must be 0.']);
            }
        }
        
        // Set default values
        $visitData['created_by'] = Auth::id();
        $visitData['created_on'] = now()->toDateString();
        $visitData['created_on_time'] = now()->toTimeString();
        $visitData['post_status'] = 0; // Not Posted by default
        $visitData['visit_status'] = 0; // Waiting by default
        $visitData['vital_status'] = 0; // Not Taken by default
        
        $patientVisit = PatientVisit::create($visitData);

        // Create a consultation record only for non-Lab Only visits
        if (!$isLabOnly && $request->doctor) {
            $consultationData = [
                'patient_id' => $request->patient,
                'doctor_id' => $request->doctor,
                'visit_id' => $patientVisit->id, // Link to the visit
                'consultation_date' => now(),
                'status' => 'active' // Initial status
            ];
            \App\Models\Consultation::create($consultationData);
        }

        return redirect()->route('patient_visits.index')
                         ->with('success', 'Patient visit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientVisit $patientVisit)
    {
        return view('patient_visits.show', compact('patientVisit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientVisit $patientVisit)
    {
        // Get all necessary data for the form
        $patients = \App\Models\Patient::where('status', 'active')->get();
        $patientCategories = \App\Models\PatientCategory::all();
        $doctors = \App\Models\Doctor::where('status', 1)->get();
        $visitTypes = \App\Models\VisitType::all();

        // If patient_id is provided, pre-select the patient
        $selectedPatient = \App\Models\Patient::find($patientVisit->patient);

        // If doctor_id is provided, pre-select the doctor
        $selectedDoctor = \App\Models\Doctor::find($patientVisit->doctor);
        if ($patientVisit->doctor) {
            $selectedDoctor = \App\Models\Doctor::with('user')->find($patientVisit->doctor);
        }

        // If visit_category_id is provided, pre-select the category
        $selectedCategory = \App\Models\PatientCategory::find($patientVisit->visit_category);
        if ($patientVisit->visit_category) {
            $selectedCategory = \App\Models\PatientCategory::find($patientVisit->visit_category);
        }

        // If visit_type_id is provided, pre-select the type
        $selectedVisitType = \App\Models\VisitType::find($patientVisit->visit_type);
        if ($patientVisit->visit_type) {
            $selectedVisitType = \App\Models\VisitType::find($patientVisit->visit_type);
        }

        // Pass the visit data to the view
        $patientVisit->visit_date = $patientVisit->visit_date ? $patientVisit->visit_date->format('Y-m-d') : null;
        $patientVisit->created_on = $patientVisit->created_on ? $patientVisit->created_on->format('Y-m-d') : null;
        $patientVisit->created_on_time = $patientVisit->created_on ? $patientVisit->created_on->format('H:i:s') : null;
        $patientVisit->pitc_at = $patientVisit->pitc_at ? $patientVisit->pitc_at->format('Y-m-d') : null;
        $patientVisit->vitals_at = $patientVisit->vitals_at ? $patientVisit->vitals_at->format('Y-m-d') : null;
        $patientVisit->consulted_at = $patientVisit->consulted_at ? $patientVisit->consulted_at->format('Y-m-d') : null;
        $patientVisit->resulted_at = $patientVisit->resulted_at ? $patientVisit->resulted_at->format('Y-m-d') : null;        
        return view('patient_visits.edit', compact('patientVisit', 'patients', 'patientCategories', 'doctors', 'visitTypes', 'selectedPatient', 'selectedDoctor', 'selectedCategory', 'selectedVisitType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientVisit $patientVisit)
    {
        // Check if visit type is "Lab Only" (both current and new)
        $currentVisitType = \App\Models\VisitType::find($patientVisit->visit_type);
        $newVisitType = \App\Models\VisitType::find($request->visit_type);
        $isCurrentLabOnly = $currentVisitType && stripos($currentVisitType->description, 'lab only') !== false;
        $isNewLabOnly = $newVisitType && stripos($newVisitType->description, 'lab only') !== false;
        
        // Prevent updating other visit types TO Lab Only
        if (!$isCurrentLabOnly && $isNewLabOnly) {
            return redirect()->back()
                ->withErrors(['visit_type' => 'Cannot change visit type to Lab Only. Lab Only visits must be created directly.'])
                ->withInput();
        }
        
        // Get current visit status to determine validation rules
        $currentStatus = $patientVisit->visit_status;
        
        // Base validation rules (removed status validations since they're managed automatically)
        $rules = [
            'post_status' => 'required|in:0,1', // 0: Not Posted, 1: Posted
            'pitc_at' => 'nullable|date',
            'vitals_at' => 'nullable|date',
            'consulted_at' => 'nullable|date',
            'resulted_at' => 'nullable|date',
            'signature' => 'nullable|string',
        ];

        // Status-based validation rules
        if ($currentStatus == 0) { // Waiting - everything can change except patient
            $rules = array_merge($rules, [
                'patient' => 'required|exists:patients,id',
                'visit_date' => 'required|date',
                'visit_category' => 'required|exists:patient_categories,id',
                'visit_type' => 'required|exists:visit_types,id',
                'amount_cash' => 'required|numeric|min:0',
                'amount_covered' => 'nullable|numeric|min:0',
                'sic_no' => 'nullable|string|max:30',
                'authorization_no' => 'nullable|string|max:30',
                'nhif_reference_no' => 'nullable|string|max:30',
                'folio_item_id' => 'nullable|string|max:32',
                'created_by' => 'nullable|exists:users,id',
                'created_on' => 'nullable|date',
            ]);
            
            // Handle Lab Only specific validation
            if ($isNewLabOnly) {
                $rules['doctor'] = 'nullable'; // Lab Only doesn't require doctor
                $rules['amount_covered'] = 'nullable|numeric|size:0'; // Must be 0 for lab only
            } else {
                $rules['doctor'] = 'required|exists:doctors,doctor_id'; // Non-Lab Only requires doctor
            }
            
            // Patient cannot change even in waiting status
            if ($request->patient != $patientVisit->patient) {
                return redirect()->back()
                    ->withErrors(['patient' => 'Patient cannot be changed once the visit is created.'])
                    ->withInput();
            }
            
        } elseif ($currentStatus == 1) { // In Treatment - limited changes
            $rules = array_merge($rules, [
                'patient' => 'required|exists:patients,id',
                'visit_date' => 'required|date',
                'visit_category' => 'required|exists:patient_categories,id',
                'visit_type' => 'required|exists:visit_types,id',
                'amount_cash' => 'required|numeric|min:0',
                'amount_covered' => 'nullable|numeric|min:0',
                'sic_no' => 'nullable|string|max:30',
                'authorization_no' => 'nullable|string|max:30',
                'nhif_reference_no' => 'nullable|string|max:30',
                'folio_item_id' => 'nullable|string|max:32',
                'created_by' => 'nullable|exists:users,id',
                'created_on' => 'nullable|date',
            ]);
            
            // Handle Lab Only specific validation for in-treatment visits
            if ($isNewLabOnly) {
                $rules['doctor'] = 'nullable'; // Lab Only doesn't require doctor
                $rules['amount_covered'] = 'nullable|numeric|size:0'; // Must be 0 for lab only
            } else {
                $rules['doctor'] = 'required|exists:doctors,doctor_id'; // Non-Lab Only requires doctor
            }
            
            // Custom validations for "In Treatment" status
            $errors = [];
            
            if ($request->patient != $patientVisit->patient) {
                $errors['patient'] = 'Patient cannot be changed once in treatment.';
            }
            
            if ($request->visit_date != ($patientVisit->visit_date ? $patientVisit->visit_date->format('Y-m-d') : null)) {
                $errors['visit_date'] = 'Visit date cannot be changed once in treatment.';
            }
            
            if ($request->visit_category != $patientVisit->visit_category) {
                $errors['visit_category'] = 'Visit category cannot be changed once in treatment.';
            }
            
            // Visit type can only change to Internal Referral (ID: 7) or stay the same, OR Lab Only can change to any type
            if ($request->visit_type != $patientVisit->visit_type && $request->visit_type != 7 && !$isCurrentLabOnly) {
                $errors['visit_type'] = 'Visit type can only be changed to Internal Referral when in treatment, except Lab Only visits which can change to any type.';
            }
            
            if ($request->amount_cash != $patientVisit->amount_cash) {
                $errors['amount_cash'] = 'Amount cash cannot be changed once in treatment.';
            }
            
            if ($request->amount_covered != $patientVisit->amount_covered) {
                $errors['amount_covered'] = 'Amount covered cannot be changed once in treatment.';
            }
            
            if (!empty($errors)) {
                return redirect()->back()->withErrors($errors)->withInput();
            }
            
        } else { // Discharged (status 2) - very limited changes
            $rules = array_merge($rules, [
                'patient' => 'required|exists:patients,id',
                'visit_date' => 'required|date',
                'visit_category' => 'required|exists:patient_categories,id',
                'visit_type' => 'required|exists:visit_types,id',
                'amount_cash' => 'required|numeric|min:0',
                'amount_covered' => 'nullable|numeric|min:0',
                'sic_no' => 'nullable|string|max:30',
                'authorization_no' => 'nullable|string|max:30',
                'nhif_reference_no' => 'nullable|string|max:30',
                'folio_item_id' => 'nullable|string|max:32',
                'created_by' => 'nullable|exists:users,id',
                'created_on' => 'nullable|date',
            ]);
            
            // Handle Lab Only specific validation for discharged visits
            if ($isNewLabOnly) {
                $rules['doctor'] = 'nullable'; // Lab Only doesn't require doctor
                $rules['amount_covered'] = 'nullable|numeric|size:0'; // Must be 0 for lab only
            } else {
                $rules['doctor'] = 'required|exists:doctors,doctor_id'; // Non-Lab Only requires doctor
            }
            
            // For discharged patients, most fields cannot be changed, but Lab Only can still transition
            $errors = [];
            
            if ($request->patient != $patientVisit->patient) {
                $errors['patient'] = 'Patient cannot be changed once discharged.';
            }
            
            if ($request->visit_date != ($patientVisit->visit_date ? $patientVisit->visit_date->format('Y-m-d') : null)) {
                $errors['visit_date'] = 'Visit date cannot be changed once discharged.';
            }
            
            if ($request->visit_category != $patientVisit->visit_category) {
                $errors['visit_category'] = 'Visit category cannot be changed once discharged.';
            }
            
            // Allow Lab Only to change to other types even when discharged
            if ($request->visit_type != $patientVisit->visit_type && !$isCurrentLabOnly) {
                $errors['visit_type'] = 'Visit type cannot be changed once discharged, except Lab Only visits which can change to any type.';
            }
            
            // Only allow doctor changes if transitioning from Lab Only to another type
            if ($request->doctor != $patientVisit->doctor && !($isCurrentLabOnly && !$isNewLabOnly)) {
                $errors['doctor'] = 'Doctor cannot be changed once discharged, except when transitioning from Lab Only to consultation visit.';
            }
            
            if ($request->amount_cash != $patientVisit->amount_cash) {
                $errors['amount_cash'] = 'Amount cash cannot be changed once discharged.';
            }
            
            if ($request->amount_covered != $patientVisit->amount_covered) {
                $errors['amount_covered'] = 'Amount covered cannot be changed once discharged.';
            }
            
            if (!empty($errors)) {
                return redirect()->back()->withErrors($errors)->withInput();
            }
        }

        $request->validate($rules);

        // Handle Lab Only visit type transitions
        $updateData = $request->except(['visit_status', 'vital_status']);
        
        // If changing FROM Lab Only TO another type, ensure doctor is required and covered amount is allowed
        if ($isCurrentLabOnly && !$isNewLabOnly) {
            // Transitioning from Lab Only to consultation visit
            if (!$request->doctor) {
                return redirect()->back()
                    ->withErrors(['doctor' => 'Doctor is required when changing from Lab Only to consultation visit.'])
                    ->withInput();
            }
            
            // Create consultation record for the new visit type
            $consultationData = [
                'patient_id' => $patientVisit->patient,
                'doctor_id' => $request->doctor,
                'visit_id' => $patientVisit->id,
                'consultation_date' => now(),
                'status' => 'active'
            ];
            \App\Models\Consultation::create($consultationData);
        }
        
        // If changing TO Lab Only (which should not happen based on earlier validation, but safety check)
        if (!$isCurrentLabOnly && $isNewLabOnly) {
            $updateData['doctor'] = null;
            $updateData['amount_covered'] = 0.00;
            
            // Remove any existing consultation records
            \App\Models\Consultation::where('visit_id', $patientVisit->id)->delete();
        }
        
        // If staying as Lab Only, ensure lab-only constraints
        if ($isCurrentLabOnly && $isNewLabOnly) {
            $updateData['doctor'] = null;
            $updateData['amount_covered'] = 0.00;
            
            // Validate covered amount for Lab Only
            if ($request->amount_covered > 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['amount_covered' => 'Lab Only visits are cash payments only. Covered amount must be 0.']);
            }
        }

        // Only update allowed fields, excluding status fields that are managed automatically
        $patientVisit->update($updateData);

        return redirect()->route('patient_visits.index')
                         ->with('success', 'Patient visit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientVisit $patientVisit)
    {
        $user = Auth::user();
        
        // Only admins and super admins can delete visits
        if (!$user || (!$user->is_admin && !$user->is_super)) {
            return redirect()->route('patient_visits.index')
                           ->with('error', 'You do not have permission to delete patient visits.');
        }
        
        try {
            // The model's boot method will handle cascading deletes for investigations and prescriptions
            $patientVisit->delete();

            return redirect()->route('patient_visits.index')
                             ->with('success', 'Patient visit and all related records deleted successfully.');
                             
        } catch (\Exception $e) {
            Log::error('Error deleting patient visit: ' . $e->getMessage());
            
            return redirect()->route('patient_visits.index')
                           ->with('error', 'Failed to delete patient visit. Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get visit category for API
     */
    public function getVisitCategory(PatientVisit $visit)
    {
        return response()->json([
            'success' => true,
            'category_id' => $visit->visit_category,
            'category_name' => $visit->visitCategory->description ?? 'Unknown'
        ]);
    }

    /**
     * Get investigations partial view for a visit (for AJAX updates)
     */
    public function getInvestigationsPartial($visitId)
    {
        try {
            $visit = PatientVisit::with(['patientInfo'])->findOrFail($visitId);
            
            // Get investigations for this visit with pricing
            $investigations = \App\Models\Investigation::where('visit_id', $visitId)
                ->with([
                    'medicalService.serviceCategory',
                    'medicalService.currentPricing',
                    'doctor.user',
                    'templateResults' => function($query) {
                        $query->with('reportedBy')->orderBy('reported_at', 'desc');
                    }
                ])
                ->orderBy('ordered_at', 'desc')
                ->get();
            
            // Render just the investigations part
            $html = view('patient_visits.partials.investigations', compact('investigations', 'visit'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $investigations->count()
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching visit investigations partial: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load investigations'
            ], 500);
        }
    }

    /**
     * API endpoint to check if patient has active visit
     * Used for real-time validation in forms
     */
    public function checkPatientActiveVisitApi($patientId)
    {
        $activeVisitCheck = $this->checkPatientActiveVisit($patientId);
        
        return response()->json([
            'hasActiveVisit' => $activeVisitCheck['hasActiveVisit'],
            'message' => $activeVisitCheck['message'],
            'activeVisit' => $activeVisitCheck['activeVisit'] ? [
                'id' => $activeVisitCheck['activeVisit']->id,
                'visit_date' => $activeVisitCheck['activeVisit']->visit_date,
                'visit_type' => $activeVisitCheck['activeVisit']->visitType->description ?? 'Unknown',
                'doctor' => $activeVisitCheck['activeVisit']->doctor->user->name ?? 'Unknown',
                'status' => $activeVisitCheck['activeVisit']->visit_status_label
            ] : null
        ]);
    }

    /**
     * Check if a patient has an active visit that would prevent creating a new one
     * 
     * @param int $patientId
     * @return array ['hasActiveVisit' => bool, 'activeVisit' => PatientVisit|null, 'message' => string]
     */
    private function checkPatientActiveVisit($patientId)
    {
        $patient = \App\Models\Patient::find($patientId);
        
        if (!$patient) {
            return [
                'hasActiveVisit' => false,
                'activeVisit' => null,
                'message' => 'Patient not found'
            ];
        }

        $activeVisit = $patient->active_visit;
        
        if ($activeVisit) {
            return [
                'hasActiveVisit' => true,
                'activeVisit' => $activeVisit,
                'message' => "Patient has an active {$activeVisit->visitType->description} visit from {$activeVisit->visit_date} that needs to be completed first."
            ];
        }

        return [
            'hasActiveVisit' => false,
            'activeVisit' => null,
            'message' => 'No active visit found'
        ];
    }
    
    /**
     * Display visits with investigation readiness status
     * For Reception/Lab to see which visits have all results ready
     */
    public function readyInvResults(Request $request)
    {
        $query = PatientVisit::with([
            'patientInfo', 
            'doctorInfo.user',
            'investigations.medicalService',
            'investigations.results'
        ])->whereHas('investigations'); // Only visits that have investigations

        // Filter by date range (default to last 7 days)
        $dateFrom = $request->input('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        
        if ($dateFrom) {
            $query->whereDate('visit_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('visit_date', '<=', $dateTo);
        }

        // Filter by status
        $status = $request->input('status', 'all');
        
        $visits = $query->orderBy('visit_date', 'desc')->get();

        // Process each visit to determine investigation readiness
        $visits->each(function ($visit) {
            $investigations = $visit->investigations->where('status', '!=', 'cancelled');
            
            $visit->total_investigations = $investigations->count();
            $visit->completed_investigations = $investigations->where('status', 'resulted')->count();
            $visit->pending_investigations = $investigations->whereIn('status', ['ordered', 'collected', 'processing'])->count();
            
            // Determine if all investigations are ready
            $visit->all_results_ready = $visit->total_investigations > 0 && 
                                      $visit->completed_investigations == $visit->total_investigations;
            
            // Get investigation details for display
            $visit->investigation_details = $investigations->map(function ($investigation) {
                return [
                    'name' => $investigation->medicalService->name ?? 'Unknown Test',
                    'status' => $investigation->status,
                    'ordered_at' => $investigation->ordered_at,
                    'resulted_at' => $investigation->resulted_at,
                    'has_results' => $investigation->results && $investigation->results->count() > 0,
                    'priority' => $investigation->priority ?? 'routine'
                ];
            });
        });

        // Filter by readiness status if requested
        if ($status === 'ready') {
            $visits = $visits->where('all_results_ready', true);
        } elseif ($status === 'pending') {
            $visits = $visits->where('all_results_ready', false);
        }

        return view('ready_report', compact('visits', 'dateFrom', 'dateTo', 'status'));
    }
}
