<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use App\Models\StoreSupplier;
use App\Models\StoreCategory;
use App\Models\StoreRequisition;
use App\Models\GoodsReceivedNote;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        return view('store.reports.index');
    }

    /**
     * Stock summary report
     */
    public function stockSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        // Stock by location
        $locationStock = StoreLocation::active()
            ->with(['stockItems' => function($query) {
                $query->where('quantity', '>', 0);
            }])
            ->get()
            ->map(function ($location) {
                $location->stock_value = $location->stockItems->sum(function($stock) {
                    return $stock->quantity * $stock->unit_cost;
                });
                $location->item_count = $location->stockItems->count();
                return $location;
            });

                // Stock by category using unified system
        $categoryStock = StoreCategory::with(['medications'])
            ->get()
            ->map(function ($category) {
                $totalStock = 0;
                
                foreach ($category->medications as $medication) {
                    $totalStock += $medication->stock_quantity; // Use the stock_quantity field from medications table
                }
                
                $category->total_stock = $totalStock;
                $category->item_count = $category->medications->count();
                
                return $category;
            });

        // Total stock value (placeholder - StoreStockBatch removed)
        $totalStockValue = 0;

        return view('store.reports.stock-summary', compact(
            'locationStock',
            'categoryStock',
            'totalStockValue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Expiry report
     */
    public function expiryReport(Request $request)
    {
        $days = $request->get('days', 30);
        
        // Expired items (placeholder - StoreStockBatch removed)
        $expiredItems = collect();

        // Expiring items (placeholder - StoreStockBatch removed)
        $expiringItems = collect();

        $totalExpiredValue = $expiredItems->sum('expired_value');
        $totalExpiringValue = $expiringItems->sum('expiring_value');

        return view('store.reports.expiry-report', compact(
            'expiredItems',
            'expiringItems',
            'totalExpiredValue',
            'totalExpiringValue',
            'days'
        ));
    }

    /**
     * Consumption report
     */
    public function consumptionReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        // Top consuming items (placeholder - StoreStockBatch removed)
        $topConsumingItems = collect();

        // Consumption by location
        $locationConsumption = StoreLocation::active()
            ->with(['requisitions' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($location) {
                $location->total_requisitions = $location->requisitions->count();
                $location->total_cost = $location->requisitions->sum('total_cost');
                return $location;
            });

        return view('store.reports.consumption-report', compact(
            'topConsumingItems',
            'locationConsumption',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Below reorder level report
     */
    public function belowReorderLevel()
    {
        $lowStockMedications = Medication::active()
            ->lowStock()
            ->with(['storeCategory'])
            ->get()
            ->map(function ($medication) {
                $medication->current_stock = $medication->stock_quantity; // Use stock_quantity from medications table
                $medication->stock_value = 0; // Placeholder since batch costing is removed
                return $medication;
            });

                // Get low stock consumables using unified medication system
        $lowStockConsumables = Medication::whereHas('storeCategory', function ($query) {
                $query->where('description', 'Consumables');
            })
            ->where('status', 'active')
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->orderBy('current_stock', 'asc')
            ->take(10)
            ->get();

        return view('store.reports.below-reorder-level', compact(
            'lowStockMedications',
            'lowStockConsumables'
        ));
    }

    /**
     * Supplier performance report
     */
    public function supplierPerformance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        $supplierPerformance = StoreSupplier::active()
            ->with(['goodsReceivedNotes' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($supplier) {
                $grns = $supplier->goodsReceivedNotes;
                $supplier->total_grns = $grns->count();
                $supplier->total_amount = $grns->sum('total_amount');
                $supplier->average_amount = $supplier->total_grns > 0 ? $supplier->total_amount / $supplier->total_grns : 0;
                $supplier->pending_amount = $supplier->getPendingAmount();
                
                // Payment performance
                $paidOnTime = $grns->filter(function($grn) {
                    return $grn->status === 'paid' && 
                           $grn->updated_at->diffInDays($grn->created_at) <= $grn->supplier->payment_terms;
                })->count();
                
                $supplier->payment_performance = $supplier->total_grns > 0 ? ($paidOnTime / $supplier->total_grns) * 100 : 0;
                
                return $supplier;
            })
            ->sortByDesc('total_amount');

        return view('store.reports.supplier-performance', compact(
            'supplierPerformance',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Monthly consumables report
     */
    public function monthlyConsumables(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $monthlyConsumables = Medication::whereHas('storeCategory', function ($query) {
                $query->where('description', 'Consumables');
            })
            ->where('is_active', true) // Fixed status field name
            ->with(['storeCategory'])
            ->get()
            ->map(function ($consumable) use ($month, $year) {
                // Placeholder for monthly consumption - would need to be calculated from stock movements
                $monthlyConsumption = 0; // StoreStockBatch removed, would calculate from movements
                
                $consumable->monthly_consumption = $monthlyConsumption;
                $consumable->current_stock = $consumable->stock_quantity; // Use stock_quantity from medications table
                
                return $consumable;
            })
            ->filter(function ($consumable) {
                return $consumable->monthly_consumption > 0;
            })
            ->sortByDesc('monthly_consumption');

        return view('store.reports.monthly-consumables', compact(
            'monthlyConsumables',
            'month',
            'year'
        ));
    }

    /**
     * Stock valuation report
     */
    public function stockValuation(Request $request)
    {
        $asOfDate = $request->get('as_of_date', Carbon::now()->toDateString());
        
        $stockValuation = collect(); // Placeholder - StoreStockBatch removed

        $totalValuation = $stockValuation->sum('total_value');

        return view('store.reports.stock-valuation', compact(
            'stockValuation',
            'totalValuation',
            'asOfDate'
        ));
    }

    /**
     * Generate PDF report
     */
    public function generatePDF(Request $request)
    {
        $reportType = $request->get('report_type');
        
        // This would integrate with a PDF generation library like DomPDF
        // For now, just return a message
        return response()->json([
            'message' => 'PDF generation for ' . $reportType . ' report will be implemented',
            'report_type' => $reportType
        ]);
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        $reportType = $request->get('report_type');
        
        // This would integrate with an Excel export library like Laravel Excel
        // For now, just return a message
        return response()->json([
            'message' => 'Excel export for ' . $reportType . ' report will be implemented',
            'report_type' => $reportType
        ]);
    }
}
