@extends('layouts.app_main_layout')

@section('page_title', 'Category Details')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Category Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store.dashboard') }}">Store</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store-categories.index') }}">Categories</a></li>
                        <li class="breadcrumb-item active">{{ $storeCategory->description }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Category Information Card -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Category Information
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('store-categories.edit', $storeCategory->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $storeCategory->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $storeCategory->description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Items:</strong></td>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            {{ $medications->count() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $storeCategory->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $storeCategory->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100">
                                <a href="{{ route('store-categories.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                <a href="{{ route('store-categories.edit', $storeCategory->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('store-categories.destroy', $storeCategory->id) }}" 
                                      method="POST" 
                                      style="display: inline-block;"
                                      onsubmit="return confirm('Are you sure you want to delete this category? This will affect {{ $medications->count() }} items.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            {{ $medications->count() > 0 ? 'disabled title=Cannot delete category with items' : '' }}>
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Quick Statistics
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-right">
                                        <h4 class="text-primary">{{ $medications->where('is_active', true)->count() }}</h4>
                                        <small class="text-muted">Active Items</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning">{{ $medications->where('is_active', false)->count() }}</h4>
                                    <small class="text-muted">Inactive Items</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-right">
                                        <h4 class="text-success">{{ $medications->where('stock_quantity', '>', 0)->count() }}</h4>
                                        <small class="text-muted">In Stock</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-danger">{{ $medications->where('stock_quantity', '<=', 0)->count() }}</h4>
                                    <small class="text-muted">Out of Stock</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items in Category -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-boxes mr-2"></i>
                                Items in this Category ({{ $medications->count() }})
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('medications.create') }}?category_id={{ $storeCategory->id }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Item
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($medications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Generic Name</th>
                                                <th>Stock Quantity</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($medications as $medication)
                                            <tr>
                                                <td>
                                                    <strong>{{ $medication->name }}</strong>
                                                    @if($medication->brand_name)
                                                        <br><small class="text-muted">{{ $medication->brand_name }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $medication->generic_name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge {{ $medication->stock_quantity > 0 ? 'badge-success' : 'badge-danger' }} text-black">
                                                        {{ number_format($medication->stock_quantity, 2) }}
                                                    </span>
                                                    @if($medication->stock_quantity <= $medication->reorder_level && $medication->stock_quantity > 0)
                                                        <small class="text-warning d-block">Low Stock</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $medication->is_active ? 'badge-success' : 'badge-secondary' }}  text-black">
                                                        {{ $medication->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('medications.show', $medication->id) }}" 
                                                           class="btn btn-sm btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('medications.edit', $medication->id) }}" 
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <h5>No items in this category</h5>
                                    <p>This category doesn't have any items yet.</p>
                                    <a href="{{ route('medications.create') }}?category_id={{ $storeCategory->id }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add First Item
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.border-right {
    border-right: 1px solid #dee2e6;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable if there are items
    if ($('#itemsTable').length) {
        $('#itemsTable').DataTable({
            responsive: true,
            autoWidth: false,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [-1] }
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });
    }
});
</script>
@endsection
