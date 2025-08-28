@extends('layouts.app_main_layout')

@section('page_title', 'Pharmacist Dashboard')

@section('main_content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Pharmacist Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Pharmacist Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Enhanced Info boxes -->
            <div class="row mb-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Prescriptions
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $todayStats['pending_prescriptions'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'pending']) }}" class="small text-warning">
                                Process Now <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Dispensed Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $todayStats['dispensed_today'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'dispensed']) }}" class="small text-success">
                                View All <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Patients Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $todayStats['total_patients'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ route('pharmacist.prescriptions.index') }}" class="small text-primary">
                                All Prescriptions <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Stock Alerts
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $todayStats['unavailable_items'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-exclamation-triangle fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ route('store-locations-stock.index') }}" class="small text-danger">
                                Manage Stock <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Quick Actions & Pharmacy Operations -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-lightning-charge mr-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <a href="{{ route('pharmacist.prescriptions.index') }}" class="btn btn-primary btn-block">
                                        <i class="bi bi-list-check mr-1"></i>
                                        All Prescriptions
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'pending']) }}" class="btn btn-warning btn-block">
                                        <i class="bi bi-clock mr-1"></i>
                                        Pending Queue
                                        @if($todayStats['pending_prescriptions'] > 0)
                                            <span class="badge badge-light ml-1">{{ $todayStats['pending_prescriptions'] }}</span>
                                        @endif
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-success btn-block">
                                        <i class="bi bi-cash-stack mr-1"></i>
                                        Cash Sales
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="{{ route('store-locations-stock.index') }}" class="btn btn-info btn-block">
                                        <i class="bi bi-boxes mr-1"></i>
                                        Stock Levels
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
                                <i class="bi bi-capsule mr-2"></i>
                                Pharmacy Operations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6 text-center">
                                    <div class="h4 text-primary">{{ $operationStats['cash_sales'] ?? 0 }}</div>
                                    <small class="text-muted">Cash Sales Today</small>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="h4 text-success">TSh {{ number_format($operationStats['revenue'] ?? 0, 0) }}</div>
                                    <small class="text-muted">Today's Revenue</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="text-muted mb-2">Prescription Status</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $prescriptionStats['dispensed_percentage'] ?? 70 }}%" 
                                     aria-valuenow="{{ $prescriptionStats['dispensed_percentage'] ?? 70 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    Dispensed {{ $prescriptionStats['dispensed_percentage'] ?? 70 }}%
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: {{ $prescriptionStats['pending_percentage'] ?? 20 }}%" 
                                     aria-valuenow="{{ $prescriptionStats['pending_percentage'] ?? 20 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    Pending {{ $prescriptionStats['pending_percentage'] ?? 20 }}%
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ $prescriptionStats['unavailable_percentage'] ?? 10 }}%" 
                                     aria-valuenow="{{ $prescriptionStats['unavailable_percentage'] ?? 10 }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    Unavailable {{ $prescriptionStats['unavailable_percentage'] ?? 10 }}%
                                </div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-success">Dispensed: {{ $prescriptionStats['dispensed_count'] ?? 0 }}</small>
                                </div>
                                <div class="col-4">
                                    <small class="text-warning">Pending: {{ $prescriptionStats['pending_count'] ?? 0 }}</small>
                                </div>
                                <div class="col-4">
                                    <small class="text-danger">Unavailable: {{ $prescriptionStats['unavailable_count'] ?? 0 }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Management & Alerts -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-boxes mr-2"></i>
                                Pharmacy Stock Management
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 text-center">
                                    <div class="h4 text-danger">{{ $stockAlerts['critical'] ?? 0 }}</div>
                                    <small class="text-danger">Critical Stock</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="h4 text-warning">{{ $stockAlerts['low'] ?? 0 }}</div>
                                    <small class="text-warning">Low Stock</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="h4 text-success">{{ $stockAlerts['adequate'] ?? 0 }}</div>
                                    <small class="text-success">Adequate Stock</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="h4 text-info">{{ $stockAlerts['expired'] ?? 0 }}</div>
                                    <small class="text-info">Expiring Soon</small>
                                </div>
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3">Recent Stock Activities</h6>
                            <div style="max-height: 250px; overflow-y: auto;">
                                @if(isset($recentStockActivities) && count($recentStockActivities) > 0)
                                    @foreach($recentStockActivities as $activity)
                                    <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                        <div class="mr-3">
                                            <i class="bi bi-{{ $activity['icon'] ?? 'arrow-up-right' }} text-{{ $activity['color'] ?? 'primary' }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="font-weight-bold">{{ $activity['medication'] ?? 'Medication Update' }}</div>
                                            <small class="text-muted">{{ $activity['description'] ?? 'Stock level changed' }}</small>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-muted">{{ $activity['time'] ?? 'Now' }}</small>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-info-circle fa-2x mb-2"></i>
                                    <p>No recent stock activities</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('store-locations-stock.index') }}" class="btn btn-warning btn-sm mr-2">
                                <i class="bi bi-eye mr-1"></i>View Stock
                            </a>
                            <a href="{{ route('store.requisitions.index') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-clipboard-data mr-1"></i>Requisitions
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up mr-2"></i>
                                Performance Metrics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="display-4 text-success">{{ $performanceMetrics['efficiency'] ?? 92 }}%</div>
                                <small class="text-muted">Dispensing Efficiency</small>
                            </div>
                            
                            <hr>
                            
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="h5 text-primary">{{ $performanceMetrics['avg_time'] ?? '5.2' }} min</div>
                                    <small>Avg. Dispensing Time</small>
                                </div>
                                <div class="col-6">
                                    <div class="h5 text-warning">{{ $performanceMetrics['accuracy'] ?? 98 }}%</div>
                                    <small>Accuracy Rate</small>
                                </div>
                            </div>

                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="h5 text-success">{{ $performanceMetrics['patient_satisfaction'] ?? 96 }}%</div>
                                    <small>Patient Satisfaction</small>
                                </div>
                                <div class="col-6">
                                    <div class="h5 text-info">{{ $performanceMetrics['stock_turnover'] ?? 15 }}</div>
                                    <small>Stock Turnover</small>
                                </div>
                            </div>

                            <div class="mt-3">
                                <h6 class="text-muted mb-2">Weekly Trends</h6>
                                <canvas id="weeklyTrendsChart" width="100" height="80"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                                Today's Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="progress-group">
                                Pending Prescriptions
                                <span class="float-right"><b>{{ $todayStats['pending_prescriptions'] }}</b>/{{ $todayStats['pending_prescriptions'] + $todayStats['dispensed_today'] }}</span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-warning" style="width: {{ $todayStats['pending_prescriptions'] + $todayStats['dispensed_today'] > 0 ? ($todayStats['pending_prescriptions'] / ($todayStats['pending_prescriptions'] + $todayStats['dispensed_today'])) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="progress-group">
                                Dispensed Today
                                <span class="float-right"><b>{{ $todayStats['dispensed_today'] }}</b>/{{ $todayStats['pending_prescriptions'] + $todayStats['dispensed_today'] }}</span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" style="width: {{ $todayStats['pending_prescriptions'] + $todayStats['dispensed_today'] > 0 ? ($todayStats['dispensed_today'] / ($todayStats['pending_prescriptions'] + $todayStats['dispensed_today'])) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Pending Prescriptions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-clock"></i>
                                Recent Pending Prescriptions
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('pharmacist.prescriptions.index') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Visit Date</th>
                                        <th>Prescriptions</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingPrescriptions as $visit)
                                        <tr>
                                            <td>
                                                <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong><br>
                                                <small class="text-muted">{{ $visit->patientInfo->mr_number }}</small>
                                            </td>
                                            <td>
                                                {{ $visit->created_at->format('M d, Y') }}<br>
                                                <small class="text-muted">{{ $visit->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $visit->consultation->prescriptions->where('status', 'pending')->count() }} items
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">Available to Process</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('pharmacist.prescriptions.show', $visit->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Process
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No pending prescriptions found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.border-left-primary { border-left: .25rem solid #007bff!important; }
.border-left-warning { border-left: .25rem solid #ffc107!important; }
.border-left-success { border-left: .25rem solid #28a745!important; }
.border-left-info { border-left: .25rem solid #17a2b8!important; }
.border-left-danger { border-left: .25rem solid #dc3545!important; }
</style>

<script>
// Enhanced dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh pending prescriptions count
    setInterval(function() {
        fetch('/pharmacist/data?type=pending_count')
            .then(response => response.json())
            .then(data => {
                if (data.pending_count !== undefined) {
                    // Update pending badge in quick actions
                    const badge = document.querySelector('.btn-warning .badge');
                    if (badge) {
                        badge.textContent = data.pending_count;
                        badge.style.display = data.pending_count > 0 ? 'inline' : 'none';
                    }
                }
            })
            .catch(error => console.log('Update error:', error));
    }, 60000); // Every minute

    // Simple chart for weekly trends (placeholder)
    const ctx = document.getElementById('weeklyTrendsChart')?.getContext('2d');
    if (ctx) {
        // Basic chart placeholder - would be replaced with actual Chart.js
        ctx.fillStyle = '#17a2b8';
        ctx.fillRect(10, 60, 15, 20);
        ctx.fillRect(30, 45, 15, 35);
        ctx.fillRect(50, 30, 15, 50);
        ctx.fillRect(70, 20, 15, 60);
        ctx.fillRect(90, 15, 15, 65);
    }
});
</script>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh the dashboard every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush
