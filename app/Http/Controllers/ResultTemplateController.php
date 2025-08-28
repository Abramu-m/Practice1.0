<?php

namespace App\Http\Controllers;

use App\Models\ResultTemplate;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ResultTemplate::with('serviceCategory');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->service_category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->ordered()->paginate(20);
        $serviceCategories = ServiceCategory::active()->ordered()->get();

        return view('result_templates.index', compact('templates', 'serviceCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $serviceCategories = ServiceCategory::active()->ordered()->get();
        
        return view('result_templates.create', compact('serviceCategories'));
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
            'service_category_id' => 'nullable|exists:service_categories,id',
            'investigation_type' => 'nullable|string|max:255',
            'template_fields' => 'nullable|json',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            $template = ResultTemplate::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'service_category_id' => $request->service_category_id,
                'investigation_type' => $request->investigation_type,
                'template_fields' => $request->template_fields ? json_decode($request->template_fields, true) : null,
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
        $resultTemplate->load(['serviceCategory', 'medicalServices']);
        
        return view('result_templates.show', compact('resultTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ResultTemplate $resultTemplate)
    {
        $serviceCategories = ServiceCategory::active()->ordered()->get();
        
        return view('result_templates.edit', compact('resultTemplate', 'serviceCategories'));
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
            'service_category_id' => 'nullable|exists:service_categories,id',
            'investigation_type' => 'nullable|string|max:255',
            'template_fields' => 'nullable|json',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            $resultTemplate->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'service_category_id' => $request->service_category_id,
                'investigation_type' => $request->investigation_type,
                'template_fields' => $request->template_fields ? json_decode($request->template_fields, true) : null,
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
        $categoryId = $request->get('service_category_id');
        
        $templates = ResultTemplate::active()
            ->when($categoryId, function($query) use ($categoryId) {
                return $query->where('service_category_id', $categoryId);
            })
            ->ordered()
            ->get(['id', 'name', 'code', 'description']);

        return response()->json($templates);
    }

    /**
     * Return a rendered preview of the template partial for quick inspection (AJAX)
     */
    public function preview(Request $request, ResultTemplate $resultTemplate)
    {
        // Derive view path from the template code. Use service category to decide folder:
        // - if service category looks like 'lab' prefer lab.result_templates.{code}
        // - otherwise prefer procedures.forms.{code}
        $code = trim((string) ($resultTemplate->code ?? ''));
    // Determine lab category by service_category_id. Default to id=1 for Laboratory (per DB dump)
    // If your lab category uses a different id, adjust $labCategoryIds accordingly.
    $labCategoryIds = [1];
    $isLabCategory = in_array((int) $resultTemplate->service_category_id, $labCategoryIds, true);

        $labView = 'lab.result_templates.' . $code;
        $procView = 'procedures.forms.' . $code;

        $view = null;
        // First try the folder suggested by service category
        if ($code && $isLabCategory && view()->exists($labView)) {
            $view = $labView;
        } elseif ($code && !$isLabCategory && view()->exists($procView)) {
            $view = $procView;
        } else {
            // If the preferred folder didn't contain the view, try the other one
            if ($code && view()->exists($labView)) {
                $view = $labView;
            } elseif ($code && view()->exists($procView)) {
                $view = $procView;
            } else {
            // Fallback mapping for legacy names (keeps prior behavior for uncommon codes)
            $mapping = [
                // Procedures forms
                'simple_procedure' => 'procedures.forms.simple',
                'vital_observations' => 'procedures.forms.vital_observations',
                'complex_form' => 'procedures.forms.complex',
                'imaging' => 'procedures.forms.imaging',
                'general_procedure' => 'procedures.forms.default',

                // Lab result templates
                'simple_lab' => 'lab.result_templates.simple',
                'general_lab' => 'lab.result_templates.general',
                'cd4' => 'lab.result_templates.cd4',
                'tb' => 'lab.result_templates.tb',
            ];

            $view = $mapping[$code] ?? null;
            }
        }

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

            $fakeInvestigation->patient = $fakePatient;
            $fakeInvestigation->results = collect([$sampleResult]);
            // Some complex templates expect form-related payloads on the investigation
            $fakeInvestigation->form_data = []; // array access
            $fakeInvestigation->formData = (object) []; // object access

            $data['investigation'] = $fakeInvestigation;
            // Also provide medicalService and patient at top-level (some partials reference them directly)
            $data['medicalService'] = $fakeMedicalService;
            $data['patient'] = $fakePatient;
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
}
