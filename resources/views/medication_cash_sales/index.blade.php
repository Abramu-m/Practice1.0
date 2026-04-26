@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Cash Sales - Medication</h1>
                <a href="{{ route('medication-cash-sales.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Cash Sale
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row g-0 align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Sales</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['total_sales'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-pills fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row g-0 align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Awaiting Payment</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['unpaid_sales'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row g-0 align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-info text-uppercase mb-1">Ready to Dispense</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['paid_ready_to_dispense'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-pills fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row g-0 align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Completed</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['completed_sales'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-success shadow">
                        <div class="card-body">
                            <div class="row g-0 align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Today's Revenue</div>
                                    <div class="h4 mb-0 fw-bold text-gray-800">TSh {{ number_format($stats['daily_revenue'], 2) }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-3x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Filter Sales</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Awaiting Payment</option>
                                <option value="dispensed" {{ request('status') == 'dispensed' ? 'selected' : '' }}>Dispensed - Payment Required</option>
                                <option value="ready_to_dispense" {{ request('status') == 'ready_to_dispense' ? 'selected' : '' }}>Paid - Ready to Dispense</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="sale_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="otc" {{ request('sale_type') == 'otc' ? 'selected' : '' }}>Over-the-Counter</option>
                                <option value="external_prescription" {{ request('sale_type') == 'external_prescription' ? 'selected' : '' }}>External Prescription</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Sale number..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Cash Sales</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="cashSalesTable" class="table table-bordered">
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
                                    <th style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#cashSalesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("medication-cash-sales.index") }}',
            data: function(d) {
                d.status = $('select[name="status"]').val();
                d.sale_type = $('select[name="sale_type"]').val();
                d.search_param = $('input[name="search"]').val();
            }
        },
        columns: [
            { data: 'sale_number_display', name: 'sale_number' },
            { data: 'type_badge', name: 'sale_type', orderable: false, searchable: false },
            { data: 'category', name: 'patientCategory.description' },
            { data: 'items_count', name: 'items_count', orderable: false, searchable: false },
            { data: 'amount', name: 'final_amount' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'creator_name', name: 'creator.name' },
            { data: 'created_date', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
        pageLength: 15
    });

    // Filter form submission
    $('.card-body form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    // Clear button
    $('.card-body form .btn-outline-secondary').on('click', function(e) {
        e.preventDefault();
        $('select[name="status"]').val('');
        $('select[name="sale_type"]').val('');
        $('input[name="search"]').val('');
        table.draw();
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>
@endsection
