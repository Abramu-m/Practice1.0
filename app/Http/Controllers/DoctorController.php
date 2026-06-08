<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'designationInfo', 'creator', 'visits']);

        // Search functionality
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('specialization', 'like', "%{$search}%")
                  ->orWhere('mct_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('designationInfo', function($designationQuery) use ($search) {
                      $designationQuery->where('description', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status_filter') && $request->status_filter !== '') {
            $query->where('status', $request->status_filter);
        }

        // Pagination (10 per page)
        $doctors = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get designations for filter
        $designations = \App\Models\Designation::all();

        return view('doctors.index', compact('doctors', 'designations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $users = \App\Models\User::whereDoesntHave('doctor')
                    ->where('is_verified', true)
                    ->where('role', 'doctor')
                    ->get();
        $designations = \App\Models\Designation::all();
        
        // If user_id is provided, pre-select the user
        $selectedUser = null;
        if ($request->has('user_id')) {
            $selectedUser = \App\Models\User::where('role', 'doctor')
                                           ->where('is_verified', true)
                                           ->whereDoesntHave('doctor')
                                           ->find($request->user_id);
        }
        
        return view('doctors.create', compact('users', 'designations', 'selectedUser'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id|unique:doctors,doctor_id',
            'designation' => 'required|exists:designations,designation_code',
            'specialization' => 'required|string|max:100',
            'mct_number' => 'nullable|string|max:50|unique:doctors,mct_number',
            'drsignature' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $doctorData = $request->all();
        $doctorData['created_by'] = Auth::id();

        Doctor::create($doctorData);

        return redirect()->route('doctors.index')
                         ->with('success', 'Doctor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'designationInfo', 'creator', 'visits.patientInfo']);
        return view('doctors.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $designations = \App\Models\Designation::all();
        return view('doctors.edit', compact('doctor', 'designations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'designation' => 'required|exists:designations,designation_code',
            'specialization' => 'required|string|max:100',
            'mct_number' => 'nullable|string|max:50|unique:doctors,mct_number,' . $doctor->doctor_id . ',doctor_id',
            'drsignature' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $doctor->update($request->all());

        return redirect()->route('doctors.index')
                         ->with('success', 'Doctor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('doctors.index')
                         ->with('success', 'Doctor deleted successfully.');
    }
}
