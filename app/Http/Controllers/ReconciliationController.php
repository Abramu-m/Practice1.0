<?php

namespace App\Http\Controllers;

use App\Services\ReconciliationService;
use App\Services\StockManagementService;
use App\Models\Medication;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReconciliationController extends Controller
{
    protected $reconciliationService;
    protected $stockService;

    public function __construct(
        ReconciliationService $reconciliationService,
        StockManagementService $stockService
    ) {
        $this->reconciliationService = $reconciliationService;
        $this->stockService = $stockService;
    }

    /**
     * Display reconciliation dashboard
     */
    public function index()
    {
        $integrityCheck = $this->reconciliationService->checkStockIntegrity();
        $dashboardMetrics = $this->reconciliationService->getDashboardMetrics();
        
        $discrepancyReport = $this->reconciliationService->generateDiscrepancyReport();
        
        $recentCorrections = $this->reconciliationService->getRecentCorrections(10);

        return view('medications.reconciliation.index', compact(
            'integrityCheck',
            'dashboardMetrics',
            'discrepancyReport',
            'recentCorrections'
        ));
    }

    /**
     * Show detailed discrepancy report
     */
    public function showDiscrepancyReport()
    {
        $detailedReport = $this->reconciliationService->generateDiscrepancyReport();
        
        $medications = Medication::where('is_active', true)->get();
        $locations = StoreLocation::where('is_active', true)->get();

        return view('medications.reconciliation.discrepancy-report', compact(
            'detailedReport',
            'medications',
            'locations'
        ));
    }

    /**
     * Run stock integrity check (AJAX)
     */
    public function runIntegrityCheck(Request $request)
    {
        try {
            $result = $this->reconciliationService->checkStockIntegrity();
            
            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-correct minor discrepancies
     */
    public function autoCorrectDiscrepancies(Request $request)
    {
        try {
            $result = $this->reconciliationService->autoCorrectMinorDiscrepancies();
            
            if ($result['success']) {
                return redirect()->route('reconciliation.index')
                    ->with('success', $result['message'] . ' ' . $result['corrections_made'] . ' corrections applied.');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error during auto-correction: ' . $e->getMessage());
        }
    }

    /**
     * Validate specific medication stock balance
     */
    public function validateMedicationBalance($medicationId)
    {
        try {
            $medication = Medication::findOrFail($medicationId);
            
            $validation = $this->reconciliationService->validateStockBalance($medicationId);
            
            return view('medications.reconciliation.medication-validation', compact(
                'medication',
                'validation'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Error validating medication balance: ' . $e->getMessage());
        }
    }

    /**
     * Show stock movements audit trail
     */
    public function showAuditTrail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medication_id' => 'nullable|exists:medications,id',
            'location_id' => 'nullable|exists:store_locations,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'movement_type' => 'nullable|in:inward,outward,transfer,adjustment'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $auditTrail = $this->reconciliationService->getAuditTrail([
            'medication_id' => $request->medication_id,
            'location_id' => $request->location_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'movement_type' => $request->movement_type,
            'limit' => 100
        ]);

        $medications = Medication::where('is_active', true)->get();
        $locations = StoreLocation::where('is_active', true)->get();

        return view('medications.reconciliation.audit-trail', compact(
            'auditTrail',
            'medications',
            'locations',
            'request'
        ));
    }

    /**
     * Export reconciliation report
     */
    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:discrepancy,integrity,audit',
            'format' => 'required|in:pdf,excel',
            'medication_id' => 'nullable|exists:medications,id',
            'location_id' => 'nullable|exists:store_locations,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $data = [];
            $filename = '';

            switch ($request->report_type) {
                case 'discrepancy':
                    $data['report'] = $this->reconciliationService->generateDiscrepancyReport();
                    $filename = 'discrepancy-report';
                    break;
                case 'integrity':
                    $data['report'] = $this->reconciliationService->checkStockIntegrity();
                    $filename = 'integrity-report';
                    break;
                case 'audit':
                    $data['report'] = $this->reconciliationService->getAuditTrail([
                        'medication_id' => $request->medication_id,
                        'location_id' => $request->location_id,
                        'limit' => 1000
                    ]);
                    $filename = 'audit-trail';
                    break;
            }

            if ($request->format === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                    'reconciliation.reports.' . $request->report_type,
                    ['report' => $data['report']]
                );
                return $pdf->download($filename . '-' . now()->format('Y-m-d') . '.pdf');
            } else {
                return back()->with('info', 'Excel export is not yet available. Use PDF format.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating report: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard metrics (AJAX)
     */
    public function getDashboardMetrics()
    {
        try {
            $metrics = $this->reconciliationService->getDashboardMetrics();
            
            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual stock correction form
     */
    public function showStockCorrection()
    {
        $medications = Medication::where('is_active', true)->get();
        $locations = StoreLocation::where('is_active', true)->get();

        return view('medications.reconciliation.stock-correction', compact('medications', 'locations'));
    }

    /**
     * Process manual stock correction
     */
    public function processStockCorrection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medication_id' => 'required|exists:medications,id',
            'location_id' => 'required|exists:store_locations,id',
            'correction_type' => 'required|in:ledger,location_stock',
            'field_to_correct' => 'required|string',
            'current_value' => 'required|numeric',
            'corrected_value' => 'required|numeric',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->reconciliationService->manualStockCorrection([
                'medication_id' => $request->medication_id,
                'location_id' => $request->location_id,
                'correction_type' => $request->correction_type,
                'field_to_correct' => $request->field_to_correct,
                'current_value' => $request->current_value,
                'corrected_value' => $request->corrected_value,
                'reason' => $request->reason,
                'notes' => $request->notes
            ]);

            if ($result['success']) {
                return redirect()->route('reconciliation.index')
                    ->with('success', 'Stock correction applied successfully.');
            } else {
                return back()->with('error', $result['message'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error applying correction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show medication ledger vs location stock comparison
     */
    public function showStockComparison($medicationId = null)
    {
        $medicationId = $medicationId ?? request('medication_id');

        $medications = Medication::where('is_active', true)->get();

        $comparison = null;
        $selectedMedication = null;

        if ($medicationId) {
            $selectedMedication = Medication::findOrFail($medicationId);
            $comparison = $this->reconciliationService->compareStockLevels($medicationId);
        }

        return view('medications.reconciliation.stock-comparison', compact(
            'medications',
            'selectedMedication',
            'comparison'
        ));
    }
}
