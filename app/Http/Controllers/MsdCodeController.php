<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MsdCode;

class MsdCodeController extends Controller
{
    /**
     * Show paginated MSD code library
     */
    public function index(Request $request)
    {
        $query = MsdCode::orderBy('code');

        if ($request->filled('term')) {
            $query->search($request->term);
        }

        $msdCodes = $query->paginate(25)->appends($request->only('term'));

        return view('msd_codes.index', compact('msdCodes', 'request'));
    }

    /**
     * Simple JSON search endpoint for AJAX/autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        $limit = (int) $request->get('limit', 10);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
            ], 400);
        }

        $results = MsdCode::active()
            ->where(function ($q) use ($query) {
                $q->where('code', 'LIKE', $query . '%')
                  ->orWhere('name', 'LIKE', '%' . $query . '%');
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
