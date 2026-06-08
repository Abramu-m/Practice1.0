<?php

namespace App\Services;

use App\Models\GoodsReceivedNote;
use App\Models\StoreRequisition;
use App\Models\MedicationLedger;
use App\Models\StoreLocationStock;
use App\Models\StoreStockMovement;
use App\Models\UnfitMedication;
use App\Models\Medication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockManagementService
{
    /**
     * Process GRN verification and update stock
     */
    public function receiveGrnItems(GoodsReceivedNote $grn)
    {
        DB::beginTransaction();
        
        try {
            // Mark GRN as verified
            $grn->update([
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            foreach ($grn->items as $grnItem) {
                // Create medication ledger entry
                $ledgerEntry = MedicationLedger::create([
                    'medication_id' => $grnItem->item_id,
                    'grn_id' => $grn->id,
                    'grn_item_id' => $grnItem->id,
                    'batch_number' => $grnItem->batch_number,
                    'manufacture_date' => $grnItem->manufacture_date,
                    'expiry_date' => $grnItem->expiry_date,
                    'unit_cost' => $grnItem->unit_cost,
                    'selling_price' => $grnItem->unit_cost * 1.2, // Default 20% markup
                    'quantity_received' => $grnItem->received_quantity,
                    'quantity_current' => $grnItem->received_quantity,
                    'quantity_dispensed' => 0,
                    'quantity_adjusted' => 0,
                    'quantity_damaged' => 0,
                    'quantity_expired' => 0,
                    'status' => 'active',
                    'supplier_batch_number' => $grnItem->batch_number,
                ]);

                // Update medication total stock
                $medication = Medication::find($grnItem->item_id);
                $medication->increment('stock_quantity', $grnItem->received_quantity);

                // Create stock movement record
                $this->createStockMovement([
                    'movement_type' => 'in',
                    'transaction_type' => 'purchase',
                    'source_type' => 'grn',
                    'source_id' => $grn->id,
                    'medication_id' => $grnItem->item_id,
                    'batch_number' => $grnItem->batch_number,
                    'from_location_id' => null, // From supplier
                    'to_location_id' => $ledgerEntry->location_id, // Main store
                    'quantity' => $grnItem->received_quantity,
                    'unit_cost' => $grnItem->unit_cost,
                    'reference_number' => $grn->grn_number,
                    'movement_date' => now(),
                    'notes' => "GRN receipt: {$grn->grn_number}"
                ]);
            }

            DB::commit();
            Log::info("GRN {$grn->grn_number} successfully processed");
            
            return [
                'success' => true,
                'message' => 'GRN items received and stock updated successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error processing GRN {$grn->grn_number}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error processing GRN: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process requisition issuance with FIFO logic
     */
    public function issueRequisition(StoreRequisition $requisition)
    {
        DB::beginTransaction();
        
        try {
            foreach ($requisition->items as $reqItem) {
                $remainingQuantity = $reqItem->quantity_requested;
                
                // Get available stock using FIFO (First In, First Out)
                $availableStock = MedicationLedger::where('medication_id', $reqItem->item_id)
                    ->where('quantity_current', '>', 0)
                    ->where('status', 'active')
                    ->orderBy('expiry_date', 'asc') // FEFO: First Expiry, First Out
                    ->orderBy('created_at', 'asc')  // FIFO: First In, First Out
                    ->get();

                if ($availableStock->sum('quantity_current') < $remainingQuantity) {
                    throw new Exception("Insufficient stock for medication ID {$reqItem->item_id}");
                }

                foreach ($availableStock as $ledgerEntry) {
                    if ($remainingQuantity <= 0) break;

                    $quantityToDeduct = min($remainingQuantity, $ledgerEntry->quantity_current);
                    
                    // Update ledger entry
                    $ledgerEntry->decrement('quantity_current', $quantityToDeduct);
                    
                    // Create location stock entry
                    $locationStock = StoreLocationStock::firstOrCreate([
                        'location_id' => $requisition->requesting_location_id,
                        'medication_id' => $reqItem->item_id,
                        'batch_number' => $ledgerEntry->batch_number,
                        'expiry_date' => $ledgerEntry->expiry_date,
                    ], [
                        'grn_item_id' => $ledgerEntry->grn_item_id,
                        'unit_cost' => $ledgerEntry->unit_cost,
                        'quantity_current' => 0,
                        'quantity_dispensed' => 0,
                        'quantity_used' => 0,
                        'quantity_discarded' => 0,
                        'status' => 'active',
                        'last_movement_date' => now()
                    ]);
                    
                    $locationStock->increment('quantity_current', $quantityToDeduct);
                    $locationStock->update(['last_movement_date' => now()]);

                    // Create stock movement record
                    $this->createStockMovement([
                        'movement_type' => 'out',
                        'transaction_type' => 'requisition',
                        'source_type' => 'requisition',
                        'source_id' => $requisition->id,
                        'medication_id' => $reqItem->item_id,
                        'batch_number' => $ledgerEntry->batch_number,
                        'from_location_id' => $ledgerEntry->location_id, // Main store
                        'to_location_id' => $requisition->requesting_location_id,
                        'quantity' => $quantityToDeduct,
                        'unit_cost' => $ledgerEntry->unit_cost,
                        'reference_number' => $requisition->requisition_number,
                        'movement_date' => now(),
                        'notes' => "Requisition issue: {$requisition->requisition_number}"
                    ]);

                    $remainingQuantity -= $quantityToDeduct;
                }

                // Update requisition item
                $reqItem->update([
                    'quantity_issued' => $reqItem->quantity_requested,
                    'status' => 'fully_issued'
                ]);
            }

            // Update requisition status
            $requisition->update([
                'status' => 'fully_issued',
                'issued_by' => Auth::id(),
                'issued_at' => now()
            ]);

            DB::commit();
            Log::info("Requisition {$requisition->requisition_number} successfully issued");
            
            return [
                'success' => true,
                'message' => 'Requisition issued successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error issuing requisition {$requisition->requisition_number}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error issuing requisition: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Transfer stock between locations
     */
    public function transferStock($fromLocationId, $toLocationId, $items, $transferReference = null)
    {
        DB::beginTransaction();
        
        try {
            foreach ($items as $item) {
                $medicationId = $item['medication_id'];
                $quantity = $item['quantity'];
                $batchNumber = $item['batch_number'] ?? null;
                
                // Find source stock
                $sourceStockQuery = StoreLocationStock::where('location_id', $fromLocationId)
                    ->where('medication_id', $medicationId)
                    ->where('quantity_current', '>', 0);
                
                if ($batchNumber) {
                    $sourceStockQuery->where('batch_number', $batchNumber);
                }
                
                $sourceStock = $sourceStockQuery->orderBy('expiry_date', 'asc')->first();
                
                if (!$sourceStock || $sourceStock->quantity_current < $quantity) {
                    throw new Exception("Insufficient stock at source location for medication ID {$medicationId}");
                }

                // Deduct from source
                $sourceStock->decrement('quantity_current', $quantity);
                $sourceStock->update(['last_movement_date' => now()]);

                // Add to destination
                $destinationStock = StoreLocationStock::firstOrCreate([
                    'location_id' => $toLocationId,
                    'medication_id' => $medicationId,
                    'batch_number' => $sourceStock->batch_number,
                    'expiry_date' => $sourceStock->expiry_date,
                ], [
                    'grn_item_id' => $sourceStock->grn_item_id,
                    'unit_cost' => $sourceStock->unit_cost,
                    'quantity_current' => 0,
                    'quantity_dispensed' => 0,
                    'quantity_used' => 0,
                    'quantity_discarded' => 0,
                    'status' => 'active',
                    'last_movement_date' => now()
                ]);
                
                $destinationStock->increment('quantity_current', $quantity);
                $destinationStock->update(['last_movement_date' => now()]);

                // Create stock movement record
                $this->createStockMovement([
                    'movement_type' => 'transfer',
                    'transaction_type' => 'transfer',
                    'source_type' => 'transfer',
                    'source_id' => null,
                    'medication_id' => $medicationId,
                    'batch_number' => $sourceStock->batch_number,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $toLocationId,
                    'quantity' => $quantity,
                    'unit_cost' => $sourceStock->unit_cost,
                    'reference_number' => $transferReference,
                    'movement_date' => now(),
                    'notes' => "Stock transfer: {$transferReference}"
                ]);
            }

            DB::commit();
            Log::info("Stock transfer completed: {$transferReference}");
            
            return [
                'success' => true,
                'message' => 'Stock transferred successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error transferring stock: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error transferring stock: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record consumption from location stock
     */
    public function recordConsumption($consumptionType, $consumptionData)
    {
        DB::beginTransaction();
        
        try {
            $locationId = $consumptionData['location_id'];
            $medicationId = $consumptionData['medication_id'];
            $quantity = $consumptionData['quantity'];
            $consumedBy = $consumptionData['consumed_by'];
            $reference = $consumptionData['reference'] ?? null;
            
            // Find available stock using FIFO
            $availableStock = StoreLocationStock::where('location_id', $locationId)
                ->where('medication_id', $medicationId)
                ->where('quantity_current', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($availableStock->sum('quantity_current') < $quantity) {
                throw new Exception("Insufficient stock for consumption");
            }

            $remainingQuantity = $quantity;
            
            foreach ($availableStock as $stock) {
                if ($remainingQuantity <= 0) break;

                $quantityToConsume = min($remainingQuantity, $stock->quantity_current);
                
                // Update stock based on consumption type
                switch ($consumptionType) {
                    case 'prescription':
                        $stock->increment('quantity_dispensed', $quantityToConsume);
                        break;
                    case 'investigation':
                    case 'procedure':
                        $stock->increment('quantity_used', $quantityToConsume);
                        break;
                }
                
                $stock->decrement('quantity_current', $quantityToConsume);
                $stock->update(['last_movement_date' => now()]);

                // Create stock movement
                $this->createStockMovement([
                    'movement_type' => 'out',
                    'transaction_type' => 'consumption',
                    'source_type' => $consumptionType,
                    'source_id' => $consumptionData['source_id'] ?? null,
                    'medication_id' => $medicationId,
                    'batch_number' => $stock->batch_number,
                    'from_location_id' => $locationId,
                    'to_location_id' => null,
                    'quantity' => $quantityToConsume,
                    'unit_cost' => $stock->unit_cost,
                    'reference_number' => $reference,
                    'movement_date' => now(),
                    'notes' => ucfirst($consumptionType) . " consumption: {$reference}"
                ]);

                $remainingQuantity -= $quantityToConsume;
            }

            DB::commit();
            Log::info("Consumption recorded: {$consumptionType} - {$reference}");
            
            return [
                'success' => true,
                'message' => 'Consumption recorded successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error recording consumption: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error recording consumption: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Dispose unfit medications
     */
    public function disposeUnfitMedications($items, $reason, $disposalMethod, $disposedBy, $notes = null)
    {
        DB::beginTransaction();
        
        try {
            foreach ($items as $item) {
                $sourceType = $item['source_type']; // 'ledger' or 'location_stock'
                $sourceId = $item['source_id'];
                $quantity = $item['quantity'];

                // Get source record
                if ($sourceType === 'ledger') {
                    $source = MedicationLedger::find($sourceId);
                    $source->increment('quantity_damaged', $quantity);
                    $source->decrement('quantity_current', $quantity);
                } else {
                    $source = StoreLocationStock::find($sourceId);
                    $source->increment('quantity_discarded', $quantity);
                    $source->decrement('quantity_current', $quantity);
                    $source->update(['last_movement_date' => now()]);
                }

                // Create unfit medication record
                UnfitMedication::create([
                    'medication_id' => $source->medication_id,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'batch_number' => $source->batch_number,
                    'expiry_date' => $source->expiry_date,
                    'quantity_discarded' => $quantity,
                    'reason' => $reason,
                    'disposal_method' => $disposalMethod,
                    'disposed_by' => $disposedBy,
                    'disposed_at' => now(),
                    'notes' => $notes,
                    'verification_required' => $quantity > 100 || $reason === 'recalled',
                ]);

                // Create stock movement
                $this->createStockMovement([
                    'movement_type' => 'out',
                    'transaction_type' => 'disposal',
                    'source_type' => 'disposal',
                    'source_id' => $sourceId,
                    'medication_id' => $source->medication_id,
                    'batch_number' => $source->batch_number,
                    'from_location_id' => $sourceType === 'location_stock' ? $source->location_id : null,
                    'to_location_id' => null,
                    'quantity' => $quantity,
                    'unit_cost' => $source->unit_cost,
                    'reference_number' => 'DISPOSAL-' . date('YmdHis'),
                    'movement_date' => now(),
                    'notes' => "Disposal: {$reason} - {$disposalMethod}"
                ]);
            }

            DB::commit();
            Log::info("Unfit medications disposed");
            
            return [
                'success' => true,
                'message' => 'Unfit medications disposed successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error disposing unfit medications: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error disposing medications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create stock movement record
     */
    private function createStockMovement($data)
    {
        return StoreStockMovement::create(array_merge($data, [
            'created_by' => Auth::id(),
            'balance_before' => 0, // Calculate if needed
            'balance_after' => 0,  // Calculate if needed
            'total_cost' => $data['quantity'] * $data['unit_cost']
        ]));
    }

    /**
     * Get stock availability for a medication at specific location
     */
    public function getStockAvailability($medicationId, $locationId = null)
    {
        if ($locationId) {
            return StoreLocationStock::where('medication_id', $medicationId)
                ->where('location_id', $locationId)
                ->where('quantity_current', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();
        }

        return MedicationLedger::where('medication_id', $medicationId)
            ->where('quantity_current', '>', 0)
            ->where('status', 'active')
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Check for low stock alerts
     */
    public function getLowStockAlerts($threshold = 10)
    {
        return Medication::where('stock_quantity', '<=', $threshold)
            ->with(['category', 'unit'])
            ->get();
    }

    /**
     * Check for expiring medications
     */
    public function getExpiringMedications($days = 30)
    {
        $expiryDate = now()->addDays($days);

        return MedicationLedger::where('expiry_date', '<=', $expiryDate)
            ->where('quantity_current', '>', 0)
            ->where('status', 'active')
            ->with(['medication'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get recent stock movements for dashboard
     */
    public function getRecentStockMovements($limit = 10)
    {
        return StoreStockMovement::with(['medication', 'fromLocation', 'toLocation', 'createdBy'])
            ->orderBy('movement_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Adjust stock levels (increase/decrease)
     */
    public function adjustStock($adjustmentData)
    {
        DB::beginTransaction();
        
        try {
            $locationId = $adjustmentData['location_id'];
            $medicationId = $adjustmentData['medication_id'];
            $adjustmentType = $adjustmentData['adjustment_type'];
            $quantity = $adjustmentData['quantity'];
            $reason = $adjustmentData['reason'];
            $batchNumber = $adjustmentData['batch_number'] ?? 'ADJ-' . now()->format('YmdHis');
            
            if ($adjustmentType === 'increase') {
                // Add stock
                StoreLocationStock::create([
                    'location_id' => $locationId,
                    'medication_id' => $medicationId,
                    'batch_number' => $batchNumber,
                    'expiry_date' => now()->addYear(), // Default 1 year expiry for adjustments
                    'quantity_current' => $quantity,
                    'quantity_received' => $quantity,
                    'unit_cost' => 0, // Adjustment items have no cost
                    'status' => 'active',
                    'last_movement_date' => now()
                ]);
                
                $movementType = 'inward';
            } else {
                // Decrease stock using FIFO
                $availableStock = $this->getStockAvailability($medicationId, $locationId);
                
                if ($availableStock->sum('quantity_current') < $quantity) {
                    throw new Exception("Insufficient stock for adjustment. Available: " . $availableStock->sum('quantity_current'));
                }
                
                $remainingToAdjust = $quantity;
                foreach ($availableStock as $stock) {
                    if ($remainingToAdjust <= 0) break;
                    
                    $adjustFromBatch = min($remainingToAdjust, $stock->quantity_current);
                    
                    $stock->decrement('quantity_current', $adjustFromBatch);
                    
                    if ($stock->quantity_current <= 0) {
                        $stock->update(['status' => 'depleted']);
                    }
                    
                    $remainingToAdjust -= $adjustFromBatch;
                }
                
                $movementType = 'outward';
            }
            
            // Record stock movement
            StoreStockMovement::create([
                'movement_type' => $movementType,
                'source_type' => 'adjustment',
                'source_id' => null,
                'medication_id' => $medicationId,
                'batch_number' => $batchNumber,
                'from_location_id' => $adjustmentType === 'decrease' ? $locationId : null,
                'to_location_id' => $adjustmentType === 'increase' ? $locationId : null,
                'quantity' => $quantity,
                'unit_cost' => 0,
                'reference_number' => 'ADJ-' . now()->format('YmdHis'),
                'movement_date' => now(),
                'created_by' => Auth::id() ?? 1,
                'notes' => $reason
            ]);
            
            DB::commit();
            Log::info("Stock adjustment completed", $adjustmentData);
            
            return [
                'success' => true,
                'message' => 'Stock adjustment completed successfully'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Stock adjustment failed: " . $e->getMessage(), $adjustmentData);
            
            return [
                'success' => false,
                'message' => 'Stock adjustment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get expiry alerts for medications expiring within specified days
     */
    public function getExpiryAlerts($daysAhead = 30)
    {
        $alertDate = now()->addDays($daysAhead);
        
        return StoreLocationStock::with(['medication', 'location'])
            ->whereHas('medication', function ($query) use ($alertDate) {
                $query->where('expiry_date', '<=', $alertDate)
                      ->where('expiry_date', '>', now())
                      ->where('status', 'active');
            })
            ->where('available_quantity', '>', 0)
            ->get()
            ->map(function ($stock) {
                $daysToExpiry = now()->diffInDays($stock->medication->expiry_date, false);
                
                return [
                    'medication_id' => $stock->medication_id,
                    'medication_name' => $stock->medication->name,
                    'location_name' => $stock->location->name,
                    'available_quantity' => $stock->available_quantity,
                    'expiry_date' => $stock->medication->expiry_date,
                    'days_to_expiry' => $daysToExpiry,
                    'severity' => $daysToExpiry <= 7 ? 'critical' : 
                                 ($daysToExpiry <= 30 ? 'warning' : 'notice')
                ];
            })
            ->sortBy('days_to_expiry')
            ->values()
            ->toArray();
    }
}
