@extends('layouts.app_main_layout')

@section('page_title', 'Service Category Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service Category Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('service_categories.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('service_categories.edit', $serviceCategory->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Name:</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Code:</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->code ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label>Description:</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->description ?? 'No description available' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Sort Order:</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->sort_order ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Status:</label>
                                <p class="form-control-plaintext">
                                    @if($serviceCategory->status == 'active')
                                        <span class="badge bg-success text-black">Active</span>
                                    @else
                                        <span class="badge bg-danger text-black">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Default Category:</label>
                                <p class="form-control-plaintext">
                                    @if($serviceCategory->is_default)
                                        <span class="badge bg-primary text-black">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Medical Services Count:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-info text-black">{{ $serviceCategory->medical_services_count }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Created At:</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Last Updated:</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medical Services</h3>
                </div>
                <div class="card-body">
                    @if($serviceCategory->medicalServices && $serviceCategory->medicalServices->count() > 0)
                        <div class="list-group">
                            @foreach($serviceCategory->medicalServices as $service)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $service->name }}</h6>
                                        <small>
                                            @if($service->status == 'active')
                                                <span class="badge bg-success text-black">Active</span>
                                            @else
                                                <span class="badge bg-danger text-black">Inactive</span>
                                            @endif
                                        </small>
                                    </div>
                                    @if($service->description)
                                        <p class="mb-1 text-muted">{{ Str::limit($service->description, 100) }}</p>
                                    @endif
                                    <small class="text-muted">Code: {{ $service->code ?? 'N/A' }}</small>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('medical_services.index', ['category_id' => $serviceCategory->id]) }}" class="btn btn-sm btn-outline-primary">
                                View All Services
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>No medical services assigned to this category yet.</p>
                            <a href="{{ route('medical_services.create', ['category_id' => $serviceCategory->id]) }}" class="btn btn-sm btn-primary">
                                Add Medical Service
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('service_categories.edit', $serviceCategory->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Category
                        </a>
                        <a href="{{ route('medical_services.create', ['category_id' => $serviceCategory->id]) }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Medical Service
                        </a>
                        <form action="{{ route('service_categories.destroy', $serviceCategory->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this category?')">
                                <i class="fas fa-trash"></i> Delete Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection
