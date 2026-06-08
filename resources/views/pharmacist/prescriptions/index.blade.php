@extends('layouts.app_main_layout')

@section('page_title', 'Prescription Management')

@section('main_content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search Patient</label>
                    <input type="text" class="form-control form-control-sm" id="rx_search"
                           placeholder="Name or MR number" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select class="form-select form-select-sm" id="rx_status"
                            onchange="rxTable.draw()">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="dispensed">Dispensed</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="rx_date_from"
                           value="{{ $dateFrom }}" onchange="rxTable.draw()">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="rx_date_to"
                           value="{{ $dateTo }}" onchange="rxTable.draw()">
                </div>
            </div>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-list-check"></i> Patient Visits with Prescriptions</h3>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible m-3 fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <table id="prescriptionsTable" class="table table-hover table-bordered w-100">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Visit Details</th>
                        <th>Prescriptions</th>
                        <th>Status</th>
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
var rxTable;

$(document).ready(function () {
    rxTable = $('#prescriptionsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: '{{ route("pharmacist.prescriptions.index") }}',
            data: function (d) {
                d.search    = $('#rx_search').val();
                d.status    = $('#rx_status').val();
                d.date_from = $('#rx_date_from').val();
                d.date_to   = $('#rx_date_to').val();
            }
        },
        columns: [
            { data: 'patient_info',      name: 'patientInfo.first_name', orderable: false },
            { data: 'visit_details',     name: 'created_at' },
            { data: 'prescriptions_info',name: 'prescriptions_info', orderable: false, searchable: false },
            { data: 'status_badge',      name: 'status_badge',       orderable: false, searchable: false },
            { data: 'actions',           name: 'actions',            orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [0, 2, 3, 4] }]
    });

    // Debounced patient search
    let _t;
    $('#rx_search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { rxTable.draw(); }, 500);
    });
});
</script>
@endsection
