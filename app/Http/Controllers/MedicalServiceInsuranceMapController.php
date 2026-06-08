<?php

namespace App\Http\Controllers;

use App\Models\MedicalServiceInsuranceMap;
use App\Models\MedicalService;
use App\Models\PatientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class MedicalServiceInsuranceMapController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MedicalServiceInsuranceMap::with(['medicalService', 'patientCategory'])
                ->select('medical_service_insurance_map.*');

            if ($request->filled('medical_service_id')) {
                $query->where('medical_service_id', $request->medical_service_id);
            }

            if ($request->filled('patient_category_id')) {
                $query->where('patient_category_id', $request->patient_category_id);
            }

            $tariffCache = [];

            return DataTables::of($query)
                ->addColumn('service_display', function ($map) {
                    $html = '<strong>' . e($map->medicalService->name) . '</strong>';
                    if ($map->medicalService->code) {
                        $html .= '<br><small class="text-muted">' . e($map->medicalService->code) . '</small>';
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
                    $price = (float) ($map->medicalService->selling_price ?? 0);
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
                    $html .= '<a href="' . route('medical-service-insurance-map.show', $map->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $html .= '<a href="' . route('medical-service-insurance-map.edit', $map->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>';
                    $html .= '<form action="' . route('medical-service-insurance-map.destroy', $map->id) . '" method="POST" style="display: inline;" onsubmit="return confirm(\'Are you sure?\')">';
                    $html .= csrf_field() . method_field('DELETE');
                    $html .= '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                    $html .= '</form></div>';
                    return $html;
                })
                ->rawColumns(['service_display', 'category_display', 'tariff_item_name', 'actions'])
                ->make(true);
        }

        $selectedService = null;
        if ($request->filled('medical_service_id')) {
            $selectedService = MedicalService::find($request->medical_service_id);
        }

        $patientCategories = PatientCategory::orderBy('description')->get();

        return view('medical-service-insurance-map.index', compact('selectedService', 'patientCategories'));
    }

    public function nhifIndex(Request $request)
    {
        if ($request->ajax()) {
            $query = MedicalServiceInsuranceMap::with(['medicalService', 'patientCategory'])
                ->whereHas('patientCategory', fn($q) => $q->where('description', 'like', '%nhif%'))
                ->select('medical_service_insurance_map.*');

            if ($request->filled('medical_service_id')) {
                $query->where('medical_service_id', $request->medical_service_id);
            }

            $tariffCache = [];

            return DataTables::of($query)
                ->addColumn('service_display', function ($map) {
                    $html = '<strong>' . e($map->medicalService->name) . '</strong>';
                    if ($map->medicalService->code) {
                        $html .= '<br><small class="text-muted">' . e($map->medicalService->code) . '</small>';
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
                    $html .= '<a href="' . route('medical-service-insurance-map.show', $map->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $html .= '<a href="' . route('medical-service-insurance-map.edit', $map->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['service_display', 'category_display', 'tariff_item_name', 'actions'])
                ->make(true);
        }

        $selectedService = null;
        if ($request->filled('medical_service_id')) {
            $selectedService = MedicalService::find($request->medical_service_id);
        }

        return view('nhif.service-mapping', compact('selectedService'));
    }

    public function create()
    {
        $patientCategories = PatientCategory::orderBy('description')->get();
        return view('medical-service-insurance-map.create', compact('patientCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'medical_service_id'  => 'required|exists:medical_services,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'insurance_item_code' => 'required|string|max:255',
        ]);

        $exists = MedicalServiceInsuranceMap::where('medical_service_id', $request->medical_service_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['medical_service_id' => 'A mapping for this service and patient category already exists.'])->withInput();
        }

        MedicalServiceInsuranceMap::create($request->only('medical_service_id', 'patient_category_id', 'insurance_item_code'));

        return redirect()->route('medical-service-insurance-map.index')
            ->with('success', 'Medical service insurance map created successfully.');
    }

    public function show(MedicalServiceInsuranceMap $medicalServiceInsuranceMap)
    {
        $medicalServiceInsuranceMap->load(['medicalService', 'patientCategory']);
        $tariffItem = $this->fetchTariffItem(
            $medicalServiceInsuranceMap->patientCategory,
            $medicalServiceInsuranceMap->insurance_item_code
        );
        return view('medical-service-insurance-map.show', compact('medicalServiceInsuranceMap', 'tariffItem'));
    }

    public function edit(MedicalServiceInsuranceMap $medicalServiceInsuranceMap)
    {
        $patientCategories = PatientCategory::orderBy('description')->get();
        $medicalServiceInsuranceMap->load(['medicalService', 'patientCategory']);

        $currentTariffItem = $this->fetchTariffItem(
            $medicalServiceInsuranceMap->patientCategory,
            $medicalServiceInsuranceMap->insurance_item_code
        );

        return view('medical-service-insurance-map.edit', compact('medicalServiceInsuranceMap', 'patientCategories', 'currentTariffItem'));
    }

    private function fetchTariffItem(PatientCategory $category, string $itemCode): ?object
    {
        $table = $category->tariffs_table ?? null;
        if (!$table || !Schema::hasTable($table)) {
            return null;
        }
        return DB::table($table)->where('item_code', $itemCode)->first();
    }

    public function update(Request $request, MedicalServiceInsuranceMap $medicalServiceInsuranceMap)
    {
        $request->validate([
            'medical_service_id'  => 'required|exists:medical_services,id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'insurance_item_code' => 'required|string|max:255',
        ]);

        $exists = MedicalServiceInsuranceMap::where('medical_service_id', $request->medical_service_id)
            ->where('patient_category_id', $request->patient_category_id)
            ->where('id', '!=', $medicalServiceInsuranceMap->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['medical_service_id' => 'A mapping for this service and patient category already exists.'])->withInput();
        }

        $medicalServiceInsuranceMap->update($request->only('medical_service_id', 'patient_category_id', 'insurance_item_code'));

        return redirect()->route('medical-service-insurance-map.index')
            ->with('success', 'Medical service insurance map updated successfully.');
    }

    public function destroy(MedicalServiceInsuranceMap $medicalServiceInsuranceMap)
    {
        $medicalServiceInsuranceMap->delete();
        return redirect()->route('medical-service-insurance-map.index')
            ->with('success', 'Medical service insurance map deleted successfully.');
    }
}
