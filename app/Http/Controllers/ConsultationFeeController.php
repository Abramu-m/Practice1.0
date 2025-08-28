<?php

namespace App\Http\Controllers;

use App\Models\ConsultationFee;
use App\Models\Doctor;
use App\Models\PatientCategory;
use App\Models\VisitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ConsultationFee::with(['doctor.user', 'patientCategory', 'visitType', 'creator']);

        // Search functionality
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('fee_amount', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('doctor.user', function($doctorQuery) use ($search) {
                      $doctorQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('patientCategory', function($categoryQuery) use ($search) {
                      $categoryQuery->where('description', 'like', "%{$search}%");
                  })
                  ->orWhereHas('visitType', function($visitTypeQuery) use ($search) {
                      $visitTypeQuery->where('description', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status_filter') && $request->status_filter !== '') {
            $query->where('status', $request->status_filter);
        }

        // Filter by doctor
        if ($request->has('doctor_filter') && $request->doctor_filter !== '') {
            $query->where('doctor_id', $request->doctor_filter);
        }

        $consultationFees = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get doctors for filter dropdown
        $doctors = Doctor::with('user')->where('status', 1)->get();

        return view('consultation_fees.index', compact('consultationFees', 'doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctors = Doctor::with('user')->where('status', 1)->get();
        $patientCategories = PatientCategory::all();
        $visitTypes = VisitType::all();
        
        return view('consultation_fees.create', compact('doctors', 'patientCategories', 'visitTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'visit_type_id' => 'required|exists:visit_types,id',
            'fee_amount' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:0,1'
        ], [
            'doctor_id.required' => 'Please select a doctor.',
            'patient_category_id.required' => 'Please select a patient category.',
            'visit_type_id.required' => 'Please select a visit type.',
            'fee_amount.required' => 'Fee amount is required.',
            'fee_amount.numeric' => 'Fee amount must be a valid number.',
            'fee_amount.min' => 'Fee amount cannot be negative.',
            'fee_amount.max' => 'Fee amount cannot exceed 999,999.99.'
        ]);

        // Check for duplicate combination
        $existing = ConsultationFee::where('doctor_id', $request->doctor_id)
                                  ->where('patient_category_id', $request->patient_category_id)
                                  ->where('visit_type_id', $request->visit_type_id)
                                  ->first();

        if ($existing) {
            return back()->withErrors(['duplicate' => 'A consultation fee already exists for this combination of doctor, patient category, and visit type.'])
                         ->withInput();
        }

        ConsultationFee::create([
            'doctor_id' => $request->doctor_id,
            'patient_category_id' => $request->patient_category_id,
            'visit_type_id' => $request->visit_type_id,
            'fee_amount' => $request->fee_amount,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('consultation_fees.index')
                         ->with('success', 'Consultation fee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultationFee $consultationFee)
    {
        $consultationFee->load(['doctor.user', 'patientCategory', 'visitType', 'creator']);
        return view('consultation_fees.show', compact('consultationFee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConsultationFee $consultationFee)
    {
        $doctors = Doctor::with('user')->where('status', 1)->get();
        $patientCategories = PatientCategory::all();
        $visitTypes = VisitType::all();
        
        return view('consultation_fees.edit', compact('consultationFee', 'doctors', 'patientCategories', 'visitTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConsultationFee $consultationFee)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'patient_category_id' => 'required|exists:patient_categories,id',
            'visit_type_id' => 'required|exists:visit_types,id',
            'fee_amount' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:0,1'
        ]);

        // Check for duplicate combination (excluding current record)
        $existing = ConsultationFee::where('doctor_id', $request->doctor_id)
                                  ->where('patient_category_id', $request->patient_category_id)
                                  ->where('visit_type_id', $request->visit_type_id)
                                  ->where('id', '!=', $consultationFee->id)
                                  ->first();

        if ($existing) {
            return back()->withErrors(['duplicate' => 'A consultation fee already exists for this combination of doctor, patient category, and visit type.'])
                         ->withInput();
        }

        $consultationFee->update([
            'doctor_id' => $request->doctor_id,
            'patient_category_id' => $request->patient_category_id,
            'visit_type_id' => $request->visit_type_id,
            'fee_amount' => $request->fee_amount,
            'description' => $request->description,
            'status' => $request->status
        ]);

        return redirect()->route('consultation_fees.index')
                         ->with('success', 'Consultation fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsultationFee $consultationFee)
    {
        $consultationFee->delete();

        return redirect()->route('consultation_fees.index')
                         ->with('success', 'Consultation fee deleted successfully.');
    }

    /**
     * AJAX endpoint to get fee for specific combination
     */
    public function getFee(Request $request)
    {
        $fee = ConsultationFee::getFee(
            $request->doctor_id,
            $request->patient_category_id,
            $request->visit_type_id
        );

        return response()->json([
            'fee' => $fee ? $fee->fee_amount : null,
            'description' => $fee ? $fee->description : null
        ]);
    }
}
