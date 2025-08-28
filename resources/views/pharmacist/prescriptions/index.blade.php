@extends('layouts.app_main_layout')

@section('page_title', 'Prescription Management')

@section('main_content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Prescription Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pharmacist.dashboard') }}">Pharmacist</a></li>
                        <li class="breadcrumb-item active">Prescriptions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-list-check"></i>
                                Patient Visits with Prescriptions
                            </h3>
                        </div>
                        
                        <!-- Filters -->
                        <div class="card-body">
                            <form method="GET" action="{{ route('pharmacist.prescriptions.index') }}" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="">All Statuses</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="dispensed" {{ request('status') == 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                                                <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="search">Patient Search</label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                   placeholder="Name or MR Number" value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="date">Date</label>
                                            <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-search"></i> Filter
                                                </button>
                                                <a href="{{ route('pharmacist.prescriptions.index') }}" class="btn btn-secondary">
                                                    <i class="bi bi-x-circle"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Quick Filter Buttons -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('pharmacist.prescriptions.index') }}" 
                                           class="btn {{ !request()->has('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                                            All Visits
                                        </a>
                                        <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'pending']) }}" 
                                           class="btn {{ request('status') == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                                            Pending Prescriptions
                                        </a>
                                        <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'dispensed']) }}" 
                                           class="btn {{ request('status') == 'dispensed' ? 'btn-success' : 'btn-outline-success' }}">
                                            Dispensed
                                        </a>
                                        <a href="{{ route('pharmacist.prescriptions.index', ['status' => 'unavailable']) }}" 
                                           class="btn {{ request('status') == 'unavailable' ? 'btn-danger' : 'btn-outline-danger' }}">
                                            Unavailable
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prescriptions Table -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Patient Information</th>
                                        <th>Visit Details</th>
                                        <th>Prescriptions</th>
                                        <th>Payment Status</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prescriptions as $visit)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">MR: {{ $visit->patientInfo->mr_number }}</small>
                                                    <br>
                                                    <small class="text-muted">Age: {{ $visit->patientInfo->age ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $visit->created_at->format('M d, Y') }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $visit->created_at->format('h:i A') }}</small>
                                                    @if($visit->consultation && $visit->consultation->doctor)
                                                        <br>
                                                        <small class="text-muted">Dr. {{ $visit->consultation->doctor->name ?? 'N/A' }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($visit->consultation && $visit->consultation->prescriptions->count() > 0)
                                                    <div>
                                        @php
                                            $prescriptions = $visit->consultation->prescriptions;
                                            $pendingCount = $prescriptions->filter(function($p) { 
                                                return in_array($p->status, ['prescribed', 'prepared']); 
                                            })->count();
                                            $dispensedCount = $prescriptions->where('status', 'dispensed')->count();
                                            $unavailableCount = $prescriptions->where('status', 'cancelled')->count();
                                        @endphp                                                        <span class="badge badge-secondary">{{ $prescriptions->count() }} Total</span>
                                                        
                                                        @if($pendingCount > 0)
                                                            <span class="badge badge-warning">{{ $pendingCount }} Pending</span>
                                                        @endif
                                                        
                                                        @if($dispensedCount > 0)
                                                            <span class="badge badge-success">{{ $dispensedCount }} Dispensed</span>
                                                        @endif
                                                        
                                                        @if($unavailableCount > 0)
                                                            <span class="badge badge-danger">{{ $unavailableCount }} Unavailable</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">No prescriptions</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <i class="bi bi-info-circle"></i> Ready to Process
                                                </span>
                                            </td>
                                            <td>
                                                @if($visit->consultation && $visit->consultation->prescriptions->count() > 0)
                                    @php
                                        $allPrescriptions = $visit->consultation->prescriptions;
                                        $hasPending = $allPrescriptions->filter(function($p) { 
                                            return in_array($p->status, ['prescribed', 'prepared']); 
                                        })->count() > 0;
                                        $allDispensed = $allPrescriptions->every(function($p) { return $p->status === 'dispensed'; });
                                        $hasUnavailable = $allPrescriptions->where('status', 'cancelled')->count() > 0;
                                    @endphp                                                    @if($hasPending)
                                                        <span class="badge badge-warning">Action Required</span>
                                                    @elseif($allDispensed)
                                                        <span class="badge badge-success">Completed</span>
                                                    @elseif($hasUnavailable)
                                                        <span class="badge badge-danger">Issues</span>
                                                    @else
                                                        <span class="badge badge-info">Processing</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary">No Prescriptions</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($visit->consultation && $visit->consultation->prescriptions->count() > 0)
                                                    <a href="{{ route('pharmacist.prescriptions.show', $visit->id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> View Details
                                                    </a>
                                                @else
                                                    <span class="text-muted">No actions</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox display-4"></i>
                                                    <p class="mt-2">No prescriptions found with the current filters.</p>
                                                    <a href="{{ route('pharmacist.prescriptions.index') }}" class="btn btn-outline-primary">
                                                        Clear Filters
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(method_exists($prescriptions, 'hasPages') && $prescriptions->hasPages())
                            <div class="card-footer clearfix">
                                {{ $prescriptions->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 2 minutes for pending prescriptions
    @if(request('status') == 'pending' || !request()->has('status'))
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 120000);
    @endif
});
</script>
@endpush
