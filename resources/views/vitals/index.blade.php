@extends('layouts.app_main_layout')

@section('page_title', 'Patient Visits - Vitals Recording')

@section('Content_Description', 'Manage Patient Visits and Record Vital Signs')

 

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Patient Visits - Vitals Recording</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>MR Number</th>
                                    <th>Visit Date</th>
                                    <th>Vitals Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visits as $visit)
                                <tr>
                                    <td>{{ $visit->patientInfo->first_name ?? 'N/A' }} {{ $visit->patientInfo->last_name ?? '' }}</td>
                                    <td>{{ $visit->patientInfo->mr_number ?? 'N/A' }}</td>
                                    <td>{{ $visit->visit_date ? $visit->visit_date->format('Y-m-d H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($visit->vitalSigns && $visit->vitalSigns->count() > 0)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Recorded
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> Not Recorded
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($visit->vitalSigns && $visit->vitalSigns->count() > 0)
                                            <a href="{{ route('vitals.show', $visit->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> View/Edit
                                            </a>
                                        @else
                                            <a href="{{ route('vitals.show',  $visit->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus"></i> Record Vitals
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No patient visits found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($visits, 'links'))
                    <div class="d-flex justify-content-center">
                        {{ $visits->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Visits</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('vitals.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="patient_search" class="form-label">Patient Name/MR Number</label>
                        <input type="text" class="form-control" id="patient_search" name="patient_search" 
                               value="{{ request('patient_search') }}" placeholder="Search by patient name or MR number">
                    </div>
                    <div class="mb-3">
                        <label for="visit_date" class="form-label">Visit Date</label>
                        <input type="date" class="form-control" id="visit_date" name="visit_date" 
                               value="{{ request('visit_date') }}">
                    </div>
                    <div class="mb-3">
                        <label for="vitals_status" class="form-label">Vitals Status</label>
                        <select class="form-select" id="vitals_status" name="vitals_status">
                            <option value="">All</option>
                            <option value="recorded" {{ request('vitals_status') == 'recorded' ? 'selected' : '' }}>Recorded</option>
                            <option value="not_recorded" {{ request('vitals_status') == 'not_recorded' ? 'selected' : '' }}>Not Recorded</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('vitals.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Filter Button -->
<div class="position-fixed bottom-0 end-0 p-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="fas fa-filter"></i> Filter
    </button>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
        });
    </script>
@endsection

@section('extra_footer_content')
@endsection