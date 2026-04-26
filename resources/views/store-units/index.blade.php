@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <!-- Navigation Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Inventory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Store Units</li>
                </ol>
            </nav>
        </div>
    </div>



    <!-- Quick Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group" role="group" aria-label="Quick Actions">
                        <a href="{{ route('store-units.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Unit
                        </a>
                        <button type="button" class="btn btn-info" onclick="exportTable()">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="$('#units-table').DataTable().search('').draw();">
                            <i class="fas fa-refresh"></i> Reset Filters
                        </button>
                    </div>
                    
                    <!-- Filter Buttons -->
                    <div class="btn-group ms-3" role="group" aria-label="Filter Options">
                        <button type="button" class="btn btn-outline-secondary" onclick="filterByStatus('all')">
                            <i class="fas fa-list"></i> All
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="filterByStatus('active')">
                            <i class="fas fa-check"></i> Active
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="filterByStatus('inactive')">
                            <i class="fas fa-times"></i> Inactive
                        </button>
                    </div>

                    <div class="btn-group ms-3" role="group" aria-label="Type Filter">
                        <button type="button" class="btn btn-outline-warning" onclick="filterByType('store')">
                            <i class="fas fa-warehouse"></i> Store
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="filterByType('dispensing')">
                            <i class="fas fa-pills"></i> Dispensing
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="filterByType('both')">
                            <i class="fas fa-exchange-alt"></i> Both
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Units</h3>
                    <div class="card-tools">
                        <!-- Search Input -->
                        <div class="input-group input-group-sm me-2" style="width: 200px; display: inline-flex;">
                            <input type="text" id="quick-search" class="form-control" placeholder="Quick search..." onkeyup="quickSearch(this.value)">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Navigation Links -->
                        <div class="btn-group btn-group-sm me-2">
                            <a href="{{ route('medications.index') }}" class="btn btn-outline-secondary" title="Medications">
                                <i class="fas fa-pills"></i>
                            </a>
                            <a href="{{ route('medication-units.index') }}" class="btn btn-outline-secondary" title="Medication Units">
                                <i class="fas fa-weight"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary" title="Suppliers" onclick="alert('Suppliers module - coming soon!')">
                                <i class="fas fa-truck"></i>
                            </a>
                        </div>
                        
                        <a href="{{ route('store-units.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Unit
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="units-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Unit Name</th>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    const table = $('#units-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("store-units.index") }}',
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id', orderable: true },
            { data: 'name', name: 'name', orderable: true },
            { data: 'code_display', name: 'code', orderable: true },
            { data: 'type_display', name: 'type', orderable: true },
            { data: 'description_display', name: 'description', orderable: true },
            { data: 'status_display', name: 'is_active', orderable: true },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 20,
        responsive: true
    });

    // Make table variable global for filter functions
    window.unitsTable = table;
});

// Filter functions
function filterByStatus(status) {
    const table = window.unitsTable;
    
    if (status === 'all') {
        table.column(5).search('').draw(); // Status column
    } else if (status === 'active') {
        table.column(5).search('Active').draw();
    } else if (status === 'inactive') {
        table.column(5).search('Inactive').draw();
    }
    
    // Update button states
    $('.btn-group[aria-label="Filter Options"] .btn').removeClass('active');
    event.target.classList.add('active');
}

function filterByType(type) {
    const table = window.unitsTable;
    
    if (type === 'store') {
        table.column(3).search('Store|Both', true, false).draw(); // Type column
    } else if (type === 'dispensing') {
        table.column(3).search('Dispensing|Both', true, false).draw();
    } else if (type === 'both') {
        table.column(3).search('Both').draw();
    }
    
    // Update button states
    $('.btn-group[aria-label="Type Filter"] .btn').removeClass('active');
    event.target.classList.add('active');
}

function exportTable() {
    // Export functionality can be added with DataTables buttons extension
    alert('Export feature - requires DataTables Buttons extension');
}

// Quick search functionality
function quickSearch(searchTerm) {
    const table = window.unitsTable;
    table.search(searchTerm).draw();
}
</script>
@endsection
@endsection
