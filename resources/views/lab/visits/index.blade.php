@extends('layouts.app_main_layout')

@section('page_title', 'Lab - Patient Visits')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-vial text-primary"></i> 
                        Lab - Patient Visits with Pending Investigations
                    </h4>
                    <div>
                        <button class="btn btn-info" onclick="loadLabStatistics()">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </button>
                        <button class="btn btn-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <!-- Quick Stats -->
                    <div class="d-flex gap-2" id="lab-stats">
                        <div class="btn bg-warning text-white d-flex align-items-center px-3 py-2 border-0" style="min-height: 38px;">
                            <span class="fw-bold me-2" id="pending-collection">-</span>
                            <span class="small">Pending Collection</span>
                        </div>
                        <div class="btn bg-info text-white d-flex align-items-center px-3 py-2 border-0" style="min-height: 38px;">
                            <span class="fw-bold me-2" id="pending-results">-</span>
                            <span class="small">Pending Results</span>
                        </div>
                        <div class="btn bg-success text-white d-flex align-items-center px-3 py-2 border-0" style="min-height: 38px;">
                            <span class="fw-bold me-2" id="completed-today">-</span>
                            <span class="small">Completed Today</span>
                        </div>
                        <div class="btn bg-danger text-white d-flex align-items-center px-3 py-2 border-0" style="min-height: 38px;">
                            <span class="fw-bold me-2" id="urgent-investigations">-</span>
                            <span class="small">Urgent</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('lab.visits.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Patient Search</label>
                                    <input type="text" name="patient_search" class="form-control" 
                                           placeholder="Name or MR Number" value="{{ request('patient_search') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Doctor</label>
                                    <select name="doctor_id" class="form-select">
                                        <option value="">All Doctors</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->doctor_id }}" {{ request('doctor_id') == $doctor->doctor_id ? 'selected' : '' }}>
                                                Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="">All Priorities</option>
                                        <option value="stat" {{ request('priority') === 'stat' ? 'selected' : '' }}>STAT</option>
                                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="routine" {{ request('priority') === 'routine' ? 'selected' : '' }}>Routine</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Patient Visits Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient</th>
                                    <th>Visit Date</th>
                                    <th>Doctor</th>
                                    <th>Lab Investigations</th>
                                    <th>Priority Status</th>
                                    <th>Visit Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                                @forelse($visits as $visit)
                                @php
                                    // Get lab investigations through consultation relationship
                                    $labInvestigations = collect();
                                    if ($visit->consultation && $visit->consultation->investigations) {
                                        $labInvestigations = $visit->consultation->investigations->filter(function($investigation) {
                                            return $investigation->medicalService && 
                                                   $investigation->medicalService->serviceCategory &&
                                                   (
                                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'lab') ||
                                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'investigation') ||
                                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'pathology') ||
                                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'hematology') ||
                                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'biochemistry') ||
                                                       str_contains(strtolower($investigation->medicalService->serviceCategory->name), 'microbiology')
                                                   ) &&
                                                   in_array($investigation->status, ['ordered', 'collected', 'processing']);
                                        });
                                    }
                                    
                                    $urgentCount = $labInvestigations->whereIn('priority', ['urgent', 'stat'])->count();
                                    $totalCount = $labInvestigations->count();
                                @endphp
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong>
                                            @if($visit->patientInfo->middle_name)
                                                {{ $visit->patientInfo->middle_name }}
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                MR #: {{ $visit->patientInfo->mr_number ?? 'N/A' }} |
                                                Age: {{ $visit->patientInfo->age ?? 'N/A' }} |
                                                Gender: {{ ucfirst($visit->patientInfo->gender ?? 'N/A') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $visit->visit_date ? $visit->visit_date->format('M d, Y') : 'N/A' }}
                                            <br>
                                            <small class="text-muted">{{ $visit->visit_date ? $visit->visit_date->format('H:i A') : '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if(optional($visit->doctorInfo)->user)
                                            Dr. {{ optional($visit->doctorInfo->user)->first_name }} {{ optional($visit->doctorInfo->user)->last_name }}
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-primary">{{ $totalCount }} investigations</span>
                                            @if($urgentCount > 0)
                                                <br><span class="badge bg-danger mt-1">{{ $urgentCount }} urgent</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statuses = $labInvestigations->pluck('status')->unique();
                                        @endphp
                                        @foreach($statuses as $status)
                                            @php
                                                $statusClass = match($status) {
                                                    'ordered' => 'warning',
                                                    'collected' => 'info',
                                                    'processing' => 'primary',
                                                    'resulted' => 'success',
                                                    'cancelled' => 'secondary',
                                                    default => 'secondary'
                                                };
                                                $count = $labInvestigations->where('status', $status)->count();
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }} me-1">
                                                {{ $count }} {{ ucfirst($status) }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge {{ $visit->visit_status_badge_class }}">
                                            {{ $visit->visit_status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('lab.visits.investigations', $visit->id) }}" 
                                               class="btn btn-sm btn-primary" title="View Lab Investigations">
                                                <i class="fas fa-flask"></i> Lab Work
                                            </a>
                                            <a href="{{ route('patient_visits.show', $visit->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Full Visit Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-vial text-muted fa-3x mb-3"></i>
                                        <h5 class="text-muted">No patient visits with pending lab investigations</h5>
                                        <p class="text-muted mb-3">
                                            @if(request()->hasAny(['patient_search', 'doctor_id', 'priority', 'date_from', 'date_to']))
                                                Try adjusting your filters or 
                                                <a href="{{ route('lab.visits.index') }}" class="text-decoration-none">clear all filters</a>
                                            @else
                                                Lab investigations will appear here when patients have pending tests.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($visits->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $visits->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lab Statistics Modal -->
<div class="modal fade" id="labStatisticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar text-primary"></i> Lab Statistics
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="labStatisticsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading statistics...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load initial statistics
    loadLabStatistics();
    
    // Refresh stats every 5 minutes
    setInterval(loadLabStatistics, 300000);
});

function loadLabStatistics() {
    fetch('{{ route("lab.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('pending-collection').textContent = data.pending_collection || 0;
            document.getElementById('pending-results').textContent = data.pending_results || 0;
            document.getElementById('completed-today').textContent = data.completed_today || 0;
            document.getElementById('urgent-investigations').textContent = data.urgent_investigations || 0;
        })
        .catch(error => {
            console.error('Error loading lab statistics:', error);
        });
}

function showLabStatistics() {
    const modal = new bootstrap.Modal(document.getElementById('labStatisticsModal'));
    modal.show();
    
    // Load detailed statistics
    fetch('{{ route("lab.statistics") }}')
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">Pending Collection</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="text-warning">${data.pending_collection || 0}</h2>
                                <p class="text-muted">Investigations awaiting sample collection</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Pending Results</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="text-info">${data.pending_results || 0}</h2>
                                <p class="text-muted">Investigations awaiting results</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Completed Today</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="text-success">${data.completed_today || 0}</h2>
                                <p class="text-muted">Results completed today</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">Urgent Investigations</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="text-danger">${data.urgent_investigations || 0}</h2>
                                <p class="text-muted">STAT and urgent priority</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('labStatisticsContent').innerHTML = content;
        })
        .catch(error => {
            document.getElementById('labStatisticsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error loading statistics: ${error.message}
                </div>
            `;
        });
}
</script>
@endpush
@endsection
