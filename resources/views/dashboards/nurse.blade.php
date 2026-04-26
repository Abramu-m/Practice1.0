@extends('layouts.app_main_layout')

@section('page_title', 'Nurse Dashboard')

@section('main_content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="fas fa-user-nurse me-2"></i>
                                Welcome, Nurse {{ auth()->user()->name }}
                            </h2>
                            <p class="mb-0 opacity-75">Nursing Care Dashboard - {{ date('l, F j, Y') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="small">
                                <i class="fas fa-heartbeat me-1"></i>
                                Ward Status: Active
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                Triage Patients
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $triagePatients ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart-pulse fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('vitals') }}" class="small text-danger">
                        Manage Triage <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Vitals Recorded
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $vitalsRecorded ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-thermometer-half fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('vitals') }}" class="small text-primary">
                        View Records <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Ward Supplies Low
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $lowSupplies ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('store-locations-stock.index') }}" class="small text-warning">
                        Check Stock <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                CTC Patients
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $ctcPatients ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-virus fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('pitc') }}" class="small text-info">
                        CTC Services <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Triage & Care Center -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Triage Center
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-4 text-danger mb-2">{{ $priorityPatients['critical'] ?? 0 }}</div>
                                <h6 class="text-danger">Critical</h6>
                                <small class="text-muted">Immediate attention required</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-4 text-warning mb-2">{{ $priorityPatients['urgent'] ?? 0 }}</div>
                                <h6 class="text-warning">Urgent</h6>
                                <small class="text-muted">Within 30 minutes</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-4 text-success mb-2">{{ $priorityPatients['stable'] ?? 0 }}</div>
                                <h6 class="text-success">Stable</h6>
                                <small class="text-muted">Routine care</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="{{ url('vitals') }}" class="btn btn-danger me-2">
                            <i class="fas fa-plus-circle me-1"></i>
                            Record Vitals
                        </a>
                        <a href="{{ route('procedures.index') }}" class="btn btn-success">
                            <i class="fas fa-clipboard-check me-1"></i>
                            Nursing Procedures
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ url('vitals') }}" class="btn btn-outline-danger w-100 mb-2">
                            <i class="fas fa-heartbeat me-1"></i>
                            Vital Signs
                        </a>
                        <a href="{{ url('pitc') }}" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-shield-check me-1"></i>
                            PITC Screening
                        </a>
                        <a href="{{ url('ctc_drug_issue') }}" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-pills me-1"></i>
                            CTC Dispensing
                        </a>
                        <a href="{{ route('store.requisitions.index') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-clipboard-list me-1"></i>
                            Ward Supplies
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ward Management & CTC Services -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-hospital me-2"></i>
                        Ward Management
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-3">Today's Ward Activities</h6>
                    
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($vitalsCompleted ?? 0) }}%" 
                             aria-valuenow="{{ $vitalsCompleted ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                            Vitals: {{ $vitalsCompleted ?? 0 }}%
                        </div>
                    </div>
                    
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($medicationRounds ?? 0) }}%" 
                             aria-valuenow="{{ $medicationRounds ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                            Medication Rounds: {{ $medicationRounds ?? 0 }}%
                        </div>
                    </div>
                    
                    <div class="progress mb-3">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($nursingCare ?? 0) }}%" 
                             aria-valuenow="{{ $nursingCare ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                            Nursing Care: {{ $nursingCare ?? 0 }}%
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-muted">Supplies Status:</small>
                            <div class="text-{{ ($suppliesStatus ?? 'success') == 'low' ? 'warning' : 'success' }} fw-bold">
                                {{ ucfirst($suppliesStatus ?? 'Good') }}
                            </div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Next Rounds:</small>
                            <div class="fw-bold">{{ $nextRounds ?? '14:00' }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('store-locations-stock.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-box me-1"></i>Ward Stock
                    </a>
                    <a href="{{ route('store.requisitions.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-clipboard-list me-1"></i>Requisitions
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-virus me-2"></i>
                        CTC Services Overview
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="row mb-3">
                        <div class="col-4 text-center">
                            <div class="h4 text-info">{{ $ctcStats['screenings'] ?? 0 }}</div>
                            <small>PITC Screenings</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="h4 text-success">{{ $ctcStats['dispensed'] ?? 0 }}</div>
                            <small>Drugs Dispensed</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="h4 text-warning">{{ $ctcStats['cd4_results'] ?? 0 }}</div>
                            <small>CD4 Results</small>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted">Recent CTC Activities</h6>
                    @if(isset($recentCTCActivities) && count($recentCTCActivities) > 0)
                        @foreach($recentCTCActivities as $activity)
                        <div class="d-flex align-items-center mb-2 p-2 border rounded">
                            <div class="me-3">
                                <i class="fas fa-{{ $activity['icon'] ?? 'circle' }} text-{{ $activity['color'] ?? 'primary' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $activity['patient'] ?? 'Patient Activity' }}</div>
                                <small class="text-muted">{{ $activity['description'] ?? 'CTC service provided' }}</small>
                            </div>
                            <small class="text-muted">{{ $activity['time'] ?? 'Now' }}</small>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>No recent CTC activities</p>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ url('pitc') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-shield-check me-1"></i>PITC
                    </a>
                    <a href="{{ url('ctc_drug_issue') }}" class="btn btn-sm btn-outline-success ms-2">
                        <i class="fas fa-pills me-1"></i>Dispensing
                    </a>
                    <a href="{{ url('cd4_form_result') }}" class="btn btn-sm btn-outline-warning ms-2">
                        <i class="fas fa-file-medical me-1"></i>CD4
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: .25rem solid #007bff!important; }
.border-left-warning { border-left: .25rem solid #ffc107!important; }
.border-left-success { border-left: .25rem solid #28a745!important; }
.border-left-info { border-left: .25rem solid #17a2b8!important; }
.border-left-danger { border-left: .25rem solid #dc3545!important; }
.opacity-75 { opacity: 0.75; }
.d-grid { display: grid; }
.gap-2 { gap: 0.5rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh triage counts every 2 minutes
    setInterval(function() {
        // This would fetch updated triage data
        console.log('Refreshing triage data...');
    }, 120000);

    // Pulse effect for critical patients
    const criticalCount = document.querySelector('.text-danger .display-4');
    if (criticalCount && parseInt(criticalCount.textContent) > 0) {
        criticalCount.classList.add('animate__animated', 'animate__pulse', 'animate__infinite');
    }
});
</script>
@endsection
