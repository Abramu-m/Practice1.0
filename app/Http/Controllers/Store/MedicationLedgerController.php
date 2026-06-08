<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\MedicationLedger;
use App\Models\Medication;
use App\Models\GoodsReceivedNote;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MedicationLedgerController extends Controller
{
    /**
     * Display a listing of medication ledger entries
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $medication_id = $request->get('medication_id');
        $status = $request->get('status', 'all');
        $location_id = $request->get('location_id');
        $grn_id = $request->get('grn_id');
        $expiry_status = $request->get('expiry_status', 'all');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        $per_page = $request->get('per_page', 15);

        // Base query with relationships
        $query = MedicationLedger::with([
            'medication:id,generic_name,brand_name,strength,formulation_id',
            'grn:id,grn_number,grn_date,supplier_id',
            'grn.supplier:id,name',
            'location:id,name'
        ]);

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('medication', function($mq) use ($search) {
                      $mq->where('generic_name', 'like', "%{$search}%")
                        ->orWhere('brand_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('grn', function($gq) use ($search) {
                      $gq->where('grn_number', 'like', "%{$search}%");
                  });
            });
        }

        // Apply medication filter
        if ($medication_id && $medication_id !== 'all') {
            $query->where('medication_id', $medication_id);
        }

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply location filter
        if ($location_id && $location_id !== 'all') {
            $query->where('location_id', $location_id);
        }

        // Apply GRN filter
        if ($grn_id && $grn_id !== 'all') {
            $query->where('grn_id', $grn_id);
        }

        // Apply expiry status filter
        if ($expiry_status !== 'all') {
            $now = Carbon::now();
            switch ($expiry_status) {
                case 'expired':
                    $query->where('expiry_date', '<', $now);
                    break;
                case 'expiring_soon':
                    $query->whereBetween('expiry_date', [$now, $now->copy()->addMonths(6)]);
                    break;
                case 'valid':
                    $query->where('expiry_date', '>', $now->copy()->addMonths(6));
                    break;
            }
        }

        // Apply date range filter
        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        // Apply sorting
        $allowedSorts = ['created_at', 'expiry_date', 'quantity_received', 'unit_cost', 'batch_number'];
        if (in_array($sort_by, $allowedSorts)) {
            $query->orderBy($sort_by, $sort_order);
        }

        // Get paginated results
        $ledgerEntries = $query->paginate($per_page);

        // Get summary statistics
        $statistics = $this->getLedgerStatistics();

        // Get filter options
        $medications = Medication::where('is_active', true)
            ->orderBy('generic_name')
            ->get(['id', 'generic_name', 'brand_name', 'strength']);

        $locations = StoreLocation::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $grns = GoodsReceivedNote::whereIn('status', ['posted'])
            ->orderBy('grn_number', 'desc')
            ->get(['id', 'grn_number', 'grn_date']);

        return view('medications.stock.ledger.index', compact(
            'ledgerEntries',
            'statistics',
            'medications',
            'locations',
            'grns',
            'search',
            'medication_id',
            'status',
            'location_id',
            'grn_id',
            'expiry_status',
            'date_from',
            'date_to',
            'sort_by',
            'sort_order'
        ));
    }

    /**
     * Show detailed view of a specific ledger entry
     */
    public function show(MedicationLedger $ledger)
    {
        $ledger->load([
            'medication',
            'grn.supplier',
            'location'
        ]);

        return view('medications.stock.ledger.show', compact('ledger'));
    }

    /**
     * Get ledger statistics for dashboard
     */
    public function getLedgerStatistics()
    {
        $now = Carbon::now();
        
        return [
            'total_entries' => MedicationLedger::count(),
            'active_entries' => MedicationLedger::where('status', 'active')->count(),
            'expired_entries' => MedicationLedger::where('expiry_date', '<', $now)->count(),
            'expiring_soon' => MedicationLedger::whereBetween('expiry_date', [
                $now, $now->copy()->addMonths(6)
            ])->count(),
            'total_quantity' => MedicationLedger::where('status', 'active')->sum('quantity_received'),
            'total_value' => MedicationLedger::where('status', 'active')
                ->selectRaw('SUM(quantity_received * unit_cost) as total')
                ->value('total') ?? 0,
            'unique_medications' => MedicationLedger::distinct('medication_id')->count(),
            'unique_batches' => MedicationLedger::distinct('batch_number')->count(),
        ];
    }

    /**
     * Get medication stock summary
     */
    public function stockSummary(Request $request)
    {
        $medication_id = $request->get('medication_id');
        $location_id = $request->get('location_id');

        $query = MedicationLedger::select([
            'medication_id',
            DB::raw('SUM(quantity_received) as total_quantity'),
            DB::raw('AVG(unit_cost) as average_cost'),
            DB::raw('COUNT(*) as batch_count'),
            DB::raw('MIN(expiry_date) as earliest_expiry'),
            DB::raw('MAX(expiry_date) as latest_expiry')
        ])
        ->with('medication:id,generic_name,brand_name,strength')
        ->where('status', 'active')
        ->groupBy('medication_id');

        if ($medication_id && $medication_id !== 'all') {
            $query->where('medication_id', $medication_id);
        }

        if ($location_id && $location_id !== 'all') {
            $query->where('location_id', $location_id);
        }

        $stockSummary = $query->orderBy('total_quantity', 'desc')->get();

        return view('medications.stock.ledger.stock-summary', compact('stockSummary'));
    }

    /**
     * Get expiry report
     */
    public function expiryReport(Request $request)
    {
        $months = $request->get('months', 6); // Default to 6 months
        $location_id = $request->get('location_id');

        $now = Carbon::now();
        $cutoffDate = $now->copy()->addMonths($months);

        $query = MedicationLedger::with([
            'medication:id,generic_name,brand_name,strength',
            'grn:id,grn_number',
            'location:id,name'
        ])
        ->where('status', 'active')
        ->where('expiry_date', '<=', $cutoffDate)
        ->orderBy('expiry_date', 'asc');

        if ($location_id && $location_id !== 'all') {
            $query->where('location_id', $location_id);
        }

        $expiringEntries = $query->get();

        // Group by expiry status
        $expired = $expiringEntries->filter(function($entry) use ($now) {
            return Carbon::parse($entry->expiry_date) < $now;
        });

        $expiringSoon = $expiringEntries->filter(function($entry) use ($now) {
            $expiryDate = Carbon::parse($entry->expiry_date);
            return $expiryDate >= $now && $expiryDate <= $now->copy()->addMonths(3);
        });

        $expiringLater = $expiringEntries->filter(function($entry) use ($now) {
            $expiryDate = Carbon::parse($entry->expiry_date);
            return $expiryDate > $now->copy()->addMonths(3);
        });

        return view('medications.stock.ledger.expiry-report', compact(
            'expired',
            'expiringSoon',
            'expiringLater',
            'months'
        ));
    }

    /**
     * Export ledger data to CSV
     */
    public function export(Request $request)
    {
        $query = MedicationLedger::with([
            'medication:id,generic_name,brand_name,strength',
            'grn:id,grn_number,grn_date',
            'grn.supplier:id,name',
            'location:id,name'
        ]);

        // Apply same filters as index
        if ($request->medication_id && $request->medication_id !== 'all') {
            $query->where('medication_id', $request->medication_id);
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->location_id && $request->location_id !== 'all') {
            $query->where('location_id', $request->location_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $entries = $query->orderBy('created_at', 'desc')->get();

        $filename = 'medication_ledger_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($entries) {
            $file = fopen('php://output', 'w');
            
            // Write CSV header
            fputcsv($file, [
                'ID',
                'Medication',
                'Brand Name',
                'Strength',
                'Batch Number',
                'GRN Number',
                'GRN Date',
                'Supplier',
                'Location',
                'Quantity Received',
                'Unit Cost',
                'Total Value',
                'Manufacture Date',
                'Expiry Date',
                'Status',
                'Created At'
            ]);

            // Write data rows
            foreach ($entries as $entry) {
                fputcsv($file, [
                    $entry->id,
                    $entry->medication->generic_name ?? '',
                    $entry->medication->brand_name ?? '',
                    $entry->medication->strength ?? '',
                    $entry->batch_number,
                    $entry->grn->grn_number ?? '',
                    $entry->grn->grn_date ? $entry->grn->grn_date->format('Y-m-d') : '',
                    $entry->grn->supplier->name ?? '',
                    $entry->location->name ?? '',
                    $entry->quantity_received,
                    $entry->unit_cost,
                    $entry->quantity_received * $entry->unit_cost,
                    $entry->manufacture_date ? Carbon::parse($entry->manufacture_date)->format('Y-m-d') : '',
                    $entry->expiry_date ? Carbon::parse($entry->expiry_date)->format('Y-m-d') : '',
                    $entry->status,
                    $entry->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get batch details for a specific medication
     */
    public function getBatchDetails(Medication $medication)
    {
        $batches = MedicationLedger::where('medication_id', $medication->id)
            ->where('status', 'active')
            ->with(['grn:id,grn_number', 'location:id,name'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json($batches);
    }

    /**
     * Update ledger entry status (for expired items, etc.)
     */
    public function updateStatus(Request $request, MedicationLedger $ledger)
    {
        $request->validate([
            'status' => 'required|in:active,expired,damaged,disposed',
            'notes' => 'nullable|string|max:1000'
        ]);

        $ledger->update([
            'status' => $request->status,
            'notes' => $request->notes ? $ledger->notes . "\n" . now()->format('Y-m-d H:i') . ": " . $request->notes : $ledger->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ledger entry status updated successfully.'
        ]);
    }

    /**
     * Mark medication as unfit and transfer to unfit medications table
     * Enhanced version with comprehensive tracking and stock movement audit trail
     */
    public function markAsUnfit(Request $request, MedicationLedger $ledger)
    {
        // Handle exhausted entries with minimal validation
        if ($request->reason === 'exhausted') {
            try {
                DB::beginTransaction();
                $ledger->delete();
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Exhausted ledger entry removed from system.',
                    'entry_deleted' => true,
                    'redirect_to' => 'index'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete exhausted entry: ' . $e->getMessage()
                ], 500);
            }
        }

        // For all other reasons, apply full validation
        $request->validate([
            'reason' => 'required|in:expired,damaged,recalled,contaminated,quality_issue,other',
            'quantity_discarded' => 'required|numeric|min:0.01|max:' . $ledger->quantity_received,
            'disposal_method' => 'required|in:incineration,return_supplier,secure_disposal,other',
            'disposal_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'requires_verification' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Generate disposal reference number
            $disposalNumber = 'DISP-' . date('Y') . '-' . str_pad(
                (\App\Models\UnfitMedication::count() + 1), 
                6, '0', STR_PAD_LEFT
            );

            // Create comprehensive unfit medication record
            $unfitMedication = \App\Models\UnfitMedication::create([
                'reference_number' => $disposalNumber,
                'medication_id' => $ledger->medication_id,
                'source_type' => 'ledger',
                'source_id' => $ledger->id,
                'batch_number' => $ledger->batch_number,
                'expiry_date' => $ledger->expiry_date,
                'quantity_discarded' => $request->quantity_discarded,
                'unit_cost' => $ledger->unit_cost,
                'reason' => $request->reason,
                'disposal_method' => $request->disposal_method,
                'disposal_date' => $request->disposal_date ?? now(),
                'status' => 'pending', // pending, completed, cancelled
                'disposed_by' => Auth::user()->id,
                'disposed_at' => now(),
                'notes' => $request->notes,
                'verification_required' => $request->requires_verification ?? false,
            ]);

            // Ensure medication is loaded and determine item type
            $ledger->load('medication');
            $itemType = $ledger->medication->isConsumable() ? 'consumable' : 'medication';

            // Create stock movement record for audit trail
            \App\Models\StoreStockMovement::create([
                'item_type' => $itemType,
                'item_id' => $ledger->medication_id,
                'store_location_id' => $ledger->location_id ?? 1, // Default to main store if no location
                'from_location_id' => $ledger->location_id,
                'to_location_id' => null, // No destination for disposal
                'movement_type' => 'waste',
                'transaction_type' => 'disposal',
                'reference_number' => $disposalNumber,
                'reference_id' => $unfitMedication->id,
                'batch_number' => $ledger->batch_number,
                'quantity' => -$request->quantity_discarded, // Negative quantity for disposal
                'unit_cost' => $ledger->unit_cost,
                'total_cost' => $ledger->unit_cost * $request->quantity_discarded,
                'movement_date' => $request->disposal_date ?? now(),
                'balance_before' => 0.00, // Would need to calculate actual balance
                'balance_after' => 0.00,  // Would need to calculate actual balance
                'notes' => $request->notes,
                'created_by' => Auth::user()->id,
            ]);

            // Update ledger entry based on reason and quantity discarded
            $entryDeleted = false;
            if ($request->reason === 'expired') {
                // For expired items: delete the entire ledger entry
                $ledger->delete();
                $entryDeleted = true;
            } else {
                // For damaged/other reasons: reduce quantity if partial, delete if full
                if ($request->quantity_discarded >= $ledger->quantity_received) {
                    // Full quantity discarded - delete the ledger entry
                    $ledger->delete();
                    $entryDeleted = true;
                } else {
                    // Partial quantity discarded - only reduce the quantity
                    $remainingQuantity = $ledger->quantity_received - $request->quantity_discarded;
                    $ledger->update([
                        'quantity_received' => $remainingQuantity
                    ]);
                    $entryDeleted = false;
                }
            }

            // Update store location stock if the ledger has a location
            if ($ledger->location_id) {
                $locationStock = \App\Models\StoreLocationStock::where([
                    'location_id' => $ledger->location_id,
                    'medication_id' => $ledger->medication_id,
                ])->first();

                if ($locationStock && $locationStock->quantity >= $request->quantity_discarded) {
                    $locationStock->decrement('quantity', $request->quantity_discarded);
                }
            }

            // Update medication total stock
            $medication = $ledger->medication;
            if ($medication) {
                $medication->decrement('stock_quantity', $request->quantity_discarded);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medication marked as unfit and transferred to disposal tracking successfully.',
                'disposal_reference' => $disposalNumber,
                'unfit_id' => $unfitMedication->id,
                'entry_deleted' => $entryDeleted,
                'redirect_to' => $entryDeleted ? 'index' : 'stay'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Error processing unfit medication: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process unfit medication: ' . $e->getMessage()
            ], 500);
        }
    }
}
