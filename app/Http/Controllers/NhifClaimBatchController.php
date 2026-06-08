<?php

namespace App\Http\Controllers;

use App\Models\NhifClaimBatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NhifClaimBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $batches = NhifClaimBatch::withCount('claims')
            ->orderBy('claim_year', 'desc')
            ->orderBy('claim_month', 'desc')
            ->paginate(12);

        return view('nhif.claim-batches.index', compact('batches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(NhifClaimBatch $batch, Request $request)
    {   
        // Check if the request is for HTML and return claims data as JSON
        if ($request->wantsJson()) {
            $claims = $batch->claims()->with('patient', 'patientVisit', 'claimDiseases', 'claimItems')->get();
            return response()->json(
                [
                    'success' => true,
                    'batch' => $batch,
                    'html' => view('nhif.claim-batches.modal', compact('claims'))->render()
                ]
            );
        }

        // Return list of claims in the batch
        $claims = $batch->claims()->with('patient', 'patientVisit', 'claimDiseases', 'claimItems')->get();

        return view('nhif.claim-batches.show', compact('batch', 'claims'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NhifClaimBatch $nhifClaimBatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NhifClaimBatch $nhifClaimBatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NhifClaimBatch $batch)
    {
        $claims = $batch->claims()->with('claimDiseases', 'claimItems')->get();

        $nonDraft = $claims->where('claim_status', '!=', 'draft');
        if ($nonDraft->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete: {$nonDraft->count()} claim(s) in this batch are not in draft status.",
            ], 422);
        }

        DB::transaction(function () use ($batch, $claims) {
            foreach ($claims as $claim) {
                $claim->claimDiseases()->delete();
                $claim->claimItems()->delete();
                $claim->delete();
            }
            $batch->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Batch {$batch->claim_no} and its {$claims->count()} draft claim(s) were deleted.",
        ]);
    }
}
