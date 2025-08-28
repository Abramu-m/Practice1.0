@extends('layouts.app_main_layout')

@section('page_title', 'Suppliers Management')

@section('styles')
<style>
    .card-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .card-stats .card-body {
        padding: 1.5rem;
    }
    
    .stats-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    
    .badge-active {
        background-color: #28a745;
    }
    
    .badge-inactive {
        background-color: #dc3545;
    }
    
    .btn-action {
        margin: 0 2px;
        padding: 4px 8px;
        font-size: 12px;
    }
    
    .credit-exceeded {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
    }
    
    .credit-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
    }
    
    .filter-card {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box .fa-search {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .search-box input {
        padding-left: 35px;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">
    <!-- Header with Stats -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0">Suppliers Management</h2>
                <a href="{{ route('medications.stock.suppliers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Supplier
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-building stats-icon"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category mb-0">Total Suppliers</p>
                                <h4 class="card-title mb-0">{{ $suppliers->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card card-stats bg-success">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-check-circle stats-icon"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category mb-0">Active Suppliers</p>
                                <h4 class="card-title mb-0">{{ $suppliers->where('is_active', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card card-stats bg-warning">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-exclamation-triangle stats-icon"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category mb-0">Credit Exceeded</p>
                                <h4 class="card-title mb-0">{{ $suppliers->where('credit_exceeded', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card card-stats bg-info">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-receipt stats-icon"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category mb-0">Total GRNs</p>
                                <h4 class="card-title mb-0">{{ $suppliers->sum('goods_received_notes_count') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('medications.stock.suppliers.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search">Search Suppliers</label>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search by name, email, phone...">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select class="form-control" id="country" name="country">
                            <option value="">All Countries</option>
                            @foreach($suppliers->pluck('country')->unique()->filter() as $country)
                                <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="credit_status">Credit Status</label>
                        <select class="form-control" id="credit_status" name="credit_status">
                            <option value="">All</option>
                            <option value="exceeded" {{ request('credit_status') == 'exceeded' ? 'selected' : '' }}>Credit Exceeded</option>
                            <option value="within_limit" {{ request('credit_status') == 'within_limit' ? 'selected' : '' }}>Within Limit</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('medications.stock.suppliers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Suppliers List</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-secondary" data-toggle="collapse" data-target="#tableFilters">
                    <i class="fas fa-cog"></i> Table Options
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="suppliersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Financial Info</th>
                            <th>Status</th>
                            <th>GRNs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr class="{{ $supplier->credit_exceeded ? 'credit-exceeded' : ($supplier->pending_amount > ($supplier->credit_limit * 0.8) ? 'credit-warning' : '') }}">
                                <td>
                                    <strong>#{{ $supplier->id }}</strong>
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $supplier->name }}</strong>
                                    </div>
                                </td>
                                
                                <td>
                                    @if($supplier->email)
                                        <div><i class="fas fa-envelope"></i> {{ $supplier->email }}</div>
                                    @endif
                                    @if($supplier->phone)
                                        <div><i class="fas fa-phone"></i> {{ $supplier->phone }}</div>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($supplier->address)
                                        <div>{{ Str::limit($supplier->address, 30) }}</div>
                                    @endif
                                    <div>
                                        @if($supplier->city){{ $supplier->city }}@endif
                                        @if($supplier->country), {{ $supplier->country }}@endif
                                    </div>
                                    @if($supplier->postal_code)
                                        <small class="text-muted">{{ $supplier->postal_code }}</small>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($supplier->credit_limit)
                                        <div>
                                            <strong>Credit Limit:</strong> ${{ number_format($supplier->credit_limit, 2) }}
                                        </div>
                                        <div>
                                            <strong>Pending:</strong> ${{ number_format($supplier->pending_amount, 2) }}
                                        </div>
                                        @if($supplier->credit_exceeded)
                                            <span class="badge badge-danger">Credit Exceeded</span>
                                        @endif
                                    @endif
                                    @if($supplier->credit_days)
                                        <div><small>Days: {{ $supplier->credit_days }}</small></div>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($supplier->is_active)
                                        <span class="badge badge-active">Active</span>
                                    @else
                                        <span class="badge badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <span class="badge badge-info">{{ $supplier->goods_received_notes_count }}</span>
                                </td>
                                
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('medications.stock.suppliers.show', $supplier->id) }}" 
                                           class="btn btn-info btn-action" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('medications.stock.suppliers.edit', $supplier->id) }}" 
                                           class="btn btn-warning btn-action" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($supplier->is_active)
                                            <button type="button" 
                                                    class="btn btn-secondary btn-action" 
                                                    onclick="toggleStatus({{ $supplier->id }}, false)"
                                                    title="Deactivate">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-success btn-action" 
                                                    onclick="toggleStatus({{ $supplier->id }}, true)"
                                                    title="Activate">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        <button type="button" 
                                                class="btn btn-danger btn-action" 
                                                onclick="deleteSupplier({{ $supplier->id }})"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>No suppliers found</h5>
                                        <p>Get started by adding your first supplier.</p>
                                        <a href="{{ route('medications.stock.suppliers.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Supplier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Status Toggle Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="statusMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusChange">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this supplier? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentSupplierId = null;
    
    function toggleStatus(supplierId, newStatus) {
        currentSupplierId = supplierId;
        const action = newStatus ? 'activate' : 'deactivate';
        document.getElementById('statusMessage').textContent = 
            `Are you sure you want to ${action} this supplier?`;
        
        document.getElementById('confirmStatusChange').onclick = function() {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('medications.stock.suppliers.index') }}/${supplierId}/toggle-status`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'is_active';
            statusInput.value = newStatus ? '1' : '0';
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            form.appendChild(statusInput);
            
            document.body.appendChild(form);
            form.submit();
        };
        
        $('#statusModal').modal('show');
    }
    
    function deleteSupplier(supplierId) {
        currentSupplierId = supplierId;
        
        document.getElementById('confirmDelete').onclick = function() {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('medications.stock.suppliers.index') }}/${supplierId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        };
        
        $('#deleteModal').modal('show');
    }
    
    // DataTable initialization
    $(document).ready(function() {
        $('#suppliersTable').DataTable({
            "pageLength": 25,
            "order": [[ 1, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [7] }
            ],
            "language": {
                "search": "Search suppliers:",
                "lengthMenu": "Show _MENU_ suppliers per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ suppliers"
            }
        });
    });
</script>
@endsection
