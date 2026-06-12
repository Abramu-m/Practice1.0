<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceivedNote;
use App\Models\StoreSupplier;
use App\Models\Medication;
use App\Models\StoreLocation;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class GoodsReceivedNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display GRN (Goods Received Note) index
     */
    public function index(Request $request)
    {
            // Get filter parameters
            $search = $request->get('search');
            $status = $request->get('status', 'all'); // all, pending, verified, approved, rejected
            $supplier = $request->get('supplier');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 15);

            // Base query
            $query = GoodsReceivedNote::with(['supplier', 'items.medication', 'verifiedBy', 'approvedBy']);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('grn_number', 'like', "%{$search}%")
                      ->orWhere('invoice_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply status filter
            switch ($status) {
                case 'draft':
                    $query->where('status', 'draft');
                    break;
                case 'received':
                    $query->where('status', 'received');
                    break;
                case 'verified':
                    $query->where('status', 'verified');
                    break;
                case 'posted':
                    $query->where('status', 'posted');
                    break;
                case 'cancelled':
                    $query->where('status', 'cancelled');
                    break;
            }

            // Apply supplier filter
            if ($supplier && $supplier !== 'all') {
                $query->where('supplier_id', $supplier);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('received_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('received_at', '<=', $dateTo);
            }

            // Get paginated results
            $grns = $query->orderBy('received_at', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);

            // Get summary statistics
            $statistics = [
                'total_grns' => GoodsReceivedNote::count(),
                'draft_grns' => GoodsReceivedNote::where('status', 'draft')->count(),
                'received_grns' => GoodsReceivedNote::where('status', 'received')->count(),
                'verified_grns' => GoodsReceivedNote::where('status', 'verified')->count(),
                'posted_grns' => GoodsReceivedNote::where('status', 'posted')->count(),
                'cancelled_grns' => GoodsReceivedNote::where('status', 'cancelled')->count(),
                'total_value' => GoodsReceivedNote::where('status', 'posted')->sum('total_amount'),
                'monthly_received' => GoodsReceivedNote::where('status', 'posted')
                    ->whereBetween('grn_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
            ];

            // Get suppliers for filter dropdown
            $suppliers = StoreSupplier::where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('medications.stock.grn.index', compact(
                'grns',
                'statistics',
                'suppliers',
                'search',
                'status',
                'supplier',
                'dateFrom',
                'dateTo'
            ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
            $suppliers = StoreSupplier::where('is_active', true)->orderBy('name')->get();
            $users = \App\Models\User::orderBy('first_name')->get();
            $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
            $medications = Medication::where('is_active', true)->orderBy('generic_name')->get();
            //get the last GRN number for auto-generation
            $lastGrn = GoodsReceivedNote::latest()->first();
            $nextId = $lastGrn ? ($lastGrn->id + 1) : 1;
            $grnNumber = 'GRN-' . date('Y') .'-'. str_pad($nextId, 6, '0', STR_PAD_LEFT);

            
            return view('medications.stock.grn.grn-create', compact('suppliers', 'users', 'locations', 'medications', 'grnNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grn_number' => 'required|string|max:50|unique:goods_received_notes,grn_number',
            'grn_date' => 'required|date',
            'supplier_id' => 'required|exists:store_suppliers,id',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'delivery_date' => 'nullable|date',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,received,verified,posted,cancelled',
            'notes' => 'nullable|string|max:1000',
            'received_by' => 'nullable|exists:users,id',
            'received_at' => 'nullable|date',
            'verified_by' => 'nullable|exists:users,id',
            'verified_at' => 'nullable|date',
            'posted_by' => 'nullable|exists:users,id',
            'posted_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Auto-generate GRN number if not provided
            $grnNumber = $request->grn_number;
            if (empty($grnNumber)) {
                $grnNumber = $this->generateGrnNumber();
            }

            // Calculate net amount if not provided
            $totalAmount = $request->total_amount ?? 0;
            $discountAmount = $request->discount_amount ?? 0;
            $taxAmount = $request->tax_amount ?? 0;
            $netAmount = $request->net_amount ?? ($totalAmount - $discountAmount + $taxAmount);

            // Ensure received_by has a value (required field)
            $receivedBy = $request->received_by ?: Auth::id();

            // Create GRN
            $grn = GoodsReceivedNote::create([
                'grn_number' => $grnNumber,
                'grn_date' => $request->grn_date,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'net_amount' => $netAmount,
                'status' => $request->status,
                'notes' => $request->notes,
                'received_by' => $receivedBy,
                'received_at' => $request->received_at,
                'verified_by' => $request->verified_by,
                'verified_at' => $request->verified_at,
                'posted_by' => $request->posted_by,
                'posted_at' => $request->posted_at,
            ]);

            DB::commit();

            // If GRN is in draft status, redirect to add items wizard
            if ($grn->status === 'draft') {
                return redirect()->route('medications.stock.grn.items.create', $grn)
                    ->with('success', 'GRN created successfully. Now add items to complete the GRN.');
            }

            return redirect()->route('medications.stock.grn.show', $grn)
                ->with('success', 'GRN created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('GRN creation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create GRN: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Generate a unique GRN number
     */
    private function generateGrnNumber()
    {
        $date = now();
        $year = $date->format('Y');
        $month = $date->format('m');
        
        // Find the last GRN number for this month
        $lastGrn = GoodsReceivedNote::where('grn_number', 'LIKE', "GRN-{$year}{$month}-%")
            ->orderBy('grn_number', 'desc')
            ->first();
        
        if ($lastGrn) {
            // Extract the sequence number and increment
            $lastNumber = intval(substr($lastGrn->grn_number, -4));
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "GRN-{$year}{$month}-{$sequence}";
    }

    /**
     * Display the specified resource.
     */
    public function show(GoodsReceivedNote $grn)
    {
        $grn->load(['supplier', 'receivedBy', 'verifiedBy', 'postedBy', 'items.medication']);
        
        return view('medications.stock.grn.show', compact('grn'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'draft') {
            return redirect()->route('medications.stock.grn.show', $grn)
                ->with('error', 'Only draft GRNs can be edited.');
        }

        $suppliers = StoreSupplier::where('is_active', true)->orderBy('name')->get();
        $users = \App\Models\User::orderBy('first_name')->get();
        
        return view('medications.stock.grn.edit', compact('grn', 'suppliers', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GoodsReceivedNote $grn)
    {
        // Handle status-only updates (like Mark as Received)
        if ($request->has('status') && count($request->all()) <= 3) { // status + _token + _method
            return $this->updateStatus($request, $grn);
        }

        // Handle full GRN updates (only for draft GRNs)
        if ($grn->status !== 'draft') {
            return redirect()->route('medications.stock.grn.show', $grn)
                ->with('error', 'Only draft GRNs can be fully updated.');
        }

        $validator = Validator::make($request->all(), [
            'grn_number' => 'required|string|max:50|unique:goods_received_notes,grn_number,' . $grn->id,
            'grn_date' => 'required|date',
            'supplier_id' => 'required|exists:store_suppliers,id',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'delivery_date' => 'nullable|date',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,received,verified,posted,cancelled',
            'notes' => 'nullable|string|max:1000',
            'received_by' => 'nullable|exists:users,id',
            'received_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Calculate net amount if not provided
            $totalAmount = $request->total_amount ?? 0;
            $discountAmount = $request->discount_amount ?? 0;
            $taxAmount = $request->tax_amount ?? 0;
            $netAmount = $request->net_amount ?? ($totalAmount - $discountAmount + $taxAmount);

            // Ensure received_by has a value (required field)
            $receivedBy = $request->received_by ?: $grn->received_by ?: Auth::id();

            // Update GRN
            $grn->update([
                'grn_number' => $request->grn_number,
                'grn_date' => $request->grn_date,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'net_amount' => $netAmount,
                'status' => $request->status,
                'notes' => $request->notes,
                'received_by' => $receivedBy,
                'received_at' => $request->received_at,
            ]);

            DB::commit();

            return redirect()->route('medications.stock.grn.show', $grn)
                ->with('success', 'GRN updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('GRN update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update GRN: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update only the status of a GRN
     */
    private function updateStatus(Request $request, GoodsReceivedNote $grn)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,received,verified,posted,cancelled',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $newStatus = $request->status;
        $oldStatus = $grn->status;

        // Validate status transitions
        $allowedTransitions = [
            'draft' => ['received', 'cancelled'],
            'received' => ['verified', 'cancelled'],
            'verified' => ['posted', 'cancelled'],
            'posted' => [], // Final status
            'cancelled' => [], // Final status
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            return back()->withErrors(['status' => "Cannot change status from {$oldStatus} to {$newStatus}."]);
        }

        DB::beginTransaction();

        try {
            $updateData = ['status' => $newStatus];

            // Set timestamps and handle business logic based on status
            if ($newStatus === 'received' && !$grn->received_at) {
                $updateData['received_at'] = now();
                $updateData['received_by'] = Auth::id();
            } elseif ($newStatus === 'verified' && !$grn->verified_at) {
                $updateData['verified_at'] = now();
                $updateData['verified_by'] = Auth::id();
            } elseif ($newStatus === 'posted' && !$grn->posted_at) {
                // Post to inventory - this is the main inventory integration
                $updateData['posted_at'] = now();
                $updateData['posted_by'] = Auth::id();
                
                // Process each GRN item and post to inventory
                $this->postGrnToInventory($grn);
            }

            $grn->update($updateData);

            DB::commit();

            $message = $newStatus === 'posted' 
                ? "GRN posted to inventory successfully. All items are now available in stock."
                : "GRN status updated to {$newStatus}.";

            return redirect()->route('medications.stock.grn.show', $grn)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('GRN status update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    /**
     * Post GRN items to inventory system
     */
    private function postGrnToInventory(GoodsReceivedNote $grn)
    {
        foreach ($grn->items as $grnItem) {
            // Create medication ledger entry for inventory tracking
            // Note: received_quantity is already in dispensing units (e.g., tablets)
            // unit_cost is already per dispensing unit (e.g., per tablet)
            \App\Models\MedicationLedger::create([
                'medication_id' => $grnItem->item_id,
                'grn_id' => $grn->id,
                'grn_item_id' => $grnItem->id,
                'batch_number' => $grnItem->batch_number,
                'manufacture_date' => $grnItem->manufacture_date,
                'expiry_date' => $grnItem->expiry_date,
                'unit_cost' => $grnItem->unit_cost, // Per dispensing unit (e.g., $0.50 per tablet)
                'quantity_received' => $grnItem->received_quantity, // Total dispensing units (e.g., 1000 tablets)
                'location_id' => 1, // Default main store location - you may want to make this configurable
                'status' => 'active',
                'notes' => $grnItem->notes . "\nStore units: {$grnItem->store_quantity} × {$grnItem->store_unit_cost} (conversion factor: {$grnItem->conversion_factor})"
            ]);

            // Update medication total stock quantity with dispensing units
            $medication = \App\Models\Medication::find($grnItem->item_id);
            if ($medication) {
                // Increment by dispensing quantity (e.g., add 1000 tablets to stock)
                $medication->increment('stock_quantity', $grnItem->received_quantity);
            }

            // Create stock movement record for audit trail
            \App\Models\StoreStockMovement::create([
                'item_type' => 'medication',
                'item_id' => $grnItem->item_id,
                'store_location_id' => 1, // Main store location
                'movement_type' => 'in',
                'transaction_type' => 'purchase',
                'reference_number' => $grn->grn_number,
                'reference_id' => $grn->id,
                'batch_number' => $grnItem->batch_number,
                'from_location_id' => null, // From supplier
                'to_location_id' => 1, // Main store location
                'quantity' => $grnItem->received_quantity, // Dispensing units (e.g., 1000 tablets)
                'unit_cost' => $grnItem->unit_cost, // Per dispensing unit (e.g., $0.50 per tablet)
                'total_cost' => $grnItem->received_quantity * $grnItem->unit_cost,
                'movement_date' => now(),
                'balance_before' => 0, // You may want to calculate actual balance
                'balance_after' => 0, // You may want to calculate actual balance
                'notes' => "Inventory posting from GRN: {$grn->grn_number}",
                'created_by' => Auth::id()
            ]);
        }

        Log::info("GRN {$grn->grn_number} successfully posted to inventory with " . $grn->items->count() . " items");

        // Trigger a reconciliation run after posting so any discrepancies are caught immediately
        try {
            app(ReconciliationService::class)->checkStockIntegrity();
        } catch (\Exception $e) {
            Log::warning("Post-GRN reconciliation check failed: " . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'pending') {
            return back()->withErrors(['delete' => 'Only pending GRNs can be deleted.']);
        }

        DB::beginTransaction();

        try {
            // Delete GRN (stock batches are no longer used)
            $grn->delete();

            DB::commit();

            return redirect()->route('store.grns.index')
                ->with('success', 'GRN deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete GRN: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve GRN (transition from verified to posted status)
     */
    public function approve(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'verified') {
            return back()->withErrors(['error' => 'Only verified GRNs can be approved (posted).']);
        }

        try {
            DB::beginTransaction();

            // Update status to posted and set posted timestamp/user
            $grn->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id()
            ]);

            // Post to inventory - this is the main inventory integration
            $this->postGrnToInventory($grn);

            DB::commit();

            return redirect()->route('store.grns.show', $grn)
                ->with('success', 'GRN posted to inventory successfully. All items are now available in stock.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error posting GRN to inventory: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error posting GRN to inventory: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject GRN
     */
    public function reject(Request $request, GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending GRNs can be rejected.']);
        }

        $request->validate([
            'reason' => 'required|string'
        ]);

        $grn->reject($request->reason);

        return redirect()->route('store.grns.show', $grn)
            ->with('success', 'GRN rejected successfully.');
    }

    /**
     * Mark GRN as paid
     */
    public function markAsPaid(GoodsReceivedNote $grn)
    {
        if ($grn->status === 'paid') {
            return back()->withErrors(['error' => 'GRN is already marked as paid.']);
        }

        $grn->markAsPaid();

        return redirect()->route('store.grns.show', $grn)
            ->with('success', 'GRN marked as paid successfully.');
    }

    /**
     * Get pending GRNs
     */
    public function getPendingGrns()
    {
        $pendingGrns = GoodsReceivedNote::pending()
            ->with(['supplier', 'receivedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($pendingGrns);
    }

    /**
     * Get GRN items
     */
    public function getItems(GoodsReceivedNote $grn)
    {
        $items = $grn->items()
            ->with(['medication', 'storeUnit', 'dispensingUnit'])
            ->get();
        
        return response()->json($items);
    }

    /**
     * Show GRN items management page
     */
    public function itemsIndex(GoodsReceivedNote $grn)
    {
        $grn->load(['items.medication', 'items.storeUnit', 'items.dispensingUnit', 'supplier']);
        
        return view('medications.stock.grn.grn_items.index', compact('grn'));
    }

    /**
     * Show GRN add items wizard page
     */
    public function itemsCreate(GoodsReceivedNote $grn)
    {
        $grn->load(['items.medication', 'items.storeUnit', 'items.dispensingUnit', 'supplier']);
        
        return view('medications.stock.grn.grn_items.create', compact('grn'));
    }

    /**
     * Add item to GRN
     */
    public function addItem(Request $request, GoodsReceivedNote $grn)
    {
        // Validate that GRN can still be modified
        if (!in_array($grn->status, ['draft', 'received'])) {
            return back()->withErrors(['error' => 'Cannot add items to this GRN. Status: ' . $grn->status]);
        }

        $request->validate([
            'item_type' => 'required|in:medication,consumable',
            'item_id' => 'required|integer|min:1',
            'store_unit_id' => 'required|exists:store_units,id',
            'dispensing_unit_id' => 'required|exists:store_units,id',
            'conversion_factor' => 'required|numeric|min:0.0001',
            'batch_number' => 'required|string|max:255',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'received_quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Validate item exists
        if ($request->item_type === 'medication') {
            $item = Medication::findOrFail($request->item_id);
        } else {
            // For now, we'll handle consumables later
            return back()->withErrors(['error' => 'Consumables not yet implemented']);
        }

        // Calculate amounts with unit conversion
        $store_quantity = $request->received_quantity; // Quantity in store units (e.g., 10 boxes)
        $conversion_factor = $request->conversion_factor; // e.g., 100 tablets per box
        $store_unit_cost = $request->unit_cost; // Cost per store unit (e.g., $50 per box)
        
        // Convert to dispensing units
        $dispensing_quantity = $store_quantity * $conversion_factor; // e.g., 10 * 100 = 1000 tablets
        $dispensing_unit_cost = $store_unit_cost / $conversion_factor; // e.g., $50 / 100 = $0.50 per tablet
        
        // Calculate totals based on store units (original entry)
        $total_cost = $store_quantity * $store_unit_cost;
        
        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = ($total_cost * $discount_percentage) / 100;
        
        $subtotal = $total_cost - $discount_amount;
        
        $tax_percentage = $request->tax_percentage ?? 0;
        $tax_amount = ($subtotal * $tax_percentage) / 100;
        
        $net_amount = $subtotal + $tax_amount;

        try {
            DB::beginTransaction();

            // Create GRN item
            $grnItem = $grn->items()->create([
                'item_id' => $request->item_id,
                'store_unit_id' => $request->store_unit_id,
                'dispensing_unit_id' => $request->dispensing_unit_id,
                'conversion_factor' => $conversion_factor,
                'batch_number' => $request->batch_number,
                'manufacture_date' => $request->manufacture_date,
                'expiry_date' => $request->expiry_date,
                'received_quantity' => $dispensing_quantity, // Store as dispensing units (total tablets)
                'unit_cost' => $dispensing_unit_cost, // Store as dispensing unit cost (per tablet)
                'total_cost' => $total_cost,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'tax_percentage' => $tax_percentage,
                'tax_amount' => $tax_amount,
                'net_amount' => $net_amount,
                'notes' => $request->notes,
                // Store the original store unit values for reference
                'store_quantity' => $store_quantity,
                'store_unit_cost' => $store_unit_cost,
            ]);

            // Update GRN total amount
            $this->updateGrnTotals($grn);

            DB::commit();

            return back()->with('success', 'Item added to GRN successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding item to GRN: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error adding item to GRN: ' . $e->getMessage()]);
        }
    }

    /**
     * Get item details for editing
     */
    public function getItem(GoodsReceivedNote $grn, $item)
    {
        $grnItem = $grn->items()->with(['medication'])->findOrFail($item);
        
        return response()->json($grnItem);
    }

    /**
     * Update GRN item
     */
    public function updateItem(Request $request, GoodsReceivedNote $grn, $item)
    {
        // Validate that GRN can still be modified
        if (!in_array($grn->status, ['draft', 'received'])) {
            return back()->withErrors(['error' => 'Cannot modify items in this GRN. Status: ' . $grn->status]);
        }

        $grnItem = $grn->items()->findOrFail($item);

        $request->validate([
            'store_unit_id' => 'required|exists:store_units,id',
            'dispensing_unit_id' => 'required|exists:store_units,id',
            'conversion_factor' => 'required|numeric|min:0.0001',
            'batch_number' => 'required|string|max:255',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'received_quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Calculate amounts with unit conversion
        $store_quantity = $request->received_quantity; // Quantity in store units (e.g., 10 boxes)
        $conversion_factor = $request->conversion_factor; // e.g., 100 tablets per box
        $store_unit_cost = $request->unit_cost; // Cost per store unit (e.g., $50 per box)
        
        // Convert to dispensing units
        $dispensing_quantity = $store_quantity * $conversion_factor; // e.g., 10 * 100 = 1000 tablets
        $dispensing_unit_cost = $store_unit_cost / $conversion_factor; // e.g., $50 / 100 = $0.50 per tablet
        
        // Calculate totals based on store units (original entry)
        $total_cost = $store_quantity * $store_unit_cost;
        
        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = ($total_cost * $discount_percentage) / 100;
        
        $subtotal = $total_cost - $discount_amount;
        
        $tax_percentage = $request->tax_percentage ?? 0;
        $tax_amount = ($subtotal * $tax_percentage) / 100;
        
        $net_amount = $subtotal + $tax_amount;

        try {
            DB::beginTransaction();

            // Update item
            $grnItem->update([
                'store_unit_id' => $request->store_unit_id,
                'dispensing_unit_id' => $request->dispensing_unit_id,
                'conversion_factor' => $conversion_factor,
                'batch_number' => $request->batch_number,
                'manufacture_date' => $request->manufacture_date,
                'expiry_date' => $request->expiry_date,
                'received_quantity' => $dispensing_quantity, // Store as dispensing units (total tablets)
                'unit_cost' => $dispensing_unit_cost, // Store as dispensing unit cost (per tablet)
                'total_cost' => $total_cost,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'tax_percentage' => $tax_percentage,
                'tax_amount' => $tax_amount,
                'net_amount' => $net_amount,
                'notes' => $request->notes,
                // Store the original store unit values for reference
                'store_quantity' => $store_quantity,
                'store_unit_cost' => $store_unit_cost,
            ]);

            // Update GRN total amount
            $this->updateGrnTotals($grn);

            DB::commit();

            return back()->with('success', 'Item updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating GRN item: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error updating item: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove item from GRN
     */
    public function removeItem(GoodsReceivedNote $grn, $item)
    {
        // Validate that GRN can still be modified
        if (!in_array($grn->status, ['draft', 'received'])) {
            return back()->withErrors(['error' => 'Cannot remove items from this GRN. Status: ' . $grn->status]);
        }

        $grnItem = $grn->items()->findOrFail($item);

        try {
            DB::beginTransaction();

            $grnItem->delete();

            // Update GRN total amount
            $this->updateGrnTotals($grn);

            DB::commit();

            return back()->with('success', 'Item removed from GRN successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing GRN item: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error removing item: ' . $e->getMessage()]);
        }
    }

    /**
     * Get medications for item selection
     */
    public function getMedications()
    {
        $medications = Medication::select('id', 'generic_name', 'brand_name', 'strength', 'dispensing_unit_id')
            ->where('is_active', true)
            ->orderBy('generic_name')
            ->get();

        return response()->json($medications);
    }

    /**
     * Get items by type for selection
     */
    public function getItemsByType($type)
    {
        if ($type === 'medication') {
            return $this->getMedications();
        } elseif ($type === 'consumable') {
            // For now, return empty array until consumables are implemented
            return response()->json([]);
        }

        return response()->json([]);
    }

    /**
     * Get store units for selection
     */
    public function getStoreUnits()
    {
        $storeUnits = \App\Models\StoreUnit::getStoreUnits();
        return response()->json($storeUnits);
    }

    /**
     * Get dispensing units for selection
     */
    public function getDispensingUnits()
    {
        $dispensingUnits = \App\Models\StoreUnit::getDispensingUnits();
        return response()->json($dispensingUnits);
    }

    /**
     * Update GRN totals based on items
     */
    private function updateGrnTotals(GoodsReceivedNote $grn)
    {
        $items = $grn->items;
        
        $total_amount = $items->sum('total_cost');
        $discount_amount = $items->sum('discount_amount');
        $tax_amount = $items->sum('tax_amount');
        $net_amount = $items->sum('net_amount');

        $grn->update([
            'total_amount' => $total_amount,
            'discount_amount' => $discount_amount,
            'tax_amount' => $tax_amount,
            'net_amount' => $net_amount,
        ]);
    }
}
