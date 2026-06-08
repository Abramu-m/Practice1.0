<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patients\create.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Add Patient' }}
 @endsection

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>Add New Patient</h3>
        </div>
        <form action="{{ route('patients.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name">First Name *</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', request('prefill.first_name')) }}" required maxlength="30">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', request('prefill.middle_name')) }}" maxlength="30">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', request('prefill.last_name')) }}" required maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date_of_birth">Date of Birth *</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', request('prefill.date_of_birth')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gender">Gender *</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', request('prefill.gender')) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', request('prefill.gender')) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', request('prefill.gender')) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contact">Contact</label>
                            <input type="text" name="contact" class="form-control" value="{{ old('contact', request('prefill.contact')) }}" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="residence">Residence</label>
                            <input type="text" name="residence" class="form-control" value="{{ old('residence') }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="occupation">Occupation</label>
                            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', request('prefill.occupation')) }}" maxlength="90">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nida">NIDA</label>
                            <input type="text" name="nida" class="form-control" value="{{ old('nida', request('prefill.nida')) }}" maxlength="32">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" maxlength="150" placeholder="patient@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="patient_category">Patient Category *</label>
                            <select name="patient_category" id="patient_category" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-type="{{ $category->type }}" {{ old('patient_category', request('prefill.patient_category')) == $category->id ? 'selected' : '' }}>
                                        {{ $category->description }} ({{ ucfirst($category->type) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="card_number">Card Number</label>
                            <input type="text" name="card_number" class="form-control" value="{{ old('card_number', request('prefill.card_number')) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <!-- Insurance-specific fields -->
                <div id="insurance-fields" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="membership_number">Membership Number</label>
                            <input type="text" name="membership_number" class="form-control" value="{{ old('membership_number', request('prefill.membership_number')) }}" maxlength="30">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vote">Vote</label>
                            <input type="text" name="vote" class="form-control" value="{{ old('vote', request('prefill.vote')) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="SchemeID">Scheme ID</label>
                            <input type="text" name="SchemeID" class="form-control" value="{{ old('SchemeID', request('prefill.SchemeID')) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ProductCode">Product Code</label>
                            <input type="text" name="ProductCode" class="form-control" value="{{ old('ProductCode', request('prefill.ProductCode')) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="PackageID">Package ID</label>
                            <input type="text" name="PackageID" class="form-control" value="{{ old('PackageID', request('prefill.PackageID')) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="SchemeName">Scheme Name</label>
                            <input type="text" name="SchemeName" class="form-control" value="{{ old('SchemeName', request('prefill.SchemeName')) }}" maxlength="90">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="HasSupplementary">Has Supplementary *</label>
                            <select name="HasSupplementary" class="form-control" required>
                                <option value="Yes" {{ old('HasSupplementary') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('HasSupplementary', 'No') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="mtuha_new">Mtuha New *</label>
                            <select name="mtuha_new" class="form-control" required>
                                <option value="Yes" {{ old('mtuha_new', 'Yes') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('mtuha_new') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                </div>
                </div>
                <!-- End insurance-specific fields -->

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status">Status *</label>
                            <select name="status" class="form-control" required readonly>
                                <option value="active" selected>Active</option>
                            </select>
                            <small class="form-text text-muted">New patients are automatically Active</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Create Patient</button>
                <a href="{{ route('patients.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('extra_footer_content')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const patientCategorySelect = document.getElementById('patient_category');
    const insuranceFields = document.getElementById('insurance-fields');
    
    function toggleInsuranceFields() {
        const selectedOption = patientCategorySelect.options[patientCategorySelect.selectedIndex];
        const categoryType = selectedOption.getAttribute('data-type');
        
        if (categoryType === 'insurance') {
            insuranceFields.style.display = 'block';
            // Make insurance fields required when visible
            const requireableFields = insuranceFields.querySelectorAll('select[name="HasSupplementary"]');
            requireableFields.forEach(field => field.setAttribute('required', 'required'));
        } else {
            insuranceFields.style.display = 'none';
            // Remove required attribute when hidden and clear values
            const allFields = insuranceFields.querySelectorAll('input, select');
            allFields.forEach(field => {
                if (field.name !== 'HasSupplementary') {
                    field.removeAttribute('required');
                    if (field.tagName === 'INPUT') {
                        field.value = '';
                    } else if (field.tagName === 'SELECT') {
                        field.selectedIndex = 0;
                    }
                }
            });
        }
    }
    
    // Check on page load (for old values)
    toggleInsuranceFields();
    
    // Check when category changes
    patientCategorySelect.addEventListener('change', toggleInsuranceFields);
});
</script>
@endsection