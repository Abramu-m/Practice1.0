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

    <!-- Quick Navigation Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-units">{{ $units->count() }}</h3>
                    <p>Total Units</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="$('#units-table').DataTable().search('').draw();">
                    View All <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="active-units">{{ $units->where('is_active', true)->count() }}</h3>
                    <p>Active Units</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="filterByStatus('active');">
                    View Active <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="store-units">{{ $units->whereIn('type', ['store', 'both'])->count() }}</h3>
                    <p>Store Units</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="filterByType('store');">
                    View Store <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="dispensing-units">{{ $units->whereIn('type', ['dispensing', 'both'])->count() }}</h3>
                    <p>Dispensing Units</p>
                </div>
                <div class="icon">
                    <i class="fas fa-pills"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="filterByType('dispensing');">
                    View Dispensing <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
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
                    <div class="btn-group ml-3" role="group" aria-label="Filter Options">
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

                    <div class="btn-group ml-3" role="group" aria-label="Type Filter">
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
                        <div class="input-group input-group-sm mr-2" style="width: 200px; display: inline-flex;">
                            <input type="text" id="quick-search" class="form-control" placeholder="Quick search..." onkeyup="quickSearch(this.value)">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Navigation Links -->
                        <div class="btn-group btn-group-sm mr-2">
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
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
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
                            <tbody>
                                @forelse($units as $unit)
                                    <tr>
                                        <td>{{ $unit->id }}</td>
                                        <td>{{ $unit->name }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $unit->code }}</span>
                                        </td>
                                        <td>
                                            @if($unit->type === 'store')
                                                <span class="badge badge-info">Store Only</span>
                                            @elseif($unit->type === 'dispensing')
                                                <span class="badge badge-warning">Dispensing Only</span>
                                            @else
                                                <span class="badge badge-success">Both</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($unit->description)
                                                {{ Str::limit($unit->description, 50) }}
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('store-units.toggle-status', $unit) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $unit->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('store-units.show', $unit) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('store-units.edit', $unit) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('store-units.destroy', $unit) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this unit?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No store units found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const table = $('#units-table').DataTable({
        responsive: true,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: -1, orderable: false }
        ],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
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
    const table = window.unitsTable;
    table.button(0).trigger(); // Trigger the first export button (copy)
}

// Reset all filters
function resetFilters() {
    const table = window.unitsTable;
    table.search('').columns().search('').draw();
    $('.btn-group .btn').removeClass('active');
}

// Quick search functionality
function quickSearch(searchTerm) {
    const table = window.unitsTable;
    table.search(searchTerm).draw();
}

// Status toggle with confirmation
function toggleStatus(unitId, currentStatus) {
    const statusText = currentStatus ? 'deactivate' : 'activate';
    const confirmMessage = `Are you sure you want to ${statusText} this unit?`;
    
    if (confirm(confirmMessage)) {
        // Submit the form
        document.getElementById(`toggle-form-${unitId}`).submit();
    }
}

// Bulk actions (future enhancement)
function selectAll() {
    const checkboxes = document.querySelectorAll('.unit-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.unit-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
