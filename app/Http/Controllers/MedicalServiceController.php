<?php

namespace App\Http\Controllers;

use App\Models\MedicalService;
use App\Models\ServiceCategory;
use App\Models\ResultTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class MedicalServiceController extends Controller
{
    /**
     * Display a listing of medical services
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MedicalService::with(['serviceCategory', 'resultTemplate']);

            // Apply custom filters passed from DataTables
            if ($request->filled('category_filter')) {
                if ($request->category_filter === 'investigations') {
                    $query->whereHas('serviceCategory', function ($q) {
                        $q->where('name', '!=', 'Procedures');
                    });
                } elseif ($request->category_filter === 'procedures') {
                    $query->whereHas('serviceCategory', function ($q) {
                        $q->where('name', 'Procedures');
                    });
                } else {
                    $query->where('service_category_id', $request->category_filter);
                }
            }

            if ($request->filled('status_filter')) {
                $query->where('is_active', $request->status_filter === 'active');
            }

            if ($request->filled('requires_sample_filter')) {
                $query->where('requires_sample', $request->requires_sample_filter === 'yes');
            }

            if ($request->filled('requires_form_filter')) {
                $query->where('requires_form', $request->requires_form_filter === 'yes');
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if (!empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('description', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('name_display', function ($service) {
                    $html = '<strong>' . e($service->name) . '</strong>';
                    if ($service->description) {
                        $html .= '<br><small class="text-muted">' . e(Str::limit($service->description, 60)) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('category_display', function ($service) {
                    if ($service->serviceCategory) {
                        return '<span class="badge bg-secondary">' . e($service->serviceCategory->name) . '</span>';
                    }
                    return '<span class="text-muted">No category</span>';
                })
                ->addColumn('sample_display', function ($service) {
                    if ($service->requires_sample) {
                        $html = '<span class="badge bg-warning">Required</span>';
                        if ($service->sample_type) {
                            $html .= '<br><small>' . e($service->sample_type) . '</small>';
                        }
                        return $html;
                    }
                    return '<span class="badge bg-secondary">Not Required</span>';
                })
                ->addColumn('form_display', function ($service) {
                    if ($service->requires_form) {
                        if ($service->form_type) {
                            $html = '<button type="button" class="btn btn-link p-0 border-0 preview-form-btn" '
                                  . 'data-form-type="' . e($service->form_type) . '" '
                                  . 'title="Preview form">'
                                  . '<span class="badge bg-info" style="cursor:pointer">' . e($service->form_type) . '</span>'
                                  . '</button>';
                        } else {
                            $html = '<span class="badge bg-info">Required</span>';
                        }
                        return $html;
                    }
                    return '<span class="badge bg-secondary">Not Required</span>';
                })
                ->addColumn('result_template_display', function ($service) {
                    if ($service->resultTemplate) {
                        return '<button type="button" class="btn btn-link p-0 border-0 preview-template-btn" '
                             . 'data-template-id="' . e($service->resultTemplate->id) . '" '
                             . 'data-template-name="' . e($service->resultTemplate->name) . '" '
                             . 'title="Preview result template">'
                             . '<span class="badge bg-primary" style="cursor:pointer">' . e($service->resultTemplate->name) . '</span>'
                             . '</button>';
                    }
                    return '<span class="badge bg-warning">Not Set</span>';
                })
                ->addColumn('status_display', function ($service) {
                    return $service->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($service) {
                    $toggleClass   = $service->is_active ? 'secondary' : 'success';
                    $toggleTitle   = $service->is_active ? 'Deactivate' : 'Activate';
                    $toggleIcon    = $service->is_active ? 'pause' : 'play';
                    $toggleConfirm = $service->is_active ? 'deactivate' : 'activate';
                    $html  = '<div class="btn-group btn-group-sm" role="group">';
                    $html .= '<a href="' . route('medical_services.show', $service) . '" class="btn btn-info" title="View Details"><i class="fas fa-eye"></i></a>';
                    $html .= '<a href="' . route('medical_services.edit', $service) . '" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $html .= '<form method="POST" action="' . route('medical_services.toggle-status', $service) . '" class="d-inline">';
                    $html .= csrf_field() . method_field('PATCH');
                    $html .= '<button type="submit" class="btn btn-' . $toggleClass . '" title="' . $toggleTitle . '" '
                           . 'onclick="return confirm(\'Are you sure you want to ' . $toggleConfirm . ' this service?\')">'
                           . '<i class="fas fa-' . $toggleIcon . '"></i></button>';
                    $html .= '</form>';
                    $html .= '<form method="POST" action="' . route('medical_services.destroy', $service) . '" class="d-inline">';
                    $html .= csrf_field() . method_field('DELETE');
                    $html .= '<button type="submit" class="btn btn-danger" title="Delete" '
                           . 'onclick="return confirm(\'Are you sure you want to delete this service? This action cannot be undone.\')">'
                           . '<i class="fas fa-trash"></i></button>';
                    $html .= '</form>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['name_display', 'category_display', 'sample_display', 'form_display', 'result_template_display', 'status_display', 'actions'])
                ->make(true);
        }

        $categories = ServiceCategory::active()->orderBy('name')->get();

        return view('medical_services.index', compact('categories'));
    }

    /**
     * Show the form for creating a new medical service
     */
    public function create()
    {
        $categories = ServiceCategory::active()->orderBy('name')->get();
        $resultTemplates = ResultTemplate::active()->ordered()->get();
        return view('medical_services.create', compact('categories', 'resultTemplates'));
    }

    /**
     * Store a newly created medical service
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'service_category_id' => 'required|exists:service_categories,id',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric|gt:min_value',
            'unit' => 'nullable|string|max:50',
            'requires_sample' => 'boolean',
            'sample_type' => 'nullable|string|max:255',
            'turnaround_time_hours' => 'nullable|integer|min:0',
            'preparation_instructions' => 'nullable|string|max:2000',
            'requires_form' => 'boolean',
            'form_type' => 'nullable|string|max:255',
            'result_template_id' => 'required|exists:result_templates,id',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            $service = MedicalService::create($data);

            DB::commit();

            return redirect()->route('medical_services.index')
                ->with('success', 'Medical service created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create medical service: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified medical service
     */
    public function show(MedicalService $medicalService)
    {
        $medicalService->load(['serviceCategory', 'resultTemplate', 'investigations.patient']);
        
        // Get recent investigations for this service
        $recentInvestigations = $medicalService->investigations()
            ->with(['patient', 'doctor'])
            ->latest('ordered_at')
            ->take(10)
            ->get();

        return view('medical_services.show', compact('medicalService', 'recentInvestigations'));
    }

    /**
     * Show the form for editing the specified medical service
     */
    public function edit(MedicalService $medicalService)
    {
        $categories = ServiceCategory::active()->orderBy('name')->get();
        $resultTemplates = ResultTemplate::active()->ordered()->get();
        return view('medical_services.edit', compact('medicalService', 'categories', 'resultTemplates'));
    }

    /**
     * Update the specified medical service
     */
    public function update(Request $request, MedicalService $medicalService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'service_category_id' => 'required|exists:service_categories,id',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric|gt:min_value',
            'unit' => 'nullable|string|max:50',
            'requires_sample' => 'boolean',
            'sample_type' => 'nullable|string|max:255',
            'turnaround_time_hours' => 'nullable|integer|min:0',
            'preparation_instructions' => 'nullable|string|max:2000',
            'requires_form' => 'boolean',
            'form_type' => 'nullable|string|max:255',
            'result_template_id' => 'required|exists:result_templates,id',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            $medicalService->update($data);

            DB::commit();

            return redirect()->route('medical_services.show', $medicalService)
                ->with('success', 'Medical service updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update medical service: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified medical service
     */
    public function destroy(MedicalService $medicalService)
    {
        try {
            // Check if service has any investigations
            if ($medicalService->investigations()->exists()) {
                return back()->with('error', 'Cannot delete service with existing investigations');
            }

            $medicalService->delete();

            return redirect()->route('medical_services.index')
                ->with('success', 'Medical service deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete medical service: ' . $e->getMessage());
        }
    }

    /**
     * Toggle service status
     */
    public function toggleStatus(MedicalService $medicalService)
    {
        try {
            $medicalService->update(['is_active' => !$medicalService->is_active]);

            $status = $medicalService->is_active ? 'activated' : 'deactivated';
            
            return back()->with('success', "Medical service {$status} successfully");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update service status: ' . $e->getMessage());
        }
    }
}
