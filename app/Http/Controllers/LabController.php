<?php

namespace App\Http\Controllers;

use App\Models\PatientVisit;
use App\Models\Investigation;
use App\Models\InvestigationTemplateResult;
use App\Models\InvestigationConsumption;
use App\Models\MedicalService;
use App\Models\ServiceCategory;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\ResultTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class LabController extends Controller
{
    /**
     * Display patient visits with pending lab investigations
     */
    public function index(Request $request)
    {
        // Get filter data
        $doctors = Doctor::active()->get();
        $serviceCategories = ServiceCategory::active()
            ->where(function($q) {
                $q->where('name', 'LIKE', '%lab%')
                  ->orWhere('name', 'LIKE', '%investigation%')
                  ->orWhere('name', 'LIKE', '%pathology%')
                  ->orWhere('name', 'LIKE', '%hematology%')
                  ->orWhere('name', 'LIKE', '%biochemistry%')
                  ->orWhere('name', 'LIKE', '%microbiology%');
            })->get();

        if ($request->ajax()) {
            $dateFrom = $request->input('date_from', now()->subDays(2)->toDateString());
            $dateTo   = $request->input('date_to',   now()->toDateString());

            // Pre-resolve lab service IDs once (tiny tables — avoids 4-level nested EXISTS)
            $labServiceIds = MedicalService::whereIn('service_category_id',
                $serviceCategories->pluck('id')
            )->pluck('id');

            // Get qualifying visit IDs via indexed JOINs, scoped to date range
            $invQuery = DB::table('investigations')
                ->join('consultations', 'investigations.consultation_id', '=', 'consultations.id')
                ->join('patient_visits', 'consultations.visit_id', '=', 'patient_visits.id')
                ->whereIn('investigations.status', [
                    Investigation::STATUS_ORDERED,
                    Investigation::STATUS_COLLECTED,
                    Investigation::STATUS_PROCESSING,
                ])
                ->whereIn('investigations.medical_service_id', $labServiceIds)
                ->where('patient_visits.visit_date', '>=', $dateFrom)
                ->where('patient_visits.visit_date', '<=', $dateTo);

            if ($request->filled('priority')) {
                $invQuery->where('investigations.priority', $request->priority);
            }

            $visitIds = $invQuery->pluck('consultations.visit_id')->unique()->values();

            // Main query: primary-key lookup on the already-filtered visit IDs
            $query = PatientVisit::with([
                'patientInfo',
                'doctorInfo.user',
                'consultation.investigations.medicalService.serviceCategory',
            ])
            ->whereIn('id', $visitIds)
            ->orderBy('visit_date', 'desc');

            if ($request->filled('doctor_id')) {
                $query->where('doctor', $request->doctor_id);
            }

            if ($request->filled('patient_search')) {
                $search = $request->patient_search;
                $query->whereHas('patientInfo', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('legacy_mrn', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('patient_info', function ($visit) {
                    $html = '<div><strong>' . e($visit->patientInfo->first_name) . ' ' . e($visit->patientInfo->last_name) . '</strong>';
                    if ($visit->patientInfo->middle_name) {
                        $html .= ' ' . e($visit->patientInfo->middle_name);
                    }
                    $html .= '<br><small class="text-muted">MR #: ' . e($visit->patientInfo->mr_number ?? 'N/A') . ' | ';
                    $html .= 'Age: ' . e($visit->patientInfo->age ?? 'N/A') . ' | ';
                    $html .= 'Gender: ' . ucfirst(e($visit->patientInfo->gender ?? 'N/A')) . '</small></div>';
                    return $html;
                })
                ->addColumn('visit_date_formatted', function ($visit) {
                    return '<div>' . ($visit->visit_date ? $visit->visit_date->format('M d, Y') : 'N/A') . 
                           '<br><small class="text-muted">' . ($visit->visit_date ? $visit->visit_date->format('H:i A') : '') . '</small></div>';
                })
                ->addColumn('doctor_name', function ($visit) {
                    if (optional($visit->doctorInfo)->user) {
                        return 'Dr. ' . e(optional($visit->doctorInfo->user)->first_name) . ' ' . e(optional($visit->doctorInfo->user)->last_name);
                    }
                    return '<span class="text-muted">Not assigned</span>';
                })
                ->addColumn('investigations_info', function ($visit) {
                    $labInvestigations = collect();
                    if ($visit->consultation && $visit->consultation->investigations) {
                        $labInvestigations = $visit->consultation->investigations->filter(function($investigation) {
                            return $investigation->medicalService && 
                                   $investigation->medicalService->serviceCategory &&
                                   (
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'lab') ||
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'investigation') ||
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'pathology') ||
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'hematology') ||
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'biochemistry') ||
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'microbiology')
                                   ) &&
                                   in_array($investigation->status, ['ordered', 'collected', 'processing']);
                        });
                    }
                    
                    $urgentCount = $labInvestigations->whereIn('priority', ['urgent', 'stat'])->count();
                    $totalCount = $labInvestigations->count();
                    
                    $html = '<div><span class="badge bg-primary">' . $totalCount . ' investigations</span>';
                    if ($urgentCount > 0) {
                        $html .= '<br><span class="badge bg-danger mt-1">' . $urgentCount . ' urgent</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('priority_status', function ($visit) {
                    $labInvestigations = collect();
                    if ($visit->consultation && $visit->consultation->investigations) {
                        $labInvestigations = $visit->consultation->investigations->filter(function($investigation) {
                            return $investigation->medicalService && 
                                   $investigation->medicalService->serviceCategory &&
                                   (
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'lab') ||
                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'investigation')
                                   ) &&
                                   in_array($investigation->status, ['ordered', 'collected', 'processing']);
                        });
                    }
                    
                    $statuses = $labInvestigations->pluck('status')->unique();
                    $html = '';
                    foreach ($statuses as $status) {
                        $statusClass = match($status) {
                            'ordered' => 'warning',
                            'collected' => 'info',
                            'processing' => 'primary',
                            'resulted' => 'success',
                            'cancelled' => 'secondary',
                            default => 'secondary'
                        };
                        $count = $labInvestigations->where('status', $status)->count();
                        $html .= '<span class="badge bg-' . $statusClass . ' me-1">' . $count . ' ' . ucfirst($status) . '</span> ';
                    }
                    return $html;
                })
                ->addColumn('visit_status', function ($visit) {
                    return '<span class="badge ' . $visit->visit_status_badge_class . '">' . e($visit->visit_status_label) . '</span>';
                })
                ->addColumn('actions', function ($visit) {
                    return '<div class="btn-group" role="group">
                                <a href="' . route('lab.visits.investigations', $visit->id) . '" 
                                   class="btn btn-sm btn-primary" title="View Lab Investigations">
                                    <i class="fas fa-flask"></i> Lab Work
                                </a>
                                <a href="' . route('patient_visits.show', $visit->id) . '" 
                                   class="btn btn-sm btn-outline-info" title="View Full Visit Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['patient_info', 'visit_date_formatted', 'doctor_name', 'investigations_info', 'priority_status', 'visit_status', 'actions'])
                ->make(true);
        }

        $dateFrom = now()->subDays(2)->toDateString();
        $dateTo   = now()->toDateString();
        return view('lab.visits.index', compact('doctors', 'serviceCategories', 'dateFrom', 'dateTo'));
    }

    /**
     * Show investigations for a specific visit
     */
    public function showVisitInvestigations($visitId)
    {
        $visit = PatientVisit::with([
            'patientInfo', 
            'doctorInfo.user',
            'consultation.investigations.medicalService.serviceCategory',
            'consultation.investigations.doctor.user',
            'consultation.investigations.results',
            'consultation.investigations.orderedBy',
            'consultation.investigations.collectedBy',
            'consultation.investigations.resultedBy'
        ])->findOrFail($visitId);
        
        // Get lab investigations through consultation relationship
        $investigations = collect();
        if ($visit->consultation && $visit->consultation->investigations) {
            $investigations = $visit->consultation->investigations->filter(function($investigation) {
                return $investigation->medicalService && 
                       $investigation->medicalService->serviceCategory &&
                       (
                           str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'lab') ||
                           str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'investigation') ||
                           str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'pathology') ||
                           str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'hematology') ||
                           str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'biochemistry') ||
                           str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'microbiology')
                       );
            })->sortByDesc('ordered_at');
        }

        return view('lab.visits.investigations', compact('visit', 'investigations'));
    }

    /**
     * Show form to add results for an investigation
     */
    public function showResultForm(Request $request, $investigationId)
    {
        $investigation = Investigation::with([
            'patient',
            'doctor.user',
            'medicalService.serviceCategory',
            'medicalService.resultTemplate', // Load the result template relationship
            'results',
            'consultation.visit',
            'templateResults'
        ])->findOrFail($investigationId);

        // Check if this is a lab investigation

        if (!$investigation->medicalService || !$investigation->medicalService->serviceCategory) {
            return redirect()->route('lab.visits.index')
                ->with('error', 'This investigation does not have a valid medical service or category.');
        }
        
        // Check if the investigation is a medical service under a lab category
        $isLabInvestigation = $investigation->medicalService->serviceCategory->name === 'Laboratory';
        if (!$isLabInvestigation) {
            return redirect()->route('lab.visits.index')
                ->with('error', 'This investigation is not a laboratory test.');
        }

        // Determine the type of result form needed
        $resultType = $this->determineResultType($investigation);

        // Get return_to parameters
        $returnTo = $request->input('return_to');
        $investigationIdForReturn = $request->input('investigation_id');

        // Get the result template types and the human-readable template name
        $resultTemplateTypes = MedicalService::getResultTemplateTypes();
        
        // Get the template from the relationship
        $resultTemplate = $investigation->medicalService->resultTemplate;
        
        // Get the display name, with proper fallbacks
        if ($resultTemplate && $resultTemplate->code) {
            // Check if we have a mapping for this template code
            if (isset($resultTemplateTypes[$resultTemplate->code])) {
                $templateDisplayName = $resultTemplateTypes[$resultTemplate->code];
            } else {
                // Use the template name from the database
                $templateDisplayName = $resultTemplate->name;
            }
        } else {
            $templateDisplayName = 'Simple Lab Values';
        }

        $availableTemplates = \App\Models\ResultTemplate::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('lab.results.form', compact('investigation', 'resultType', 'returnTo', 'investigationIdForReturn', 'resultTemplateTypes', 'templateDisplayName', 'availableTemplates'));
    }

    /**
     * Store investigation results using template-based approach
     */
    public function storeResults(Request $request, $investigationId)
    {
        $investigation = Investigation::with(['consultation.visit', 'medicalService.resultTemplate'])->findOrFail($investigationId);

        $request->validate([
            'action' => 'required|in:draft,preliminary,final',
        ]);

        try {
            DB::beginTransaction();

            // Get the template from the relationship; fall back to manually selected template
            $resultTemplate = $investigation->medicalService->resultTemplate;

            if (!$resultTemplate && $request->filled('selected_template_code')) {
                $resultTemplate = \App\Models\ResultTemplate::where('code', $request->input('selected_template_code'))->first();
            }

            $templateCode = $resultTemplate ? $resultTemplate->code : 'single_numeric_lab';
            if (empty($templateCode) || $templateCode === 'none') {
                $templateCode = 'single_numeric_lab';
            }

            $templateName = $resultTemplate ? $resultTemplate->name : 'Single Numeric Lab Values';

            // Collect all template data from the request
            $templateData = [];
            $allRequestData = $request->all();
            
            // Extract template fields (those prefixed with 'template_')
            foreach ($allRequestData as $key => $value) {
                if (strpos($key, 'template_') === 0) {
                    $fieldName = str_replace('template_', '', $key);
                    $templateData[$fieldName] = $value;
                }
            }

            // Store the template-based result (update if one already exists for this investigation)
            $result = InvestigationTemplateResult::updateOrCreate(
                ['investigation_id' => $investigation->id],
                [
                    'template_name' => $templateName,
                    'template_version' => '1.0',
                    'form_data' => $templateData,
                    'form_status' => $request->action,
                    'metadata' => [
                        'template_code' => $templateCode,
                        'user_agent' => $request->header('User-Agent'),
                        'ip_address' => $request->ip(),
                        'submitted_at' => now()->toISOString(),
                        'form_fields_count' => count($templateData)
                    ],
                    'reported_by' => Auth::id(),
                    'reported_at' => now()
                ]
            );

            // Update investigation status
            $status = $request->action === 'draft' 
                ? Investigation::STATUS_PROCESSING 
                : Investigation::STATUS_RESULTED;

            $investigation->update([
                'status' => $status,
                'resulted_at' => $request->action !== 'draft' ? now() : null,
                'resulted_by' => Auth::id()
            ]);

            DB::commit();

            $message = match($request->action) {
                'draft' => 'Results saved as draft successfully',
                'preliminary' => 'Preliminary results submitted successfully',
                'final' => 'Final results submitted successfully',
                default => 'Results submitted successfully'
            };
            
            // Handle return_to parameter for proper redirection
            $returnTo = $request->input('return_to');
            
            if ($returnTo === 'investigations.show') {
                $investigationId = $request->input('investigation_id', $investigation->id);
                return redirect()->route('investigations.show', $investigationId)
                    ->with('success', $message);
            } elseif ($returnTo === 'investigations.index') {
                return redirect()->route('investigations.index')
                    ->with('success', $message);
            } else {
                // Default behavior - redirect to lab visits
                $visitId = $investigation->consultation && $investigation->consultation->visit 
                    ? $investigation->consultation->visit->id 
                    : $investigation->patient_id; // Fallback to patient investigations if no visit
                
                return redirect()->route('lab.visits.investigations', $visitId)
                    ->with('success', $message);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error saving results: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update investigation status (collected, processing, etc.)
     */
    public function updateStatus(Request $request, $investigationId)
    {
        $request->validate([
            'status' => 'required|in:collected,processing,resulted,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        $investigation = Investigation::findOrFail($investigationId);

        // Check stock availability and deduct when status is being changed to 'collected' or 'processing'
        if ($request->status === 'collected') {
            $stockCheck = $this->checkStockAvailability($investigation);
            
            if (!$stockCheck['can_proceed']) {
                // Get the stock location from the first detail item
                $stockLocation = 'Laboratory'; // Default fallback
                if (!empty($stockCheck['details']) && isset($stockCheck['details'][0]['stock_location'])) {
                    $stockLocation = $stockCheck['details'][0]['stock_location'];
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot collect investigation: Insufficient stock for required consumables.',
                    'stock_details' => $stockCheck['details'],
                    'stock_location' => $stockLocation
                ], 422);
            }

            // Deduct consumables from lab stock
            $this->deductConsumablesFromStock($investigation);
        }
        
        // Handle direct ordering to processing (for investigations that don't require samples)
        if ($request->status === 'processing' && $investigation->status === 'ordered') {
            $stockCheck = $this->checkStockAvailability($investigation);
            
            if (!$stockCheck['can_proceed']) {
                // Get the stock location from the first detail item
                $stockLocation = 'Laboratory'; // Default fallback
                if (!empty($stockCheck['details']) && isset($stockCheck['details'][0]['stock_location'])) {
                    $stockLocation = $stockCheck['details'][0]['stock_location'];
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot start processing: Insufficient stock for required consumables.',
                    'stock_details' => $stockCheck['details'],
                    'stock_location' => $stockLocation
                ], 422);
            }

            // Deduct consumables from lab stock (only if going directly from ordered to processing)
            $this->deductConsumablesFromStock($investigation);
        }

        $updateData = ['status' => $request->status];

        // Set appropriate timestamps
        switch ($request->status) {
            case 'collected':
                $updateData['collected_at'] = now();
                $updateData['collected_by'] = Auth::id();
                break;
            case 'resulted':
                $updateData['resulted_at'] = now();
                $updateData['resulted_by'] = Auth::id();
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = Auth::id();
                break;
        }

        // Add notes if provided
        if ($request->notes) {
            $updateData['notes'] = $investigation->notes 
                ? $investigation->notes . "\n\n" . now()->format('Y-m-d H:i') . " - " . $request->notes
                : now()->format('Y-m-d H:i') . " - " . $request->notes;
        }

        $investigation->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Investigation status updated successfully.',
            'status' => $investigation->status
        ]);
    }

    /**
     * Determine the appropriate store location type based on medical service category
     */
    private function getLocationTypeFromServiceCategory($investigation)
    {
        $categoryName = $investigation->medicalService->serviceCategory->name;
        
        // Check for laboratory services
        if ($categoryName === 'Laboratory') {
            return 'laboratory';
        }
        
        // Check for radiology services
        if ($categoryName === 'Radiology') {
            return 'radiology';
        }
        
        // Check for procedure/nursing services
        if ($categoryName === 'Procedures') {
            return 'nursing';
        }
        
        // Default to laboratory if category is unclear
        return 'laboratory';
    }

    /**
     * Check stock availability for investigation consumables
     */
    private function checkStockAvailability($investigation)
    {
        $medicalService = $investigation->medicalService;
        
        // Get the appropriate location type based on service category
        $locationType = $this->getLocationTypeFromServiceCategory($investigation);
        
        // Get the location for stock checking
        $location = \App\Models\StoreLocation::where('type', $locationType)
            ->where('is_active', true)
            ->first();
            
        if (!$location) {
            // If no appropriate location found, return cannot proceed
            return [
                'can_proceed' => false,
                'details' => [
                    [
                        'medication_name' => 'System Error',
                        'required_quantity' => 0,
                        'available_quantity' => 0,
                        'is_available' => false,
                        'is_optional' => false,
                        'error' => "No active {$locationType} location found"
                    ]
                ]
            ];
        }
        
        $consumables = \App\Models\InvestigationConsumable::where('medical_service_id', $medicalService->id)
            ->where('is_active', true)
            ->with('medication')
            ->get();

        $stockDetails = [];
        $canProceed = true;

        foreach ($consumables as $consumable) {
            // Check stock in the appropriate location
            $totalStock = \App\Models\StoreLocationStock::where('medication_id', $consumable->medication_id)
                ->where('location_id', $location->id)  // Check in determined location
                ->where('status', 'active')
                ->where('quantity', '>', 0)
                ->sum('quantity');

            $isAvailable = $totalStock >= $consumable->quantity_required;

            if (!$isAvailable && !$consumable->is_optional) {
                $canProceed = false;
            }

            $stockDetails[] = [
                'medication_name' => $consumable->medication->generic_name,
                'required_quantity' => $consumable->quantity_required,
                'available_quantity' => $totalStock,
                'is_available' => $isAvailable,
                'is_optional' => $consumable->is_optional,
                'stock_location' => $location->name
            ];
        }

        return [
            'can_proceed' => $canProceed,
            'details' => $stockDetails
        ];
    }

    /**
     * Deduct consumables from appropriate location stock when investigation is collected
     */
    private function deductConsumablesFromStock($investigation)
    {
        $medicalService = $investigation->medicalService;
        
        // Get the appropriate location type based on service category
        $locationType = $this->getLocationTypeFromServiceCategory($investigation);
        
        // Get the appropriate location
        $location = \App\Models\StoreLocation::where('type', $locationType)
            ->where('is_active', true)
            ->first();
            
        if (!$location) {
            throw new \Exception("No active {$locationType} location found for stock deduction");
        }
        
        $consumables = \App\Models\InvestigationConsumable::where('medical_service_id', $medicalService->id)
            ->where('is_active', true)
            ->with('medication')
            ->get();

        // Array to collect batch information used
        $batchesUsed = [];

        foreach ($consumables as $consumable) {
            $quantityToDeduct = $consumable->quantity_required;
            
            // Skip optional consumables if there's insufficient stock
            if ($consumable->is_optional) {
                $availableStock = \App\Models\StoreLocationStock::where('medication_id', $consumable->medication_id)
                    ->where('location_id', $location->id)
                    ->where('status', 'active')
                    ->where('quantity', '>', 0)
                    ->sum('quantity');
                    
                if ($availableStock < $quantityToDeduct) {
                    continue; // Skip this optional consumable
                }
            }
            
            // Get stock records ordered by expiry date (FIFO)
            $stockRecords = \App\Models\StoreLocationStock::where('medication_id', $consumable->medication_id)
                ->where('location_id', $location->id)
                ->where('status', 'active')
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            
            $remainingToDeduct = $quantityToDeduct;
            
            foreach ($stockRecords as $stockRecord) {
                if ($remainingToDeduct <= 0) break;
                
                $quantityUsedFromThisRecord = 0;
                
                if ($stockRecord->quantity >= $remainingToDeduct) {
                    // This record has enough stock to cover the remaining requirement
                    $quantityUsedFromThisRecord = $remainingToDeduct;
                    $stockRecord->quantity -= $remainingToDeduct;
                    $stockRecord->save();
                    $remainingToDeduct = 0;
                } else {
                    // This record doesn't have enough, use all of it
                    $quantityUsedFromThisRecord = $stockRecord->quantity;
                    $remainingToDeduct -= $stockRecord->quantity;
                    $stockRecord->quantity = 0;
                    $stockRecord->status = 'consumed'; // Mark as consumed
                    $stockRecord->save();
                }
                
                // Collect batch information for investigation tracking
                if ($quantityUsedFromThisRecord > 0) {
                    $batchesUsed[] = [
                        'medication_id' => $consumable->medication_id,
                        'medication_name' => $consumable->medication->generic_name ?? $consumable->medication->brand_name ?? 'Unknown',
                        'batch' => $stockRecord->batch_number ?? 'N/A',
                        'expiry' => $stockRecord->expiry_date ? $stockRecord->expiry_date->format('Y-m-d') : null,
                        'location_id' => $location->id,
                        'location_name' => $location->name,
                        'quantity_used' => $quantityUsedFromThisRecord,
                        'unit_cost' => $stockRecord->unit_cost ?? 0.00,
                        'consumed_at' => now()->toISOString()
                    ];
                }
                
                // Log the consumption in investigation_consumptions table
                \App\Models\InvestigationConsumption::create([
                    'investigation_id' => $investigation->id,
                    'medication_id' => $consumable->medication_id,
                    'batch_number' => $stockRecord->batch_number ?? 'N/A',
                    'quantity_used' => $quantityUsedFromThisRecord,
                    'cost_per_unit' => $stockRecord->unit_cost ?? 0.00,
                    'consumed_from_location_id' => $location->id,
                    'consumed_by' => Auth::id(),
                    'consumed_at' => now(),
                    'notes' => "Automatic consumption during collection of investigation: {$investigation->medicalService->name}"
                ]);
            }
            
            // Update the main medication stock quantity after deduction
            $totalStock = \App\Models\StoreLocationStock::where('location_id', $location->id)
                ->where('medication_id', $consumable->medication_id)
                ->where('status', 'active')
                ->sum('quantity');

            // Update the medication's stock_quantity field
            $consumable->medication->update([
                'stock_quantity' => $totalStock
            ]);
            
            if ($remainingToDeduct > 0 && !$consumable->is_optional) {
                throw new \Exception("Insufficient stock for {$consumable->medication->generic_name}. Could not deduct {$remainingToDeduct} units.");
            }
        }

        // Update investigation with batch information used
        if (!empty($batchesUsed)) {
            $investigation->update([
                'batches_used' => $batchesUsed
            ]);
        }
    }

    /**
     * API endpoint to check stock for a specific investigation
     */
    public function checkInvestigationStock($investigationId)
    {
        $investigation = Investigation::findOrFail($investigationId);
        $stockCheck = $this->checkStockAvailability($investigation);
        
        // Get the stock location from the investigation's service category
        $locationType = $this->getLocationTypeFromServiceCategory($investigation);
        $location = \App\Models\StoreLocation::where('type', $locationType)
            ->where('is_active', true)
            ->first();
            
        $stockLocation = $location ? $location->name : 'Laboratory'; // Default fallback
        
        return response()->json([
            'success' => true,
            'can_proceed' => $stockCheck['can_proceed'],
            'details' => $stockCheck['details'],
            'stock_location' => $stockLocation,
            'message' => $stockCheck['can_proceed'] 
                ? 'All required consumables are available in ' . $stockLocation
                : 'Some required consumables are not available in ' . $stockLocation
        ]);
    }

    /**
     * Get lab statistics for dashboard
     */
    public function getStatistics()
    {
        $stats = [
            'pending_collection' => Investigation::whereIn('status', [Investigation::STATUS_ORDERED])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%lab%');
                })->count(),
            
            'pending_results' => Investigation::whereIn('status', [
                Investigation::STATUS_COLLECTED, 
                Investigation::STATUS_PROCESSING
            ])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%lab%');
                })->count(),
            
            'completed_today' => Investigation::where('status', Investigation::STATUS_RESULTED)
                ->whereDate('resulted_at', today())
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%lab%');
                })->count(),
            
            'urgent_investigations' => Investigation::whereIn('priority', ['urgent', 'stat'])
                ->whereIn('status', [
                    Investigation::STATUS_ORDERED,
                    Investigation::STATUS_COLLECTED,
                    Investigation::STATUS_PROCESSING
                ])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%lab%');
                })->count()
        ];

        return response()->json($stats);
    }

    /**
     * Determine the type of result form needed based on the medical service's result template
     */
    private function determineResultType($investigation)
    {
        $service = $investigation->medicalService;
        
        // Check if the service has a result template assigned
        if ($service && $service->result_template && $service->result_template !== 'none') {
            // Get the result template from the database
            $resultTemplate = \App\Models\ResultTemplate::active()
                ->where('code', $service->result_template)
                ->first();
                
            if ($resultTemplate) {
                return $resultTemplate->code;
            }
        }
        
        // Fallback to heuristic-based determination for services without templates
        $serviceName = strtolower($investigation->medicalService->name);
        
        // Check for simple lab values
        if (preg_match('/(blood count|cbc|fbc|glucose|urea|creatinine|electrolytes|liver function|lipid)/i', $serviceName)) {
            return 'simple_lab';
        }
        
        // Check for complex reports
        if (preg_match('/(culture|sensitivity|microscopy|histology|cytology)/i', $serviceName)) {
            return 'general_lab';
        }
        
        // Check for specific test types
        if (preg_match('/(cd4|cd8|viral load)/i', $serviceName)) {
            return 'cd4';
        }
        
        if (preg_match('/(tb|tuberculosis|afb|acid fast|genexpert|xpert)/i', $serviceName)) {
            return 'tb';
        }
        
        return 'simple_lab'; // Default to simple lab template
    }

    /**
     * View a specific template result
     */
    public function viewTemplateResult($resultId)
    {
        $result = InvestigationTemplateResult::with([
            'investigation.patient',
            'investigation.medicalService',
            'investigation.doctor.user',
            'investigation.consultation.visit',
            'investigation.visit.patientInfo',
            'investigation.visit.doctorInfo',
            'reportedBy',
            'verifiedBy'
        ])->findOrFail($resultId);

        return view('lab.results.view', compact('result'));
    }

    /**
     * View a specific template result for modal display
     */
    public function viewTemplateResultModal($resultId)
    {
        try {
            $result = InvestigationTemplateResult::with([
                'investigation.patient',
                'investigation.medicalService',
                'investigation.doctor.user',
                'investigation.consultation.visit',
                'investigation.visit.patientInfo',
                'investigation.visit.doctorInfo',
                'reportedBy',
                'verifiedBy'
            ])->findOrFail($resultId);

            return view('lab.results.modal', compact('result'));
        } catch (\Exception $e) {
            Log::error('Error in viewTemplateResultModal:', [
                'result_id' => $resultId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * View investigation results - redirects to the most appropriate result
     */
    public function viewInvestigationResults($investigationId)
    {
        $investigation = Investigation::with([
            'templateResults' => function($query) {
                $query->orderBy('reported_at', 'desc');
            },
            'patient',
            'consultation.visit'
        ])->findOrFail($investigationId);

        // Check if there are any template results
        if ($investigation->templateResults->isEmpty()) {
            return redirect()->route('lab.results.form', $investigationId)
                ->with('info', 'No results found for this investigation. You can add results using the form.');
        }

        // Try to find the final result first
        $finalResult = $investigation->templateResults->where('form_status', 'final')->first();
        
        if ($finalResult) {
            // Redirect to the final result
            return redirect()->route('lab.template-results.view', $finalResult->id);
        }

        // If no final result, get the most recent result
        $latestResult = $investigation->templateResults->first(); // Already ordered by reported_at desc
        
        if ($latestResult) {
            return redirect()->route('lab.template-results.view', $latestResult->id);
        }

        // Fallback (shouldn't happen due to isEmpty check above)
        return redirect()->route('lab.results.form', $investigationId)
            ->with('info', 'No results found for this investigation.');
    }

    /**
     * View all investigation results for the visit
     */
    public function viewVisitResultsModal($visitId)
    {
        try {
            $visit = PatientVisit::with(['patientInfo', 'doctorInfo.user'])->findOrFail($visitId);
            $results = InvestigationTemplateResult::with([
                'investigation.medicalService',
                'reportedBy',
                'verifiedBy'
            ])->whereHas('investigation.consultation.visit', function($query) use ($visitId) {
                $query->where('id', $visitId);
            })->get();

            return view('lab.print.modal', compact('results', 'visit'));
        } catch (\Exception $e) {
            Log::error('Error in viewVisitResultsModal:', [
                'visit_id' => $visitId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate PDF for all investigation results for a visit
     */
    public function downloadVisitResultsPdf($visitId)
    {
        try {
            $visit = PatientVisit::with(['patientInfo', 'doctorInfo.user'])->findOrFail($visitId);
            $results = InvestigationTemplateResult::with([
                'investigation.medicalService',
                'reportedBy',
                'verifiedBy'
            ])->whereHas('investigation.consultation.visit', function($query) use ($visitId) {
                $query->where('id', $visitId);
            })->get();

            $pdf = Pdf::loadView('lab.print.modal', compact('results', 'visit'))
                ->setPaper('a4');

            return $pdf->stream('visit_results_'.$visitId.'.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating visit results PDF:', [
                'visit_id' => $visitId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Failed to generate PDF');
        }
    }

}
