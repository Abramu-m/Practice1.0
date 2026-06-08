@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Service Categories' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Service Categories</h3>
                    <a href="{{ route('service_categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('service_categories.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="search" class="form-label">Search:</label>
                                        <input type="text" 
                                               name="search" 
                                               id="search"
                                               class="form-control" 
                                               placeholder="Search by name or description"
                                               value="{{ request('search') }}">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status:</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="{{ route('service_categories.index') }}" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Services Count</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>
                                            <strong>{{ $category->name }}</strong>
                                        </td>
                                        <td>
                                            @if($category->description)
                                                {{ Str::limit($category->description, 100) }}
                                            @else
                                                <span class="text-muted">No description</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $category->medical_services_count }}</span>
                                            @if($category->medical_services_count > 0)
                                                <a href="{{ route('service_categories.show', $category) }}" 
                                                   class="btn btn-sm btn-outline-primary ms-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('service_categories.show', $category) }}" 
                                                   class="btn btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('service_categories.edit', $category) }}" 
                                                   class="btn btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('service_categories.toggle-status', $category) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-{{ $category->is_active ? 'secondary' : 'success' }}" 
                                                            title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}"
                                                            onclick="return confirm('Are you sure you want to {{ $category->is_active ? 'deactivate' : 'activate' }} this category?')">
                                                        <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('service_categories.destroy', $category) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No service categories found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} 
                                of {{ $categories->total() }} categories
                            </p>
                        </div>
                        <div class="col-md-6">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ],
        language: {
            search: "Search categories:",
            lengthMenu: "Show _MENU_ categories per page",
            info: "Showing _START_ to _END_ of _TOTAL_ categories",
            infoEmpty: "No categories found",
            infoFiltered: "(filtered from _MAX_ total categories)"
        }
    });
});
</script>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
</style>
@endpush
