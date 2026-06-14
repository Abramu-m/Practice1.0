<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Icd10;
use App\Models\MtuhaDiagnosis;
use Yajra\DataTables\Facades\DataTables;

class Icd10Controller extends Controller
{
    /**
     * Show the ICD-10 list with mtuha mapping options. The table itself is
     * loaded via server-side DataTables (see the $request->ajax() branch).
     */
    public function index(Request $request)
    {
        $mtuha = MtuhaDiagnosis::orderBy('description')->get();

        if ($request->ajax()) {
            $query = Icd10::with('mtuha')->orderBy('code');

            if ($request->filled('term')) {
                $query->search($request->term);
            }

            // Filter by mtuha diagnosis id (show all ICDs assigned to a given mtuha dx)
            if ($request->filled('mtuha_diagnosis')) {
                $query->where('mtuha_diagnosis', $request->mtuha_diagnosis);
            }

            return DataTables::of($query)
                ->addColumn('mtuha_display', function ($item) {
                    return $item->mtuha
                        ? e($item->mtuha_name)
                        : '<em class="text-muted">(unassigned)</em>';
                })
                ->addColumn('actions', function ($item) use ($mtuha) {
                    return view('icd10.partials.assign-form', [
                        'item' => $item,
                        'mtuha' => $mtuha,
                    ])->render();
                })
                ->rawColumns(['mtuha_display', 'actions'])
                ->make(true);
        }

        return view('icd10.index', compact('mtuha'));
    }

    /**
     * Update the mtuha mapping for a given ICD-10 code
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'mtuha_diagnosis' => 'nullable|exists:mtuha_diagnoses,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $icd = Icd10::findOrFail($id);
        $icd->fill($data);
        $icd->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'ICD-10 mapping updated.']);
        }

        return back()->with('success', 'ICD-10 mapping updated.');
    }

    /**
     * Simple JSON search endpoint for AJAX/autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        $type = $request->get('type', 'code');
        $limit = (int) $request->get('limit', 10);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $icd10Query = Icd10::query();

        if ($type === 'code') {
            // match code prefix OR description contains term so users can search by either
            $icd10Query->where(function($q) use ($query) {
                $q->where('code', 'LIKE', $query . '%')
                  ->orWhere('description', 'LIKE', '%' . $query . '%');
            });
        } else {
            $icd10Query->where('description', 'LIKE', '%' . $query . '%');
        }

        $results = $icd10Query->orderBy('code')->take($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => $results->count()
        ]);
    }

    /**
     * AJAX search for Mtuha diagnoses (used by Select2)
     */
    public function mtuhaSearch(Request $request)
    {
        $query = $request->get('query', '');
        $limit = (int) $request->get('limit', 20);

        $mtuhaQuery = MtuhaDiagnosis::query();
        if (!empty($query)) {
            $mtuhaQuery->where('description', 'LIKE', '%' . $query . '%');
        }

        $results = $mtuhaQuery->orderBy('description')->take($limit)->get(['id', 'description']);

        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => $results->count()
        ]);
    }

    /**
     * Return a single mtuha diagnosis by id (used for safe prefill)
     */
    public function mtuhaGet($id)
    {
        $mtuha = MtuhaDiagnosis::find($id);
        if (!$mtuha) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $mtuha]);
    }
}
