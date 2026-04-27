<?php

namespace App\Http\Controllers;

use App\Models\MedicalService;
use App\Models\ServiceCategory;
use App\Models\ResultTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicalServiceController extends Controller
{
    /**
     * Display a listing of medical services
     */
    public function index(Request $request)
    {
        $query = MedicalService::with(['serviceCategory', 'resultTemplate']);

        // Apply filters
        if ($request->filled('category')) {
            if ($request->category === 'investigations') {
                // Investigations = all medical services except those with category name "Procedures"
                $query->whereHas('serviceCategory', function($q) {
                    $q->where('name', '!=', 'Procedures');
                });
            } elseif ($request->category === 'procedures') {
                // Procedures = medical services with category name "Procedures"
                $query->whereHas('serviceCategory', function($q) {
                    $q->where('name', 'Procedures');
                });
            } else {
                // Regular category ID filtering
                $query->where('service_category_id', $request->category);
            }
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('requires_sample')) {
            $query->where('requires_sample', $request->requires_sample === 'yes');
        }

        if ($request->filled('requires_form')) {
            $query->where('requires_form', $request->requires_form === 'yes');
        }

        $services = $query->orderBy('name')
                         ->paginate(20);

        $categories = ServiceCategory::active()->orderBy('name')->get();

        return view('medical_services.index', compact('services', 'categories'));
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
