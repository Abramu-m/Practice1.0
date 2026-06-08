<?php

namespace App\Http\Controllers;

use App\Models\ResultTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ResultTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ResultTemplate::query();

            // Apply filters
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            return DataTables::of($query)
                ->addColumn('name_display', function ($template) {
                    $html = '<strong>' . e($template->name) . '</strong>';
                    if ($template->description) {
                        $html .= '<br><small class="text-muted">' . e(\Illuminate\Support\Str::limit($template->description, 50)) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('code_display', function ($template) {
                    return '<code>' . e($template->code) . '</code>';
                })
                ->addColumn('sort_order_display', function ($template) {
                    return '<span class="badge bg-light text-dark">' . e($template->sort_order) . '</span>';
                })
                ->addColumn('status_display', function ($template) {
                    if ($template->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($template) {
                    $viewBtn = '<a href="' . route('result-templates.show', $template) . '" class="btn btn-info" title="View Details">' .
                               '<i class="fas fa-eye"></i></a>';
                    
                    $previewBtn = '<button type="button" class="btn btn-primary preview-template-btn" title="Preview Template" ' .
                                  'data-id="' . $template->id . '" ' .
                                  'data-name="' . e($template->name) . '" ' .
                                  'data-code="' . e($template->code) . '" ' .
                                  'data-description="' . e($template->description) . '">' .
                                  '<i class="fas fa-eye-dropper"></i></button>';
                    
                    $editBtn = '<a href="' . route('result-templates.edit', $template) . '" class="btn btn-warning" title="Edit">' .
                               '<i class="fas fa-edit"></i></a>';
                    
                    $toggleBtn = '<form method="POST" action="' . route('result-templates.toggle-status', $template) . '" class="d-inline">' .
                                 csrf_field() . method_field('PATCH') .
                                 '<button type="submit" class="btn btn-' . ($template->is_active ? 'secondary' : 'success') . '" ' .
                                 'title="' . ($template->is_active ? 'Deactivate' : 'Activate') . '">' .
                                 '<i class="fas fa-' . ($template->is_active ? 'pause' : 'play') . '"></i></button></form>';
                    
                    $deleteBtn = '<form method="POST" action="' . route('result-templates.destroy', $template) . '" class="d-inline" ' .
                                 'onsubmit="return confirm(\'Are you sure you want to delete this template? This action cannot be undone.\')">' .
                                 csrf_field() . method_field('DELETE') .
                                 '<button type="submit" class="btn btn-danger" title="Delete">' .
                                 '<i class="fas fa-trash"></i></button></form>';
                    
                    return '<div class="btn-group btn-group-sm" role="group">' . 
                           $viewBtn . $previewBtn . $editBtn . $toggleBtn . $deleteBtn . 
                           '</div>';
                })
                ->rawColumns(['name_display', 'code_display', 'sort_order_display', 'status_display', 'actions'])
                ->make(true);
        }

        return view('result_templates.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('result_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:result_templates,code',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            $template = ResultTemplate::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->sort_order ?? 0
            ]);

            DB::commit();

            return redirect()->route('result-templates.index')
                ->with('success', 'Result template created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create result template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ResultTemplate $resultTemplate)
    {
        $resultTemplate->load(['medicalServices']);
        
        return view('result_templates.show', compact('resultTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ResultTemplate $resultTemplate)
    {
        return view('result_templates.edit', compact('resultTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResultTemplate $resultTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:result_templates,code,' . $resultTemplate->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            $resultTemplate->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->sort_order ?? 0
            ]);

            DB::commit();

            return redirect()->route('result-templates.index')
                ->with('success', 'Result template updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update result template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResultTemplate $resultTemplate)
    {
        try {
            // Check if template is being used by any medical services
            if ($resultTemplate->medicalServices()->exists()) {
                return back()->with('error', 'Cannot delete template that is being used by medical services.');
            }

            $resultTemplate->delete();

            return redirect()->route('result-templates.index')
                ->with('success', 'Result template deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete result template: ' . $e->getMessage());
        }
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(ResultTemplate $resultTemplate)
    {
        try {
            $resultTemplate->update(['is_active' => !$resultTemplate->is_active]);

            $status = $resultTemplate->is_active ? 'activated' : 'deactivated';
            
            return back()->with('success', "Result template {$status} successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to toggle template status: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to get templates by service category
     */
    public function getByServiceCategory(Request $request)
    {
        $templates = ResultTemplate::active()
            ->ordered()
            ->get(['id', 'name', 'code', 'description']);

        return response()->json($templates);
    }

    /**
     * Return a rendered preview of the template partial for quick inspection (AJAX)
     */
    public function preview(Request $request, ResultTemplate $resultTemplate)
    {
        // Always resolve preview view from lab.result_templates.{code}
        $code = trim((string) ($resultTemplate->code ?? ''));

        $view = $code ? 'lab.result_templates.' . $code : null;

        if (!$view || !view()->exists($view)) {
            $message = '<div class="alert alert-info">No preview available for this template.</div>';
            if ($request->ajax()) {
                return response($message, 200)->header('Content-Type', 'text/html');
            }
            return response()->json(['html' => $message]);
        }

        // Provide minimal data expected by partials to avoid undefined variable errors
        $data = [
            'investigation' => null,
            'editMode' => false,
            'existingData' => [],
        ];

        // For preview rendering we create a lightweight fake investigation so blade partials
        // that expect $investigation and $investigation->medicalService->resultTemplate can render.
        // Use a non-zero id to satisfy truthy checks in templates (e.g. @if($investigation && $investigation->id)).
        try {
            $fakeInvestigation = new \stdClass();
            $fakeInvestigation->id = -1; // non-zero sentinel id for preview
            $fakeMedicalService = new \stdClass();
            $fakeMedicalService->id = -1;
            // Provide basic fields templates may expect
            $fakeMedicalService->name = $resultTemplate->name ?? 'Preview Service';
            $fakeMedicalService->code = $resultTemplate->code ?? 'preview';
            // Provide numeric range/unit fields often used by lab templates
            $fakeMedicalService->min_value = $resultTemplate->min_value ?? null;
            $fakeMedicalService->max_value = $resultTemplate->max_value ?? null;
            $fakeMedicalService->unit = $resultTemplate->unit ?? '';
            // Provide the resultTemplate object so partials can access ->code etc.
            $fakeMedicalService->resultTemplate = $resultTemplate;
            $fakeInvestigation->medicalService = $fakeMedicalService;
            // Provide a minimal patient object for templates that reference patient info
            $fakePatient = new \stdClass();
            $fakePatient->id = -1;
            $fakePatient->first_name = 'Preview';
            $fakePatient->last_name = 'Patient';
            $fakePatient->full_name = trim($fakePatient->first_name . ' ' . $fakePatient->last_name);

            // Some templates expect $investigation->results (complex forms). Provide empty or sample results
            $sampleResult = new \stdClass();
            $sampleResult->name = 'Sample Parameter';
            // Some templates use parameter_name while others use parameter
            $sampleResult->parameter = 'Sample Parameter';
            $sampleResult->parameter_name = 'Sample Parameter';
            $sampleResult->value = '0';
            $sampleResult->unit = '';
            $sampleResult->min_value = null;
            $sampleResult->max_value = null;
            $sampleResult->reference_range = '';
            // Provide convenience fields expected by simple lab template
            $sampleResult->normal_range = '';
            $sampleResult->remarks = '';
            // Provide form payload and status expected by complex procedure templates
            $sampleResult->form_data = [];
            $sampleResult->form_status = 'draft';
            $sampleResult->status = '';

            // Dummy visit for templates that reference $visit (e.g. genxpert_tb)
            $fakeVisitPatient = new \stdClass();
            $fakeVisitPatient->first_name   = 'Preview';
            $fakeVisitPatient->last_name    = 'Patient';
            $fakeVisitPatient->age          = '—';
            $fakeVisitPatient->gender       = '—';
            $fakeVisitPatient->address      = '—';
            $fakeVisitPatient->phone_number = '—';

            $fakeVisitDoctorUser = new \stdClass();
            $fakeVisitDoctorUser->name = 'Dr. Preview';
            $fakeVisitDoctor = new \stdClass();
            $fakeVisitDoctor->user = $fakeVisitDoctorUser;

            $fakeVisit = new \stdClass();
            $fakeVisit->id          = -1;
            $fakeVisit->created_at  = \Carbon\Carbon::now();
            $fakeVisit->patientInfo = $fakeVisitPatient;
            $fakeVisit->doctorInfo  = $fakeVisitDoctor;

            $fakeInvestigation->patient = $fakePatient;
            $fakeInvestigation->results = collect([$sampleResult]);
            // Some complex templates expect form-related payloads on the investigation
            $fakeInvestigation->form_data = []; // array access
            $fakeInvestigation->formData = (object) []; // object access

            $data['investigation'] = $fakeInvestigation;
            // Also provide medicalService and patient at top-level (some partials reference them directly)
            $data['medicalService'] = $fakeMedicalService;
            $data['patient'] = $fakePatient;
            $data['visit'] = $fakeVisit;
        } catch (\Exception $e) {
            // ignore; if fake object creation fails, the view will fall back to existingData-based mock
        }

        try {
            $html = view($view, $data)->render();
        } catch (\Exception $e) {
            $html = '<div class="alert alert-danger">Failed to render preview: ' . e($e->getMessage()) . '</div>';
        }

        // If the request is AJAX (from the client preview), return raw HTML for easier insertion.
        if ($request->ajax()) {
            return response($html, 200)->header('Content-Type', 'text/html');
        }

        return response()->json(['html' => $html]);
    }

    /**
     * Return a mock results-view HTML for the given template (AJAX) — mirrors the real results display.
     */
    public function resultsPreview(Request $request, ResultTemplate $resultTemplate)
    {
        try {
            $html = view('result_templates._results_preview', ['template' => $resultTemplate])->render();
        } catch (\Exception $e) {
            $html = '<div class="alert alert-danger">Failed to render results preview: ' . e($e->getMessage()) . '</div>';
        }

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
