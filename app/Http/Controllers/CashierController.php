<?php

namespace App\Http\Controllers;

use App\Models\PatientVisit;
use App\Models\Investigation;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dateFrom = $request->input('date_from', now()->subDays(2)->toDateString());
            $dateTo   = $request->input('date_to',   now()->toDateString());

            $query = PatientVisit::with([
                'patientInfo',
                'doctorInfo.user',
                'investigations' => fn($q) => $q->where('status', '!=', 'cancelled')
                    ->select('id', 'visit_id', 'is_paid', 'status', 'cash_amount'),
                'consultation.prescriptions' => fn($q) => $q->where('status', '!=', 'cancelled')
                    ->select('id', 'consultation_id', 'is_paid', 'status', 'cash_amount'),
            ])
            ->select('patient_visits.*')
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where('visit_date', '>=', $dateFrom . ' 00:00:00')
                  ->where('visit_date', '<=', $dateTo . ' 23:59:59')
                  ->orWhere(function ($q2) {
                      // Still-open visits with unpaid items stay visible until paid, however old.
                      $q2->where('visit_status', '!=', 2)
                         ->where(function ($q3) {
                             $q3->whereHas('investigations', fn($iq) => $iq->where('status', '!=', 'cancelled')->where('is_paid', false))
                                ->orWhereHas('consultation.prescriptions', fn($pq) => $pq->where('status', '!=', 'cancelled')->where('is_paid', false));
                         });
                  });
            });

            if ($request->filled('status')) {
                $query->where('visit_status', $request->status);
            }

            if ($request->filled('search_term')) {
                $term = $request->search_term;
                $query->where(function ($q) use ($term) {
                    $q->whereHas('patientInfo', fn($p) =>
                        $p->where('first_name', 'like', "%{$term}%")
                          ->orWhere('last_name',   'like', "%{$term}%")
                          ->orWhere('middle_name', 'like', "%{$term}%")
                          ->orWhere('legacy_mrn', 'like', "%{$term}%")
                    )
                    ->orWhere('sic_no',            'like', "%{$term}%")
                    ->orWhere('authorization_no',  'like', "%{$term}%")
                    ->orWhere('nhif_reference_no', 'like', "%{$term}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('patient', function ($visit) {
                    if (!$visit->patientInfo) {
                        return '<span class="text-danger">Patient not found</span>';
                    }
                    $name = e($visit->patientInfo->first_name . ' ' . $visit->patientInfo->last_name);
                    $mr   = e($visit->patientInfo->mr_number ?? 'C' . str_pad($visit->patientInfo->id, 4, '0', STR_PAD_LEFT));
                    return "<strong>{$name}</strong><br><small class=\"text-muted\">{$mr}</small>";
                })
                ->addColumn('visit_date_col', function ($visit) {
                    if (!$visit->visit_date) return 'N/A';
                    return $visit->visit_date->format('M d, Y')
                        . '<br><small class="text-muted">' . $visit->visit_date->format('h:i A') . '</small>';
                })
                ->addColumn('doctor', function ($visit) {
                    $name = optional(optional($visit->doctorInfo)->user)->name;
                    return $name ? e($name) : '<span class="text-muted">Not assigned</span>';
                })
                ->addColumn('status_col', function ($visit) {
                    return '<span class="badge ' . $visit->visit_status_badge_class . '">'
                        . $visit->visit_status_label . '</span>';
                })
                ->addColumn('investigations_col', function ($visit) {
                    $invs  = $visit->investigations ?? collect();
                    $count = $invs->count();
                    if ($count === 0) return '<span class="text-muted">None</span>';
                    $paid   = $invs->where('is_paid', true)->count();
                    $unpaid = $count - $paid;
                    $s   = $count > 1 ? 's' : '';
                    $html = "<button class=\"btn btn-sm btn-outline-info mb-1\" onclick=\"viewInvestigations({$visit->id})\">"
                          . "<i class=\"bi bi-eye\"></i> {$count} Investigation{$s}</button>"
                          . "<br><div class=\"small\">"
                          . "<span class=\"badge bg-success\"><i class=\"bi bi-check-circle\"></i> {$paid} Paid</span>";
                    if ($unpaid > 0) {
                        $html .= "<span class=\"badge bg-warning\"><i class=\"bi bi-clock\"></i> {$unpaid} Pending</span>";
                    }
                    return $html . '</div>';
                })
                ->addColumn('prescriptions_col', function ($visit) {
                    $rxs   = optional($visit->consultation)->prescriptions ?? collect();
                    $count = $rxs->count();
                    if ($count === 0) return '<span class="text-muted">None</span>';
                    $paid   = $rxs->where('is_paid', true)->count();
                    $unpaid = $count - $paid;
                    $s   = $count > 1 ? 's' : '';
                    $html = "<button class=\"btn btn-sm btn-outline-success mb-1\" onclick=\"viewPrescriptions({$visit->id})\">"
                          . "<i class=\"bi bi-eye\"></i> {$count} Prescription{$s}</button>"
                          . "<br><div class=\"small\">"
                          . "<span class=\"badge bg-success\"><i class=\"bi bi-check-circle\"></i> {$paid} Paid</span>";
                    if ($unpaid > 0) {
                        $html .= "<span class=\"badge bg-warning\"><i class=\"bi bi-clock\"></i> {$unpaid} Pending</span>";
                    }
                    return $html . '</div>';
                })
                ->orderColumn('visit_date_col', 'visit_date $1')
                ->rawColumns(['patient', 'visit_date_col', 'doctor', 'status_col', 'investigations_col', 'prescriptions_col'])
                ->make(true);
        }

        $dateFrom = now()->subDays(2)->toDateString();
        $dateTo   = now()->toDateString();
        return view('cashier.index', compact('dateFrom', 'dateTo'));
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
        
        // Get all non-cancelled investigations for this visit
        $investigations = $visit->investigations->where('status', '!=', 'cancelled')->sortByDesc('created_at');

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
        
        // Get non-cancelled prescriptions from consultation
        $prescriptions = $visit->consultation ? 
            $visit->consultation->prescriptions->where('status', '!=', 'cancelled')->sortByDesc('created_at') : 
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
            'payment_method' => 'nullable|required_if:action,paid|in:cash,mobile_money,card',
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
            'payment_method' => 'nullable|required_if:action,paid|in:cash,mobile_money,card',
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
            'payment_method' => 'nullable|required_if:action,paid|in:cash,mobile_money,card',
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
            'payment_method' => 'nullable|required_if:action,paid|in:cash,mobile_money,card',
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
