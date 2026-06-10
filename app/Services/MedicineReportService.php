<?php

namespace App\Services;

use App\Models\MedicineDispensingReportRow;
use Illuminate\Support\Facades\DB;

class MedicineReportService extends BaseReportService
{
    /**
     * Build the Monthly Drug Dispensing Report (Taarifa ya Mwezi ya Kutolea Dawa),
     * matching the official MoH form: per-drug quantities dispensed, split by
     * patient age group (<5, 5-59, 60+).
     */
    public function buildReport()
    {
        $baseData = $this->getBaseReportData();

        $rows = MedicineDispensingReportRow::orderBy('sort_order')->get();
        $medicationIds = $rows->pluck('medication_id')->filter()->unique()->values();

        $totals = [];

        if ($medicationIds->isNotEmpty()) {
            $results = DB::table('prescriptions as pr')
                ->join('patient_visits as pv', 'pv.id', '=', 'pr.visit_id')
                ->join('patients as p', 'p.id', '=', 'pv.patient')
                ->whereIn('pr.medication_id', $medicationIds)
                ->whereNotNull('pr.dispensed_at')
                ->whereNotNull('p.date_of_birth')
                ->whereBetween('pr.dispensed_at', [$this->startDate, $this->endDate])
                ->select(
                    'pr.medication_id',
                    DB::raw('TIMESTAMPDIFF(YEAR, p.date_of_birth, pr.dispensed_at) as age_years'),
                    DB::raw('SUM(pr.quantity_dispensed) as qty')
                )
                ->groupBy('pr.medication_id', 'age_years')
                ->get();

            foreach ($results as $result) {
                $ageGroup = $result->age_years < 5 ? 'under_5' : ($result->age_years < 60 ? '5_to_59' : '60_plus');
                $totals[$result->medication_id][$ageGroup] = ($totals[$result->medication_id][$ageGroup] ?? 0) + (int) $result->qty;
            }
        }

        $dispensingRows = $rows->map(function ($row) use ($totals) {
            $medTotals = $totals[$row->medication_id] ?? null;

            $under5 = $medTotals['under_5'] ?? 0;
            $midAge = $medTotals['5_to_59'] ?? 0;
            $elder  = $medTotals['60_plus'] ?? 0;

            return [
                'row_key'        => $row->row_key,
                'row_no'         => $row->row_no,
                'row_no_rowspan' => $row->row_no_rowspan,
                'drug_label'     => $row->drug_label,
                'drug_rowspan'   => $row->drug_rowspan,
                'unit_label'     => $row->unit_label,
                'under_5'        => $row->medication_id ? $under5 : null,
                '5_to_59'        => $row->medication_id ? $midAge : null,
                '60_plus'        => $row->medication_id ? $elder : null,
                'total'          => $row->medication_id ? ($under5 + $midAge + $elder) : null,
            ];
        })->values()->toArray();

        return array_merge($baseData, [
            'report_type'     => 'medicines_monthly',
            'title'           => 'Taarifa ya Mwezi ya Kutolea Dawa',
            'dispensing_rows' => $dispensingRows,
        ]);
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
