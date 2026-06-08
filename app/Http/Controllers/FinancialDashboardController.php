<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    /**
     * Display the financial dashboard
     */
    public function index()
    {
        // Today's summary
        $todaySummary = [
            'income' => FinancialTransaction::getTodayIncome(),
            'expenses' => FinancialTransaction::getTodayExpenses(),
            'net_balance' => FinancialTransaction::getTodayNetBalance(),
            'transactions_count' => FinancialTransaction::today()->count(),
        ];

        // Monthly summary
        $monthlySummary = [
            'income' => FinancialTransaction::getMonthlyIncome(),
            'expenses' => FinancialTransaction::getMonthlyExpenses(),
            'net_balance' => FinancialTransaction::getMonthlyIncome() - FinancialTransaction::getMonthlyExpenses(),
        ];

        // Recent transactions
        $recentTransactions = FinancialTransaction::with(['patient', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Income by category (this month)
        $incomeByCategory = FinancialTransaction::income()
            ->thisMonth()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();

        // Daily trends (last 7 days)
        $dailyTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyTrends[] = [
                'date' => $date->format('M d'),
                'income' => FinancialTransaction::income()
                    ->whereDate('transaction_date', $date)
                    ->sum('amount'),
                'expenses' => FinancialTransaction::expense()
                    ->whereDate('transaction_date', $date)
                    ->sum('amount'),
            ];
        }

        // Payment methods distribution
        $paymentMethods = FinancialTransaction::income()
            ->thisMonth()
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->toArray();

        $pendingExpenses = collect();

        return view('financial.dashboard', compact(
            'todaySummary',
            'monthlySummary',
            'recentTransactions',
            'incomeByCategory',
            'dailyTrends',
            'paymentMethods',
            'pendingExpenses'
        ));
    }

    /**
     * Get financial data for AJAX requests
     */
    public function getData(Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', 'today');

        switch ($type) {
            case 'summary':
                return $this->getSummaryData($period);
            case 'trends':
                return $this->getTrendsData($period);
            case 'categories':
                return $this->getCategoriesData($period);
            case 'methods':
                return $this->getPaymentMethodsData($period);
            case 'pending_count':
                return response()->json([
                    'pending_count' => FinancialTransaction::where('status', 'pending')->count()
                ]);
            default:
                return response()->json(['error' => 'Invalid data type'], 400);
        }
    }

    /**
     * Get summary data
     */
    private function getSummaryData($period)
    {
        $query = FinancialTransaction::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->whereYear('transaction_date', now()->year);
                break;
        }

        $income = (clone $query)->income()->sum('amount');
        $expenses = (clone $query)->expense()->sum('amount');

        return response()->json([
            'income' => $income,
            'expenses' => $expenses,
            'net_balance' => $income - $expenses,
            'transactions_count' => $query->count(),
        ]);
    }

    /**
     * Get trends data
     */
    private function getTrendsData($period)
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            default => 7
        };

        $trends = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trends[] = [
                'date' => $date->format('M d'),
                'income' => FinancialTransaction::income()
                    ->whereDate('transaction_date', $date)
                    ->sum('amount'),
                'expenses' => FinancialTransaction::expense()
                    ->whereDate('transaction_date', $date)
                    ->sum('amount'),
            ];
        }

        return response()->json($trends);
    }

    /**
     * Get categories data
     */
    private function getCategoriesData($period)
    {
        $query = FinancialTransaction::income();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->whereYear('transaction_date', now()->year);
                break;
        }

        $categories = $query->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => ucfirst(str_replace('_', ' ', $item->category)),
                    'total' => $item->total
                ];
            });

        return response()->json($categories);
    }

    /**
     * Get payment methods data
     */
    private function getPaymentMethodsData($period)
    {
        $query = FinancialTransaction::income();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->whereYear('transaction_date', now()->year);
                break;
        }

        $methods = $query->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $item->payment_method)),
                    'total' => $item->total
                ];
            });

        return response()->json($methods);
    }
}
