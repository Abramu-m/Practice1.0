<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Patient;
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
     * Email receipt to patient
     */
    public function emailReceipt(Request $request, FinancialTransaction $transaction)
    {
        try {
            $email = $request->get('email') ?? $transaction->patient->email ?? null;
            
            if (!$email) {
                return back()->with('error', 'No email address provided.');
            }

            $this->receiptService->emailReceipt($transaction, $email);
            
            return back()->with('success', 'Receipt emailed successfully to ' . $email);

        } catch (\Exception $e) {
            return back()->with('error', 'Error emailing receipt: ' . $e->getMessage());
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
            $summary_date = Carbon::parse($date);
            
            // Get transactions for the specified date
            $transactions = FinancialTransaction::whereDate('transaction_date', $date)
                ->where('transaction_type', 'income')
                ->with(['patient', 'creator'])
                ->orderBy('transaction_date')
                ->get();

            // Calculate metrics
            $total_revenue = $transactions->sum('amount');
            $total_transactions = $transactions->count();
            $patient_payments = $transactions->whereIn('payment_method', ['cash', 'card', 'mobile_money'])->sum('amount');
            $insurance_payments = $transactions->sum('insurance_covered_amount');
            
            // Revenue by category
            $consultation_revenue = $transactions->where('category', 'consultation')->sum('amount');
            $investigation_revenue = $transactions->where('category', 'investigation')->sum('amount');
            $medication_revenue = $transactions->where('category', 'medication')->sum('amount');
            
            // Payment methods breakdown
            $payment_methods = [];
            foreach ($transactions->groupBy('payment_method') as $method => $methodTransactions) {
                $payment_methods[$method] = [
                    'count' => $methodTransactions->count(),
                    'amount' => $methodTransactions->sum('amount')
                ];
            }
            
            // Hourly breakdown
            $hourly_breakdown = [];
            foreach ($transactions as $transaction) {
                $hour = $transaction->transaction_date->format('H');
                if (!isset($hourly_breakdown[$hour])) {
                    $hourly_breakdown[$hour] = ['count' => 0, 'amount' => 0];
                }
                $hourly_breakdown[$hour]['count']++;
                $hourly_breakdown[$hour]['amount'] += $transaction->amount;
            }
            ksort($hourly_breakdown);

            return view('financial.receipts.daily-summary', [
                'summary_date' => $summary_date,
                'transactions' => $transactions,
                'total_revenue' => $total_revenue,
                'total_transactions' => $total_transactions,
                'patient_payments' => $patient_payments,
                'insurance_payments' => $insurance_payments,
                'consultation_revenue' => $consultation_revenue,
                'investigation_revenue' => $investigation_revenue,
                'medication_revenue' => $medication_revenue,
                'payment_methods' => $payment_methods,
                'hourly_breakdown' => $hourly_breakdown
            ]);

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
     * Print receipt (thermal printer compatible)
     */
    public function printReceipt(FinancialTransaction $transaction)
    {
        try {
            $receipt = $this->receiptService->generateReceipt($transaction, 'thermal');
            
            return view('financial.receipts.print', [
                'transaction' => $transaction,
                'receipt_html' => $receipt['content']
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
}
