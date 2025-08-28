@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Medical Service Pricing</h3>
                    <div class="card-tools">
                        <a href="{{ route('medical-service-pricing.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('medical-service-pricing.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="medical_service_id">Medical Service <span class="text-danger">*</span></label>
                                    <select class="form-control @error('medical_service_id') is-invalid @enderror" 
                                            id="medical_service_id" name="medical_service_id" required>
                                        <option value="">Select Medical Service</option>
                                        @foreach($medicalServices as $service)
                                            <option value="{{ $service->id }}" {{ old('medical_service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                                @if($service->serviceCategory)
                                                    ({{ $service->serviceCategory->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medical_service_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
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
                                <div class="form-group">
                                    <label for="selling_price">Selling Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">TSh</span>
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
                                <div class="form-group">
                                    <label for="markup_percentage">Markup %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('markup_percentage') is-invalid @enderror" 
                                               id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage') }}" 
                                               step="0.01" min="0" max="1000">
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
                                <div class="form-group">
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
                                <div class="form-group">
                                    <label for="effective_from">Effective From <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('effective_from') is-invalid @enderror" 
                                           id="effective_from" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" required>
                                    @error('effective_from')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_to">Effective To</label>
                                    <input type="date" class="form-control @error('effective_to') is-invalid @enderror" 
                                           id="effective_to" name="effective_to" value="{{ old('effective_to') }}">
                                    <small class="form-text text-muted">Leave blank for indefinite</small>
                                    @error('effective_to')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                                    <small class="form-text text-muted">Optional notes about this pricing</small>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Pricing
                        </button>
                        <a href="{{ route('medical-service-pricing.index') }}" class="btn btn-secondary">
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
    // Validate effective dates
    $('#effective_from, #effective_to').change(function() {
        const fromDate = $('#effective_from').val();
        const toDate = $('#effective_to').val();
        
        if (fromDate && toDate && toDate <= fromDate) {
            $('#effective_to').val('');
            alert('Effective To date must be after Effective From date');
        }
    });

    // Price formatting
    $('#selling_price').on('input', function() {
        let value = $(this).val().replace(/[^\d.]/g, '');
        if (value.includes('.')) {
            const parts = value.split('.');
            if (parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
        }
        $(this).val(value);
    });
});
</script>
@endsection
