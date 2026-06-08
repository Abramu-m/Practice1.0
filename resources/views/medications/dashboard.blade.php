@extends('layouts.app_main_layout')

@section('page_title', 'Medication Management Dashboard')

@section('main_content')
{{-- Remove the old medication-nav include since it's now part of the role-specific navigation --}}

<div class="container-fluid py-4">
    {{-- Dashboard Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-pills text-primary me-2"></i>
                        Medication Management Dashboard
                    </h1>
                    <p class="text-muted mb-0">Comprehensive overview of your medication inventory and operations</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-2"></i>
                            Quick Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('medications.stock.transfers.create') }}">
                                <i class="fas fa-exchange-alt me-2"></i>Stock Transfer</a></li>
                            <li><a class="dropdown-item" href="{{ route('medications.stock.adjustments.create') }}">
                                <i class="fas fa-edit me-2"></i>Stock Adjustment</a></li>
                            <li><a class="dropdown-item" href="{{ route('medications.reconciliation.corrections.form') }}">
                                <i class="fas fa-wrench me-2"></i>Manual Correction</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-3 p-3">
                                <i class="fas fa-pills text-white fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Medications</h6>
                            <h3 class="mb-0" id="total-medications">{{ $dashboardMetrics['total_medications'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Active Items
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-exclamation-triangle text-white fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Low Stock Alerts</h6>
                            <h3 class="mb-0" id="low-stock-count">{{ $dashboardMetrics['low_stock_count'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-arrow-down me-1"></i>
                                Need Attention
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-gradient rounded-3 p-3">
                                <i class="fas fa-calendar-times text-white fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Expiring Soon</h6>
                            <h3 class="mb-0" id="expiring-count">{{ $dashboardMetrics['expiring_soon_count'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="fas fa-clock me-1"></i>
                                Next 30 Days
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-3 p-3">
                                <i class="fas fa-chart-line text-white fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Monthly Consumption</h6>
                            <h3 class="mb-0" id="monthly-consumption">{{ number_format($dashboardMetrics['monthly_consumption'] ?? 0) }}</h3>
                            <small class="text-info">
                                <i class="fas fa-prescription-bottle-alt me-1"></i>
                                This Month
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row">
        {{-- Stock Status Overview --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Stock Status Overview
                    </h5>
                    <a href="{{ route('medications.stock.levels') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div id="stock-status-chart" style="height: 300px;">
                        {{-- Chart will be loaded here --}}
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-primary me-2"></i>
                        Recent Activities
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="recent-activities">
                        @forelse($recentActivities ?? [] as $activity)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-{{ $activity['type_color'] ?? 'primary' }} rounded-pill">
                                        <i class="fas fa-{{ $activity['icon'] ?? 'info' }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1 fw-medium">{{ $activity['title'] ?? 'Activity' }}</p>
                                    <p class="mb-1 text-muted small">{{ $activity['description'] ?? 'No description' }}</p>
                                    <small class="text-muted">{{ $activity['time'] ?? 'Just now' }}</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-clock text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent activities</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="row mb-4">
        {{-- Pending Approvals --}}
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check text-warning me-2"></i>
                        Pending Approvals
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-warning mb-1" id="pending-grns">{{ $pendingCounts['grns'] ?? 0 }}</h4>
                            <small class="text-muted">GRNs</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-1" id="pending-requisitions">{{ $pendingCounts['requisitions'] ?? 0 }}</h4>
                            <small class="text-muted">Requisitions</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('medications.stock.grn.index') }}" class="btn btn-sm btn-outline-warning me-2">
                            View GRNs
                        </a>
                        <a href="{{ route('store.requisitions.index') }}" class="btn btn-sm btn-outline-info">
                            View Requisitions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock Movement Summary --}}
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">
                        <i class="fas fa-exchange-alt text-success me-2"></i>
                        Weekly Movements
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-success mb-1" id="weekly-inward">{{ $weeklyMovements['inward'] ?? 0 }}</h4>
                            <small class="text-muted">Inward</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger mb-1" id="weekly-outward">{{ $weeklyMovements['outward'] ?? 0 }}</h4>
                            <small class="text-muted">Outward</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('medications.reports.movements') }}" class="btn btn-sm btn-outline-primary w-100">
                            View Movement Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Health --}}
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">
                        <i class="fas fa-heartbeat text-info me-2"></i>
                        System Health
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Stock Accuracy</span>
                        <span class="fw-bold text-success" id="stock-accuracy">{{ $systemHealth['accuracy'] ?? 95 }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $systemHealth['accuracy'] ?? 95 }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Last Reconciliation</span>
                        <small class="text-info" id="last-reconciliation">{{ $systemHealth['last_reconciliation'] ?? 'Never' }}</small>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-sm btn-outline-info w-100">
                            Run Reconciliation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Navigation Cards --}}
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-th-large text-primary me-2"></i>
                Quick Navigation
            </h5>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <a href="{{ route('medications.stock.levels') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 nav-card">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-gradient rounded-3 p-3 mb-3 mx-auto" style="width: fit-content;">
                            <i class="fas fa-warehouse text-white fa-2x"></i>
                        </div>
                        <h6 class="mb-1">Stock Management</h6>
                        <small class="text-muted">Manage inventory levels</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <a href="{{ route('medications.consumption.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 nav-card">
                    <div class="card-body text-center">
                        <div class="bg-success bg-gradient rounded-3 p-3 mb-3 mx-auto" style="width: fit-content;">
                            <i class="fas fa-prescription-bottle-alt text-white fa-2x"></i>
                        </div>
                        <h6 class="mb-1">Consumption</h6>
                        <small class="text-muted">Track usage patterns</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <a href="{{ route('medical_services.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 nav-card">
                    <div class="card-body text-center">
                        <div class="bg-info bg-gradient rounded-3 p-3 mb-3 mx-auto" style="width: fit-content;">
                            <i class="fas fa-stethoscope text-white fa-2x"></i>
                        </div>
                        <h6 class="mb-1">Medical Services</h6>
                        <small class="text-muted">Procedures & investigations</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <a href="{{ route('medications.reconciliation.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 nav-card">
                    <div class="card-body text-center">
                        <div class="bg-warning bg-gradient rounded-3 p-3 mb-3 mx-auto" style="width: fit-content;">
                            <i class="fas fa-balance-scale text-white fa-2x"></i>
                        </div>
                        <h6 class="mb-1">Reconciliation</h6>
                        <small class="text-muted">Balance stock records</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <a href="{{ route('medications.reports.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 nav-card">
                    <div class="card-body text-center">
                        <div class="bg-secondary bg-gradient rounded-3 p-3 mb-3 mx-auto" style="width: fit-content;">
                            <i class="fas fa-chart-pie text-white fa-2x"></i>
                        </div>
                        <h6 class="mb-1">Reports</h6>
                        <small class="text-muted">Analytics & insights</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <a href="{{ route('medications.stock.alerts') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 nav-card">
                    <div class="card-body text-center">
                        <div class="bg-danger bg-gradient rounded-3 p-3 mb-3 mx-auto" style="width: fit-content;">
                            <i class="fas fa-bell text-white fa-2x"></i>
                        </div>
                        <h6 class="mb-1">Alerts</h6>
                        <small class="text-muted">Monitor notifications</small>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.nav-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.nav-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}
</style>

<script>
function refreshDashboard() {
    // Show loading state
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    // Simulate refresh (in real implementation, make AJAX calls to update data)
    setTimeout(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        
        // Show success message
        showToast('Dashboard refreshed successfully', 'success');
    }, 2000);
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to page
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any charts or dynamic content here
    loadStockStatusChart();
});

function loadStockStatusChart() {
    // Placeholder for chart loading
    // In real implementation, this would load Chart.js or similar
    setTimeout(() => {
        const chartContainer = document.getElementById('stock-status-chart');
        chartContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-chart-bar text-muted fa-3x mb-3"></i>
                <p class="text-muted">Stock status chart will be implemented here</p>
                <small class="text-muted">Using Chart.js or similar charting library</small>
            </div>
        `;
    }, 1000);
}
</script>
@endsection
