@extends('layouts.app_main_layout')

@section('page_title', 'Lab Technician Dashboard')

@section('main_content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="fas fa-microscope me-2"></i>
                                Welcome, {{ auth()->user()->name }}
                            </h2>
                            <p class="mb-0 opacity-75">Laboratory Dashboard - {{ date('l, F j, Y') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="small">
                                <i class="fas fa-flask me-1"></i>
                                Lab Status: {{ $labStatus ?? 'Active' }}
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
                                Pending Tests
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $pendingTests ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-vial fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('investigations.index') }}?status=pending" class="small text-primary">
                        View Pending <i class="fas fa-arrow-circle-right"></i>
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
                    <a href="{{ route('investigations.index') }}?status=completed&date=today" class="small text-success">
                        View Results <i class="fas fa-arrow-circle-right"></i>
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
                                QC Due
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $qcDue ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('clinical_chemistry_control') }}" class="small text-warning">
                        Quality Control <i class="fas fa-arrow-circle-right"></i>
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
                                Urgent Tests
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $urgentTests ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('investigations.index') }}?priority=urgent" class="small text-danger">
                        Process Urgent <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lab Workstation -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-desktop me-2"></i>
                        Lab Workstation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Current Workload</h6>
                            
                            <!-- Priority Test Queue -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-danger fw-bold">
                                        <i class="fas fa-circle text-danger"></i> Critical/Urgent
                                    </span>
                                    <span class="badge bg-danger">{{ $testQueue['critical'] ?? 0 }}</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-danger" role="progressbar" 
                                         style="width: {{ min(100, ($testQueue['critical'] ?? 0) * 10) }}%" 
                                         aria-valuenow="{{ $testQueue['critical'] ?? 0 }}" 
                                         aria-valuemin="0" aria-valuemax="10">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-warning fw-bold">
                                        <i class="fas fa-circle text-warning"></i> Routine
                                    </span>
                                    <span class="badge bg-warning">{{ $testQueue['routine'] ?? 0 }}</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: {{ min(100, ($testQueue['routine'] ?? 0) * 2) }}%" 
                                         aria-valuenow="{{ $testQueue['routine'] ?? 0 }}" 
                                         aria-valuemin="0" aria-valuemax="50">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-success fw-bold">
                                        <i class="fas fa-circle text-success"></i> Non-Urgent
                                    </span>
                                    <span class="badge bg-success">{{ $testQueue['non_urgent'] ?? 0 }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ min(100, ($testQueue['non_urgent'] ?? 0) * 1) }}%" 
                                         aria-valuenow="{{ $testQueue['non_urgent'] ?? 0 }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('lab.visits.index') }}" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-list-alt me-1"></i>
                                    View Lab Queue
                                </a>
                                <a href="{{ route('investigations.index') }}" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-flask me-1"></i>
                                    Process Investigations
                                </a>
                                <a href="{{ route('procedures.index') }}" class="btn btn-info w-100 mb-2">
                                    <i class="fas fa-clipboard-list me-1"></i>
                                    Lab Procedures
                                </a>
                                <a href="{{ url('lab_diary') }}" class="btn btn-warning w-100">
                                    <i class="fas fa-book me-1"></i>
                                    Lab Diary
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">Equipment Status</h6>
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    <div class="equipment-status {{ $equipmentStatus['hematology'] ?? 'operational' }}">
                                        <i class="fas fa-microscope fa-2x mb-2"></i>
                                        <h6>Hematology</h6>
                                        <small class="status-text">{{ ucfirst($equipmentStatus['hematology'] ?? 'Operational') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="equipment-status {{ $equipmentStatus['chemistry'] ?? 'operational' }}">
                                        <i class="fas fa-vial fa-2x mb-2"></i>
                                        <h6>Chemistry</h6>
                                        <small class="status-text">{{ ucfirst($equipmentStatus['chemistry'] ?? 'Operational') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="equipment-status {{ $equipmentStatus['serology'] ?? 'operational' }}">
                                        <i class="fas fa-syringe fa-2x mb-2"></i>
                                        <h6>Serology</h6>
                                        <small class="status-text">{{ ucfirst($equipmentStatus['serology'] ?? 'Operational') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="equipment-status {{ $equipmentStatus['microbiology'] ?? 'operational' }}">
                                        <i class="fas fa-bacteria fa-2x mb-2"></i>
                                        <h6>Microbiology</h6>
                                        <small class="status-text">{{ ucfirst($equipmentStatus['microbiology'] ?? 'Operational') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Today's Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-4 text-success">{{ $performanceStats['completion_rate'] ?? 85 }}%</div>
                        <small class="text-muted">Test Completion Rate</small>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="h5 text-primary">{{ $performanceStats['total_tests'] ?? 0 }}</div>
                            <small>Total Tests</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-warning">{{ $performanceStats['avg_time'] ?? '0' }} min</div>
                            <small>Avg. Time</small>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-success">{{ $performanceStats['accuracy'] ?? 98 }}%</div>
                            <small>Accuracy Rate</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-info">{{ $performanceStats['efficiency'] ?? 92 }}%</div>
                            <small>Efficiency</small>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Test Categories Today</h6>
                        <div class="mb-2">
                            <small>Hematology</small>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ $testCategories['hematology'] ?? 30 }}%" 
                                     aria-valuenow="{{ $testCategories['hematology'] ?? 30 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small>Chemistry</small>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $testCategories['chemistry'] ?? 40 }}%" 
                                     aria-valuenow="{{ $testCategories['chemistry'] ?? 40 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small>Serology</small>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: {{ $testCategories['serology'] ?? 20 }}%" 
                                     aria-valuenow="{{ $testCategories['serology'] ?? 20 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small>Microbiology</small>
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: {{ $testCategories['microbiology'] ?? 10 }}%" 
                                     aria-valuenow="{{ $testCategories['microbiology'] ?? 10 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Control & Specialized Tests -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-check me-2"></i>
                        Quality Control Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 text-center">
                            <div class="h4 text-success">{{ $qcStats['passed'] ?? 0 }}</div>
                            <small class="text-success">Passed Controls</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h4 text-danger">{{ $qcStats['failed'] ?? 0 }}</div>
                            <small class="text-danger">Failed Controls</small>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-3">QC Schedule</h6>
                    <div class="qc-schedule" style="max-height: 200px; overflow-y: auto;">
                        @if(isset($qcSchedule) && count($qcSchedule) > 0)
                            @foreach($qcSchedule as $qc)
                            <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                <div class="me-3">
                                    <i class="fas fa-{{ $qc['status'] == 'completed' ? 'check-circle text-success' : ($qc['status'] == 'due' ? 'exclamation-triangle text-warning' : 'clock text-primary') }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $qc['test'] ?? 'Quality Control' }}</div>
                                    <small class="text-muted">{{ $qc['description'] ?? 'Routine QC check' }}</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">{{ $qc['due_time'] ?? 'Now' }}</small>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p>All QC checks up to date</p>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ url('clinical_chemistry_control') }}" class="btn btn-warning btn-sm me-2">
                        <i class="fas fa-cog me-1"></i>Run QC
                    </a>
                    <a href="{{ url('lab_diary') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-book me-1"></i>Lab Diary
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-medical me-2"></i>
                        Specialized Tests & Forms
                    </h5>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    <h6 class="text-muted mb-3">TB/Leprosy Programs</h6>
                    
                    <div class="row mb-3">
                        <div class="col-6 text-center">
                            <div class="h4 text-warning">{{ $specializedStats['tb_tests'] ?? 0 }}</div>
                            <small>TB Tests</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h4 text-info">{{ $specializedStats['leprosy_tests'] ?? 0 }}</div>
                            <small>Leprosy Tests</small>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-3">Recent Activities</h6>
                    @if(isset($recentSpecializedTests) && count($recentSpecializedTests) > 0)
                        @foreach($recentSpecializedTests as $test)
                        <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                            <div class="me-3">
                                <i class="fas fa-{{ $test['icon'] ?? 'vial' }} text-{{ $test['color'] ?? 'primary' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $test['patient'] ?? 'Patient Test' }}</div>
                                <small class="text-muted">{{ $test['test_type'] ?? 'Specialized test' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $test['status'] == 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($test['status'] ?? 'pending') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>No recent specialized tests</p>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ url('tb_leprosy_form') }}" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-file-plus me-1"></i>TB Form
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('tb_leprosy_register') }}" class="btn btn-warning btn-sm w-100">
                                <i class="fas fa-book me-1"></i>Register
                            </a>
                        </div>
                    </div>
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

.equipment-status {
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.equipment-status.operational {
    background-color: rgba(40, 167, 69, 0.1);
    border: 2px solid rgba(40, 167, 69, 0.3);
}

.equipment-status.maintenance {
    background-color: rgba(255, 193, 7, 0.1);
    border: 2px solid rgba(255, 193, 7, 0.3);
}

.equipment-status.offline {
    background-color: rgba(220, 53, 69, 0.1);
    border: 2px solid rgba(220, 53, 69, 0.3);
}

.equipment-status.operational .status-text { color: #28a745; }
.equipment-status.maintenance .status-text { color: #ffc107; }
.equipment-status.offline .status-text { color: #dc3545; }

.d-grid { display: grid; }
.gap-2 { gap: 0.5rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh urgent tests count
    setInterval(function() {
        // This would fetch updated counts for urgent tests
        console.log('Checking for urgent tests...');
    }, 60000);

    // Equipment status monitoring
    setInterval(function() {
        // This would check equipment connectivity/status
        console.log('Monitoring equipment status...');
    }, 300000);

    // Add pulse animation to urgent test count if > 0
    const urgentCount = document.querySelector('.border-left-danger .h5');
    if (urgentCount && parseInt(urgentCount.textContent) > 0) {
        urgentCount.parentElement.parentElement.parentElement.parentElement.classList.add('animate__animated', 'animate__pulse');
    }
});

// Equipment status click handlers
document.querySelectorAll('.equipment-status').forEach(function(element) {
    element.addEventListener('click', function() {
        const equipmentName = this.querySelector('h6').textContent;
        // Could open equipment details modal
        console.log('Equipment clicked:', equipmentName);
    });
});
</script>
@endsection
