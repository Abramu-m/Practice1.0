@extends('layouts.app_main_layout')

@section('page_title')
    Salary Payments
@endsection

@section('main_content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-cash-coin"></i> Salary Payments</h3>
        <div>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="bi bi-gear"></i> Generate Payroll
            </button>
            <a href="{{ route('hr.salary-payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Salary Payment
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <input type="number" id="filter_year" class="form-control" value="{{ now()->year }}" min="2000">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select id="filter_month" class="form-select">
                        <option value="">-- All --</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="filter_status" class="form-select">
                        <option value="">-- All --</option>
                        <option value="draft">Draft</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select id="filter_employee" class="form-select select2">
                        <option value="">-- All --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <table id="salaryPaymentsTable" class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hr.salary-payments.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Creates a draft salary payment for every active employee for the selected period,
                        pre-filled with their recurring allowances/deductions and PAYE. Employees who
                        already have a payment for this period are skipped.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="pay_period_year" class="form-control" value="{{ now()->year }}" min="2000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <select name="pay_period_month" class="form-select" required>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });

    var table = $('#salaryPaymentsTable').DataTable({
        responsive: true,
        pageLength: 25,
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        ajax: {
            url: '{{ route("hr.salary-payments.index") }}',
            data: function (d) {
                d.pay_period_year = $('#filter_year').val();
                d.pay_period_month = $('#filter_month').val();
                d.status = $('#filter_status').val();
                d.employee_id = $('#filter_employee').val();
            }
        },
        columns: [
            { data: 'payment_number', name: 'payment_number' },
            { data: 'employee_name', name: 'employee_name' },
            { data: 'period', name: 'period' },
            { data: 'net_salary_display', name: 'net_salary_display' },
            { data: 'status_badge', name: 'status_badge' },
            { data: 'actions', name: 'actions', orderable: false }
        ],
        columnDefs: [
            { orderable: false, targets: [-1] }
        ]
    });

    $('#filter_year, #filter_month, #filter_status, #filter_employee').on('change', function () {
        table.ajax.reload();
    });
});
</script>
@endsection
