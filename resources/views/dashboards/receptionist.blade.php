@extends('layouts.app_main_layout')

@section('page_title', 'Receptionist Dashboard')

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
                                <i class="fas fa-user-tie me-2"></i>
                                Welcome, {{ auth()->user()->name }}
                            </h2>
                            <p class="mb-0 opacity-75">Reception & Cashier Dashboard - {{ date('l, F j, Y') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="small">
                                <i class="fas fa-clock me-1"></i>
                                {{ now()->format('H:i:s') }}
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
                                Today's Registrations
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $todaysRegistrations ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('patients') }}" class="small text-primary">
                        Manage Patients <i class="fas fa-arrow-circle-right"></i>
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
                                Today's Revenue
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                TSh {{ number_format($todaysRevenue ?? 0, 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('financial.receipts.daily.summary') }}" class="small text-success">
                        Daily Summary <i class="fas fa-arrow-circle-right"></i>
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
                                Pending Visits
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $pendingVisits ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('patient_visits.index') }}" class="small text-warning">
                        View Visits <i class="fas fa-arrow-circle-right"></i>
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
                                Ready Results
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $readyResults ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-medical fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('readyInvResults') }}" class="small text-info">
                        View Results <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Reception Desk & Cashier Operations -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-desktop me-2"></i>
                        Reception Desk Operations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Quick Registration</h6>
                            <div class="mb-3">
                                <a href="{{ url('patients') }}?action=new" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Register New Patient
                                </a>
                            </div>
                            <div class="mb-3">
                                <a href="{{ route('patient_visits.index') }}?action=new" class="btn btn-success w-100">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Schedule Visit
                                </a>
                            </div>
                            <div class="mb-3">
                                <a href="{{ url('readyInvResults') }}" class="btn btn-info w-100">
                                    <i class="fas fa-file-download me-2"></i>
                                    Collect Results
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Patient Search</h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Patient name or number..." id="patientSearch">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" onclick="searchPatient()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="small text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Quick search by name, phone, or patient number
                            </div>
                            
                            <!-- Recent Activities -->
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <h6 class="card-title text-muted mb-2">Recent Activities</h6>
                                    <div style="max-height: 150px; overflow-y: auto;">
                                        @if(isset($recentActivities) && count($recentActivities) > 0)
                                            @foreach($recentActivities as $activity)
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-{{ $activity['icon'] ?? 'circle' }} text-{{ $activity['color'] ?? 'muted' }} me-2"></i>
                                                <small>{{ $activity['description'] ?? 'Activity recorded' }}</small>
                                                <small class="text-muted ms-auto">{{ $activity['time'] ?? 'Now' }}</small>
                                            </div>
                                            @endforeach
                                        @else
                                        <small class="text-muted">No recent activities</small>
                                        @endif
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
                        <i class="fas fa-cash-register me-2"></i>
                        Cashier Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-4 text-success">TSh {{ number_format($cashierStats['daily_total'] ?? 0, 0) }}</div>
                        <small class="text-muted">Today's Collections</small>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-primary">{{ $cashierStats['transactions'] ?? 0 }}</div>
                            <small>Transactions</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-info">{{ $cashierStats['receipts'] ?? 0 }}</div>
                            <small>Receipts</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ min(100, ($cashierStats['daily_percentage'] ?? 0)) }}%" 
                                 aria-valuenow="{{ $cashierStats['daily_percentage'] ?? 0 }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ $cashierStats['daily_percentage'] ?? 0 }}%
                            </div>
                        </div>
                        <small class="text-muted">Daily target progress</small>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('cashier.index') }}" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-eye me-1"></i>Cashier Desk
                    </a>
                    <a href="{{ route('financial.receipts.daily.summary') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-chart-line me-1"></i>Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Operations -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Today's Schedule
                    </h5>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    @if(isset($todaysSchedule) && count($todaysSchedule) > 0)
                        @foreach($todaysSchedule as $appointment)
                        <div class="d-flex align-items-center mb-3 p-2 border-left border-{{ $appointment['status'] == 'completed' ? 'success' : ($appointment['status'] == 'in_progress' ? 'warning' : 'primary') }} bg-light rounded">
                            <div class="me-3 text-center">
                                <div class="fw-bold text-primary">{{ $appointment['time'] ?? '09:00' }}</div>
                                <small class="text-muted">{{ $appointment['type'] ?? 'Visit' }}</small>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $appointment['patient_name'] ?? 'Walk-in Patient' }}</div>
                                <small class="text-muted d-block">{{ $appointment['service'] ?? 'General consultation' }}</small>
                                <small class="text-muted">{{ $appointment['doctor'] ?? 'Dr. Available' }}</small>
                            </div>
                            <div>
                                <span class="badge bg-{{ $appointment['status'] == 'completed' ? 'success' : ($appointment['status'] == 'in_progress' ? 'warning' : 'primary') }}">
                                    {{ ucfirst($appointment['status'] ?? 'scheduled') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>No appointments scheduled</p>
                        <a href="{{ route('patient_visits.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Schedule Visit
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted">Scheduled</small>
                            <div class="fw-bold text-primary">{{ $scheduleStats['scheduled'] ?? 0 }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">In Progress</small>
                            <div class="fw-bold text-warning">{{ $scheduleStats['in_progress'] ?? 0 }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Completed</small>
                            <div class="fw-bold text-success">{{ $scheduleStats['completed'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Daily Operations Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 text-center">
                            <div class="h4 text-primary">{{ $operationsStats['new_patients'] ?? 0 }}</div>
                            <small class="text-muted">New Patients</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h4 text-success">{{ $operationsStats['return_patients'] ?? 0 }}</div>
                            <small class="text-muted">Return Visits</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6 text-center">
                            <div class="h4 text-warning">{{ $operationsStats['cash_payments'] ?? 0 }}</div>
                            <small class="text-muted">Cash Payments</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h4 text-info">{{ $operationsStats['insurance_claims'] ?? 0 }}</div>
                            <small class="text-muted">Insurance Claims</small>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-2">Payment Methods Today</h6>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $paymentStats['cash_percentage'] ?? 60 }}%" 
                             aria-valuenow="{{ $paymentStats['cash_percentage'] ?? 60 }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            Cash {{ $paymentStats['cash_percentage'] ?? 60 }}%
                        </div>
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: {{ $paymentStats['insurance_percentage'] ?? 30 }}%" 
                             aria-valuenow="{{ $paymentStats['insurance_percentage'] ?? 30 }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            Insurance {{ $paymentStats['insurance_percentage'] ?? 30 }}%
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: {{ $paymentStats['other_percentage'] ?? 10 }}%" 
                             aria-valuenow="{{ $paymentStats['other_percentage'] ?? 10 }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            Other {{ $paymentStats['other_percentage'] ?? 10 }}%
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('financial.transactions.create') }}" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-plus me-1"></i>Cash Book
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('financial.receipts.index') }}" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-receipt me-1"></i>Receipts
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
.opacity-75 { opacity: 0.75; }
</style>

<script>
function searchPatient() {
    const searchTerm = document.getElementById('patientSearch').value;
    if (searchTerm.trim()) {
        // Redirect to patient search or show modal
        window.location.href = `{{ url('patients') }}?search=${encodeURIComponent(searchTerm)}`;
    }
}

// Allow enter key to trigger search
document.getElementById('patientSearch')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchPatient();
    }
});

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    // This would refresh key metrics
    console.log('Refreshing reception dashboard...');
}, 300000);

// Real-time clock update
setInterval(function() {
    const now = new Date();
    const timeElement = document.querySelector('.card-body .small');
    if (timeElement) {
        timeElement.innerHTML = '<i class="fas fa-clock me-1"></i>' + now.toLocaleTimeString();
    }
}, 1000);
</script>
@endsection
