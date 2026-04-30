@extends('layouts.app_main_layout')

@section('page_title', 'Result Template Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Result Template Details</h3>
                    <div>
                        <a href="{{ route('result-templates.edit', $resultTemplate) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Template
                        </a>
                        <a href="{{ route('result-templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Templates
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><strong>Template Name:</strong></label>
                                <p class="form-control-plaintext">{{ $resultTemplate->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><strong>Template Code:</strong></label>
                                <p class="form-control-plaintext"><code>{{ $resultTemplate->code }}</code></p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><strong>Sort Order:</strong></label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-light text-dark">{{ $resultTemplate->sort_order }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><strong>Status:</strong></label>
                                <p class="form-control-plaintext">
                                    @if($resultTemplate->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($resultTemplate->description)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label><strong>Description:</strong></label>
                                <p class="form-control-plaintext">{{ $resultTemplate->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Timestamps -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><strong>Created:</strong></label>
                                <p class="form-control-plaintext">{{ $resultTemplate->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><strong>Last Updated:</strong></label>
                                <p class="form-control-plaintext">{{ $resultTemplate->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Medical Services -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Medical Services Using This Template</h5>
                </div>
                <div class="card-body">
                    @if($resultTemplate->medicalServices->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($resultTemplate->medicalServices as $service)
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $service->name }}</h6>
                                        <small>
                                            @if($service->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </small>
                                    </div>
                                    @if($service->description)
                                        <p class="mb-1 text-muted">{{ Str::limit($service->description, 100) }}</p>
                                    @endif
                                    <small class="text-muted">Code: {{ $service->code }}</small>
                                    @if($service->serviceCategory)
                                        <br><small class="text-muted">Category: {{ $service->serviceCategory->name }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('medical_services.index', ['result_template' => $resultTemplate->code]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                View All Services with This Template
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>This template is not currently being used by any medical services.</p>
                            <a href="{{ route('medical_services.create') }}" class="btn btn-sm btn-outline-primary">
                                Create New Medical Service
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('result-templates.edit', $resultTemplate) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Template
                        </a>
                        
                        <form method="POST" action="{{ route('result-templates.toggle-status', $resultTemplate) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $resultTemplate->is_active ? 'secondary' : 'success' }} w-100">
                                <i class="fas fa-{{ $resultTemplate->is_active ? 'pause' : 'play' }}"></i> 
                                {{ $resultTemplate->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        
                        @if($resultTemplate->medicalServices->count() == 0)
                        <form method="POST" action="{{ route('result-templates.destroy', $resultTemplate) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete Template
                            </button>
                        </form>
                        @else
                        <div class="alert alert-warning">
                            <small><i class="fas fa-exclamation-triangle"></i> Cannot delete: Template is in use by {{ $resultTemplate->medicalServices->count() }} service(s)</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
