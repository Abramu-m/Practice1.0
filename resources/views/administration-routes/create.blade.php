@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Administration Route</h3>
                    <div class="card-tools">
                        <a href="{{ route('administration-routes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('administration-routes.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_name">Route Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('route_name') is-invalid @enderror" 
                                           id="route_name" name="route_name" value="{{ old('route_name') }}" 
                                           placeholder="e.g., Oral" required>
                                    @error('route_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_code">Route Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('route_code') is-invalid @enderror" 
                                           id="route_code" name="route_code" value="{{ old('route_code') }}" 
                                           placeholder="e.g., PO" required>
                                    @error('route_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_abbreviation">Route Abbreviation</label>
                                    <input type="text" class="form-control @error('route_abbreviation') is-invalid @enderror" 
                                           id="route_abbreviation" name="route_abbreviation" value="{{ old('route_abbreviation') }}" 
                                           placeholder="e.g., PO" maxlength="10">
                                    @error('route_abbreviation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_order">Display Order</label>
                                    <input type="text" class="form-control @error('display_order') is-invalid @enderror" 
                                           id="display_order" name="display_order" value="{{ old('display_order', 1) }}" 
                                           min="1" max="100">
                                    @error('display_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Lower numbers appear first in lists</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the administration route...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="instructions">Instructions</label>
                            <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                      id="instructions" name="instructions" rows="3" 
                                      placeholder="Special instructions for this route...">{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="requires_prescription" name="requires_prescription" 
                                               {{ old('requires_prescription', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="requires_prescription">Requires Prescription</label>
                                    </div>
                                    <small class="form-text text-muted">Check if this route requires a prescription</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Route
                        </button>
                        <a href="{{ route('administration-routes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
