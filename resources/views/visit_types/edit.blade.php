@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Edit Visit Type' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Visit Type</div>
                <div class="card-body">
                    <form action="{{ route('visit_types.update', $visitType) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="description">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror"
                                   id="description" name="description"
                                   value="{{ old('description', $visitType->description) }}" required>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nhif_visit_type_code">NHIF Visit Type Code</label>
                            <input type="number" min="1" class="form-control @error('nhif_visit_type_code') is-invalid @enderror"
                                   id="nhif_visit_type_code" name="nhif_visit_type_code" value="{{ old('nhif_visit_type_code', $visitType->nhif_visit_type_code) }}">
                            <small class="form-text text-muted">
                                Maps this visit type to NHIF's AuthorizeCard VisitTypeID (1=Normal, 2=Emergency, 3=Referral, 4=Follow up, 5=Revisit within same week).
                                Leave empty if this visit type should not appear in the NHIF authorization dropdown.
                            </small>
                            @error('nhif_visit_type_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="patient_categories">Allowed Patient Categories</label>
                            @php
                                $selectedCategories = collect(old('patient_categories', $visitType->patientCategories->pluck('id')->all()))
                                    ->map(fn($id) => (int) $id)
                                    ->all();
                            @endphp
                            <select class="form-select select2-patient-categories @error('patient_categories') is-invalid @enderror"
                                id="patient_categories" name="patient_categories[]" multiple style="width: 100%;">
                                @foreach($patientCategories as $category)
                                    <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                                        {{ $category->description }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave empty to allow this visit type for all patient categories.</small>
                            @error('patient_categories')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Update Visit Type</button>
                            <a href="{{ route('visit_types.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2-patient-categories').select2({
        theme: 'default',
        placeholder: 'Select patient categories...',
        closeOnSelect: false,
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection

@section('extra_footer_content')
@endsection