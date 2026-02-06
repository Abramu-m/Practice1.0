@extends('layouts.app_main_layout')

@section('main_content')
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Search and Filter Card -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('cashier.index') }}" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Patients</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Patient name, SIC No, Authorization No, NHIF Ref...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Visit Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Visits</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Waiting</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>In Treatment</option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Discharged</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Patient Visits Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-people"></i>
                        Patient Visits ({{ $visits->total() }} total)
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $visits->count() }} shown</span>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Visit Date</th>
                                <th>Doctor</th>
                                <th>Visit Status</th>
                                <th>Investigations</th>
                                <th>Prescriptions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visits as $visit)
                            <tr>
                                <td>
                                    @if($visit->patientInfo)
                                        <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong>
                                        @if($visit->patientInfo->middle_name)
                                            {{ $visit->patientInfo->middle_name }}
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            ID: {{ $visit->patientInfo->id }}
                                            @if($visit->patientInfo->dob)
                                                | Age: {{ \Carbon\Carbon::parse($visit->patientInfo->dob)->age }}
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-danger">Patient not found</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $visit->visit_date ? $visit->visit_date->format('M d, Y') : 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $visit->visit_date ? $visit->visit_date->format('h:i A') : '' }}</small>
                                </td>
                                <td>
                                        @if(optional($visit->doctorInfo)->user)
                                            {{ optional($visit->doctorInfo->user)->name }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $visit->visit_status_badge_class }}">
                                        {{ $visit->visit_status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($visit->investigations_count > 0)
                                        <button class="btn btn-sm btn-outline-info mb-1" 
                                                onclick="viewInvestigations({{ $visit->id }})">
                                            <i class="bi bi-eye"></i>
                                            {{ $visit->investigations_count }} Investigation{{ $visit->investigations_count > 1 ? 's' : '' }}
                                        </button>
                                        <br>
                                        <div class="small">
                                            @php
                                                $paidInvestigations = $visit->investigations->where('is_paid', true)->count();
                                                $unpaidInvestigations = $visit->investigations_count - $paidInvestigations;
                                            @endphp
                                            <span class="badge badge-success">
                                                <i class="bi bi-check-circle"></i> {{ $paidInvestigations }} Paid
                                            </span>
                                            @if($unpaidInvestigations > 0)
                                                <span class="badge badge-warning">
                                                    <i class="bi bi-clock"></i> {{ $unpaidInvestigations }} Pending
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No investigations</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($visit->prescriptions_count > 0)
                                        <button class="btn btn-sm btn-outline-success mb-1" 
                                                onclick="viewPrescriptions({{ $visit->id }})">
                                            <i class="bi bi-eye"></i>
                                            {{ $visit->prescriptions_count }} Prescription{{ $visit->prescriptions_count > 1 ? 's' : '' }}
                                        </button>
                                        <br>
                                        <div class="small">
                                            @php
                                                $paidPrescriptions = $visit->prescriptions->where('is_paid', true)->count();
                                                $unpaidPrescriptions = $visit->prescriptions_count - $paidPrescriptions;
                                            @endphp
                                            <span class="badge badge-success">
                                                <i class="bi bi-check-circle"></i> {{ $paidPrescriptions }} Paid
                                            </span>
                                            @if($unpaidPrescriptions > 0)
                                                <span class="badge badge-warning">
                                                    <i class="bi bi-clock"></i> {{ $unpaidPrescriptions }} Pending
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No prescriptions</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <h5>No patient visits found</h5>
                                        <p>Try adjusting your search criteria</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($visits->hasPages())
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info">
                                Showing {{ $visits->firstItem() }} to {{ $visits->lastItem() }} of {{ $visits->total() }} entries
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate">
                                {{ $visits->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
</div>

<!-- Investigations Modal -->
<div class="modal fade" id="investigationsModal" tabindex="-1" aria-labelledby="investigationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investigationsModalLabel">
                    <i class="bi bi-clipboard-data"></i>
                    Patient Investigations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="investigationsModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Prescriptions Modal -->
<div class="modal fade" id="prescriptionsModal" tabindex="-1" aria-labelledby="prescriptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prescriptionsModalLabel">
                    <i class="bi bi-capsule"></i>
                    Patient Prescriptions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prescriptionsModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function viewInvestigations(visitId) {
    $('#investigationsModal').modal('show');
    $('#investigationsModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    fetch(`/cashier/visits/${visitId}/investigations`)
        .then(response => response.text())
        .then(html => {
            $('#investigationsModalBody').html(html);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#investigationsModalBody').html('<div class="alert alert-danger">Error loading investigations</div>');
        });
}

function viewPrescriptions(visitId) {
    $('#prescriptionsModal').modal('show');
    $('#prescriptionsModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    fetch(`/cashier/visits/${visitId}/prescriptions`)
        .then(response => response.text())
        .then(html => {
            $('#prescriptionsModalBody').html(html);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#prescriptionsModalBody').html('<div class="alert alert-danger">Error loading prescriptions</div>');
        });
}
</script>
@endsection

@section('styles')
<style>
.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}
.badge-info {
    background-color: #17a2b8 !important;
    color: white !important;
}
.badge-success {
    background-color: #28a745 !important;
    color: white !important;
}
.table td {
    vertical-align: middle;
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.small .badge {
    font-size: 0.65em;
    margin: 1px;
    padding: 0.25em 0.4em;
}
.small .badge i {
    font-size: 0.8em;
    margin-right: 2px;
}
</style>
@endsection
