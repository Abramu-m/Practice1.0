<?php

namespace App\Http\Controllers;

use App\Models\VitalSigns;
use App\Models\PatientVisit;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class VitalsController extends Controller
{

    /**
     * Display a listing of the vitals for the logged-in nurse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PatientVisit::with(['patientInfo', 'consultation', 'vitalSigns'])
                ->orderBy('visit_date', 'desc');

            // Apply filters
            if ($request->filled('patient_search')) {
                $search = $request->patient_search;
                $query->whereHas('patientInfo', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('mr_number', 'like', "%{$search}%");
                });
            }

            if ($request->filled('visit_date')) {
                $query->whereDate('visit_date', $request->visit_date);
            }

            if ($request->filled('vitals_status')) {
                if ($request->vitals_status === 'recorded') {
                    $query->has('vitalSigns');
                } elseif ($request->vitals_status === 'not_recorded') {
                    $query->doesntHave('vitalSigns');
                }
            }

            return DataTables::of($query)
                ->addColumn('patient_name', function ($visit) {
                    return ($visit->patientInfo->first_name ?? 'N/A') . ' ' . ($visit->patientInfo->last_name ?? '');
                })
                ->addColumn('mr_number', function ($visit) {
                    return $visit->patientInfo->mr_number ?? 'N/A';
                })
                ->addColumn('visit_date_formatted', function ($visit) {
                    return $visit->visit_date ? $visit->visit_date->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('vitals_status', function ($visit) {
                    if ($visit->vitalSigns && $visit->vitalSigns->count() > 0) {
                        return '<span class="badge bg-success"><i class="fas fa-check"></i> Recorded</span>';
                    }
                    return '<span class="badge bg-danger"><i class="fas fa-times"></i> Not Recorded</span>';
                })
                ->addColumn('actions', function ($visit) {
                    if ($visit->vitalSigns && $visit->vitalSigns->count() > 0) {
                        return '<a href="' . route('vitals.show', $visit->id) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View/Edit
                                </a>';
                    }
                    return '<a href="' . route('vitals.show', $visit->id) . '" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Record Vitals
                            </a>';
                })
                ->rawColumns(['vitals_status', 'actions'])
                ->make(true);
        }

        return view('vitals.index');
    }
    /**
     * Display vitals form for a patient visit
     */
    public function show($visitId)
    {
        $visit = PatientVisit::findOrFail($visitId);

        // Get vital signs for this visit
        $vitals = VitalSigns::where('visit_id', $visitId)->latest('created_at')->first();
        $vitalsHistory = VitalSigns::where('visit_id', $visitId)->orderBy('created_at', 'desc')->get();

        return view('vitals.show', compact('visit', 'vitals', 'vitalsHistory'));
    }

    /**
     * Store vitals for a visit
     */
    public function store(Request $request, $visitId)
    {
        $visit = PatientVisit::findOrFail($visitId);

        $request->validate([
            'systolic_bp' => 'nullable|numeric|min:0|max:300',
            'diastolic_bp' => 'nullable|numeric|min:0|max:200',
            'pulse_rate' => 'nullable|numeric|min:0|max:220',
            'temperature' => 'nullable|numeric|min:30|max:50',
            'respiratory_rate' => 'nullable|numeric|min:0|max:60',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'bmi' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        $vitals = VitalSigns::create([
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient,
            'consultation_id' => $visit->consultation->id,
            'systolic_bp' => $request->systolic_bp,
            'diastolic_bp' => $request->diastolic_bp,
            'pulse_rate' => $request->pulse_rate,
            'temperature' => $request->temperature,
            'respiratory_rate' => $request->respiratory_rate,
            'oxygen_saturation' => $request->oxygen_saturation,
            'height' => $request->height,
            'weight' => $request->weight,
            'bmi' => $request->bmi,
            'notes' => $request->notes,
            'recorded_by' => Auth::id(),
            'recorded_at' => now()
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vital signs recorded successfully.',
                'vitals' => $vitals
            ]);
        }

        return redirect()->route('vitals.show', $visitId)
            ->with('success', 'Vital signs recorded successfully.');
    }

    /**
     * Update vitals
     */
    public function update(Request $request, $vitalsId)
    {
        $vitals = VitalSigns::findOrFail($vitalsId);

        $request->validate([
            'systolic_bp' => 'nullable|numeric|min:0|max:300',
            'diastolic_bp' => 'nullable|numeric|min:0|max:200',
            'pulse_rate' => 'nullable|numeric|min:0|max:220',
            'temperature' => 'nullable|numeric|min:30|max:50',
            'respiratory_rate' => 'nullable|numeric|min:0|max:60',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'bmi' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        $vitals->update([
            'systolic_bp' => $request->systolic_bp,
            'diastolic_bp' => $request->diastolic_bp,
            'pulse_rate' => $request->pulse_rate,
            'temperature' => $request->temperature,
            'respiratory_rate' => $request->respiratory_rate,
            'oxygen_saturation' => $request->oxygen_saturation,
            'height' => $request->height,
            'weight' => $request->weight,
            'bmi' => $request->bmi,
            'notes' => $request->notes,
            'updated_by' => Auth::id()
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vital signs updated successfully.',
                'vitals' => $vitals
            ]);
        }

        return redirect()->route('vitals.show', $vitals->visit_id)
            ->with('success', 'Vital signs updated successfully.');
    }

    /**
     * Get vital signs statistics for dashboard
     */
    public function statistics()
    {
        $stats = [
            'total_vitals_today' => VitalSigns::whereDate('created_at', today())->count(),
            'total_vitals_this_week' => VitalSigns::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'abnormal_vitals_today' => VitalSigns::whereDate('created_at', today())
                ->where(function($query) {
                    $query->where('systolic_bp', '>', 140)
                          ->orWhere('systolic_bp', '<', 90)
                          ->orWhere('diastolic_bp', '>', 90)
                          ->orWhere('diastolic_bp', '<', 60)
                          ->orWhere('pulse_rate', '>', 100)
                          ->orWhere('pulse_rate', '<', 60)
                          ->orWhere('temperature', '>', 37.5)
                          ->orWhere('oxygen_saturation', '<', 95);
                })->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get current vitals for a visit (for modal display)
     */
    public function getCurrentVitals($visitId)
    {
        $visit = PatientVisit::findOrFail($visitId);
        
        $vitals = VitalSigns::where('visit_id', $visitId)
            ->with('recordedBy')
            ->latest('created_at')
            ->first();

        if (!$vitals) {
            return response()->json([
                'success' => false,
                'message' => 'No vitals recorded for this visit',
                'vitals' => null
            ], 404);
        }

        // Add recorded by name to vitals object
        $vitals->recorded_by_name = $vitals->recordedBy ? $vitals->recordedBy->name : null;

        return response()->json([
            'success' => true,
            'vitals' => $vitals
        ]);
    }

    /**
     * Get vitals history for a visit (for modal display)
     */
    public function getVitalsHistory($visitId)
    {
        $visit = PatientVisit::findOrFail($visitId);
        
        $history = VitalSigns::where('visit_id', $visitId)
            ->with('recordedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add recorded by name to each vitals record
        $history->each(function($vitals) {
            $vitals->recorded_by_name = $vitals->recordedBy ? $vitals->recordedBy->name : null;
        });

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }
}
