<?php

namespace App\Http\Controllers;

use App\Models\MedicationPricing;
use App\Models\Medication;
use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MedicationPricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
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

            return DataTables::of($query)
                ->addColumn('medication_display', function ($price) {
                    $html = '<strong>' . e($price->medication->generic_name) . '</strong>';
                    if ($price->medication->brand_name) {
                        $html .= '<br><small class="text-muted">' . e($price->medication->brand_name) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('category_display', function ($price) {
                    return '<span class="badge bg-info text-black">' . e($price->patientCategory->description) . '</span>';
                })
                ->addColumn('selling_price_display', function ($price) {
                    return 'TSh ' . number_format($price->selling_price, 2);
                })
                ->addColumn('markup_display', function ($price) {
                    if ($price->markup_percentage) {
                        return number_format($price->markup_percentage, 1) . '%';
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('discount_display', function ($price) {
                    if ($price->discount_percentage) {
                        return '<span class="text-danger">' . number_format($price->discount_percentage, 1) . '%</span>';
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('effective_period', function ($price) {
                    return '<small><strong>From:</strong> ' . $price->effective_from->format('M d, Y') . '<br>' .
                           '<strong>To:</strong> ' . ($price->effective_to ? $price->effective_to->format('M d, Y') : 'Ongoing') . '</small>';
                })
                ->addColumn('status_display', function ($price) {
                    $html = '<span class="text-black badge bg-' . ($price->is_active ? 'success' : 'danger') . '">';
                    $html .= $price->is_active ? 'Active' : 'Inactive';
                    $html .= '</span>';
                    if ($price->isCurrent()) {
                        $html .= ' <span class="badge bg-primary text-black">Current</span>';
                    }
                    return $html;
                })
                ->addColumn('actions', function ($price) {
                    $html = '<div class="btn-group">';
                    $html .= '<a href="' . route('medication-pricing.show', $price->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $html .= '<a href="' . route('medication-pricing.edit', $price->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>';
                    $html .= '<form action="' . route('medication-pricing.destroy', $price->id) . '" method="POST" style="display: inline;" onsubmit="return confirm(\'Are you sure?\')">';
                    $html .= csrf_field() . method_field('DELETE');
                    $html .= '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                    $html .= '</form></div>';
                    return $html;
                })
                ->rawColumns(['medication_display', 'category_display', 'markup_display', 'discount_display', 'effective_period', 'status_display', 'actions'])
                ->make(true);
        }

        // Only load selected medication if filtered
        $selectedMedication = null;
        if ($request->filled('medication_id')) {
            $selectedMedication = Medication::find($request->medication_id);
        }

        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medication-pricing.index', compact('selectedMedication', 'patientCategories'));
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
