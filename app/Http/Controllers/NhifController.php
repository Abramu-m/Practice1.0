<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientCategory;
use App\Models\Facility;
use App\Models\NhifMember;
use App\Models\NhifClaim;
use App\Models\NhifClaimBatch;
use App\Models\NhifSetting;
use App\Models\NhifTariff;
use App\Jobs\SubmitNhifClaimJob;
use App\Jobs\SyncNhifTariffsJob;
use App\Services\NhifService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
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

        // Recent patient visits that have NHIF members and no existing claim
        $patientVisits = \App\Models\PatientVisit::with(['patientInfo.nhifMember'])
            ->whereHas('patientInfo.nhifMember')
            ->whereNotIn('id', NhifClaim::whereNotNull('patient_visit_id')->pluck('patient_visit_id'))
            ->latest()
            ->take(50)
            ->get();

        // Draft claims for the "submit" select
        $draftClaims = NhifClaim::where('claim_status', 'draft')
            ->with('patient')
            ->get();

        // All claims listing (used by the table). Keep eager loaded patient relationship and paginate.
        $allClaims = NhifClaim::with('patient')->latest()->paginate(25);

        //Get batches sorted by newest dates for the batch submission section
        $batches = NhifClaimBatch::orderBy('claim_year', 'desc')
            ->orderBy('claim_month', 'desc')
            ->paginate(12);

        return view('nhif.claims', compact('stats', 'patientVisits', 'draftClaims', 'allClaims', 'batches'));
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
     * Show NHIF integration settings (mode toggle + credentials)
     */
    public function settingsView()
    {
        $settings = NhifSetting::current();
        $facility = Facility::current();

        $mode = $settings->mode;
        $urlConfig = config('nhif.url', []);

        // Read-only summary of the API endpoints the active mode points at
        $endpoints = [
            'Base URL'             => $urlConfig[$mode] ?? null,
            'Token'                => $urlConfig['token'][$mode] ?? null,
            'Member Verification'  => $urlConfig['member_verification'][$mode] ?? null,
            'Authorize Card'       => $urlConfig['authorize'][$mode] ?? null,
            'Tariffs'              => $urlConfig['tariffs'] ?? null,
            'Submit Claim'         => $urlConfig['claim'] ?? null,
            'Get Submitted Claims' => $urlConfig['claim_submitted'] ?? null,
            'Referral'             => $urlConfig['referral'] ?? null,
            'Pre-approved Services'=> $urlConfig['pre_approved'] ?? null,
        ];

        return view('nhif.settings', compact('settings', 'facility', 'endpoints'));
    }

    /**
     * Update NHIF integration settings
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'mode'     => 'required|in:test,production',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        // Leave the stored password untouched if the field is left blank
        if ($data['password'] === null || $data['password'] === '') {
            unset($data['password']);
        }

        $settings = NhifSetting::current();
        $settings->fill($data)->save();

        return redirect()->route('nhif.settings')->with('success', 'NHIF settings updated successfully.');
    }

    /**
     * Create a new claim
     */
    public function createClaim(Request $request)
    {
        $request->validate([
            'patient_visit_id' => 'required|exists:patient_visits,id',
        ]);

        // Pre-creation validation
        $verification = $this->nhifService->verifyClaimData((int) $request->patient_visit_id);
        if (!$verification['is_valid']) {
            return response()->json([
                'success'  => false,
                'message'  => 'Claim validation failed',
                'errors'   => $verification['errors'],
                'warnings' => $verification['warnings'],
            ], 422);
        }

        try {
            // Build claim data (now in NhifService, derives month/year from visit date)
            $claimData = $this->nhifService->buildClaimData((int) $request->patient_visit_id);

            $claimMonth   = $claimData['ClaimMonth'];
            $claimYear    = $claimData['ClaimYear'];
            $facilityCode = config('nhif.facility_code');

            // Find or create a batch for this month/year
            $batch = NhifClaimBatch::firstOrCreate(
                ['claim_month' => $claimMonth, 'claim_year' => $claimYear],
                [
                    'claim_no'     => 'NHIF/' . $facilityCode . '/' . strtoupper(
                        \Carbon\Carbon::createFromDate($claimYear, $claimMonth, 1)->format('M-Y')
                    ),
                    'facility_code' => $facilityCode,
                ]
            );

            // Create the claim record
            $claim = NhifClaim::create([
                'nhif_claim_batch_id'  => $batch->id,
                'folio_id'             => $claimData['FolioID'],
                'claim_year'           => $claimYear,
                'claim_month'          => $claimMonth,
                'folio_no'             => $claimData['FolioNo'],
                'serial_no'            => $claimData['SerialNo'],
                'card_no'              => $claimData['CardNo'],
                'patient_id'           => $claimData['PatientFileNo'],
                'patient_visit_id'     => $request->patient_visit_id,
                'authorization_no'     => $claimData['AuthorizationNo'],
                'attendance_date'      => $claimData['AttendanceDate'],
                'patient_type_code'    => 'OUT',
                'practitioner_no'      => $claimData['PractitionerNo'],
                'total_amount_claimed' => $claimData['total_amount'],
                'claim_status'         => 'draft',
                'facility_code'        => $facilityCode,
            ]);

            // Diseases
            foreach ($claimData['FolioDiseases'] as $disease) {
                $claim->claimDiseases()->create([
                    'nhif_claim_id'    => $claim->id,
                    'folio_disease_id' => Str::uuid(),
                    'disease_code'     => $disease['DiseaseCode'],
                    'disease_name'     => null,
                    'remarks'          => $disease['Status'] ?? null,
                ]);
            }

            // Items
            foreach ($claimData['FolioItems'] as $item) {
                $claim->claimItems()->create([
                    'nhif_claim_id'     => $claim->id,
                    'folio_item_id'     => Str::uuid(),
                    'item_code'         => $item['ItemCode'] ?: $item['ItemName'],
                    'item_name'         => $item['ItemName'],
                    'other_details'     => $item['OtherDetails'],
                    'item_quantity'     => $item['ItemQuantity'],
                    'unit_price'        => $item['UnitPrice'],
                    'amount_claimed'    => $item['AmountClaimed'],
                    'approval_ref_no'   => $item['ApprovalRefNo'],
                    'medical_service_id' => $item['MedicalServiceID'] ?? null,
                    'medication_id'     => $item['MedicationID'] ?? null,
                ]);
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Claim created successfully',
                'warnings' => $verification['warnings'],
                'data'     => $claim->load('claimDiseases', 'claimItems'),
            ]);

        } catch (\Exception $e) {
            Log::error('NHIF Claim Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the claim',
                'error'   => $e->getMessage(),
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

        SyncNhifTariffsJob::dispatch($request->facility_code);

        return response()->json([
            'success' => true,
            'message' => 'Tariff sync queued — refresh in a few minutes to see updated tariffs',
        ]);
    }

    /**
     * Submit a single claim to NHIF
     */
    public function submitClaim(Request $request)
    {
        $request->validate([
            'claim_id' => 'required|exists:nhif_claims,id',
        ]);

        try {
            $claim = NhifClaim::findOrFail($request->claim_id);

            if ($claim->claim_status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft claims can be submitted',
                ], 422);
            }

            $claim->update(['claim_status' => 'queued']);

            SubmitNhifClaimJob::dispatch($claim->id);

            return response()->json([
                'success' => true,
                'message' => 'Queued for submission — check back shortly',
                'status'  => 'queued',
            ]);

        } catch (\Exception $e) {
            Log::error('NHIF Claim Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during claim submission',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit all draft claims in a batch to NHIF
     */
    public function submitBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:nhif_claim_batches,id',
        ]);

        try {
            $batch = NhifClaimBatch::with(['claims' => function ($q) {
                $q->where('claim_status', 'draft');
            }])->findOrFail($request->batch_id);

            $draftClaims = $batch->claims;

            if ($draftClaims->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No draft claims found in this batch',
                ], 422);
            }

            $claimIds = $draftClaims->pluck('id')->all();

            NhifClaim::whereIn('id', $claimIds)->update(['claim_status' => 'queued']);

            Bus::batch(
                array_map(fn (int $claimId) => new SubmitNhifClaimJob($claimId), $claimIds)
            )
                ->finally(fn ($batchJob) => $batch->update(['status' => 'Submitted']))
                ->dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Queued ' . count($claimIds) . ' claim(s) for submission — check back shortly',
                'status'  => 'queued',
                'queued'  => count($claimIds),
            ]);

        } catch (\Exception $e) {
            Log::error('NHIF Batch Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during batch submission',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return a JSON preview of the claim data for a visit (before actual creation).
     */
    public function previewClaim($visitId)
    {
        try {
            $data = $this->nhifService->buildClaimData((int) $visitId);

            $monthName = \Carbon\Carbon::createFromDate($data['ClaimYear'], $data['ClaimMonth'], 1)->format('F Y');

            return response()->json([
                'patient_name'    => trim($data['FirstName'] . ' ' . $data['LastName']),
                'card_no'         => $data['CardNo'],
                'gender'          => $data['Gender'],
                'dob'             => $data['DateOfBirth'],
                'contact'         => $data['TelephoneNo'],
                'doctor_name'     => $data['DoctorName'],
                'attendance_date' => $data['AttendanceDate'],
                'claim_month'     => $data['ClaimMonth'],
                'claim_year'      => $data['ClaimYear'],
                'claim_period'    => $monthName,
                'authorization_no' => $data['AuthorizationNo'],
                'diagnoses'       => array_map(fn($d) => [
                    'code'   => $d['DiseaseCode'],
                    'status' => $d['Status'],
                ], $data['FolioDiseases']),
                'items'           => array_map(fn($i) => [
                    'name'         => $i['ItemName'],
                    'qty'          => $i['ItemQuantity'],
                    'unit_price'   => $i['UnitPrice'],
                    'amount'       => $i['AmountClaimed'],
                    'approval_ref' => $i['ApprovalRefNo'],
                    'type'         => $i['OtherDetails'],
                ], $data['FolioItems']),
                'total_amount' => $data['total_amount'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * View claim details (enriched with diseases, items, feedback, errors)
     */
    public function viewClaim($claimId)
    {
        $claim = NhifClaim::with([
            'patient',
            'claimDiseases',
            'claimItems',
            'claimFeedback',
            'claimErrors',
        ])->findOrFail($claimId);

        return response()->json($claim);
    }

    /*
    Delete claim - only if status is draft
    */
    public function deleteClaim($claimId)
    {
        $claim = NhifClaim::findOrFail($claimId);
        if ($claim->claim_status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only claims with status "draft" can be deleted'
            ], 422);
        }
        $claim->claimDiseases()->delete();
        $claim->claimItems()->delete();
        $claim->delete();

        return response()->json([
            'success' => true,
            'message' => 'Claim deleted successfully'
        ]);
    }

    /**
     * Consider moving to a separate ClaimController
     */
}

