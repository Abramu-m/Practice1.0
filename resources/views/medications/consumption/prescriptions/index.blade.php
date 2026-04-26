@extends('layouts.app_main_layout')

@section('page_title', 'Prescription Consumption Tracking')

@push('styles')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<style>
    .stats-card { 
        transition: transform 0.2s;
    }
    .stats-card:hover { 
        transform: translateY(-2px);
    }
    .filter-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .prescription-status {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-partially_dispensed { background-color: #d1ecf1; color: #0c5460; }
    .status-dispensed { background-color: #d4edda; color: #155724; }
    .status-cancelled { background-color: #f8d7da; color: #721c24; }
    .medication-badge {
        background-color: #e9ecef;
        color: #495057;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        margin: 0.125rem;
        display: inline-block;
    }
</style>
@endpush

@section('main_content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Prescription Consumption Tracking</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('medications.consumption.index') }}">Consumption</a></li>
                        <li class="breadcrumb-item active">Prescriptions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info stats-card">
                        <div class="inner">
                            <h3>{{ number_format($statistics['total_prescriptions']) }}</h3>
                            <p>Total Prescriptions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning stats-card">
                        <div class="inner">
                            <h3>{{ number_format($statistics['pending_prescriptions']) }}</h3>
                            <p>Pending</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success stats-card">
                        <div class="inner">
                            <h3>{{ number_format($statistics['dispensed_prescriptions']) }}</h3>
                            <p>Dispensed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary stats-card">
                        <div class="inner">
                            <h3>{{ number_format($statistics['monthly_prescriptions']) }}</h3>
                            <p>This Month</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-month"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card filter-card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter me-2"></i>
                        Filters
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.consumption.prescriptions.index') }}" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ $search }}" placeholder="Prescription #, patient, medication...">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="all">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="patient" class="form-label">Patient</label>
                            <select class="form-control" id="patient" name="patient">
                                <option value="all">All Patients</option>
                                @foreach($recentPatients as $recentPatient)
                                    <option value="{{ $recentPatient->id }}" {{ $patient == $recentPatient->id ? 'selected' : '' }}>
                                        {{ $recentPatient->first_name }} {{ $recentPatient->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="doctor" class="form-label">Doctor</label>
                            <select class="form-control" id="doctor" name="doctor">
                                <option value="all">All Doctors</option>
                                @foreach($recentDoctors as $recentDoctor)
                                    <option value="{{ $recentDoctor->id }}" {{ $doctor == $recentDoctor->id ? 'selected' : '' }}>
                                        Dr. {{ $recentDoctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="date_range" class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="date_range" name="date_range" readonly>
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-1 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Prescriptions List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Prescriptions</h3>
                            <div class="card-tools">
                                <span class="badge bg-secondary">{{ $prescriptions->total() }} total</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($prescriptions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Prescription #</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Medications</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($prescriptions as $prescription)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $prescription->prescription_number ?? '#' . $prescription->id }}</strong>
                                                    </td>
                                                    <td>
                                                        @if($prescription->patient)
                                                            <div>{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</div>
                                                            <small class="text-muted">MR: {{ $prescription->patient->mr_number }}</small>
                                                        @else
                                                            <span class="text-muted">Unknown Patient</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($prescription->consultation && $prescription->consultation->doctor)
                                                            Dr. {{ $prescription->consultation->doctor->name }}
                                                        @else
                                                            <span class="text-muted">Unknown Doctor</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($prescription->prescriptionItems->count() > 0)
                                                            @foreach($prescription->prescriptionItems->take(3) as $item)
                                                                <span class="medication-badge">
                                                                    {{ $item->medication->name ?? 'Unknown' }}
                                                                    @if($item->quantity_dispensed > 0)
                                                                        ({{ $item->quantity_dispensed }}/{{ $item->quantity }})
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                            @if($prescription->prescriptionItems->count() > 3)
                                                                <span class="badge bg-light">
                                                                    +{{ $prescription->prescriptionItems->count() - 3 }} more
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No medications</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="prescription-status status-{{ $prescription->status }}">
                                                            {{ ucfirst(str_replace('_', ' ', $prescription->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div>{{ $prescription->created_at->format('M j, Y') }}</div>
                                                        <small class="text-muted">{{ $prescription->created_at->format('g:i A') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('medications.consumption.prescription.show', $prescription->id) }}" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($prescription->status === 'pending' || $prescription->status === 'partially_dispensed')
                                                                <a href="{{ route('medications.consumption.prescription.dispense', $prescription->id) }}" 
                                                                   class="btn btn-outline-success">
                                                                    <i class="fas fa-prescription-bottle"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    {{ $prescriptions->appends(request()->query())->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-prescription-bottle-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No prescriptions found</h5>
                                    <p class="text-muted">Try adjusting your filters or search criteria.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Quick Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Stats</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-warning">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                        <h5 class="description-header">{{ $statistics['partially_dispensed'] }}</h5>
                                        <span class="description-text">Partially Dispensed</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <span class="description-percentage text-success">
                                            <i class="fas fa-pills"></i>
                                        </span>
                                        <h5 class="description-header">{{ number_format($statistics['total_items_dispensed']) }}</h5>
                                        <span class="description-text">Items Dispensed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Prescribed Medications -->
                    @if(count($statistics['top_prescribed_medications']) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Prescribed Medications</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($statistics['top_prescribed_medications']->take(5) as $topMed)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $topMed->medication->name ?? 'Unknown' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $topMed->prescription_count }} prescriptions</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">{{ number_format($topMed->total_dispensed) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#date_range').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        $('input[name="date_from"]').val(picker.startDate.format('YYYY-MM-DD'));
        $('input[name="date_to"]').val(picker.endDate.format('YYYY-MM-DD'));
    });

    $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('input[name="date_from"]').val('');
        $('input[name="date_to"]').val('');
    });

    // Set initial date range if values exist
    @if($dateFrom && $dateTo)
    $('#date_range').val('{{ date("m/d/Y", strtotime($dateFrom)) }} - {{ date("m/d/Y", strtotime($dateTo)) }}');
    @endif

    // Auto-submit form when filters change
    $('#status, #patient, #doctor').on('change', function() {
        $(this).closest('form').submit();
    });

    // Search with enter key
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush
