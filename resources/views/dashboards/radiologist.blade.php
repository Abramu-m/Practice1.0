@extends('layouts.app_main_layout')

@section('page_title', 'Radiologist Dashboard')

@section('main_content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="fas fa-x-ray me-2"></i>
                                Welcome, Dr. {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </h2>
                            <p class="mb-0 opacity-75">Radiology Dashboard - {{ date('l, F j, Y') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="small">
                                <i class="fas fa-clock me-1"></i>
                                Last updated: {{ now()->format('H:i:s') }}
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
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Today's Orders
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $todaysRadiologyOrders ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('lab.visits.index') }}" class="small text-primary">
                        View Orders <i class="fas fa-arrow-circle-right"></i>
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
                                Pending Reports
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $pendingRadiologyReports ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-medical-alt fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('lab.visits.index') }}" class="small text-warning">
                        Review Reports <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Completed Today
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $completedToday ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('lab.visits.index') }}" class="small text-success">
                        View Completed <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                Urgent Studies
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $urgentStudies ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('lab.visits.index') }}" class="small text-danger">
                        Review Urgent <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Study Categories & Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Today's Study Categories
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($studyCategories) && array_sum($studyCategories) > 0)
                        <div class="row">
                            @foreach($studyCategories as $category => $count)
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">{{ $category }}:</span>
                                    <span class="badge bg-primary">{{ $count }}</span>
                                </div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ array_sum($studyCategories) > 0 ? ($count / array_sum($studyCategories)) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-x-ray fa-3x mb-3"></i>
                        <p>No studies scheduled for today</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('lab.visits.index') }}"
                               class="btn btn-outline-warning w-100">
                                <i class="fas fa-clipboard-list me-1"></i>
                                Pending Reports
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('lab.visits.index') }}"
                               class="btn btn-outline-danger w-100">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Urgent Studies
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('lab.visits.index') }}"
                               class="btn btn-outline-info w-100">
                                <i class="fas fa-search me-1"></i>
                                Search Studies
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('store.requisitions.index') }}" 
                               class="btn btn-outline-success w-100">
                                <i class="fas fa-boxes me-1"></i>
                                Contrast & Supplies
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Queue & Equipment Status -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ul me-2"></i>
                        Today's Work Queue
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($todaysQueue) && count($todaysQueue) > 0)
                        @foreach($todaysQueue as $study)
                        <div class="d-flex align-items-center mb-3 p-3 border rounded {{ $study->priority == 'urgent' ? 'border-danger bg-light' : '' }}">
                            <div class="me-3">
                                <div class="text-primary fw-bold">
                                    {{ $study->created_at->format('H:i') }}
                                </div>
                                @if($study->priority == 'urgent')
                                    <span class="badge bg-danger">URGENT</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $study->patient->first_name ?? 'Unknown' }} {{ $study->patient->last_name ?? 'Patient' }}
                                </div>
                                <small class="text-muted">
                                    {{ $study->medicalService->name ?? 'Radiology Study' }}
                                </small>
                                <br>
                                <small class="text-info">
                                    Ordered by: Dr. {{ $study->doctor->first_name ?? 'Unknown' }} {{ $study->doctor->last_name ?? 'Doctor' }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('lab.results.form', $study->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                        <p>No studies in queue for today</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-3">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Equipment Status
                    </h6>
                </div>
                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                    @if(isset($equipmentStatus))
                        @foreach($equipmentStatus as $equipment => $status)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <small class="fw-bold">{{ $equipment }}</small>
                                <br>
                                <small class="text-muted">Last: {{ $status['last_maintenance'] }}</small>
                            </div>
                            <div>
                                <span class="badge bg-{{ $status['status'] == 'operational' ? 'success' : ($status['status'] == 'maintenance' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($status['status']) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Alerts & Notifications
                    </h6>
                </div>
                <div class="card-body" style="max-height: 180px; overflow-y: auto;">
                    @if(isset($alerts) && count($alerts) > 0)
                        @foreach($alerts as $alert)
                        <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show p-2 mb-2">
                            <small>
                                <strong>{{ $alert['title'] }}</strong><br>
                                {{ $alert['message'] }}
                            </small>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">No alerts at this time</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Performance Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">This Week's Summary</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Total Studies:</span>
                                <span class="fw-bold">{{ $weeklyStats['total_studies'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Completed Studies:</span>
                                <span class="fw-bold">{{ $weeklyStats['completed_studies'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Avg Turnaround:</span>
                                <span class="fw-bold text-success">{{ $weeklyStats['average_turnaround'] ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Patient Satisfaction:</span>
                                <span class="fw-bold text-success">{{ $weeklyStats['patient_satisfaction'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Quality Metrics</h6>
                            @if(isset($qualityMetrics))
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Repeat Rate:</span>
                                    <span class="fw-bold text-info">{{ $qualityMetrics['repeat_rate'] ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Critical Findings:</span>
                                    <span class="fw-bold text-warning">{{ $qualityMetrics['critical_findings'] ?? 0 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Turnaround Target:</span>
                                    <span class="fw-bold text-success">{{ $qualityMetrics['turnaround_target'] ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>My Workload Today:</span>
                                    <span class="fw-bold">{{ $qualityMetrics['radiologist_workload'] ?? 0 }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Urgent Studies
                    </h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if(isset($urgentStudiesList) && count($urgentStudiesList) > 0)
                        @foreach($urgentStudiesList as $urgent)
                        <div class="d-flex align-items-center mb-3 p-2 border-left-danger border rounded">
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $urgent->patient->first_name ?? 'Unknown' }} {{ $urgent->patient->last_name ?? 'Patient' }}
                                </div>
                                <small class="text-muted">
                                    {{ $urgent->medicalService->name ?? 'Radiology Study' }}
                                </small>
                                <br>
                                <small class="text-danger">
                                    Ordered: {{ $urgent->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('lab.results.form', $urgent->id) }}" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">No urgent studies pending</p>
                    </div>
                    @endif
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update time every minute
    setInterval(function() {
        const now = new Date();
        const timeElement = document.querySelector('.card-body .small');
        if (timeElement) {
            timeElement.innerHTML = 
                '<i class="fas fa-clock me-1"></i>Last updated: ' + now.toLocaleTimeString();
        }
    }, 60000);

    // Auto-refresh urgent studies every 5 minutes
    setInterval(function() {
        // This would trigger an AJAX call to refresh urgent studies
        console.log('Auto-refreshing urgent studies...');
    }, 300000);
});
</script>
@endsection
