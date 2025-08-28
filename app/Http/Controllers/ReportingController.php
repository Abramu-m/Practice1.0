<?php

namespace App\Http\Controllers;

use App\Services\StockManagementService;
use App\Services\ConsumptionTrackingService;
use App\Services\ReconciliationService;
use App\Models\Medication;
use App\Models\StoreLocation;
use App\Models\StockMovement;
use App\Models\MedicationConsumption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    protected $stockService;
    protected $consumptionService;
    protected $reconciliationService;

    public function __construct(
        StockManagementService $stockService,
        ConsumptionTrackingService $consumptionService,
        ReconciliationService $reconciliationService
    ) {
        $this->stockService = $stockService;
        $this->consumptionService = $consumptionService;
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Display reporting dashboard
     */
    public function index()
    {
        $dashboardMetrics = $this->getDashboardMetrics();
        
        return view('reports.index', compact('dashboardMetrics'));
    }

    /**
     * Stock level reports
     */
    public function stockLevelReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'nullable|exists:store_locations,id',
            'category' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $filters = $request->only(['location_id', 'category', 'status', 'stock_status']);
        $stockLevels = $this->generateStockLevelReport($filters);
        
        $locations = StoreLocation::where('status', 'active')->get();
        $categories = Medication::distinct()->pluck('category')->filter();

        return view('reports.stock-levels', compact(
            'stockLevels',
            'locations',
            'categories',
            'filters'
        ));
    }

    /**
     * Consumption analytics report
     */
    public function consumptionReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'medication_id' => 'nullable|exists:medications,id',
            'location_id' => 'nullable|exists:store_locations,id',
            'consumption_type' => 'nullable|in:prescription,investigation,procedure'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $filters = $request->only([
            'start_date', 'end_date', 'medication_id', 
            'location_id', 'consumption_type'
        ]);

        $consumptionData = $this->generateConsumptionReport($filters);
        
        $medications = Medication::where('status', 'active')->get();
        $locations = StoreLocation::where('status', 'active')->get();

        return view('reports.consumption', compact(
            'consumptionData',
            'medications',
            'locations',
            'filters'
        ));
    }

    /**
     * Stock movement history report
     */
    public function movementReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'medication_id' => 'nullable|exists:medications,id',
            'location_id' => 'nullable|exists:store_locations,id',
            'movement_type' => 'nullable|in:inward,outward,transfer,adjustment'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $filters = $request->only([
            'start_date', 'end_date', 'medication_id',
            'location_id', 'movement_type'
        ]);

        $movementData = $this->generateMovementReport($filters);
        
        $medications = Medication::where('status', 'active')->get();
        $locations = StoreLocation::where('status', 'active')->get();

        return view('reports.movements', compact(
            'movementData',
            'medications',
            'locations',
            'filters'
        ));
    }

    /**
     * Expiry tracking report
     */
    public function expiryReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'months_ahead' => 'nullable|integer|min:1|max:24',
            'location_id' => 'nullable|exists:store_locations,id',
            'include_expired' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $monthsAhead = $request->input('months_ahead', 6);
        $locationId = $request->input('location_id');
        $includeExpired = $request->boolean('include_expired');

        $expiryData = $this->generateExpiryReport($monthsAhead, $locationId, $includeExpired);
        
        $locations = StoreLocation::where('status', 'active')->get();

        return view('reports.expiry', compact(
            'expiryData',
            'locations',
            'monthsAhead',
            'locationId',
            'includeExpired'
        ));
    }

    /**
     * ABC analysis report
     */
    public function abcAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'analysis_period' => 'required|in:3,6,12',
            'value_basis' => 'required|in:consumption_value,consumption_quantity'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $analysisPeriod = $request->input('analysis_period');
        $valueBasis = $request->input('value_basis');

        $abcData = $this->generateABCAnalysis($analysisPeriod, $valueBasis);

        return view('reports.abc-analysis', compact(
            'abcData',
            'analysisPeriod',
            'valueBasis'
        ));
    }

    /**
     * Custom report builder
     */
    public function customReport(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'report_name' => 'required|string|max:255',
                'report_type' => 'required|in:stock,consumption,movement,expiry',
                'date_range' => 'required|in:7,30,90,365,custom',
                'start_date' => 'required_if:date_range,custom|nullable|date',
                'end_date' => 'required_if:date_range,custom|nullable|date|after_or_equal:start_date',
                'filters' => 'nullable|array',
                'metrics' => 'required|array|min:1'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $reportData = $this->generateCustomReport($request->all());

            return view('reports.custom-result', compact('reportData'));
        }

        $medications = Medication::where('status', 'active')->get();
        $locations = StoreLocation::where('status', 'active')->get();

        return view('reports.custom-builder', compact('medications', 'locations'));
    }

    /**
     * Export report data
     */
    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:stock_levels,consumption,movements,expiry,abc,custom',
            'format' => 'required|in:pdf,excel,csv',
            'filters' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $reportData = $this->generateReportForExport($request->all());
            $filename = $request->report_type . '-report-' . now()->format('Y-m-d');

            switch ($request->format) {
                case 'pdf':
                    $pdf = \PDF::loadView('reports.exports.' . $request->report_type, $reportData);
                    return $pdf->download($filename . '.pdf');
                    
                case 'excel':
                    // Excel export implementation would go here
                    return back()->with('info', 'Excel export functionality to be implemented.');
                    
                case 'csv':
                    return $this->exportToCSV($reportData, $filename);
                    
                default:
                    return back()->with('error', 'Invalid export format.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating export: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics()
    {
        $totalMedications = Medication::where('status', 'active')->count();
        $lowStockItems = $this->stockService->getLowStockAlerts();
        $expiringItems = $this->stockService->getExpiryAlerts(30); // 30 days
        
        $monthlyConsumption = MedicationConsumption::where('consumption_date', '>=', now()->subMonth())
            ->sum('quantity_consumed');
        
        $recentMovements = StockMovement::where('movement_date', '>=', now()->subDays(7))
            ->count();

        return [
            'total_medications' => $totalMedications,
            'low_stock_count' => count($lowStockItems),
            'expiring_soon_count' => count($expiringItems),
            'monthly_consumption' => $monthlyConsumption,
            'weekly_movements' => $recentMovements,
            'last_updated' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate stock level report data
     */
    private function generateStockLevelReport($filters)
    {
        $query = Medication::with(['locationStocks.location'])
            ->where('status', $filters['status'] ?? 'active');

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $medications = $query->get()->map(function ($medication) use ($filters) {
            $locationStocks = $medication->locationStocks;

            if (!empty($filters['location_id'])) {
                $locationStocks = $locationStocks->where('location_id', $filters['location_id']);
            }

            $totalStock = $locationStocks->sum('available_quantity');
            $stockStatus = $this->determineStockStatus($medication, $totalStock);

            // Filter by stock status if specified
            if (!empty($filters['stock_status']) && $stockStatus !== $filters['stock_status']) {
                return null;
            }

            return [
                'id' => $medication->id,
                'name' => $medication->name,
                'generic_name' => $medication->generic_name,
                'category' => $medication->category,
                'total_stock' => $totalStock,
                'reorder_level' => $medication->reorder_level,
                'stock_status' => $stockStatus,
                'location_stocks' => $locationStocks->map(function ($stock) {
                    return [
                        'location_name' => $stock->location->name,
                        'available_quantity' => $stock->available_quantity,
                        'reserved_quantity' => $stock->reserved_quantity
                    ];
                })
            ];
        })->filter();

        return $medications;
    }

    /**
     * Generate consumption report data
     */
    private function generateConsumptionReport($filters)
    {
        $query = MedicationConsumption::with(['medication', 'location'])
            ->whereBetween('consumption_date', [$filters['start_date'], $filters['end_date']]);

        if (!empty($filters['medication_id'])) {
            $query->where('medication_id', $filters['medication_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['consumption_type'])) {
            $query->where('consumption_type', $filters['consumption_type']);
        }

        $consumptions = $query->get();

        // Group by medication for summary
        $periodDays = max(1, now()->parse($filters['start_date'])->diffInDays($filters['end_date']));
        $summary = $consumptions->groupBy('medication_id')->map(function ($group) use ($periodDays) {
            $first = $group->first();
            return [
                'medication_name' => $first->medication->name,
                'total_consumed' => $group->sum('quantity_consumed'),
                'total_value' => $group->sum(function ($item) {
                    return $item->quantity_consumed * ($item->unit_cost ?? 0);
                }),
                'consumption_count' => $group->count(),
                'avg_daily_consumption' => $group->sum('quantity_consumed') / $periodDays
            ];
        });

        return [
            'summary' => $summary,
            'details' => $consumptions,
            'period' => [
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date']
            ]
        ];
    }

    /**
     * Determine stock status based on quantity and reorder level
     */
    private function determineStockStatus($medication, $totalStock)
    {
        if ($totalStock <= 0) {
            return 'out_of_stock';
        } elseif ($totalStock <= $medication->reorder_level) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Generate movement report (simplified version)
     */
    private function generateMovementReport($filters)
    {
        $query = StockMovement::with(['medication', 'fromLocation', 'toLocation'])
            ->whereBetween('movement_date', [$filters['start_date'], $filters['end_date']]);

        if (!empty($filters['medication_id'])) {
            $query->where('medication_id', $filters['medication_id']);
        }

        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        return $query->orderBy('movement_date', 'desc')->get();
    }

    /**
     * Generate expiry report (simplified version)
     */
    private function generateExpiryReport($monthsAhead, $locationId, $includeExpired)
    {
        // This would implement expiry tracking logic
        // For now, returning placeholder structure
        return [
            'expiring_soon' => [],
            'expired' => [],
            'months_ahead' => $monthsAhead
        ];
    }

    /**
     * Generate ABC analysis (simplified version)
     */
    private function generateABCAnalysis($analysisPeriod, $valueBasis)
    {
        // ABC analysis implementation would go here
        return [
            'a_class' => [],
            'b_class' => [],
            'c_class' => [],
            'analysis_period' => $analysisPeriod,
            'value_basis' => $valueBasis
        ];
    }

    /**
     * Generate custom report (simplified version)
     */
    private function generateCustomReport($reportConfig)
    {
        // Custom report generation logic would go here
        return [
            'config' => $reportConfig,
            'data' => [],
            'generated_at' => now()
        ];
    }

    /**
     * Generate report data for export
     */
    private function generateReportForExport($exportConfig)
    {
        // Export data generation logic would go here
        return [
            'config' => $exportConfig,
            'data' => [],
            'generated_at' => now()
        ];
    }

    /**
     * Export data to CSV
     */
    private function exportToCSV($data, $filename)
    {
        // CSV export implementation would go here
        return response()->streamDownload(function () use ($data) {
            echo "CSV export functionality to be implemented\n";
        }, $filename . '.csv');
    }
}
