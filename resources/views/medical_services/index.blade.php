@extends('layouts.app_main_layout')

@section('page_title')
    @if(request('category') == 'investigations')
        {{ 'Medical Investigations' }}
    @elseif(request('category') == 'procedures')
        {{ 'Medical Procedures' }}
    @else
        {{ 'Medical Services' }}
    @endif
 @endsection

@section('Content_Description')
    @if(request('category') == 'investigations')
        {{ 'Manage medical investigations (all medical services except procedures).' }}
    @elseif(request('category') == 'procedures')
        {{ 'Manage medical procedures.' }}
    @else
        {{ 'Manage medical services and investigations.' }}
    @endif
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        @if(request('category') == 'investigations')
                            Medical Investigations
                        @elseif(request('category') == 'procedures')
                            Medical Procedures
                        @else
                            Medical Services
                        @endif
                    </h3>
                    <a href="{{ route('medical_services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Service
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('medical_services.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="search" class="form-label">Search:</label>
                                        <input type="text" 
                                               name="search" 
                                               id="search"
                                               class="form-control" 
                                               placeholder="Search by name, code, or description"
                                               value="{{ request('search') }}">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="category" class="form-label">Category:</label>
                                        <select name="category" id="category" class="form-select">
                                            <option value="">All Categories</option>
                                            <option value="investigations" {{ request('category') == 'investigations' ? 'selected' : '' }}>
                                                Investigations
                                            </option>
                                            <option value="procedures" {{ request('category') == 'procedures' ? 'selected' : '' }}>
                                                Procedures
                                            </option>
                                            <optgroup label="Specific Categories">
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" 
                                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="status" class="form-label">Status:</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="requires_sample" class="form-label">Requires Sample:</label>
                                        <select name="requires_sample" id="requires_sample" class="form-select">
                                            <option value="">All</option>
                                            <option value="yes" {{ request('requires_sample') == 'yes' ? 'selected' : '' }}>Yes</option>
                                            <option value="no" {{ request('requires_sample') == 'no' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="requires_form" class="form-label">Requires Form:</label>
                                        <select name="requires_form" id="requires_form" class="form-select">
                                            <option value="">All</option>
                                            <option value="yes" {{ request('requires_form') == 'yes' ? 'selected' : '' }}>Yes</option>
                                            <option value="no" {{ request('requires_form') == 'no' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="{{ route('medical_services.index') }}" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Sample</th>
                                    <th>Form</th>
                                    <th>Result Template</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>
                                            <code>{{ $service->code }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $service->name }}</strong>
                                            @if($service->description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->serviceCategory)
                                                <span class="badge bg-secondary">{{ $service->serviceCategory->name }}</span>
                                            @else
                                                <span class="text-muted">No category</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->requires_sample)
                                                <span class="badge bg-warning">Required</span>
                                                @if($service->sample_type)
                                                    <br><small>{{ $service->sample_type }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->requires_form)
                                                <span class="badge bg-info">Required</span>
                                                @if($service->form_type)
                                                    <br><small>{{ $service->form_type }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->resultTemplate)
                                                <span class="badge bg-primary">{{ $service->resultTemplate->name }}</span>
                                            @else
                                                <span class="badge bg-warning">Not Set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('medical_services.show', $service) }}" 
                                                   class="btn btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medical_services.edit', $service) }}" 
                                                   class="btn btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('medical_services.toggle-status', $service) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-{{ $service->is_active ? 'secondary' : 'success' }}" 
                                                            title="{{ $service->is_active ? 'Deactivate' : 'Activate' }}"
                                                            onclick="return confirm('Are you sure you want to {{ $service->is_active ? 'deactivate' : 'activate' }} this service?')">
                                                        <i class="fas fa-{{ $service->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('medical_services.destroy', $service) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this service? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No medical services found.
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
                                Showing {{ $services->firstItem() ?? 0 }} to {{ $services->lastItem() ?? 0 }} 
                                of {{ $services->total() }} services
                            </p>
                        </div>
                        <div class="col-md-6">
                            {{ $services->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
