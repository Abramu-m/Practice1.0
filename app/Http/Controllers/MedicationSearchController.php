<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;

class MedicationSearchController extends Controller
{
    /**
     * Search medications for AJAX autocomplete
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->get('query', $request->get('q', '')); // Support both 'query' and 'q' params
        $limit = (int) $request->get('limit', 30);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $medications = Medication::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where(function($q) use ($query) {
                $q->where('generic_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('brand_name', 'LIKE', '%' . $query . '%');
            })
            ->with(['formulation', 'dispensingUnit']) // Load relationships
            ->orderBy('generic_name')
            ->take($limit)
            ->get(['id', 'generic_name', 'brand_name', 'strength', 'formulation_id', 'dispensing_unit_id', 'stock_quantity']);

        // Transform data to include formulation and unit names
        $medications = $medications->map(function($med) {
            return [
                'id' => $med->id,
                'generic_name' => $med->generic_name,
                'brand_name' => $med->brand_name,
                'strength' => $med->strength,
                'formulation' => $med->formulation ? $med->formulation->name : null,
                'unit' => $med->dispensingUnit ? $med->dispensingUnit->unit_name : null,
                'stock_quantity' => $med->stock_quantity,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $medications,
            'count' => $medications->count()
        ]);
    }
}
