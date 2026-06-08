<?php

namespace App\Services;

use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use App\Models\MedicationLedger;
use App\Models\StoreLocationStock;
use App\Models\StoreStockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class StoreRequisitionService
{
    /**
     * Process a requisition from draft to submitted
     */
    public function submitRequisition(StoreRequisition $requisition)
    {
        if ($requisition->status !== 'draft') {
            throw new Exception('Only draft requisitions can be submitted');
        }

        $requisition->update([
            'status' => 'submitted',
            'requisition_date' => now()
        ]);

        return $requisition;
    }

    /**
     * Approve a requisition (check availability of items)
     */
    public function verifyRequisition(StoreRequisition $requisition)
    {
        if ($requisition->status !== 'submitted') {
            throw new Exception('Only submitted requisitions can be verified');
        }

        $canFulfill = true;
        $verificationResults = [];

        foreach ($requisition->items as $item) {
            if ($item->item_type === 'medication') {
                $availableStock = $this->getAvailableStockForMedication(
                    $item->item_id, 
                    $item->requested_quantity
                );

                $verificationResults[] = [
                    'item_id' => $item->id,
                    'available_quantity' => $availableStock['total_available'],
                    'can_fulfill' => $availableStock['total_available'] >= $item->requested_quantity,
                    'batches' => $availableStock['batches']
                ];

                if ($availableStock['total_available'] < $item->requested_quantity) {
                    $canFulfill = false;
                }
            }
        }

        // For now, we'll mark as approved regardless of stock availability
        // The partial/full status will be determined during issuing
        $status = 'approved';
        
        $requisition->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return [
            'requisition' => $requisition,
            'can_fulfill' => $canFulfill,
            'verification_results' => $verificationResults
        ];
    }

    /**
     * Issue/fulfill an approved requisition
     */
    public function issueRequisition(StoreRequisition $requisition, $userId)
    {
        if (!in_array($requisition->status, ['approved', 'partially_issued'])) {
            throw new Exception('Only approved requisitions can be issued');
        }

        DB::beginTransaction();

        try {
            foreach ($requisition->items as $item) {
                if ($item->item_type === 'medication') {
                    $this->transferMedicationToLocation(
                        $requisition,
                        $item,
                        $item->approved_quantity ?: $item->requested_quantity
                    );
                }
            }

            // Use DB::raw to ensure proper value handling for ENUM
            $requisition->update([
                'status' => DB::raw("'fully_issued'"),
                'issued_by' => $userId,
                'issued_at' => now()
            ]);

            DB::commit();

            return $requisition;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error issuing requisition: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transfer medication from ledger to store location stock
     */
    protected function transferMedicationToLocation(StoreRequisition $requisition, StoreRequisitionItem $reqItem, $quantity)
    {
        // Get available medication batches using FEFO (First Expiry, First Out)
        $availableStock = MedicationLedger::where('medication_id', $reqItem->item_id)
            ->where('status', 'active')
            ->where('quantity_received', '>', 0)
            ->orderBy('expiry_date', 'asc') // FEFO: First Expiry, First Out
            ->orderBy('created_at', 'asc')  // FIFO: First In, First Out
            ->get();

        if ($availableStock->sum('quantity_received') < $quantity) {
            throw new Exception("Insufficient stock for medication ID {$reqItem->item_id}");
        }

        $remainingQuantity = $quantity;

        foreach ($availableStock as $ledgerEntry) {
            if ($remainingQuantity <= 0) break;

            $quantityToDeduct = min($remainingQuantity, $ledgerEntry->quantity_received);
            
            // Update ledger entry
            $ledgerEntry->decrement('quantity_received', $quantityToDeduct);
            
            // Create or update location stock entry
            $locationStock = StoreLocationStock::updateOrCreate(
                [
                    'location_id' => $requisition->requesting_location_id,
                    'medication_id' => $reqItem->item_id,
                    'batch_number' => $ledgerEntry->batch_number,
                    'expiry_date' => $ledgerEntry->expiry_date,
                ],
                [
                    'requisition_id' => $requisition->id,
                    'requisition_item_id' => $reqItem->id,
                    'manufacture_date' => $ledgerEntry->manufacture_date,
                    'unit_cost' => $ledgerEntry->unit_cost,
                    'status' => 'active'
                ]
            );

            // If updating existing stock, increment the quantity; if creating new, set the quantity
            if ($locationStock->wasRecentlyCreated) {
                $locationStock->update(['quantity' => $quantityToDeduct]);
            } else {
                $locationStock->increment('quantity', $quantityToDeduct);
            }

            // Create stock movement record
            $this->createStockMovement([
                'movement_type' => 'out',
                'transaction_type' => 'requisition',
                'source_type' => 'requisition',
                'source_id' => $requisition->id,
                'requisition_id' => $requisition->id,
                'medication_id' => $reqItem->item_id,
                'batch_number' => $ledgerEntry->batch_number,
                'from_location_id' => $ledgerEntry->location_id, // Main store
                'to_location_id' => $requisition->requesting_location_id,
                'quantity' => $quantityToDeduct,
                'unit_cost' => $ledgerEntry->unit_cost,
                'reference_number' => $requisition->requisition_number,
                'movement_date' => now(),
                'notes' => "Requisition transfer: {$requisition->requisition_number}"
            ]);

            $remainingQuantity -= $quantityToDeduct;

            // Delete ledger entry if exhausted to remove exhausted entries
            if ($ledgerEntry->quantity_received <= 0) {
                $ledgerEntry->delete();
            }
        }

        // Update requisition item status
        $reqItem->update([
            'issued_quantity' => $quantity,
            'status' => DB::raw("'issued'") // Use DB::raw to ensure proper value handling for ENUM
        ]);
    }

    /**
     * Get available stock for a medication
     */
    protected function getAvailableStockForMedication($medicationId, $requiredQuantity)
    {
        $batches = MedicationLedger::where('medication_id', $medicationId)
            ->where('status', 'active')
            ->where('quantity_received', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $totalAvailable = $batches->sum('quantity_received');

        return [
            'total_available' => $totalAvailable,
            'batches' => $batches,
            'can_fulfill' => $totalAvailable >= $requiredQuantity
        ];
    }

    /**
     * Create stock movement record
     */
    protected function createStockMovement($data)
    {
        return StoreStockMovement::create([
            'item_type' => 'medication',
            'item_id' => $data['medication_id'],
            'store_location_id' => $data['to_location_id'],
            'batch_id' => null, // We'll set this if you have a batches table
            'movement_type' => $data['movement_type'],
            'transaction_type' => $data['transaction_type'],
            'reference_number' => $data['reference_number'],
            'reference_id' => $data['requisition_id'] ?? null,
            'batch_number' => $data['batch_number'],
            'from_location_id' => $data['from_location_id'],
            'to_location_id' => $data['to_location_id'],
            'quantity' => $data['quantity'], // Dispensing units
            'unit_cost' => $data['unit_cost'], // Per dispensing unit
            'total_cost' => $data['quantity'] * $data['unit_cost'],
            'movement_date' => $data['movement_date'],
            'balance_before' => 0, // You may want to calculate actual balance
            'balance_after' => 0, // You may want to calculate actual balance
            'notes' => $data['notes'],
            'created_by' => Auth::id() ?? 1
        ]);
    }

    /**
     * Generate unique requisition number
     */
    public function generateRequisitionNumber()
    {
        $prefix = 'REQ';
        $date = now()->format('Ymd');
        
        $lastRequisition = StoreRequisition::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRequisition) {
            $lastNumber = intval(substr($lastRequisition->requisition_number, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new requisition
     */
    public function createRequisition($requestingLocationId, $issuingLocationId, $requestedBy, $items, $priority = 'normal', $purpose = null)
    {
        DB::beginTransaction();

        try {
            // Create requisition
            $requisition = StoreRequisition::create([
                'requisition_number' => $this->generateRequisitionNumber(),
                'requisition_date' => now(),
                'requesting_location_id' => $requestingLocationId,
                'issuing_location_id' => $issuingLocationId,
                'priority' => $priority,
                'purpose' => $purpose,
                'status' => 'draft',
                'requested_by' => $requestedBy
            ]);

            // Create requisition items
            $totalEstimatedCost = 0;
            
            foreach ($items as $item) {
                $itemCost = $item['requested_quantity'] * ($item['unit_cost'] ?? 0);
                $totalEstimatedCost += $itemCost;

                StoreRequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'total_cost' => $itemCost,
                    'justification' => $item['justification'] ?? null
                ]);
            }

            // Update total estimated cost
            $requisition->update(['total_estimated_cost' => $totalEstimatedCost]);

            DB::commit();

            return $requisition;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating requisition: ' . $e->getMessage());
            throw $e;
        }
    }
}
