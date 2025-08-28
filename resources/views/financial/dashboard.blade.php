@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Financial Dashboard' }}
@endsection

@section('infoboxes')
           <!-- Recent Transactions -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Recent Financial Transactions
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('financial.receipts.index') }}" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-receipt"></i> Receipt Management
                        </a>
                        <a href="{{ route('financial.transactions.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> View All Transactions
                        </a>
                    </div>
                </div>
@endsection

@section('Content_Description')
    {{ 'Real-time financial tracking and analytics dashboard.' }}
@endsection

@section('main_content')
<div class="container-fluid">
    <!-- Summary Cards Row -->
    <div class="row">
        <!-- Today's Income -->
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-success">
                <span class="info-box-icon">
                    <i class="fas fa-arrow-up"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Today's Income</span>
                    <span class="info-box-number">
                        ${{ number_format($todaySummary['income'], 2) }}
                    </span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $todaySummary['transactions_count'] }} transactions
                    </span>
                </div>
            </div>
        </div>

        <!-- Today's Expenses -->
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-danger">
                <span class="info-box-icon">
                    <i class="fas fa-arrow-down"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Today's Expenses</span>
                    <span class="info-box-number">
                        ${{ number_format($todaySummary['expenses'], 2) }}
                    </span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Balance -->
        <div class="col-lg-3 col-md-6">
            <div class="info-box {{ $todaySummary['net_balance'] >= 0 ? 'bg-success' : 'bg-warning' }}">
                <span class="info-box-icon">
                    <i class="fas fa-balance-scale"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Net Balance</span>
                    <span class="info-box-number">
                        ${{ number_format($todaySummary['net_balance'], 2) }}
                    </span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Income -->
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-info">
                <span class="info-box-icon">
                    <i class="fas fa-chart-line"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Monthly Income</span>
                    <span class="info-box-number">
                        ${{ number_format($monthlySummary['income'], 2) }}
                    </span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Daily Trends Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Daily Financial Trends (Last 7 Days)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Income by Category -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Income by Category
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="incomeCategoryChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Recent Transactions
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('financial.transactions.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Transaction #</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Patient</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>
                                        <a href="{{ route('financial.transactions.show', $transaction) }}">
                                            {{ $transaction->transaction_number }}
                                        </a>
                                    </td>
                                    <td>{{ $transaction->transaction_date->format('M d, H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->transaction_type_badge }}">
                                            {{ ucfirst($transaction->transaction_type) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->category)) }}</td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>
                                        @if($transaction->patient)
                                            {{ $transaction->patient->first_name }} {{ $transaction->patient->last_name }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $transaction->status_badge }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="generateReceipt({{ $transaction->id }})" title="Generate Receipt">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewTransaction({{ $transaction->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Expenses -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Pending Expenses
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($pendingExpenses as $expense)
                        <div class="expense-item mb-3 p-2 border rounded">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $expense->expense_number }}</strong>
                                <span class="text-muted">${{ number_format($expense->amount, 2) }}</span>
                            </div>
                            <div class="text-sm">
                                {{ Str::limit($expense->description, 50) }}
                            </div>
                            <div class="text-xs text-muted">
                                Requested by: {{ $expense->requester->name }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No pending expenses</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('financial.transactions.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> New Transaction
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('financial.transactions.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-list"></i> View All Transactions
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('financial.transactions.export') }}" class="btn btn-success btn-block">
                                <i class="fas fa-download"></i> Export Report
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-warning btn-block" onclick="refreshDashboard()">
                                <i class="fas fa-sync"></i> Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Trends Chart
    const dailyTrendsCtx = document.getElementById('dailyTrendsChart').getContext('2d');
    const dailyTrendsChart = new Chart(dailyTrendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($dailyTrends, 'date')) !!},
            datasets: [{
                label: 'Income',
                data: {!! json_encode(array_column($dailyTrends, 'income')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true,
                tension: 0.1
            }, {
                label: 'Expenses',
                data: {!! json_encode(array_column($dailyTrends, 'expenses')) !!},
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Tsh' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Income by Category Chart
    const incomeCategoryCtx = document.getElementById('incomeCategoryChart').getContext('2d');
    const categoryLabels = {!! json_encode(array_keys($incomeByCategory)) !!}.map(label => 
        label.charAt(0).toUpperCase() + label.slice(1).replace('_', ' ')
    );
    const categoryData = {!! json_encode(array_values($incomeByCategory)) !!};
    
    const incomeCategoryChart = new Chart(incomeCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': $' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});

// Refresh dashboard function
function refreshDashboard() {
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(refreshDashboard, 300000);

// Receipt and transaction functions
function generateReceipt(transactionId) {
    // Open receipt generation modal or redirect
    window.open(`/financial/receipts/${transactionId}/generate`, '_blank');
}

function viewTransaction(transactionId) {
    // Redirect to transaction details page
    window.location.href = `/financial/transactions/${transactionId}`;
}
</script>
@endsection
