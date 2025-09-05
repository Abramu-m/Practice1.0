@extends('layouts.app_main_layout')

@section('page_title', 'Add New Medication')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Medication</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <form action="{{ route('medications.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="generic_name">Generic Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('generic_name') is-invalid @enderror" 
                                           id="generic_name" name="generic_name" value="{{ old('generic_name') }}" required>
                                    @error('generic_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_name">Brand Name</label>
                                    <input type="text" class="form-control @error('brand_name') is-invalid @enderror" 
                                           id="brand_name" name="brand_name" value="{{ old('brand_name') }}">
                                    @error('brand_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="strength">Strength</label>
                                    <input type="text" class="form-control @error('strength') is-invalid @enderror" 
                                           id="strength" name="strength" value="{{ old('strength') }}" placeholder="e.g., 500mg">
                                    @error('strength')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="formulation_id">Formulation</label>
                                    <select class="form-control select2-formulation @error('formulation_id') is-invalid @enderror" 
                                            id="formulation_id" name="formulation_id" style="width: 100%;">
                                        <option value="">Select or type formulation...</option>
                                        @foreach($formulations as $formulation)
                                            <option value="{{ $formulation->id }}" 
                                                {{ old('formulation_id') == $formulation->id ? 'selected' : '' }}>
                                                {{ $formulation->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('formulation_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dispensing_unit_id">Dispensing Unit</label>
                                    <select class="form-control select2-dispensing-unit @error('dispensing_unit_id') is-invalid @enderror" 
                                            id="dispensing_unit_id" name="dispensing_unit_id" style="width: 100%;">
                                        <option value="">Select or type dispensing unit...</option>
                                        @foreach($dispensingUnits as $unit)
                                            <option value="{{ $unit->id }}" 
                                                {{ old('dispensing_unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->unit_name }} ({{ $unit->unit_code }})
                                                @if($unit->unit_symbol)
                                                    - {{ $unit->unit_symbol }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dispensing_unit_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="barcode">Barcode</label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                           id="barcode" name="barcode" value="{{ old('barcode') }}">
                                    @error('barcode')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', request('category_id')) == $category->id ? 'selected' : '' }}>
                                                {{ $category->description ?? $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reorder_level">Reorder Level</label>
                                    <input type="text" class="form-control @error('reorder_level') is-invalid @enderror" 
                                           id="reorder_level" name="reorder_level" value="{{ old('reorder_level', 10) }}" 
                                           min="0" step="0.01">
                                    @error('reorder_level')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="maximum_stock_level">Maximum Stock Level</label>
                                    <input type="text" class="form-control @error('maximum_stock_level') is-invalid @enderror" 
                                           id="maximum_stock_level" name="maximum_stock_level" value="{{ old('maximum_stock_level', 100) }}" 
                                           min="0" step="0.01">
                                    @error('maximum_stock_level')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="storage_conditions">Storage Conditions</label>
                                    <input type="text" class="form-control @error('storage_conditions') is-invalid @enderror" 
                                           id="storage_conditions" name="storage_conditions" value="{{ old('storage_conditions') }}">
                                    @error('storage_conditions')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="requires_prescription" value="0">
                                        <input type="checkbox" class="custom-control-input" id="requires_prescription" name="requires_prescription" value="1"
                                               {{ old('requires_prescription', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="requires_prescription">Requires Prescription</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_controlled" value="0">
                                        <input type="checkbox" class="custom-control-input" id="is_controlled" name="is_controlled" value="1"
                                               {{ old('is_controlled', false) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_controlled">Controlled Substance</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Medication
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
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Name:</strong> Enter the medication name</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Generic Name:</strong> Scientific/generic name</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Brand Name:</strong> Commercial brand name</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Strength:</strong> Dosage strength (e.g., 500mg)</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Formulation:</strong> Select from predefined formulation types</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Unit Price:</strong> Cost per unit</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Minimum Stock:</strong> Reorder level</li>
                        <li><i class="fas fa-info-circle text-info"></i> <strong>Expiry Date:</strong> When medication expires</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Select2 assets are loaded globally in the layout; only keep initialization below -->

<script>
$(document).ready(function() {
    // Initialize Select2 for formulation dropdown
    $('.select2-formulation').select2({
        theme: 'bootstrap',
        placeholder: 'Select or type formulation...',
        allowClear: true,
        width: '100%',
        // Enable searching/filtering
        minimumInputLength: 0,
        // Custom matcher for better search
        matcher: function(params, data) {
            // If there are no search terms, return all data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Skip if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // Check if the text contains the term (case insensitive)
            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                return data;
            }

            // Return null if the term doesn't match
            return null;
        }
    });

    // Initialize Select2 for dispensing unit dropdown
    $('.select2-dispensing-unit').select2({
        theme: 'bootstrap',
        placeholder: 'Select or type dispensing unit...',
        allowClear: true,
        width: '100%',
        // Enable searching/filtering
        minimumInputLength: 0,
        // Custom matcher for better search
        matcher: function(params, data) {
            // If there are no search terms, return all data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Skip if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // Check if the text contains the term (case insensitive)
            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                return data;
            }

            // Return null if the term doesn't match
            return null;
        }
    });

    // Handle validation state - make select2 work with Bootstrap validation
    $('.select2-formulation, .select2-dispensing-unit').on('select2:close', function() {
        $(this).trigger('blur');
    });

    // Fix validation styling for select2
    $('.select2-formulation, .select2-dispensing-unit').on('select2:open', function() {
        $('.select2-dropdown').addClass('select2-dropdown--bootstrap');
    });
});
</script>
@endsection
