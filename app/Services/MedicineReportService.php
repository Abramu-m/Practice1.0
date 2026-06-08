<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MedicineReportService extends BaseReportService
{
    /**
     * Build medicines monthly consumption report
     */
    public function buildReport()
    {
        $baseData = $this->getBaseReportData();

        // Get prescription dispensals in date range
        $prescriptions = DB::table('prescriptions as pr')
            ->join('consultations as c', 'c.id', '=', 'pr.consultation_id')
            ->join('patient_visits as pv', 'pv.id', '=', 'c.visit_id')
            ->join('medications as m', 'm.id', '=', 'pr.medication_id')
            ->leftJoin('store_categories as sc', 'sc.id', '=', 'm.category_id')
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereNotNull('pr.dispensed_at')
            ->select(
                'm.id',
                DB::raw("COALESCE(m.brand_name, m.generic_name) as name"),
                DB::raw("COALESCE(sc.description, 'Uncategorized') as category"),
                DB::raw('SUM(pr.quantity_dispensed) as quantity_dispensed')
            )
            ->groupBy('m.id', 'm.brand_name', 'm.generic_name', 'sc.description')
            ->orderBy('quantity_dispensed', 'DESC')
            ->get();

        // Get investigation consumables in date range
        $investigations = DB::table('investigations as inv')
            ->join('patient_visits as pv', 'pv.id', '=', 'inv.visit_id')
            ->join('medical_services as ms', 'ms.id', '=', 'inv.medical_service_id')
            ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
            ->whereNull('inv.cancelled_at')
            ->select(
                'ms.name as service_name',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('ms.name')
            ->orderBy('count', 'DESC')
            ->get();

        // Get tracer medicines (essential medicines subset)
        $tracerMedicines = $this->getTracerMedicines($prescriptions);

        return array_merge($baseData, [
            'report_type' => 'medicines_monthly',
            'title' => 'Medicines Monthly Consumption Report',
            'medications' => [
                'total_dispensed' => $prescriptions->sum('quantity_dispensed'),
                'unique_medications' => $prescriptions->count(),
                'by_medication' => $prescriptions->map(function ($med) {
                    return [
                        'name' => $med->name,
                        'category' => $med->category,
                        'quantity_dispensed' => $med->quantity_dispensed,
                    ];
                })->toArray(),
                'by_category' => $this->groupMedicationsByCategory($prescriptions),
            ],
            'investigations' => [
                'total_conducted' => $investigations->sum('count'),
                'by_type' => $investigations->map(function ($inv) {
                    return [
                        'name' => $inv->service_name,
                        'count' => $inv->count,
                    ];
                })->toArray(),
            ],
            'tracer_medicines' => [
                'total' => $tracerMedicines['total'],
                'items' => $tracerMedicines['items'],
            ],
        ]);
    }

    /**
     * Get tracer medicines dispensed during the report period (used by monthly report)
     */
    private function getTracerMedicines($allMedicines = null)
    {
        $tracerIds = \App\Models\Medication::where('is_tracer', true)->pluck('id')->toArray();

        if (empty($tracerIds)) {
            return ['total' => 0, 'items' => []];
        }

        if (!$allMedicines) {
            $allMedicines = DB::table('prescriptions as pr')
                ->join('consultations as c', 'c.id', '=', 'pr.consultation_id')
                ->join('patient_visits as pv', 'pv.id', '=', 'c.visit_id')
                ->join('medications as m', 'm.id', '=', 'pr.medication_id')
                ->whereBetween('pv.visit_date', [$this->startDate, $this->endDate])
                ->whereNotNull('pr.dispensed_at')
                ->select(
                    'm.id',
                    DB::raw("COALESCE(m.brand_name, m.generic_name) as name"),
                    DB::raw('SUM(pr.quantity_dispensed) as quantity_dispensed')
                )
                ->groupBy('m.id', 'm.brand_name', 'm.generic_name')
                ->get();
        }

        $tracerMedicines = collect($allMedicines)->filter(fn($med) => in_array($med->id, $tracerIds));

        return [
            'total' => $tracerMedicines->sum('quantity_dispensed'),
            'items' => $tracerMedicines->map(fn($med) => [
                'name'               => $med->name,
                'quantity_dispensed' => $med->quantity_dispensed,
            ])->toArray(),
        ];
    }

    /**
     * Build tracer medicines availability report (current stock status)
     */
    public function buildTracerReport()
    {
        $baseData = $this->getBaseReportData();

        $tracers = \App\Models\Medication::where('is_tracer', true)
            ->where('is_active', true)
            ->orderBy('generic_name')
            ->get(['id', 'generic_name', 'brand_name', 'strength', 'stock_quantity']);

        $items = $tracers->map(function ($med) {
            return [
                'name'           => $med->brand_name
                    ? "{$med->generic_name} ({$med->brand_name})"
                    : $med->generic_name,
                'strength'       => $med->strength ?? '—',
                'stock_quantity' => (int) $med->stock_quantity,
                'available'      => (int) $med->stock_quantity > 0,
            ];
        })->toArray();

        $availableCount   = collect($items)->where('available', true)->count();
        $unavailableCount = count($items) - $availableCount;

        return array_merge($baseData, [
            'report_type'      => 'tracer_medicines',
            'title'            => 'Tracer Medicines Availability Report',
            'tracer_medicines' => [
                'items'             => $items,
                'total'             => count($items),
                'available_count'   => $availableCount,
                'unavailable_count' => $unavailableCount,
            ],
        ]);
    }

    /**
     * Group medications by category
     */
    private function groupMedicationsByCategory(\Illuminate\Support\Collection $medications)
    {
        return $medications->groupBy('category')
            ->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'total_quantity' => $group->sum('quantity_dispensed'),
                    'unique_items' => $group->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Build low stock medicines report
     */
    public function buildLowStockReport()
    {
        $baseData = $this->getBaseReportData();

        // Get low stock medicines from medications table
        $lowStockMedicines = DB::table('medications as m')
            ->leftJoin('store_categories as sc', 'sc.id', '=', 'm.category_id')
            ->where('m.is_active', true)
            ->whereRaw('COALESCE(m.stock_quantity, 0) <= COALESCE(m.reorder_level, 0)')
            ->select(
                'm.id',
                DB::raw("COALESCE(m.brand_name, m.generic_name) as name"),
                DB::raw("COALESCE(sc.description, 'Uncategorized') as category"),
                'm.reorder_level',
                DB::raw('COALESCE(m.stock_quantity, 0) as current_stock')
            )
            ->orderBy('m.stock_quantity', 'ASC')
            ->get();

        return array_merge($baseData, [
            'report_type' => 'low_stock_medicines',
            'title' => 'Low Stock Medicines Report',
            'total_low_stock_items' => $lowStockMedicines->count(),
            'medicines' => $lowStockMedicines->map(function ($med) {
                return [
                    'name' => $med->name,
                    'category' => $med->category,
                    'reorder_level' => $med->reorder_level,
                    'current_stock' => $med->current_stock,
                    'status' => $med->current_stock == 0 ? 'out_of_stock' : 'low_stock',
                ];
            })->toArray(),
        ]);
    }
}
