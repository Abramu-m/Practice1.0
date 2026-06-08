@extends('layouts.app_main_layout')

@section('page_title', 'Patient Visits - Vitals Recording')

@section('main_content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search Patient</label>
                    <input type="text" class="form-control form-control-sm" id="patient_search"
                           placeholder="Name or MR number" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Vitals Status</label>
                    <select class="form-select form-select-sm" id="vitals_status"
                            onchange="vitalsTable.draw()">
                        <option value="">All</option>
                        <option value="recorded">Recorded</option>
                        <option value="not_recorded">Not Recorded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="date_from"
                           value="{{ $dateFrom }}" onchange="vitalsTable.draw()">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="date_to"
                           value="{{ $dateTo }}" onchange="vitalsTable.draw()">
                </div>
            </div>
        </div>
    </div>

    <!-- Vitals Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Patient Visits — Vitals Recording</h4>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible m-3 fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <table id="vitalsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>MR Number</th>
                        <th>Visit Date</th>
                        <th>Vitals Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
var vitalsTable;

$(document).ready(function () {
    vitalsTable = $('#vitalsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: '{{ route("vitals.index") }}',
            data: function (d) {
                d.patient_search = $('#patient_search').val();
                d.vitals_status  = $('#vitals_status').val();
                d.date_from      = $('#date_from').val();
                d.date_to        = $('#date_to').val();
            }
        },
        columns: [
            { data: 'patient_name',        name: 'patientInfo.first_name' },
            { data: 'mr_number',           name: 'patientInfo.mr_number' },
            { data: 'visit_date_formatted',name: 'visit_date' },
            { data: 'vitals_status',       name: 'vitals_status',  orderable: false, searchable: false },
            { data: 'actions',             name: 'actions',        orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [3, 4] }]
    });

    // Debounced patient search
    let _t;
    $('#patient_search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { vitalsTable.draw(); }, 500);
    });
});
</script>
@endsection
