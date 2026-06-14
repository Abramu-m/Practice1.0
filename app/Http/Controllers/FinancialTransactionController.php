<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FinancialTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default to the last 3 days (including today) when no date range is given
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(2)->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        $applyFilters = function ($query) use ($request, $dateFrom, $dateTo) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);

            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('patient', function ($patientQuery) use ($search) {
                          $patientQuery->where('first_name', 'like', "%{$search}%")
                                      ->orWhere('last_name', 'like', "%{$search}%");
                      });
                });
            }

            return $query;
        };

        $transactions = $applyFilters(FinancialTransaction::with(['patient', 'visit', 'creator', 'approver']))
            ->orderBy('transaction_date', 'desc')
            ->paginate(25);

        // Calculate summary for the same filtered range
        $summaryQuery = $applyFilters(FinancialTransaction::query());

        $summary = [
            'total_income' => (clone $summaryQuery)->where('transaction_type', 'income')->sum('amount'),
            'total_expenses' => (clone $summaryQuery)->where('transaction_type', 'expense')->sum('amount'),
        ];
        $summary['net_balance'] = $summary['total_income'] - $summary['total_expenses'];

        // Get filter options
        $categories = FinancialTransaction::distinct()->pluck('category', 'category');
        $paymentMethods = FinancialTransaction::distinct()->pluck('payment_method', 'payment_method');

        return view('financial.transactions.index', compact(
            'transactions',
            'categories',
            'paymentMethods',
            'summary',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = [
            'consultation' => 'Consultation',
            'investigation' => 'Investigation',
            'medication' => 'Medication',
            'general_expense' => 'General Expense',
            'other' => 'Other'
        ];

        $paymentMethods = [
            'cash' => 'Cash',
            'insurance' => 'Insurance',
            'bank' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'other' => 'Other'
        ];

        return view('financial.transactions.create', compact('categories', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'payment_method' => 'required|in:cash,insurance,bank,mobile_money,other',
            'payment_reference' => 'nullable|string|max:100',
            'patient_id' => 'nullable|exists:patients,id',
            'visit_id' => 'nullable|exists:patient_visits,id',
            'notes' => 'nullable|string'
        ]);

        $validated['source_type'] = 'general_expense';
        $validated['patient_paid_amount'] = $validated['payment_method'] === 'insurance' ? 0 : $validated['amount'];
        $validated['insurance_covered_amount'] = $validated['payment_method'] === 'insurance' ? $validated['amount'] : 0;

        $transaction = FinancialTransaction::create($validated);

        return redirect()->route('financial.transactions.show', $transaction)
                        ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialTransaction $transaction)
    {
        $transaction->load(['patient', 'visit', 'creator', 'approver']);
        
        return view('financial.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinancialTransaction $transaction)
    {
        // Only allow editing of draft transactions
        if ($transaction->status !== 'pending') {
            return redirect()->route('financial.transactions.show', $transaction)
                           ->with('error', 'Only pending transactions can be edited.');
        }

        $categories = [
            'consultation' => 'Consultation',
            'investigation' => 'Investigation',
            'medication' => 'Medication',
            'general_expense' => 'General Expense',
            'other' => 'Other'
        ];

        $paymentMethods = [
            'cash' => 'Cash',
            'insurance' => 'Insurance',
            'bank' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'other' => 'Other'
        ];

        return view('financial.transactions.edit', compact('transaction', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinancialTransaction $transaction)
    {
        // Only allow updating of pending transactions
        if ($transaction->status !== 'pending') {
            return redirect()->route('financial.transactions.show', $transaction)
                           ->with('error', 'Only pending transactions can be updated.');
        }

        $validated = $request->validate([
            'transaction_type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'payment_method' => 'required|in:cash,insurance,bank,mobile_money,other',
            'payment_reference' => 'nullable|string|max:100',
            'patient_id' => 'nullable|exists:patients,id',
            'visit_id' => 'nullable|exists:patient_visits,id',
            'notes' => 'nullable|string'
        ]);

        $validated['patient_paid_amount'] = $validated['payment_method'] === 'insurance' ? 0 : $validated['amount'];
        $validated['insurance_covered_amount'] = $validated['payment_method'] === 'insurance' ? $validated['amount'] : 0;

        $transaction->update($validated);

        return redirect()->route('financial.transactions.show', $transaction)
                        ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialTransaction $transaction)
    {
        // Only allow deletion of pending transactions
        if ($transaction->status !== 'pending') {
            return redirect()->route('financial.transactions.index')
                           ->with('error', 'Only pending transactions can be deleted.');
        }

        $transaction->delete();

        return redirect()->route('financial.transactions.index')
                        ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Approve a transaction
     */
    public function approve(Request $request, FinancialTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return redirect()->route('financial.transactions.show', $transaction)
                           ->with('error', 'Only pending transactions can be approved.');
        }

        $transaction->update([
            'status' => 'completed',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $request->approval_notes
        ]);

        return redirect()->route('financial.transactions.show', $transaction)
                        ->with('success', 'Transaction approved successfully.');
    }

    /**
     * Cancel a transaction
     */
    public function cancel(Request $request, FinancialTransaction $transaction)
    {
        $request->validate([
            'cancellation_reason' => 'required|string'
        ]);

        $transaction->update([
            'status' => 'cancelled',
            'notes' => $request->cancellation_reason
        ]);

        return redirect()->route('financial.transactions.show', $transaction)
                        ->with('success', 'Transaction cancelled successfully.');
    }

    /**
     * Export transactions to Excel
     */
    public function export(Request $request)
    {
        // This would typically use Laravel Excel package
        // For now, return a CSV response
        
        $query = FinancialTransaction::with(['patient', 'visit', 'creator']);
        
        // Apply same filters as index
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        
        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        
        $filename = 'financial_transactions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Transaction Number',
                'Date',
                'Type',
                'Category',
                'Subcategory',
                'Amount',
                'Payment Method',
                'Patient Name',
                'Description',
                'Status',
                'Created By'
            ]);
            
            // Data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->transaction_date->format('Y-m-d H:i:s'),
                    ucfirst($transaction->transaction_type),
                    ucfirst(str_replace('_', ' ', $transaction->category)),
                    $transaction->subcategory,
                    $transaction->amount,
                    ucfirst(str_replace('_', ' ', $transaction->payment_method)),
                    $transaction->patient ? $transaction->patient->first_name . ' ' . $transaction->patient->last_name : '',
                    $transaction->description,
                    ucfirst($transaction->status),
                    $transaction->creator->name ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
