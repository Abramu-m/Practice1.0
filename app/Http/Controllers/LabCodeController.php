<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabCode;

class LabCodeController extends Controller
{
    /**
     * Show paginated lab code library (LOINC + SNOMED), filterable by system
     */
    public function index(Request $request)
    {
        $query = LabCode::orderBy('coding_system')->orderBy('code');

        if ($request->filled('term')) {
            $query->search($request->term);
        }

        if ($request->filled('system')) {
            $query->where('coding_system', $request->system);
        }

        $labCodes = $query->paginate(25)->appends($request->only('term', 'system'));

        return view('lab_codes.index', compact('labCodes', 'request'));
    }

    /**
     * Simple JSON search endpoint for AJAX/autocomplete, scoped by coding system
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        $system = $request->get('system');
        $limit = (int) $request->get('limit', 10);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
            ], 400);
        }

        if (! in_array($system, ['loinc', 'snomed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'A valid coding system (loinc or snomed) is required',
            ], 400);
        }

        $results = LabCode::active()
            ->where('coding_system', $system)
            ->where(function ($q) use ($query) {
                $q->where('code', 'LIKE', $query . '%')
                  ->orWhere('display_name', 'LIKE', '%' . $query . '%');
            })
            ->orderBy('code')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => $results->count(),
        ]);
    }
}
