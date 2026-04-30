@extends('layouts.app_main_layout')

@section('page_title', 'Create Result Template')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Create New Result Template</h3>
                    <a href="{{ route('result-templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Templates
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('result-templates.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Template Name -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" 
                                           placeholder="e.g., Simple Lab Results"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Template Code -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Template Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="code" 
                                           id="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code') }}" 
                                           placeholder="e.g., cd4, simple_lab, imaging"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Enter a unique code. The system will look for a Blade template file named <strong>[code].blade.php</strong> during result entry.
                                    </small>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" 
                                              id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Detailed description of when and how to use this template">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" 
                                           name="sort_order" 
                                           id="sort_order"
                                           class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', 0) }}" 
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Templates with lower sort order appear first in lists.
                                    </small>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               id="is_active"
                                               class="form-check-input" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Only active templates can be selected when creating/editing medical services.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Template
                                </button>
                                <a href="{{ route('result-templates.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
