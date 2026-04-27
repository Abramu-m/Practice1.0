<?php

namespace App\Http\Controllers;

use App\Models\MedicalServicePricing;
use App\Models\MedicalService;
use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MedicalServicePricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MedicalServicePricing::with(['medicalService.serviceCategory', 'patientCategory'])
                ->join('medical_services', 'medical_services_pricing.medical_service_id', '=', 'medical_services.id')
                ->select('medical_services_pricing.*');

            // Filter by medical service
            if ($request->filled('medical_service_id')) {
                $query->where('medical_services_pricing.medical_service_id', $request->medical_service_id);
            }

            // Filter by patient category
            if ($request->filled('patient_category_id')) {
                $query->where('medical_services_pricing.patient_category_id', $request->patient_category_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('medical_services_pricing.is_active', $request->status === 'active');
            }

            // Filter by effective status
            if ($request->filled('effective_status')) {
                $now = now()->toDateString();
                if ($request->effective_status === 'current') {
                    $query->current();
                } elseif ($request->effective_status === 'future') {
                    $query->where('medical_services_pricing.effective_from', '>', $now);
                } elseif ($request->effective_status === 'expired') {
                    $query->where('medical_services_pricing.effective_to', '<', $now);
                }
            }

            return DataTables::of($query)
                ->orderColumn('service_display', 'medical_services.name $1')
                ->addColumn('service_display', function ($price) {
                    $html = '<strong>' . e($price->medicalService->name) . '</strong>';
                    if ($price->medicalService->code) {
                        $html .= '<br><small class="text-muted">' . e($price->medicalService->code) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('category_display', function ($price) {
                    $categoryName = $price->medicalService->serviceCategory->name ?? 'N/A';
                    return '<span class="badge bg-secondary text-black">' . e($categoryName) . '</span>';
                })
                ->addColumn('patient_category_display', function ($price) {
                    return '<span class="badge bg-info text-black">' . e($price->patientCategory->description) . '</span>';
                })
                ->addColumn('price_display', function ($price) {
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
                        return number_format($price->discount_percentage, 1) . '%';
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('effective_period', function ($price) {
                    $from = $price->effective_from ? $price->effective_from->format('M j, Y') : 'Not set';
                    $to = $price->effective_to ? $price->effective_to->format('M j, Y') : '<span class="text-muted">Indefinite</span>';
                    return '<small><strong>From:</strong> ' . $from . '<br><strong>To:</strong> ' . $to . '</small>';
                })
                ->addColumn('status_display', function ($price) {
                    $status = $price->status;
                    $badgeClass = $status === 'Active' ? 'success' : 
                                ($status === 'Future' ? 'warning' : 
                                ($status === 'Expired' ? 'secondary' : 'danger'));
                    return '<span class="badge bg-' . $badgeClass . '">' . e($status) . '</span>';
                })
                ->addColumn('actions', function ($price) {
                    $viewBtn = '<a href="' . route('medical-service-pricing.show', $price) . '" class="btn btn-outline-info" title="View">' .
                               '<i class="fas fa-eye"></i></a>';
                    $editBtn = '<a href="' . route('medical-service-pricing.edit', $price) . '" class="btn btn-outline-primary" title="Edit">' .
                               '<i class="fas fa-edit"></i></a>';
                    $deleteBtn = '<form action="' . route('medical-service-pricing.destroy', $price) . '" method="POST" style="display: inline-block;" ' .
                                 'onsubmit="return confirm(\'Are you sure you want to delete this pricing?\')">' .
                                 csrf_field() . method_field('DELETE') .
                                 '<button type="submit" class="btn btn-outline-danger" title="Delete">' .
                                 '<i class="fas fa-trash"></i></button></form>';
                    
                    return '<div class="btn-group btn-group-sm">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['service_display', 'category_display', 'patient_category_display', 'markup_display', 'discount_display', 'effective_period', 'status_display', 'actions'])
                ->make(true);
        }

        $medicalServices = MedicalService::with('serviceCategory')->orderBy('name')->get();
        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medical-service-pricing.index', compact('medicalServices', 'patientCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicalServices = MedicalService::with('serviceCategory')->orderBy('name')->get();
        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medical-service-pricing.create', compact('medicalServices', 'patientCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'medical_service_id' => 'required|exists:medical_services,id',
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
        $existingPricing = MedicalServicePricing::where('medical_service_id', $request->medical_service_id)
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
            })->exists();

        if ($existingPricing) {
            return back()->withInput()
                ->withErrors(['effective_from' => 'There is already an active pricing for this service and category that overlaps with the selected period.']);
        }

        try {
            DB::beginTransaction();

            $pricing = MedicalServicePricing::create($request->all());

            DB::commit();

            return redirect()->route('medical-service-pricing.show', $pricing)
                ->with('success', 'Medical service pricing created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create medical service pricing: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalServicePricing $medicalServicePricing)
    {
        $medicalServicePricing->load(['medicalService.serviceCategory', 'patientCategory']);
        
        return view('medical-service-pricing.show', compact('medicalServicePricing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicalServicePricing $medicalServicePricing)
    {
        $medicalServices = MedicalService::with('serviceCategory')->orderBy('name')->get();
        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medical-service-pricing.edit', compact('medicalServicePricing', 'medicalServices', 'patientCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicalServicePricing $medicalServicePricing)
    {
        $request->validate([
            'medical_service_id' => 'required|exists:medical_services,id',
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
        $existingPricing = MedicalServicePricing::where('medical_service_id', $request->medical_service_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->where('id', '!=', $medicalServicePricing->id)
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
            })->exists();

        if ($existingPricing) {
            return back()->withInput()
                ->withErrors(['effective_from' => 'There is already an active pricing for this service and category that overlaps with the selected period.']);
        }

        try {
            DB::beginTransaction();

            $medicalServicePricing->update($request->all());

            DB::commit();

            return redirect()->route('medical-service-pricing.show', $medicalServicePricing)
                ->with('success', 'Medical service pricing updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update medical service pricing: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalServicePricing $medicalServicePricing)
    {
        try {
            $medicalServicePricing->delete();

            return redirect()->route('medical-service-pricing.index')
                ->with('success', 'Medical service pricing deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete medical service pricing: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update pricing records
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'pricing_ids' => 'required|array',
            'pricing_ids.*' => 'exists:medical_services_pricing,id',
            'action' => 'required|in:activate,deactivate,delete',
            'bulk_selling_price' => 'nullable|numeric|min:0',
            'bulk_markup_percentage' => 'nullable|numeric|min:0|max:1000',
            'bulk_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $pricing = MedicalServicePricing::whereIn('id', $request->pricing_ids);

            switch ($request->action) {
                case 'activate':
                    $pricing->update(['is_active' => true]);
                    $message = 'Selected pricing records activated successfully.';
                    break;

                case 'deactivate':
                    $pricing->update(['is_active' => false]);
                    $message = 'Selected pricing records deactivated successfully.';
                    break;

                case 'delete':
                    $pricing->delete();
                    $message = 'Selected pricing records deleted successfully.';
                    break;
            }

            // Apply bulk price updates if provided
            if ($request->filled('bulk_selling_price') && $request->action !== 'delete') {
                MedicalServicePricing::whereIn('id', $request->pricing_ids)
                    ->update(['selling_price' => $request->bulk_selling_price]);
            }

            if ($request->filled('bulk_markup_percentage') && $request->action !== 'delete') {
                MedicalServicePricing::whereIn('id', $request->pricing_ids)
                    ->update(['markup_percentage' => $request->bulk_markup_percentage]);
            }

            if ($request->filled('bulk_discount_percentage') && $request->action !== 'delete') {
                MedicalServicePricing::whereIn('id', $request->pricing_ids)
                    ->update(['discount_percentage' => $request->bulk_discount_percentage]);
            }

            DB::commit();

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk operation failed: ' . $e->getMessage());
        }
    }
}
