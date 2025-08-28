@extends('layouts.app_main_layout')

@section('page_title', 'Reconciliation Dashboard')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-balance-scale text-primary me-2"></i>
                        Stock Reconciliation Dashboard
                    </h1>
                    <p class="text-muted mb-0">Monitor stock integrity and resolve discrepancies</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="runIntegrityCheck()">
                        <i class="fas fa-shield-alt me-2"></i>
                        Run Integrity Check
                    </button>
                    <button class="btn btn-outline-warning" onclick="autoCorrectDiscrepancies()">
                        <i class="fas fa-magic me-2"></i>
                        Auto Correct
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Overview Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-{{ $integrityCheck['status'] === 'passed' ? 'success' : 'danger' }} bg-gradient rounded-3 p-3">
                                <i class="fas fa-{{ $integrityCheck['status'] === 'passed' ? 'check-circle' : 'exclamation-triangle' }} text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Integrity Status</h6>
                            <h5 class="mb-0 text-{{ $integrityCheck['status'] === 'passed' ? 'success' : 'danger' }}">
                                {{ ucfirst($integrityCheck['status'] ?? 'Unknown') }}
                            </h5>
                            <small class="text-muted">Last check: {{ $integrityCheck['last_check'] ?? 'Never' }}</small>
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
                                <i class="fas fa-exclamation-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Discrepancies</h6>
                            <h4 class="mb-0 text-warning" id="total-discrepancies">{{ $dashboardMetrics['total_discrepancies'] ?? 0 }}</h4>
                            <small class="text-muted">Need attention</small>
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
                            <div class="bg-danger bg-gradient rounded-3 p-3">
                                <i class="fas fa-times-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Critical Issues</h6>
                            <h4 class="mb-0 text-danger" id="critical-discrepancies">{{ $dashboardMetrics['critical_discrepancies'] ?? 0 }}</h4>
                            <small class="text-muted">High priority</small>
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
                                <i class="fas fa-percentage text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Accuracy Rate</h6>
                            <h4 class="mb-0 text-info" id="accuracy-percentage">{{ $dashboardMetrics['accuracy_percentage'] ?? 0 }}%</h4>
                            <small class="text-muted">Stock accuracy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row">
        {{-- Discrepancy Report --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Active Discrepancies
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('medications.reconciliation.discrepancies') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i>
                                Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterDiscrepancies('all')">All Discrepancies</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterDiscrepancies('critical')">Critical Only</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterDiscrepancies('minor')">Minor Only</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="discrepancies-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Medication</th>
                                    <th>Ledger Qty</th>
                                    <th>Physical Qty</th>
                                    <th>Variance</th>
                                    <th>Severity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($discrepancyReport['discrepancies'] ?? [] as $discrepancy)
                                <tr data-severity="{{ $discrepancy['severity'] ?? 'minor' }}">
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $discrepancy['medication_name'] ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $discrepancy['generic_name'] ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td class="fw-medium">{{ number_format($discrepancy['ledger_quantity'] ?? 0) }}</td>
                                    <td class="fw-medium">{{ number_format($discrepancy['physical_quantity'] ?? 0) }}</td>
                                    <td>
                                        @php
                                            $variance = ($discrepancy['variance'] ?? 0);
                                            $varianceClass = $variance > 0 ? 'text-success' : ($variance < 0 ? 'text-danger' : 'text-muted');
                                        @endphp
                                        <span class="fw-bold {{ $varianceClass }}">
                                            {{ $variance > 0 ? '+' : '' }}{{ number_format($variance) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $severity = $discrepancy['severity'] ?? 'minor';
                                            $badgeClass = match($severity) {
                                                'critical' => 'bg-danger',
                                                'major' => 'bg-warning text-dark',
                                                'minor' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($severity) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewDiscrepancyDetails({{ $discrepancy['medication_id'] ?? 0 }})"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="correctDiscrepancy({{ $discrepancy['medication_id'] ?? 0 }})"
                                                    title="Correct">
                                                <i class="fas fa-wrench"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                        <p class="text-muted mb-0">No discrepancies found!</p>
                                        <small class="text-muted">Your stock records are in perfect sync</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Corrections --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-info me-2"></i>
                        Recent Corrections
                    </h5>
                    <a href="{{ route('medications.reconciliation.audit') }}" class="btn btn-sm btn-outline-info">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($recentCorrections ?? [] as $correction)
                        <div class="list-group-item border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-medium">{{ $correction['medication_name'] ?? 'Unknown' }}</p>
                                            <p class="mb-1 text-muted small">
                                                {{ $correction['field_corrected'] ?? 'Unknown field' }}: 
                                                {{ $correction['previous_value'] ?? 0 }} → {{ $correction['new_value'] ?? 0 }}
                                            </p>
                                            <small class="text-muted">
                                                {{ $correction['corrected_at'] ?? 'Unknown time' }}
                                            </small>
                                        </div>
                                        <small class="text-muted">{{ $correction['location_name'] ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 text-center py-4">
                            <i class="fas fa-history text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent corrections</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Row --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-primary me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('medications.reconciliation.comparison') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-not-equal me-2"></i>
                                    Stock Comparison
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('medications.reconciliation.corrections.form') }}" class="btn btn-outline-warning">
                                    <i class="fas fa-wrench me-2"></i>
                                    Manual Correction
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('medications.reconciliation.audit') }}" class="btn btn-outline-info">
                                    <i class="fas fa-history me-2"></i>
                                    Audit Trail
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-success" onclick="exportReconciliationReport()">
                                    <i class="fas fa-download me-2"></i>
                                    Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- System Status --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat text-info me-2"></i>
                        System Health & Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ number_format($dashboardMetrics['total_medications'] ?? 0) }}</h4>
                                <small class="text-muted">Total Medications</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ $dashboardMetrics['accuracy_percentage'] ?? 0 }}%</h4>
                                <small class="text-muted">Accuracy Rate</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="border-end">
                                <h4 class="text-info mb-1">{{ number_format($dashboardMetrics['corrections_this_month'] ?? 0) }}</h4>
                                <small class="text-muted">Corrections This Month</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border-end">
                                <h4 class="text-warning mb-1">{{ $dashboardMetrics['last_check_time'] ?? 'Never' }}</h4>
                                <small class="text-muted">Last Integrity Check</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <h4 class="text-{{ $integrityCheck['status'] === 'passed' ? 'success' : 'danger' }} mb-1">
                                {{ ucfirst($integrityCheck['status'] ?? 'Unknown') }}
                            </h4>
                            <small class="text-muted">System Status</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runIntegrityCheck() {
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Running Check...';
    btn.disabled = true;
    
    fetch('{{ route("medications.reconciliation.integrity.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        
        if (data.success) {
            showToast('Integrity check completed successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.error || 'Integrity check failed', 'error');
        }
    })
    .catch(error => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        showToast('Error running integrity check', 'error');
    });
}

