<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\StoreLocation;
use App\Models\StoreLocationStock;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use App\Models\Medication;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PharmacistController extends Controller
{
    /**
     * Display the pharmacist dashboard
     */
    public function dashboard()
    {
        $pendingPrescriptions = $this->getPendingPrescriptions();
        $todayStats = $this->getTodayStats();
        
        return view('pharmacist.dashboard', compact('pendingPrescriptions', 'todayStats'));
    }

    /**
     * Display prescriptions awaiting dispensing
     */
    public function prescriptions(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = $this->buildPrescriptionsQuery($request);
                
                return DataTables::of($query)
                    ->addColumn('patient_info', function ($visit) {
                        return '<div>
                                    <strong>' . e($visit->patientInfo->first_name) . ' ' . e($visit->patientInfo->last_name) . '</strong>
                                    <br><small class="text-muted">MR: ' . e($visit->patientInfo->mr_number) . '</small>
                                    <br><small class="text-muted">Age: ' . e($visit->patientInfo->age ?? 'N/A') . '</small>
                                </div>';
                    })
                    ->addColumn('visit_details', function ($visit) {
                        $html = '<div><strong>' . $visit->created_at->format('M d, Y') . '</strong>
                                 <br><small class="text-muted">' . $visit->created_at->format('h:i A') . '</small>';
                        if ($visit->consultation && $visit->consultation->doctor) {
                            $html .= '<br><small class="text-muted">Dr. ' . e($visit->consultation->doctor->name ?? 'N/A') . '</small>';
                        }
                        $html .= '</div>';
                        return $html;
                    })
                    ->addColumn('prescriptions_info', function ($visit) {
                        if ($visit->consultation && $visit->consultation->prescriptions->count() > 0) {
                            $prescriptions = $visit->consultation->prescriptions;
                            $pendingCount = $prescriptions->filter(function($p) { 
                                return in_array($p->status, ['prescribed', 'prepared']); 
                            })->count();
                            $dispensedCount = $prescriptions->where('status', 'dispensed')->count();
                            $unavailableCount = $prescriptions->where('status', 'cancelled')->count();
                            
                            $html = '<div>
                                        <span class="badge bg-secondary">' . $prescriptions->count() . ' Total</span>';
                            if ($pendingCount > 0) {
                                $html .= ' <span class="badge bg-warning">' . $pendingCount . ' Pending</span>';
                            }
                            if ($dispensedCount > 0) {
                                $html .= ' <span class="badge bg-success">' . $dispensedCount . ' Dispensed</span>';
                            }
                            if ($unavailableCount > 0) {
                                $html .= ' <span class="badge bg-danger">' . $unavailableCount . ' Unavailable</span>';
                            }
                            $html .= '</div>';
                            return $html;
                        }
                        return '<span class="text-muted">No prescriptions</span>';
                    })
                    ->addColumn('status_badge', function ($visit) {
                        if ($visit->consultation && $visit->consultation->prescriptions->count() > 0) {
                            $allPrescriptions = $visit->consultation->prescriptions;
                            $hasPending = $allPrescriptions->filter(function($p) { 
                                return in_array($p->status, ['prescribed', 'prepared']); 
                            })->count() > 0;
                            $allDispensed = $allPrescriptions->every(function($p) { return $p->status === 'dispensed'; });
                            $hasUnavailable = $allPrescriptions->where('status', 'cancelled')->count() > 0;
                            
                            if ($hasPending) {
                                return '<span class="badge bg-warning">Action Required</span>';
                            } elseif ($allDispensed) {
                                return '<span class="badge bg-success">Completed</span>';
                            } elseif ($hasUnavailable) {
                                return '<span class="badge bg-danger">Issues</span>';
                            } else {
                                return '<span class="badge bg-info">Processing</span>';
                            }
                        }
                        return '<span class="badge bg-secondary">No Prescriptions</span>';
                    })
                    ->addColumn('actions', function ($visit) {
                        if ($visit->consultation && $visit->consultation->prescriptions->count() > 0) {
                            $consultationId = $visit->consultation->id;
                            $viewUrl = route('pharmacist.prescriptions.show', $visit->id);
                            return '
                                <div class="d-flex gap-1">
                                    <a href="' . $viewUrl . '" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                            onclick="viewVisitPrescriptionsModal(' . $consultationId . ')">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                </div>';
                        }
                        return '<span class="text-muted">No actions</span>';
                    })
                    ->rawColumns(['patient_info', 'visit_details', 'prescriptions_info', 'status_badge', 'actions'])
                    ->make(true);
            }

            $dateFrom = now()->subDays(2)->toDateString();
            $dateTo   = now()->toDateString();
            return view('pharmacist.prescriptions.index', compact('dateFrom', 'dateTo'));
        } catch (\Exception $e) {
            Log::error('Prescriptions query error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            $dateFrom = now()->subDays(2)->toDateString();
            $dateTo   = now()->toDateString();
            return view('pharmacist.prescriptions.index', compact('dateFrom', 'dateTo'));
        }
    }

    /**
     * Show prescription details for dispensing
     */
    public function showPrescription(PatientVisit $visit)
    {
        $visit->load([
            'patientInfo',
            'consultation.prescriptions.medication',
            'consultation.prescriptions.frequency',
            'consultation.prescriptions.administrationRoute'
        ]);

        // Change this per context: 'Main Pharmacy', 'Lab', 'Ward', etc.
        // Could also be driven by Auth::user()->storeLocation->name or role
        $dispensingLocation = 'Main Pharmacy';

        // Total quantity already requested in open requisitions, keyed by medication_id
        $medicationIds = $visit->consultation?->prescriptions->pluck('medication_id')->filter()->unique() ?? collect();
        $pendingReqQtys = StoreRequisitionItem::where('item_type', 'medication')
            ->whereIn('item_id', $medicationIds)
            ->whereHas('requisition', fn($q) => $q->whereNotIn('status', ['fully_issued', 'cancelled', 'rejected']))
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(requested_quantity) as total_requested')
            ->pluck('total_requested', 'item_id');

        return view('pharmacist.prescriptions.show', compact('visit', 'dispensingLocation', 'pendingReqQtys'));
    }

    /**
     * Dispense a prescription
     */
    public function dispensePrescription(Request $request, Prescription $prescription)
    {
        $request->validate([
            'quantity_dispensed' => 'required|integer|min:0|max:' . $prescription->quantity,
            'notes' => 'nullable|string|max:500'
        ]);

        // Check stock availability before dispensing
        $availableStock = $this->getAvailableStock($prescription->medication_id);
        if ($availableStock < $request->quantity_dispensed) {
            return redirect()->back()->with('error', 
                "Insufficient stock available. Available: {$availableStock}, Requested: {$request->quantity_dispensed}");
        }

        try {
            DB::beginTransaction();

            // Get batches used for this dispensing with location info
            $batchesUsed = $this->getBatchesForDispensing($prescription->medication_id, $request->quantity_dispensed);

            $prescription->update([
                'status' => 'dispensed',
                'quantity_dispensed' => $request->quantity_dispensed,
                'batches_used' => $batchesUsed,
                'dispensing_type' => 'batch',
                'dispensed_at' => now(),
                'dispensed_by' => Auth::id(),
                'pharmacist_notes' => $request->notes
            ]);

            // Update medication stock using the same logic as cash sales
            $this->updateMedicationStockWithBatches($prescription, $request->quantity_dispensed);

            DB::commit();

            return redirect()->back()->with('success', 'Prescription dispensed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error dispensing prescription: ' . $e->getMessage());
        }
    }

    /**
     * Mark prescription as unavailable
     */
    public function markUnavailable(Request $request, Prescription $prescription)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $prescription->update([
            'status' => 'cancelled',
            'pharmacist_notes' => $request->reason,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id()
        ]);

        return redirect()->back()->with('info', 'Prescription marked as unavailable.');
    }

    /**
     * Get pending prescriptions for dashboard
     */
    private function getPendingPrescriptions()
    {
        $dateFrom = now()->subDays(2)->startOfDay();

        $visitIds = DB::table('prescriptions')
            ->join('consultations', 'prescriptions.consultation_id', '=', 'consultations.id')
            ->where('prescriptions.created_at', '>=', $dateFrom)
            ->whereIn('prescriptions.status', ['prescribed', 'prepared'])
            ->pluck('consultations.visit_id')
            ->unique();

        return PatientVisit::with([
            'patientInfo',
            'consultation.prescriptions' => function ($query) {
                $query->whereIn('status', ['prescribed', 'prepared']);
            }
        ])
        ->whereIn('id', $visitIds)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    }

    /**
     * Get today's statistics
     */
    private function getTodayStats()
    {
        $dayStart = now()->startOfDay();
        $dayEnd   = now()->endOfDay();

        return [
            'pending_prescriptions' => Prescription::whereIn('status', ['prescribed', 'prepared'])
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count(),
            'dispensed_today' => Prescription::where('status', 'dispensed')
                ->whereBetween('dispensed_at', [$dayStart, $dayEnd])
                ->count(),
            'total_patients' => DB::table('patient_visits')
                ->join('consultations', 'patient_visits.id', '=', 'consultations.visit_id')
                ->join('prescriptions', 'consultations.id', '=', 'prescriptions.consultation_id')
                ->whereBetween('patient_visits.created_at', [$dayStart, $dayEnd])
                ->distinct()
                ->count('patient_visits.patient'),
            'unavailable_items' => Prescription::where('status', 'cancelled')
                ->whereBetween('updated_at', [$dayStart, $dayEnd])
                ->count(),
        ];
    }

    /**
     * Build prescriptions query with filters
     */
    private function buildPrescriptionsQuery(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(2)->toDateString());
        $dateTo   = $request->input('date_to',   now()->toDateString());

        $query = PatientVisit::with([
            'patientInfo',
            'consultation.prescriptions.medication',
            'consultation.doctor.user'
        ])
        ->whereHas('consultation.prescriptions')
        ->where('created_at', '>=', $dateFrom . ' 00:00:00')
        ->where('created_at', '<=', $dateTo   . ' 23:59:59')
        ->orderBy('created_at', 'desc');

        if ($request->filled('status') && is_string($request->status)) {
            $status = $request->status;
            $query->whereHas('consultation.prescriptions', function ($q) use ($status) {
                switch ($status) {
                    case 'pending':
                        $q->whereIn('status', ['prescribed', 'prepared']);
                        break;
                    case 'dispensed':
                        $q->where('status', 'dispensed');
                        break;
                    case 'unavailable':
                        $q->where('status', 'cancelled');
                        break;
                    default:
                        $q->where('status', $status);
                }
            });
        }

        if ($request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $query->whereHas('patientInfo', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('legacy_mrn', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Get pharmacist data for AJAX requests
     */
    public function getData(Request $request)
    {
        $type = $request->get('type');
        
        switch ($type) {
            case 'pending_count':
                $count = Prescription::whereIn('status', ['prescribed', 'prepared'])->count();
                
                return response()->json(['pending_count' => $count]);
                
            default:
                return response()->json(['error' => 'Invalid data type'], 400);
        }
    }

    /**
     * Add a medication item to an existing open requisition
     */
    public function addItemToRequisition(Request $request, StoreRequisition $requisition)
    {
        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'quantity'      => 'required|numeric|min:1',
        ]);

        if (in_array($requisition->status, ['fully_issued', 'cancelled', 'rejected'])) {
            return response()->json(['message' => 'Cannot add items to a ' . $requisition->status . ' requisition.'], 422);
        }

        $existing = $requisition->items()
            ->where('item_type', 'medication')
            ->where('item_id', $request->medication_id)
            ->first();

        if ($existing) {
            $existing->increment('requested_quantity', $request->quantity);
        } else {
            $requisition->items()->create([
                'item_type'          => 'medication',
                'item_id'            => $request->medication_id,
                'requested_quantity' => $request->quantity,
                'unit_cost'          => 0,
            ]);
        }

        return response()->json(['message' => 'Item added to requisition successfully.']);
    }

    /**
     * Create a new draft requisition with a single medication item
     */
    public function createRequisitionWithItem(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'quantity'      => 'required|numeric|min:1',
        ]);

        $pharmacyLocation = StoreLocation::where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'dispensing')
                  ->orWhere('name', 'like', '%Main Pharmacy%');
            })
            ->first();

        if (!$pharmacyLocation) {
            return response()->json(['message' => 'Main Pharmacy location not found.'], 422);
        }

        DB::beginTransaction();
        try {
            $todayCount = StoreRequisition::whereDate('created_at', today())->count() + 1;
            $reqNumber  = 'REQ-' . date('Ymd') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT);

            $requisition = StoreRequisition::create([
                'requisition_number'   => $reqNumber,
                'requisition_date'     => today(),
                'requesting_location_id' => $pharmacyLocation->id,
                'issuing_location_id'  => $pharmacyLocation->id,
                'priority'             => 'normal',
                'status'               => 'draft',
                'requested_by'         => Auth::id(),
            ]);

            $requisition->items()->create([
                'item_type'          => 'medication',
                'item_id'            => $request->medication_id,
                'requested_quantity' => $request->quantity,
                'unit_cost'          => 0,
            ]);

            DB::commit();

            return response()->json([
                'message'            => 'Requisition ' . $reqNumber . ' created as draft.',
                'show_url'           => route('store.requisitions.show', $requisition->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all open requisitions issued from Main Pharmacy
     */
    public function getOpenRequisitions()
    {
        $pharmacyLocation = StoreLocation::where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'dispensing')
                  ->orWhere('name', 'like', '%Main Pharmacy%');
            })
            ->first();

        $requisitions = StoreRequisition::with([
            'requestingLocation',
            'requestedBy',
            'items.medication:id,generic_name',
        ])
            ->withCount('items')
            ->whereNotIn('status', ['fully_issued', 'cancelled', 'rejected'])
            ->when($pharmacyLocation, fn($q) => $q->where('issuing_location_id', $pharmacyLocation->id))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requisitions' => $requisitions->map(fn($req) => [
                'id'                  => $req->id,
                'requisition_number'  => $req->requisition_number,
                'status'              => $req->status,
                'priority'            => $req->priority,
                'requesting_location' => $req->requestingLocation->name ?? 'N/A',
                'requested_by'        => $req->requestedBy->name ?? 'N/A',
                'requisition_date'    => $req->requisition_date?->format('M d, Y'),
                'required_date'       => $req->required_date?->format('M d, Y'),
                'items_count'         => $req->items_count,
                'show_url'            => route('store.requisitions.show', $req->id),
                'medication_items'    => $req->items
                    ->where('item_type', 'medication')
                    ->map(fn($item) => [
                        'medication_id'      => $item->item_id,
                        'name'               => $item->medication->generic_name ?? 'Unknown',
                        'requested_quantity' => $item->requested_quantity,
                        'issued_quantity'    => $item->issued_quantity ?? 0,
                    ])->values(),
            ]),
        ]);
    }

    /**
     * Update medication stock after dispensing
     */
    private function updateMedicationStock($prescription, $quantityDispensed)
    {
        try {
            // Get pharmacy location (main pharmacy store)
            $pharmacyLocation = StoreLocation::where('type', 'dispensing')
                ->orWhere('name', 'LIKE', '%Main Pharmacy%')
                ->where('is_active', true)
                ->first();

            if (!$pharmacyLocation) {
                // Fallback to first active location if no pharmacy found
                $pharmacyLocation = StoreLocation::where('is_active', true)->first();
            }

            if (!$pharmacyLocation) {
                Log::warning('No active store location found for stock update', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id
                ]);
                return;
            }

            // Find available stock for this medication at the pharmacy location
            // Priority: earliest expiry date first (FIFO)
            $stockItems = StoreLocationStock::where('location_id', $pharmacyLocation->id)
                ->where('medication_id', $prescription->medication_id)
                ->where('status', StoreLocationStock::STATUS_ACTIVE)
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            if ($stockItems->isEmpty()) {
                Log::warning('No stock available for medication', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id,
                    'location_id' => $pharmacyLocation->id
                ]);
                return;
            }

            $remainingToDispense = $quantityDispensed;
            
            foreach ($stockItems as $stockItem) {
                if ($remainingToDispense <= 0) {
                    break;
                }

                $availableQuantity = $stockItem->quantity;
                $quantityToTake = min($remainingToDispense, $availableQuantity);

                // Update the stock item
                $newQuantity = $availableQuantity - $quantityToTake;
                
                $stockItem->update([
                    'quantity' => $newQuantity,
                    'status' => $newQuantity <= 0 ? StoreLocationStock::STATUS_DEPLETED : StoreLocationStock::STATUS_ACTIVE
                ]);

                $remainingToDispense -= $quantityToTake;

                Log::info('Stock updated for medication dispensing', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id,
                    'stock_item_id' => $stockItem->id,
                    'quantity_taken' => $quantityToTake,
                    'remaining_stock' => $newQuantity,
                    'remaining_to_dispense' => $remainingToDispense
                ]);
            }

            // Update the main medication stock quantity (optional summary field)
            if (isset($prescription->medication)) {
                $totalStockInPharmacy = StoreLocationStock::where('location_id', $pharmacyLocation->id)
                    ->where('medication_id', $prescription->medication_id)
                    ->where('status', StoreLocationStock::STATUS_ACTIVE)
                    ->sum('quantity');

                $prescription->medication->update([
                    'stock_quantity' => $totalStockInPharmacy
                ]);
            }

            if ($remainingToDispense > 0) {
                Log::warning('Insufficient stock to fulfill complete dispensing', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id,
                    'requested_quantity' => $quantityDispensed,
                    'unfulfilled_quantity' => $remainingToDispense
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating medication stock', [
                'prescription_id' => $prescription->id,
                'medication_id' => $prescription->medication_id,
                'error' => $e->getMessage()
            ]);
            // Don't throw the exception to prevent blocking the dispensing process
            // The prescription will still be marked as dispensed even if stock update fails
        }
    }

    /**
     * Get available stock for a medication in pharmacy locations
     */
    private function getAvailableStock($medicationId)
    {
        // Get pharmacy location
        $pharmacyLocation = StoreLocation::where('type', 'dispensing')
            ->orWhere('name', 'LIKE', '%Main Pharmacy%')
            ->where('is_active', true)
            ->first();

        if (!$pharmacyLocation) {
            $pharmacyLocation = StoreLocation::where('is_active', true)->first();
        }

        if (!$pharmacyLocation) {
            return 0;
        }

        // Sum available stock for this medication
        return StoreLocationStock::where('location_id', $pharmacyLocation->id)
            ->where('medication_id', $medicationId)
            ->where('status', StoreLocationStock::STATUS_ACTIVE)
            ->where('quantity', '>', 0)
            ->sum('quantity');
    }

    /**
     * Get batches used for dispensing with location information
     */
    private function getBatchesForDispensing($medicationId, $quantityToDispense)
    {
        // Get pharmacy location
        $pharmacyLocation = StoreLocation::where('type', 'dispensing')
            ->orWhere('name', 'LIKE', '%Main Pharmacy%')
            ->where('is_active', true)
            ->first();

        if (!$pharmacyLocation) {
            $pharmacyLocation = StoreLocation::where('is_active', true)->first();
        }

        if (!$pharmacyLocation) {
            return [];
        }

        // Get stock items ordered by expiry date (FIFO)
        $stockItems = StoreLocationStock::where('location_id', $pharmacyLocation->id)
            ->where('medication_id', $medicationId)
            ->where('status', StoreLocationStock::STATUS_ACTIVE)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $batchesUsed = [];
        $remainingToDispense = $quantityToDispense;

        foreach ($stockItems as $stockItem) {
            if ($remainingToDispense <= 0) break;

            $availableQuantity = $stockItem->quantity;
            $quantityToTake = min($remainingToDispense, $availableQuantity);

            // Record batch used with location info
            $batchesUsed[] = [
                'batch_number' => $stockItem->batch_number,
                'quantity' => number_format($quantityToTake, 2, '.', ''),
                'expiry_date' => $stockItem->expiry_date,
                'location_id' => $stockItem->location_id,
                'location_name' => $stockItem->location->name ?? 'Unknown Location'
            ];

            $remainingToDispense -= $quantityToTake;
        }

        return $batchesUsed;
    }

    /**
     * Update medication stock with batch tracking (similar to cash sale logic)
     */
    private function updateMedicationStockWithBatches($prescription, $quantityDispensed)
    {
        try {
            // Get pharmacy location
            $pharmacyLocation = StoreLocation::where('type', 'dispensing')
                ->orWhere('name', 'LIKE', '%Main Pharmacy%')
                ->where('is_active', true)
                ->first();

            if (!$pharmacyLocation) {
                $pharmacyLocation = StoreLocation::where('is_active', true)->first();
            }

            if (!$pharmacyLocation) {
                Log::warning('No active store location found for stock update', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id
                ]);
                return;
            }

            // Get stock items ordered by expiry date (FIFO)
            $stockItems = StoreLocationStock::where('location_id', $pharmacyLocation->id)
                ->where('medication_id', $prescription->medication_id)
                ->where('status', StoreLocationStock::STATUS_ACTIVE)
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            if ($stockItems->isEmpty()) {
                Log::warning('No stock available for medication', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id,
                    'location_id' => $pharmacyLocation->id
                ]);
                return;
            }

            $remainingToDispense = $quantityDispensed;

            foreach ($stockItems as $stockItem) {
                if ($remainingToDispense <= 0) break;

                $availableQuantity = $stockItem->quantity;
                $quantityToTake = min($remainingToDispense, $availableQuantity);

                // Update stock
                $newQuantity = $availableQuantity - $quantityToTake;
                $stockItem->update([
                    'quantity' => $newQuantity,
                    'status' => $newQuantity <= 0 ? 
                        StoreLocationStock::STATUS_DEPLETED : 
                        StoreLocationStock::STATUS_ACTIVE
                ]);

                $remainingToDispense -= $quantityToTake;

                Log::info('Stock updated for prescription dispensing', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id,
                    'stock_item_id' => $stockItem->id,
                    'batch_number' => $stockItem->batch_number,
                    'quantity_taken' => $quantityToTake,
                    'remaining_stock' => $newQuantity
                ]);
            }

            // Update the main medication stock quantity
            if (isset($prescription->medication)) {
                $totalStockInPharmacy = StoreLocationStock::where('location_id', $pharmacyLocation->id)
                    ->where('medication_id', $prescription->medication_id)
                    ->where('status', StoreLocationStock::STATUS_ACTIVE)
                    ->sum('quantity');

                $prescription->medication->update([
                    'stock_quantity' => $totalStockInPharmacy
                ]);
            }

            if ($remainingToDispense > 0) {
                Log::warning('Insufficient stock to fulfill complete dispensing', [
                    'prescription_id' => $prescription->id,
                    'medication_id' => $prescription->medication_id,
                    'requested_quantity' => $quantityDispensed,
                    'unfulfilled_quantity' => $remainingToDispense
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating medication stock with batches', [
                'prescription_id' => $prescription->id,
                'medication_id' => $prescription->medication_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
