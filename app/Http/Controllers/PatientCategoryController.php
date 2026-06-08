<?php

namespace App\Http\Controllers;

use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientCategoryController extends Controller
{
    /**
     * Candidate tariff tables an insurance category can point at.
     * These are created by the pricing migration (e.g. nhif_tariffs,
     * jubilee_tariff). The UI offers them as a dropdown so a category
     * can only be wired to a table that actually exists.
     */
    private function tariffTables(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->filter(fn ($name) => str_contains($name, 'tariff'))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patientCategories = PatientCategory::query();

        if ($request->has('search')) {
            $patientCategories->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('filter')) {
            $patientCategories->where('is_active', $request->filter);
        }

        if ($request->has('type_filter') && $request->type_filter != '') {
            $patientCategories->where('type', $request->type_filter);
        }

        $patientCategories = $patientCategories->paginate(10);

        return view('patient_categories.index', compact('patientCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tariffTables = $this->tariffTables();

        return view('patient_categories.create', compact('tariffTables'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:50|unique:patient_categories,description',
            'type' => 'required|in:cash,insurance',
            'is_active' => 'boolean',
            'code' => 'nullable|string|max:30|unique:patient_categories,code',
            'tariffs_table' => ['nullable', 'string', Rule::in($this->tariffTables())],
        ]);

        $patientCategory = new PatientCategory();
        $patientCategory->description = $request->description;
        $patientCategory->type = $request->type;
        $patientCategory->is_active = $request->is_active ?? true;
    $patientCategory->code = $request->code ?? null;
        // tariffs_table only applies to insurance categories
        $patientCategory->tariffs_table = $request->type === 'insurance'
            ? ($request->filled('tariffs_table') ? $request->tariffs_table : null)
            : null;
        $patientCategory->created_by = Auth::id();
        $patientCategory->save();

        return redirect()->route('patient_categories.index')->with('success', 'Patient category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientCategory $patientCategory)
    {
        return view('patient_categories.show', compact('patientCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientCategory $patientCategory)
    {
        $tariffTables = $this->tariffTables();

        return view('patient_categories.edit', compact('patientCategory', 'tariffTables'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientCategory $patientCategory)
    {
        // Allow the current value too, so an edit isn't blocked if the
        // category already points at a table that's since been dropped.
        $allowedTables = array_values(array_unique(array_filter(
            array_merge($this->tariffTables(), [$patientCategory->tariffs_table])
        )));

        $request->validate([
            'description' => 'required|string|max:50|unique:patient_categories,description,' . $patientCategory->id,
            'type' => 'required|in:cash,insurance',
            'is_active' => 'boolean',
            'code' => 'nullable|string|max:30|unique:patient_categories,code,' . $patientCategory->id,
            'tariffs_table' => ['nullable', 'string', Rule::in($allowedTables)],
        ]);

        $patientCategory->description = $request->description;
        $patientCategory->type = $request->type;
        $patientCategory->is_active = $request->is_active ?? true;
    $patientCategory->code = $request->code ?? null;
        // tariffs_table only applies to insurance categories
        $patientCategory->tariffs_table = $request->type === 'insurance'
            ? ($request->filled('tariffs_table') ? $request->tariffs_table : null)
            : null;
        $patientCategory->created_by = Auth::id();
        $patientCategory->save();

        return redirect()->route('patient_categories.index')->with('success', 'Patient category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientCategory $patientCategory)
    {
        $patientCategory->delete();

        return redirect()->route('patient_categories.index')->with('success', 'Patient category deleted successfully.');
    }
}
