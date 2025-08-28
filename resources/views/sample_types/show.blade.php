@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Sample Type Details' }}
 @endsection

@section('Content_Description')
    {{ 'Sample type: ' . $sampleType->name }}
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sample Type Details</h3>
                    <div>
                        <a href="{{ route('sample_types.edit', $sampleType) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Sample Type
                        </a>
                        <a href="{{ route('sample_types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sample Types
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="35%">Sample Type Name:</th>
                                            <td><strong>{{ $sampleType->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Sample Code:</th>
                                            <td><code>{{ $sampleType->code }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Container Type:</th>
                                            <td>
                                                @if($sampleType->container_type)
                                                    <span class="badge bg-info">{{ $sampleType->container_type }}</span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Color Code:</th>
                                            <td>
                                                @if($sampleType->color_code)
                                                    <span class="badge bg-secondary">{{ $sampleType->color_code }}</span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($sampleType->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created:</th>
                                            <td>{{ $sampleType->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td>{{ $sampleType->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Specifications -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Sample Specifications</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Sample Volume:</th>
                                            <td>
                                                @if($sampleType->volume_ml)
                                                    <strong class="text-primary">{{ $sampleType->volume_ml }} ml</strong>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Stability:</th>
                                            <td>
                                                @if($sampleType->stability_hours)
                                                    <span class="badge bg-primary">{{ $sampleType->stability_readable }}</span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Requires Fasting:</th>
                                            <td>
                                                @if($sampleType->requires_fasting)
                                                    <span class="badge bg-warning">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Statistics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Usage Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="60%">Associated Medical Services:</th>
                                            <td><strong>{{ $sampleType->medicalServices()->count() }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Active Services:</th>
                                            <td>{{ $sampleType->medicalServices()->where('is_active', true)->count() }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($sampleType->description)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Description</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $sampleType->description }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Collection Instructions -->
                        @if($sampleType->collection_instructions)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Collection Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        {{ $sampleType->collection_instructions }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Storage Requirements -->
                        @if($sampleType->storage_requirements)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Storage Requirements</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-thermometer-half"></i>
                                        {{ $sampleType->storage_requirements }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Associated Medical Services -->
                        @if($sampleType->medicalServices->count() > 0)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Associated Medical Services</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Service Name</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sampleType->medicalServices as $service)
                                                <tr>
                                                    <td><code>{{ $service->code }}</code></td>
                                                    <td><strong>{{ $service->name }}</strong></td>
                                                    <td>
                                                        @if($service->serviceCategory)
                                                            <span class="badge bg-secondary">{{ $service->serviceCategory->name }}</span>
                                                        @else
                                                            <span class="text-muted">No category</span>
                                                        @endif
                                                    </td>
                                                    <td><strong>${{ number_format($service->price, 2) }}</strong></td>
                                                    <td>
                                                        @if($service->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('medical_services.show', $service) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .card {
        margin-bottom: 1rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
    }
</style>
@endpush
