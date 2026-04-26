<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCategory;
use App\Models\NhifMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = Patient::with(['category', 'creator', 'visits', 'activeVisit.visitType']);

            // Apply category filter
            if ($request->has('category_filter') && $request->category_filter != '') {
                $query->where('patient_category', $request->category_filter);
            }

            // Apply status filter
            if ($request->has('status_filter') && $request->status_filter != '') {
                $query->where('status', $request->status_filter);
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = trim($request->search['value']);
                        
                        // If search looks like an MR number, extract and match id
                        if (preg_match('/MR-\d{4}-(\d+)/', $search, $matches)) {
                            $query->where('id', intval($matches[1]));
                        } elseif (ctype_digit($search) && strlen($search) <= 6) {
                            // Treat short all-digit searches (likely an ID) as exact id lookup
                            $query->where('id', intval($search));
                        } else {
                            // Fallback: broad LIKE search across several fields
                            $query->where(function($q) use ($search) {
                                $q->where('first_name', 'like', '%' . $search . '%')
                                  ->orWhere('last_name', 'like', '%' . $search . '%')
                                  ->orWhere('middle_name', 'like', '%' . $search . '%')
                                  ->orWhere('contact', 'like', '%' . $search . '%')
                                  ->orWhere('nida', 'like', '%' . $search . '%')
                                  ->orWhere('card_number', 'like', '%' . $search . '%');
                            });
                        }
                    }
                })
                ->addIndexColumn()
                ->addColumn('full_name', function ($patient) {
                    return $patient->full_name;
                })
                ->addColumn('gender', function ($patient) {
                    return ucfirst($patient->gender);
                })
                ->addColumn('date_of_birth', function ($patient) {
                    return $patient->date_of_birth->format('d/m/Y');
                })
                ->addColumn('contact', function ($patient) {
                    return $patient->contact ?? 'N/A';
                })
                ->addColumn('category', function ($patient) {
                    $html = $patient->category->description ?? 'N/A';
                    if (!empty($patient->card_number)) {
                        $html .= '<br><small class="text-muted">Card: ' . e($patient->card_number) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('visits', function ($patient) {
                    return '<span class="badge bg-info" style="color: black">' . $patient->visits->count() . ' visit(s)</span>';
                })
                ->addColumn('status', function ($patient) {
                    if ($patient->status == 'active') {
                        return '<span class="badge bg-success" style="color: black">Active</span>';
                    } else {
                        return '<span class="badge bg-danger" style="color: black">Inactive</span>';
                    }
                })
                ->addColumn('actions', function ($patient) {
                    return view('patients._actions', compact('patient'))->render();
                })
                ->rawColumns(['category', 'visits', 'status', 'actions'])
                ->make(true);
        }

        // Regular page load - just return the view with categories
        $categories = PatientCategory::where('is_active', true)->get();
        return view('patients.index', compact('categories'));
    }

    /**
     * Format LatestAuthorization or similar NHIF field into: "Facility: <name>; Date: YYYY-MM-DD; Status: Accepted;"
     */
    private function formatAuthorizationStatus($latestAuth)
    {
        if (empty($latestAuth)) return null;

        $facility = trim($latestAuth);
        $dateFormatted = 'N/A';

        // Try patterns like: "Facility Name on August 11,2025" or "Facility Name on August 11, 2025"
        if (preg_match('/^(.*?)\s+on\s+([A-Za-z]+\s+\d{1,2},\s*\d{4})/i', $latestAuth, $m)) {
            $facility = trim($m[1]);
            try {
                $dateFormatted = \Carbon\Carbon::parse($m[2])->format('Y-m-d');
            } catch (\Exception $e) {
                $dateFormatted = 'N/A';
            }
        } elseif (preg_match('/^(.*?)\s+on\s+(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $latestAuth, $m)) {
            // handle "11 August 2025" style
            $facility = trim($m[1]);
            try {
                $dateFormatted = \Carbon\Carbon::parse($m[2])->format('Y-m-d');
            } catch (\Exception $e) {
                $dateFormatted = 'N/A';
            }
        }

        return 'Facility: ' . $facility . '; Date: ' . $dateFormatted . '; Status: Accepted;';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = PatientCategory::where('is_active', true)->get();
        return view('patients.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get the selected patient category to determine validation rules
        $patientCategory = PatientCategory::find($request->patient_category);
        
        $validationRules = [
            'first_name' => 'required|string|max:30',
            'middle_name' => 'nullable|string|max:30',
            'last_name' => 'required|string|max:30',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'contact' => 'nullable|string|max:100',
            'residence' => 'nullable|string|max:30',
            'occupation' => 'nullable|string|max:90',
            'nida' => 'nullable|string|max:32|unique:patients,nida',
            'patient_category' => 'required|exists:patient_categories,id',
            'card_number' => 'nullable|string|max:30',
            'mtuha_new' => 'required|in:Yes,No',
            'status' => 'required|in:active,inactive'
        ];
        
        // Add insurance-specific validation rules if category type is insurance
        if ($patientCategory && $patientCategory->type === 'insurance') {
            $validationRules = array_merge($validationRules, [
                'membership_number' => 'nullable|string|max:30',
                'vote' => 'nullable|string|max:30',
                'SchemeID' => 'nullable|integer',
                'ProductCode' => 'nullable|string|max:30',
                'PackageID' => 'nullable|integer',
                'HasSupplementary' => 'required|in:Yes,No',
                'SchemeName' => 'nullable|string|max:90',
            ]);
        } else {
            // For cash patients, HasSupplementary is not required
            $validationRules['HasSupplementary'] = 'nullable|in:Yes,No';
        }
        
        $request->validate($validationRules);
        
        $patient = new Patient();
        $patient->fill($request->all());
        $patient->created_by = Auth::id();
        $patient->save();

        // If patient category is NHIF (determined by new flags/code) and card number provided,
        // create or update an NhifMember record linked to this patient. Map additional NHIF fields
        if ($patient->card_number) {
            $isNhifCategory = false;
            if ($patientCategory) {
                // prefer explicit code, fallback to description
                $isNhifCategory = ($patientCategory->code === 2) || (strtolower(trim($patientCategory->description ?? '')) === 'nhif');
            }

            if ($isNhifCategory) {
                    // Prefer NHIF response payload if supplied from verification. Be tolerant of
                    // JSON string, HTML-encoded JSON, or individual NHIF fields posted directly.
                    $nhifData = null;
                    if ($request->has('nhif_response')) {
                        $raw = $request->input('nhif_response');
                        if (is_array($raw)) {
                            $nhifData = $raw;
                        } else {
                            // Try JSON decode
                            $decoded = json_decode($raw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $nhifData = $decoded;
                            } else {
                                // try HTML entity decode then json
                                $decoded2 = json_decode(html_entity_decode($raw), true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                                    $nhifData = $decoded2;
                                }
                            }
                        }
                    }

                    // If we couldn't get a payload, attempt to build nhifData from individual request fields
                    if (empty($nhifData)) {
                        $possible = ['LatestAuthorization','AuthorizationNo','EmployerNo','SchemeID','ProductCode','Remarks','CardStatus','CardNo','MembershipNo','CHNationalID','FirstName','MiddleName','LastName','Gender','DateOfBirth','ExpiryDate'];
                        $built = [];
                        foreach ($possible as $k) {
                            if ($request->has($k)) $built[$k] = $request->input($k);
                            // also check lowercase keys
                            $lk = strtolower($k);
                            if ($request->has($lk)) $built[$k] = $request->input($lk);
                        }
                        if (!empty($built)) $nhifData = $built;
                    }

                    NhifMember::updateOrCreate(
                        ['patient_id' => $patient->id],
                        array_filter([
                            'card_no' => $patient->card_number,
                            'first_name' => $patient->first_name,
                            'middle_name' => $patient->middle_name,
                            'last_name' => $patient->last_name,
                            'full_name' => $patient->full_name,
                            'gender' => $patient->gender,
                            'date_of_birth' => $patient->date_of_birth,
                            'authorization_no' => $nhifData['AuthorizationNo'] ?? $patient->membership_number ?? null,
                            'authorization_status' => $this->formatAuthorizationStatus($nhifData['authorization_status'] ?? $nhifData['LatestAuthorization'] ?? null),
                            // authorization_status is derived from LatestAuthorization when present, or from nhifData['authorization_status'] if provided
                            'authorization_status' => $this->formatAuthorizationStatus($nhifData['authorization_status'] ?? $nhifData['LatestAuthorization'] ?? null),
                            'employer_no' => $nhifData['EmployerNo'] ?? $patient->vote ?? null,
                            'scheme_id' => $nhifData['SchemeID'] ?? $patient->SchemeID ?? null,
                            'product_code' => $nhifData['ProductCode'] ?? $patient->ProductCode ?? null,
                            'remarks' => $nhifData['Remarks'] ?? $patient->SchemeName ?? null,
                            'verification_date' => now(),
                            'verified_by' => Auth::id(),
                            'card_status' => $nhifData['CardStatus'] ?? 'Active'
                        ], function($value) { return $value !== null; })
                    );
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Patient created successfully',
                'data' => [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'card_number' => $patient->card_number,
                ],
                'redirect_url' => route('patients.index', ['search' => $patient->id])
            ]);
        }

        // For normal (non-AJAX) creates, redirect to the patients index filtered to the newly created patient
        return redirect()->route('patients.index', ['search' => $patient->id])->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        $patient->load(['category', 'creator']);
        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $categories = PatientCategory::where('is_active', true)->get();
        return view('patients.edit', compact('patient', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        // Get the selected patient category to determine validation rules
        $patientCategory = PatientCategory::find($request->patient_category);
        
        $validationRules = [
            'first_name' => 'required|string|max:30',
            'middle_name' => 'nullable|string|max:30',
            'last_name' => 'required|string|max:30',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'contact' => 'nullable|string|max:100',
            'residence' => 'nullable|string|max:30',
            'occupation' => 'nullable|string|max:90',
            'nida' => 'nullable|string|max:32|unique:patients,nida,' . $patient->id,
            'patient_category' => 'required|exists:patient_categories,id',
            'card_number' => 'nullable|string|max:30',
            'mtuha_new' => 'required|in:Yes,No',
            'status' => 'required|in:active,inactive'
        ];
        
        // Add insurance-specific validation rules if category type is insurance
        if ($patientCategory && $patientCategory->type === 'insurance') {
            $validationRules = array_merge($validationRules, [
                'membership_number' => 'nullable|string|max:30',
                'vote' => 'nullable|string|max:30',
                'SchemeID' => 'nullable|integer',
                'ProductCode' => 'nullable|string|max:30',
                'PackageID' => 'nullable|integer',
                'HasSupplementary' => 'required|in:Yes,No',
                'SchemeName' => 'nullable|string|max:90',
            ]);
        } else {
            // For cash patients, HasSupplementary is not required
            $validationRules['HasSupplementary'] = 'nullable|in:Yes,No';
        }
        
        $request->validate($validationRules);

        $patient->fill($request->all());
        $patient->save();

        // If patient category is NHIF and card number provided, create or update NhifMember
        $patientCategory = PatientCategory::find($request->patient_category);
        if ($patient->card_number) {
            $isNhifCategory = false;
            if ($patientCategory) {
                // prefer explicit code, fallback to description
                $isNhifCategory = (strtolower(trim($patientCategory->code ?? '')) === 'nhif') || (strtolower(trim($patientCategory->description ?? '')) === 'nhif');
            }

            if ($isNhifCategory) {
                // parse nhif_response similar to store()
                $nhifData = null;
                if ($request->has('nhif_response')) {
                    $raw = $request->input('nhif_response');
                    if (is_array($raw)) {
                        $nhifData = $raw;
                    } else {
                        $decoded = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $nhifData = $decoded;
                        } else {
                            $decoded2 = json_decode(html_entity_decode($raw), true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                                $nhifData = $decoded2;
                            }
                        }
                    }
                }

                if (empty($nhifData)) {
                    $possible = ['LatestAuthorization','AuthorizationNo','EmployerNo','SchemeID','ProductCode','Remarks','CardStatus','CardNo','MembershipNo','CHNationalID'];
                    $built = [];
                    foreach ($possible as $k) {
                        if ($request->has($k)) $built[$k] = $request->input($k);
                        $lk = strtolower($k);
                        if ($request->has($lk)) $built[$k] = $request->input($lk);
                    }
                    if (!empty($built)) $nhifData = $built;
                }

                NhifMember::updateOrCreate(
                    ['patient_id' => $patient->id],
                    array_filter([
                        'card_no' => $patient->card_number,
                        'first_name' => $patient->first_name,
                        'middle_name' => $patient->middle_name,
                        'last_name' => $patient->last_name,
                        'full_name' => $patient->full_name,
                        'gender' => $patient->gender,
                        'date_of_birth' => $patient->date_of_birth,
                        'authorization_no' => $nhifData['AuthorizationNo'] ?? $patient->membership_number ?? null,
                        'authorization_status' => $this->formatAuthorizationStatus($nhifData['authorization_status'] ?? $nhifData['LatestAuthorization'] ?? null),
                        'employer_no' => $nhifData['EmployerNo'] ?? $patient->vote ?? null,
                        'scheme_id' => $nhifData['SchemeID'] ?? $patient->SchemeID ?? null,
                        'product_code' => $nhifData['ProductCode'] ?? $patient->ProductCode ?? null,
                        'remarks' => $nhifData['Remarks'] ?? $patient->SchemeName ?? null,
                        'verification_date' => now(),
                        'verified_by' => Auth::id(),
                        'card_status' => $nhifData['CardStatus'] ?? 'Active'
                    ], function($value) { return $value !== null; })
                );
            }
        }

        return redirect()->route('patients.index')->with('success', 'Patient updated successfully.');
    }

    /**
     * AJAX endpoint for patient search (used by Select2)
     */
    public function search(Request $request)
    {
        $term = $request->input('q', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = Patient::query()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name');

        // Apply search filter
        if ($term) {
            $query->where(function($q) use ($term) {
                // Check if it looks like an MR number
                if (preg_match('/MR-\d{4}-(\d+)/', $term, $matches)) {
                    $q->where('id', intval($matches[1]));
                } elseif (ctype_digit($term) && strlen($term) <= 6) {
                    // Treat short all-digit searches as ID lookup
                    $q->where('id', intval($term));
                } else {
                    // Search across multiple fields
                    $q->where('first_name', 'like', '%' . $term . '%')
                      ->orWhere('last_name', 'like', '%' . $term . '%')
                      ->orWhere('middle_name', 'like', '%' . $term . '%')
                      ->orWhere('contact', 'like', '%' . $term . '%')
                      ->orWhere('nida', 'like', '%' . $term . '%')
                      ->orWhere('card_number', 'like', '%' . $term . '%');
                }
            });
        }

        // Get total count for pagination
        $total = $query->count();

        // Get paginated results
        $patients = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format for Select2
        $results = $patients->map(function($patient) {
            return [
                'id' => $patient->id,
                'text' => $patient->full_name . ' - ' . ($patient->contact ?? 'No contact'),
                'full_name' => $patient->full_name,
                'contact' => $patient->contact,
                'mr_number' => $patient->mr_number,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        // Only admins and super admins can delete patients
        $user = Auth::user();
        if (!$user || (!$user->is_admin && !$user->is_super)) {
            abort(403, 'Unauthorized. Only administrators can delete patients.');
        }

        $patient->delete();
        return redirect()->route('patients.index')->with('success', 'Patient deleted successfully.');
    }

    /**
     * Get investigations partial view for a visit (for patients index AJAX updates)
     */
    public function getVisitInvestigationsPartial($visitId)
    {
        try {
            $visit = \App\Models\PatientVisit::with(['patientInfo'])->findOrFail($visitId);
            
            // Get investigations for this visit with pricing
            $investigations = \App\Models\Investigation::where('visit_id', $visitId)
                ->with(['medicalService.serviceCategory', 'medicalService.currentPricing', 'doctor.user'])
                ->orderBy('ordered_at', 'desc')
                ->get();
            
            // Count only active (non-cancelled) investigations
            $activeCount = $investigations->where('status', '!=', 'cancelled')->count();
            
            // Render just the investigations part
            $html = view('patients.partials.visit_investigations', compact('investigations', 'visit'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $activeCount,
                'total_count' => $investigations->count()
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching visit investigations partial: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load investigations'
            ], 500);
        }
    }
}