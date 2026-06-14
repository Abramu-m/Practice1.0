@extends('layouts.app_main_layout')

@section('page_title')
    HR Dashboard
@endsection

@section('main_content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-people-fill"></i> Human Resources Dashboard</h3>
        <div>
            <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people"></i> Employees
            </a>
            <a href="{{ route('hr.salary-payments.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cash-coin"></i> Salary Payments
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="bi bi-person-check-fill"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Employees</span>
                    <span class="info-box-number">{{ $activeEmployeeCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="bi bi-people-fill"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Employees</span>
                    <span class="info-box-number">{{ $totalEmployeeCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="bi bi-hourglass-split"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Approvals</span>
                    <span class="info-box-number">{{ $pendingApprovals }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="bi bi-cash-stack"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Approved, Awaiting Payment</span>
                    <span class="info-box-number">{{ $pendingPayments }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-calendar-month"></i> {{ now()->format('F Y') }} Payroll Summary</h3>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h6 class="text-muted">Draft</h6>
                    <h4>Tsh {{ number_format($payrollSummary['draft'], 2) }}</h4>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Approved</h6>
                    <h4>Tsh {{ number_format($payrollSummary['approved'], 2) }}</h4>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Paid</h6>
                    <h4 class="text-success">Tsh {{ number_format($payrollSummary['paid'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clock-history"></i> Recent Salary Payments</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->employee->name }}</td>
                            <td>{{ $payment->period_label }}</td>
                            <td>Tsh {{ number_format($payment->net_salary, 2) }}</td>
                            <td><span class="badge {{ $payment->status_badge }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>
                                <a href="{{ route('hr.salary-payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No salary payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
