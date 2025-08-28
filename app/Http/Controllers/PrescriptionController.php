<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\MedicationFrequency;
use App\Models\StoreLocation;
use App\Models\StoreLocationStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Prescription $prescription)
    {
        // Eager load relationships
        $prescription->load('medication', 'frequency', 'administrationRoute');
        return response()->json(['success' => true, 'prescription' => $prescription]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prescription $prescription)
    {
        $frequencies = MedicationFrequency::all();
        return view('prescriptions.partials.edit-modal', compact('prescription', 'frequencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prescription $prescription)
    {
        $validatedData = $request->validate([
            'dosage' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'frequency_id' => 'required|exists:medication_frequencies,id',
            'duration_days' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string',
            'status' => 'required|in:prescribed,dispensed,cancelled',
        ]);

        $prescription->update($validatedData);

        return response()->json(['success' => true, 'message' => 'Prescription updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prescription $prescription)
    {
        try {
            $prescription->delete();
            return response()->json(['success' => true, 'message' => 'Prescription deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete prescription.'], 500);
        }
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function updateStatus(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:prescribed,dispensed,cancelled',
        ]);

        try {
            $prescription->status = $validated['status'];
            if ($validated['status'] === 'dispensed') {
                $prescription->dispensed_at = now();
            }
            $prescription->save();
            return response()->json(['success' => true, 'message' => 'Prescription status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update prescription status.'], 500);
        }
    }

    /**
     * Dispense the specified resource in storage.
     */
    public function dispense(Prescription $prescription)
    {
        try {
            // Get available stock
            $availableStock = $this->getAvailableStock($prescription->medication_id);
            $quantityToDispense = $prescription->quantity - ($prescription->quantity_dispensed ?? 0);
            
            if ($availableStock < $quantityToDispense) {
                return response()->json([
                    'success' => false, 
                    'message' => "Insufficient stock. Available: {$availableStock}, Required: {$quantityToDispense}"
                ], 400);
            }

            // Get batches for dispensing
            $batchesUsed = $this->getBatchesForDispensing($prescription->medication_id, $quantityToDispense);
            
            $prescription->update([
                'status' => 'dispensed',
                'quantity_dispensed' => $prescription->quantity,
                'batches_used' => $batchesUsed,
                'dispensing_type' => 'batch',
                'dispensed_at' => now(),
                'dispensed_by' => Auth::id()
            ]);

            // Update stock
            $this->updateMedicationStockWithBatches($prescription, $quantityToDispense);

            return response()->json(['success' => true, 'message' => 'Prescription dispensed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to dispense prescription: ' . $e->getMessage()], 500);
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
     * Update medication stock with batch tracking
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
                return;
            }

            // Get stock items ordered by expiry date (FIFO)
            $stockItems = StoreLocationStock::where('location_id', $pharmacyLocation->id)
                ->where('medication_id', $prescription->medication_id)
                ->where('status', StoreLocationStock::STATUS_ACTIVE)
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

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

        } catch (\Exception $e) {
            Log::error('Error updating medication stock with batches', [
                'prescription_id' => $prescription->id,
                'medication_id' => $prescription->medication_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
