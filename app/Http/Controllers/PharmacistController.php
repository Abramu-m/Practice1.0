<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\StoreLocation;
use App\Models\StoreLocationStock;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            $query = $this->buildPrescriptionsQuery($request);
            
            $prescriptions = $query->paginate(20);
            
            return view('pharmacist.prescriptions.index', compact('prescriptions'));
        } catch (\Exception $e) {
            Log::error('Prescriptions query error: ' . $e->getMessage());
            
            // Return empty paginated result on error
            $prescriptions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $prescriptions->withPath($request->url());
            
            return view('pharmacist.prescriptions.index', compact('prescriptions'));
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
        
        return view('pharmacist.prescriptions.show', compact('visit'));
    }

    /**
     * Dispense a prescription
     */
    public function dispensePrescription(Request $request, Prescription $prescription)
    {
        $request->validate([
            'quantity_dispensed' => 'required|numeric|min:0|max:' . $prescription->quantity,
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
        return PatientVisit::with([
            'patientInfo',
            'consultation.prescriptions' => function($query) {
                $query->whereIn('status', ['prescribed', 'prepared']);
            }
        ])
        ->whereHas('consultation.prescriptions', function($query) {
            $query->whereIn('status', ['prescribed', 'prepared']);
        })
        // For now, we'll show all pending prescriptions
        // In the future, you can add payment status checks here
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    }

    /**
     * Get today's statistics
     */
    private function getTodayStats()
    {
        $today = now()->toDateString();
        
        return [
            'pending_prescriptions' => Prescription::whereIn('status', ['prescribed', 'prepared'])
                ->whereDate('created_at', $today)
                ->count(),
            'dispensed_today' => Prescription::where('status', 'dispensed')
                ->whereDate('dispensed_at', $today)
                ->count(),
            'total_patients' => PatientVisit::whereDate('created_at', $today)
                ->whereHas('consultation.prescriptions')
                ->distinct('patient')  // Using 'patient' instead of 'patient_id'
                ->count(),
            'unavailable_items' => Prescription::where('status', 'cancelled')
                ->whereDate('updated_at', $today)
                ->count()
        ];
    }

    /**
     * Build prescriptions query with filters
     */
    private function buildPrescriptionsQuery(Request $request)
    {
        $query = PatientVisit::with([
            'patientInfo',
            'consultation.prescriptions.medication'
        ])
        ->whereHas('consultation.prescriptions')
        ->orderBy('created_at', 'desc');

        // Filter by prescription status
        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('consultation.prescriptions', function($q) use ($status) {
                // Map the filter status to database status values
                switch($status) {
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
        // If no status filter is provided, show all visits with prescriptions (no status filter)

        // Filter by patient search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patientInfo', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
                  
                // Check if search looks like an MR number format and extract ID
                if (preg_match('/MR-\d{4}-(\d+)/', $search, $matches)) {
                    $q->orWhere('id', intval($matches[1]));
                } elseif (is_numeric($search)) {
                    // Also check for raw numeric ID
                    $q->orWhere('id', $search);
                }
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
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
