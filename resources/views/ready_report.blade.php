@extends('layouts.app_main_layout')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-file-earmark-check text-success"></i>
                    Ready Results Report
                </h1>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filtersCard">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                    <button type="button" class="btn btn-success" onclick="printAllReadyResults()" 
                            title="Print all visits with ready results">
                        <i class="bi bi-printer-fill"></i> Print All Ready
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Page
                    </button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="collapse mb-4" id="filtersCard">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('readyInvResults') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Visits</option>
                                        <option value="ready" {{ $status === 'ready' ? 'selected' : '' }}>Ready (All Results Available)</option>
                                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending (Some Results Missing)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-search"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('readyInvResults') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-list-ul"></i> Total Visits
                            </h5>
                            <h2 class="mb-0">{{ $visits->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-check-circle"></i> Ready Results
                            </h5>
                            <h2 class="mb-0">{{ $visits->where('all_results_ready', true)->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-clock"></i> Pending Results
                            </h5>
                            <h2 class="mb-0">{{ $visits->where('all_results_ready', false)->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-flask"></i> Total Tests
                            </h5>
                            <h2 class="mb-0">{{ $visits->sum('total_investigations') }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visits Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-table"></i> 
                        Visits with Investigations 
                        <small class="text-muted">({{ $visits->count() }} visits found)</small>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($visits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Visit Date</th>
                                        <th>Doctor</th>
                                        <th>Investigations</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visits as $visit)
                                    <tr class="{{ $visit->all_results_ready ? 'table-success' : ($visit->pending_investigations > 0 ? 'table-warning' : '') }}">
                                        <td>
                                            <div>
                                                <strong>
                                                    {{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}
                                                </strong>
                                                @if($visit->patientInfo->middle_name)
                                                    {{ $visit->patientInfo->middle_name }}
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    MR #: {{ $visit->patientInfo->mr_number ?? 'N/A' }} |
                                                    Age: {{ $visit->patientInfo->age ?? 'N/A' }} |
                                                    {{ ucfirst($visit->patientInfo->gender ?? 'N/A') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($visit->visit_date)->format('l') }}</small>
                                        </td>
                                        <td>
                                            @if(optional($visit->doctorInfo)->user)
                                                Dr. {{ optional($visit->doctorInfo->user)->name }}
                                                <br>
                                                <small class="text-muted">{{ $visit->doctorInfo->specialty ?? 'General' }}</small>
                                            @else
                                                <span class="text-muted">No Doctor Assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="investigation-summary">
                                                <small class="text-muted d-block mb-1">
                                                    Total: {{ $visit->total_investigations }} tests
                                                </small>
                                                @foreach($visit->investigation_details as $detail)
                                                    <div class="investigation-item mb-1">
                                                        <span class="badge {{ $detail['status'] === 'resulted' ? 'bg-success' : 'bg-warning' }} me-1">
                                                            {{ ucfirst($detail['status']) }}
                                                        </span>
                                                        <small>{{ $detail['name'] }}</small>
                                                        @if($detail['priority'] === 'urgent' || $detail['priority'] === 'stat')
                                                            <span class="badge bg-danger ms-1">{{ strtoupper($detail['priority']) }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            @if($visit->all_results_ready)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> All Ready
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                @php
                                                    $progressPercentage = $visit->total_investigations > 0 
                                                        ? round(($visit->completed_investigations / $visit->total_investigations) * 100) 
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar {{ $visit->all_results_ready ? 'bg-success' : 'bg-warning' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $progressPercentage }}%">
                                                    {{ $progressPercentage }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $visit->completed_investigations }}/{{ $visit->total_investigations }} completed
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <a href="{{ route('patient_visits.show', $visit->id) }}" 
                                                   class="btn btn-outline-primary" title="View Visit">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                @if($visit->all_results_ready)
                                                    <button class="btn btn-success" 
                                                            onclick="printResults({{ $visit->id }})" 
                                                            title="Print All Ready Results">
                                                        <i class="bi bi-printer-fill"></i> Print All
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-success" 
                                                            onclick="printAvailableResults({{ $visit->id }})" 
                                                            title="Print Available Results">
                                                        <i class="bi bi-printer"></i> Print Available
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline-info" 
                                                        onclick="viewResultsDetails({{ $visit->id }})" 
                                                        title="View Results Details">
                                                    <i class="bi bi-list-check"></i> Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-clipboard-data" style="font-size: 3rem; opacity: 0.5;"></i>
                            <h5 class="text-muted mt-3">No visits with investigations found</h5>
                            <p class="text-muted">
                                @if($status !== 'all')
                                    Try adjusting the filters above or 
                                    <a href="{{ route('readyInvResults') }}">view all visits</a>.
                                @else
                                    There are no patient visits with investigations in the selected date range.
                                @endif
                            </p>
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
    function printResults(visitId) {
        // Print all ready results for a specific visit
        if (confirm('Print all ready results for this visit?')) {
            window.open(`/patient-visits/${visitId}/print-results?type=ready`, '_blank');
        }
    }
    
    function printAvailableResults(visitId) {
        // Print available results for a specific visit (even if not all are ready)
        if (confirm('Print available results for this visit? Some results may still be pending.')) {
            window.open(`/patient-visits/${visitId}/print-results?type=available`, '_blank');
        }
    }
    
    function viewResultsDetails(visitId) {
        // View detailed results information
        window.open(`/patient-visits/${visitId}/results-details`, '_blank');
    }
    
    function printAllReadyResults() {
        // Get all visits with ready results
        const readyVisits = [];
        @foreach($visits->where('all_results_ready', true) as $visit)
            readyVisits.push({{ $visit->id }});
        @endforeach
        
        if (readyVisits.length === 0) {
            alert('No visits have all results ready for printing.');
            return;
        }
        
        if (confirm(`Print results for all ${readyVisits.length} visits with ready results?`)) {
            // Create a form to send the visit IDs
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/print-multiple-results';
            form.target = '_blank';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrfToken.content;
                form.appendChild(tokenInput);
            }
            
            // Add visit IDs
            const visitIdsInput = document.createElement('input');
            visitIdsInput.type = 'hidden';
            visitIdsInput.name = 'visit_ids';
            visitIdsInput.value = JSON.stringify(readyVisits);
            form.appendChild(visitIdsInput);
            
            // Add to document and submit
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    }

    // Auto-refresh every 30 seconds to show updated results
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);

    // Show filters if there are any applied
    @if(request()->has(['date_from', 'date_to', 'status']) && request('status') !== 'all')
        document.getElementById('filtersCard').classList.add('show');
    @endif
    
    // Add click handlers for result status badges to show more details
    document.querySelectorAll('.badge').forEach(badge => {
        if (badge.textContent.includes('Ready') || badge.textContent.includes('Pending')) {
            badge.style.cursor = 'pointer';
            badge.title = 'Click for more details';
            badge.addEventListener('click', function() {
                const row = this.closest('tr');
                const investigationDetails = row.querySelector('.investigation-summary');
                if (investigationDetails.style.display === 'none') {
                    investigationDetails.style.display = 'block';
                } else {
                    investigationDetails.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection

@push('scripts')

<style>
    .investigation-item {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    
    .investigation-summary {
        max-width: 300px;
    }
    
    @media print {
        .btn, .card-header, #filtersCard {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table th {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endpush
