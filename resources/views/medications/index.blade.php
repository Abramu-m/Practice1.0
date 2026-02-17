@extends('layouts.app_main_layout')

@section('page_title', 'Medications')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medications</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{ route('medications.formulations.index') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-tags"></i> Manage Formulations
                            </a>
                            <a href="{{ route('medications.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Medication
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="categoryFilter" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->description ?? $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-control">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="stockStatusFilter" class="form-control">
                                <option value="">All Stock Status</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="expired">Expired</option>
                                <option value="expiring_soon">Expiring Soon</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="clearFilters" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="medicationsTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Generic Name</th>
                                    <th>Brand Name</th>
                                    <th>Strength</th>
                                    <th>Dispensing Unit</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
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
    var table = $('#medicationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("medications.index") }}',
            data: function(d) {
                d.category_id = $('#categoryFilter').val();
                d.status = $('#statusFilter').val();
                d.stock_status = $('#stockStatusFilter').val();
            },
            error: function(xhr, error, code) {
                console.error('DataTables AJAX error:', error);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'generic_display', name: 'generic_name', orderable: true },
            { data: 'brand_name', name: 'brand_name', orderable: true },
            { data: 'strength', name: 'strength', orderable: false },
            { data: 'dispensing_unit_display', name: 'dispensingUnit.unit_code', orderable: false },
            { data: 'category_display', name: 'storeCategory.description', orderable: true },
            { data: 'stock_display', name: 'stock_quantity', orderable: true },
            { data: 'status', name: 'is_active', orderable: true },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });

    // Filter on change
    $('#categoryFilter, #statusFilter, #stockStatusFilter').on('change', function() {
        table.draw();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#categoryFilter').val('');
        $('#statusFilter').val('');
        $('#stockStatusFilter').val('');
        table.draw();
    });
});
</script>
@endsection
