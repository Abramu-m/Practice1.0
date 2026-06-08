<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\MedicationLedger;
use App\Models\StoreLocationStock;
use App\Models\GoodsReceivedNoteItem;
use App\Models\StoreStockMovement;
use App\Models\StoreLocation;
use App\Models\StockCorrection;
use App\Models\ReconciliationRun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReconciliationService
{
    /**
     * Run comprehensive stock integrity check and record the run.
     */
    public function checkStockIntegrity()
    {
        $startedAt = microtime(true);

        $run = ReconciliationRun::create([
            'run_type'     => 'manual',
            'triggered_by' => Auth::id(),
            'status'       => 'in_progress',
        ]);

        try {
            $report = [
                'total_medications' => 0,
                'discrepancies' => [],
                'summary' => [
                    'medications_with_discrepancies' => 0,
                    'total_discrepancy_value' => 0,
                    'critical_discrepancies' => 0,
                ],
                'checks_performed' => [
                    'medication_stock_vs_ledger' => [],
                    'ledger_vs_grn_items'        => [],
                    'location_stock_vs_issued'   => [],
                    'movement_audit_trail'       => [],
                ],
            ];

            $report['checks_performed']['medication_stock_vs_ledger'] = $this->checkMedicationStockVsLedger();
            $report['checks_performed']['ledger_vs_grn_items']        = $this->checkLedgerVsGrnItems();
            $report['checks_performed']['location_stock_vs_issued']   = $this->checkLocationStockVsIssued();
            $report['checks_performed']['movement_audit_trail']       = $this->checkMovementAuditTrail();

            $report = $this->compileSummary($report);

            $totalDiscrepancies = collect($report['checks_performed'])->flatten(1)->count();

            $run->update([
                'status'                    => 'completed',
                'total_medications_checked' => $report['total_medications'],
                'discrepancies_found'       => $totalDiscrepancies,
                'duration_seconds'          => max(0, (int) round(microtime(true) - $startedAt)),
            ]);

            return $report;

        } catch (\Exception $e) {
            $run->update(['status' => 'failed', 'notes' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check medication.stock_quantity against sum of store_location_stock quantities.
     * These two must always agree: the medication record holds the aggregate balance,
     * location stock holds where that balance is physically distributed.
     */
    private function checkMedicationStockVsLedger()
    {
        $discrepancies = [];

        // Single query: sum location stock per medication
        $locationTotals = StoreLocationStock::selectRaw('medication_id, SUM(quantity) as total')
            ->groupBy('medication_id')
            ->pluck('total', 'medication_id');

        $medications = Medication::all();

        foreach ($medications as $medication) {
            $medicationStock = (float) $medication->stock_quantity;
            $locationTotal   = (float) ($locationTotals[$medication->id] ?? 0);
            $diff            = $medicationStock - $locationTotal;

            if (abs($diff) > 0.01) {
                $discrepancies[] = [
                    'medication_id'    => $medication->id,
                    'medication_name'  => $medication->generic_name ?? $medication->name,
                    'medication_stock' => $medicationStock,
                    'location_total'   => $locationTotal,
                    'difference'       => $diff,
                    'severity'         => abs($diff) > 10 ? 'critical' : 'minor',
                    'type'             => 'stock_vs_location',
                ];
            }
        }

        return $discrepancies;
    }

    /**
     * Check ledger entries against GRN items
     */
    private function checkLedgerVsGrnItems()
    {
        $discrepancies = [];
        
        $ledgerEntries = MedicationLedger::with(['grnItem'])
            ->whereNotNull('grn_item_id')
            ->get();
        
        foreach ($ledgerEntries as $entry) {
            if (!$entry->grnItem) {
                $discrepancies[] = [
                    'ledger_id' => $entry->id,
                    'medication_name' => $entry->medication->generic_name ?? 'Unknown',
                    'issue' => 'Missing GRN item reference',
                    'severity' => 'critical',
                    'type' => 'missing_grn_reference'
                ];
                continue;
            }

            // Check if quantities match
            if (abs($entry->quantity_received - $entry->grnItem->received_quantity) > 0.01) {
                $discrepancies[] = [
                    'ledger_id' => $entry->id,
                    'grn_item_id' => $entry->grnItem->id,
                    'medication_name' => $entry->medication->generic_name ?? 'Unknown',
                    'ledger_quantity' => $entry->quantity_received,
                    'grn_quantity' => $entry->grnItem->received_quantity,
                    'difference' => $entry->quantity_received - $entry->grnItem->received_quantity,
                    'severity' => 'critical',
                    'type' => 'quantity_mismatch'
                ];
            }

            // Check batch numbers match
            if ($entry->batch_number !== $entry->grnItem->batch_number) {
                $discrepancies[] = [
                    'ledger_id' => $entry->id,
                    'grn_item_id' => $entry->grnItem->id,
                    'medication_name' => $entry->medication->name ?? 'Unknown',
                    'issue' => 'Batch number mismatch',
                    'ledger_batch' => $entry->batch_number,
                    'grn_batch' => $entry->grnItem->batch_number,
                    'severity' => 'major',
                    'type' => 'batch_mismatch'
                ];
            }
        }

        return $discrepancies;
    }

    /**
     * Check location stock against issued quantities
     */
    private function checkLocationStockVsIssued()
    {
        $discrepancies = [];
        
        // Get all location stock grouped by medication and batch
        $locationStocks = StoreLocationStock::selectRaw('
            medication_id, 
            batch_number, 
            SUM(quantity) as total_quantity
        ')
        ->groupBy('medication_id', 'batch_number')
        ->get();

        foreach ($locationStocks as $stock) {
            // Check against movements - compare location stock with outward movements
            $outwardMovements = StoreStockMovement::where('item_id', $stock->medication_id)
                ->where('item_type', 'medication')
                ->where('batch_number', $stock->batch_number)
                ->whereIn('transaction_type', ['requisition', 'consumption', 'disposal'])
                ->where('movement_type', 'out')
                ->sum('quantity');

            // For now, we'll compare current stock vs expected remaining after movements
            // This is a simplified check since we don't track consumption separately
            $inwardMovements = StoreStockMovement::where('item_id', $stock->medication_id)
                ->where('item_type', 'medication')
                ->where('batch_number', $stock->batch_number)
                ->where('movement_type', 'in')
                ->sum('quantity');
                
            $expectedRemaining = $inwardMovements - $outwardMovements;
            
            if (abs($expectedRemaining - $stock->total_quantity) > 0.01) {
                $discrepancies[] = [
                    'medication_id' => $stock->medication_id,
                    'batch_number' => $stock->batch_number,
                    'current_stock' => $stock->total_quantity,
                    'expected_remaining' => $expectedRemaining,
                    'inward_movements' => $inwardMovements,
                    'outward_movements' => $outwardMovements,
                    'difference' => $stock->total_quantity - $expectedRemaining,
                    'severity' => abs($stock->total_quantity - $expectedRemaining) > 5 ? 'major' : 'minor',
                    'type' => 'stock_vs_movements'
                ];
            }
        }

        return $discrepancies;
    }

    /**
     * Check movement audit trail completeness
     */
    private function checkMovementAuditTrail()
    {
        $issues = [];
        
        // Check for movements without proper references
        $orphanedMovements = StoreStockMovement::whereNull('reference_id')
            ->whereNotIn('transaction_type', ['adjustment', 'transfer'])
            ->count();

        if ($orphanedMovements > 0) {
            $issues[] = [
                'issue' => "Found {$orphanedMovements} movements without reference IDs",
                'severity' => 'major',
                'type' => 'orphaned_movements'
            ];
        }

        // Check for duplicate movements
        $duplicateMovements = DB::table('store_stock_movements')
            ->select('transaction_type', 'reference_id', 'item_id', 'batch_number', DB::raw('COUNT(*) as count'))
            ->groupBy('transaction_type', 'reference_id', 'item_id', 'batch_number')
            ->having('count', '>', 1)
            ->get();

        if ($duplicateMovements->count() > 0) {
            $issues[] = [
                'issue' => "Found {$duplicateMovements->count()} potential duplicate movements",
                'severity' => 'major',
                'type' => 'duplicate_movements',
                'details' => $duplicateMovements->toArray()
            ];
        }

        return $issues;
    }

    /**
     * Compile summary from all checks
     */
    private function compileSummary($report)
    {
        $allDiscrepancies = collect();
        
        foreach ($report['checks_performed'] as $checkName => $discrepancies) {
            $allDiscrepancies = $allDiscrepancies->merge($discrepancies);
        }

        $report['total_medications'] = Medication::count();
        $report['summary']['medications_with_discrepancies'] = $allDiscrepancies
            ->pluck('medication_id')
            ->unique()
            ->count();
        
        $report['summary']['critical_discrepancies'] = $allDiscrepancies
            ->where('severity', 'critical')
            ->count();

        $report['summary']['total_discrepancy_value'] = $allDiscrepancies
            ->where('type', 'stock_vs_ledger')
            ->sum('difference');

        // Add overall status based on discrepancies found
        $criticalCount = $report['summary']['critical_discrepancies'];
        $totalDiscrepancies = $allDiscrepancies->count();
        
        if ($criticalCount > 0) {
            $report['status'] = 'critical';
        } elseif ($totalDiscrepancies > 10) {
            $report['status'] = 'warning';
        } elseif ($totalDiscrepancies > 0) {
            $report['status'] = 'minor_issues';
        } else {
            $report['status'] = 'good';
        }

        return $report;
    }

    /**
     * Generate detailed discrepancy report
     */
    public function generateDiscrepancyReport($medicationId = null)
    {
        $query = MedicationLedger::with(['medication', 'grnItem']);
        
        if ($medicationId) {
            $query->where('medication_id', $medicationId);
        }

        $ledgerEntries = $query->get();
        $discrepancies = [];

        foreach ($ledgerEntries as $entry) {
            $issues = [];
            
            // Check expiry status
            if ($entry->expiry_date < now() && $entry->status === 'active') {
                $issues[] = 'Expired but still active';
            }

            // Check negative quantities
            if ($entry->quantity_received < 0) {
                $issues[] = 'Negative received quantity';
            }

            // Basic quantity validation - ensure received quantity makes sense
            if ($entry->quantity_received == 0 && $entry->status === 'active') {
                $issues[] = 'Active entry with zero quantity';
            }

            if (!empty($issues)) {
                $discrepancies[] = [
                    'ledger_id'         => $entry->id,
                    'medication_id'     => $entry->medication_id,
                    'medication'        => $entry->medication->name ?? 'Unknown',
                    'batch_number'      => $entry->batch_number,
                    'issues'            => $issues,
                    'received_quantity' => $entry->quantity_received,
                    'status'            => $entry->status,
                    'expiry_date'       => $entry->expiry_date,
                    'severity'          => $entry->expiry_date < now() ? 'critical' : 'minor',
                ];
            }
        }

        return $discrepancies;
    }

    /**
     * Auto-correct minor discrepancies.
     * Stock corrections: syncs medication.stock_quantity to sum of location stock
     * when the variance is <= 5 units (safe threshold).
     * Expiry corrections: marks ledger entries whose expiry_date has passed as 'expired'.
     */
    public function autoCorrectMinorDiscrepancies($dryRun = false)
    {
        $corrections = [];

        // Sync medication.stock_quantity to SUM(store_location_stock.quantity) for minor variances
        $locationTotals = StoreLocationStock::selectRaw('medication_id, SUM(quantity) as total')
            ->groupBy('medication_id')
            ->pluck('total', 'medication_id');

        $medications = Medication::all();

        foreach ($medications as $medication) {
            $locationTotal = (float) ($locationTotals[$medication->id] ?? 0);
            $currentStock  = (float) $medication->stock_quantity;
            $diff          = abs($currentStock - $locationTotal);

            if ($diff > 0.01 && $diff <= 5) {
                $corrections[] = [
                    'type'          => 'medication_stock_correction',
                    'medication_id' => $medication->id,
                    'medication_name' => $medication->name,
                    'old_quantity'  => $currentStock,
                    'new_quantity'  => $locationTotal,
                    'difference'    => $locationTotal - $currentStock,
                ];

                if (!$dryRun) {
                    $medication->update(['stock_quantity' => $locationTotal]);
                    StockCorrection::create([
                        'medication_id'   => $medication->id,
                        'location_id'     => null,
                        'correction_type' => 'auto',
                        'field_corrected' => 'stock_quantity',
                        'old_value'       => $currentStock,
                        'new_value'       => $locationTotal,
                        'difference'      => $locationTotal - $currentStock,
                        'reason'          => 'Auto-correction: stock_quantity synced to sum of location stock',
                        'corrected_by'    => null,
                        'correction_date' => now(),
                    ]);
                }
            }
        }

        // Mark expired ledger batches
        $expiredEntries = MedicationLedger::where('expiry_date', '<', now())
            ->where('status', 'active')
            ->get();

        foreach ($expiredEntries as $entry) {
            $corrections[] = [
                'type'            => 'expired_status_correction',
                'ledger_id'       => $entry->id,
                'medication_name' => $entry->medication->name ?? 'Unknown',
                'batch_number'    => $entry->batch_number,
                'expiry_date'     => $entry->expiry_date,
                'old_status'      => $entry->status,
                'new_status'      => 'expired',
            ];

            if (!$dryRun) {
                $entry->update(['status' => 'expired']);
                StockCorrection::create([
                    'medication_id'   => $entry->medication_id,
                    'location_id'     => null,
                    'correction_type' => 'expired_status',
                    'field_corrected' => 'status',
                    'old_value'       => null,
                    'new_value'       => null,
                    'difference'      => null,
                    'reason'          => 'Auto-correction: expired batch status updated',
                    'corrected_by'    => null,
                    'correction_date' => now(),
                ]);
            }
        }

        return [
            'success'          => true,
            'corrections_made' => count($corrections),
            'message'          => empty($corrections) ? 'No minor discrepancies found.' : 'Auto-correction completed.',
            'corrections'      => $corrections,
        ];
    }

    /**
     * Validate stock balance for specific medication
     */
    public function validateStockBalance($medicationId)
    {
        $medication = Medication::with(['ledgerEntries', 'locationStock'])->find($medicationId);
        
        if (!$medication) {
            return ['error' => 'Medication not found'];
        }

        $validation = [
            'medication_id' => $medicationId,
            'medication_name' => $medication->name,
            'medication_stock' => $medication->stock_quantity,
            'ledger_total' => $medication->ledgerEntries->sum('quantity_received'),
            'location_total' => StoreLocationStock::where('medication_id', $medication->id)->sum('quantity'),
            'movements' => []
        ];

        // Get movement totals
        $inwardMovements = StoreStockMovement::where('item_id', $medicationId)
            ->where('item_type', 'medication')
            ->whereIn('movement_type', ['in'])
            ->sum('quantity');

        $outwardMovements = StoreStockMovement::where('item_id', $medicationId)
            ->where('item_type', 'medication')
            ->whereIn('movement_type', ['out'])
            ->sum('quantity');

        $validation['movements'] = [
            'inward_total' => $inwardMovements,
            'outward_total' => $outwardMovements,
            'net_movement' => $inwardMovements - $outwardMovements
        ];

        // Calculate discrepancies
        $validation['discrepancies'] = [
            'medication_vs_ledger' => $validation['medication_stock'] - $validation['ledger_total'],
            'ledger_vs_location' => $validation['ledger_total'] - $validation['location_total'],
            'stock_vs_movements' => $validation['medication_stock'] - $validation['movements']['net_movement']
        ];

        $validation['is_balanced'] = abs($validation['discrepancies']['medication_vs_ledger']) < 0.01;

        return $validation;
    }

    /**
     * Get reconciliation dashboard data
     */
    public function getDashboardData()
    {
        return [
            'total_medications' => Medication::count(),
            'active_batches' => MedicationLedger::where('status', 'active')->count(),
            'expired_batches' => MedicationLedger::where('status', 'expired')->count(),
            'low_stock_items' => Medication::where('stock_quantity', '<=', 10)->count(),
            'expiring_soon' => MedicationLedger::where('expiry_date', '<=', now()->addDays(30))
                ->where('status', 'active')
                ->count(),
            'total_locations' => StoreLocationStock::distinct('location_id')->count(),
            'pending_disposals' => \App\Models\UnfitMedication::where('verification_required', true)
                ->whereNull('verified_at')
                ->count(),
            'last_reconciliation' => $this->getLastReconciliationDate()
        ];
    }

    private function getLastReconciliationDate()
    {
        return ReconciliationRun::where('status', 'completed')->latest()->value('created_at');
    }

    /**
     * Get dashboard metrics for reconciliation overview
     */
    public function getDashboardMetrics()
    {
        $totalMedications = Medication::where('is_active', true)->count();
        
        $integrityCheck = $this->checkStockIntegrity();
        $discrepancyItems = $this->generateDiscrepancyReport();

        $totalDiscrepancies    = count($discrepancyItems);
        $criticalDiscrepancies = collect($discrepancyItems)->where('severity', 'critical')->count();
        
        $totalCorrections = StockCorrection::whereMonth('correction_date', now()->month)
            ->whereYear('correction_date', now()->year)
            ->count();
        
        $averageAccuracy = $totalMedications > 0 ? 
            (($totalMedications - $totalDiscrepancies) / $totalMedications) * 100 : 100;

        return [
            'total_medications' => $totalMedications,
            'integrity_status' => $integrityCheck['status'],
            'total_discrepancies' => $totalDiscrepancies,
            'critical_discrepancies' => $criticalDiscrepancies,
            'accuracy_percentage' => round($averageAccuracy, 2),
            'corrections_this_month' => $totalCorrections,
            'last_check_time' => now()->format('Y-m-d H:i:s')
        ];
    }

    public function getRecentCorrections($limit = 20)
    {
        return StockCorrection::with(['medication', 'location', 'correctedBy'])
            ->orderBy('correction_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit trail for stock movements
     */
    public function getAuditTrail($filters = [])
    {
        $query = StoreStockMovement::with(['medication', 'storeLocation']);

        if (!empty($filters['medication_id'])) {
            $query->where('item_id', $filters['medication_id'])
                  ->where('item_type', 'medication');
        }

        if (!empty($filters['location_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('from_location_id', $filters['location_id'])
                  ->orWhere('to_location_id', $filters['location_id']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('movement_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('movement_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        $limit = $filters['limit'] ?? 100;

        return $query->orderBy('movement_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'medication_name' => $movement->medication->name ?? 'Unknown',
                    'movement_type' => $movement->movement_type,
                    'quantity' => $movement->quantity,
                    'from_location' => $movement->fromLocation->name ?? 'N/A',
                    'to_location' => $movement->toLocation->name ?? 'N/A',
                    'movement_date' => $movement->movement_date,
                    'reference_number' => $movement->reference_number,
                    'notes' => $movement->notes,
                    'created_by' => $movement->created_by ?? 'System'
                ];
            });
    }

    /**
     * Process manual stock correction
     */
    public function manualStockCorrection($correctionData)
    {
        try {
            DB::beginTransaction();

            $medication = Medication::findOrFail($correctionData['medication_id']);
            $location = StoreLocation::findOrFail($correctionData['location_id']);

            $correctionRecord = StockCorrection::create([
                'medication_id'   => $correctionData['medication_id'],
                'location_id'     => $correctionData['location_id'],
                'correction_type' => $correctionData['correction_type'],
                'field_corrected' => $correctionData['field_to_correct'],
                'old_value'       => $correctionData['current_value'],
                'new_value'       => $correctionData['corrected_value'],
                'difference'      => $correctionData['corrected_value'] - $correctionData['current_value'],
                'reason'          => $correctionData['reason'],
                'notes'           => $correctionData['notes'] ?? null,
                'corrected_by'    => Auth::id(),
                'correction_date' => now(),
            ]);

            // Apply the correction based on type
            if ($correctionData['correction_type'] === 'ledger') {
                if ($correctionData['field_to_correct'] === 'stock_quantity') {
                    $medication->update(['stock_quantity' => $correctionData['corrected_value']]);
                }
            } elseif ($correctionData['correction_type'] === 'location_stock') {
                // Update location stock
                $locationStock = StoreLocationStock::where('medication_id', $correctionData['medication_id'])
                    ->where('location_id', $correctionData['location_id'])
                    ->first();

                if ($locationStock && $correctionData['field_to_correct'] === 'quantity') {
                    $locationStock->update(['quantity' => $correctionData['corrected_value']]);
                }
            }

            DB::commit();

            return [
                'success'       => true,
                'message'       => 'Stock correction applied successfully',
                'correction_id' => $correctionRecord->id,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to apply correction: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Compare stock levels between ledger and locations
     */
    public function compareStockLevels($medicationId)
    {
        $medication = Medication::findOrFail($medicationId);
        
        $ledgerQuantity = (float) $medication->stock_quantity;
        
        $locationStocks = StoreLocationStock::where('medication_id', $medicationId)
            ->with('location')
            ->get();
        
        $totalLocationStock = $locationStocks->sum('quantity');
        
        $difference = $ledgerQuantity - $totalLocationStock;
        
        return [
            'medication' => [
                'id' => $medication->id,
                'name' => $medication->name,
                'generic_name' => $medication->generic_name
            ],
            'ledger_quantity' => $ledgerQuantity,
            'total_location_stock' => $totalLocationStock,
            'difference' => $difference,
            'is_balanced' => $difference === 0,
            'location_breakdown' => $locationStocks->map(function ($stock) {
                return [
                    'location_id' => $stock->location_id,
                    'location_name' => $stock->location->name,
                    'available_quantity' => $stock->available_quantity,
                    'reserved_quantity' => $stock->reserved_quantity,
                    'last_updated' => $stock->updated_at->format('Y-m-d H:i:s')
                ];
            }),
            'status' => $difference === 0 ? 'balanced' : 
                       ($difference > 0 ? 'ledger_excess' : 'location_excess'),
            'severity' => abs($difference) > 10 ? 'high' : 
                         (abs($difference) > 0 ? 'medium' : 'none')
        ];
    }
}
