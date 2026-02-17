@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Sample Types' }}
 @endsection

@section('Content_Description')
    {{ 'Manage sample types for medical investigations.' }}
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sample Types</h3>
                    <a href="{{ route('sample_types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Sample Type
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('sample_types.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="search" class="form-label">Search:</label>
                                        <input type="text" 
                                               name="search" 
                                               id="search"
                                               class="form-control" 
                                               placeholder="Search by name, code, or description"
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
                                        <label for="requires_fasting" class="form-label">Requires Fasting:</label>
                                        <select name="requires_fasting" id="requires_fasting" class="form-select">
                                            <option value="">All</option>
                                            <option value="yes" {{ request('requires_fasting') == 'yes' ? 'selected' : '' }}>Yes</option>
                                            <option value="no" {{ request('requires_fasting') == 'no' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="{{ route('sample_types.index') }}" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sample Types Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Container</th>
                                    <th>Volume</th>
                                    <th>Stability</th>
                                    <th>Fasting</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sampleTypes as $sampleType)
                                    <tr>
                                        <td>
                                            <code>{{ $sampleType->code }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $sampleType->name }}</strong>
                                            @if($sampleType->description)
                                                <br><small class="text-muted">{{ Str::limit($sampleType->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sampleType->container_type)
                                                <span class="badge bg-info">{{ $sampleType->container_type }}</span>
                                                @if($sampleType->color_code)
                                                    <br><small class="text-muted">{{ $sampleType->color_code }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sampleType->volume_ml)
                                                <strong>{{ $sampleType->volume_ml }} ml</strong>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sampleType->stability_hours)
                                                <span class="badge bg-primary">{{ $sampleType->stability_readable }}</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sampleType->requires_fasting)
                                                <span class="badge bg-warning">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sampleType->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('sample_types.show', $sampleType) }}" 
                                                   class="btn btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sample_types.edit', $sampleType) }}" 
                                                   class="btn btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('sample_types.toggle-status', $sampleType) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-{{ $sampleType->is_active ? 'secondary' : 'success' }}" 
                                                            title="{{ $sampleType->is_active ? 'Deactivate' : 'Activate' }}"
                                                            onclick="return confirm('Are you sure you want to {{ $sampleType->is_active ? 'deactivate' : 'activate' }} this sample type?')">
                                                        <i class="fas fa-{{ $sampleType->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('sample_types.destroy', $sampleType) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this sample type? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            No sample types found.
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
                                Showing {{ $sampleTypes->firstItem() ?? 0 }} to {{ $sampleTypes->lastItem() ?? 0 }} 
                                of {{ $sampleTypes->total() }} sample types
                            </p>
                        </div>
                        <div class="col-md-6">
                            {{ $sampleTypes->links() }}
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
            search: "Search sample types:",
            lengthMenu: "Show _MENU_ sample types per page",
            info: "Showing _START_ to _END_ of _TOTAL_ sample types",
            infoEmpty: "No sample types found",
            infoFiltered: "(filtered from _MAX_ total sample types)"
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
