@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Medication Insurance Mapping</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-insurance-map.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('medication-insurance-map.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="medication_id">Medication <span class="text-danger">*</span></label>
                                    <select class="form-control select2-medication @error('medication_id') is-invalid @enderror"
                                            id="medication_id" name="medication_id" required style="width: 100%;">
                                        <option value="">Select Medication</option>
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
                                            <option value="{{ $category->id }}"
                                                    data-has-tariffs="{{ $category->tariffs_table ? '1' : '0' }}"
                                                    {{ old('patient_category_id') == $category->id ? 'selected' : '' }}>
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
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="insurance_item_code">Insurance Item Code <span class="text-danger">*</span></label>

                                    {{-- Tariff select (shown when category has a tariffs_table) --}}
                                    <select class="form-control select2-tariff @error('insurance_item_code') is-invalid @enderror"
                                            id="insurance_item_code_select" style="width: 100%; display: none;">
                                        <option value="">Select patient category first...</option>
                                    </select>

                                    {{-- Fallback text input (shown when category has no tariffs_table) --}}
                                    <input type="text" class="form-control @error('insurance_item_code') is-invalid @enderror"
                                           id="insurance_item_code_text"
                                           value="{{ old('insurance_item_code') }}"
                                           maxlength="255" style="display: none;">

                                    {{-- Hidden field that always holds the submitted value --}}
                                    <input type="hidden" name="insurance_item_code" id="insurance_item_code"
                                           value="{{ old('insurance_item_code') }}" required>

                                    @error('insurance_item_code')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted" id="tariff-hint">Select a patient category to load available tariff items.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3" id="unit-price-display" style="display: none;">
                                    <label>Insurance Tariff Price</label>
                                    <div class="form-control bg-light" id="unit-price-value">—</div>
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
                            <i class="fas fa-save"></i> Save Mapping
                        </button>
                        <a href="{{ route('medication-insurance-map.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var tariffSearchUrl = '{{ route("tariff-lookup.search") }}';

    // Medication Select2
    $('.select2-medication').select2({
        placeholder: 'Type to search for medication...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("medications.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) { return { q: params.term, page: params.page || 1 }; },
            processResults: function(data) {
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        }
    });

    // When patient category changes, reinitialize the tariff Select2
    $('#patient_category_id').on('change', function() {
        var categoryId = $(this).val();
        var hasTariffs = $('option:selected', this).data('has-tariffs');

        // Reset code field
        $('#insurance_item_code').val('');
        $('#unit-price-display').hide();
        $('#unit-price-value').text('—');

        if (!categoryId) {
            $('#insurance_item_code_select').hide();
            $('#insurance_item_code_text').hide();
            $('#tariff-hint').text('Select a patient category to load available tariff items.');
            return;
        }

        if (hasTariffs == '1') {
            $('#insurance_item_code_text').hide();
            $('#insurance_item_code_select').show();
            $('#tariff-hint').text('Search by item name or code from the insurer\'s tariff list.');

            // Destroy and reinit Select2 with new category
            if ($('#insurance_item_code_select').hasClass('select2-hidden-accessible')) {
                $('#insurance_item_code_select').select2('destroy');
            }
            $('#insurance_item_code_select').val(null).empty()
                .append('<option value="">Type to search tariff items...</option>');

            $('#insurance_item_code_select').select2({
                placeholder: 'Type to search tariff items...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: tariffSearchUrl,
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return { patient_category_id: categoryId, q: params.term, page: params.page || 1 };
                    },
                    processResults: function(data) {
                        return { results: data.results, pagination: data.pagination };
                    },
                    cache: true
                }
            }).on('select2:select', function(e) {
                var data = e.params.data;
                $('#insurance_item_code').val(data.id);
                if (data.unit_price !== undefined) {
                    $('#unit-price-display').show();
                    $('#unit-price-value').text('TSh ' + parseFloat(data.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2}));
                }
            }).on('select2:clear', function() {
                $('#insurance_item_code').val('');
                $('#unit-price-display').hide();
            });

        } else {
            $('#insurance_item_code_select').hide();
            $('#insurance_item_code_text').show();
            $('#tariff-hint').text('No tariff table configured for this category. Enter the code manually.');

            $('#insurance_item_code_text').off('input').on('input', function() {
                $('#insurance_item_code').val($(this).val());
            });
        }
    });

    // Trigger change on load if old value exists (after validation failure)
    @if(old('patient_category_id'))
        $('#patient_category_id').trigger('change');
    @endif
});
</script>
@endsection
