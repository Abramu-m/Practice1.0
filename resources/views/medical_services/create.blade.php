@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Create Medical Service' }}
 @endsection

@section('Content_Description')
    {{ 'Create a new medical service.' }}
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Create Medical Service</h3>
                    <a href="{{ route('medical_services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Services
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('medical_services.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Service Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="code" 
                                           id="code"
                                           class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code') }}" 
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="service_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="service_category_id" 
                                            id="service_category_id" 
                                            class="form-select @error('service_category_id') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('service_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="turnaround_time_hours" class="form-label">Turnaround Time (Hours)</label>
                                    <input type="number" 
                                           name="turnaround_time_hours" 
                                           id="turnaround_time_hours"
                                           class="form-control @error('turnaround_time_hours') is-invalid @enderror" 
                                           value="{{ old('turnaround_time_hours') }}" 
                                           min="0">
                                    @error('turnaround_time_hours')
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
                                              rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Reference Values Section -->
                            <div class="col-md-12">
                                <h5 class="text-primary">Reference Values (for Lab Tests)</h5>
                                <hr>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="min_value" class="form-label">Minimum Value</label>
                                    <input type="number" 
                                           name="min_value" 
                                           id="min_value"
                                           class="form-control @error('min_value') is-invalid @enderror" 
                                           value="{{ old('min_value') }}" 
                                           step="0.0001"
                                           placeholder="e.g., 3.5">
                                    @error('min_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_value" class="form-label">Maximum Value</label>
                                    <input type="number" 
                                           name="max_value" 
                                           id="max_value"
                                           class="form-control @error('max_value') is-invalid @enderror" 
                                           value="{{ old('max_value') }}" 
                                           step="0.0001"
                                           placeholder="e.g., 11.0">
                                    @error('max_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit</label>
                                    <input type="text" 
                                           name="unit" 
                                           id="unit"
                                           class="form-control @error('unit') is-invalid @enderror" 
                                           value="{{ old('unit') }}" 
                                           placeholder="e.g., mg/dL, mmol/L, cells/μL">
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sample Requirements -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="requires_sample" 
                                               id="requires_sample"
                                               class="form-check-input" 
                                               value="1"
                                               {{ old('requires_sample') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_sample">
                                            Requires Sample Collection
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12" id="sample_type_container" style="display: none;">
                                <div class="mb-3">
                                    <label for="sample_type" class="form-label">Sample Type</label>
                                    <input type="text" 
                                           name="sample_type" 
                                           id="sample_type"
                                           class="form-control @error('sample_type') is-invalid @enderror" 
                                           value="{{ old('sample_type') }}" 
                                           placeholder="e.g., Blood (EDTA), Urine, Stool">
                                    @error('sample_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="preparation_instructions" class="form-label">Preparation Instructions</label>
                                    <textarea name="preparation_instructions" 
                                              id="preparation_instructions"
                                              class="form-control @error('preparation_instructions') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Instructions for patient preparation (e.g., fasting requirements)">{{ old('preparation_instructions') }}</textarea>
                                    @error('preparation_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Form Requirements -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="requires_form" 
                                               id="requires_form"
                                               class="form-check-input" 
                                               value="1"
                                               {{ old('requires_form') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_form">
                                            Requires Additional Clinical Form
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12" id="form_type_container" style="display: none;">
                                <div class="mb-3">
                                    <label for="form_type" class="form-label">Form Type</label>
                                    <input type="text" 
                                           name="form_type" 
                                           id="form_type"
                                           class="form-control @error('form_type') is-invalid @enderror" 
                                           placeholder="e.g., blood_test_form, xray_form, consultation_form"
                                           value="{{ old('form_type') }}">
                                    @error('form_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Enter the name of the form template that will be included using @@include directive.
                                    </small>
                                </div>
                            </div>

                            <!-- Result Template -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="result_template_id" class="form-label">Result Template <span class="text-danger">*</span></label>
                                    <select name="result_template_id" 
                                            id="result_template_id" 
                                            class="form-select @error('result_template_id') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Result Template</option>
                                        @foreach($resultTemplates as $template)
                                            <option value="{{ $template->id }}" 
                                                    data-category="{{ $template->service_category_id }}"
                                                    data-type="{{ $template->investigation_type }}"
                                                    {{ old('result_template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }} 
                                                @if($template->investigation_type)
                                                    ({{ $template->investigation_type }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('result_template_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Choose the result template that will be used for this service.
                                        <br><a href="{{ route('result-templates.index') }}" target="_blank" class="text-primary">
                                            <i class="fas fa-external-link-alt"></i> Manage Result Templates
                                        </a>
                                    </small>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
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
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Service
                                </button>
                                <a href="{{ route('medical_services.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle sample type field based on requires_sample checkbox
    $('#requires_sample').change(function() {
        if ($(this).is(':checked')) {
            $('#sample_type_container').show();
        } else {
            $('#sample_type_container').hide();
            $('#sample_type').val('');
        }
    });

    // Toggle form type based on requires_form checkbox
    $('#requires_form').change(function() {
        if ($(this).is(':checked')) {
            $('#form_type_container').show();
        } else {
            $('#form_type_container').hide();
            $('#form_type').val('');
        }
    });

    // Initialize display based on current values
    if ($('#requires_sample').is(':checked')) {
        $('#sample_type_container').show();
    }
    
    if ($('#requires_form').is(':checked')) {
        $('#form_type_container').show();
    }

    // Reference values validation
    $('#min_value, #max_value').on('input', function() {
        const minValue = parseFloat($('#min_value').val());
        const maxValue = parseFloat($('#max_value').val());
        
        if (!isNaN(minValue) && !isNaN(maxValue) && minValue >= maxValue) {
            $('#max_value')[0].setCustomValidity('Maximum value must be greater than minimum value');
        } else {
            $('#max_value')[0].setCustomValidity('');
        }
    });

    // Filter result templates based on service category
    $('#service_category_id').change(function() {
        const selectedCategory = $(this).val();
        const templateSelect = $('#result_template_id');
        const allOptions = templateSelect.find('option');
        
        // Show/hide options based on category match
        allOptions.each(function() {
            const option = $(this);
            const optionCategory = option.data('category');
            
            // Always show empty option and legacy options
            if (!option.val() || option.parent().attr('label') === 'Legacy Options') {
                option.show();
                return;
            }
            
            // Show if template has no specific category (available for all) or matches selected category
            if (!optionCategory || optionCategory == selectedCategory) {
                option.show();
            } else {
                option.hide();
                // Deselect if hidden option was selected
                if (option.is(':selected')) {
                    templateSelect.val('');
                }
            }
        });
        
        // Refresh select if it's a select2 or similar plugin
        if (templateSelect.hasClass('select2-hidden-accessible')) {
            templateSelect.trigger('change.select2');
        }
    });

    // Initialize template filtering on page load
    $('#service_category_id').trigger('change');
});
</script>
@endpush
