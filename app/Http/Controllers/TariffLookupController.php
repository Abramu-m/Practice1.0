<?php

namespace App\Http\Controllers;

use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TariffLookupController extends Controller
{
    /**
     * Search tariff items for a given patient category.
     * Returns results for Select2 AJAX.
     */
    public function search(Request $request)
    {
        $request->validate([
            'patient_category_id' => 'required|exists:patient_categories,id',
            'q'                   => 'nullable|string|max:100',
        ]);

        $category = PatientCategory::find($request->patient_category_id);
        $tariffsTable = $category->tariffs_table;

        if (!$tariffsTable || !Schema::hasTable($tariffsTable)) {
            return response()->json(['results' => [], 'pagination' => ['more' => false]]);
        }

        $search = trim($request->q ?? '');
        $page   = max(1, (int) $request->get('page', 1));
        $perPage = 20;

        $query = DB::table($tariffsTable)
            ->select('item_code', 'item_name', 'unit_price');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', '%' . $search . '%')
                  ->orWhere('item_code', 'like', '%' . $search . '%');
            });
        }

        $total   = $query->count();
        $records = (clone $query)
            ->orderBy('item_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $results = $records->map(fn($r) => [
            'id'         => $r->item_code,
            'text'       => $r->item_name . ' [' . $r->item_code . ']',
            'item_name'  => $r->item_name,
            'unit_price' => $r->unit_price,
        ]);

        return response()->json([
            'results'    => $results,
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }

    /**
     * Get a single tariff item by item_code for a patient category.
     * Used to pre-populate Select2 on edit page.
     */
    public function item(Request $request)
    {
        $request->validate([
            'patient_category_id' => 'required|exists:patient_categories,id',
            'item_code'           => 'required|string',
        ]);

        $category = PatientCategory::find($request->patient_category_id);
        $tariffsTable = $category->tariffs_table;

        if (!$tariffsTable || !Schema::hasTable($tariffsTable)) {
            return response()->json(null);
        }

        $record = DB::table($tariffsTable)
            ->select('item_code', 'item_name', 'unit_price')
            ->where('item_code', $request->item_code)
            ->first();

        if (!$record) {
            return response()->json(null);
        }

        return response()->json([
            'id'         => $record->item_code,
            'text'       => $record->item_name . ' [' . $record->item_code . ']',
            'item_name'  => $record->item_name,
            'unit_price' => $record->unit_price,
        ]);
    }
}
