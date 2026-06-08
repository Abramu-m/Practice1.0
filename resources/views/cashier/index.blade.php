@extends('layouts.app_main_layout')

@section('page_title', 'Cashier Dashboard')

@section('main_content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">

            <!-- Filters (external to DataTable) -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Patients</label>
                            <input type="text" class="form-control" id="search"
                                   placeholder="Patient name, SIC No, Authorization No, NHIF Ref..."
                                   autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Visit Status</label>
                            <select class="form-select" id="status">
                                <option value="">All Visits</option>
                                <option value="0">Waiting</option>
                                <option value="1">In Treatment</option>
                                <option value="2">Discharged</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From</label>
                            <input type="date" class="form-control" id="date_from" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To</label>
                            <input type="date" class="form-control" id="date_to" value="{{ $dateTo }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Visits DataTable -->
            <div class="card">
                <div class="card-body p-0">
                    <table id="visits-table" class="table table-hover text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Visit Date</th>
                                <th>Doctor</th>
                                <th>Visit Status</th>
                                <th>Investigations</th>
                                <th>Prescriptions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Investigations Modal -->
<div class="modal fade" id="investigationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clipboard-data"></i> Patient Investigations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="investigationsModalBody">
                <div class="text-center"><div class="spinner-border" role="status"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Prescriptions Modal -->
<div class="modal fade" id="prescriptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-capsule"></i> Patient Prescriptions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="prescriptionsModalBody">
                <div class="text-center"><div class="spinner-border" role="status"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    var table = $('#visits-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: '{{ route('cashier.index') }}',
            data: function (d) {
                d.search_term = $('#search').val();
                d.status      = $('#status').val();
                d.date_from   = $('#date_from').val();
                d.date_to     = $('#date_to').val();
            }
        },
        columns: [
            { data: 'patient',            name: 'patient',            orderable: false },
            { data: 'visit_date_col',     name: 'visit_date_col',     orderable: true },
            { data: 'doctor',             name: 'doctor',             orderable: false },
            { data: 'status_col',         name: 'status_col',         orderable: false },
            { data: 'investigations_col', name: 'investigations_col', orderable: false },
            { data: 'prescriptions_col',  name: 'prescriptions_col',  orderable: false },
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [{ orderable: false, targets: [0, 2, 3, 4, 5] }]
    });

    // Debounced text search
    let _t;
    $('#search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { table.draw(); }, 500);
    });

    // Instant filter for selects and dates
    $('#status, #date_from, #date_to').on('change', function () { table.draw(); });
});

function viewInvestigations(visitId) {
    $('#investigationsModal').modal('show');
    $('#investigationsModalBody').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    fetch(`/cashier/visits/${visitId}/investigations`)
        .then(r => r.text())
        .then(html => $('#investigationsModalBody').html(html));
}

function viewPrescriptions(visitId) {
    $('#prescriptionsModal').modal('show');
    $('#prescriptionsModalBody').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    fetch(`/cashier/visits/${visitId}/prescriptions`)
        .then(r => r.text())
        .then(html => $('#prescriptionsModalBody').html(html));
}
</script>
@endsection

@section('styles')
<style>
.table td { vertical-align: middle; }
.small .badge { font-size: 0.65em; margin: 1px; padding: 0.25em 0.4em; }
.small .badge i { font-size: 0.8em; margin-right: 2px; }
</style>
@endsection
