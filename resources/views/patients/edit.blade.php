<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patients\edit.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Edit Patient' }}
 @endsection

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>Edit Patient: {{ $patient->full_name }}</h3>
        </div>
        <form action="{{ route('patients.update', $patient->id) }}" method="POST">
            @csrf
            @method('PUT')
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
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required maxlength="30">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $patient->middle_name) }}" maxlength="30">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth *</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $patient->date_of_birth->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact">Contact</label>
                            <input type="text" name="contact" class="form-control" value="{{ old('contact', $patient->contact) }}" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="residence">Residence</label>
                            <input type="text" name="residence" class="form-control" value="{{ old('residence', $patient->residence) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="occupation">Occupation</label>
                            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $patient->occupation) }}" maxlength="90">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nida">NIDA</label>
                            <input type="text" name="nida" class="form-control" value="{{ old('nida', $patient->nida) }}" maxlength="32">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_category">Patient Category *</label>
                            <select name="patient_category" id="patient_category" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}" {{ old('patient_category', $patient->patient_category) == $category->id ? 'selected' : '' }}>
                                        {{ $category->description }} ({{ ucfirst($category->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" name="card_number" class="form-control" value="{{ old('card_number', $patient->card_number) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <!-- Insurance-specific fields -->
                <div id="insurance-fields" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="membership_number">Membership Number</label>
                            <input type="text" name="membership_number" class="form-control" value="{{ old('membership_number', $patient->membership_number) }}" maxlength="30">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vote">Vote</label>
                            <input type="text" name="vote" class="form-control" value="{{ old('vote', $patient->vote) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="SchemeID">Scheme ID</label>
                            <input type="text" name="SchemeID" class="form-control" value="{{ old('SchemeID', $patient->SchemeID) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ProductCode">Product Code</label>
                            <input type="text" name="ProductCode" class="form-control" value="{{ old('ProductCode', $patient->ProductCode) }}" maxlength="30">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="PackageID">Package ID</label>
                            <input type="text" name="PackageID" class="form-control" value="{{ old('PackageID', $patient->PackageID) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="SchemeName">Scheme Name</label>
                            <input type="text" name="SchemeName" class="form-control" value="{{ old('SchemeName', $patient->SchemeName) }}" maxlength="90">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="HasSupplementary">Has Supplementary *</label>
                            <select name="HasSupplementary" class="form-control" required>
                                <option value="Yes" {{ old('HasSupplementary', $patient->HasSupplementary) == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('HasSupplementary', $patient->HasSupplementary) == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mtuha_new">Mtuha New *</label>
                            <select name="mtuha_new" class="form-control" required>
                                <option value="Yes" {{ old('mtuha_new', $patient->mtuha_new) == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('mtuha_new', $patient->mtuha_new) == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                </div>
                </div>
                <!-- End insurance-specific fields -->

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ old('status', $patient->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $patient->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Patient</button>
                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-secondary">Cancel</a>
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
            // Remove required attribute when hidden and clear values for cash patients
            const allFields = insuranceFields.querySelectorAll('input, select');
            allFields.forEach(field => {
                if (field.name !== 'HasSupplementary') {
                    field.removeAttribute('required');
                    // Only clear values if switching TO cash category, not when loading the page
                    if (patientCategorySelect.selectedIndex > 0) {
                        if (field.tagName === 'INPUT') {
                            field.value = '';
                        } else if (field.tagName === 'SELECT') {
                            field.selectedIndex = 0;
                        }
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