@extends('layouts.app_main_layout')

@section('page_title', 
    $user->role === 'nurse' ? 'Nursing Procedures Management' : 
    ($user->role === 'doctor' ? 'Doctor Procedures Management' : 
    ($user->role === 'radiologist' ? 'Radiology Procedures Management' : 
    'Procedure Results Management'))
)

@section('main_content')
<div class="container-fluid {{ $user->role }}-theme">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        @if($user->role === 'nurse')
                            <i class="fas fa-user-nurse text-primary"></i> Nursing Procedures Management
                        @elseif($user->role === 'doctor')
                            <i class="fas fa-user-md text-success"></i> Doctor Procedures Management
                        @elseif($user->role === 'radiologist')
                            <i class="fas fa-x-ray text-info"></i> Radiology Procedures Management
                        @else
                            <i class="fas fa-clipboard-list text-secondary"></i> Procedure Results Management
                        @endif
                        <small class="text-muted ms-2">({{ ucfirst($user->role) }} View)</small>
                    </h4>
                    <div>
                        <button class="btn btn-info" onclick="loadStatistics()">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </button>
                        <button class="btn btn-secondary" onclick="refreshList()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('procedures.index') }}" class="row g-3">
                                
                                @if($user->role === 'doctor')
                                    <!-- Doctor-specific filter type -->
                                    <div class="col-md-2">
                                        <label class="form-label">Filter Type</label>
                                        <select name="filter_type" class="form-select">
                                            <option value="">All Procedures</option>
                                            <option value="my_procedures" {{ request('filter_type') === 'my_procedures' ? 'selected' : '' }}>My Procedures</option>
                                            <option value="pending_review" {{ request('filter_type') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                                        </select>
                                    </div>
                                @endif

                                <div class="{{ $user->role === 'doctor' ? 'col-md-3' : 'col-md-3' }}">
                                    <label class="form-label">Service Category</label>
                                    <select name="service_category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($serviceCategories as $category)
                                            <option value="{{ $category->id }}" {{ request('service_category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                @if($user->role !== 'doctor')
                                    <!-- Show doctor filter for non-doctors -->
                                    <div class="col-md-3">
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
                                @endif
                                <div class="col-md-2">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="">All Priorities</option>
                                        <option value="stat" {{ request('priority') === 'stat' ? 'selected' : '' }}>STAT</option>
                                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="routine" {{ request('priority') === 'routine' ? 'selected' : '' }}>Routine</option>
                                    </select>
                                </div>
                                <div class="{{ $user->role === 'doctor' ? 'col-md-3' : 'col-md-3' }}">
                                    <label class="form-label">Patient Search</label>
                                    <input type="text" name="patient_search" class="form-control" 
                                           placeholder="Name or MR Number" value="{{ request('patient_search') }}">
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

                    <!-- Quick Stats -->
                    <div class="row mb-4" id="quick-stats">
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $investigations->total() }}</h4>
                                    <p class="mb-0">
                                        @if($user->role === 'nurse')
                                            Nursing Procedures
                                        @elseif($user->role === 'doctor')  
                                            Doctor Procedures
                                        @elseif($user->role === 'radiologist')
                                            Radiology Studies
                                        @else
                                            Pending Results
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $investigations->where('priority', 'urgent')->count() + $investigations->where('priority', 'stat')->count() }}</h4>
                                    <p class="mb-0">Urgent/STAT</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $investigations->where('status', 'processing')->count() }}</h4>
                                    <p class="mb-0">In Progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $investigations->where('status', 'collected')->count() }}</h4>
                                    <p class="mb-0">
                                        @if($user->role === 'nurse')
                                            Ready for Processing
                                        @else
                                            Sample Collected
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Procedures Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>SN</th>
                                    <th>MR Number</th>
                                    <th>Patient Name</th>
                                    <th>Age</th>
                                    @if($user->role !== 'doctor')
                                        <th>Ordered By</th>
                                    @endif
                                    <th>
                                        @if($user->role === 'nurse')
                                            Nursing Procedure
                                        @elseif($user->role === 'radiologist')
                                            Study Name
                                        @else
                                            Procedure Name
                                        @endif
                                    </th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Result Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($investigations as $index => $investigation)
                                    <tr class="{{ $investigation->isOverdue() ? 'table-danger' : '' }} {{ $investigation->priority === 'stat' ? 'table-warning' : '' }}" data-investigation-id="{{ $investigation->id }}">
                                        <td>{{ $index + 1 }}.</td>
                                        <td>
                                            <strong>{{ $investigation->patient->mr_number ?? 'N/A' }}</strong>
                                            @if($investigation->isOverdue())
                                                <br><span class="badge bg-danger">OVERDUE</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($investigation->patient)
                                                {{ $investigation->patient->first_name }} {{ $investigation->patient->last_name }}
                                            @else
                                                <span class="text-muted">Unknown Patient</span>
                                            @endif
                                        </td>
                                        <td>{{ $investigation->formatted_age ?? 'N/A' }}</td>
                                        @if($user->role !== 'doctor')
                                        <td>
                                            @if($investigation->doctor)
                                                Dr. {{ $investigation->doctor->user->first_name }} {{ $investigation->doctor->user->last_name }}
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td>
                                            @if($investigation->medicalService)
                                                <a href="{{ route('procedures.show', $investigation) }}" class="text-decoration-none">
                                                    <strong>{{ $investigation->medicalService->name }}</strong>
                                                </a>
                                                @if($investigation->medicalService->requires_sample)
                                                    <br><small class="text-info">Sample: {{ $investigation->medicalService->sample_type }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Unknown Service</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $investigation->priority_badge_class }}">
                                                {{ strtoupper($investigation->priority) }}
                                            </span>
                                        </td>
                                        <td>{{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ $investigation->ordered_at ? $investigation->ordered_at->format('H:i') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $investigation->status_badge_class }}">
                                                @if($investigation->status === 'ordered')
                                                    Ordered
                                                @elseif($investigation->status === 'collected')
                                                    Sample Collected
                                                @elseif($investigation->status === 'processing')
                                                    Processing
                                                @elseif($investigation->status === 'resulted')
                                                    Resulted
                                                @else
                                                    {{ ucfirst($investigation->status) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $result = $investigation->results->first();
                                            @endphp
                                            @if($result)
                                                @if($result->form_status === 'draft')
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-edit"></i> Draft
                                                    </span>
                                                @elseif($result->form_status === 'preliminary')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Preliminary
                                                    </span>
                                                @elseif($result->form_status === 'final')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Final
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-minus"></i> No Results
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @php
                                                    $result = $investigation->results->first();
                                                    $canEdit = $result && $result->form_status !== 'final';
                                                @endphp
                                                
                                                <!-- Role-specific Main Action Button -->
                                                @if($user->role === 'nurse')
                                                    <!-- Nurse: Focus on procedures/starting -->
                                                    @if($investigation->status === 'ordered')
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                               onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                               title="Start Procedure">
                                                            <i class="fas fa-play"></i> Start Procedure
                                                        </button>
                                                    @elseif($investigation->status === 'processing')
                                                        <a href="{{ route('procedures.show', $investigation) }}" 
                                                           class="btn btn-sm btn-outline-success" 
                                                           title="Complete Procedure">
                                                            <i class="fas fa-check"></i> Complete
                                                        </a>
                                                    @else
                                                        <a href="{{ route('procedures.show', $investigation) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="View Procedure">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    @endif
                                                    
                                                @elseif($user->role === 'radiologist')
                                                    <!-- Radiologist: Focus on imaging studies -->
                                                    @if(in_array($investigation->status, ['collected', 'processing']) || ($result && $result->form_status !== 'final'))
                                                        @if(!$result || $result->form_status === 'draft' || $result->form_status === 'preliminary')
                                                            <a href="{{ route('procedures.show', $investigation) }}" 
                                                               class="btn btn-sm {{ $result ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                               title="{{ $result ? 'Edit Report' : 'Create Report' }}">
                                                                <i class="fas {{ $result ? 'fa-edit' : 'fa-plus' }}"></i>
                                                                {{ $result ? 'Edit Report' : 'Create Report' }}
                                                            </a>
                                                        @endif
                                                    @endif
                                                    
                                                    @if($investigation->status === 'resulted' && $result)
                                                        <a href="{{ route('procedures.show', $investigation) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="View Report">
                                                            <i class="fas fa-eye"></i> View Report
                                                        </a>
                                                    @endif
                                                    
                                                @else
                                                    <!-- Doctor/Lab: Standard result entry -->
                                                    @if(in_array($investigation->status, ['collected', 'processing']) || ($result && $result->form_status !== 'final'))
                                                        @if(!$result || $result->form_status === 'draft' || $result->form_status === 'preliminary')
                                                            <a href="{{ route('procedures.show', $investigation) }}" 
                                                               class="btn btn-sm {{ $result ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                               title="{{ $result ? 'Edit Results' : 'Add Results' }}">
                                                                <i class="fas {{ $result ? 'fa-edit' : 'fa-plus' }}"></i>
                                                                {{ $result ? 'Edit' : 'Add' }}
                                                            </a>
                                                        @endif
                                                    @endif
                                                    
                                                    @if($investigation->status === 'resulted' && $result)
                                                        <a href="{{ route('procedures.show', $investigation) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="View Results">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    @endif
                                                @endif

                                                <!-- Role-specific Status Update Buttons -->
                                                @if($user->role === 'nurse')
                                                    <!-- Nurse-specific actions -->
                                                    @if($investigation->status === 'ordered' && $investigation->medicalService && $investigation->medicalService->requires_sample)
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'collected')"
                                                                title="Mark Sample Collected (Stock will be deducted)">
                                                            <i class="fas fa-flask"></i>
                                                        </button>
                                                    @endif
                                                    @if($investigation->status === 'ordered' && (!$investigation->medicalService || !$investigation->medicalService->requires_sample))
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                                title="Start Procedure (⚠️ Stock will be deducted)">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    @endif
                                                    @if($investigation->status === 'collected')
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                                title="Start Procedure (No additional stock deduction)">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    @endif
                                                    
                                                @elseif($user->role === 'radiologist')
                                                    <!-- Radiologist-specific actions -->
                                                    @if($investigation->status === 'ordered' && (!$investigation->medicalService || !$investigation->medicalService->requires_sample))
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                                title="Start Study (⚠️ Stock will be deducted)">
                                                            <i class="fas fa-x-ray"></i>
                                                        </button>
                                                    @endif
                                                    @if($investigation->status === 'collected')
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                                title="Start Study (No additional stock deduction)">
                                                            <i class="fas fa-x-ray"></i>
                                                        </button>
                                                    @endif
                                                    
                                                @else
                                                    <!-- Doctor/Lab actions -->
                                                    @if($investigation->status === 'ordered' && $investigation->medicalService && $investigation->medicalService->requires_sample)
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'collected')"
                                                                title="Mark Sample Collected (Stock will be deducted)">
                                                            <i class="fas fa-flask"></i>
                                                        </button>
                                                    @endif
                                                    @if($investigation->status === 'ordered' && (!$investigation->medicalService || !$investigation->medicalService->requires_sample))
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                                title="Start Processing (⚠️ Stock will be deducted)">
                                                            <i class="fas fa-spinner"></i>
                                                        </button>
                                                    @endif
                                                    @if($investigation->status === 'collected')
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                                                                title="Start Processing (No additional stock deduction)">
                                                            <i class="fas fa-spinner"></i>
                                                        </button>
                                                    @endif
                                                @endif

                                                <!-- Common action buttons -->
                                                @if($result)
                                                    <a href="{{ route('procedures.view-results', $investigation) }}" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       title="Generate Report">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                @endif
                                                
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        onclick="showStockDetailsForInvestigation({{ $investigation->id }})"
                                                        title="Check Stock">
                                                    <i class="fas fa-boxes"></i>
                                                </button>
                                                
                                                @if(in_array($investigation->status, ['ordered', 'collected', 'processing']))
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="updateInvestigationStatus({{ $investigation->id }}, 'cancelled')"
                                                            title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $user->role === 'doctor' ? '11' : '12' }}" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                                <p>
                                                    @if($user->role === 'nurse')
                                                        No nursing procedures awaiting attention
                                                    @elseif($user->role === 'radiologist')
                                                        No radiology studies awaiting reports
                                                    @else
                                                        No procedures awaiting results
                                                    @endif
                                                </p>
                                                <small>All investigations are up to date!</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $investigations->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div class="modal fade" id="statisticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Procedure Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="statistics-content">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* Base styling */
