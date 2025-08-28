<?php

namespace App\Http\Controllers;

use App\Models\PatientVisit;
use App\Models\Investigation;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CashierController extends Controller
{
    /**
     * Display a listing of patient visits for cashier
     */
    public function index(Request $request)
    {
        $query = PatientVisit::with([
            'patientInfo', 
            'visitCategory', 
            'doctorInfo.user', 
            'createdBy'
        ]);

        // Search functionality
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patientInfo', function($patientQuery) use ($search) {
                    $patientQuery->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%")
                                 ->orWhere('middle_name', 'like', "%{$search}%");
                                 
                    // Check if search looks like an MR number format and extract ID
                    if (preg_match('/MR-\d{4}-(\d+)/', $search, $matches)) {
                        $patientQuery->orWhere('id', intval($matches[1]));
                    } elseif (is_numeric($search)) {
                        // Also check for raw numeric ID
                        $patientQuery->orWhere('id', $search);
                    }
                })
                ->orWhere('sic_no', 'like', "%{$search}%")
                ->orWhere('authorization_no', 'like', "%{$search}%")
                ->orWhere('nhif_reference_no', 'like', "%{$search}%")
                ->orWhere('id', $search);
            });
        }

        // Filter by visit status if needed
        if ($request->has('status') && $request->status !== '') {
            $query->where('visit_status', $request->status);
        }

        // Order by most recent visits first
        $visits = $query->orderBy('created_at', 'desc')->paginate(15);

        // Add investigations and prescriptions count for each visit
        foreach ($visits as $visit) {
            // Load investigations directly by visit_id (includes both consultation and lab-only investigations)
            $visit->load(['investigations' => function($query) {
                $query->select('id', 'visit_id', 'consultation_id', 'patient_id', 'is_paid', 'status', 'total_price', 'ordered_at');
            }]);
            
            // Load prescriptions for the visit (assuming they're also through consultation)
            $visit->load(['consultation.prescriptions' => function($query) {
                $query->select('id', 'consultation_id', 'patient_id', 'is_paid', 'status', 'total_price');
            }]);
            
            // Get prescriptions from consultation if it exists
            if ($visit->consultation && $visit->consultation->prescriptions) {
                $visit->prescriptions = $visit->consultation->prescriptions;
            } else {
                $visit->prescriptions = collect();
            }

            $visit->investigations_count = $visit->investigations->count();
            $visit->prescriptions_count = $visit->prescriptions->count();

            // Calculate pending amounts (items not paid)
            $visit->pending_investigations_amount = $visit->investigations
                ->where('is_paid', false)
                ->where('status', '!=', 'cancelled')
                ->sum('total_price');

            $visit->pending_prescriptions_amount = $visit->prescriptions
                ->where('is_paid', false)
                ->where('status', '!=', 'cancelled')
                ->sum('total_price');
        }

        return view('cashier.index', compact('visits'));
    }

    /**
     * Show investigations for a specific patient visit
     */
    public function showInvestigations(Request $request, $visitId)
    {
        $visit = PatientVisit::with([
            'patientInfo', 
            'doctorInfo.user',
            'investigations.medicalService',
            'investigations.doctor.user'
        ])->findOrFail($visitId);
        
        // Get all investigations for this visit (both consultation and lab-only)
        $investigations = $visit->investigations->sortByDesc('created_at');

        return view('cashier.investigations', compact('visit', 'investigations'));
    }

    /**
     * Show prescriptions for a specific patient visit
     */
    public function showPrescriptions(Request $request, $visitId)
    {
        $visit = PatientVisit::with([
            'patientInfo', 
            'doctorInfo.user',
            'consultation.prescriptions.medication',
            'consultation.prescriptions.doctorInfo.user', 
            'consultation.prescriptions.frequency'
        ])->findOrFail($visitId);
        
        // Get prescriptions from consultation
        $prescriptions = $visit->consultation ? 
            $visit->consultation->prescriptions->sortByDesc('created_at') : 
            collect();

        return view('cashier.prescriptions', compact('visit', 'prescriptions'));
    }

    /**
     * Update investigation payment status
     */
    public function updateInvestigationPayment(Request $request, $investigationId)
    {
        $request->validate([
            'action' => 'required|in:paid,cancelled',
            'payment_method' => 'required_if:action,paid|in:cash,card,insurance,nhif',
            'amount_paid' => 'required_if:action,paid|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        $investigation = Investigation::findOrFail($investigationId);

        DB::transaction(function () use ($investigation, $request) {
            $updateData = [];
            
            if ($request->action === 'paid') {
                $updateData = [
                    'is_paid' => true,
                    'payment_method' => $request->payment_method,
                    'amount_paid' => $request->amount_paid,
                    'paid_at' => now(),
                    'paid_by' => Auth::id()
                ];
                
                // Handle discount
                if ($request->discount_percent && $request->discount_percent > 0) {
                    $updateData['is_discount'] = true;
                    $updateData['discount_percent'] = $request->discount_percent;
                }
            } else {
                $updateData = [
                    'status' => 'cancelled'
                ];
            }
            
            Log::info("Updating investigation", [
                'investigation_id' => $investigation->id,
                'update_data' => $updateData,
                'action' => $request->action
            ]);
            
            $investigation->update($updateData);

            // Create financial transaction record when payment is made
            if ($request->action === 'paid') {
                // The InvestigationFinancialObserver will automatically create the financial transaction
                // when is_paid is set to true, so no additional code needed here
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Investigation payment status updated successfully'
        ]);
    }

    /**
     * Update prescription payment status
     */
    public function updatePrescriptionPayment(Request $request, $prescriptionId)
    {
        $request->validate([
            'action' => 'required|in:paid,cancelled',
            'payment_method' => 'required_if:action,paid|in:cash,card,insurance,nhif',
            'amount_paid' => 'required_if:action,paid|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        $prescription = Prescription::findOrFail($prescriptionId);

        try {
            DB::transaction(function () use ($prescription, $request) {
                $updateData = [];
                
                if ($request->action === 'paid') {
                    $updateData = [
                        'is_paid' => true,
                        'payment_method' => $request->payment_method,
                        'amount_paid' => $request->amount_paid,
                        'paid_at' => now(),
                        'paid_by' => Auth::id()
                    ];
                    
                    // Handle discount
                    if ($request->discount_percent && $request->discount_percent > 0) {
                        $updateData['is_discount'] = true;
                        $updateData['discount_percent'] = $request->discount_percent;
                    }
                } else {
                    $updateData = [
                        'status' => 'cancelled'
                    ];
                }
                
                Log::info("Updating prescription", [
                    'prescription_id' => $prescription->id,
                    'update_data' => $updateData,
                    'action' => $request->action
                ]);
                
                $prescription->update($updateData);

                // Create financial transaction record when payment is made
                if ($request->action === 'paid') {
                    // The MedicationDispensingObserver will automatically create the financial transaction
                    // when is_paid is set to true, so no additional code needed here
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Prescription payment status updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Prescription payment error: ' . $e->getMessage(), [
                'prescription_id' => $prescriptionId,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update investigations payment status
     */
    public function bulkUpdateInvestigations(Request $request)
    {
        $request->validate([
            'investigation_ids' => 'required|array',
            'investigation_ids.*' => 'exists:investigations,id',
            'action' => 'required|in:paid,cancelled',
            'payment_method' => 'required_if:action,paid|in:cash,card,insurance,nhif',
            'discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        DB::transaction(function () use ($request) {
            $updateData = [];
            
            if ($request->action === 'paid') {
                $updateData = [
                    'is_paid' => true,
                    'payment_method' => $request->payment_method,
                    'paid_at' => now(),
                    'paid_by' => Auth::id()
                ];
                
                // Handle discount
                if ($request->discount_percent && $request->discount_percent > 0) {
                    $updateData['is_discount'] = true;
                    $updateData['discount_percent'] = $request->discount_percent;
                }
            } else {
                $updateData = [
                    'status' => 'cancelled'
                ];
            }
            
            Investigation::whereIn('id', $request->investigation_ids)->update($updateData);
        });

        return response()->json([
            'success' => true,
            'message' => 'Investigations updated successfully'
        ]);
    }

    /**
     * Bulk update prescriptions payment status
     */
    public function bulkUpdatePrescriptions(Request $request)
    {
        $request->validate([
            'prescription_ids' => 'required|array',
            'prescription_ids.*' => 'exists:prescriptions,id',
            'action' => 'required|in:paid,cancelled',
            'payment_method' => 'required_if:action,paid|in:cash,card,insurance,nhif',
            'discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        DB::transaction(function () use ($request) {
            $updateData = [];
            
            if ($request->action === 'paid') {
                $updateData = [
                    'is_paid' => true,
                    'payment_method' => $request->payment_method,
                    'paid_at' => now(),
                    'paid_by' => Auth::id()
                ];
                
                // Handle discount
                if ($request->discount_percent && $request->discount_percent > 0) {
                    $updateData['is_discount'] = true;
                    $updateData['discount_percent'] = $request->discount_percent;
                }
            } else {
                $updateData = [
                    'status' => 'cancelled'
                ];
            }
            
            Prescription::whereIn('id', $request->prescription_ids)->update($updateData);
        });

        return response()->json([
            'success' => true,
            'message' => 'Prescriptions updated successfully'
        ]);
    }
}
