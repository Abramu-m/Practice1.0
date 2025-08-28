<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCategory;
use App\Models\NhifMember;
use App\Models\NhifClaim;
use App\Models\NhifTariff;
use App\Services\NhifService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NhifController extends Controller
{
    protected $nhifService;

    public function __construct(NhifService $nhifService)
    {
        $this->nhifService = $nhifService;
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
     * Show NHIF integration dashboard
     */
    public function index()
    {
        $recentVerifications = NhifMember::with('patient', 'verifiedBy')
            ->latest('verification_date')
            ->take(10)
            ->get();

        $recentClaims = NhifClaim::with('patient', 'submittedBy')
            ->latest('submission_date')
            ->take(10)
            ->get();

        $stats = [
            'total_members' => NhifMember::count(),
            'active_members' => NhifMember::where('card_status', 'Active')->count(),
            'total_claims' => NhifClaim::count(),
            'submitted_claims' => NhifClaim::whereNotNull('submission_date')->count(),
        ];
        //patients
        // $patients = Patient::with('nhifMember')
        //     ->whereHas('nhifMember')
        //     ->latest()
        //     ->take(10)
        //     ->get();
        $patients = Patient::all();


        return view('nhif.index', compact('recentVerifications', 'recentClaims', 'stats', 'patients'));
    }

    /**
     * Show NHIF member verification page
     */
    public function verifyView()
    {
    // Provide patients list and today's verification stats to the view
    $patients = Patient::orderBy('first_name')->get();
    $todayTotal = NhifMember::whereDate('verification_date', now()->toDateString())->count();
    $todayActive = NhifMember::whereDate('verification_date', now()->toDateString())->where('card_status', 'Active')->count();

    return view('nhif.verify', compact('patients', 'todayTotal', 'todayActive'));
    }

    /**
     * Show NHIF tariffs synchronization page
     */
    public function tariffsView()
    {
        // Server-side search and pagination for NHIF tariffs
        $search = request('search');
        $tariffsQuery = NhifTariff::query();
        if ($search) {
            $tariffsQuery->where('item_code', 'like', "%{$search}%")
                         ->orWhere('item_name', 'like', "%{$search}%");
        }
        $tariffs = $tariffsQuery->orderBy('item_code')->paginate(25)->withQueryString();

    $lastSync = NhifTariff::latest('last_updated')->first();
    $totalTariffs = NhifTariff::count();
    $restrictedTariffs = NhifTariff::where('is_restricted', true)->count();
    $excludedTariffs = NhifTariff::where('is_excluded', true)->count();
    $schemes = NhifTariff::distinct('scheme_id')->count();

    return view('nhif.tariffs', compact('tariffs', 'lastSync', 'totalTariffs', 'restrictedTariffs', 'excludedTariffs', 'schemes'));
    }

    /**
     * Show NHIF claims management page
     */
    public function claimsView()
    {
        // Summary stats used by the claims view
        $stats = [
            'total_claims' => NhifClaim::count(),
            'draft_claims' => NhifClaim::where('claim_status', 'draft')->count(),
            'submitted_claims' => NhifClaim::where('claim_status', 'submitted')->count(),
            'total_amount' => NhifClaim::sum('total_amount_claimed'),
        ];

        // Recent patient visits that have NHIF members (for create claim select)
        $patientVisits = \App\Models\PatientVisit::with(['patient.nhifMember'])
            ->whereHas('patient.nhifMember')
            ->latest()
            ->take(50)
            ->get();

        // Draft claims for the "submit" select
        $draftClaims = NhifClaim::where('claim_status', 'draft')
            ->with('patient')
            ->get();

    // All claims listing (used by the table). Keep eager loaded patient relationship and paginate.
    $allClaims = NhifClaim::with('patient')->latest()->paginate(25);

        return view('nhif.claims', compact('stats', 'patientVisits', 'draftClaims', 'allClaims'));
    }

    /**
     * Show NHIF reports page
     */
    public function reportsView()
    {
        // Summary stats for the reports dashboard
        $stats = [
            'total_members' => NhifMember::count(),
            'active_members' => NhifMember::where('card_status', 'Active')->count(),
            'total_claims' => NhifClaim::count(),
            'claims_value' => NhifClaim::sum('total_amount_claimed'),
        ];

        // Recent activity
        $recentVerifications = NhifMember::with('patient', 'verifiedBy')
            ->latest('verification_date')
            ->take(5)
            ->get();

        $recentClaims = NhifClaim::with('patient', 'submittedBy')
            ->whereNotNull('submission_date')
            ->latest('submission_date')
            ->take(5)
            ->get();

        // Claims status distribution for charting
        $statusCounts = [
            'draft' => NhifClaim::where('claim_status', 'draft')->count(),
            'submitted' => NhifClaim::where('claim_status', 'submitted')->count(),
            'approved' => NhifClaim::where('claim_status', 'approved')->count(),
            'rejected' => NhifClaim::where('claim_status', 'rejected')->count(),
        ];

        // Claims trend (last 6 months)
        $claimsTrend = [];
        $claimsTrendLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = \Carbon\Carbon::now()->startOfMonth()->subMonths($i);
            $end = (clone $start)->endOfMonth();
            $label = $start->format('M');
            $claimsTrendLabels[] = $label;
            $claimsTrend[] = NhifClaim::whereBetween('submission_date', [$start->toDateString(), $end->toDateString()])->count();
        }

        // Verification trend (last 7 days)
        $verificationLabels = [];
        $verificationData = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = \Carbon\Carbon::today()->subDays($d);
            $verificationLabels[] = $day->format('D');
            $verificationData[] = NhifMember::whereDate('verification_date', $day->toDateString())->count();
        }

        return view('nhif.reports', compact(
            'stats',
            'recentVerifications',
            'recentClaims',
            'statusCounts',
            'claimsTrend',
            'claimsTrendLabels',
            'verificationLabels',
            'verificationData'
        ));
    }

    /**
     * Create a new claim
     */
    public function createClaim(Request $request)
    {
        $request->validate([
            'patient_visit_id' => 'required|exists:patient_visits,id',
            'claim_month' => 'required|integer|min:1|max:12',
            'claim_year' => 'required|integer|min:2020',
        ]);

        try {
            $visit = \App\Models\PatientVisit::with('patient.nhifMember')->findOrFail($request->patient_visit_id);
            
            if (!$visit->patient->nhifMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient does not have NHIF membership record'
                ], 400);
            }

            $claim = NhifClaim::create([
                'folio_id' => Str::uuid(),
                'claim_year' => $request->claim_year,
                'claim_month' => $request->claim_month,
                'folio_no' => $visit->id,
                'serial_no' => "SN{$visit->id}",
                'card_no' => $visit->patient->nhifMember->card_no,
                'patient_id' => $visit->patient->id,
                'patient_visit_id' => $visit->id,
                'authorization_no' => $visit->patient->nhifMember->authorization_no,
                'attendance_date' => $visit->created_at->format('Y-m-d'),
                'patient_type_code' => 'OUT',
                'practitioner_no' => config('nhif.practitioner_no', '12345'),
                'total_amount_claimed' => 0, // Will be calculated from items
                'claim_status' => 'draft',
                'facility_code' => config('nhif.facility_code'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim created successfully',
                'data' => $claim
            ]);

        } catch (\Exception $e) {
            Log::error('NHIF Claim Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the claim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate custom report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'report_type' => 'required|in:summary,claims,members,tariffs,financial',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        try {
            // This is a placeholder - implement actual report generation
            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'content' => '<p>Report content would be generated here based on the selected criteria.</p>',
                'download_url' => '/nhif/download-report/' . time()
            ]);

        } catch (\Exception $e) {
            Log::error('NHIF Report Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate quick report
     */
    public function quickReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:daily,weekly,monthly,pending-claims',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        try {
            // This is a placeholder - implement actual quick report generation
            $reportData = [
                'daily' => 'Daily NHIF activities report',
                'weekly' => 'Weekly NHIF summary report',
                'monthly' => 'Monthly NHIF financial report',
                'pending-claims' => 'Pending claims report'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Quick report generated successfully',
                'content' => '<p>' . $reportData[$request->type] . ' would be generated here.</p>',
                'download_url' => '/nhif/download-quick-report/' . $request->type
            ]);

        } catch (\Exception $e) {
            Log::error('NHIF Quick Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the quick report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export tariffs
     */
    public function exportTariffs()
    {
        try {
            $tariffs = NhifTariff::all();
            
            // This is a placeholder - implement actual export functionality
            $filename = 'nhif_tariffs_' . date('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($tariffs) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Item Code', 'Item Name', 'Unit Price', 'Scheme ID', 'Package ID', 'Status']);
                
                foreach ($tariffs as $tariff) {
                    fputcsv($file, [
                        $tariff->item_code,
                        $tariff->item_name,
                        $tariff->unit_price,
                        $tariff->scheme_id,
                        $tariff->package_id,
                        $tariff->is_excluded ? 'Excluded' : ($tariff->is_restricted ? 'Restricted' : 'Available')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('NHIF Tariffs Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export tariffs');
        }
    }

    /**
     * Export claims
     */
    public function exportClaims()
    {
        try {
            $claims = NhifClaim::with('patient')->get();
            
            $filename = 'nhif_claims_' . date('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($claims) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Folio No', 'Patient Name', 'Card No', 'Amount Claimed', 'Status', 'Submission Date']);
                
                foreach ($claims as $claim) {
                    fputcsv($file, [
                        $claim->folio_no,
                        $claim->patient->full_name ?? 'N/A',
                        $claim->card_no,
                        $claim->total_amount_claimed,
                        $claim->claim_status,
                        $claim->submission_date?->format('Y-m-d H:i:s') ?? 'Not submitted'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('NHIF Claims Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export claims');
        }
    }

    /**
     * Verify NHIF member
     */
    public function verifyMember(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
            'visit_type_id' => 'integer|min:1|max:4',
            'referral_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'override_emergency' => 'nullable|in:1',
        ]);

        try {
            $result = $this->nhifService->getCardDetails($request->card_number);

            if ($result['success']) {
                $nhifData = $result['data'];

                // Try to find patient by card number
                $patient = Patient::where('card_number', $nhifData['CardNo'])->first();

                // If patient not found return prefill data so the UI can open the patient creation form.
                if (! $patient) {
                    // Try to find a default insurance patient category id
                    $insuranceCategory = PatientCategory::where('is_active', true)->where('type', 'insurance')->first();

                    // Normalize gender value to expected form inputs (male|female|other)
                    $rawGender = $nhifData['Gender'] ?? null;
                    $gender = null;
                    if ($rawGender !== null) {
                        $g = strtolower(trim($rawGender));
                        if ($g === 'male') $gender = 'male';
                        elseif ($g === 'female') $gender = 'female';
                        elseif ($g === 'other' || $g === 'unspecified') $gender = 'other';
                    }

                    $prefill = [
                        'first_name' => $nhifData['FirstName'] ?? '',
                        'middle_name' => $nhifData['MiddleName'] ?? '',
                        'last_name' => $nhifData['LastName'] ?? '',
                        'date_of_birth' => isset($nhifData['DateOfBirth']) ? \Carbon\Carbon::parse($nhifData['DateOfBirth'])->format('Y-m-d') : null,
                        'gender' => $gender,
                        'card_number' => $nhifData['CardNo'] ?? '',
                        'membership_number' => $nhifData['MembershipNo'] ?? '',
                        'nida' => $nhifData['CHNationalID'] ?? null,
                        'SchemeID' => $nhifData['SchemeID'] ?? null,
                        'ProductCode' => $nhifData['ProductCode'] ?? null,
                        'PackageID' => $nhifData['PackageID'] ?? null,
                        'SchemeName' => $nhifData['SchemeName'] ?? null,
                        'occupation' => $nhifData['EmployerName'] ?? null,
                        'vote' => $nhifData['EmployerNo'] ?? null,
                        'contact' => $nhifData['Contact'] ?? null,
                        'patient_category' => PatientCategory::where('description', 'NHIF')->value('id')
                    ];

                    return response()->json([
                        'success' => true,
                        'patient_exists' => false,
                        'message' => 'Patient not found locally. Redirecting to patient creation form.',
                        'prefill' => $prefill,
                        'nhif_response' => $nhifData
                    ]);
                }

                // verification does not impose authorization restrictions; continue to store NHIF member info

                // Store or update NHIF member record and link to patient
                $nhifMember = NhifMember::updateOrCreate(
                    ['card_no' => $nhifData['CardNo']],
                    [
                        'card_status' => $nhifData['CardStatus'] ?? null,
                        'first_name' => $nhifData['FirstName'] ?? null,
                        'middle_name' => $nhifData['MiddleName'] ?? null,
                        'last_name' => $nhifData['LastName'] ?? null,
                        'full_name' => $nhifData['FullName'] ?? null,
                        'gender' => $nhifData['Gender'] ?? null,
                        'date_of_birth' => isset($nhifData['DateOfBirth']) ? 
                            \Carbon\Carbon::parse($nhifData['DateOfBirth'])->format('Y-m-d') : null,
                        'expiry_date' => isset($nhifData['ExpiryDate']) && $nhifData['ExpiryDate'] ? 
                            \Carbon\Carbon::parse($nhifData['ExpiryDate'])->format('Y-m-d') : null,
                            // Format authorization_status using helper to ensure consistent parsing
                            'authorization_status' => $this->formatAuthorizationStatus($nhifData['authorization_status'] ?? $nhifData['LatestAuthorization'] ?? null),
                        'authorization_no' => $nhifData['AuthorizationNo'] ?? null,
                        'employer_no' => $nhifData['EmployerNo'] ?? null,
                        'scheme_id' => $nhifData['SchemeID'] ?? null,
                        'product_code' => $nhifData['ProductCode'] ?? null,
                        'remarks' => $nhifData['Remarks'] ?? null,
                        'patient_id' => $patient->id,
                        'verification_date' => now(),
                        'verified_by' => Auth::id(),
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Member verified successfully',
                    'data' => $nhifMember,
                    'patient_exists' => true,
                    'patient' => $patient,
                    'nhif_response' => $nhifData,
                    'redirect_url' => route('patients.show', $patient->id)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? null
            ], 400);

        } catch (\Exception $e) {
            Log::error('NHIF Member Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get card details
     */
    public function getCardDetails(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
        ]);

        try {
            $result = $this->nhifService->getCardDetails($request->card_number);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('NHIF Card Details Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching card details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authorize NHIF member for service (uses the NHIF authorize endpoint)
     */
    public function authorize(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
            'visit_type_id' => 'required|integer|min:1|max:4',
            'referral_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'override_emergency' => 'nullable|in:1',
        ]);

        try {
            $card = $request->card_number;

            // Check existing NHIF member authorization for same-day at other facility
            $existingMember = NhifMember::where('card_no', $card)->first();
            if ($existingMember && $existingMember->authorization_status) {
                $existingFacility = $existingMember->getAuthorizationFacility();
                $existingDate = $existingMember->getAuthorizationDate();
                $isToday = false;
                if ($existingDate) {
                    try { $isToday = \Carbon\Carbon::parse($existingDate)->isToday(); } catch (\Exception $e) { $isToday = false; }
                }

                $currentFacility = config('nhif.facility_name') ?? config('nhif.facility_code') ?? null;
                if ($isToday && $existingFacility && $currentFacility && trim($existingFacility) !== trim($currentFacility)) {
                    $visitType = intval($request->input('visit_type_id', 0));
                    $override = $request->input('override_emergency');
                    if ($visitType !== 2 && $override !== '1') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Authorization blocked: patient already authorized today at another facility. Only Emergency (visit_type_id=2) is allowed unless emergency override is provided.'
                        ], 422);
                    }
                }
            }

            // Call NHIF authorize endpoint
            $result = $this->nhifService->authorizeCard($card, intval($request->visit_type_id), $request->referral_number ?? '', $request->remarks ?? '');

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Authorization failed',
                    'error' => $result['error'] ?? null,
                    'raw' => $result['data'] ?? null,
                ], 422);
            }

            $nhifData = $result['data'] ?? [];

            // Update or create NhifMember record with authorization result
            $nhifMember = NhifMember::updateOrCreate(
                ['card_no' => $nhifData['CardNo'] ?? $card],
                [
                    'card_status' => $nhifData['CardStatus'] ?? null,
                    'first_name' => $nhifData['FirstName'] ?? null,
                    'middle_name' => $nhifData['MiddleName'] ?? null,
                    'last_name' => $nhifData['LastName'] ?? null,
                    'full_name' => $nhifData['FullName'] ?? null,
                    'gender' => $nhifData['Gender'] ?? null,
                    'date_of_birth' => isset($nhifData['DateOfBirth']) && $nhifData['DateOfBirth'] ? \Carbon\Carbon::parse($nhifData['DateOfBirth'])->format('Y-m-d') : null,
                    'expiry_date' => isset($nhifData['ExpiryDate']) && $nhifData['ExpiryDate'] ? \Carbon\Carbon::parse($nhifData['ExpiryDate'])->format('Y-m-d') : null,
                    'authorization_status' => isset($nhifData['AuthorizationStatus']) ? ('Facility: ' . ($nhifData['AuthorizationFacility'] ?? '') . '; Date: ' . (isset($nhifData['AuthorizationDate']) ? \Carbon\Carbon::parse($nhifData['AuthorizationDate'])->format('Y-m-d') : now()->format('Y-m-d')) . '; Status: ' . $nhifData['AuthorizationStatus'] . ';') : null,
                    'authorization_no' => $nhifData['AuthorizationNo'] ?? null,
                    'employer_no' => $nhifData['EmployerNo'] ?? null,
                    'scheme_id' => $nhifData['SchemeID'] ?? null,
                    'product_code' => $nhifData['ProductCode'] ?? null,
                    'remarks' => $nhifData['Remarks'] ?? null,
                    'verification_date' => now(),
                    'verified_by' => Auth::id(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Authorization result received',
                'data' => $nhifData,
                'nhif_member' => $nhifMember,
            ], 200);

        } catch (\Exception $e) {
            Log::error('NHIF Authorization Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during authorization',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download and sync tariffs
     */
    public function syncTariffs(Request $request)
    {
        $request->validate([
            'facility_code' => 'required|string',
        ]);

        try {
            $result = $this->nhifService->downloadTariffsWithoutExcludedService($request->facility_code);

            if ($result['success']) {
                $data = $result['data'];
                $pricePackages = $data['PricePackage'] ?? [];
                $excludedServices = $data['ExcludedServices'] ?? [];

                $syncedCount = 0;

                // Sync price packages
                foreach ($pricePackages as $package) {
                    NhifTariff::updateOrCreate(
                        [
                            'facility_code' => $request->facility_code,
                            'item_code' => $package['ItemCode'],
                            'scheme_id' => $package['SchemeID'],
                        ],
                        [
                            'item_name' => $package['ItemName'] ?? null,
                            'package_id' => $package['PackageID'] ?? null,
                            'unit_price' => $package['UnitPrice'] ?? 0,
                            'is_restricted' => $package['IsRestricted'] ?? false,
                            'is_excluded' => false,
                            'last_updated' => now(),
                        ]
                    );
                    $syncedCount++;
                }

                // Mark excluded services
                foreach ($excludedServices as $excluded) {
                    NhifTariff::where('facility_code', $request->facility_code)
                        ->where('item_code', $excluded['ItemCode'])
                        ->where('scheme_id', $excluded['SchemeID'])
                        ->update([
                            'is_excluded' => true,
                            'excluded_for_products' => explode(',', $excluded['ExcludedForProducts'] ?? ''),
                        ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Successfully synced {$syncedCount} tariff items",
                    'synced_count' => $syncedCount
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? null
            ], 400);

        } catch (\Exception $e) {
            Log::error('NHIF Tariffs Sync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during tariffs synchronization',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit claim to NHIF
     */
    public function submitClaim(Request $request)
    {
        $request->validate([
            'patient_visit_id' => 'required|exists:patient_visits,id',
        ]);

        try {
            // Build claim data from patient visit
            $claimData = $this->buildClaimData($request->patient_visit_id);
            
            $result = $this->nhifService->submitClaimToNHIF($claimData);

            if ($result['success']) {
                // Update claim record as submitted
                $claim = NhifClaim::where('patient_visit_id', $request->patient_visit_id)->first();
                if ($claim) {
                    $claim->update([
                        'claim_status' => 'submitted',
                        'submission_date' => now(),
                        'response_data' => $result['data'],
                        'submitted_by' => Auth::id(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Claim submitted successfully',
                    'data' => $result['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? null
            ], 400);

        } catch (\Exception $e) {
            Log::error('NHIF Claim Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during claim submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build claim data from patient visit
     */
    private function buildClaimData($patientVisitId)
    {
        // This is a simplified version - you'll need to adapt this based on your data structure
        $visit = \App\Models\PatientVisit::with(['patient', 'consultations', 'prescriptions'])
            ->findOrFail($patientVisitId);

        $patient = $visit->patient;
        $nhifMember = $patient->nhifMember;

        if (!$nhifMember) {
            throw new \Exception('Patient does not have NHIF membership record');
        }

        $folioId = Str::uuid();
        
        return [
            'entities' => [
                [
                    'FolioID' => $folioId,
                    'ClaimYear' => now()->year,
                    'ClaimMonth' => now()->month,
                    'FolioNo' => $visit->id,
                    'SerialNo' => "SN{$visit->id}",
                    'CardNo' => $nhifMember->card_no,
                    'FirstName' => $patient->first_name,
                    'LastName' => $patient->last_name,
                    'Gender' => $patient->gender,
                    'DateOfBirth' => $patient->date_of_birth?->format('Y-m-d\TH:i:s.v'),
                    'Age' => $patient->date_of_birth?->age ?? 0,
                    'TelephoneNo' => $patient->contact,
                    'AuthorizationNo' => $nhifMember->authorization_no,
                    'AttendanceDate' => $visit->created_at->format('Y-m-d\TH:i:s'),
                    'PatientTypeCode' => 'OUT', // Adjust based on your logic
                    'PractitionerNo' => config('nhif.practitioner_no', '12345'),
                    'CreatedBy' => Auth::user()->name,
                    'DateCreated' => now()->format('Y-m-d\TH:i:s.v'),
                    'FolioDiseases' => [], // Add diseases from consultations
                    'FolioItems' => [], // Add services and medications
                ]
            ]
        ];
    }
}
