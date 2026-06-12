<?php

namespace App\Http\Controllers;

use App\Services\ConsumptionTrackingService;
use App\Services\StockManagementService;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
     * Display prescriptions awaiting/under dispensing
     */
    public function prescriptionsIndex(Request $request)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $patient = $request->get('patient', 'all');
        $doctor = $request->get('doctor', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Prescription::with(['patient', 'doctorInfo.user', 'medication', 'consultation']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('card_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('medication', function ($mq) use ($search) {
                      $mq->where('generic_name', 'like', "%{$search}%")
                          ->orWhere('brand_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($patient !== 'all') {
            $query->where('patient_id', $patient);
        }

        if ($doctor !== 'all') {
            $query->where('doctor_id', $doctor);
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $prescriptions = $query->orderByDesc('created_at')->paginate(15);

        $statistics = [
            'total_prescriptions' => Prescription::count(),
            'pending_prescriptions' => Prescription::pending()->count(),
            'dispensed_prescriptions' => Prescription::where('status', Prescription::STATUS_DISPENSED)->count(),
            'monthly_prescriptions' => Prescription::where('created_at', '>=', now()->startOfMonth())->count(),
            'partially_dispensed' => Prescription::whereColumn('quantity_dispensed', '<', 'quantity')
                ->where('quantity_dispensed', '>', 0)
                ->count(),
            'total_items_dispensed' => (int) Prescription::sum('quantity_dispensed'),
            'top_prescribed_medications' => Prescription::select('medication_id')
                ->selectRaw('COUNT(*) as prescription_count')
                ->selectRaw('SUM(quantity_dispensed) as total_dispensed')
                ->whereNotNull('medication_id')
                ->with('medication')
                ->groupBy('medication_id')
                ->orderByDesc('prescription_count')
                ->limit(5)
                ->get(),
        ];

        $statusOptions = Prescription::getStatusOptions();

        $patientIds = Prescription::select('patient_id')->whereNotNull('patient_id')->distinct()->pluck('patient_id');
        $recentPatients = Patient::whereIn('id', $patientIds)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        $doctorIds = Prescription::select('doctor_id')->whereNotNull('doctor_id')->distinct()->pluck('doctor_id');
        $recentDoctors = Doctor::with('user')->whereIn('doctor_id', $doctorIds)->get();

        return view('medications.consumption.prescriptions.index', compact(
            'prescriptions', 'statistics', 'search', 'status', 'statusOptions',
            'patient', 'recentPatients', 'doctor', 'recentDoctors', 'dateFrom', 'dateTo'
        ));
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

    private function getConsumptionOverview($dateFrom, $dateTo, $storeLocation)
    {
        return [
            'total_consumption' => 0,
            'total_cost' => 0,
            'unique_medications' => 0,
            'avg_daily_consumption' => 0,
            'period_days' => 0,
        ];
    }

    private function getConsumptionTrends($dateFrom, $dateTo, $storeLocation)
    {
        return collect();
    }

    private function getTopConsumedMedications($dateFrom, $dateTo, $storeLocation, $limit = 10)
    {
        return collect();
    }

    private function getConsumptionByCategory($dateFrom, $dateTo, $storeLocation)
    {
        return collect();
    }

    private function getConsumptionByLocation($dateFrom, $dateTo)
    {
        return collect();
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

    private function getCostAnalysis($dateFrom, $dateTo, $storeLocation)
    {
        return [
            'total_cost' => 0,
            'avg_cost_per_item' => 0,
            'total_items' => 0,
        ];
    }

    private function getStockTurnoverAnalysis($dateFrom, $dateTo, $storeLocation)
    {
        return collect();
    }
}
