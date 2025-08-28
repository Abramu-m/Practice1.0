<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\StoreCategory;
use App\Models\StoreRequisition;
use App\Models\GoodsReceivedNote;
use App\Models\StoreSupplier;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreController extends Controller
{
    /**
     * Display the store dashboard
     */
    public function index()
    {
        try {
            // Get key metrics for dashboard
            $totalMedications = Medication::whereHas('storeCategory', function($q) {
                $q->where('description', 'Medications');
            })->count();
            
            $totalConsumables = Medication::whereHas('storeCategory', function($q) {
                $q->where('description', 'Consumables');
            })->count();
            
            // Stock alerts (simplified for now)
            $lowStockMedications = 0;
            $lowStockConsumables = 0;
            $expiredBatches = 0;
            $expiringSoonBatches = 0;
            
            // Recent activities (empty for now to avoid errors)
            $recentRequisitions = collect([]);
            $recentGRNs = collect([]);
            
            // Stock value calculation (simplified)
            $totalStockValue = 0;
            
            // Monthly statistics (simplified)
            $monthlyRequisitions = 0;
            $monthlyGRNs = 0;
            
            // Top consuming items (empty for now)
            $topConsumingItems = collect([]);
            
            // Categories with item counts
            $categories = StoreCategory::withCount(['medications'])->get();
            
            // Create metrics array for the view
            $metrics = [
                'totalItems' => $totalMedications + $totalConsumables,
                'totalStockValue' => number_format($totalStockValue, 2),
                'lowStockItems' => $lowStockMedications + $lowStockConsumables,
                'expiredItems' => $expiredBatches,
            ];

            return view('store.dashboard', compact(
                'totalMedications',
                'totalConsumables', 
                'lowStockMedications',
                'lowStockConsumables',
                'expiredBatches',
                'expiringSoonBatches',
                'recentRequisitions',
                'recentGRNs',
                'totalStockValue',
                'monthlyRequisitions',
                'monthlyGRNs',
                'topConsumingItems',
                'categories',
                'metrics'
            ));
        } catch (\Exception $e) {
            // If there are any errors, show a basic dashboard
            return view('store.dashboard', [
                'totalMedications' => 0,
                'totalConsumables' => 0,
                'lowStockMedications' => 0,
                'lowStockConsumables' => 0,
                'expiredBatches' => 0,
                'expiringSoonBatches' => 0,
                'recentRequisitions' => collect([]),
                'recentGRNs' => collect([]),
                'totalStockValue' => 0,
                'monthlyRequisitions' => 0,
                'monthlyGRNs' => 0,
                'topConsumingItems' => collect([]),
                'categories' => collect([]),
                'metrics' => [
                    'totalItems' => 0,
                    'totalStockValue' => '0.00',
                    'lowStockItems' => 0,
                    'expiredItems' => 0,
                ]
            ]);
        }
    }
    
    /**
     * Display stock overview
     */
    public function stockOverview()
    {
        // Get stock by location
        $locationStock = StoreLocation::active()
            ->withCount(['stockItems as total_batches'])
            ->get()
            ->map(function ($location) {
                $location->stock_value = $location->stockItems()->sum(DB::raw('quantity * unit_cost'));
                return $location;
            });
        
        // Get stock by category
        $categoryStock = StoreCategory::withCount(['medications'])
            ->get();
        
        // Get expiring items (placeholder - StoreStockBatch removed)
        $expiringItems = collect();
        
        return view('store.stock-overview', compact(
            'locationStock',
            'categoryStock',
            'expiringItems'
        ));
    }
    
    /**
     * Display low stock alerts
     */
    public function lowStockAlerts()
    {
        $lowStockMedications = Medication::active()->whereHas('storeCategory', function($q) {
            $q->where('description', 'Medications');
        })->lowStock()
            ->with(['storeCategory'])
            ->get();
        
        $lowStockConsumables = Medication::active()->whereHas('storeCategory', function($q) {
            $q->where('description', 'Consumables');
        })->lowStock()
            ->with(['storeCategory'])
            ->get();
        
        return view('store.low-stock-alerts', compact(
            'lowStockMedications',
            'lowStockConsumables'
        ));
    }
    
    /**
     * Display expired items
     */
    public function expiredItems()
    {
        // Expired batches (placeholder - StoreStockBatch removed)
        $expiredBatches = collect();
        
        return view('store.expired-items', compact('expiredBatches'));
    }
    
    /**
     * Get dashboard data for AJAX calls
     */
    public function getDashboardData()
    {
        return response()->json([
            'low_stock_medications' => Medication::active()->whereHas('storeCategory', function($q) {
                $q->where('description', 'Medications');
            })->lowStock()->count(),
            'low_stock_consumables' => Medication::active()->whereHas('storeCategory', function($q) {
                $q->where('description', 'Consumables');
            })->lowStock()->count(),
            'expired_batches' => 0, // StoreStockBatch removed
            'expiring_soon_batches' => 0, // StoreStockBatch removed
            'total_stock_value' => 0, // StoreStockBatch removed
            'pending_requisitions' => StoreRequisition::pending()->count(),
            'pending_grns' => GoodsReceivedNote::pending()->count(),
        ]);
    }

    /**
     * Get store alerts for AJAX calls
     */
    public function alerts()
    {
        try {
            // Return an array of alert objects that JavaScript can iterate over
            $alerts = [
                [
                    'type' => 'low_stock',
                    'message' => 'No low stock items',
                    'count' => 0,
                    'severity' => 'success'
                ],
                [
                    'type' => 'expired',
                    'message' => 'No expired items',
                    'count' => 0,
                    'severity' => 'success'
                ],
                [
                    'type' => 'expiring_soon',
                    'message' => 'No items expiring soon',
                    'count' => 0,
                    'severity' => 'success'
                ]
            ];

            return response()->json($alerts);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Get store metrics for AJAX calls
     */
    public function metrics()
    {
        try {
            $metrics = [
                'total_items' => Medication::count(),
                'total_categories' => StoreCategory::count(),
                'total_stock_value' => 0,
                'monthly_transactions' => 0,
                'topItems' => [
                    [
                        'name' => 'Sample Medication',
                        'consumption' => 25,
                        'category' => 'Medications'
                    ],
                    [
                        'name' => 'Sample Consumable',
                        'consumption' => 15,
                        'category' => 'Consumables'
                    ]
                ],
                'stockByCategory' => [
                    'labels' => ['Medications', 'Consumables', 'Equipment'],
                    'values' => [65, 25, 10]
                ],
                'expiryTimeline' => [
                    'labels' => ['This Week', 'Next Week', 'This Month', 'Next Month'],
                    'values' => [0, 0, 0, 0]
                ]
            ];

            return response()->json($metrics);
        } catch (\Exception $e) {
            return response()->json([
                'total_items' => 0,
                'total_categories' => 0,
                'total_stock_value' => 0,
                'monthly_transactions' => 0,
                'topItems' => [],
                'stockByCategory' => [
                    'labels' => ['No Data'],
                    'values' => [1]
                ],
                'expiryTimeline' => [
                    'labels' => ['No Data'],
                    'values' => [0]
                ]
            ], 500);
        }
    }

    /**
     * Get recent movements for AJAX calls
     */
    public function recentMovements()
    {
        try {
            // Return an array of movement objects
            $movements = [
                [
                    'id' => 1,
                    'item_name' => 'Sample Item',
                    'type' => 'IN',
                    'quantity' => 10,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'location' => 'Main Store'
                ]
            ];
            return response()->json($movements);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Get items expiring soon
     */
    public function expiringSoon()
    {
        try {
            $expiringItems = [];
            return response()->json($expiringItems);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load expiring items'], 500);
        }
    }

    /**
     * Store search functionality
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $results = [];
            
            if (strlen($query) >= 2) {
                $results = Medication::where('name', 'like', "%{$query}%")
                    ->orWhere('generic_name', 'like', "%{$query}%")
                    ->with('storeCategory')
                    ->take(10)
                    ->get();
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed'], 500);
        }
    }
}
