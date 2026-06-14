@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search Sale Number</label>
                    <input type="text" class="form-control form-control-sm" id="cs_search"
                           placeholder="Sale number..." autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select class="form-select form-select-sm" id="cs_status"
                            onchange="csTable.draw()">
                        <option value="">All</option>
                        <option value="pending">Awaiting Payment</option>
                        <option value="dispensed">Dispensed – Payment Required</option>
                        <option value="ready_to_dispense">Paid – Ready to Dispense</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Type</label>
                    <select class="form-select form-select-sm" id="cs_type"
                            onchange="csTable.draw()">
                        <option value="">All Types</option>
                        <option value="otc">Over-the-Counter</option>
                        <option value="external_prescription">External Prescription</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="cs_date_from"
                           value="{{ $dateFrom }}" onchange="csTable.draw()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="cs_date_to"
                           value="{{ $dateTo }}" onchange="csTable.draw()">
                </div>
                @unless(auth()->user()->isCashier())
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('medication-cash-sales.create') }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-plus"></i> New
                    </a>
                </div>
                @endunless
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Cash Sales — Medication</h6>
        </div>
        <div class="card-body p-0">
            <table id="cashSalesTable" class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th>Sale Number</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
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
var csTable;

$(document).ready(function () {
    csTable = $('#cashSalesTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: '{{ route("medication-cash-sales.index") }}',
            data: function (d) {
                d.search_param = $('#cs_search').val();
                d.status       = $('#cs_status').val();
                d.sale_type    = $('#cs_type').val();
                d.date_from    = $('#cs_date_from').val();
                d.date_to      = $('#cs_date_to').val();
            }
        },
        columns: [
            { data: 'sale_number_display', name: 'sale_number' },
            { data: 'type_badge',   name: 'sale_type',               orderable: false, searchable: false },
            { data: 'category',     name: 'patientCategory.description' },
            { data: 'items_count',  name: 'items_count',             orderable: false, searchable: false },
            { data: 'amount',       name: 'final_amount' },
            { data: 'status_badge', name: 'status',                  orderable: false, searchable: false },
            { data: 'creator_name', name: 'creator.name' },
            { data: 'created_date', name: 'created_at' },
            { data: 'actions',      name: 'actions',                 orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [1, 3, 5, 8] }]
    });

    // Debounced sale number search
    let _t;
    $('#cs_search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { csTable.draw(); }, 500);
    });
});
</script>
@endsection
