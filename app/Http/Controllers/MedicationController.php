<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\MedicationFormulation;
use App\Models\MedicationUnit;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MedicationController extends Controller
{
    /**
     * Display a listing of medications.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Medication::query();

            // Category filter
            if ($request->has('category_id') && $request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            // Stock status filter
            if ($request->filled('stock_status')) {
                switch ($request->stock_status) {
                    case 'low_stock':
                        $query->whereRaw('stock_quantity <= reorder_level');
                        break;
                    case 'out_of_stock':
                        $query->where('stock_quantity', 0);
                        break;
                }
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = trim($request->search['value']);
                        $query->where(function($q) use ($search) {
                            $q->where('generic_name', 'like', '%' . $search . '%')
                              ->orWhere('brand_name', 'like', '%' . $search . '%')
                              ->orWhere('strength', 'like', '%' . $search . '%');
                        });
                    }
                })
                ->addColumn('generic_display', function ($medication) {
                    $html = '<strong>' . e($medication->generic_name) . '</strong>';
                    if ($medication->formulation_id) {
                        $formulation = \App\Models\MedicationFormulation::find($medication->formulation_id);
                        if ($formulation) {
                            $html .= '<br><small class="text-muted">' . e($formulation->description) . '</small>';
                        }
                    }
                    return $html;
                })
                ->addColumn('dispensing_unit_display', function ($medication) {
                    if ($medication->dispensing_unit_id) {
                        $unit = \App\Models\MedicationUnit::find($medication->dispensing_unit_id);
                        if ($unit) {
                            return '<span class="badge bg-secondary">' . e($unit->unit_code) . '</span>' .
                                   '<small class="text-muted d-block">' . e($unit->unit_name) . '</small>';
                        }
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('category_display', function ($medication) {
                    if ($medication->category_id) {
                        $category = \App\Models\StoreCategory::find($medication->category_id);
                        if ($category) {
                            return e($category->description);
                        }
                    }
                    return '<span class="text-muted">No Category</span>';
                })
                ->addColumn('stock_display', function ($medication) {
                    $status = 'In Stock';
                    if ($medication->stock_quantity == 0) {
                        $status = 'Out of Stock';
                    } elseif ($medication->stock_quantity <= $medication->reorder_level) {
                        $status = 'Low Stock';
                    }
                    return number_format($medication->stock_quantity) . '<br><small class="text-muted">' . $status . '</small>';
                })
                ->addColumn('status', function ($medication) {
                    if ($medication->is_active) {
                        return '<span class="badge bg-success text-black">Active</span>';
                    }
                    return '<span class="badge bg-danger text-black">Inactive</span>';
                })
                ->addColumn('actions', function ($medication) {
                    return view('medications._actions', compact('medication'))->render();
                })
                ->rawColumns(['generic_display', 'dispensing_unit_display', 'category_display', 'stock_display', 'status', 'actions'])
                ->make(true);
        }

        $categories = StoreCategory::orderBy('description')->get();

        return view('medications.index', compact('categories'));
    }

    /**
     * Show the form for creating a new medication.
     */
    public function create()
    {
        $categories = StoreCategory::orderBy('description')->get();
        $formulations = MedicationFormulation::active()->orderBy('description')->get();
        $dispensingUnits = MedicationUnit::active()->get();
        
        return view('medications.create', compact('categories', 'formulations', 'dispensingUnits'));
    }

    /**
     * Store a newly created medication in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'formulation_id' => 'nullable|exists:medication_formulations,id',
            'dispensing_unit_id' => 'nullable|exists:medication_units,id',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:store_categories,id',
            'reorder_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'storage_conditions' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            // Set default stock quantity to 0 since it will be managed through GRN
            $validated['stock_quantity'] = 0;
            
            Medication::create($validated);
            
            DB::commit();
            
            return redirect()->route('medications.index')
                ->with('success', 'Medication created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating medication: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified medication.
     */
    public function show(Medication $medication)
    {
        $medication->load(['storeCategory', 'formulation', 'dispensingUnit', 'prescriptions', 'ledgerEntries']);
        
        return view('medications.show', compact('medication'));
    }

    /**
     * Show the form for editing the specified medication.
     */
    public function edit(Medication $medication)
    {
        $categories = StoreCategory::orderBy('description')->get();
        $formulations = MedicationFormulation::active()->orderBy('description')->get();
        $dispensingUnits = MedicationUnit::active()->get();
        
        return view('medications.edit', compact('medication', 'categories', 'formulations', 'dispensingUnits'));
    }

    /**
     * Update the specified medication in storage.
     */
    public function update(Request $request, Medication $medication)
    {
        $validated = $request->validate([
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'formulation_id' => 'nullable|exists:medication_formulations,id',
            'dispensing_unit_id' => 'nullable|exists:medication_units,id',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:store_categories,id',
            'reorder_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'storage_conditions' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        try {
            DB::beginTransaction();
            
            $medication->update($validated);
            
            DB::commit();
            
            return redirect()->route('medications.index')
                ->with('success', 'Medication updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating medication: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified medication from storage.
     */
    public function destroy(Medication $medication)
    {
        try {
            DB::beginTransaction();
            
            $medication->delete();
            
            DB::commit();
            
            return redirect()->route('medications.index')
                ->with('success', 'Medication deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting medication: ' . $e->getMessage());
        }
    }

    /**
     * Toggle medication active status.
     */
    public function toggleStatus(Medication $medication)
    {
        try {
            $medication->update(['is_active' => !$medication->is_active]);
            
            $status = $medication->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', "Medication {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating medication status: ' . $e->getMessage());
        }
    }

    /**
     * Get medications for API requests.
     */
    public function apiList(Request $request)
    {
        $query = Medication::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('generic_name', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        $medications = $query->take(10)->get();

        return response()->json($medications);
    }

    /**
     * Get low stock medications.
     */
    public function lowStock()
    {
        $medications = Medication::lowStock()
            ->with(['storeCategory'])
            ->orderBy('generic_name')
            ->get();

        return view('medications.low_stock', compact('medications'));
    }

    /**
     * Get expired medications.
     */
    public function expired()
    {
        $medications = Medication::expired()
            ->with(['storeCategory', 'ledgerEntries'])
            ->orderBy('generic_name')
            ->get();

        return view('medications.expired', compact('medications'));
    }

    /**
     * Get medications expiring soon.
     */
    public function expiringSoon()
    {
        $medications = Medication::expiringSoon()
            ->with(['storeCategory', 'ledgerEntries'])
            ->orderBy('generic_name')
            ->get();

        return view('medications.expiring_soon', compact('medications'));
    }

    /**
     * API endpoint to get stock information for a specific item
     */
    public function apiStock(Request $request, Medication $medication)
    {
        $stockData = [
            'id' => $medication->id,
            'name' => $medication->generic_name ?: $medication->brand_name,
            'category' => $medication->storeCategory->description ?? null,
            'current_stock' => $medication->stock_quantity,
            'reorder_level' => $medication->reorder_level,
            'maximum_stock_level' => $medication->maximum_stock_level,
            'stock_status' => $medication->stock_status,
            'is_low_stock' => $medication->is_low_stock,
            'is_in_stock' => $medication->is_in_stock,
        ];

        // Get batch information from ledger entries
        $batches = $medication->ledgerEntries()->where('status', 'active')->get();
        if ($batches->count() > 0) {
            $stockData['batches'] = $batches->map(function ($batch) {
                return [
                    'batch_number' => $batch->batch_number,
                    'quantity' => $batch->quantity_received,
                    'expiry_date' => $batch->expiry_date,
                    'unit_cost' => $batch->unit_cost,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $stockData
        ]);
    }

    /**
     * API endpoint to get batch information for a specific item
     */
    public function apiBatches(Request $request, Medication $medication)
    {
        $batches = $medication->ledgerEntries()
            ->where('status', 'active')
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity_current' => $batch->quantity_received,
                    'expiry_date' => $batch->expiry_date,
                    'unit_cost' => $batch->unit_cost,
                    'is_expired' => $batch->is_expired,
                    'is_expiring_soon' => $batch->is_expiring_soon,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $batches
        ]);
    }

    /**
     * API endpoint to check if items need reordering
     */
    public function apiCheckReorder(Request $request)
    {
        $lowStockItems = Medication::lowStock()
            ->active()
            ->with(['storeCategory'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->generic_name ?: $item->brand_name,
                    'category' => $item->storeCategory->description ?? 'N/A',
                    'current_stock' => $item->stock_quantity,
                    'reorder_level' => $item->reorder_level,
                    'recommended_order_quantity' => max(
                        $item->maximum_stock_level - $item->stock_quantity,
                        $item->reorder_level
                    ),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $lowStockItems,
            'count' => $lowStockItems->count()
        ]);
    }

    /**
     * Search medications for Select2 AJAX dropdown
     */
    public function search(Request $request)
    {
        $term = $request->input('q', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = Medication::query()
            ->orderBy('generic_name');

        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('generic_name', 'like', '%' . $term . '%')
                  ->orWhere('brand_name', 'like', '%' . $term . '%')
                  ->orWhere('strength', 'like', '%' . $term . '%');
            });
        }

        $total = $query->count();
        $medications = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $medications->map(function($medication) {
            $text = $medication->generic_name;
            if ($medication->brand_name) {
                $text .= ' (' . $medication->brand_name . ')';
            }
            if ($medication->strength) {
                $text .= ' - ' . $medication->strength;
            }

            return [
                'id' => $medication->id,
                'text' => $text
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }
}
