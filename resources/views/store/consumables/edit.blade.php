@extends('layouts.app_main_layout')

@section('page_title', 'Edit Consumable')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Consumable: {{ $consumable->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('store.consumables.show', $consumable->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                <form action="{{ route('store.consumables.update', $consumable->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $consumable->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $consumable->code) }}" required>
                                    @error('code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('category_id', $consumable->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_id">Unit <span class="text-danger">*</span></label>
                                    <select class="form-control @error('unit_id') is-invalid @enderror" 
                                            id="unit_id" name="unit_id" required>
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" 
                                                {{ old('unit_id', $consumable->unit_id) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }} ({{ $unit->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $consumable->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reorder_level">Reorder Level <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('reorder_level') is-invalid @enderror" 
                                           id="reorder_level" name="reorder_level" 
                                           value="{{ old('reorder_level', $consumable->reorder_level) }}" 
                                           min="0" required>
                                    @error('reorder_level')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="maximum_level">Maximum Level</label>
                                    <input type="text" class="form-control @error('maximum_level') is-invalid @enderror" 
                                           id="maximum_level" name="maximum_level" 
                                           value="{{ old('maximum_level', $consumable->maximum_level) }}" 
                                           min="0">
                                    @error('maximum_level')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="barcode">Barcode</label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                   id="barcode" name="barcode" value="{{ old('barcode', $consumable->barcode) }}">
                            @error('barcode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                       {{ old('is_active', $consumable->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Consumable
                        </button>
                        <a href="{{ route('medications.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Stock Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $consumable->getCurrentStock() > 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Stock</span>
                            <span class="info-box-number">{{ number_format($consumable->getCurrentStock()) }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $consumable->stock_status_color }}">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stock Status</span>
                            <span class="info-box-number">{{ ucfirst(str_replace('_', ' ', $consumable->stock_status)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