.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Role-based theme classes */
.nurse-theme .card-header h4 {
    color: #0d6efd; /* Bootstrap primary blue for nurses */
}
.nurse-theme .table-dark {
    background-color: #0d6efd !important;
}
.nurse-theme .btn-outline-primary:not(.dropdown-toggle) {
    border-color: #0d6efd;
    color: #0d6efd;
}
.nurse-theme .btn-outline-primary:not(.dropdown-toggle):hover {
    background-color: #0d6efd;
    color: #fff;
}

.doctor-theme .card-header h4 {
    color: #198754; /* Bootstrap success green for doctors */
}
.doctor-theme .table-dark {
    background-color: #198754 !important;
}

.radiologist-theme .card-header h4 {
    color: #0dcaf0; /* Bootstrap info cyan for radiologists */
}
.radiologist-theme .table-dark {
    background-color: #0dcaf0 !important;
    color: #000 !important;
}
.radiologist-theme .table-dark th {
    color: #000 !important;
}
.radiologist-theme .btn-outline-success:not(.dropdown-toggle) {
    border-color: #0dcaf0;
    color: #0dcaf0;
}
.radiologist-theme .btn-outline-success:not(.dropdown-toggle):hover {
    background-color: #0dcaf0;
    color: #000;
}

/* Common styling */
.table-row-urgent {
    border-left: 4px solid #dc3545;
}

