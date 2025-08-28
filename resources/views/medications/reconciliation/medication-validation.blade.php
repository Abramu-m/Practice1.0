@extends('layouts.app_main_layout')

@section('page_title', 'Medication Balance Validation')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Medication Balance Validation
                    </h1>
                    <p class="text-muted mb-0">Detailed validation of {{ $medication->name }} stock balance</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    <a href="{{ route('medications.reconciliation.discrepancies') }}" class="btn btn-outline-info">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        View All Discrepancies
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Medication Info --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-pills me-2"></i>
                        Medication Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <strong>Name:</strong> {{ $medication->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Generic Name:</strong> {{ $medication->generic_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Category:</strong> {{ $medication->category ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong> 
                            <span class="badge {{ $medication->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $medication->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Results --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Validation Results
                        @if(isset($validation['status']))
                            @php
                                $statusClass = match($validation['status']) {
                                    'valid' => 'bg-success',
                                    'warning' => 'bg-warning',
                                    'error' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} ms-2">{{ ucfirst($validation['status']) }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($validation) && count($validation) > 0)
                        {{-- Overall Status --}}
                        @if(isset($validation['status']))
                            <div class="alert {{ $validation['status'] === 'valid' ? 'alert-success' : ($validation['status'] === 'warning' ? 'alert-warning' : 'alert-danger') }}">
                                <h6>
                                    <i class="fas {{ $validation['status'] === 'valid' ? 'fa-check-circle' : ($validation['status'] === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle') }} me-2"></i>
                                    {{ $validation['message'] ?? 'Validation completed' }}
                                </h6>
                                @if(isset($validation['summary']))
                                    <p class="mb-0">{{ $validation['summary'] }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Balance Summary --}}
                        @if(isset($validation['balance_summary']))
                            <h6>Balance Summary</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        @foreach($validation['balance_summary'] as $key => $value)
                                            <tr>
                                                <td><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong></td>
                                                <td>
                                                    @if(is_numeric($value))
                                                        <span class="badge bg-primary">{{ $value }}</span>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Issues Found --}}
                        @if(isset($validation['issues']) && count($validation['issues']) > 0)
                            <h6>Issues Found</h6>
                            <div class="list-group mb-4">
                                @foreach($validation['issues'] as $issue)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    @php
                                                        $severity = $issue['severity'] ?? 'minor';
                                                        $iconClass = match($severity) {
                                                            'critical' => 'fas fa-exclamation-circle text-danger',
                                                            'major' => 'fas fa-exclamation-triangle text-warning',
                                                            'minor' => 'fas fa-info-circle text-info',
                                                            default => 'fas fa-question-circle text-secondary'
                                                        };
                                                    @endphp
                                                    <i class="{{ $iconClass }} me-2"></i>
                                                    {{ $issue['title'] ?? 'Issue' }}
                                                </h6>
                                                <p class="mb-1">{{ $issue['description'] ?? 'No description available' }}</p>
                                                @if(isset($issue['recommendation']))
                                                    <small class="text-muted">
                                                        <strong>Recommendation:</strong> {{ $issue['recommendation'] }}
                                                    </small>
                                                @endif
                                            </div>
                                            <span class="badge bg-{{ $severity === 'critical' ? 'danger' : ($severity === 'major' ? 'warning' : 'info') }}">
                                                {{ ucfirst($severity) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Location Breakdown --}}
                        @if(isset($validation['location_breakdown']) && count($validation['location_breakdown']) > 0)
                            <h6>Stock by Location</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Location</th>
                                            <th>Physical Stock</th>
                                            <th>Ledger Balance</th>
                                            <th>Difference</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($validation['location_breakdown'] as $location)
                                            <tr>
                                                <td>{{ $location['location_name'] ?? 'Unknown' }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $location['physical_stock'] ?? '0' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $location['ledger_balance'] ?? '0' }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $difference = ($location['physical_stock'] ?? 0) - ($location['ledger_balance'] ?? 0);
                                                        $diffClass = $difference > 0 ? 'text-success' : ($difference < 0 ? 'text-danger' : 'text-muted');
                                                    @endphp
                                                    <span class="{{ $diffClass }}">
                                                        {{ $difference > 0 ? '+' : '' }}{{ $difference }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = $location['status'] ?? 'unknown';
                                                        $statusClass = match($status) {
                                                            'balanced' => 'bg-success',
                                                            'discrepancy' => 'bg-warning',
                                                            'major_discrepancy' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Recommendations --}}
                        @if(isset($validation['recommendations']) && count($validation['recommendations']) > 0)
                            <h6>Recommendations</h6>
                            <ul class="list-unstyled">
                                @foreach($validation['recommendations'] as $recommendation)
                                    <li class="mb-2">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>
                                        {{ $recommendation }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <h5>No Validation Data Available</h5>
                            <p class="text-muted">Unable to validate medication balance at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Panel --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(isset($validation['status']) && $validation['status'] !== 'valid')
                            <a href="{{ route('medications.reconciliation.stock-correction') }}?medication_id={{ $medication->id }}" 
                               class="btn btn-warning">
                                <i class="fas fa-wrench me-2"></i>
                                Correct Discrepancy
                            </a>
                        @endif
                        
                        <a href="{{ route('medications.reconciliation.audit-trail') }}?medication_id={{ $medication->id }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-history me-2"></i>
                            View Movement History
                        </a>
                        
                        <button type="button" class="btn btn-outline-primary" onclick="refreshValidation()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Refresh Validation
                        </button>
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="exportValidation()">
                            <i class="fas fa-download me-2"></i>
                            Export Report
                        </button>
                    </div>
                </div>
            </div>

            {{-- Medication Stats --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Medication Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted">Total Locations</small>
                                <div class="h5 mb-0">
                                    {{ isset($validation['location_breakdown']) ? count($validation['location_breakdown']) : '0' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted">Total Stock</small>
                                <div class="h5 mb-0">
                                    {{ $validation['balance_summary']['total_physical_stock'] ?? '0' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted">Issues Found</small>
                                <div class="h5 mb-0 text-{{ isset($validation['issues']) && count($validation['issues']) > 0 ? 'danger' : 'success' }}">
                                    {{ isset($validation['issues']) ? count($validation['issues']) : '0' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted">Last Checked</small>
                                <div class="small mb-0">
                                    {{ $validation['last_validated'] ?? now()->format('M d, H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function refreshValidation() {
    // Show loading state
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;

    // Reload the page to refresh validation
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function exportValidation() {
    // Implement export functionality
    const medicationId = {{ $medication->id }};
    const url = `{{ route('medications.reconciliation.export-report') }}?report_type=validation&format=pdf&medication_id=${medicationId}`;
    window.open(url, '_blank');
}

// Auto-refresh validation every 5 minutes
setInterval(() => {
    // Check if user is still on the page
    if (!document.hidden) {
        const lastRefresh = localStorage.getItem('validationLastRefresh');
        const now = Date.now();
        
        if (!lastRefresh || (now - parseInt(lastRefresh)) > 300000) { // 5 minutes
            localStorage.setItem('validationLastRefresh', now.toString());
            
            // Show subtle notification
            const toast = document.createElement('div');
            toast.className = 'toast position-fixed top-0 end-0 m-3';
            toast.innerHTML = `
                <div class="toast-header">
                    <i class="fas fa-sync-alt me-2"></i>
                    <strong class="me-auto">Auto-refresh</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Validation data updated
                </div>
            `;
            document.body.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    }
}, 300000); // 5 minutes
</script>
@endsection
