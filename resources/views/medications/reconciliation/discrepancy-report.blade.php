@extends('layouts.app_main_layout')

@section('page_title', 'Discrepancy Report')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Stock Discrepancy Report
                    </h1>
                    <p class="text-muted mb-0">Detailed analysis of stock integrity issues and recommendations</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class="fas fa-download me-2"></i>
                        Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.reconciliation.discrepancies') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="medication_id" class="form-label">Medication</label>
                                <select name="medication_id" id="medication_id" class="form-select">
                                    <option value="">All Medications</option>
                                    @foreach($medications as $medication)
                                        <option value="{{ $medication->id }}" 
                                            {{ request('medication_id') == $medication->id ? 'selected' : '' }}>
                                            {{ $medication->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="location_id" class="form-label">Location</label>
                                <select name="location_id" id="location_id" class="form-select">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="severity" class="form-label">Severity</label>
                                <select name="severity" id="severity" class="form-select">
                                    <option value="">All Severities</option>
                                    <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="major" {{ request('severity') == 'major' ? 'selected' : '' }}>Major</option>
                                    <option value="minor" {{ request('severity') == 'minor' ? 'selected' : '' }}>Minor</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    Apply Filters
                                </button>
                                <a href="{{ route('medications.reconciliation.discrepancies') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Critical Issues</h5>
                            <h2 class="mb-0">{{ collect($detailedReport)->where('severity', 'critical')->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Major Issues</h5>
                            <h2 class="mb-0">{{ collect($detailedReport)->where('severity', 'major')->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Minor Issues</h5>
                            <h2 class="mb-0">{{ collect($detailedReport)->where('severity', 'minor')->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Checked</h5>
                            <h2 class="mb-0">{{ collect($detailedReport)->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Discrepancies Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Detailed Discrepancies
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($detailedReport) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Medication</th>
                                        <th>Batch Number</th>
                                        <th>Issues</th>
                                        <th>Current Qty</th>
                                        <th>Calculated Qty</th>
                                        <th>Status</th>
                                        <th>Expiry Date</th>
                                        <th>Severity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detailedReport as $discrepancy)
                                        <tr>
                                            <td>
                                                <strong>{{ $discrepancy['medication'] ?? 'Unknown' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $discrepancy['batch_number'] ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if(isset($discrepancy['issues']) && is_array($discrepancy['issues']))
                                                    @foreach($discrepancy['issues'] as $issue)
                                                        <span class="badge bg-warning me-1">{{ $issue }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No specific issues</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $discrepancy['current_quantity'] ?? '0' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $discrepancy['calculated_quantity'] ?? '0' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $status = $discrepancy['status'] ?? 'unknown';
                                                    $badgeClass = match($status) {
                                                        'active' => 'bg-success',
                                                        'expired' => 'bg-danger',
                                                        'inactive' => 'bg-secondary',
                                                        default => 'bg-warning'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                            </td>
                                            <td>
                                                @if(isset($discrepancy['expiry_date']))
                                                    {{ \Carbon\Carbon::parse($discrepancy['expiry_date'])->format('Y-m-d') }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $severity = $discrepancy['severity'] ?? 'minor';
                                                    $severityClass = match($severity) {
                                                        'critical' => 'bg-danger',
                                                        'major' => 'bg-warning',
                                                        'minor' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $severityClass }}">{{ ucfirst($severity) }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if(isset($discrepancy['medication_id']))
                                                        <a href="{{ route('medications.reconciliation.corrections.form') }}?medication_id={{ $discrepancy['medication_id'] }}"
                                                           class="btn btn-outline-warning" title="Apply correction">
                                                            <i class="fas fa-wrench"></i>
                                                        </a>
                                                        <a href="{{ route('medications.reconciliation.medications.validate', $discrepancy['medication_id']) }}"
                                                           class="btn btn-outline-info" title="Full validation">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h4>No Discrepancies Found</h4>
                            <p class="text-muted">All stock levels appear to be in order. Great job maintaining stock integrity!</p>
                            <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
@endsection
