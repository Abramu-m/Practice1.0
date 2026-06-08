@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Medical Service Insurance Mapping</h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-service-insurance-map.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('medical-service-insurance-map.update', $medicalServiceInsuranceMap->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Medical Service</label>
                                    <div class="form-control bg-light">
                                        {{ $medicalServiceInsuranceMap->medicalService->name }}
                                        @if($medicalServiceInsuranceMap->medicalService->code)
                                            <span class="text-muted">({{ $medicalServiceInsuranceMap->medicalService->code }})</span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="medical_service_id" value="{{ $medicalServiceInsuranceMap->medical_service_id }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Patient Category</label>
                                    <div class="form-control bg-light">
                                        {{ $medicalServiceInsuranceMap->patientCategory->description }}
                                    </div>
                                    <input type="hidden" name="patient_category_id" value="{{ $medicalServiceInsuranceMap->patient_category_id }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="insurance_item_code">Insurance Item Code <span class="text-danger">*</span></label>

                                    <select class="form-control select2-tariff @error('insurance_item_code') is-invalid @enderror"
                                            id="insurance_item_code_select" style="width: 100%;">
                                        @if($currentTariffItem)
                                            <option value="{{ $medicalServiceInsuranceMap->insurance_item_code }}" selected>
                                                {{ $currentTariffItem->item_name }} [{{ $medicalServiceInsuranceMap->insurance_item_code }}]
                                            </option>
                                        @else
                                            <option value="{{ $medicalServiceInsuranceMap->insurance_item_code }}" selected>
                                                {{ $medicalServiceInsuranceMap->insurance_item_code }}
                                            </option>
                                        @endif
                                    </select>

                                    <input type="text" class="form-control @error('insurance_item_code') is-invalid @enderror"
                                           id="insurance_item_code_text"
                                           value="{{ old('insurance_item_code', $medicalServiceInsuranceMap->insurance_item_code) }}"
                                           maxlength="255" style="display: none;">

                                    <input type="hidden" name="insurance_item_code" id="insurance_item_code"
                                           value="{{ old('insurance_item_code', $medicalServiceInsuranceMap->insurance_item_code) }}">

                                    @error('insurance_item_code')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted" id="tariff-hint"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3" id="unit-price-display"
                                     @if(!$currentTariffItem) style="display: none;" @endif>
                                    <label>Insurance Tariff Price</label>
                                    <div class="form-control bg-light" id="unit-price-value">
                                        @if($currentTariffItem)
                                            TSh {{ number_format($currentTariffItem->unit_price, 2) }}
                                        @else
                                            —
                                        @endif
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
                            <i class="fas fa-save"></i> Update Mapping
                        </button>
                        <a href="{{ route('medical-service-insurance-map.index') }}" class="btn btn-secondary">
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
    var initialCategoryId = '{{ $medicalServiceInsuranceMap->patient_category_id }}';
    var hasTariffsInitial = '{{ $medicalServiceInsuranceMap->patientCategory->tariffs_table ? "1" : "0" }}';

    function initTariffSelect(categoryId, hasTariffs) {
        if (hasTariffs == '1') {
            $('#insurance_item_code_text').hide();
            $('#insurance_item_code_select').show();

            if ($('#insurance_item_code_select').hasClass('select2-hidden-accessible')) {
                $('#insurance_item_code_select').select2('destroy');
            }

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
            $('#insurance_item_code_text').off('input').on('input', function() {
                $('#insurance_item_code').val($(this).val());
            });
        }
    }

    // Init on page load for current category
    initTariffSelect(initialCategoryId, hasTariffsInitial);

});
</script>
@endsection
