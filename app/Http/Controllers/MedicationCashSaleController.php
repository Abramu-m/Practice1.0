<?php

namespace App\Http\Controllers;

use App\Models\MedicationCashSale;
use App\Models\MedicationCashSaleItem;
use App\Models\Medication;
use App\Models\PatientCategory;
use App\Models\MedicationPricing;
use App\Models\StoreLocationStock;
use App\Models\StoreLocation;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicationCashSaleController extends Controller
{
    protected $financialTransactionService;

    public function __construct(FinancialTransactionService $financialTransactionService)
    {
        $this->financialTransactionService = $financialTransactionService;
    }

    /**
     * Display cash sale list
     */
    public function index(Request $request)
    {
        $query = MedicationCashSale::with(['patientCategory', 'creator', 'items.medication'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                // Special filter for completed sales (paid and dispensed)
                $query->where('is_paid', true)->whereNotNull('dispensed_at');
            } elseif ($request->status === 'ready_to_dispense') {
                // Special filter for paid but not dispensed
                $query->where('is_paid', true)->whereNull('dispensed_at');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by sale type
        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }

        // Search by sale number
        if ($request->filled('search')) {
            $query->where('sale_number', 'like', '%' . $request->search . '%');
        }

        $cashSales = $query->paginate(15);

        // Get stock information for each sale (excluding cancelled items)
        $stockInfo = [];
        foreach ($cashSales as $sale) {
            $hasStockIssues = false;
            $stockDetails = [];
            
            foreach ($sale->items as $item) {
                $availableStock = $this->getAvailableStock($item->medication_id);
                $stockDetails[$item->id] = [
                    'available' => $availableStock,
                    'required' => $item->quantity,
                    'sufficient' => $availableStock >= $item->quantity
                ];
                
                // Only consider stock issues for items that can still be dispensed
                if ($item->canBeDispensed() && $availableStock < $item->quantity) {
                    $hasStockIssues = true;
                }
            }
            
            $stockInfo[$sale->id] = [
                'has_issues' => $hasStockIssues,
                'details' => $stockDetails
            ];
        }

        // Statistics
        $stats = [
            'total_sales' => MedicationCashSale::count(),
            'pending_sales' => MedicationCashSale::pending()->count(),
            'dispensed_sales' => MedicationCashSale::dispensed()->count(),
            'unpaid_sales' => MedicationCashSale::unpaid()->count(),
            'paid_ready_to_dispense' => MedicationCashSale::paid()->whereNull('dispensed_at')->count(),
            'completed_sales' => MedicationCashSale::completed()->count(),
            'daily_revenue' => MedicationCashSale::paid()
                ->whereDate('paid_at', today())
                ->sum('final_amount'),
        ];

        return view('medication_cash_sales.index', compact('cashSales', 'stats', 'stockInfo'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $cashCategories = PatientCategory::where('type', 'cash')
            ->where('is_active', true)
            ->get();

        $medications = Medication::with(['pricing' => function($query) {
                $query->active()->current();
            }])
            ->where('is_active', true)
            ->orderBy('generic_name')
            ->get();

        $medicationFrequencies = \App\Models\MedicationFrequency::where('is_active', true)
            ->orderBy('frequency_name')
            ->get();

        $administrationRoutes = \App\Models\AdministrationRoute::where('is_active', true)
            ->orderBy('route_name')
            ->get();

        return view('medication_cash_sales.create', compact('cashCategories', 'medications', 'medicationFrequencies', 'administrationRoutes'));
    }

    /**
     * Store new cash sale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_type' => 'required|in:otc,external_prescription',
            'external_prescription_details' => 'required_if:sale_type,external_prescription|nullable|string',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'medications' => 'required|array|min:1',
            'medications.*.medication_id' => 'required|exists:medications,id',
            'medications.*.quantity' => 'required|numeric|min:0.1|max:999999.99',
            'medications.*.dosage' => 'nullable|string',
            'medications.*.medication_frequency_id' => 'nullable|exists:medication_frequencies,id',
            'medications.*.administration_route_id' => 'nullable|exists:administration_routes,id',
            'medications.*.duration_days' => 'nullable|integer|min:1',
            'medications.*.instructions' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create cash sale
            $cashSale = MedicationCashSale::create([
                'sale_number' => MedicationCashSale::generateSaleNumber(),
                'sale_type' => $validated['sale_type'],
                'external_prescription_details' => $validated['external_prescription_details'] ?? null,
                'patient_category_id' => $validated['patient_category_id'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'created_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Add items
            foreach ($validated['medications'] as $medicationData) {
                $unitPrice = MedicationCashSaleItem::getPriceForMedication(
                    $medicationData['medication_id'], 
                    $validated['patient_category_id']
                );

                $item = MedicationCashSaleItem::create([
                    'cash_sale_id' => $cashSale->id,
                    'medication_id' => $medicationData['medication_id'],
                    'quantity' => $medicationData['quantity'],
                    'dosage' => $medicationData['dosage'] ?? null,
                    'medication_frequency_id' => $medicationData['medication_frequency_id'] ?? null,
                    'administration_route_id' => $medicationData['administration_route_id'] ?? null,
                    'duration_days' => $medicationData['duration_days'] ?? null,
                    'instructions' => $medicationData['instructions'] ?? null,
                    'dispensing_type' => 'batch', // Default to batch, can be changed during dispensing
                    'unit_price' => $unitPrice,
                    'total_price' => $medicationData['quantity'] * $unitPrice,
                ]);
            }

            // Calculate totals
            $cashSale->calculateTotals();

            DB::commit();

            return redirect()->route('medication-cash-sales.index')
                ->with('success', 'Cash sale created successfully. Sale Number: ' . $cashSale->sale_number);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating cash sale: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error creating cash sale: ' . $e->getMessage());
        }
    }

    /**
     * Show cash sale details
     */
    public function show(MedicationCashSale $medicationCashSale)
    {
        $medicationCashSale->load([
            'patientCategory',
            'items.medication',
            'items.medicationFrequency',
            'items.administrationRoute',
            'items.dispenser',
            'creator',
            'dispenser',
            'cashier' // This is the paidBy relationship
        ]);

        // Get stock information for each medication (excluding cancelled items)
        $stockInfo = [];
        $hasStockIssues = false;
        foreach ($medicationCashSale->items as $item) {
            $availableStock = $this->getAvailableStock($item->medication_id);
            $stockInfo[$item->id] = [
                'available' => $availableStock,
                'required' => $item->quantity,
                'sufficient' => $availableStock >= $item->quantity
            ];
            
            // Only consider stock issues for items that are not cancelled and can still be dispensed
            if ($item->canBeDispensed() && $availableStock < $item->quantity) {
                $hasStockIssues = true;
            }
        }

        return view('medication_cash_sales.show', compact('medicationCashSale', 'stockInfo', 'hasStockIssues'));
    }

    /**
     * Dispense medications (Pharmacist function)
     * NOTE: Can only dispense if sale is PAID
     */
    public function dispense(MedicationCashSale $medicationCashSale)
    {
        // Enforce paid-first policy
        if (!$medicationCashSale->is_paid) {
            return back()->with('error', 'Medications can only be dispensed after payment has been processed. Please ensure payment is completed first.');
        }

        if (!$medicationCashSale->canBeDispensed()) {
            return back()->with('error', 'This sale cannot be dispensed in its current state.');
        }

        try {
            DB::beginTransaction();

            foreach ($medicationCashSale->items as $item) {
                if (!$item->canBeDispensed()) {
                    continue;
                }

                // Use the same dispensing logic as prescriptions
                $this->dispenseMedicationItem($item);
            }

            // Mark sale as dispensed
            $medicationCashSale->markAsDispensed(Auth::id());

            DB::commit();

            return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                ->with('success', 'Medications dispensed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error dispensing cash sale medications: ' . $e->getMessage());
            return back()->with('error', 'Error dispensing medications: ' . $e->getMessage());
        }
    }

    /**
     * Dispense individual medication item
     */
    public function dispenseItem(Request $request, MedicationCashSaleItem $item)
    {
        // Validate that parent sale is paid
        if (!$item->cashSale->is_paid) {
            return back()->with('error', 'Items can only be dispensed after payment has been processed.');
        }

        $validated = $request->validate([
            'quantity_to_dispense' => 'required|numeric|min:0.1|max:' . $item->remaining_quantity,
        ]);

        try {
            DB::beginTransaction();

            // Dispense the specified quantity
            $this->dispenseMedicationItem($item, $validated['quantity_to_dispense']);

            // Check and update parent sale completion status
            $this->checkAndUpdateParentSaleStatus($item->cashSale);

            DB::commit();

            return back()->with('success', 'Item dispensed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error dispensing individual item: ' . $e->getMessage());
            return back()->with('error', 'Error dispensing item: ' . $e->getMessage());
        }
    }

    /**
     * Process payment (Cashier function)
     */
    public function processPayment(Request $request, MedicationCashSale $medicationCashSale)
    {
        // Debug logging
        Log::info('Payment processing started', [
            'sale_id' => $medicationCashSale->id,
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        if (!$medicationCashSale->canBePaid()) {
            Log::warning('Payment failed - sale cannot be paid', [
                'sale_id' => $medicationCashSale->id,
                'status' => $medicationCashSale->status
            ]);
            return back()->with('error', 'This sale cannot be paid in its current state.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_money',
            'amount_paid' => 'required|numeric|min:0',
            'print_receipt' => 'nullable|boolean',
        ]);

        if ($validated['amount_paid'] < $medicationCashSale->final_amount) {
            return back()->withInput()
                ->with('error', 'Payment amount is insufficient.');
        }

        try {
            DB::beginTransaction();

            // Mark sale as paid
            $medicationCashSale->markAsPaid(
                Auth::id(),
                $validated['payment_method'],
                $validated['amount_paid']
            );

            // Create financial transaction
            $this->financialTransactionService->createFromCashSalePayment($medicationCashSale, $validated['payment_method']);

            DB::commit();

            $message = 'Payment processed successfully. Transaction completed.';
            
            // Calculate change if applicable
            $change = $validated['amount_paid'] - $medicationCashSale->final_amount;
            if ($change > 0) {
                $message .= ' Change due: TSh ' . number_format($change, 2);
            }

            // Handle receipt printing if requested
            $printReceipt = $validated['print_receipt'] ?? false;
            if ($printReceipt) {
                // Add a flag to trigger receipt printing (could be handled via JavaScript)
                session()->flash('print_receipt', true);
                session()->flash('receipt_data', [
                    'sale_number' => $medicationCashSale->sale_number,
                    'total' => $medicationCashSale->final_amount,
                    'paid' => $validated['amount_paid'],
                    'change' => $change,
                    'payment_method' => $validated['payment_method']
                ]);
            }

            return redirect()->route('medication-cash-sales.index', $medicationCashSale)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing cash sale payment: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Get medication pricing for AJAX
     * Handles both search queries and individual medication pricing
     */
    public function getMedicationPricing(Request $request)
    {
        $medicationId = $request->medication_id;
        $categoryId = $request->category_id;
        $searchQuery = $request->search;

        // Handle search query (for medication suggestions)
        if ($searchQuery && !$medicationId) {
            $medications = Medication::with(['formulation'])
                ->where('is_active', true)
                ->where(function($query) use ($searchQuery) {
                    $query->where('generic_name', 'like', '%' . $searchQuery . '%')
                          ->orWhere('brand_name', 'like', '%' . $searchQuery . '%')
                          ->orWhere('strength', 'like', '%' . $searchQuery . '%');
                })
                ->orderBy('generic_name')
                ->limit(10)
                ->get();

            $medicationsWithPricing = $medications->map(function($medication) use ($categoryId) {
                // Get pricing for this medication
                $pricing = MedicationPricing::where('medication_id', $medication->id)
                    ->where('patient_category_id', $categoryId)
                    ->active()
                    ->current()
                    ->first();

                // Get available stock
                $availableStock = $this->getAvailableStock($medication->id);

                return [
                    'id' => $medication->id,
                    'generic_name' => $medication->generic_name,
                    'brand_name' => $medication->brand_name,
                    'strength' => $medication->strength,
                    'formulation' => $medication->formulation,
                    'unit_price' => $pricing ? $pricing->selling_price : 0,
                    'pricing_exists' => !!$pricing,
                    'available_stock' => $availableStock,
                    'in_stock' => $availableStock > 0,
                ];
            });

            return response()->json([
                'medications' => $medicationsWithPricing,
                'search_query' => $searchQuery
            ]);
        }

        // Handle individual medication pricing (for specific medication)
        if ($medicationId && $categoryId) {
            $pricing = MedicationPricing::where('medication_id', $medicationId)
                ->where('patient_category_id', $categoryId)
                ->active()
                ->current()
                ->first();

            $medication = Medication::find($medicationId);

            // Check available stock
            $availableStock = $this->getAvailableStock($medicationId);

            return response()->json([
                'medication' => $medication,
                'unit_price' => $pricing ? $pricing->selling_price : 0,
                'pricing_exists' => !!$pricing,
                'available_stock' => $availableStock,
                'in_stock' => $availableStock > 0,
            ]);
        }

        // Invalid request
        return response()->json([
            'error' => 'Invalid request. Provide either search query or medication_id with category_id.'
        ], 400);
    }

    /**
     * Cancel individual medication item
     */
    public function cancelItem(Request $request, MedicationCashSaleItem $item)
    {
        // Validate the cancellation reason
        $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        // Validate that parent sale allows cancellation
        if ($item->cashSale->status === 'paid' && $item->status === 'dispensed') {
            return back()->with('error', 'Cannot cancel a dispensed item.');
        }

        // Prevent cancellation of already cancelled items
        if ($item->status === 'cancelled') {
            return back()->with('error', 'This item is already cancelled.');
        }

        try {
            DB::beginTransaction();

            // Store cancellation metadata - using notes column for cancellation reason
            $cancellationData = [
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now()->toDateTimeString(),
                'reason' => $request->cancel_reason,
                'original_status' => $item->status
            ];

            // Mark item as cancelled with metadata
            $item->update([
                'status' => MedicationCashSaleItem::STATUS_CANCELLED,
                'notes' => json_encode($cancellationData), // Store cancellation info in notes
            ]);

            // Recalculate sale totals
            $item->cashSale->calculateTotals();

            // Check and update parent sale completion status
            $this->checkAndUpdateParentSaleStatus($item->cashSale);

            DB::commit();

            return back()->with('success', 'Item cancelled successfully. Reason recorded.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling individual item: ' . $e->getMessage());
            return back()->with('error', 'Error cancelling item: ' . $e->getMessage());
        }
    }

    /**
     * Cancel cash sale - Smart cancellation logic:
     * - For unpaid/unissued sales: Delete immediately
     * - For paid sales: Require admin permissions and cancellation reason
     */
    public function cancel(Request $request, MedicationCashSale $medicationCashSale)
    {
        // For paid sales - require admin permissions and reason
        if ($medicationCashSale->is_paid) {
            // Check if user is admin
            $user = Auth::user();
            $isAdmin = method_exists($user, 'role') && in_array($user->role, ['admin', 'super_admin']);
            
            if (!$isAdmin) {
                return back()->with('error', 'Only administrators can cancel paid sales.');
            }

            // Validate cancellation reason for paid sales
            $request->validate([
                'cancel_reason' => 'required|string|min:15',
            ]);

            try {
                DB::beginTransaction();

                // Cancel all associated items
                $medicationCashSale->items()->update([
                    'status' => MedicationCashSaleItem::STATUS_CANCELLED,
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                ]);

                // Cancel the paid sale with reason (but keep is_paid = true for audit)
                $medicationCashSale->update([
                    'status' => MedicationCashSale::STATUS_CANCELLED,
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                    'cancellation_reason' => $request->cancel_reason,
                ]);

                // Log the paid sale cancellation
                Log::info("Paid Medication Cash Sale Cancelled", [
                    'sale_id' => $medicationCashSale->id,
                    'sale_number' => $medicationCashSale->sale_number,
                    'total_amount' => $medicationCashSale->final_amount,
                    'cancelled_by' => Auth::id(),
                    'cancel_reason' => $request->cancel_reason,
                    'cancelled_at' => now()
                ]);

                DB::commit();

                return back()->with('success', 'Paid sale cancelled successfully. Reason recorded.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error cancelling paid sale: ' . $e->getMessage());
                return back()->with('error', 'Error cancelling sale: ' . $e->getMessage());
            }
        }

        // For unpaid/unissued sales - delete immediately
        try {
            DB::beginTransaction();

            // Store sale info for logging before deletion
            $saleNumber = $medicationCashSale->sale_number;
            $saleId = $medicationCashSale->id;

            // Log the deletion
            Log::info("Unpaid Medication Cash Sale Deleted via Cancel", [
                'sale_id' => $saleId,
                'sale_number' => $saleNumber,
                'status' => $medicationCashSale->status,
                'deleted_by' => Auth::id(),
                'deleted_at' => now()
            ]);

            // Delete the sale (items will be cascade deleted)
            $medicationCashSale->delete();

            DB::commit();

            return back()->with('success', "Sale {$saleNumber} has been deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting unpaid sale: ' . $e->getMessage());
            return back()->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }

    /**
     * Dispense individual medication item (using existing PharmacistController logic)
     */
    private function dispenseMedicationItem(MedicationCashSaleItem $item, $quantityToDispense = null)
    {
        $medicationId = $item->medication_id;
        $quantityToDispense = $quantityToDispense ?? $item->quantity;

        // Get pharmacy location (same logic as PharmacistController)
        $pharmacyLocation = \App\Models\StoreLocation::where('type', 'dispensing')
            ->orWhere('name', 'LIKE', '%Main Pharmacy%')
            ->where('is_active', true)
            ->first();

        if (!$pharmacyLocation) {
            $pharmacyLocation = \App\Models\StoreLocation::where('is_active', true)->first();
        }

        if (!$pharmacyLocation) {
            throw new \Exception('No active pharmacy location found');
        }

        // Find available stock (FIFO)
        $stockItems = StoreLocationStock::where('location_id', $pharmacyLocation->id)
            ->where('medication_id', $medicationId)
            ->where('status', StoreLocationStock::STATUS_ACTIVE)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        if ($stockItems->sum('quantity') < $quantityToDispense) {
            throw new \Exception("Insufficient stock for medication: {$item->medication->generic_name}");
        }

        $remainingToDispense = $quantityToDispense;
        $batchesUsed = [];

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

            // Record batch used
            $batchesUsed[] = [
                'batch_number' => $stockItem->batch_number,
                'quantity' => $quantityToTake,
                'expiry_date' => $stockItem->expiry_date,
            ];

            $remainingToDispense -= $quantityToTake;
        }

        // Mark item as dispensed (with partial or full quantity)
        $currentDispensed = $item->quantity_dispensed + $quantityToDispense;
        $item->markAsDispensed($batchesUsed, $currentDispensed, Auth::id());
        
        // Note: Status is now automatically set in markAsDispensed method
    }

    /**
     * Get available stock for medication (public method for view access)
     */
    public function getAvailableStock($medicationId)
    {
        $pharmacyLocation = \App\Models\StoreLocation::where('type', 'dispensing')
            ->orWhere('name', 'LIKE', '%Main Pharmacy%')
            ->where('is_active', true)
            ->first();

        if (!$pharmacyLocation) {
            $pharmacyLocation = \App\Models\StoreLocation::where('is_active', true)->first();
        }

        if (!$pharmacyLocation) {
            return 0;
        }

        return StoreLocationStock::where('location_id', $pharmacyLocation->id)
            ->where('medication_id', $medicationId)
            ->where('status', StoreLocationStock::STATUS_ACTIVE)
            ->where('quantity', '>', 0)
            ->sum('quantity');
    }

    /**
     * Delete an unpaid medication cash sale
     */
    public function destroy(MedicationCashSale $medicationCashSale)
    {
        try {
            // Prevent cashiers and receptionists from deleting sales
            if (Auth::user()->role === 'cashier' || Auth::user()->role === 'receptionist') {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Unauthorized action. Cashiers and receptionists cannot delete sales.');
            }

            // Only allow deletion of unpaid sales 
            if ($medicationCashSale->is_paid) {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Cannot delete a paid sale. Use cancel option instead.');
            }

            // Prevent deletion of cancelled sales (maintaining audit trail)
            if ($medicationCashSale->status == 'cancelled') {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Cannot delete a cancelled sale. Cancelled sales are kept for audit purposes.');
            }

            // Prevent deletion if any medications have been dispensed
            if ($medicationCashSale->dispensed_at) {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Cannot delete a sale that has been partially or fully dispensed. Use cancel option instead.');
            }

            // Validate required reason
            $request = request();
            $request->validate([
                'delete_reason' => 'required|string|min:10'
            ]);

            // Log the deletion reason
            $deleteReason = $request->delete_reason;
            
            // Store deletion information before deleting
            Log::info("Medication Cash Sale Deleted", [
                'sale_id' => $medicationCashSale->id,
                'sale_number' => $medicationCashSale->sale_number,
                'total_amount' => $medicationCashSale->final_amount,
                'deleted_by' => Auth::id(),
                'delete_reason' => $deleteReason,
                'deleted_at' => now()
            ]);

            // Delete the sale (items will be cascade deleted)
            $saleNumber = $medicationCashSale->sale_number;
            $medicationCashSale->delete();

            return redirect()->route('medication-cash-sales.index')
                ->with('success', "Sale {$saleNumber} has been successfully deleted.");

        } catch (\Exception $e) {
            Log::error('Error deleting medication cash sale: ' . $e->getMessage());
            return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                ->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a paid medication cash sale
     */
    public function cancelPaid(MedicationCashSale $medicationCashSale)
    {
        try {
            // Prevent cashiers and receptionists from cancelling paid sales
            $user = Auth::user();
            $isAdmin = method_exists($user, 'role') && in_array($user->role, ['admin', 'super_admin']);
            
            if (!$isAdmin) {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Unauthorized action. Only Admins can cancel paid sales.');
            }

            // Only allow cancellation of paid sales
            if (!$medicationCashSale->is_paid) {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Only paid sales can be cancelled using this method.');
            }

            if ($medicationCashSale->status == 'cancelled') {
                return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                    ->with('error', 'Sale is already cancelled.');
            }

            // Validate required cancellation reason
            $request = request();
            $request->validate([
                'cancel_reason' => 'required|string|min:15',
                'refund_required' => 'required|in:yes,no'
            ]);

            $cancelReason = $request->cancel_reason;
            $refundRequired = $request->refund_required == 'yes';

            // Update sale status
            $medicationCashSale->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $cancelReason,
                'refund_required' => $refundRequired,
            ]);

            // Cancel all items (return stock to available)
            foreach ($medicationCashSale->items as $item) {
                if ($item->dispensed_at) {
                    // If item was dispensed, we need to return stock
                    $this->returnItemStock($item);
                }
                
                $item->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id(),
                ]);
            }

            // Log the cancellation
            Log::info("Paid Medication Cash Sale Cancelled", [
                'sale_id' => $medicationCashSale->id,
                'sale_number' => $medicationCashSale->sale_number,
                'total_amount' => $medicationCashSale->final_amount,
                'cancelled_by' => Auth::id(),
                'cancel_reason' => $cancelReason,
                'refund_required' => $refundRequired,
                'cancelled_at' => now()
            ]);

            // Create a financial transaction entry if refund is required
            if ($refundRequired) {
                // This would typically integrate with your financial system
                // For now, we'll just log it
                Log::info("Refund Required for Cancelled Sale", [
                    'sale_id' => $medicationCashSale->id,
                    'amount' => $medicationCashSale->final_amount,
                    'requires_manual_processing' => true
                ]);
            }

            $message = "Sale {$medicationCashSale->sale_number} has been successfully cancelled.";
            if ($refundRequired) {
                $message .= " Refund processing may be required.";
            }

            return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error cancelling paid medication cash sale: ' . $e->getMessage());
            return redirect()->route('medication-cash-sales.show', $medicationCashSale)
                ->with('error', 'Error cancelling sale: ' . $e->getMessage());
        }
    }

    /**
     * Return stock for a cancelled dispensed item
     */
    private function returnItemStock(MedicationCashSaleItem $item)
    {
        try {
            // Find the pharmacy location
            $pharmacyLocation = StoreLocation::where('is_pharmacy', true)
                ->where('is_active', true)
                ->first();

            if (!$pharmacyLocation) {
                Log::warning("No pharmacy location found for stock return");
                return;
            }

            // For simplicity, we'll return the dispensed quantity as a single batch
            // In a more sophisticated system, you'd track which specific batches were used
            $existingStock = StoreLocationStock::where('location_id', $pharmacyLocation->id)
                ->where('medication_id', $item->medication_id)
                ->where('status', StoreLocationStock::STATUS_ACTIVE)
                ->orderBy('expiry_date', 'asc')
                ->first();

            if ($existingStock) {
                // Add to existing stock
                $existingStock->increment('quantity', $item->quantity_dispensed);
            } else {
                // Create new stock entry (this might need more sophisticated handling)
                Log::warning("No existing stock found to return dispensed medication", [
                    'medication_id' => $item->medication_id,
                    'quantity_to_return' => $item->quantity_dispensed
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error returning stock for cancelled item: ' . $e->getMessage());
        }
    }

    /**
     * Check and update parent sale completion status
     * Called after individual item operations (cancel/dispense)
     */
    private function checkAndUpdateParentSaleStatus(MedicationCashSale $cashSale)
    {
        // Only check paid sales that aren't already marked as dispensed
        if (!$cashSale->is_paid) {
            return;
        }

        // Check if sale is now completed (all dispensable items dispensed)
        if ($cashSale->isCompleted()) {
            // Mark the sale as fully dispensed if not already marked
            if (!$cashSale->dispensed_at) {
                $cashSale->update([
                    'dispensed_by' => Auth::id(),
                    'dispensed_at' => now(),
                ]);
                
                Log::info('Parent cash sale marked as completed after individual item operations', [
                    'sale_id' => $cashSale->id,
                    'sale_number' => $cashSale->sale_number,
                    'completion_triggered_by' => Auth::id()
                ]);
            }
        }
    }
}