function autoCorrectDiscrepancies() {
    if (confirm('This will automatically correct minor discrepancies. Continue?')) {
        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Correcting...';
        btn.disabled = true;
        
        fetch('{{ route("medications.reconciliation.auto.correct") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            
            if (data.success) {
                showToast('Auto-correction completed successfully', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(data.message || 'Auto-correction failed', 'error');
            }
        })
        .catch(error => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            showToast('Error during auto-correction', 'error');
        });
    }
}

function filterDiscrepancies(filter) {
    const rows = document.querySelectorAll('#discrepancies-table tbody tr');
    
    rows.forEach(row => {
        if (filter === 'all') {
            row.style.display = '';
        } else {
            const severity = row.getAttribute('data-severity');
            row.style.display = severity === filter ? '' : 'none';
        }
    });
}

function viewDiscrepancyDetails(medicationId) {
    window.location.href = `{{ route('medications.reconciliation.medications.validate', ':id') }}`.replace(':id', medicationId);
}

function correctDiscrepancy(medicationId) {
    window.location.href = `{{ route('medications.reconciliation.corrections.form') }}?medication_id=${medicationId}`;
}

function exportReconciliationReport() {
    window.open('{{ route("medications.reconciliation.export") }}?report_type=discrepancy&format=pdf', '_blank');
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
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
</script>
@endsection
