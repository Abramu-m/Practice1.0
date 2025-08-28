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
        $query = Investigation::with(['patient', 'doctor', 'medicalService.serviceCategory', 'results']);

        // Apply role-based filtering
        $this->applyRoleBasedFiltering($query, $user);

        // Apply navigation filter type for doctors
        if ($request->filled('filter_type') && $user->role === 'doctor') {
            if ($request->filter_type === 'procedures') {
                $query->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', '=', 'Procedures');
                });
            } elseif ($request->filter_type === 'radiology') {
                $query->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', '=', 'Radiology');
                });
            }
        }

        // Apply standard filters
        if ($request->filled('service_category')) {
            $query->whereHas('medicalService', function($q) use ($request) {
                $q->where('service_category_id', $request->service_category);
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('patient_search')) {
            $search = $request->patient_search;
            
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%');
                  
                // Check if search looks like an MR number format and extract ID
                if (preg_match('/MR-\d{4}-(\d+)/', $search, $matches)) {
                    $q->orWhere('id', intval($matches[1]));
                } elseif (is_numeric($search)) {
                    // Also check for raw numeric ID
                    $q->orWhere('id', $search);
                }
            });
        }

        // Only show investigations from last 30 days
        $query->where('ordered_at', '>=', now()->subDays(30));

        $investigations = $query->orderBy('priority', 'desc')
                               ->orderBy('ordered_at', 'desc')
                               ->paginate(20);

        // Get role-specific service categories for filtering
        $serviceCategories = $this->getRoleSpecificServiceCategories($user, $request->input('filter_type'));
        $doctors = Doctor::active()->get();

        return view('procedures.index', compact('investigations', 'serviceCategories', 'doctors', 'user'));
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
        
        // Parse template fields to get additional metadata if available
        $templateFields = $resultTemplate->template_fields ?? [];
        $description = isset($templateFields['description']) 
            ? $templateFields['description'] 
            : 'Template-based results entry form';
            
        return [
            'type' => $resultTemplate->code,
            'title' => $resultTemplate->name,
            'description' => $description
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
