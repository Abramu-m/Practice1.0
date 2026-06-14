<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryPayment;

class HrDashboardController extends Controller
{
    public function index()
    {
        $activeEmployeeCount = Employee::active()->count();
        $totalEmployeeCount = Employee::count();

        $now = now();
        $periodPayments = SalaryPayment::forPeriod($now->year, $now->month);

        $payrollSummary = [
            'draft' => (clone $periodPayments)->where('status', 'draft')->sum('net_salary'),
            'approved' => (clone $periodPayments)->where('status', 'approved')->sum('net_salary'),
            'paid' => (clone $periodPayments)->where('status', 'paid')->sum('net_salary'),
        ];

        $pendingApprovals = SalaryPayment::where('status', 'draft')->count();
        $pendingPayments = SalaryPayment::where('status', 'approved')->count();

        $recentPayments = SalaryPayment::with('employee')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('hr.dashboard', compact(
            'activeEmployeeCount',
            'totalEmployeeCount',
            'payrollSummary',
            'pendingApprovals',
            'pendingPayments',
            'recentPayments'
        ));
    }
}
