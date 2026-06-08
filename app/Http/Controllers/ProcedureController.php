<?php

namespace App\Http\Controllers;

use App\Models\Investigation;
use App\Models\InvestigationTemplateResult;
use App\Models\MedicalService;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\ResultTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProcedureController extends Controller
{
    /**
     * Apply role-based filtering to the investigations query
     */
    private function applyRoleBasedFiltering($query, $user)
    {
        // Only show paid investigations for all roles
        $query->where('is_paid', true);
        
        if ($user->role === 'nurse') {
            // Nurses see only Procedures service category
            $query->whereHas('medicalService.serviceCategory', function($q) {
                $q->where('name', '=', 'Procedures');
            });
        } elseif ($user->role === 'doctor') {
            // Doctors see both Procedures and Radiology (exclude Laboratory)
            $query->whereHas('medicalService.serviceCategory', function($q) {
                $q->whereIn('name', ['Procedures', 'Radiology']);
            });
        } elseif ($user->role === 'radiologist') {
            // Radiologists see only Radiology
            $query->whereHas('medicalService.serviceCategory', function($q) {
                $q->where('name', '=', 'Radiology');
            });
        } else {
            // Default: exclude Laboratory (original behavior for other roles)
            $query->whereHas('medicalService.serviceCategory', function($q) {
                $q->where('name', '!=', 'Laboratory');
            });
        }
    }

    /**
     * Get service categories based on user role for filter dropdown
     */
    private function getRoleSpecificServiceCategories($user, $filterType = null)
    {
        $categories = collect();
        
        if ($user->role === 'nurse') {
            // Nurses see only Procedures
            $categories = \App\Models\ServiceCategory::active()
                ->where('name', 'Procedures')
                ->get();
        } elseif ($user->role === 'doctor') {
            // Handle navigation filter for doctors
            if ($filterType === 'procedures') {
                $categories = \App\Models\ServiceCategory::active()
                    ->where('name', 'Procedures')
                    ->get();
            } elseif ($filterType === 'radiology') {
                $categories = \App\Models\ServiceCategory::active()
                    ->where('name', 'Radiology')
                    ->get();
            } else {
                // Default: Doctors see Procedures and Radiology
                $categories = \App\Models\ServiceCategory::active()
                    ->whereIn('name', ['Procedures', 'Radiology'])
                    ->get();
            }
        } elseif ($user->role === 'radiologist') {
            // Radiologists see only Radiology
            $categories = \App\Models\ServiceCategory::active()
                ->where('name', 'Radiology')
                ->get();
        } else {
            // Default: all except Laboratory
            $categories = \App\Models\ServiceCategory::active()
                ->where('name', '!=', 'Laboratory')
                ->get();
        }
        
        return $categories;
    }
    /**
     * Display procedures/investigations awaiting results
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($request->ajax()) {
            // Pre-resolve service IDs for the user's role + filter_type (avoids nested EXISTS)
            $roleCategories  = $this->getRoleSpecificServiceCategories($user, $request->input('filter_type'));
            $roleServiceIds  = MedicalService::whereIn('service_category_id', $roleCategories->pluck('id'))->pluck('id');

            // Date range — default to last 3 days; index-friendly (no DATE() wrapping)
            $dateFrom = $request->filled('date_from') ? $request->date_from : now()->subDays(2)->toDateString();
            $dateTo   = $request->filled('date_to')   ? $request->date_to   : now()->toDateString();

            $query = Investigation::with(['patient', 'doctor', 'medicalService.serviceCategory', 'results'])
                ->where('is_paid', true)
                ->whereIn('medical_service_id', $roleServiceIds)
                ->where('ordered_at', '>=', $dateFrom . ' 00:00:00')
                ->where('ordered_at', '<=', $dateTo   . ' 23:59:59');

            if ($request->filled('service_category')) {
                $catServiceIds = MedicalService::where('service_category_id', $request->service_category)->pluck('id');
                $query->whereIn('medical_service_id', $catServiceIds);
            }

            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('patient_search')) {
                $search     = $request->patient_search;
                $patientQ   = DB::table('patients')
                    ->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name',  'like', '%' . $search . '%');
                if (preg_match('/MR-\d{4}-(\d+)/', $search, $matches)) {
                    $patientQ->orWhere('id', intval($matches[1]));
                } elseif (is_numeric($search)) {
                    $patientQ->orWhere('id', $search);
                }
                $query->whereIn('patient_id', $patientQ->pluck('id'));
            }

            return DataTables::of($query)
                ->addColumn('mr_number', function ($investigation) {
                    $html = '<strong>' . e($investigation->patient->mr_number ?? 'N/A') . '</strong>';
                    if ($investigation->isOverdue()) {
                        $html .= '<br><span class="badge bg-danger">OVERDUE</span>';
                    }
                    return $html;
                })
                ->addColumn('patient_name', function ($investigation) {
                    if ($investigation->patient) {
                        return e($investigation->patient->first_name . ' ' . $investigation->patient->last_name);
                    }
                    return '<span class="text-muted">Unknown Patient</span>';
                })
                ->addColumn('age', function ($investigation) {
                    return e($investigation->formatted_age ?? 'N/A');
                })
                ->addColumn('ordered_by', function ($investigation) use ($user) {
                    if ($user->role !== 'doctor') {
                        if ($investigation->doctor) {
                            return 'Dr. ' . e($investigation->doctor->user->first_name . ' ' . $investigation->doctor->user->last_name);
                        }
                        return '<span class="text-muted">Unknown</span>';
                    }
                    return null;
                })
                ->addColumn('procedure_name', function ($investigation) {
                    if ($investigation->medicalService) {
                        $html = '<a href="' . route('procedures.show', $investigation) . '" class="text-decoration-none">';
                        $html .= '<strong>' . e($investigation->medicalService->name) . '</strong></a>';
                        if ($investigation->medicalService->requires_sample) {
                            $html .= '<br><small class="text-info">Sample: ' . e($investigation->medicalService->sample_type) . '</small>';
                        }
                        return $html;
                    }
                    return '<span class="text-muted">Unknown Service</span>';
                })
                ->addColumn('priority', function ($investigation) {
                    return '<span class="badge ' . e($investigation->priority_badge_class) . '">' . 
                           e(strtoupper($investigation->priority)) . '</span>';
                })
                ->addColumn('date', function ($investigation) {
                    return $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y') : 'N/A';
                })
                ->addColumn('time', function ($investigation) {
                    return $investigation->ordered_at ? $investigation->ordered_at->format('H:i') : 'N/A';
                })
                ->addColumn('status', function ($investigation) {
                    $statusText = ucfirst($investigation->status);
                    if ($investigation->status === 'ordered') {
                        $statusText = 'Ordered';
                    } elseif ($investigation->status === 'collected') {
                        $statusText = 'Sample Collected';
                    } elseif ($investigation->status === 'processing') {
                        $statusText = 'Processing';
                    } elseif ($investigation->status === 'resulted') {
                        $statusText = 'Resulted';
                    }
                    return '<span class="badge ' . e($investigation->status_badge_class) . '">' . e($statusText) . '</span>';
                })
                ->addColumn('result_status', function ($investigation) {
                    $result = $investigation->results->first();
                    if ($result) {
                        if ($result->form_status === 'draft') {
                            return '<span class="badge bg-secondary"><i class="fas fa-edit"></i> Draft</span>';
                        } elseif ($result->form_status === 'preliminary') {
                            return '<span class="badge bg-warning"><i class="fas fa-clock"></i> Preliminary</span>';
                        } elseif ($result->form_status === 'final') {
                            return '<span class="badge bg-success"><i class="fas fa-check"></i> Final</span>';
                        }
                    }
                    return '<span class="badge bg-light text-dark"><i class="fas fa-minus"></i> No Results</span>';
                })
                ->addColumn('actions', function ($investigation) use ($user) {
                    return view('procedures._actions', compact('investigation', 'user'))->render();
                })
                ->rawColumns(['mr_number', 'ordered_by', 'procedure_name', 'priority', 'status', 'result_status', 'actions'])
                ->orderColumn('ordered_at', function ($query, $order) {
                    $query->orderBy('priority', 'desc')->orderBy('ordered_at', $order);
                })
                ->make(true);
        }

        $serviceCategories = $this->getRoleSpecificServiceCategories($user, $request->input('filter_type'));
        $doctors  = Doctor::active()->get();
        $dateFrom = now()->subDays(2)->toDateString();
        $dateTo   = now()->toDateString();

        return view('procedures.index', compact('serviceCategories', 'doctors', 'user', 'dateFrom', 'dateTo'));
    }

    /**
     * Show procedure/investigation for result entry
     */
    public function show(Investigation $procedure)
    {
        $investigation = $procedure; // For consistency with view variable
        $investigation->load([
            'patient',
            'doctor', 
            'medicalService.serviceCategory',
            'results',
            'consultation'
        ]);

        // Check if this is a procedure type that needs special handling
        $procedureType = $this->determineProcedureType($investigation);

        // Get existing result data if available (for editing draft/preliminary results)
        $existingResult = $investigation->results()->first();
        $existingData = null;
        $editMode = false;
        
        if ($existingResult) {
            $existingData = $existingResult->form_data ?? [];
            $editMode = true;
            
            // Add result status info for the view
            $existingData['_result_status'] = $existingResult->form_status;
            $existingData['_result_id'] = $existingResult->id;
            $existingData['_created_at'] = $existingResult->created_at;
            $existingData['_updated_at'] = $existingResult->updated_at;
        }

        return view('procedures.show', compact('investigation', 'procedureType', 'existingData', 'editMode'));
    }

    /**
     * Store procedure results
     */
    public function storeResult(Request $request, $id)
    {
        $investigation = Investigation::findOrFail($id);
        
        // Check if existing result is final and prevent modification
        $existingResult = $investigation->results()->first();
        if ($existingResult && $existingResult->form_status === 'final') {
            return back()->with('error', 'Cannot modify final results. Final reports are locked for editing.');
        }
        
        // Get available result template codes from the database (ResultTemplate->medicalServices)
        $availableTemplateCodes = ResultTemplate::active()->pluck('code')->toArray();

        $request->validate([
            'result_type' => 'required|in:' . implode(',', $availableTemplateCodes),
            'action' => 'required|in:draft,submit,preliminary,final',
            // Dynamic validation based on result type
        ]);

        try {
            DB::beginTransaction();

            // Handle all result types with a single unified method
            $this->storeUnifiedResult($investigation, $request);

            // Update investigation status based on action
            $status = Investigation::STATUS_RESULTED;
            if ($request->action === 'draft') {
                $status = Investigation::STATUS_PROCESSING;
            }

            $investigation->update([
                'status' => $status,
                'resulted_at' => $request->action !== 'draft' ? now() : null,
                'resulted_by' => Auth::id()
            ]);

            DB::commit();

            $message = $request->action === 'draft' ? 'Draft saved successfully' : 'Procedure results submitted successfully';
            
            return redirect()->route('procedures.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store procedure results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()
                ->with('error', 'Failed to save results: ' . $e->getMessage());
        }
    }

    /**
     * Update existing procedure results
     */
    public function update(Request $request, Investigation $procedure)
    {
        $investigation = $procedure; // For consistency
        if ($investigation->status !== Investigation::STATUS_RESULTED) {
            return back()->with('error', 'Can only update resulted investigations');
        }

        $request->validate([
            'report' => 'nullable|string',
            'parameters' => 'nullable|array',
            'form_data' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            // Get the latest result to update
            $latestResult = $investigation->results()->latest('reported_at')->first();
            
            if ($latestResult) {
                // Update existing result
                $currentFormData = $latestResult->form_data ?? [];
                
                // Merge new data with existing
                if ($request->filled('report')) {
                    $currentFormData['report'] = $request->report;
                }
                
                if ($request->filled('parameters')) {
                    $currentFormData['parameters'] = $request->parameters;
                }
                
                if ($request->filled('form_data')) {
                    $currentFormData = array_merge($currentFormData, $request->form_data);
                }

                $latestResult->update([
                    'form_data' => $currentFormData,
                    'form_status' => $request->action ?? $latestResult->form_status,
                    'verified_by' => Auth::id(),
                    'verified_at' => now()
                ]);
            } else {
                // Create new result if none exists
                $formData = [];
                
                if ($request->filled('report')) {
                    $formData['report'] = $request->report;
                }
                
                if ($request->filled('parameters')) {
                    $formData['parameters'] = $request->parameters;
                }
                
                if ($request->filled('form_data')) {
                    $formData = array_merge($formData, $request->form_data);
                }

                $investigation->results()->create([
                    'template_name' => 'Updated Report',
                    'template_version' => '1.0',
                    'form_data' => $formData,
                    'form_status' => $request->action ?? 'final',
                    'reported_by' => Auth::id(),
                    'reported_at' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('procedures.show', $investigation)
                ->with('success', 'Results updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update results: ' . $e->getMessage());
        }
    }

    /**
     * Generate procedure report
     */
    public function report(Investigation $procedure)
    {
        $investigation = $procedure; // For consistency
        $investigation->load([
            'patient',
            'doctor',
            'medicalService.serviceCategory',
            'results' => function($query) {
                $query->with(['reportedBy', 'verifiedBy'])->orderBy('reported_at', 'desc');
            }
        ]);

        return view('procedures.report', compact('investigation'));
    }

    /**
     * Get normal study templates
     */
    public function getNormalStudies(Request $request)
    {
        $serviceId = $request->input('service_id');
        
        // This would be normal study templates - for now return sample data
        $normalStudies = [
            ['id' => 1, 'name' => 'Normal Chest X-Ray', 'template' => 'Heart size and shape are normal. Lung fields are clear bilaterally. No acute cardiopulmonary abnormality.'],
            ['id' => 2, 'name' => 'Normal ECG', 'template' => 'Normal sinus rhythm. Rate and intervals are within normal limits. No acute ST-T wave changes.'],
            ['id' => 3, 'name' => 'Normal Blood Count', 'template' => 'Complete blood count parameters are within normal reference ranges. No abnormal cells seen.']
        ];

        return response()->json($normalStudies);
    }

    /**
     * Unified method to store all types of procedure results
     */
    private function storeUnifiedResult(Investigation $investigation, Request $request)
    {
        // Get the result template information
        $resultTemplate = \App\Models\ResultTemplate::active()
            ->where('code', $request->result_type)
            ->first();
            
        $templateName = $resultTemplate ? $resultTemplate->name : ucwords(str_replace('_', ' ', $request->result_type));
        
        // Capture all form data except system fields
        $formData = $request->except(['_token', '_method', 'result_type', 'action', 'investigation_id']);
        
        // Get existing result if any to preserve existing data when updating
        $existingResult = $investigation->results()->first();
        
        // Merge with existing form data if updating (preserves existing data)
        if ($existingResult && $existingResult->form_data) {
            $existingFormData = $existingResult->form_data;
            // Merge new data with existing, giving priority to new data
            $formData = array_merge($existingFormData, $formData);
        }
        
        // Handle file uploads dynamically
        $formData = $this->handleFileUploads($request, $formData, $existingResult);
        
        // Store the result
        $investigation->results()->updateOrCreate(
            ['investigation_id' => $investigation->id],
            [
                'template_name' => $templateName,
                'template_version' => '1.0',
                'form_data' => $formData,
                'form_status' => $request->action === 'draft' ? 'draft' : ($request->action === 'preliminary' ? 'preliminary' : 'final'),
                'reported_by' => Auth::id(),
                'reported_at' => now()
            ]
        );
    }

    /**
     * Handle file uploads for any type of procedure result
     */
    private function handleFileUploads(Request $request, array $formData, $existingResult = null)
    {
        // Define common file upload field patterns
        $fileFields = [
            'result_image' => 'single',
            'primary_images' => 'multiple', 
            'additional_images' => 'multiple',
            'procedure_images' => 'multiple',
            'procedure_documents' => 'multiple'
        ];
        
        // Preserve existing files if no new ones uploaded
        if ($existingResult && $existingResult->form_data) {
            $existingFormData = $existingResult->form_data;
            foreach ($fileFields as $fieldName => $type) {
                if (!$request->hasFile($fieldName) && isset($existingFormData[$fieldName])) {
                    $formData[$fieldName] = $existingFormData[$fieldName];
                }
            }
        }
        
        // Handle new file uploads
        foreach ($fileFields as $fieldName => $type) {
            if ($request->hasFile($fieldName)) {
                if ($type === 'single') {
                    // Single file upload
                    $filePath = $request->file($fieldName)->store('procedure_files', 'public');
                    $formData[$fieldName] = $filePath;
                } else {
                    // Multiple file upload
                    $formData[$fieldName] = [];
                    foreach ($request->file($fieldName) as $file) {
                        $filePath = $file->store('procedure_files', 'public');
                        $formData[$fieldName][] = $filePath;
                    }
                }
            }
        }
        
        return $formData;
    }

    /**
     * Determine procedure type based on service result_template
     */
    private function determineProcedureType(Investigation $investigation)
    {
        $service = $investigation->medicalService;

        // Get the result template row
        $resultTemplate = $service->resultTemplate;
        
        if (!$service || !$resultTemplate) {
            // Fallback to default if template not found in database
            return [
                'type' => 'default',
                'title' => 'Default Procedure Results',
                'description' => 'Standard procedure results entry form'
            ];
        }
        
        return [
            'type' => $resultTemplate->code,
            'title' => $resultTemplate->name,
            'description' => $resultTemplate->description ?? 'Template-based results entry form'
        ];
    }

    /**
     * Get pending procedures statistics
     */
    public function statistics()
    {
        $user = Auth::user();
        
        // Base query with payment filter
        $baseQuery = Investigation::where('is_paid', true);
        
        // Apply role-based filtering to statistics
        $this->applyRoleBasedFiltering($baseQuery, $user);
        
        $stats = [
            'pending_procedures' => (clone $baseQuery)->whereIn('status', [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING])->count(),
            'urgent_pending' => (clone $baseQuery)->urgent()->whereIn('status', [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING])->count(),
            'overdue_procedures' => (clone $baseQuery)->whereIn('status', [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING])
                ->get()->filter->isOverdue()->count(),
            'completed_today' => (clone $baseQuery)->where('status', Investigation::STATUS_RESULTED)
                ->whereDate('resulted_at', today())->count(),
                
            'by_category' => (clone $baseQuery)->join('medical_services', 'investigations.medical_service_id', '=', 'medical_services.id')
                ->join('service_categories', 'medical_services.service_category_id', '=', 'service_categories.id')
                ->whereIn('investigations.status', [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING])
                ->select('service_categories.name', DB::raw('count(*) as count'))
                ->groupBy('service_categories.name')
                ->pluck('count', 'name'),
                
            'weekly_completion' => (clone $baseQuery)->where('status', Investigation::STATUS_RESULTED)
                ->where('resulted_at', '>=', now()->subWeek())
                ->select(DB::raw('DATE(resulted_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
                
            // Additional payment-related stats
            'total_paid_amount' => (clone $baseQuery)->sum('amount_paid'),
            'average_investigation_cost' => (clone $baseQuery)->avg('amount_paid')
        ];

        return response()->json($stats);
    }
}
