<?php

namespace App\Http\Controllers;

use App\Services\StockManagementService;
use App\Services\ReconciliationService;
use App\Models\GoodsReceivedNote;
use App\Models\StoreRequisition;
use App\Models\Medication;
use App\Models\StoreLocation;
use App\Models\StoreLocationStock;
use App\Models\StoreStockMovement;
use App\Models\UnfitMedication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class StockManagementController extends Controller
{
    protected $stockService;
    protected $reconciliationService;

    public function __construct(
        ReconciliationService $reconciliationService
    ) {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Display stock overview dashboard
     */
    public function index()
    {
        $stockSummary = [
            'total_medications' => Medication::count(),
            'low_stock_count' => Medication::lowStock(10)->count(),
            'expiring_soon' => Medication::expiring(30)->count(),
            'expired_count' => Medication::expired()->count(),
            'total_locations' => StoreLocation::count(),
        ];

        $recentMovements = $this->stockService->getRecentStockMovements(10);
        $lowStockItems = Medication::lowStock(10)->with(['ledgerEntries'])->get();
        $expiringItems = Medication::expiring(30)->with(['ledgerEntries'])->get();

        return view('stock.index', compact(
            'stockSummary',
            'recentMovements', 
            'lowStockItems',
            'expiringItems'
        ));
    }

    /**
     * Display medication management dashboard
     */
    public function dashboard()
    {

        try {
            // Dashboard metrics
            $dashboardMetrics = [
                'total_medications' => Medication::count(),
                'low_stock_count' => Medication::lowStock()->count(),
                'expiring_soon_count' => Medication::expiringSoon()->count(),
                'monthly_consumption' => 0, // TODO: Implement monthly consumption calculation
            ];

            // Recent activities (placeholder data for now)
            $recentActivities = [
                [
                    'title' => 'Stock Transfer',
                    'description' => 'Transfer to Ward A completed',
                    'time' => '2 hours ago',
                    'type_color' => 'primary',
                    'icon' => 'exchange-alt'
                ],
                [
                    'title' => 'GRN Received',
                    'description' => 'New medication shipment received',
                    'time' => '4 hours ago',
                    'type_color' => 'success',
                    'icon' => 'truck'
                ],
                [
                    'title' => 'Low Stock Alert',
                    'description' => 'Paracetamol running low',
                    'time' => '6 hours ago',
                    'type_color' => 'warning',
                    'icon' => 'exclamation-triangle'
                ]
            ];

            // Pending counts
            $pendingCounts = [
                'grns' => GoodsReceivedNote::where('status', 'pending')->count(),
                'requisitions' => StoreRequisition::where('status', 'pending')->count(),
            ];

            // Weekly movements (placeholder data)
            $weeklyMovements = [
                'inward' => 150, // TODO: Calculate actual inward movements
                'outward' => 120, // TODO: Calculate actual outward movements
            ];

            // System health (placeholder data)
            $systemHealth = [
                'accuracy' => 95, // TODO: Calculate actual stock accuracy
                'last_reconciliation' => 'Last week', // TODO: Get actual last reconciliation date
            ];

            return view('medications.dashboard', compact(
                'dashboardMetrics',
                'recentActivities',
                'pendingCounts',
                'weeklyMovements',
                'systemHealth'
            ));
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error loading medications dashboard: ' . $e->getMessage());
            
            // Return with default/empty data
            return view('medications.dashboard', [
                'dashboardMetrics' => [
                    'total_medications' => 0,
                    'low_stock_count' => 0,
                    'expiring_soon_count' => 0,
                    'monthly_consumption' => 0,
                ],
                'recentActivities' => [],
                'pendingCounts' => ['grns' => 0, 'requisitions' => 0],
                'weeklyMovements' => ['inward' => 0, 'outward' => 0],
                'systemHealth' => ['accuracy' => 0, 'last_reconciliation' => 'Never'],
            ]);
        }
    }

    /**
     * Display stock levels overview
     */
    public function stockLevels(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search');
            $location = $request->get('location');
            $category = $request->get('category');
            $status = $request->get('status', 'all'); // all, low, normal, high, expired, expiring
            $perPage = $request->get('per_page', 25);

            // Base query
            $query = Medication::with(['ledgerEntries', 'storeLocationStocks.storeLocation']);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%")
                      ->orWhere('brand_name', 'like', "%{$search}%")
                      ->orWhere('batch_number', 'like', "%{$search}%");
                });
            }

            // Apply location filter
            if ($location && $location !== 'all') {
                $query->whereHas('storeLocationStocks', function($q) use ($location) {
                    $q->where('store_location_id', $location);
                });
            }

            // Apply category filter
            if ($category && $category !== 'all') {
                $query->where('category', $category);
            }

            // Apply status filter
            switch ($status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring':
                    $query->expiringSoon();
                    break;
                case 'normal':
                    $query->where('quantity_available', '>', DB::raw('reorder_level'))
                          ->where('expiry_date', '>', now()->addDays(30));
                    break;
            }

            // Get paginated results
            $medications = $query->orderBy('generic_name')->paginate($perPage);

            // Get summary statistics
            $statistics = [
                'total_medications' => Medication::count(),
                'low_stock_count' => Medication::lowStock()->count(),
                'expired_count' => Medication::expired()->count(),
                'expiring_soon_count' => Medication::expiringSoon()->count(),
                'total_quantity' => Medication::sum('quantity_available'),
                'total_value' => Medication::sum(DB::raw('quantity_available * unit_cost')),
            ];

            // Get store locations for filter dropdown
            $storeLocations = StoreLocation::orderBy('name')->get();

            // Get medication categories for filter dropdown
            $categories = Medication::distinct('category')
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category');

            return view('medications.stock.levels', compact(
                'medications',
                'statistics',
                'storeLocations', 
                'categories',
                'search',
                'location',
                'category',
                'status'
            ));

        } catch (\Exception $e) {
            Log::error('Stock levels view error: ' . $e->getMessage());
            
            // Create an empty paginated collection
            $emptyMedications = new LengthAwarePaginator(
                collect(),  // empty collection
                0,          // total items
                25,         // items per page
                1,          // current page
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            
            return view('medications.stock.levels')->with([
                'medications' => $emptyMedications,
                'statistics' => [
                    'total_medications' => 0,
                    'low_stock_count' => 0,
                    'expired_count' => 0,
                    'expiring_soon_count' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                ],
                'storeLocations' => collect(),
                'categories' => collect(),
                'search' => '',
                'location' => '',
                'category' => '',
                'status' => 'all'
            ]);
        }
    }

    /**
     * Display stock alerts and notifications
     */
    public function stockAlerts(Request $request)
    {
        try {
            // Get filter parameters
            $alertType = $request->get('type', 'all'); // all, low_stock, expired, expiring, out_of_stock
            $priority = $request->get('priority', 'all'); // all, critical, high, medium, low
            $location = $request->get('location');
            $perPage = $request->get('per_page', 20);

            // Initialize collections for different alert types
            $lowStockAlerts = collect();
            $expiredAlerts = collect();
            $expiringAlerts = collect();
            $outOfStockAlerts = collect();

            // Get low stock alerts
            if (in_array($alertType, ['all', 'low_stock'])) {
                $lowStockItems = Medication::lowStock()
                    ->with(['ledgerEntries', 'storeLocationStocks.storeLocation'])
                    ->get();

                foreach ($lowStockItems as $medication) {
                    $lowStockAlerts->push([
                        'id' => 'low_stock_' . $medication->id,
                        'type' => 'low_stock',
                        'priority' => $medication->quantity_available <= ($medication->reorder_level * 0.5) ? 'critical' : 'high',
                        'title' => 'Low Stock Alert',
                        'message' => "{$medication->generic_name} is running low",
                        'medication' => $medication,
                        'current_stock' => $medication->quantity_available,
                        'reorder_level' => $medication->reorder_level,
                        'created_at' => now()->subHours(rand(1, 48)), // Simulated timestamp
                        'status' => 'active',
                        'action_required' => 'reorder',
                    ]);
                }
            }

            // Get expired medications alerts
            if (in_array($alertType, ['all', 'expired'])) {
                $expiredItems = Medication::expired()
                    ->with(['ledgerEntries', 'storeLocationStocks.storeLocation'])
                    ->get();

                foreach ($expiredItems as $medication) {
                    $expiredAlerts->push([
                        'id' => 'expired_' . $medication->id,
                        'type' => 'expired',
                        'priority' => 'critical',
                        'title' => 'Expired Medication',
                        'message' => "{$medication->generic_name} has expired",
                        'medication' => $medication,
                        'expiry_date' => $medication->expiry_date,
                        'days_expired' => now()->diffInDays($medication->expiry_date),
                        'created_at' => $medication->expiry_date,
                        'status' => 'active',
                        'action_required' => 'dispose',
                    ]);
                }
            }

            // Get expiring soon alerts
            if (in_array($alertType, ['all', 'expiring'])) {
                $expiringItems = Medication::expiringSoon(30)
                    ->with(['ledgerEntries', 'storeLocationStocks.storeLocation'])
                    ->get();

                foreach ($expiringItems as $medication) {
                    $daysToExpiry = now()->diffInDays($medication->expiry_date);
                    $expiringAlerts->push([
                        'id' => 'expiring_' . $medication->id,
                        'type' => 'expiring',
                        'priority' => $daysToExpiry <= 7 ? 'high' : 'medium',
                        'title' => 'Expiring Soon',
                        'message' => "{$medication->generic_name} expires in {$daysToExpiry} days",
                        'medication' => $medication,
                        'expiry_date' => $medication->expiry_date,
                        'days_to_expiry' => $daysToExpiry,
                        'created_at' => now()->subDays(rand(1, 7)),
                        'status' => 'active',
                        'action_required' => 'monitor',
                    ]);
                }
            }

            // Get out of stock alerts
            if (in_array($alertType, ['all', 'out_of_stock'])) {
                $outOfStockItems = Medication::where('quantity_available', '<=', 0)
                    ->with(['ledgerEntries', 'storeLocationStocks.storeLocation'])
                    ->get();

                foreach ($outOfStockItems as $medication) {
                    $outOfStockAlerts->push([
                        'id' => 'out_of_stock_' . $medication->id,
                        'type' => 'out_of_stock',
                        'priority' => 'critical',
                        'title' => 'Out of Stock',
                        'message' => "{$medication->generic_name} is out of stock",
                        'medication' => $medication,
                        'current_stock' => $medication->quantity_available,
                        'created_at' => now()->subHours(rand(1, 24)),
                        'status' => 'active',
                        'action_required' => 'urgent_reorder',
                    ]);
                }
            }

            // Combine all alerts
            $allAlerts = collect()
                ->merge($lowStockAlerts)
                ->merge($expiredAlerts)
                ->merge($expiringAlerts)
                ->merge($outOfStockAlerts);

            // Apply priority filter
            if ($priority !== 'all') {
                $allAlerts = $allAlerts->where('priority', $priority);
            }

            // Apply location filter if specified
            if ($location && $location !== 'all') {
                $allAlerts = $allAlerts->filter(function ($alert) use ($location) {
                    return $alert['medication']->storeLocationStocks
                        ->pluck('store_location_id')
                        ->contains($location);
                });
            }

            // Sort by priority and date
            $priorityOrder = ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];
            $allAlerts = $allAlerts->sortBy([
                ['priority', function ($a, $b) use ($priorityOrder) {
                    return $priorityOrder[$a] <=> $priorityOrder[$b];
                }],
                ['created_at', 'desc']
            ]);

            // Create manual pagination
            $currentPage = $request->get('page', 1);
            $total = $allAlerts->count();
            $alerts = $allAlerts->forPage($currentPage, $perPage);

            $paginatedAlerts = new LengthAwarePaginator(
                $alerts,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );

            // Get summary statistics
            $alertsSummary = [
                'total_alerts' => $allAlerts->count(),
                'critical_alerts' => $allAlerts->where('priority', 'critical')->count(),
                'high_alerts' => $allAlerts->where('priority', 'high')->count(),
                'medium_alerts' => $allAlerts->where('priority', 'medium')->count(),
                'low_alerts' => $allAlerts->where('priority', 'low')->count(),
                'low_stock_count' => $lowStockAlerts->count(),
                'expired_count' => $expiredAlerts->count(),
                'expiring_count' => $expiringAlerts->count(),
                'out_of_stock_count' => $outOfStockAlerts->count(),
            ];

            // Get store locations for filter dropdown
            $storeLocations = StoreLocation::orderBy('name')->get();

            return view('medications.stock.alerts', compact(
                'paginatedAlerts',
                'alertsSummary',
                'storeLocations',
                'alertType',
                'priority',
                'location'
            ));

        } catch (\Exception $e) {
            Log::error('Stock alerts view error: ' . $e->getMessage());
            
            // Create empty paginated alerts
            $emptyAlerts = new LengthAwarePaginator(
                collect(),
                0,
                20,
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            
            return view('medications.stock.alerts')->with([
                'paginatedAlerts' => $emptyAlerts,
                'alertsSummary' => [
                    'total_alerts' => 0,
                    'critical_alerts' => 0,
                    'high_alerts' => 0,
                    'medium_alerts' => 0,
                    'low_alerts' => 0,
                    'low_stock_count' => 0,
                    'expired_count' => 0,
                    'expiring_count' => 0,
                    'out_of_stock_count' => 0,
                ],
                'storeLocations' => collect(),
                'alertType' => 'all',
                'priority' => 'all',
                'location' => ''
            ]);
        }
    }

    /**
     * Display store requisitions index
     */
    public function requisitionsIndex(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search');
            $status = $request->get('status', 'all'); // all, pending, approved, rejected, processing
            $department = $request->get('department');
            $priority = $request->get('priority');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 15);

            // Base query - update to use correct relationship name
            $query = StoreRequisition::with(['requestedBy', 'approvedBy', 'items.medication', 'requestingLocation']);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('requisition_number', 'like', "%{$search}%")
                      ->orWhere('purpose', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('requestedBy', function($rq) use ($search) {
                          $rq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('requestingLocation', function($dq) use ($search) {
                          $dq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply status filter
            switch ($status) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'approved':
                    $query->where('status', 'approved');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected');
                    break;
                case 'processing':
                    $query->where('status', 'processing');
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
            }

            // Apply department filter (using requesting_location_id)
            if ($department && $department !== 'all') {
                $query->where('requesting_location_id', $department);
            }

            // Apply priority filter
            if ($priority && $priority !== 'all') {
                $query->where('priority', $priority);
            }

            // Apply date range filter (using requisition_date)
            if ($dateFrom) {
                $query->whereDate('requisition_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('requisition_date', '<=', $dateTo);
            }

            // Get paginated results (order by requisition_date)
            $requisitions = $query->orderBy('requisition_date', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->paginate($perPage);

            // Get summary statistics
            $statistics = [
                'total_requisitions' => StoreRequisition::count(),
                'pending_requisitions' => StoreRequisition::where('status', 'pending')->count(),
                'approved_requisitions' => StoreRequisition::where('status', 'approved')->count(),
                'processing_requisitions' => StoreRequisition::where('status', 'processing')->count(),
                'completed_requisitions' => StoreRequisition::where('status', 'completed')->count(),
                'rejected_requisitions' => StoreRequisition::where('status', 'rejected')->count(),
                'monthly_requisitions' => StoreRequisition::whereBetween('requisition_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'high_priority_pending' => StoreRequisition::where('status', 'pending')->where('priority', 'high')->count(),
            ];

            // Get departments for filter dropdown (using StoreLocation model)
            $departments = StoreLocation::where('is_active', true)->orderBy('name')->get();

            return view('store.requisitions.index', compact(
                'requisitions',
                'statistics',
                'departments',
                'search',
                'status',
                'department',
                'priority',
                'dateFrom',
                'dateTo'
            ));

        } catch (\Exception $e) {
            Log::error('Requisitions index view error: ' . $e->getMessage());
            
            // Create empty paginated requisitions
            $emptyRequisitions = new LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            
            return view('store.requisitions.index')->with([
                'requisitions' => $emptyRequisitions,
                'statistics' => [
                    'total_requisitions' => 0,
                    'pending_requisitions' => 0,
                    'approved_requisitions' => 0,
                    'processing_requisitions' => 0,
                    'completed_requisitions' => 0,
                    'rejected_requisitions' => 0,
                    'monthly_requisitions' => 0,
                    'high_priority_pending' => 0,
                ],
                'departments' => collect(),
                'search' => '',
                'status' => 'all',
                'department' => '',
                'priority' => '',
                'dateFrom' => '',
                'dateTo' => ''
            ]);
        }
    }

    /**
     * Show stock transfer form
     */
    public function showStockTransfer()
    {
        $locations = StoreLocation::where('status', 'active')->get();
        $medications = Medication::where('status', 'active')->get();

        return view('stock.transfer', compact('locations', 'medications'));
    }

    /**
     * Process stock transfer
     */
    public function transferStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_location_id' => 'required|exists:store_locations,id',
            'to_location_id' => 'required|exists:store_locations,id|different:from_location_id',
            'items' => 'required|array',
            'items.*.medication_id' => 'required|exists:medications,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.batch_number' => 'nullable|string|max:50',
            'transfer_reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->stockService->transferStock(
                $request->from_location_id,
                $request->to_location_id,
                $request->items,
                [
                    'reason' => $request->transfer_reason,
                    'notes' => $request->notes
                ]
            );
            
            if ($result['success']) {
                return redirect()->route('stock.transfers.index')
                    ->with('success', 'Stock transfer completed successfully.');
            } else {
                return back()->with('error', $result['message'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing transfer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show stock adjustment form
     */
    public function showStockAdjustment()
    {
        $locations = StoreLocation::where('status', 'active')->get();
        $medications = Medication::where('status', 'active')->get();

        return view('stock.adjustment', compact('locations', 'medications'));
    }

    /**
     * Process stock adjustment
     */
    public function adjustStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:store_locations,id',
            'medication_id' => 'required|exists:medications,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'batch_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->stockService->adjustStock([
                'location_id' => $request->location_id,
                'medication_id' => $request->medication_id,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'batch_number' => $request->batch_number,
                'notes' => $request->notes
            ]);
            
            if ($result['success']) {
                return redirect()->route('stock.adjustments.index')
                    ->with('success', 'Stock adjustment processed successfully.');
            } else {
                return back()->with('error', $result['message'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing adjustment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get stock availability for a medication at a location (AJAX)
     */
    public function getStockAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medication_id' => 'required|exists:medications,id',
            'location_id' => 'required|exists:store_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        try {
            $availability = $this->stockService->getStockAvailability(
                $request->medication_id,
                $request->location_id
            );

            return response()->json([
                'success' => true,
                'total_available' => $availability->sum('quantity_current'),
                'batches' => $availability->map(function($stock) {
                    return [
                        'id' => $stock->id,
                        'batch_number' => $stock->batch_number,
                        'expiry_date' => $stock->expiry_date->format('Y-m-d'),
                        'quantity' => $stock->quantity_current,
                        'unit_cost' => $stock->unit_cost
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show disposal form
     */
    public function showDisposal()
    {
        $expiredStock = StoreLocationStock::expired()
            ->with(['medication', 'location'])
            ->where('quantity_current', '>', 0)
            ->get();

        $nearExpiryStock = StoreLocationStock::expiringSoon(30)
            ->with(['medication', 'location'])
            ->where('quantity_current', '>', 0)
            ->get();

        return view('stock.disposal', compact('expiredStock', 'nearExpiryStock'));
    }

    /**
     * Process medication disposal
     */
    public function disposeStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.stock_id' => 'required|exists:store_locations_stock,id',
            'items.*.quantity_to_dispose' => 'required|numeric|min:0.01',
            'items.*.reason' => 'required|string|max:255',
            'disposal_method' => 'required|string|max:255',
            'verification_required' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->stockService->disposeUnfitMedications(
                $request->items,
                'disposal',
                $request->disposal_method,
                [
                    'verification_required' => $request->boolean('verification_required'),
                    'notes' => $request->notes
                ]
            );
            
            if ($result['success']) {
                return redirect()->route('stock.disposal.index')
                    ->with('success', 'Medication disposal processed successfully.');
            } else {
                return back()->with('error', $result['message'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing disposal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display stock transfers index
     */
    public function transfersIndex(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search');
            $status = $request->get('status', 'all'); // all, pending, approved, completed, cancelled
            $fromLocation = $request->get('from_location');
            $toLocation = $request->get('to_location');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 15);

            // Import StoreStockMovement model
            $query = StoreStockMovement::transfer()
                ->with(['medication', 'fromLocation', 'toLocation', 'user']);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('medication', function($mq) use ($search) {
                          $mq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply status filter
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Apply location filters
            if ($fromLocation && $fromLocation !== 'all') {
                $query->where('from_location_id', $fromLocation);
            }
            if ($toLocation && $toLocation !== 'all') {
                $query->where('to_location_id', $toLocation);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('movement_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('movement_date', '<=', $dateTo);
            }

            // Get paginated results
            $transfers = $query->orderBy('movement_date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate($perPage);

            // Get summary statistics
            $statistics = [
                'total_transfers' => StoreStockMovement::transfer()->count(),
                'pending_transfers' => StoreStockMovement::transfer()->where('status', 'pending')->count(),
                'completed_transfers' => StoreStockMovement::transfer()->where('status', 'completed')->count(),
                'cancelled_transfers' => StoreStockMovement::transfer()->where('status', 'cancelled')->count(),
                'monthly_transfers' => StoreStockMovement::transfer()
                    ->whereBetween('movement_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'total_value_transferred' => StoreStockMovement::transfer()
                    ->where('status', 'completed')
                    ->sum(DB::raw('quantity * unit_cost')),
            ];

            // Get store locations for filter dropdown
            $storeLocations = StoreLocation::where('is_active', true)->orderBy('name')->get();

            return view('medications.stock.transfers.index', compact(
                'transfers',
                'statistics',
                'storeLocations',
                'search',
                'status',
                'fromLocation',
                'toLocation',
                'dateFrom',
                'dateTo'
            ));

        } catch (\Exception $e) {
            Log::error('Transfers index view error: ' . $e->getMessage());
            
            // Create empty paginated transfers
            $emptyTransfers = new LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            
            return view('medications.stock.transfers.index')->with([
                'transfers' => $emptyTransfers,
                'statistics' => [
                    'total_transfers' => 0,
                    'pending_transfers' => 0,
                    'completed_transfers' => 0,
                    'cancelled_transfers' => 0,
                    'monthly_transfers' => 0,
                    'total_value_transferred' => 0,
                ],
                'storeLocations' => collect(),
                'search' => '',
                'status' => 'all',
                'fromLocation' => '',
                'toLocation' => '',
                'dateFrom' => '',
                'dateTo' => ''
            ]);
        }
    }

    /**
     * Show the form for creating a new transfer
     */
    public function createTransfer()
    {
        try {
            $storeLocations = StoreLocation::where('is_active', true)->orderBy('name')->get();
            $medications = Medication::active()->orderBy('generic_name')->get();
            
            return view('medications.stock.transfers.create', compact('storeLocations', 'medications'));
        } catch (\Exception $e) {
            Log::error('Error loading transfer creation form: ' . $e->getMessage());
            return redirect()->route('medications.stock.transfers.index')
                ->with('error', 'Error loading transfer form');
        }
    }

    /**
     * Store a newly created transfer
     */
    public function storeTransfer(Request $request)
    {
        try {
            $validated = $request->validate([
                'from_location_id' => 'required|exists:store_locations,id',
                'to_location_id' => 'required|exists:store_locations,id|different:from_location_id',
                'transfer_date' => 'required|date|after_or_equal:today',
                'notes' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.medication_id' => 'required|exists:medications,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.batch_number' => 'nullable|string|max:50',
                'items.*.unit_cost' => 'nullable|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Generate transfer reference number
            $transferNumber = 'TRF-' . date('Y') . '-' . str_pad(StoreStockMovement::transfer()->count() + 1, 6, '0', STR_PAD_LEFT);

            // Create transfer movements for each item
            foreach ($validated['items'] as $item) {
                StoreStockMovement::create([
                    'reference_number' => $transferNumber,
                    'medication_id' => $item['medication_id'],
                    'from_location_id' => $validated['from_location_id'],
                    'to_location_id' => $validated['to_location_id'],
                    'movement_type' => 'transfer',
                    'reference_type' => 'transfer',
                    'movement_date' => $validated['transfer_date'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'batch_number' => $item['batch_number'],
                    'notes' => $validated['notes'],
                    'status' => 'pending',
                    'user_id' => Auth::user()->id,
                ]);
            }

            DB::commit();

            return redirect()->route('medications.stock.transfers.index')
                ->with('success', 'Transfer created successfully and is pending approval');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating transfer: ' . $e->getMessage());
            return back()->with('error', 'Error creating transfer')->withInput();
        }
    }

    /**
     * Process/approve a transfer
     */
    public function processTransfer(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:approve,reject',
                'notes' => 'nullable|string|max:500'
            ]);

            // Find all transfer movements with the same reference number
            $transfer = StoreStockMovement::transfer()->findOrFail($id);
            $allTransferItems = StoreStockMovement::transfer()
                ->where('reference_number', $transfer->reference_number)
                ->get();

            DB::beginTransaction();

            if ($validated['action'] === 'approve') {
                // Process each transfer item
                foreach ($allTransferItems as $transferItem) {
                    // Update stock at from location (decrease)
                    $fromStock = StoreLocationStock::where([
                        'store_location_id' => $transferItem->from_location_id,
                        'medication_id' => $transferItem->medication_id,
                    ])->first();

                    if ($fromStock && $fromStock->quantity_current >= $transferItem->quantity) {
                        $fromStock->decrement('quantity_current', $transferItem->quantity);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'success' => false, 
                            'message' => 'Insufficient stock at source location for ' . $transferItem->medication->name
                        ]);
                    }

                    // Update stock at to location (increase)
                    $toStock = StoreLocationStock::updateOrCreate([
                        'store_location_id' => $transferItem->to_location_id,
                        'medication_id' => $transferItem->medication_id,
                    ], [
                        'quantity_current' => 0,
                        'reorder_level' => 10,
                        'max_level' => 100,
                        'unit_cost' => $transferItem->unit_cost,
                        'batch_number' => $transferItem->batch_number,
                    ]);

                    $toStock->increment('quantity_current', $transferItem->quantity);

                    // Update transfer status
                    $transferItem->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'processed_by' => Auth::user()->id,
                        'processing_notes' => $validated['notes']
                    ]);
                }

                $message = 'Transfer approved and stock updated successfully';
            } else {
                // Reject transfer
                foreach ($allTransferItems as $transferItem) {
                    $transferItem->update([
                        'status' => 'cancelled',
                        'processed_at' => now(),
                        'processed_by' => Auth::user()->id,
                        'processing_notes' => $validated['notes']
                    ]);
                }

                $message = 'Transfer rejected successfully';
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing transfer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error processing transfer']);
        }
    }

    /**
     * Display stock adjustments index
     */
    public function adjustmentsIndex(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search');
            $type = $request->get('type', 'all'); // all, increase, decrease
            $reason = $request->get('reason', 'all'); // all, damage, expiry, theft, count_correction, etc.
            $location = $request->get('location');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 15);

            // Base query for adjustments
            $query = StoreStockMovement::where('movement_type', 'adjustment')
                ->with(['medication', 'fromLocation', 'toLocation', 'user']);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhere('reason', 'like', "%{$search}%")
                      ->orWhereHas('medication', function($mq) use ($search) {
                          $mq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply type filter (increase/decrease)
            if ($type === 'increase') {
                $query->where('quantity', '>', 0);
            } elseif ($type === 'decrease') {
                $query->where('quantity', '<', 0);
            }

            // Apply reason filter
            if ($reason !== 'all') {
                $query->where('reason', $reason);
            }

            // Apply location filter
            if ($location && $location !== 'all') {
                $query->where(function($q) use ($location) {
                    $q->where('from_location_id', $location)
                      ->orWhere('to_location_id', $location);
                });
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('movement_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('movement_date', '<=', $dateTo);
            }

            // Get paginated results
            $adjustments = $query->orderBy('movement_date', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->paginate($perPage);

            // Get summary statistics
            $statistics = [
                'total_adjustments' => StoreStockMovement::where('movement_type', 'adjustment')->count(),
                'positive_adjustments' => StoreStockMovement::where('movement_type', 'adjustment')->where('quantity', '>', 0)->count(),
                'negative_adjustments' => StoreStockMovement::where('movement_type', 'adjustment')->where('quantity', '<', 0)->count(),
                'monthly_adjustments' => StoreStockMovement::where('movement_type', 'adjustment')
                    ->whereBetween('movement_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'total_value_adjusted' => StoreStockMovement::where('movement_type', 'adjustment')
                    ->sum(DB::raw('ABS(quantity) * unit_cost')),
                'reasons_breakdown' => StoreStockMovement::where('movement_type', 'adjustment')
                    ->selectRaw('reason, COUNT(*) as count')
                    ->groupBy('reason')
                    ->pluck('count', 'reason')
                    ->toArray(),
            ];

            // Get store locations for filter dropdown
            $storeLocations = StoreLocation::where('is_active', true)->orderBy('name')->get();

            // Get common adjustment reasons for filter
            $adjustmentReasons = [
                'damage' => 'Damage/Breakage',
                'expiry' => 'Expiry',
                'theft' => 'Theft/Loss',
                'count_correction' => 'Stock Count Correction',
                'donation' => 'Donation',
                'return' => 'Return to Supplier',
                'other' => 'Other',
            ];

            return view('medications.stock.adjustments.index', compact(
                'adjustments',
                'statistics',
                'storeLocations',
                'adjustmentReasons',
                'search',
                'type',
                'reason',
                'location',
                'dateFrom',
                'dateTo'
            ));

        } catch (\Exception $e) {
            Log::error('Adjustments index view error: ' . $e->getMessage());
            
            // Create empty paginated adjustments
            $emptyAdjustments = new LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            
            return view('medications.stock.adjustments.index')->with([
                'adjustments' => $emptyAdjustments,
                'statistics' => [
                    'total_adjustments' => 0,
                    'positive_adjustments' => 0,
                    'negative_adjustments' => 0,
                    'monthly_adjustments' => 0,
                    'total_value_adjusted' => 0,
                    'reasons_breakdown' => [],
                ],
                'storeLocations' => collect(),
                'adjustmentReasons' => [],
                'search' => '',
                'type' => 'all',
                'reason' => 'all',
                'location' => '',
                'dateFrom' => '',
                'dateTo' => ''
            ]);
        }
    }

    /**
     * Show the form for creating a new adjustment
     */
    public function createAdjustment()
    {
        try {
            $storeLocations = StoreLocation::where('is_active', true)->orderBy('name')->get();
            $medications = Medication::active()->orderBy('generic_name')->get();
            
            // Adjustment reasons
            $adjustmentReasons = [
                'damage' => 'Damage/Breakage',
                'expiry' => 'Expiry',
                'theft' => 'Theft/Loss',
                'count_correction' => 'Stock Count Correction',
                'donation' => 'Donation',
                'return' => 'Return to Supplier',
                'other' => 'Other',
            ];
            
            return view('medications.stock.adjustments.create', compact('storeLocations', 'medications', 'adjustmentReasons'));
        } catch (\Exception $e) {
            Log::error('Error loading adjustment creation form: ' . $e->getMessage());
            return redirect()->route('medications.stock.adjustments.index')
                ->with('error', 'Error loading adjustment form');
        }
    }

    /**
     * Store a newly created adjustment
     */
    public function storeAdjustment(Request $request)
    {
        try {
            $validated = $request->validate([
                'location_id' => 'required|exists:store_locations,id',
                'medication_id' => 'required|exists:medications,id',
                'adjustment_type' => 'required|in:increase,decrease',
                'quantity' => 'required|numeric|min:0.01',
                'reason' => 'required|string|max:255',
                'unit_cost' => 'nullable|numeric|min:0',
                'batch_number' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            // Generate adjustment reference number
            $adjustmentNumber = 'ADJ-' . date('Y') . '-' . str_pad(
                StoreStockMovement::where('movement_type', 'adjustment')->count() + 1, 
                6, '0', STR_PAD_LEFT
            );

            // Determine quantity sign based on adjustment type
            $adjustmentQuantity = $validated['adjustment_type'] === 'increase' 
                ? $validated['quantity'] 
                : -$validated['quantity'];

            // Create adjustment movement
            $adjustment = StoreStockMovement::create([
                'reference_number' => $adjustmentNumber,
                'medication_id' => $validated['medication_id'],
                'from_location_id' => $validated['location_id'], // Source location for tracking
                'to_location_id' => $validated['location_id'],   // Same location for adjustments
                'movement_type' => 'adjustment',
                'reference_type' => 'adjustment',
                'movement_date' => now(),
                'quantity' => $adjustmentQuantity,
                'unit_cost' => $validated['unit_cost'] ?? 0,
                'batch_number' => $validated['batch_number'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'status' => 'completed', // Adjustments are immediately applied
                'user_id' => Auth::user()->id,
            ]);

            // Update stock levels
            if ($validated['adjustment_type'] === 'increase') {
                // Increase stock
                $stock = StoreLocationStock::updateOrCreate([
                    'store_location_id' => $validated['location_id'],
                    'medication_id' => $validated['medication_id'],
                ], [
                    'quantity_current' => 0,
                    'reorder_level' => 10,
                    'max_level' => 100,
                    'unit_cost' => $validated['unit_cost'] ?? 0,
                    'batch_number' => $validated['batch_number'],
                ]);

                $stock->increment('quantity_current', $validated['quantity']);
            } else {
                // Decrease stock
                $stock = StoreLocationStock::where([
                    'store_location_id' => $validated['location_id'],
                    'medication_id' => $validated['medication_id'],
                ])->first();

                if ($stock && $stock->quantity_current >= $validated['quantity']) {
                    $stock->decrement('quantity_current', $validated['quantity']);
                } else {
                    DB::rollBack();
                    return back()->with('error', 'Insufficient stock for this adjustment')
                                ->withInput();
                }
            }

            DB::commit();

            return redirect()->route('medications.stock.adjustments.index')
                ->with('success', 'Stock adjustment processed successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating adjustment: ' . $e->getMessage());
            return back()->with('error', 'Error processing adjustment')->withInput();
        }
    }

    /**
     * Display medication disposal index page
     */
    public function disposalIndex(Request $request)
    {
            // Get filter parameters
            $search = $request->get('search');
            $status = $request->get('status', 'all'); // all, pending_verification, verified, no_verification
            $reason = $request->get('reason', 'all'); // all, expired, damaged, recalled, etc.
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 15);

            // Base query for UnfitMedication records only
            $query = UnfitMedication::with([
                'medication:id,generic_name,brand_name,strength',
                'disposedBy:id,first_name,middle_name,last_name',
                'verifiedBy:id,first_name,middle_name,last_name'
            ]);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('batch_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('medication', function($mq) use ($search) {
                          $mq->where('generic_name', 'like', "%{$search}%")
                            ->orWhere('brand_name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply verification status filter
            if ($status === 'pending_verification') {
                $query->where('verification_required', true)->whereNull('verified_by');
            } elseif ($status === 'verified') {
                $query->where('verification_required', true)->whereNotNull('verified_by');
            } elseif ($status === 'no_verification') {
                $query->where('verification_required', false);
            }

            // Apply reason filter
            if ($reason !== 'all') {
                $query->where('reason', $reason);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('disposed_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('disposed_at', '<=', $dateTo);
            }

            // Get paginated results
            $disposals = $query->orderBy('disposed_at', 'desc')->paginate($perPage);

            // Get statistics from UnfitMedication table only
            $statistics = [
                'total_disposals' => UnfitMedication::count(),
                'pending_verification' => UnfitMedication::where('verification_required', true)->whereNull('verified_by')->count(),
                'verified_disposals' => UnfitMedication::where('verification_required', true)->whereNotNull('verified_by')->count(),
                'monthly_disposals' => UnfitMedication::whereBetween('disposed_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'total_quantity_disposed' => UnfitMedication::sum('quantity_discarded'),
                'reasons_breakdown' => UnfitMedication::selectRaw('reason, COUNT(*) as count')->groupBy('reason')->pluck('count', 'reason')->toArray(),
            ];

            // Get common disposal reasons for filter
            $disposalReasons = [
                'expired' => 'Expired',
                'damaged' => 'Damaged/Broken',
                'exhausted' => 'Exhausted',
                'contaminated' => 'Contaminated',
                'recalled' => 'Product Recall',
                'quality_issue' => 'Quality Issue',
                'other' => 'Other',
            ];

            return view('medications.stock.disposal.index', compact(
                'disposals',
                'statistics',
                'disposalReasons',
                'search',
                'status',
                'reason',
                'dateFrom',
                'dateTo'
            ));
    }

    /**
     * Get disposal details (AJAX)
     */
    public function getDisposalDetails($disposalId)
    {
        try {
            $disposal = UnfitMedication::with([
                'medication:id,generic_name,brand_name,strength',
                'disposedBy:id,first_name,middle_name,last_name',
                'verifiedBy:id,first_name,middle_name,last_name'
            ])->findOrFail($disposalId);

            return response()->json([
                'success' => true,
                'disposal' => $disposal
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching disposal details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Disposal not found or error loading details'
            ], 404);
        }
    }

    /**
     * Verify a disposal
     */
    public function verifyDisposal($disposalId)
    {
        try {
            // Ensure user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be authenticated to verify disposals'
                ], 401);
            }

            $disposal = UnfitMedication::findOrFail($disposalId);

            // Check if verification is required
            if (!$disposal->verification_required) {
                return response()->json([
                    'success' => false,
                    'message' => 'This disposal does not require verification'
                ], 400);
            }

            // Check if already verified
            if ($disposal->verified_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'This disposal has already been verified'
                ], 400);
            }

            // Verify the disposal
            $disposal->update([
                'verified_by' => Auth::user()->id,
                'verified_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Disposal verified successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error verifying disposal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error verifying disposal'
            ], 500);
        }
    }

    /**
     * Complete a disposal
     */
    public function completeDisposal($disposalId)
    {
        try {
            $disposal = UnfitMedication::findOrFail($disposalId);

            // Update disposal as completed (you might want to add a status field)
            $disposal->update([
                'notes' => ($disposal->notes ? $disposal->notes . ' | ' : '') . 'Completed on ' . now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Disposal marked as completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Error completing disposal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error completing disposal'
            ], 500);
        }
    }

    /**
     * Cancel a disposal
     */
    public function cancelDisposal($disposalId)
    {
        try {
            $disposal = UnfitMedication::findOrFail($disposalId);

            // Check if disposal can be cancelled (e.g., not yet verified)
            if ($disposal->verified_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a verified disposal'
                ], 400);
            }

            DB::beginTransaction();

            // If we want to actually reverse the disposal, we'd need to restore stock
            // For now, we'll just mark it as cancelled in notes
            $disposal->update([
                'notes' => ($disposal->notes ? $disposal->notes . ' | ' : '') . 'CANCELLED on ' . now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Disposal cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling disposal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling disposal'
            ], 500);
        }
    }
}
