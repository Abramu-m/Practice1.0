@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Medication Pricing</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-pricing.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('medication-pricing.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="medication_id">Medication <span class="text-danger">*</span></label>
                                    <select class="form-control @error('medication_id') is-invalid @enderror" 
                                            id="medication_id" name="medication_id" required>
                                        <option value="">Select Medication</option>
                                        @foreach($medications as $medication)
                                            <option value="{{ $medication->id }}" {{ old('medication_id') == $medication->id ? 'selected' : '' }}>
                                                {{ $medication->name }}
                                                @if($medication->generic_name)
                                                    ({{ $medication->generic_name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medication_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patient_category_id">Patient Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient_category_id') is-invalid @enderror" 
                                            id="patient_category_id" name="patient_category_id" required>
                                        <option value="">Select Patient Category</option>
                                        @foreach($patientCategories as $category)
                                            <option value="{{ $category->id }}" {{ old('patient_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="selling_price">Selling Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="text" class="form-control @error('selling_price') is-invalid @enderror" 
                                               id="selling_price" name="selling_price" value="{{ old('selling_price') }}" 
                                               step="0.01" min="0" required>
                                        @error('selling_price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="markup_percentage">Markup %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('markup_percentage') is-invalid @enderror" 
                                               id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage') }}" 
                                               step="0.01" min="0" max="100">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('markup_percentage')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="discount_percentage">Discount %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                               id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" 
                                               step="0.01" min="0" max="100">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('discount_percentage')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="effective_from">Effective From <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('effective_from') is-invalid @enderror" 
                                           id="effective_from" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" required>
                                    @error('effective_from')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="effective_to">Effective To</label>
                                    <input type="date" class="form-control @error('effective_to') is-invalid @enderror" 
                                           id="effective_to" name="effective_to" value="{{ old('effective_to') }}">
                                    @error('effective_to')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank for ongoing pricing</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
                            <i class="fas fa-save"></i> Create Pricing
                        </button>
                        <a href="{{ route('medication-pricing.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
