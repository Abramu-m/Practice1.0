<?php

namespace App\Http\Controllers;

use App\Models\Investigation;
use App\Models\MedicalService;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class InvestigationController extends Controller
{
    /**
     * Display a listing of investigations
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Investigation::with(['patient', 'doctor', 'medicalService.serviceCategory'])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'Laboratory');
                });

            // Apply filters
            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            if ($request->filled('patient_search')) {
                $query->whereHas('patient', function($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->patient_search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->patient_search . '%')
                      ->orWhere('patient_id', 'like', '%' . $request->patient_search . '%');
                });
            }

            if ($request->filled('service_category')) {
                $query->whereHas('medicalService', function($q) use ($request) {
                    $q->where('service_category_id', $request->service_category);
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('ordered_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('ordered_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addColumn('id_display', function ($investigation) {
                    $html = '<strong>#' . e($investigation->id) . '</strong>';
                    if ($investigation->isOverdue()) {
                        $html .= '<span class="badge bg-danger ms-1">OVERDUE</span>';
                    }
                    return $html;
                })
                ->addColumn('patient_display', function ($investigation) {
                    if ($investigation->patient) {
                        $html = '<div><strong>' . e($investigation->patient->first_name . ' ' . $investigation->patient->last_name) . '</strong><br>';
                        $html .= '<small class="text-muted">' . e($investigation->patient->mr_number) . '</small></div>';
                        return $html;
                    }
                    return '<span class="text-muted">Unknown Patient</span>';
                })
                ->addColumn('investigation_display', function ($investigation) {
                    if ($investigation->medicalService) {
                        $html = '<div><strong>' . e($investigation->medicalService->name) . '</strong><br>';
                        $html .= '<small class="text-muted">' . e($investigation->medicalService->code) . '</small>';
                        if ($investigation->medicalService->requires_sample) {
                            $html .= '<br><span class="badge bg-info">Sample: ' . e($investigation->medicalService->sample_type) . '</span>';
                        }
                        $html .= '</div>';
                        return $html;
                    }
                    return '<span class="text-muted">Unknown Service</span>';
                })
                ->addColumn('doctor_display', function ($investigation) {
                    if ($investigation->doctor) {
                        return 'Dr. ' . e($investigation->doctor->user->first_name . ' ' . $investigation->doctor->user->last_name);
                    }
                    return '<span class="text-muted">Unknown Doctor</span>';
                })
                ->addColumn('priority', function ($investigation) {
                    return '<span class="badge ' . e($investigation->priority_badge_class) . '">' . 
                           e($investigation->priority_label) . '</span>';
                })
                ->addColumn('status', function ($investigation) {
                    return '<span class="badge ' . e($investigation->status_badge_class) . '">' . 
                           e($investigation->status_label) . '</span>';
                })
                ->addColumn('ordered_date', function ($investigation) {
                    $html = $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y H:i') : 'N/A';
                    if ($investigation->formatted_age) {
                        $html .= '<br><small class="text-muted">' . e($investigation->formatted_age) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('price_display', function ($investigation) {
                    $html = '<div><strong>' . e($investigation->formatted_total_price) . '</strong>';
                    if ($investigation->insurance_covered_amount > 0) {
                        $html .= '<br><small class="text-success">Covered: $' . number_format($investigation->insurance_covered_amount, 2) . '</small>';
                        $html .= '<br><small class="text-info">Effective: ' . e($investigation->formatted_effective_price) . '</small>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('actions', function ($investigation) {
                    return view('investigations._actions', compact('investigation'))->render();
                })
                ->rawColumns(['id_display', 'patient_display', 'investigation_display', 'doctor_display', 'priority', 'status', 'price_display', 'ordered_date', 'actions'])
                ->orderColumn('ordered_at', function ($query, $order) {
                    $query->orderBy('ordered_at', $order);
                })
                ->make(true);
        }

        // Get filter options
        $doctors = User::whereIn('id', function($query) {
            $query->select('doctor_id')->from('investigations')->distinct();
        })->get();
        $serviceCategories = \App\Models\ServiceCategory::active()->get();

        return view('investigations.index', compact('doctors', 'serviceCategories'));
    }

    /**
     * Show the form for creating a new investigation
     */
    public function create(Request $request)
    {
        $consultationId = $request->input('consultation_id');
        $consultation = $consultationId ? Consultation::with('patient')->findOrFail($consultationId) : null;
        
        $medicalServices = MedicalService::active()->with('serviceCategory')->get();
        $doctors = User::whereIn('id', function($query) {
            $query->select('doctor_id')->from('investigations')->distinct();
        })->get();
        $patients = Patient::active()->get();

        return view('investigations.create', compact('consultation', 'medicalServices', 'doctors', 'patients'));
    }

    /**
     * Store a newly created investigation
     */
    public function store(Request $request)
    {
        // Check if this is a batch request (from Lab Modal)
        if ($request->has('services') && is_array($request->services)) {
            return $this->storeBatchInvestigations($request);
        }

        // Single investigation request
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:patient_visits,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'doctor_id' => 'nullable|exists:doctors,doctor_id',
            'medical_service_id' => 'required|exists:medical_services,id',
            'quantity' => 'required|numeric|min:1|max:10',
            'priority' => 'required|in:routine,urgent,stat',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $service = MedicalService::with(['currentPricing', 'serviceCategory'])->findOrFail($request->medical_service_id);
            
            // Get patient information
            $patient = Patient::findOrFail($request->patient_id);
            
            // Get doctor information from visit if not provided
            $doctorId = $request->doctor_id;
            if (!$doctorId && $request->visit_id) {
                $visit = \App\Models\PatientVisit::findOrFail($request->visit_id);
                $doctorId = $visit->doctor; // For Lab Only visits, this will be null, which is fine
            }
            
            // Get pricing information - use the API response structure
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
            
            $quantity = (int) ($request->quantity ?? 1);
            
            $investigation = Investigation::create([
                'patient_id' => $patient->id,
                'visit_id' => $request->visit_id,
                'consultation_id' => $request->consultation_id,
                'doctor_id' => $doctorId,
                'medical_service_id' => $request->medical_service_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
                'insurance_covered_amount' => $insuranceCoveredAmount * $quantity,
                'priority' => $request->priority ?? 'routine',
                'notes' => $request->notes,
                'status' => Investigation::STATUS_ORDERED,
                'ordered_at' => now(),
                'ordered_by' => Auth::id()
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Investigation ordered successfully',
                    'data' => $investigation->load(['medicalService', 'patient'])
                ]);
            }

            return redirect()->route('investigations.show', $investigation)
                ->with('success', 'Investigation ordered successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to order investigation: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Failed to order investigation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified investigation
     */
    public function show($id)
    {
        try {
            $investigation = Investigation::with([
                'patient',
                'doctor',
                'medicalService.serviceCategory',
                'consultation',
                'results'
            ])->findOrFail($id);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $investigation->id,
                        'service_name' => $investigation->medicalService->name ?? 'Unknown Service',
                        'service_code' => $investigation->medicalService->code ?? '',
                        'service_category' => $investigation->medicalService->serviceCategory->name ?? 'Unknown Category',
                        'form_type' => $investigation->medicalService->form_type ?? null,
                        'service_form_type' => $investigation->medicalService->form_type ?? null,
                        'result_template' => $investigation->medicalService->result_template ?? null,
                        'consultation_id' => $investigation->consultation_id,
                        'price' => $investigation->unit_price,
                        'quantity' => $investigation->quantity,
                        'total_price' => $investigation->unit_price * $investigation->quantity,
                        'priority' => $investigation->priority,
                        'priority_label' => ucfirst($investigation->priority),
                        'status' => $investigation->status,
                        'status_label' => $investigation->status_label ?? ucfirst(str_replace('_', ' ', $investigation->status)),
                        'notes' => $investigation->notes,
                        'clinical_data' => $investigation->clinical_data,
                        'created_at' => $investigation->created_at->toISOString(),
                        'ordered_at' => $investigation->ordered_at ? $investigation->ordered_at->toISOString() : null,
                        'collected_at' => $investigation->collected_at ? $investigation->collected_at->toISOString() : null,
                        'resulted_at' => $investigation->resulted_at ? $investigation->resulted_at->toISOString() : null,
                        'patient' => $investigation->patient ? [
                            'id' => $investigation->patient->id,
                            'name' => $investigation->patient->first_name . ' ' . $investigation->patient->last_name,
                            'patient_id' => $investigation->patient->patient_id ?? $investigation->patient->id
                        ] : null,
                        'doctor' => $investigation->doctor ? [
                            'id' => $investigation->doctor->id,
                            'name' => 'Dr. ' . $investigation->doctor->first_name . ' ' . $investigation->doctor->last_name
                        ] : null,
                        'results' => $investigation->results ? $investigation->results->count() : 0,
                        'has_results' => $investigation->results && $investigation->results->count() > 0
                    ]
                ]);
            }
            
            return view('investigations.show', compact('investigation'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investigation not found'
                ], 404);
            }
            
            return redirect()->back()->with('error', 'Investigation not found');
        }
    }

    /**
     * Show the form for editing the specified investigation
     */
    public function edit(Investigation $investigation)
    {
        // Only allow editing if not yet collected
        if (in_array($investigation->status, [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING, Investigation::STATUS_RESULTED])) {
            return redirect()->route('investigations.show', $investigation)
                ->with('error', 'Cannot edit investigation that has been collected or processed');
        }

        $medicalServices = MedicalService::active()->with('serviceCategory')->get();
        $doctors = Doctor::active()->get();

        return view('investigations.edit', compact('investigation', 'medicalServices', 'doctors'));
    }

    /**
     * Update the specified investigation
     */
    public function update(Request $request, Investigation $investigation)
    {
        // Only allow editing if not yet collected
        if (in_array($investigation->status, [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING, Investigation::STATUS_RESULTED])) {
            return redirect()->route('investigations.show', $investigation)
                ->with('error', 'Cannot edit investigation that has been collected or processed');
        }

        $request->validate([
            'medical_service_id' => 'required|exists:medical_services,id',
            'quantity' => 'required|numeric|min:1|max:10',
            'priority' => 'required|in:routine,urgent,stat',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $service = MedicalService::findOrFail($request->medical_service_id);

            $investigation->update([
                'medical_service_id' => $request->medical_service_id,
                'quantity' => $request->quantity,
                'unit_price' => $service->price,
                'insurance_covered_amount' => $service->insurance_covered_amount * $request->quantity,
                'priority' => $request->priority,
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('investigations.show', $investigation)
                ->with('success', 'Investigation updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update investigation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified investigation (actually delete it)
     */
    public function destroy(Investigation $investigation)
    {
        // Only allow deletion if not yet collected and not paid
        if (in_array($investigation->status, [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING, Investigation::STATUS_RESULTED])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete investigation that has been collected or processed'
                ], 422);
            }
            return redirect()->route('investigations.show', $investigation)
                ->with('error', 'Cannot delete investigation that has been collected or processed');
        }

        // Don't allow deletion if investigation is paid
        if ($investigation->is_paid) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete paid investigation'
                ], 422);
            }
            return redirect()->route('investigations.show', $investigation)
                ->with('error', 'Cannot delete paid investigation');
        }

        try {
            // Actually delete the investigation
            $investigation->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Investigation deleted successfully'
                ]);
            }

            return redirect()->route('investigations.index')
                ->with('success', 'Investigation deleted successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete investigation: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to delete investigation: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified investigation (set status to cancelled)
     */
    public function cancel(Investigation $investigation)
    {
        // Only allow cancellation if not yet collected
        if (in_array($investigation->status, [Investigation::STATUS_COLLECTED, Investigation::STATUS_PROCESSING, Investigation::STATUS_RESULTED])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel investigation that has been collected or processed'
                ], 422);
            }
            return redirect()->route('investigations.show', $investigation)
                ->with('error', 'Cannot cancel investigation that has been collected or processed');
        }

        try {
            // Set status to cancelled
            $investigation->update([
                'status' => Investigation::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Investigation cancelled successfully'
                ]);
            }

            return redirect()->route('investigations.index')
                ->with('success', 'Investigation cancelled successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel investigation: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to cancel investigation: ' . $e->getMessage());
        }
    }

    /**
     * Update investigation status
     */
    public function updateStatus(Request $request, Investigation $investigation)
    {
        $request->validate([
            'status' => 'required|in:ordered,paid,collected,processing,resulted,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Handle 'paid' action separately - it only updates payment fields, not status
            if ($request->status === 'paid') {
                $updateData = [
                    'is_paid' => true,
                    'paid_at' => now(),
                    'paid_by' => Auth::id()
                ];
            } else {
                // For actual status changes
                $updateData = [
                    'status' => $request->status
                ];

                // Set appropriate timestamps and user
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
            }

            if ($request->filled('notes')) {
                $updateData['notes'] = $investigation->notes . "\n\n" . now()->format('Y-m-d H:i') . " - " . $request->notes;
            }

            $investigation->update($updateData);

            // Handle AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Investigation status updated successfully',
                    'data' => [
                        'status' => $investigation->status,
                        'updated_at' => $investigation->updated_at->toISOString()
                    ]
                ]);
            }

            return redirect()->route('investigations.show', $investigation)
                ->with('success', 'Investigation status updated successfully');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Get investigation statistics
     */
    public function statistics(Request $request)
    {
        $stats = [
            'total_investigations' => Investigation::count(),
            'pending_investigations' => Investigation::pending()->count(),
            'completed_investigations' => Investigation::completed()->count(),
            'urgent_investigations' => Investigation::urgent()->count(),
            'overdue_investigations' => Investigation::active()->get()->filter->isOverdue()->count(),
            
            'by_status' => Investigation::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
                
            'by_priority' => Investigation::select('priority', DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->pluck('count', 'priority'),
                
            'monthly_trends' => Investigation::select(
                    DB::raw('MONTH(ordered_at) as month'),
                    DB::raw('YEAR(ordered_at) as year'),
                    DB::raw('count(*) as count')
                )
                ->whereYear('ordered_at', now()->year)
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
        ];

        return view('investigations.statistics', compact('stats'));
    }

    /**
     * Store multiple investigations (batch) - used for Lab Modal
     */
    protected function storeBatchInvestigations(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'required|exists:patient_visits,id',
            'services' => 'required|array|min:1',
            'services.*.medical_service_id' => 'required|exists:medical_services,id',
            'services.*.notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $patient = Patient::findOrFail($request->patient_id);
            $createdInvestigations = [];

            foreach ($request->services as $serviceData) {
                $service = MedicalService::with(['currentPricing', 'serviceCategory'])
                    ->findOrFail($serviceData['medical_service_id']);

                // Get pricing information - use the API response structure
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
                    'visit_id' => $request->visit_id,
                    'consultation_id' => null, // Lab Only visits don't have consultations
                    'doctor_id' => null, // Lab Only visits don't require doctors
                    'medical_service_id' => $service->id,
                    'quantity' => 1, // Default quantity for lab investigations
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice,
                    'insurance_covered_amount' => $insuranceCoveredAmount,
                    'priority' => 'routine', // Default priority
                    'notes' => $serviceData['notes'] ?? null,
                    'status' => Investigation::STATUS_ORDERED,
                    'ordered_at' => now(),
                    'ordered_by' => Auth::id()
                ]);

                $createdInvestigations[] = $investigation;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lab investigations ordered successfully',
                'data' => $createdInvestigations,
                'count' => count($createdInvestigations)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating batch investigations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error ordering investigations: ' . $e->getMessage()
            ], 500);
        }
    }
}
