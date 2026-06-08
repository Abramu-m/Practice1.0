<?php

namespace App\Http\Controllers;

use App\Models\MedicationInsuranceMap;
use App\Models\Medication;
use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class MedicationInsuranceMapController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MedicationInsuranceMap::with(['medication', 'patientCategory'])
                ->select('medication_insurance_map.*');

            if ($request->filled('medication_id')) {
                $query->where('medication_id', $request->medication_id);
            }

            if ($request->filled('patient_category_id')) {
                $query->where('patient_category_id', $request->patient_category_id);
            }

            $tariffCache = [];

            return DataTables::of($query)
                ->addColumn('medication_display', function ($map) {
                    $html = '<strong>' . e($map->medication->generic_name) . '</strong>';
                    if ($map->medication->brand_name) {
                        $html .= '<br><small class="text-muted">' . e($map->medication->brand_name) . '</small>';
                    }
                    if ($map->medication->strength) {
                        $html .= '<br><small class="text-muted">' . e($map->medication->strength) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('category_display', function ($map) {
                    return '<span class="badge bg-info text-black">' . e($map->patientCategory->description) . '</span>';
                })
                ->addColumn('tariff_item_name', function ($map) use (&$tariffCache) {
                    $table = $map->patientCategory->tariffs_table ?? null;
                    if (!$table) {
                        return '<span class="text-muted">—</span>';
                    }
                    $key = $table . '::' . $map->insurance_item_code;
                    if (!array_key_exists($key, $tariffCache)) {
                        $tariffCache[$key] = DB::table($table)
                            ->where('item_code', $map->insurance_item_code)
                            ->first(['item_name', 'unit_price']);
                    }
                    return $tariffCache[$key]
                        ? e($tariffCache[$key]->item_name)
                        : '<span class="text-muted text-danger">Not in tariff</span>';
                })
                ->addColumn('selling_price', function ($map) {
                    $price = (float) ($map->medication->selling_price ?? 0);
                    return $price > 0 ? number_format($price, 2) : '—';
                })
                ->addColumn('insurance_price', function ($map) use (&$tariffCache) {
                    $table = $map->patientCategory->tariffs_table ?? null;
                    if (!$table) return '—';
                    $key = $table . '::' . $map->insurance_item_code;
                    if (!array_key_exists($key, $tariffCache)) {
                        $tariffCache[$key] = DB::table($table)
                            ->where('item_code', $map->insurance_item_code)
                            ->first(['item_name', 'unit_price']);
                    }
                    return $tariffCache[$key] ? number_format((float) $tariffCache[$key]->unit_price, 2) : '—';
                })
                ->addColumn('actions', function ($map) {
                    $html = '<div class="btn-group">';
                    $html .= '<a href="' . route('medication-insurance-map.show', $map->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $html .= '<a href="' . route('medication-insurance-map.edit', $map->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>';
                    $html .= '<form action="' . route('medication-insurance-map.destroy', $map->id) . '" method="POST" style="display: inline;" onsubmit="return confirm(\'Are you sure?\')">';
                    $html .= csrf_field() . method_field('DELETE');
                    $html .= '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                    $html .= '</form></div>';
                    return $html;
                })
                ->rawColumns(['medication_display', 'category_display', 'tariff_item_name', 'actions'])
                ->make(true);
        }

        $selectedMedication = null;
        if ($request->filled('medication_id')) {
            $selectedMedication = Medication::find($request->medication_id);
        }

        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medication-insurance-map.index', compact('selectedMedication', 'patientCategories'));
    }

    public function nhifIndex(Request $request)
    {
        if ($request->ajax()) {
            $query = MedicationInsuranceMap::with(['medication', 'patientCategory'])
                ->whereHas('patientCategory', fn($q) => $q->where('description', 'like', '%nhif%'))
                ->select('medication_insurance_map.*');

            if ($request->filled('medication_id')) {
                $query->where('medication_id', $request->medication_id);
            }

            $tariffCache = [];

            return DataTables::of($query)
                ->addColumn('medication_display', function ($map) {
                    $html = '<strong>' . e($map->medication->generic_name) . '</strong>';
                    if ($map->medication->brand_name) {
                        $html .= '<br><small class="text-muted">' . e($map->medication->brand_name) . '</small>';
                    }
                    if ($map->medication->strength) {
                        $html .= '<br><small class="text-muted">' . e($map->medication->strength) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('category_display', function ($map) {
                    return '<span class="badge bg-info text-black">' . e($map->patientCategory->description) . '</span>';
                })
                ->addColumn('tariff_item_name', function ($map) use (&$tariffCache) {
                    $table = $map->patientCategory->tariffs_table ?? null;
                    if (!$table) {
                        return '<span class="text-muted">—</span>';
                    }
                    $key = $table . '::' . $map->insurance_item_code;
                    if (!array_key_exists($key, $tariffCache)) {
                        $tariffCache[$key] = DB::table($table)
                            ->where('item_code', $map->insurance_item_code)
                            ->value('item_name');
                    }
                    return $tariffCache[$key]
                        ? e($tariffCache[$key])
                        : '<span class="text-danger">Not in tariff</span>';
                })
                ->addColumn('actions', function ($map) {
                    $html = '<div class="btn-group">';
                    $html .= '<a href="' . route('medication-insurance-map.show', $map->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $html .= '<a href="' . route('medication-insurance-map.edit', $map->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['medication_display', 'category_display', 'tariff_item_name', 'actions'])
                ->make(true);
        }

        $selectedMedication = null;
        if ($request->filled('medication_id')) {
            $selectedMedication = Medication::find($request->medication_id);
        }

        return view('nhif.medication-mapping', compact('selectedMedication'));
    }

    public function create()
    {
        $patientCategories = PatientCategory::orderBy('description')->get();
        return view('medication-insurance-map.create', compact('patientCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'medication_id'       => 'required|exists:medications,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'insurance_item_code' => 'required|string|max:255',
        ]);

        $exists = MedicationInsuranceMap::where('medication_id', $request->medication_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['medication_id' => 'A mapping for this medication and patient category already exists.'])->withInput();
        }

        MedicationInsuranceMap::create($request->only('medication_id', 'patient_category_id', 'insurance_item_code'));

        return redirect()->route('medication-insurance-map.index')
            ->with('success', 'Medication insurance map created successfully.');
    }

    public function show(MedicationInsuranceMap $medicationInsuranceMap)
    {
        $medicationInsuranceMap->load(['medication', 'patientCategory']);
        $tariffItem = $this->fetchTariffItem(
            $medicationInsuranceMap->patientCategory,
            $medicationInsuranceMap->insurance_item_code
        );
        return view('medication-insurance-map.show', compact('medicationInsuranceMap', 'tariffItem'));
    }

    public function edit(MedicationInsuranceMap $medicationInsuranceMap)
    {
        $patientCategories = PatientCategory::orderBy('description')->get();
        $medicationInsuranceMap->load(['medication', 'patientCategory']);

        $currentTariffItem = $this->fetchTariffItem(
            $medicationInsuranceMap->patientCategory,
            $medicationInsuranceMap->insurance_item_code
        );

        return view('medication-insurance-map.edit', compact('medicationInsuranceMap', 'patientCategories', 'currentTariffItem'));
    }

    private function fetchTariffItem(PatientCategory $category, string $itemCode): ?object
    {
        $table = $category->tariffs_table ?? null;
        if (!$table || !Schema::hasTable($table)) {
            return null;
        }
        return DB::table($table)->where('item_code', $itemCode)->first();
    }

    public function update(Request $request, MedicationInsuranceMap $medicationInsuranceMap)
    {
        $request->validate([
            'medication_id'       => 'required|exists:medications,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'insurance_item_code' => 'required|string|max:255',
        ]);

        $exists = MedicationInsuranceMap::where('medication_id', $request->medication_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->where('id', '!=', $medicationInsuranceMap->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['medication_id' => 'A mapping for this medication and patient category already exists.'])->withInput();
        }

        $medicationInsuranceMap->update($request->only('medication_id', 'patient_category_id', 'insurance_item_code'));

        return redirect()->route('medication-insurance-map.index')
            ->with('success', 'Medication insurance map updated successfully.');
    }

    public function destroy(MedicationInsuranceMap $medicationInsuranceMap)
    {
        $medicationInsuranceMap->delete();
        return redirect()->route('medication-insurance-map.index')
            ->with('success', 'Medication insurance map deleted successfully.');
    }
}