.table-row-stat {
    border-left: 4px solid #fd7e14;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { border-left-color: #fd7e14; }
    50% { border-left-color: #dc3545; }
    100% { border-left-color: #fd7e14; }
}

.card-stat {
    transition: transform 0.2s;
}

.card-stat:hover {
    transform: translateY(-2px);
}

/* Enhanced disabled button styling */
.btn.disabled, .btn:disabled {
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.dropdown-item.disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    color: #6c757d !important;
}

/* Low stock row highlighting */
.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-left: 3px solid #ffc107 !important;
}

/* Stock warning badge */
.badge.bg-warning {
    cursor: pointer;
    transition: all 0.2s ease;
}

.badge.bg-warning:hover {
    background-color: #e0a800 !important;
    transform: scale(1.05);
}
</style>
@endsection

@section('scripts')
<script>
function updateStatus(investigationId, newStatus) {
    if (confirm(`Mark investigation as ${newStatus}?`)) {
        fetch(`/investigations/${investigationId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating status');
        });
    }
}

function loadStatistics() {
    const modal = new bootstrap.Modal(document.getElementById('statisticsModal'));
    modal.show();
    
    fetch('/procedures/statistics')
        .then(response => response.json())
        .then(data => {
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><h6>Summary</h6></div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><td>Pending Procedures:</td><td><strong>${data.pending_procedures}</strong></td></tr>
                                    <tr><td>Urgent Pending:</td><td><strong class="text-danger">${data.urgent_pending}</strong></td></tr>
                                    <tr><td>Overdue:</td><td><strong class="text-warning">${data.overdue_procedures}</strong></td></tr>
                                    <tr><td>Completed Today:</td><td><strong class="text-success">${data.completed_today}</strong></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><h6>By Category</h6></div>
                            <div class="card-body">
                                <table class="table table-sm">
            `;
            
            Object.entries(data.by_category).forEach(([category, count]) => {
                content += `<tr><td>${category}:</td><td><strong>${count}</strong></td></tr>`;
            });
            
            content += `
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header"><h6>Weekly Completion Trend</h6></div>
                            <div class="card-body">
                                <div class="row">
            `;
            
            Object.entries(data.weekly_completion).forEach(([date, count]) => {
                content += `
                    <div class="col text-center">
                        <div class="badge bg-primary mb-1">${count}</div>
                        <div><small>${new Date(date).toLocaleDateString()}</small></div>
                    </div>
                `;
            });
            
            content += `
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('statistics-content').innerHTML = content;
        })
        .catch(error => {
            document.getElementById('statistics-content').innerHTML = 
                '<div class="alert alert-danger">Error loading statistics</div>';
        });
}

function refreshList() {
    location.reload();
}

// Function to show role-specific low stock alerts
function showLowStockAlert(userRole, actionType) {
    let message = '';
    
    switch(actionType) {
        case 'collect':
            message = userRole === 'nurse' ? 'Cannot collect sample - insufficient stock in laboratory' : 'Cannot mark as collected - insufficient stock available';
            break;
        case 'process':
            message = userRole === 'nurse' ? 'Cannot start procedure - insufficient supplies available' : 
                     userRole === 'radiologist' ? 'Cannot start study - insufficient materials available' :
                     'Cannot mark as processing - insufficient stock available';
            break;
        case 'results':
            message = userRole === 'radiologist' ? 'Cannot create report - procedure cannot proceed due to low stock' :
                     'Cannot add results - collection blocked due to insufficient stock';
            break;
        default:
            message = 'Cannot proceed - insufficient stock available';
    }
    
    // Show a more styled alert/notification
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        // Create toast notification if Bootstrap is available
        const toastHtml = `
            <div class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.body.lastElementChild;
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    } else {
        // Fallback to alert
        alert(message);
    }
}

// Update investigation status function (links to LabController)
function updateInvestigationStatus(investigationId, newStatus) {
    if (!confirm(`Are you sure you want to mark this procedure as ${newStatus}?`)) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/lab/investigations/${investigationId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw { status: response.status, data: errorData };
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
            
            // Reload the page to show updated status
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Check if this is a stock availability error (422 status)
        if (error.status === 422 && error.data.stock_details) {
            // Show stock error modal
            const stockLocation = error.data.stock_location || 'Laboratory';
            showStockErrorModal(error.data.stock_details, error.data.message, stockLocation);
        } else {
            alert('Failed to update status. Please try again.');
        }
    });
}

// Show stock error modal when consumables are insufficient
function showStockErrorModal(stockDetails, message, stockLocation = 'Laboratory') {
    const modalHtml = `
        <div class="modal fade" id="stockErrorModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Insufficient Stock
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            ${message}
                        </div>
                        
                        <h6 class="mb-3">Stock Requirements (${stockLocation} Location):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Required</th>
                                        <th>Available (Lab)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${stockDetails.map(item => `
                                        <tr class="${!item.is_available && !item.is_optional ? 'table-danger' : item.is_available ? 'table-success' : 'table-warning'}">
                                            <td>
                                                ${item.medication_name || 'Unknown Item'}
                                                ${item.is_optional ? '<small class="text-muted">(Optional)</small>' : ''}
                                            </td>
                                            <td>${item.required_quantity}</td>
                                            <td>${item.available_quantity}</td>
                                            <td>
                                                ${item.is_available 
                                                    ? '<span class="badge bg-success">Sufficient</span>' 
                                                    : item.is_optional 
                                                        ? '<span class="badge bg-warning">Low Stock (Optional)</span>'
                                                        : '<span class="badge bg-danger">Insufficient</span>'
                                                }
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Collection cannot proceed until all required items have sufficient stock in the ${stockLocation} location.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('stockErrorModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('stockErrorModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('stockErrorModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Function to show stock details modal (like in lab interface)
function showStockDetailsForInvestigation(investigationId) {
    if (!investigationId) {
        alert('Please specify an investigation ID');
        return;
    }
    
    // Show loading state
    const loadingHtml = `
        <div class="modal fade" id="stockLoadingModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                        <p>Checking stock availability...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loadingHtml);
    const loadingModal = new bootstrap.Modal(document.getElementById('stockLoadingModal'));
    loadingModal.show();
    
    // Fetch stock details for this specific investigation
    fetch(`/lab/investigations/${investigationId}/check-stock`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        loadingModal.hide();
        document.getElementById('stockLoadingModal').remove();
        
        if (!response.ok) {
            return response.json().then(errorData => {
                throw { status: response.status, data: errorData };
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.details) {
            // Use the same modal as the error modal but with different title and styling
            const stockLocation = data.stock_location || 'Laboratory';
            showStockDetailsModal(data.details, data.message || 'Stock information for this procedure:', data.can_proceed, stockLocation);
        } else {
            alert('No stock details available for this procedure.');
        }
    })
    .catch(error => {
        console.error('Error fetching stock details:', error);
        alert('Failed to fetch stock details. Please try again.');
    });
}

// Show stock details modal (enhanced version of showStockErrorModal)
function showStockDetailsModal(stockDetails, message, canProceed = false, stockLocation = 'Laboratory') {
    const modalTitle = canProceed ? 'Stock Information' : 'Insufficient Stock';
    const headerClass = canProceed ? 'bg-info' : 'bg-warning';
    const alertClass = canProceed ? 'alert-info' : 'alert-warning';
    
    const modalHtml = `
        <div class="modal fade" id="stockDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header ${headerClass}">
                        <h5 class="modal-title text-white">
                            <i class="fas ${canProceed ? 'fa-info-circle' : 'fa-exclamation-triangle'}"></i>
                            ${modalTitle}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="${alertClass}">
                            <i class="fas fa-info-circle"></i>
                            ${message}
                        </div>
                        
                        <h6 class="mb-3">Stock Requirements (${stockLocation}):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Required</th>
                                        <th>Available (${stockLocation})</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${stockDetails.map(item => `
                                        <tr class="${!item.is_available && !item.is_optional ? 'table-danger' : item.is_available ? 'table-success' : 'table-warning'}">
                                            <td>
                                                ${item.medication_name || 'Unknown Item'}
                                                ${item.is_optional ? '<small class="text-muted">(Optional)</small>' : ''}
                                            </td>
                                            <td>${item.required_quantity}</td>
                                            <td>${item.available_quantity}</td>
                                            <td>
                                                ${item.is_available 
                                                    ? '<span class="badge bg-success">Sufficient</span>' 
                                                    : item.is_optional 
                                                        ? '<span class="badge bg-warning">Low Stock (Optional)</span>'
                                                        : '<span class="badge bg-danger">Insufficient</span>'
                                                }
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                ${canProceed 
                                    ? 'All required items have sufficient stock. Collection can proceed.'
                                    : `Collection cannot proceed until all required items have sufficient stock in the ${stockLocation} location.`
                                }
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('stockDetailsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('stockDetailsModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('stockDetailsModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Check stock for all procedures on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get all investigation rows
    const investigationRows = document.querySelectorAll('tr[data-investigation-id]');
    
    investigationRows.forEach(row => {
        const investigationId = row.getAttribute('data-investigation-id');
        
        if (investigationId) {
            // Check stock for this investigation
            fetch(`/lab/investigations/${investigationId}/check-stock`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && !data.can_proceed) {
                    // Add low stock indicator to the row
                    row.classList.add('table-warning');
                    
                    // Add warning icon to the status column (10th column)
                    const statusCell = row.querySelector('td:nth-child(10)');
                    if (statusCell) {
                        const warning = document.createElement('span');
                        warning.className = 'badge bg-warning ms-2';
                        warning.style.cursor = 'pointer';
                        warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Low Stock';
                        warning.title = 'Click to view stock details - collection blocked';
                        warning.onclick = function() {
                            showStockDetailsForInvestigation(investigationId);
                        };
                        statusCell.appendChild(warning);
                    }
                    
                    // Disable the collect button
                    const collectBtn = row.querySelector('button[onclick*="collected"]');
                    if (collectBtn) {
                        collectBtn.classList.add('disabled', 'btn-outline-secondary');
                        collectBtn.classList.remove('btn-outline-primary', 'btn-outline-info');
                        collectBtn.style.pointerEvents = 'none';
                        collectBtn.title = 'Cannot collect - insufficient stock';
                        collectBtn.innerHTML = collectBtn.innerHTML + ' <i class="fas fa-exclamation-triangle text-warning"></i>';
                        
                        // Prevent click events
                        collectBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            showLowStockAlert('{{ $user->role ?? "default" }}', 'collect');
                        });
                    }
                    
                    // Disable the "Start Procedure" button for nurses and other processing buttons
                    const startProcedureBtn = row.querySelector('button[onclick*="processing"]');
                    if (startProcedureBtn) {
                        startProcedureBtn.classList.add('disabled', 'btn-outline-secondary');
                        startProcedureBtn.classList.remove('btn-outline-primary');
                        startProcedureBtn.style.pointerEvents = 'none';
                        startProcedureBtn.title = 'Cannot start procedure - insufficient stock';
                        
                        // Add warning icon if not already present
                        if (!startProcedureBtn.innerHTML.includes('fa-exclamation-triangle')) {
                            startProcedureBtn.innerHTML = startProcedureBtn.innerHTML + ' <i class="fas fa-exclamation-triangle text-warning"></i>';
                        }
                        
                        startProcedureBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            showLowStockAlert('{{ $user->role ?? "default" }}', 'process');
                        });
                    }
                    
                    // Disable action buttons in dropdown
                    const actionBtns = row.querySelectorAll('.dropdown-menu a[onclick*="collected"], .dropdown-menu a[onclick*="processing"], .dropdown-menu a[href*="procedures.show"]');
                    actionBtns.forEach(btn => {
                        btn.classList.add('disabled');
                        btn.style.pointerEvents = 'none';
                        btn.style.opacity = '0.6';
                        btn.style.color = '#6c757d';
                        
                        // Add warning icon to dropdown items
                        if (!btn.querySelector('.fa-exclamation-triangle')) {
                            const icon = btn.querySelector('i');
                            if (icon) {
                                icon.parentNode.insertBefore(document.createTextNode(' '), icon.nextSibling);
                                const warningIcon = document.createElement('i');
                                warningIcon.className = 'fas fa-exclamation-triangle text-warning ms-1';
                                icon.parentNode.insertBefore(warningIcon, icon.nextSibling.nextSibling);
                            }
                        }
                        
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Determine action type based on button content/onclick
                            let actionType = 'default';
                            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes('collected')) {
                                actionType = 'collect';
                            } else if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes('processing')) {
                                actionType = 'process';
                            } else if (btn.getAttribute('href') && btn.getAttribute('href').includes('procedures.show')) {
                                actionType = 'results';
                            }
                            
                            showLowStockAlert('{{ $user->role ?? "default" }}', actionType);
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error checking stock for procedure', investigationId, ':', error);
            });
        }
    });
    
    // Existing priority highlighting
    document.querySelectorAll('tr').forEach(row => {
        const priorityBadge = row.querySelector('.badge');
        if (priorityBadge && priorityBadge.textContent.includes('STAT')) {
            row.classList.add('table-row-stat');
        } else if (priorityBadge && priorityBadge.textContent.includes('URGENT')) {
            row.classList.add('table-row-urgent');
        }
    });
});

// Auto-refresh removed to prevent CSRF token conflicts during form submission

// Initialize DataTable
$('.table').DataTable({
    responsive: true,
    order: [[5, 'desc']], // Order by date ordered (descending)
    pageLength: 25,
    columnDefs: [
        { orderable: false, targets: [-1] } // Disable sorting on actions column
    ],
    language: {
        search: "Search procedures:",
        lengthMenu: "Show _MENU_ procedures per page",
        info: "Showing _START_ to _END_ of _TOTAL_ procedures",
        infoEmpty: "No procedures found",
        infoFiltered: "(filtered from _MAX_ total procedures)"
    }
});
</script>
@endsection
