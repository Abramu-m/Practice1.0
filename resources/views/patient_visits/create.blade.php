@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Create Patient Visit' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Create New Patient Visit
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('patient_visits.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Visits
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('patient_visits.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control select2-patient @error('patient') is-invalid @enderror" id="patient" name="patient" required>
                                        <option value="">Search and select patient...</option>
                                        @if(isset($selectedPatient))
                                            <option value="{{ $selectedPatient->id }}" selected>
                                                {{ $selectedPatient->full_name }} - {{ $selectedPatient->contact }}
                                            </option>
                                        @endif
                                    </select>
                                    @error('patient')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="visit_date">Visit Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('visit_date') is-invalid @enderror" 
                                           id="visit_date" name="visit_date" value="{{ old('visit_date', date('Y-m-d')) }}" required>
                                    @error('visit_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="visit_category">Visit Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('visit_category') is-invalid @enderror" id="visit_category" name="visit_category" required>
                                        <option value="">Select Category</option>
                                        @foreach($patientCategories as $category)
                                            <option value="{{ $category->id }}" {{ old('visit_category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('visit_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="visit_type">Visit Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('visit_type') is-invalid @enderror" id="visit_type" name="visit_type" required>
                                        <option value="">Select Visit Type</option>
                                        @foreach($visitTypes as $visitType)
                                            <option value="{{ $visitType->id }}" {{ old('visit_type') == $visitType->id ? 'selected' : '' }}>
                                                {{ $visitType->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('visit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="doctor">Attending Doctor</label>
                                    <select class="form-control @error('doctor') is-invalid @enderror" id="doctor" name="doctor">
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->doctor_id }}" 
                                                {{ (old('doctor') == $doctor->doctor_id || (isset($selectedDoctor) && $selectedDoctor->doctor_id == $doctor->doctor_id)) ? 'selected' : '' }}>
                                                {{ $doctor->user->name ?? 'N/A' }} - {{ $doctor->specialization }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Consultation Fee</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="text" class="form-control" id="consultation_fee_display" readonly placeholder="Fee will be calculated automatically">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="apply_fee_btn" disabled>
                                                <i class="fas fa-check"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Calculated based on doctor, patient category, and visit type.</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="amount_cash">Cash Amount <span class="text-danger">*</span></label>
                                    <input type="text" step="0.01" min="0" class="form-control @error('amount_cash') is-invalid @enderror" 
                                           id="amount_cash" name="amount_cash" value="{{ old('amount_cash', 0.00) }}" required>
                                    @error('amount_cash')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="amount_covered">Covered Amount</label>
                                    <input type="text" step="0.01" min="0" class="form-control @error('amount_covered') is-invalid @enderror" 
                                           id="amount_covered" name="amount_covered" value="{{ old('amount_covered', 0.00) }}">
                                    @error('amount_covered')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="sic_no">SIC Number</label>
                                    <input type="text" maxlength="30" class="form-control @error('sic_no') is-invalid @enderror" 
                                           id="sic_no" name="sic_no" value="{{ old('sic_no') }}">
                                    @error('sic_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="authorization_no">Authorization Number</label>
                                    <input type="text" maxlength="30" class="form-control @error('authorization_no') is-invalid @enderror" 
                                           id="authorization_no" name="authorization_no" value="{{ old('authorization_no') }}">
                                    @error('authorization_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="nhif_reference_no">NHIF Reference Number</label>
                                    <input type="text" maxlength="30" class="form-control @error('nhif_reference_no') is-invalid @enderror" 
                                           id="nhif_reference_no" name="nhif_reference_no" value="{{ old('nhif_reference_no') }}">
                                    @error('nhif_reference_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Patient Visit
                                    </button>
                                    <a href="{{ route('patient_visits.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    @if(isset($selectedPatient))
                                        <a href="{{ route('patients.show', $selectedPatient->id) }}" class="btn btn-info">
                                            <i class="fas fa-user"></i> View Patient Details
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Select2 assets are loaded globally in the layout; only keep initialization below -->

<script>
// Initialize Select2 for patient dropdown with AJAX
$(document).ready(function() {
    $('.select2-patient').select2({
        placeholder: 'Type to search for patient...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 2,
        ajax: {
            url: '{{ route('patients.search') }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        language: {
            inputTooShort: function() {
                return 'Type 2 or more characters to search';
            },
            searching: function() {
                return 'Searching patients...';
            },
            noResults: function() {
                return 'No patients found';
            }
        }
    });

    // Handle validation state - make select2 work with Bootstrap validation
    $('.select2-patient').on('select2:close', function() {
        if ($(this).hasClass('is-invalid')) {
            $(this).next('.select2-container').addClass('is-invalid');
        } else {
            $(this).next('.select2-container').removeClass('is-invalid');
        }
    });
});

// Calculate balance automatically and handle consultation fee lookup
document.addEventListener('DOMContentLoaded', function() {
    const cashAmount = document.getElementById('amount_cash');
    const coveredAmount = document.getElementById('amount_covered');
    const balance = document.getElementById('balance');
    const doctorSelect = document.getElementById('doctor');
    const visitCategorySelect = document.getElementById('visit_category');
    const visitTypeSelect = document.getElementById('visit_type');
    const consultationFeeDisplay = document.getElementById('consultation_fee_display');
    const applyFeeBtn = document.getElementById('apply_fee_btn');
    const doctorFormGroup = doctorSelect.closest('.form-group');
    const consultationFeeFormGroup = consultationFeeDisplay.closest('.form-group');
    
    let currentFee = null;
    let currentFeeDescription = null;
    
    function calculateBalance() {
        const cash = parseFloat(cashAmount.value) || 0;
        const covered = parseFloat(coveredAmount.value) || 0;
        const total = cash + covered;
        if (balance) {
            balance.value = total.toFixed(2);
        }
    }
    
    function checkVisitType() {
        const selectedVisitType = visitTypeSelect.options[visitTypeSelect.selectedIndex];
        const visitTypeText = selectedVisitType ? selectedVisitType.text.toLowerCase() : '';
        
        if (visitTypeText.includes('lab only')) {
            // Hide doctor selection and consultation fee for Lab Only visits
            doctorFormGroup.style.display = 'none';
            consultationFeeFormGroup.style.display = 'none';
            
            // Clear doctor selection and remove required attribute
            doctorSelect.value = '';
            doctorSelect.removeAttribute('required');
            
            // Clear consultation fee display
            consultationFeeDisplay.value = 'Not applicable for Lab Only visits';
            consultationFeeDisplay.className = 'form-control text-muted';
            applyFeeBtn.disabled = true;
            applyFeeBtn.className = 'btn btn-outline-secondary';
            applyFeeBtn.innerHTML = '<i class="fas fa-times"></i> N/A';
            
            // Restrict to cash payments only for Lab Only visits
            coveredAmount.value = '0.00';
            coveredAmount.disabled = true;
            coveredAmount.closest('.form-group').querySelector('label').innerHTML = 'Covered Amount <small class="text-muted">(Not applicable for Lab Only)</small>';
            
            // Update cash amount label to indicate cash only
            cashAmount.closest('.form-group').querySelector('label').innerHTML = 'Cash Amount <span class="text-danger">*</span> <small class="text-success">(Cash Only for Lab Visits)</small>';
            
            currentFee = null;
            currentFeeDescription = null;
        } else {
            // Show doctor selection and consultation fee for other visit types
            doctorFormGroup.style.display = 'block';
            consultationFeeFormGroup.style.display = 'block';
            
            // Re-enable covered amount for non-lab visits
            coveredAmount.disabled = false;
            coveredAmount.closest('.form-group').querySelector('label').innerHTML = 'Covered Amount';
            cashAmount.closest('.form-group').querySelector('label').innerHTML = 'Cash Amount <span class="text-danger">*</span>';
            
            // Reset consultation fee display
            consultationFeeDisplay.value = '';
            consultationFeeDisplay.className = 'form-control';
            applyFeeBtn.disabled = true;
            applyFeeBtn.className = 'btn btn-outline-secondary';
            applyFeeBtn.innerHTML = '<i class="fas fa-check"></i> Apply';
            
            // Lookup consultation fee if all required fields are selected
            lookupConsultationFee();
        }
    }
    
    function validateLabOnlyPayment() {
        const selectedVisitType = visitTypeSelect.options[visitTypeSelect.selectedIndex];
        const visitTypeText = selectedVisitType ? selectedVisitType.text.toLowerCase() : '';
        
        if (visitTypeText.includes('lab only')) {
            const coveredValue = parseFloat(coveredAmount.value) || 0;
            if (coveredValue > 0) {
                // Show warning and reset covered amount
                coveredAmount.value = '0.00';
                
                // Show flash message
                const warningMsg = document.createElement('div');
                warningMsg.className = 'alert alert-warning alert-dismissible mt-2';
                warningMsg.innerHTML = `
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> Lab Only visits are cash payments only. Covered amount has been reset.
                `;
                coveredAmount.closest('.form-group').appendChild(warningMsg);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (warningMsg.parentNode) {
                        warningMsg.remove();
                    }
                }, 5000);
                
                calculateBalance();
            }
        }
    }
    
    function lookupConsultationFee() {
        // Don't lookup fee for Lab Only visits
        const selectedVisitType = visitTypeSelect.options[visitTypeSelect.selectedIndex];
        const visitTypeText = selectedVisitType ? selectedVisitType.text.toLowerCase() : '';
        
        if (visitTypeText.includes('lab only')) {
            return;
        }
        
        const doctorId = doctorSelect.value;
        const categoryId = visitCategorySelect.value;
        const visitTypeId = visitTypeSelect.value;
        
        if (doctorId && categoryId && visitTypeId) {
            // Show loading state
            consultationFeeDisplay.value = 'Loading...';
            applyFeeBtn.disabled = true;
            
            // Make AJAX request to get fee
            fetch('{{ route("consultation_fees.get_fee") }}?' + new URLSearchParams({
                doctor_id: doctorId,
                patient_category_id: categoryId,
                visit_type_id: visitTypeId
            }))
            .then(response => response.json())
            .then(data => {
                if (data.fee !== null) {
                    currentFee = parseFloat(data.fee);
                    currentFeeDescription = data.description;
                    consultationFeeDisplay.value = currentFee.toFixed(2);
                    consultationFeeDisplay.className = 'form-control text-success font-weight-bold';
                    applyFeeBtn.disabled = false;
                    applyFeeBtn.className = 'btn btn-success';
                    applyFeeBtn.innerHTML = '<i class="fas fa-check"></i> Apply $' + currentFee.toFixed(2);
                } else {
                    currentFee = null;
                    currentFeeDescription = null;
                    consultationFeeDisplay.value = 'No fee structure found';
                    consultationFeeDisplay.className = 'form-control text-warning';
                    applyFeeBtn.disabled = true;
                    applyFeeBtn.className = 'btn btn-outline-secondary';
                    applyFeeBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> No Fee';
                }
            })
            .catch(error => {
                console.error('Error fetching fee:', error);
                consultationFeeDisplay.value = 'Error loading fee';
                consultationFeeDisplay.className = 'form-control text-danger';
                applyFeeBtn.disabled = true;
            });
        } else {
            // Clear fee display if required fields are not selected
            consultationFeeDisplay.value = '';
            consultationFeeDisplay.className = 'form-control';
            applyFeeBtn.disabled = true;
            applyFeeBtn.className = 'btn btn-outline-secondary';
            applyFeeBtn.innerHTML = '<i class="fas fa-check"></i> Apply';
            currentFee = null;
            currentFeeDescription = null;
        }
    }
    
    function applyConsultationFee() {
        if (currentFee !== null) {
            // Check if patient category suggests cash or covered payment
            const categoryText = visitCategorySelect.options[visitCategorySelect.selectedIndex].text.toLowerCase();
            
            if (categoryText.includes('nhif') || categoryText.includes('insurance') || categoryText.includes('covered')) {
                // For insured patients, apply to covered amount
                coveredAmount.value = currentFee.toFixed(2);
                // Clear cash amount if not already set
                if (parseFloat(cashAmount.value) === 0) {
                    cashAmount.value = '0.00';
                }
            } else {
                // For cash patients, apply to cash amount
                cashAmount.value = currentFee.toFixed(2);
                // Clear covered amount if not already set
                if (parseFloat(coveredAmount.value) === 0) {
                    coveredAmount.value = '0.00';
                }
            }
            
            // Recalculate balance
            calculateBalance();
            
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'alert alert-success alert-dismissible mt-2';
            successMsg.innerHTML = `
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check"></i> Applied consultation fee: $${currentFee.toFixed(2)}
                ${currentFeeDescription ? '<br><small>' + currentFeeDescription + '</small>' : ''}
            `;
            applyFeeBtn.parentNode.parentNode.appendChild(successMsg);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                if (successMsg.parentNode) {
                    successMsg.remove();
                }
            }, 3000);
        }
    }
    
    // Event listeners
    cashAmount.addEventListener('input', calculateBalance);
    coveredAmount.addEventListener('input', function() {
        validateLabOnlyPayment();
        calculateBalance();
    });
    doctorSelect.addEventListener('change', lookupConsultationFee);
    visitCategorySelect.addEventListener('change', lookupConsultationFee);
    visitTypeSelect.addEventListener('change', function() {
        checkVisitType();
        lookupConsultationFee();
    });
    applyFeeBtn.addEventListener('click', applyConsultationFee);
    
    // Calculate balance on page load
    calculateBalance();
    
    // Check visit type on page load
    checkVisitType();
    
    // Check for pre-selected values and lookup fee
    if (doctorSelect.value && visitCategorySelect.value && visitTypeSelect.value) {
        lookupConsultationFee();
    }
});
</script>
@endsection