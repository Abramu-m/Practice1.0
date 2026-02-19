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
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="prescriptionsTable" class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Patient Information</th>
                                            <th>Visit Details</th>
                                            <th>Prescriptions</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#prescriptionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("pharmacist.prescriptions.index") }}',
            data: function(d) {
                d.status = $('#status').val() || '';
                d.search = $('#search').val() || '';
                d.date = $('#date').val() || '';
            }
        },
        columns: [
            { 
                data: 'patient_info', 
                name: 'patientInfo.first_name', 
                orderable: false,
                defaultContent: '<span class="text-muted">N/A</span>'
            },
            { 
                data: 'visit_details', 
                name: 'created_at',
                defaultContent: '<span class="text-muted">N/A</span>'
            },
            { 
                data: 'prescriptions_info', 
                name: 'prescriptions_info', 
                orderable: false, 
                searchable: false,
                defaultContent: '<span class="text-muted">N/A</span>'
            },
            { 
                data: 'status_badge', 
                name: 'status_badge', 
                orderable: false, 
                searchable: false,
                defaultContent: '<span class="text-muted">N/A</span>'
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                defaultContent: '<span class="text-muted">No actions</span>'
            }
        ],
        order: [[1, 'desc']],
        pageLength: 20
    });

    // Filter form submission
        $('.card-body form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        // Quick filter buttons
        $('.btn-group a').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            var params = new URLSearchParams(url.split('?')[1]);
            $('#status').val(params.get('status') || '');
            table.draw();
        });

        // Auto-refresh every 2 minutes for pending prescriptions
        @if(request('status') == 'pending' || !request()->has('status'))
            setInterval(function() {
                if (document.visibilityState === 'visible') {
                    table.ajax.reload(null, false);
                }
            }, 120000);
    @endif
});
</script>
@endsection
