@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Edit Sample Type' }}
 @endsection

@section('Content_Description')
    {{ 'Edit sample type: ' . $sampleType->name }}
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Sample Type</h3>
                    <div>
                        <a href="{{ route('sample_types.show', $sampleType) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('sample_types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sample Types
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('sample_types.update', $sampleType) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Sample Type Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $sampleType->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Sample Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="code" 
                                           id="code"
                                           class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code', $sampleType->code) }}" 
                                           maxlength="10"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="container_type" class="form-label">Container Type</label>
                                    <input type="text" 
                                           name="container_type" 
                                           id="container_type"
                                           class="form-control @error('container_type') is-invalid @enderror" 
                                           value="{{ old('container_type', $sampleType->container_type) }}" 
                                           placeholder="e.g., EDTA Tube, Plain Tube, Sterile Container">
                                    @error('container_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color_code" class="form-label">Color Code</label>
                                    <input type="text" 
                                           name="color_code" 
                                           id="color_code"
                                           class="form-control @error('color_code') is-invalid @enderror" 
                                           value="{{ old('color_code', $sampleType->color_code) }}" 
                                           placeholder="e.g., Purple, Red, Blue">
                                    @error('color_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="volume_ml" class="form-label">Sample Volume (ml)</label>
                                    <input type="text" 
                                           name="volume_ml" 
                                           id="volume_ml"
                                           class="form-control @error('volume_ml') is-invalid @enderror" 
                                           value="{{ old('volume_ml', $sampleType->volume_ml) }}" 
                                           step="0.1"
                                           min="0">
                                    @error('volume_ml')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stability_hours" class="form-label">Stability (Hours)</label>
                                    <input type="text" 
                                           name="stability_hours" 
                                           id="stability_hours"
                                           class="form-control @error('stability_hours') is-invalid @enderror" 
                                           value="{{ old('stability_hours', $sampleType->stability_hours) }}" 
                                           min="0">
                                    @error('stability_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" 
                                              id="description"
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3">{{ old('description', $sampleType->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="collection_instructions" class="form-label">Collection Instructions</label>
                                    <textarea name="collection_instructions" 
                                              id="collection_instructions"
                                              class="form-control @error('collection_instructions') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Instructions for sample collection">{{ old('collection_instructions', $sampleType->collection_instructions) }}</textarea>
                                    @error('collection_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="storage_requirements" class="form-label">Storage Requirements</label>
                                    <textarea name="storage_requirements" 
                                              id="storage_requirements"
                                              class="form-control @error('storage_requirements') is-invalid @enderror" 
                                              rows="2" 
                                              placeholder="e.g., Room temperature, Refrigerated, Frozen">{{ old('storage_requirements', $sampleType->storage_requirements) }}</textarea>
                                    @error('storage_requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Checkboxes -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="requires_fasting" 
                                               id="requires_fasting"
                                               class="form-check-input" 
                                               value="1"
                                               {{ old('requires_fasting', $sampleType->requires_fasting) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_fasting">
                                            Requires Fasting
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               id="is_active"
                                               class="form-check-input" 
                                               value="1"
                                               {{ old('is_active', $sampleType->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Sample Type
                                </button>
                                <a href="{{ route('sample_types.show', $sampleType) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="{{ route('sample_types.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
