@extends('layouts.app_main_layout')

@section('page_title', 'Doctor Dashboard')

@section('main_content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="fas fa-user-md me-2"></i>
                                Welcome, Dr. {{ auth()->user()->name }}
                            </h2>
                            <p class="mb-0 opacity-75">Clinical Dashboard - {{ date('l, F j, Y') }}</p>
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
                                Total Consultations
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $totalConsultations ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                Active: {{ $activeConsultations ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('patient_visits.index') }}" class="small text-primary">
                        View All <i class="fas fa-arrow-circle-right"></i>
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
                                Total Procedures
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $totalProcedures ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                Active: {{ $activeProcedures ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('procedures.index') }}" class="small text-warning">
                        Review <i class="fas fa-arrow-circle-right"></i>
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
                                Posted Claims
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $postedClaims ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-medical fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ url('PostClaim') }}" class="small text-info">
                        View Claims <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="row mb-4">
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
                            <a href="{{ route('patient_visits.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-plus me-1"></i>
                                New Consultation
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('procedures.index') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-clipboard-check me-1"></i>
                                Medical Procedures
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('procedures.index', ['filter_type' => 'radiology']) }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-x-ray me-1"></i>
                                Radiology Orders
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ url('PostClaim') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-file-invoice me-1"></i>
                                Review Claims
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Today's Schedule
                    </h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if(isset($todaysAppointments) && count($todaysAppointments) > 0)
                        @foreach($todaysAppointments as $appointment)
                        <div class="d-flex align-items-center mb-3 p-2 border rounded">
                            <div class="me-3">
                                <div class="text-primary fw-bold">{{ $appointment->time ?? '09:00' }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $appointment->patient_name ?? 'Patient Visit' }}</div>
                                <small class="text-muted">{{ $appointment->visit_type ?? 'General Consultation' }}</small>
                            </div>
                            <div>
                                <span class="badge badge-{{ $appointment->status == 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($appointment->status ?? 'pending') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>No appointments scheduled for today</p>
                        <a href="{{ route('patient_visits.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Schedule Visit
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Clinical Insights -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Clinical Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="consultationsChart" width="400" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">This Week's Summary</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Total Consultations:</span>
                                <span class="fw-bold">{{ $weeklyStats['consultations'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Procedures Completed:</span>
                                <span class="fw-bold">{{ $weeklyStats['procedures'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Claims Submitted:</span>
                                <span class="fw-bold">{{ $weeklyStats['claims'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Patient Satisfaction:</span>
                                <span class="fw-bold text-success">{{ $weeklyStats['satisfaction'] ?? '95%' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Alerts & Notifications
                    </h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
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
</div>

<style>
.border-left-primary { border-left: .25rem solid #007bff!important; }
.border-left-warning { border-left: .25rem solid #ffc107!important; }
.border-left-success { border-left: .25rem solid #28a745!important; }
.border-left-info { border-left: .25rem solid #17a2b8!important; }
.opacity-75 { opacity: 0.75; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update time every minute
    setInterval(function() {
        const now = new Date();
        document.querySelector('.card-body .small').innerHTML = 
            '<i class="fas fa-clock me-1"></i>Last updated: ' + now.toLocaleTimeString();
    }, 60000);

    // Simple chart for consultations (placeholder)
    const ctx = document.getElementById('consultationsChart')?.getContext('2d');
    if (ctx) {
        // This would be replaced with actual Chart.js implementation
        ctx.fillStyle = '#007bff';
        ctx.fillRect(50, 50, 100, 100);
        ctx.fillStyle = '#fff';
        ctx.font = '14px Arial';
        ctx.fillText('Chart Placeholder', 60, 105);
    }
});
</script>
@endsection
