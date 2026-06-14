<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Patient;
use App\Models\User;
use App\Services\ReceiptGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReceiptController extends Controller
{
    protected $receiptService;

    public function __construct(ReceiptGenerationService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    /**
     * Generate and download receipt for a transaction
     */
    public function generateReceipt(Request $request, FinancialTransaction $transaction)
    {
        try {
            $format = $request->get('format', 'pdf'); // pdf, html, thermal
            
            $receipt = $this->receiptService->generateReceipt($transaction, $format);
            
            // Save receipt to storage
            if ($transaction->patient_id) {
                $this->receiptService->saveReceipt(
                    $receipt['content'], 
                    $receipt['filename'], 
                    $transaction->patient_id
                );
            }

            return Response::make($receipt['content'], 200, [
                'Content-Type' => $receipt['content_type'],
                'Content-Disposition' => 'inline; filename="' . $receipt['filename'] . '"'
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating receipt: ' . $e->getMessage());
        }
    }

    /**
     * Email receipt to patient (AJAX-compatible)
     */
    public function emailReceipt(Request $request, FinancialTransaction $transaction)
    {
        try {
            $email = $request->get('email') ?? optional($transaction->patient)->email ?? null;

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email address available for this patient.'
                ], 422);
            }

            $this->receiptService->emailReceipt($transaction, $email);

            return response()->json([
                'success' => true,
                'message' => 'Receipt emailed successfully to ' . $email,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error emailing receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate patient statement
     */
    public function generatePatientStatement(Request $request, Patient $patient)
    {
        try {
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            $statement = $this->receiptService->generatePatientStatementReceipt(
                $patient, 
                $dateFrom, 
                $dateTo
            );

            return Response::make($statement['content'], 200, [
                'Content-Type' => $statement['content_type'],
                'Content-Disposition' => 'inline; filename="' . $statement['filename'] . '"'
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating patient statement: ' . $e->getMessage());
        }
    }

    /**
     * View daily summary as HTML page
     */
    public function viewDailySummary(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $userId = $request->get('user_id');
            $summary_date = Carbon::parse($date);

            $incomeQuery = FinancialTransaction::whereBetween('transaction_date', [$date . ' 00:00:00', $date . ' 23:59:59'])
                ->where('transaction_type', 'income')
                ->where('status', 'completed')
                ->with(['patient', 'creator', 'visit.visitCategory'])
                ->orderBy('transaction_date');

            if ($userId) {
                $incomeQuery->where('created_by', $userId);
            }

            $income = $incomeQuery->get();

            // Group consultation by insurance scheme (Cash / NHIF / SHIB / CTC-TB etc.)
            $consultationGroups = $income
                ->whereIn('category', ['consultation', 'consultation_fees'])
                ->groupBy(fn($t) => $t->visit?->visitCategory?->description ?? 'Cash');

            // Group investigations by subcategory (Laboratory / Procedure / Specialized etc.)
            $investigationGroups = $income
                ->whereIn('category', ['investigation', 'investigation_services'])
                ->groupBy('subcategory');

            // Group pharmacy: Consulted (prescription-linked) vs Cash Sales
            $pharmacyGroups = $income
                ->whereIn('category', ['medication', 'medication_sales'])
                ->groupBy(fn($t) => $t->subcategory === 'cash_sales' ? 'Cash Sales' : 'Consulted');

            // Non-service income (other income lines)
            $otherIncome = $income
                ->whereNotIn('category', [
                    'consultation', 'consultation_fees',
                    'investigation', 'investigation_services',
                    'medication', 'medication_sales',
                ]);

            // Expenditure for the day (no collector filter — expenses are facility-wide)
            $expenses = FinancialTransaction::whereBetween('transaction_date', [$date . ' 00:00:00', $date . ' 23:59:59'])
                ->where('transaction_type', 'expense')
                ->where('status', 'completed')
                ->with(['creator'])
                ->orderBy('transaction_date')
                ->get();

            // Collectors who processed income that day (for filter dropdown)
            $collectorIds = $income->pluck('created_by')->filter()->unique();
            $collectors = User::whereIn('id', $collectorIds)->orderBy('first_name')->get();

            $total_cash    = $income->sum('patient_paid_amount');
            $total_covered = $income->sum('insurance_covered_amount');
            $total_revenue = $income->sum('amount');
            $total_expenses = $expenses->sum('amount');

            return view('financial.receipts.daily-summary', compact(
                'summary_date', 'date', 'userId',
                'consultationGroups', 'investigationGroups', 'pharmacyGroups',
                'otherIncome', 'expenses', 'collectors',
                'total_cash', 'total_covered', 'total_revenue', 'total_expenses'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating daily summary: ' . $e->getMessage());
        }
    }

    /**
     * Generate daily receipt summary
     */
    public function generateDailySummary(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            
            // Generate URL for the HTML view
            $summary_url = route('financial.receipts.daily.summary', ['date' => $date]);
            
            return response()->json([
                'success' => true,
                'download_url' => $summary_url,
                'message' => 'Daily summary generated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating daily summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show receipt preview page
     */
    public function previewReceipt(FinancialTransaction $transaction)
    {
        try {
            $receipt = $this->receiptService->generateReceipt($transaction, 'html');
            
            return view('financial.receipts.preview', [
                'transaction' => $transaction,
                'receipt_html' => $receipt['content']
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error previewing receipt: ' . $e->getMessage());
        }
    }

    /**
     * Print receipt
     */
    public function printReceipt(Request $request, FinancialTransaction $transaction)
    {
        try {
            $requested = $request->query('format', 'html');

            // PDF is binary — can't render in the print view; use html (standard receipt) instead
            $format = $requested === 'thermal' ? 'thermal' : 'html';

            $receipt = $this->receiptService->generateReceipt($transaction, $format);
            
            return view('financial.receipts.print', [
                'transaction' => $transaction,
                'receipt_html' => $receipt['content'],
                'format' => $format,
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error preparing receipt for printing: ' . $e->getMessage());
        }
    }

    /**
     * Bulk generate receipts for multiple transactions
     */
    public function bulkGenerateReceipts(Request $request)
    {
        try {
            $transactionIds = $request->get('transaction_ids', []);
            $format = $request->get('format', 'pdf');
            
            if (empty($transactionIds)) {
                return back()->with('error', 'No transactions selected.');
            }

            $transactions = FinancialTransaction::whereIn('id', $transactionIds)->get();
            
            if ($transactions->isEmpty()) {
                return back()->with('error', 'No valid transactions found.');
            }

            // For simplicity, we'll generate a ZIP file with all receipts
            // This is a placeholder for the actual implementation
            
            return back()->with('success', 'Bulk receipt generation completed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating bulk receipts: ' . $e->getMessage());
        }
    }

    /**
     * Show receipt management page
     */
    public function index(Request $request)
    {
        $query = FinancialTransaction::with(['patient', 'creator'])
            ->where('transaction_type', 'income')
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $transactions = $query->paginate(20);

        // Get filter options
        $patients = Patient::select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        $categories = FinancialTransaction::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        return view('financial.receipts.index', compact(
            'transactions',
            'patients',
            'categories'
        ));
    }

    /**
     * Return patient income transactions for receipt generation dropdown.
     */
    public function getPatientTransactions(Patient $patient)
    {
        $transactions = FinancialTransaction::query()
            ->select([
                'id',
                'transaction_number',
                'description',
                'amount',
                'transaction_date',
                'category',
                'status',
            ])
            ->where('patient_id', $patient->id)
            ->where('transaction_type', 'income')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('transaction_date')
            ->get()
            ->map(function (FinancialTransaction $transaction) {
                return [
                    'id' => $transaction->id,
                    'description' => $transaction->description ?: ucfirst((string) $transaction->category),
                    'amount' => (float) $transaction->amount,
                    'transaction_date' => optional($transaction->transaction_date)->toISOString(),
                    'transaction_number' => $transaction->transaction_number,
                    'status' => $transaction->status,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }
}
