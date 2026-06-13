@extends('layouts.app_main_layout')

@section('page_title', 'Consumption Tracking Dashboard')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-prescription-bottle-alt text-primary me-2"></i>
                        Consumption Tracking Dashboard
                    </h1>
                    <p class="text-muted mb-0">Monitor medication usage across prescriptions, investigations, and procedures</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.consumption.export') }}" class="btn btn-outline-success">
                        <i class="fas fa-download me-2"></i>
                        Export Data
                    </a>
                    <button class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-3 p-3">
                                <i class="fas fa-prescription text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Prescriptions</h6>
                            <h4 class="mb-0 text-primary" id="prescriptions-today">{{ $consumptionStats['prescriptions_dispensed'] ?? 0 }}</h4>
                            <small class="text-muted">This month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-3 p-3">
                                <i class="fas fa-microscope text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Investigations</h6>
                            <h4 class="mb-0 text-info" id="investigations-today">{{ $consumptionStats['investigations_with_batches'] ?? 0 }}</h4>
                            <small class="text-muted">With consumptions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-3 p-3">
                                <i class="fas fa-procedures text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cash Sales</h6>
                            <h4 class="mb-0 text-success" id="procedures-today">{{ $consumptionStats['cash_sales_dispensed'] ?? 0 }}</h4>
                            <small class="text-muted">This month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-calculator text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Quantity</h6>
                            <h4 class="mb-0 text-warning" id="consumption-value">{{ number_format($consumptionStats['grand_total_quantity'] ?? 0) }}</h4>
                            <small class="text-muted">All consumptions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row">
        {{-- Consumption Trends Chart --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Consumption Trends (Last 30 Days)
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="chart-period" id="period-7" autocomplete="off">
                        <label class="btn btn-outline-primary" for="period-7">7 Days</label>
                        
                        <input type="radio" class="btn-check" name="chart-period" id="period-30" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="period-30">30 Days</label>
                        
                        <input type="radio" class="btn-check" name="chart-period" id="period-90" autocomplete="off">
                        <label class="btn btn-outline-primary" for="period-90">90 Days</label>
                    </div>
                </div>
                <div class="card-body">
                    <div id="consumption-trends-chart" style="height: 300px;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading chart...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-primary me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('medications.consumption.prescriptions.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-prescription me-2"></i>
                            Manage Prescriptions
                        </a>
                        <a href="{{ route('medications.consumption.analytics') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>
                            View Analytics
                        </a>
                        <a href="{{ route('medical_services.index', ['category' => 'investigations']) }}" class="btn btn-outline-success">
                            <i class="fas fa-microscope me-2"></i>
                            Investigation Services
                        </a>
                        <a href="{{ route('medical_services.index', ['category' => 'procedures']) }}" class="btn btn-outline-warning">
                            <i class="fas fa-procedures me-2"></i>
                            Procedure Services
                        </a>
                        <hr>
                        <a href="{{ route('medications.reports.consumption') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-alt me-2"></i>
                            Generate Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Consumption Activity by Category --}}
    <div class="row">
        {{-- Laboratory Investigations --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-microscope text-info me-2"></i>
                        Recent Lab Investigations
                    </h5>
                    <a href="{{ route('lab.visits.index') }}" class="btn btn-sm btn-outline-info">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentConsumptions['lab_investigations'] ?? [] as $investigation)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-info rounded-pill">
                                        <i class="fas fa-microscope"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-medium">{{ $investigation->medicalService->name ?? 'Unknown Service' }}</p>
                                            <p class="mb-1 text-muted small">
                                                Patient: {{ $investigation->patient->first_name ?? 'Unknown' }} {{ $investigation->patient->last_name ?? '' }}
                                                - Status: {{ ucfirst($investigation->status ?? 'Unknown') }}
                                            </p>
                                            <small class="text-muted">{{ $investigation->collected_at ? $investigation->collected_at->diffForHumans() : ($investigation->created_at ? $investigation->created_at->diffForHumans() : 'Unknown time') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">{{ $investigation->investigationConsumptions->count() ?? 0 }} items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-microscope text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent lab investigations</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Nursing Procedures --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-nurse text-primary me-2"></i>
                        Recent Nursing Procedures
                    </h5>
                    <a href="{{ route('lab.visits.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentConsumptions['nursing_procedures'] ?? [] as $procedure)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary rounded-pill">
                                        <i class="fas fa-user-nurse"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-medium">{{ $procedure->medicalService->name ?? 'Unknown Procedure' }}</p>
                                            <p class="mb-1 text-muted small">
                                                Patient: {{ $procedure->patient->first_name ?? 'Unknown' }} {{ $procedure->patient->last_name ?? '' }}
                                                - Status: {{ ucfirst($procedure->status ?? 'Unknown') }}
                                            </p>
                                            <small class="text-muted">{{ $procedure->collected_at ? $procedure->collected_at->diffForHumans() : ($procedure->created_at ? $procedure->created_at->diffForHumans() : 'Unknown time') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">{{ $procedure->investigationConsumptions->count() ?? 0 }} items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-user-nurse text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent nursing procedures</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Radiology Investigations --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-x-ray text-secondary me-2"></i>
                        Recent Radiology Studies
                    </h5>
                    <a href="{{ route('lab.visits.index') }}" class="btn btn-sm btn-outline-secondary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentConsumptions['radiology_investigations'] ?? [] as $radiology)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-secondary rounded-pill">
                                        <i class="fas fa-x-ray"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-medium">{{ $radiology->medicalService->name ?? 'Unknown Study' }}</p>
                                            <p class="mb-1 text-muted small">
                                                Patient: {{ $radiology->patient->first_name ?? 'Unknown' }} {{ $radiology->patient->last_name ?? '' }}
                                                - Status: {{ ucfirst($radiology->status ?? 'Unknown') }}
                                            </p>
                                            <small class="text-muted">{{ $radiology->collected_at ? $radiology->collected_at->diffForHumans() : ($radiology->created_at ? $radiology->created_at->diffForHumans() : 'Unknown time') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">{{ $radiology->investigationConsumptions->count() ?? 0 }} items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-x-ray text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent radiology studies</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Consultation Prescriptions --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-prescription text-success me-2"></i>
                        Recent Prescriptions
                    </h5>
                    <a href="{{ route('medications.consumption.prescriptions.index') }}" class="btn btn-sm btn-outline-success">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentConsumptions['consultation_prescriptions'] ?? [] as $prescription)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fas fa-prescription"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-medium">{{ $prescription->medication->generic_name ?? 'Unknown Medication' }}</p>
                                            <p class="mb-1 text-muted small">
                                                Patient: {{ $prescription->patient->first_name ?? 'Unknown' }} {{ $prescription->patient->last_name ?? '' }}
                                                - Prescription #{{ $prescription->id }}
                                            </p>
                                            <small class="text-muted">{{ $prescription->dispensed_at ? $prescription->dispensed_at->diffForHumans() : ($prescription->created_at ? $prescription->created_at->diffForHumans() : 'Unknown time') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Qty: {{ number_format($prescription->quantity_dispensed ?? 0) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-prescription text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent prescriptions</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Medication Cash Sales --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cash-register text-warning me-2"></i>
                        Recent Cash Sales
                    </h5>
                    <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-sm btn-outline-warning">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentConsumptions['medication_cash_sales'] ?? [] as $cashSale)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-warning text-dark rounded-pill">
                                        <i class="fas fa-cash-register"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-medium">{{ $cashSale->medication->generic_name ?? 'Unknown Medication' }}</p>
                                            <p class="mb-1 text-muted small">
                                                Sale #{{ $cashSale->cashSale->sale_number ?? 'Unknown' }}
                                                - Qty: {{ number_format($cashSale->quantity_dispensed ?? 0) }}
                                                - {{ ucfirst($cashSale->cashSale->sale_type ?? 'otc') }}
                                            </p>
                                            <small class="text-muted">{{ $cashSale->dispensed_at ? $cashSale->dispensed_at->diffForHumans() : ($cashSale->created_at ? $cashSale->created_at->diffForHumans() : 'Unknown time') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-success fw-medium">Tsh {{ number_format($cashSale->total_price ?? 0, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-cash-register text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent cash sales</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Consumed Medications --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy text-primary me-2"></i>
                        Top Consumed (This Month)
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar me-1"></i>
                            This Month
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="updateTopConsumed('week')">This Week</a></li>
                            <li><a class="dropdown-item active" href="#" onclick="updateTopConsumed('month')">This Month</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateTopConsumed('quarter')">This Quarter</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Medication</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody id="top-consumed-tbody">
                                @forelse($topConsumed ?? [] as $index => $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $index < 3 ? ['warning', 'secondary', 'dark'][$index] : 'light text-dark' }}">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $item['name'] ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $item['generic_name'] ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td class="fw-bold">{{ number_format($item['total_quantity'] ?? 0) }}</td>
                                    <td class="text-success fw-medium">Tsh {{ number_format($item['total_value'] ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">
                                        No consumption data available
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Prescriptions Alert --}}
    @if(($pendingPrescriptions ?? 0) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-1">Pending Prescriptions</h5>
                        <p class="mb-2">You have <strong>{{ $pendingPrescriptions }}</strong> prescriptions waiting to be dispensed.</p>
                        <a href="{{ route('medications.consumption.prescriptions.index') }}?status=pending" class="btn btn-warning btn-sm">
                            <i class="fas fa-prescription me-2"></i>
                            View Pending Prescriptions
                        </a>
                    </div>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Statistics --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Consumption Summary Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ number_format($summaryStats['weekly_total'] ?? 0) }}</h4>
                                <small class="text-muted">This Week</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-info mb-1">{{ number_format($summaryStats['monthly_total'] ?? 0) }}</h4>
                                <small class="text-muted">This Month</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ number_format($summaryStats['quarterly_total'] ?? 0) }}</h4>
                                <small class="text-muted">This Quarter</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <h4 class="text-warning mb-1">{{ number_format($summaryStats['average_daily'] ?? 0) }}</h4>
                            <small class="text-muted">Daily Average</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshData() {
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function updateTopConsumed(period) {
    // Update the dropdown button text
    const periodTexts = {
        'week': 'This Week',
        'month': 'This Month',
        'quarter': 'This Quarter'
    };
    
    const button = document.querySelector('[data-bs-toggle="dropdown"]');
    button.innerHTML = `<i class="fas fa-calendar me-1"></i>${periodTexts[period]}`;
    
    // In a real implementation, this would make an AJAX call to fetch new data
    const tbody = document.getElementById('top-consumed-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-3">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading ${periodTexts[period].toLowerCase()} data...</span>
            </td>
        </tr>
    `;
    
    // Simulate loading delay
    setTimeout(() => {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-3 text-muted">
                    Data for ${periodTexts[period].toLowerCase()} would be loaded here
                </td>
            </tr>
        `;
    }, 1000);
}

// Load chart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadConsumptionTrendsChart();
    
    // Handle period change for chart
    document.querySelectorAll('input[name="chart-period"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                loadConsumptionTrendsChart(this.id.replace('period-', ''));
            }
        });
    });
});

function loadConsumptionTrendsChart(period = '30') {
    const chartContainer = document.getElementById('consumption-trends-chart');
    
    // Show loading state
    chartContainer.innerHTML = `
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading chart...</span>
            </div>
        </div>
    `;
    
    // Simulate chart loading (in real implementation, use Chart.js or similar)
    setTimeout(() => {
        chartContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-chart-line text-muted fa-3x mb-3"></i>
                <p class="text-muted">Consumption trends chart for last ${period} days</p>
                <small class="text-muted">Chart will be implemented using Chart.js or similar library</small>
            </div>
        `;
    }, 1000);
}
</script>
@endsection
