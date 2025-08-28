<?php

namespace App\Http\Controllers;

use App\Models\MedicationPricing;
use App\Models\Medication;
use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationPricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MedicationPricing::with(['medication', 'patientCategory']);

        // Filter by medication
        if ($request->filled('medication_id')) {
            $query->where('medication_id', $request->medication_id);
        }

        // Filter by patient category
        if ($request->filled('patient_category_id')) {
            $query->where('patient_category_id', $request->patient_category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by effective status
        if ($request->filled('effective_status')) {
            $now = now()->toDateString();
            if ($request->effective_status === 'current') {
                $query->current();
            } elseif ($request->effective_status === 'future') {
                $query->where('effective_from', '>', $now);
            } elseif ($request->effective_status === 'expired') {
                $query->where('effective_to', '<', $now);
            }
        }

        $pricing = $query->latest()->paginate(20);

        $medications = Medication::orderBy('generic_name')->get();
        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medication-pricing.index', compact('pricing', 'medications', 'patientCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medications = Medication::orderBy('generic_name')->get();
        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medication-pricing.create', compact('medications', 'patientCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'selling_price' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:1000',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'required|in:0,1,true,false',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for overlapping pricing periods
        $existingPricing = MedicationPricing::where('medication_id', $request->medication_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('effective_from', '<=', $request->effective_from)
                      ->where(function ($subQ) use ($request) {
                          $subQ->whereNull('effective_to')
                               ->orWhere('effective_to', '>=', $request->effective_from);
                      });
                })->orWhere(function ($q) use ($request) {
                    if ($request->effective_to) {
                        $q->where('effective_from', '<=', $request->effective_to)
                          ->where(function ($subQ) use ($request) {
                              $subQ->whereNull('effective_to')
                                   ->orWhere('effective_to', '>=', $request->effective_to);
                          });
                    }
                });
            })
            ->exists();

        if ($existingPricing) {
            return back()->withErrors(['effective_from' => 'Pricing period overlaps with existing active pricing.'])->withInput();
        }

        MedicationPricing::create([
            'medication_id' => $request->medication_id,
            'patient_category_id' => $request->patient_category_id,
            'selling_price' => $request->selling_price,
            'markup_percentage' => $request->markup_percentage,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->notes,
        ]);

        return redirect()->route('medication-pricing.index')
                        ->with('success', 'Medication pricing created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationPricing $medicationPricing)
    {
        $medicationPricing->load(['medication', 'patientCategory']);
        
        return view('medication-pricing.show', compact('medicationPricing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicationPricing $medicationPricing)
    {
        $medications = Medication::orderBy('generic_name')->get();
        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medication-pricing.edit', compact('medicationPricing', 'medications', 'patientCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationPricing $medicationPricing)
    {
        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'selling_price' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:1000',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'required|in:0,1,true,false',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for overlapping pricing periods (excluding current record)
        $existingPricing = MedicationPricing::where('medication_id', $request->medication_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->where('id', '!=', $medicationPricing->id)
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('effective_from', '<=', $request->effective_from)
                      ->where(function ($subQ) use ($request) {
                          $subQ->whereNull('effective_to')
                               ->orWhere('effective_to', '>=', $request->effective_from);
                      });
                })->orWhere(function ($q) use ($request) {
                    if ($request->effective_to) {
                        $q->where('effective_from', '<=', $request->effective_to)
                          ->where(function ($subQ) use ($request) {
                              $subQ->whereNull('effective_to')
                                   ->orWhere('effective_to', '>=', $request->effective_to);
                          });
                    }
                });
            })
            ->exists();

        if ($existingPricing) {
            return back()->withErrors(['effective_from' => 'Pricing period overlaps with existing active pricing.'])->withInput();
        }

        $medicationPricing->update([
            'medication_id' => $request->medication_id,
            'patient_category_id' => $request->patient_category_id,
            'selling_price' => $request->selling_price,
            'markup_percentage' => $request->markup_percentage,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->notes,
        ]);

        return redirect()->route('medication-pricing.index')
                        ->with('success', 'Medication pricing updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationPricing $medicationPricing)
    {
        $medicationPricing->delete();

        return redirect()->route('medication-pricing.index')
                        ->with('success', 'Medication pricing deleted successfully.');
    }

    /**
     * Bulk update pricing for multiple medications
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'medication_ids' => 'required|array',
            'medication_ids.*' => 'exists:medications,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'markup_percentage' => 'required|numeric|min:0|max:1000',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->medication_ids as $medicationId) {
                $medication = Medication::find($medicationId);
                
                // Get the latest cost from ledger entries (for informational purposes only)
                $latestCost = $medication->ledgerEntries()
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('date_received', 'desc')
                    ->first()?->unit_cost ?? 0;

                if ($latestCost > 0) {
                    $sellingPrice = $latestCost * (1 + $request->markup_percentage / 100);
                    
                    MedicationPricing::updateOrCreate([
                        'medication_id' => $medicationId,
                        'patient_category_id' => $request->patient_category_id,
                        'effective_from' => $request->effective_from,
                    ], [
                        'selling_price' => $sellingPrice,
                        'markup_percentage' => $request->markup_percentage,
                        'discount_percentage' => 0,
                        'effective_to' => $request->effective_to,
                        'is_active' => true,
                    ]);
                }
            }
        });

        return redirect()->route('medication-pricing.index')
                        ->with('success', 'Bulk pricing updated successfully.');
    }
}
