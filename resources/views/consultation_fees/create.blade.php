@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Create Consultation Fee' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Create New Consultation Fee
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('consultation_fees.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('consultation_fees.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Doctor Selection -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="doctor_id">Doctor <span class="text-danger">*</span></label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->doctor_id }}" {{ old('doctor_id') == $doctor->doctor_id ? 'selected' : '' }}>
                                                {{ $doctor->user->name ?? 'Unknown' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Patient Category Selection -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="patient_category_id">Patient Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient_category_id') is-invalid @enderror" id="patient_category_id" name="patient_category_id" required>
                                        <option value="">Select Patient Category</option>
                                        @foreach($patientCategories as $category)
                                            <option value="{{ $category->id }}" {{ old('patient_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Visit Type Selection -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="visit_type_id">Visit Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('visit_type_id') is-invalid @enderror" id="visit_type_id" name="visit_type_id" required>
                                        <option value="">Select Visit Type</option>
                                        @foreach($visitTypes as $visitType)
                                            <option value="{{ $visitType->id }}" {{ old('visit_type_id') == $visitType->id ? 'selected' : '' }}>
                                                {{ $visitType->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('visit_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Cash Amount -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cash_amount">Cash Amount (Tsh) <span class="text-danger">*</span></label>
                                    <input type="text" step="0.01" min="0" max="999999.99" 
                                           class="form-control @error('cash_amount') is-invalid @enderror" 
                                           id="cash_amount" name="cash_amount" 
                                           value="{{ old('cash_amount') }}" 
                                           placeholder="Enter cash amount" required>
                                    @error('cash_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Covered Amount -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="covered_amount">Covered Amount (Tsh)</label>
                                    <input type="text" step="0.01" min="0" max="999999.99" 
                                           class="form-control @error('covered_amount') is-invalid @enderror" 
                                           id="covered_amount" name="covered_amount" 
                                           value="{{ old('covered_amount') }}" 
                                           placeholder="Enter covered amount" required>
                                    @error('covered_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Optional description for this fee structure">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mb-3 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Consultation Fee
                            </button>
                            <a href="{{ route('consultation_fees.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
<script>
$(document).ready(function() {
    // Check for existing fee combination when selections change
    $('#doctor_id, #patient_category_id, #visit_type_id').change(function() {
        var doctorId = $('#doctor_id').val();
        var categoryId = $('#patient_category_id').val();
        var visitTypeId = $('#visit_type_id').val();
        
        if (doctorId && categoryId && visitTypeId) {
            $.get('{{ route("consultation_fees.get_fee") }}', {
                doctor_id: doctorId,
                patient_category_id: categoryId,
                visit_type_id: visitTypeId
            })
            .done(function(data) {
                if (data.cash_amount !== null) {
                    alert('Warning: A fee structure already exists for this combination. Cash Amount: $' + data.cash_amount);
                    $('#cash_amount').val(data.cash_amount);
                    $('#description').val(data.description || '');
                }
            });
        }
    });
});
</script>
@endsection
