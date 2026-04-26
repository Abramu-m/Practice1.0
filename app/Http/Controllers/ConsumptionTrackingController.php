<?php

namespace App\Http\Controllers;

use App\Services\ConsumptionTrackingService;
use App\Services\StockManagementService;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\Procedure;
use App\Models\Patient;
use App\Models\Medication;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsumptionTrackingController extends Controller
{
    protected $consumptionService;
    protected $stockService;

    public function __construct(
        ConsumptionTrackingService $consumptionService,
        StockManagementService $stockService
    ) {
        $this->consumptionService = $consumptionService;
        $this->stockService = $stockService;
    }

    /**
     * Display consumption dashboard
     */
    public function index()
    {
        try {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();

            // Get consumption stats with error handling
            $consumptionStats = [];
            $topConsumed = [];
            $recentConsumptions = [
                'lab_investigations' => collect(),
                'nursing_procedures' => collect(),
                'radiology_investigations' => collect(),
                'consultation_prescriptions' => collect(),
                'medication_cash_sales' => collect()
            ];

            try {
                $consumptionStats = $this->consumptionService->getConsumptionStats($startDate, $endDate);
            } catch (\Exception $e) {
                Log::error('Error getting consumption stats: ' . $e->getMessage());
                $consumptionStats = [
                    'investigation_consumptions' => 0,
                    'procedure_consumptions' => 0,
                    'prescriptions_dispensed' => 0,
                    'total_investigation_cost' => 0,
                    'total_procedure_cost' => 0,
                    'total_prescription_cost' => 0,
                    'top_consumed_medications' => []
                ];
            }

            try {
                $topConsumed = $this->consumptionService->getTopConsumedMedications($startDate, $endDate);
            } catch (\Exception $e) {
                Log::error('Error getting top consumed medications: ' . $e->getMessage());
                $topConsumed = [];
            }

            try {
                $recentConsumptions = $this->consumptionService->getCategorizedRecentConsumptions(5);
            } catch (\Exception $e) {
                Log::error('Error getting recent consumptions: ' . $e->getMessage());
                $recentConsumptions = [
                    'lab_investigations' => collect(),
                    'nursing_procedures' => collect(),
                    'radiology_investigations' => collect(),
                    'consultation_prescriptions' => collect(),
                    'medication_cash_sales' => collect()
                ];
            }

            return view('medications.consumption.index', compact(
                'consumptionStats',
                'topConsumed',
                'recentConsumptions'
            ));

        } catch (\Exception $e) {
            Log::error('Consumption index error: ' . $e->getMessage());
            
            // Return view with empty data to prevent complete failure
            return view('medications.consumption.index')->with([
                'consumptionStats' => [
                    'investigation_consumptions' => 0,
                    'procedure_consumptions' => 0,
                    'prescriptions_dispensed' => 0,
                    'total_investigation_cost' => 0,
                    'total_procedure_cost' => 0,
                    'total_prescription_cost' => 0,
                    'top_consumed_medications' => []
                ],
                'topConsumed' => [],
                'recentConsumptions' => [
                    'lab_investigations' => collect(),
                    'nursing_procedures' => collect(),
                    'radiology_investigations' => collect(),
                    'consultation_prescriptions' => collect(),
                    'medication_cash_sales' => collect()
                ]
            ]);
        }
    }
    /**
     * Show patient consumption history
     */
    public function showPatientHistory($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        
        $consumptionHistory = $this->consumptionService->getPatientConsumptionHistory($patientId);
        
        return view('consumption.patient-history', compact('patient', 'consumptionHistory'));
    }

    /**
     * Get consumption analytics (AJAX)
     */
    public function getAnalytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:store_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        try {
            $stats = $this->consumptionService->getConsumptionStats(
                $request->start_date,
                $request->end_date,
                $request->department_id
            );

            $topConsumed = $this->consumptionService->getTopConsumedMedications(
                $request->start_date,
                $request->end_date,
                $request->department_id
            );

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'top_consumed' => $topConsumed
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Display comprehensive consumption analytics dashboard
     */
    public function consumptionAnalytics(Request $request)
    {
        try {
            // Get filter parameters
            $period = $request->get('period', '30'); // days
            $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));
            $storeLocation = $request->get('store_location', 'all');
            $medicationType = $request->get('medication_type', 'all');

            // Calculate analytics data
            $analytics = [
                'overview' => $this->getConsumptionOverview($dateFrom, $dateTo, $storeLocation),
                'trends' => $this->getConsumptionTrends($dateFrom, $dateTo, $storeLocation),
                'topMedications' => $this->getTopConsumedMedications($dateFrom, $dateTo, $storeLocation),
                'byCategory' => $this->getConsumptionByCategory($dateFrom, $dateTo, $storeLocation),
                'byLocation' => $this->getConsumptionByLocation($dateFrom, $dateTo),
                'wastageAnalysis' => $this->getWastageAnalysis($dateFrom, $dateTo, $storeLocation),
                'costAnalysis' => $this->getCostAnalysis($dateFrom, $dateTo, $storeLocation),
                'stockTurnover' => $this->getStockTurnoverAnalysis($dateFrom, $dateTo, $storeLocation),
            ];

            // Get filter options
            $storeLocations = StoreLocation::where('is_active', true)->get();
            
            // Get medication types/categories for filtering
            $medicationTypes = collect();
            try {
                if (Schema::hasColumn('medications', 'category_id') && Schema::hasTable('store_categories')) {
                    // Use store_categories table
                    $medicationTypes = DB::table('store_categories')
                        ->whereExists(function($query) {
                            $query->select(DB::raw(1))
                                  ->from('medications')
                                  ->whereColumn('medications.category_id', 'store_categories.id');
                        })
                        ->pluck('name')
                        ->sort();
                } elseif (Schema::hasColumn('medications', 'category')) {
                    // Use direct category column
                    $medicationTypes = Medication::distinct()->pluck('category')->filter()->sort();
                }
            } catch (\Exception $e) {
                Log::error('Error getting medication types: ' . $e->getMessage());
            }

            return view('medications.consumption.analytics', compact(
                'analytics',
                'storeLocations',
                'medicationTypes',
                'period',
                'dateFrom',
                'dateTo',
                'storeLocation',
                'medicationType'
            ));

        } catch (\Exception $e) {
            Log::error('Consumption analytics error: ' . $e->getMessage());
            
            // Return default empty analytics with collections
            $emptyAnalytics = [
                'overview' => [
                    'total_consumption' => 0,
                    'total_cost' => 0,
                    'unique_medications' => 0,
                    'avg_daily_consumption' => 0,
                ],
                'trends' => collect(),
                'topMedications' => collect(),
                'byCategory' => collect(),
                'byLocation' => collect(),
                'wastageAnalysis' => [],
                'costAnalysis' => [],
                'stockTurnover' => collect(),
            ];
            
            return view('medications.consumption.analytics')->with([
                'analytics' => $emptyAnalytics,
                'storeLocations' => collect(),
                'medicationTypes' => collect(),
                'period' => '30',
                'dateFrom' => now()->subDays(30)->format('Y-m-d'),
                'dateTo' => now()->format('Y-m-d'),
                'storeLocation' => 'all',
                'medicationType' => 'all'
            ]);
        }
    }

    /**
     * Get consumption overview metrics
     */
    private function getConsumptionOverview($dateFrom, $dateTo, $storeLocation)
    {
        try {
            $query = DB::table('prescription_items')
                ->whereBetween('dispensed_at', [$dateFrom, $dateTo])
                ->whereNotNull('dispensed_at');

            if ($storeLocation !== 'all') {
                $query->whereExists(function($q) use ($storeLocation) {
                    $q->select(DB::raw(1))
                        ->from('store_location_stocks')
                        ->whereColumn('store_location_stocks.id', 'prescription_items.location_stock_id')
                        ->where('store_location_id', $storeLocation);
                });
            }

            $totalConsumption = (clone $query)->sum('quantity_dispensed');
            $uniqueMedications = (clone $query)->distinct()->count('medication_id');

            // Calculate total cost (with fallback for missing unit_cost)
            $totalCost = 0;
            $items = (clone $query)->leftJoin('store_location_stocks', 'prescription_items.location_stock_id', '=', 'store_location_stocks.id')
                ->select('prescription_items.quantity_dispensed', 'store_location_stocks.unit_cost')
                ->get();
            foreach ($items as $item) {
                $unitCost = $item->unit_cost ?? 0;
                $totalCost += $item->quantity_dispensed * $unitCost;
            }

            $days = max(1, \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1);
            $avgDailyConsumption = $totalConsumption / $days;

            return [
                'total_consumption' => $totalConsumption,
                'total_cost' => $totalCost,
                'unique_medications' => $uniqueMedications,
                'avg_daily_consumption' => round($avgDailyConsumption, 2),
                'period_days' => $days,
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating consumption overview: ' . $e->getMessage());
            return [
                'total_consumption' => 0,
                'total_cost' => 0,
                'unique_medications' => 0,
                'avg_daily_consumption' => 0,
                'period_days' => 0,
            ];
        }
    }

    /**
     * Get consumption trends over time
     */
    private function getConsumptionTrends($dateFrom, $dateTo, $storeLocation)
    {
        try {
            $query = DB::table('prescription_items')
                ->selectRaw('DATE(dispensed_at) as date, SUM(quantity_dispensed) as total')
                ->whereBetween('dispensed_at', [$dateFrom, $dateTo])
                ->whereNotNull('dispensed_at');

            if ($storeLocation !== 'all') {
                $query->whereExists(function($q) use ($storeLocation) {
                    $q->select(DB::raw(1))
                        ->from('store_location_stocks')
                        ->whereColumn('store_location_stocks.id', 'prescription_items.location_stock_id')
                        ->where('store_location_id', $storeLocation);
                });
            }

            return $query->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function($item) {
                    return [
                        'date' => $item->date,
                        'total' => (int) $item->total,
                        'formatted_date' => \Carbon\Carbon::parse($item->date)->format('M j')
                    ];
                });

        } catch (\Exception $e) {
            Log::error('Error calculating consumption trends: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get top consumed medications
     */
    private function getTopConsumedMedications($dateFrom, $dateTo, $storeLocation, $limit = 10)
    {
        try {
            $query = DB::table('prescription_items')
                ->selectRaw('medication_id, SUM(quantity_dispensed) as total_dispensed, COUNT(*) as prescription_count')
                ->whereBetween('dispensed_at', [$dateFrom, $dateTo])
                ->whereNotNull('dispensed_at');

            if ($storeLocation !== 'all') {
                $query->whereExists(function($q) use ($storeLocation) {
                    $q->select(DB::raw(1))
                        ->from('store_location_stocks')
                        ->whereColumn('store_location_stocks.id', 'prescription_items.location_stock_id')
                        ->where('store_location_id', $storeLocation);
                });
            }

            return $query->groupBy('medication_id')
                ->orderBy('total_dispensed', 'desc')
                ->limit($limit)
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting top consumed medications: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get consumption by medication category
     */
    private function getConsumptionByCategory($dateFrom, $dateTo, $storeLocation)
    {
        try {
            // Check if medications table has category_id column (foreign key approach)
            $hasCategoryId = Schema::hasColumn('medications', 'category_id');
            
            if (!$hasCategoryId) {
                // Check if medications table has direct category column
                $hasCategory = Schema::hasColumn('medications', 'category');
                if (!$hasCategory) {
                    return collect();
                }
                
                // Use direct category column
                $query = DB::table('prescription_items')
                    ->selectRaw('medications.category, SUM(prescription_items.quantity_dispensed) as total')
                    ->join('medications', 'prescription_items.medication_id', '=', 'medications.id')
                    ->whereBetween('prescription_items.dispensed_at', [$dateFrom, $dateTo])
                    ->whereNotNull('prescription_items.dispensed_at');

                if ($storeLocation !== 'all') {
                    $query->whereExists(function($q) use ($storeLocation) {
                        $q->select(DB::raw(1))
                            ->from('store_location_stocks')
                            ->whereColumn('store_location_stocks.id', 'prescription_items.location_stock_id')
                            ->where('store_location_id', $storeLocation);
                    });
                }

                return $query->groupBy('medications.category')
                    ->orderBy('total', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'category' => $item->category ?: 'Uncategorized',
                            'total' => (int) $item->total
                        ];
                    });
            } else {
                // Use store_categories relationship through category_id
                $query = DB::table('prescription_items')
                    ->selectRaw('COALESCE(store_categories.name, "Uncategorized") as category, SUM(prescription_items.quantity_dispensed) as total')
                    ->join('medications', 'prescription_items.medication_id', '=', 'medications.id')
                    ->leftJoin('store_categories', 'medications.category_id', '=', 'store_categories.id')
                    ->whereBetween('prescription_items.dispensed_at', [$dateFrom, $dateTo])
                    ->whereNotNull('prescription_items.dispensed_at');

                if ($storeLocation !== 'all') {
                    $query->whereExists(function($q) use ($storeLocation) {
                        $q->select(DB::raw(1))
                            ->from('store_location_stocks')
                            ->whereColumn('store_location_stocks.id', 'prescription_items.location_stock_id')
                            ->where('store_location_id', $storeLocation);
                    });
                }

                return $query->groupBy('store_categories.name')
                    ->orderBy('total', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'category' => $item->category,
                            'total' => (int) $item->total
                        ];
                    });
            }

        } catch (\Exception $e) {
            Log::error('Error getting consumption by category: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get consumption by store location
     */
    private function getConsumptionByLocation($dateFrom, $dateTo)
    {
        try {
            return DB::table('prescription_items')
                ->selectRaw('store_location_stocks.store_location_id, store_locations.name, SUM(prescription_items.quantity_dispensed) as total')
                ->join('store_location_stocks', 'prescription_items.store_location_stock_id', '=', 'store_location_stocks.id')
                ->join('store_locations', 'store_location_stocks.store_location_id', '=', 'store_locations.id')
                ->whereBetween('prescription_items.dispensed_at', [$dateFrom, $dateTo])
                ->whereNotNull('prescription_items.dispensed_at')
                ->groupBy('store_location_stocks.store_location_id', 'store_locations.name')
                ->orderBy('total', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'location' => $item->name,
                        'total' => (int) $item->total
                    ];
                });

        } catch (\Exception $e) {
            Log::error('Error getting consumption by location: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get wastage analysis
     */
    private function getWastageAnalysis($dateFrom, $dateTo, $storeLocation)
    {
        try {
            // This would require additional wastage tracking tables
            // For now, return placeholder data
            return [
                'total_wastage' => 0,
                'wastage_cost' => 0,
                'wastage_percentage' => 0,
                'top_wasted_items' => []
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating wastage analysis: ' . $e->getMessage());
            return [
                'total_wastage' => 0,
                'wastage_cost' => 0,
                'wastage_percentage' => 0,
                'top_wasted_items' => []
            ];
        }
    }

    /**
     * Get cost analysis
     */
    private function getCostAnalysis($dateFrom, $dateTo, $storeLocation)
    {
        try {
            $query = DB::table('prescription_items')
                ->leftJoin('store_location_stocks', 'prescription_items.location_stock_id', '=', 'store_location_stocks.id')
                ->select('prescription_items.quantity_dispensed', 'store_location_stocks.unit_cost')
                ->whereBetween('prescription_items.dispensed_at', [$dateFrom, $dateTo])
                ->whereNotNull('prescription_items.dispensed_at');

            if ($storeLocation !== 'all') {
                $query->whereExists(function($q) use ($storeLocation) {
                    $q->select(DB::raw(1))
                        ->from('store_location_stocks as sls_filter')
                        ->whereColumn('sls_filter.id', 'prescription_items.location_stock_id')
                        ->where('sls_filter.store_location_id', $storeLocation);
                });
            }

            $items = $query->get();
            $totalCost = 0;
            $itemCount = 0;

            foreach ($items as $item) {
                $unitCost = $item->unit_cost ?? 0;
                $totalCost += $item->quantity_dispensed * $unitCost;
                $itemCount++;
            }

            $avgCostPerItem = $itemCount > 0 ? $totalCost / $itemCount : 0;

            return [
                'total_cost' => $totalCost,
                'avg_cost_per_item' => round($avgCostPerItem, 2),
                'total_items' => $itemCount,
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating cost analysis: ' . $e->getMessage());
            return [
                'total_cost' => 0,
                'avg_cost_per_item' => 0,
                'total_items' => 0,
            ];
        }
    }

    /**
     * Get stock turnover analysis
     */
    private function getStockTurnoverAnalysis($dateFrom, $dateTo, $storeLocation)
    {
        try {
            // Calculate basic turnover metrics
            $consumptionQuery = DB::table('prescription_items')
                ->selectRaw('medication_id, SUM(quantity_dispensed) as total_consumed')
                ->whereBetween('dispensed_at', [$dateFrom, $dateTo])
                ->whereNotNull('dispensed_at');

            if ($storeLocation !== 'all') {
                $consumptionQuery->whereExists(function($q) use ($storeLocation) {
                    $q->select(DB::raw(1))
                        ->from('store_location_stocks')
                        ->whereColumn('store_location_stocks.id', 'prescription_items.location_stock_id')
                        ->where('store_location_id', $storeLocation);
                });
            }

            $consumption = $consumptionQuery->groupBy('medication_id')->get();

            $turnoverData = [];
            foreach ($consumption as $item) {
                // Get current stock level
                $stockQuery = \App\Models\StoreLocationStock::where('medication_id', $item->medication_id);
                if ($storeLocation !== 'all') {
                    $stockQuery->where('store_location_id', $storeLocation);
                }
                $currentStock = $stockQuery->sum('quantity_available');
                
                $turnoverRatio = $currentStock > 0 ? $item->total_consumed / $currentStock : 0;
                
                $turnoverData[] = [
                    'medication_id' => $item->medication_id,
                    'consumption' => $item->total_consumed,
                    'current_stock' => $currentStock,
                    'turnover_ratio' => round($turnoverRatio, 2)
                ];
            }

            return collect($turnoverData)->sortByDesc('turnover_ratio')->take(10);

        } catch (\Exception $e) {
            Log::error('Error calculating stock turnover: ' . $e->getMessage());
            return collect();
        }
    }
}
