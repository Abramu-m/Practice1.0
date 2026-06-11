@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Edit Patient Visit' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Edit Patient Visit
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('patient_visits.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Visits
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $isWaiting = $patientVisit->visit_status == 0;
                        $isInTreatment = $patientVisit->visit_status == 1;
                        $isDischarged = $patientVisit->visit_status == 2;
                    @endphp
                    
                    @if(!empty($fromNhif))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>NHIF authorization successful.</strong> This visit has been created with the authorized details locked. Complete the <strong>doctor</strong>, <strong>visit type</strong> and <strong>amounts</strong> below, then save.
                        </div>
                    @endif

                    @if($isInTreatment)
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> This patient is currently in treatment. Only doctor and visit type (to Internal Referral) can be changed.
                        </div>
                    @elseif($isDischarged)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> This patient has been discharged. Only visit status and limited fields can be modified.
                        </div>
                    @endif
                    
                    <form action="{{ route('patient_visits.update', $patientVisit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patient">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient') is-invalid @enderror" id="patient" name="patient" required disabled>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" 
                                                {{ old('patient', $selectedPatient->id ?? $patientVisit->patient) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->full_name }} - {{ $patient->contact }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="patient" value="{{ $patientVisit->patient }}">
                                    @error('patient')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Patient cannot be changed once visit is created.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="visit_date">Visit Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('visit_date') is-invalid @enderror"
                                           id="visit_date" name="visit_date" value="{{ old('visit_date', $patientVisit->visit_date ? $patientVisit->visit_date->format('Y-m-d') : '') }}"
                                           required {{ (!$isWaiting || !empty($fromNhif)) ? 'readonly' : '' }}>
                                    @error('visit_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(!empty($fromNhif))
                                        <small class="text-muted">Visit date is set by the NHIF authorization.</small>
                                    @elseif(!$isWaiting)
                                        <small class="text-muted">Visit date cannot be changed once in treatment.</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="visit_category">Patient Category <span class="text-danger">*</span></label>
                                    @php $lockCategory = !$isWaiting || !empty($fromNhif); @endphp
                                    <select class="form-control @error('visit_category') is-invalid @enderror" id="visit_category" name="visit_category" required {{ $lockCategory ? 'disabled' : '' }}>
                                        <option value="">Select Category</option>
                                        @foreach($patientCategories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('visit_category', $selectedCategory->id ?? $patientVisit->visit_category) == $category->id ? 'selected' : '' }}>
                                                {{ $category->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($lockCategory)
                                        <input type="hidden" name="visit_category" value="{{ $patientVisit->visit_category }}">
                                    @endif
                                    @error('visit_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(!empty($fromNhif))
                                        <small class="text-muted">Category is set by the NHIF authorization.</small>
                                    @elseif(!$isWaiting)
                                        <small class="text-muted">Category cannot be changed once in treatment.</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="visit_type">Visit Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('visit_type') is-invalid @enderror" id="visit_type" name="visit_type" required>
                                        @if($isWaiting)
                                            <option value="">Select Visit Type</option>
                                            @foreach($visitTypes as $visitType)
                                                <option value="{{ $visitType->id }}" 
                                                    {{ old('visit_type', $selectedVisitType->id ?? $patientVisit->visit_type) == $visitType->id ? 'selected' : '' }}>
                                                    {{ $visitType->description }}
                                                </option>
                                            @endforeach
                                        @elseif($isInTreatment)
                                            <!-- Keep current selection -->
                                            @foreach($visitTypes as $visitType)
                                                @if($visitType->id == $patientVisit->visit_type)
                                                    <option value="{{ $visitType->id }}" selected>{{ $visitType->description }}</option>
                                                @endif
                                            @endforeach
                                            <!-- Allow change to Internal Referral -->
                                            @foreach($visitTypes as $visitType)
                                                @if($visitType->id == 7 && $visitType->id != $patientVisit->visit_type)
                                                    <option value="{{ $visitType->id }}">{{ $visitType->description }}</option>
                                                @endif
                                            @endforeach
                                        @else
                                            <!-- Discharged - cannot change -->
                                            @foreach($visitTypes as $visitType)
                                                @if($visitType->id == $patientVisit->visit_type)
                                                    <option value="{{ $visitType->id }}" selected>{{ $visitType->description }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('visit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($isInTreatment)
                                        <small class="text-muted">Can only change to Internal Referral when in treatment.</small>
                                    @elseif($isDischarged)
                                        <small class="text-muted">Visit type cannot be changed once discharged.</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="doctor">Doctor</label>
                                    <select class="form-control @error('doctor') is-invalid @enderror" id="doctor" name="doctor" {{ $isDischarged ? 'disabled' : '' }}>
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->doctor_id }}" 
                                                {{ old('doctor', $selectedDoctor->doctor_id ?? $patientVisit->doctor) == $doctor->doctor_id ? 'selected' : '' }}>
                                                {{ $doctor->user->name ?? 'Unknown' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($isDischarged)
                                        <input type="hidden" name="doctor" value="{{ $patientVisit->doctor }}">
                                    @endif
                                    @error('doctor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($isDischarged)
                                        <small class="text-muted">Doctor cannot be changed once discharged.</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Consultation Fee</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Tsh</span>
                                        </div>
                                        <input type="text" class="form-control" id="consultation_fee_display" readonly
                                               placeholder="{{ $isWaiting ? 'Fee will be calculated automatically' : 'Fees locked during treatment' }}"
                                               {{ !$isWaiting ? 'disabled' : '' }}>
                                    </div>
                                    <small class="text-muted">
                                        @if($isWaiting)
                                            Calculated based on doctor, patient category, and visit type.
                                        @else
                                            Consultation fees cannot be changed once in treatment.
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="amount_cash">Cash Amount <span class="text-danger">*</span></label>
                                    <input type="text" step="0.01" min="0" class="form-control @error('amount_cash') is-invalid @enderror" 
                                           id="amount_cash" name="amount_cash" value="{{ old('amount_cash', $patientVisit->amount_cash) }}" 
                                           required {{ !$isWaiting ? 'readonly' : '' }}>
                                    @error('amount_cash')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(!$isWaiting)
                                        <small class="text-muted">Amount cannot be changed once in treatment.</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="amount_covered">Covered Amount</label>
                                    <input type="text" step="0.01" min="0" class="form-control @error('amount_covered') is-invalid @enderror" 
                                           id="amount_covered" name="amount_covered" value="{{ old('amount_covered', $patientVisit->amount_covered) }}" 
                                           {{ !$isWaiting ? 'readonly' : '' }}>
                                    @error('amount_covered')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(!$isWaiting)
                                        <small class="text-muted">Amount cannot be changed once in treatment.</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="balance">Total Balance</label>
                                    <input type="text" step="0.01" class="form-control bg-light" id="balance" readonly
                                           value="{{ ($patientVisit->amount_cash ?? 0) + ($patientVisit->amount_covered ?? 0) }}">
                                    <small class="text-muted">Automatically calculated sum of cash and covered amounts.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sic_no">SIC Number</label>
                                    <input type="text" maxlength="30" class="form-control @error('sic_no') is-invalid @enderror" 
                                           id="sic_no" name="sic_no" value="{{ old('sic_no', $patientVisit->sic_no) }}" 
                                           {{ !$isWaiting ? 'readonly' : '' }}>
                                    @error('sic_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="authorization_no">Authorization Number</label>
                                    <input type="text" maxlength="30" class="form-control @error('authorization_no') is-invalid @enderror"
                                           id="authorization_no" name="authorization_no" value="{{ old('authorization_no', $patientVisit->authorization_no) }}"
                                           {{ (!$isWaiting || !empty($fromNhif)) ? 'readonly' : '' }}>
                                    @error('authorization_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(!empty($fromNhif))
                                        <small class="text-muted">Authorization number is set by the NHIF authorization.</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nhif_reference_no">NHIF Reference Number</label>
                                    <input type="text" maxlength="30" class="form-control @error('nhif_reference_no') is-invalid @enderror" 
                                           id="nhif_reference_no" name="nhif_reference_no" value="{{ old('nhif_reference_no', $patientVisit->nhif_reference_no) }}" 
                                           {{ !$isWaiting ? 'readonly' : '' }}>
                                    @error('nhif_reference_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="folio_item_id">Folio Item ID</label>
                                    <input type="text" maxlength="32" class="form-control @error('folio_item_id') is-invalid @enderror" 
                                           id="folio_item_id" name="folio_item_id" value="{{ old('folio_item_id', $patientVisit->folio_item_id) }}" 
                                           {{ !$isWaiting ? 'readonly' : '' }}>
                                    @error('folio_item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="post_status">Post Status</label>
                                    <select class="form-control @error('post_status') is-invalid @enderror" id="post_status" name="post_status">
                                        <option value="0" {{ old('post_status', $patientVisit->post_status) == 0 ? 'selected' : '' }}>Not Posted</option>
                                        <option value="1" {{ old('post_status', $patientVisit->post_status) == 1 ? 'selected' : '' }}>Posted</option>
                                    </select>
                                    @error('post_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Patient Visit
                            </button>
                            @if($isInTreatment && auth()->user()->role === 'doctor')
                                <a href="{{ route('consultations.show', $patientVisit->id) }}" class="btn btn-success">
                                    <i class="fas fa-user-md"></i> Start Consultation
                                </a>
                            @endif
                            <a href="{{ route('patient_visits.index') }}" class="btn btn-secondary">
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
// Calculate balance automatically and handle consultation fee lookup
document.addEventListener('DOMContentLoaded', function() {
    const cashAmount = document.getElementById('amount_cash');
    const coveredAmount = document.getElementById('amount_covered');
    const balance = document.getElementById('balance');
    const doctorSelect = document.getElementById('doctor');
    const visitCategorySelect = document.getElementById('visit_category');
    const visitTypeSelect = document.getElementById('visit_type');
    const consultationFeeDisplay = document.getElementById('consultation_fee_display');

    // Check if we're in waiting status (fees can be changed)
    const isWaiting = {{ $isWaiting ? 'true' : 'false' }};

    function calculateBalance() {
        const cash = parseFloat(cashAmount.value) || 0;
        const covered = parseFloat(coveredAmount.value) || 0;
        const total = cash + covered;
        balance.value = total.toFixed(2);
    }
    
    function lookupConsultationFee() {
        if (!isWaiting) {
            return; // Don't lookup fees if not in waiting status
        }
        
        const doctorId = doctorSelect.value;
        const categoryId = visitCategorySelect.value;
        const visitTypeId = visitTypeSelect.value;
        
        if (doctorId && categoryId && visitTypeId) {
            // Show loading state
            consultationFeeDisplay.value = 'Loading...';
            consultationFeeDisplay.className = 'form-control';

            // Make AJAX request to get fee
            fetch('{{ route("consultation_fees.get_fee") }}?' + new URLSearchParams({
                doctor_id: doctorId,
                patient_category_id: categoryId,
                visit_type_id: visitTypeId,
                patient_id: '{{ $patientVisit->patient }}'
            }))
            .then(response => response.json())
            .then(data => {
                if (data.cash_amount || data.covered_amount) {
                    const cash = parseFloat(data.cash_amount) || 0;
                    const covered = parseFloat(data.covered_amount) || 0;
                    const fee = cash + covered;

                    consultationFeeDisplay.value = fee.toFixed(2) + (data.description ? ' - ' + data.description : '');
                    consultationFeeDisplay.className = 'form-control text-success fw-bold';

                    cashAmount.value = cash.toFixed(2);
                    coveredAmount.value = covered.toFixed(2);
                    calculateBalance();
                } else {
                    consultationFeeDisplay.value = data.reason || 'No fee structure found';
                    consultationFeeDisplay.className = 'form-control text-warning';
                }
            })
            .catch(error => {
                console.error('Error fetching fee:', error);
                consultationFeeDisplay.value = 'Error loading fee';
                consultationFeeDisplay.className = 'form-control text-danger';
            });
        } else {
            // Clear fee display if required fields are not selected
            consultationFeeDisplay.value = '';
            consultationFeeDisplay.className = 'form-control';
        }
    }

    // Event listeners
    if (cashAmount && coveredAmount && balance) {
        cashAmount.addEventListener('input', calculateBalance);
        coveredAmount.addEventListener('input', calculateBalance);
    }
    
    if (isWaiting && doctorSelect && visitCategorySelect && visitTypeSelect) {
        doctorSelect.addEventListener('change', lookupConsultationFee);
        visitCategorySelect.addEventListener('change', lookupConsultationFee);
        visitTypeSelect.addEventListener('change', lookupConsultationFee);
    }

    // Calculate balance on page load
    if (cashAmount && coveredAmount && balance) {
        calculateBalance();
    }
    
    // Check for pre-selected values and lookup fee on page load if in waiting status
    if (isWaiting && doctorSelect && visitCategorySelect && visitTypeSelect) {
        if (doctorSelect.value && visitCategorySelect.value && visitTypeSelect.value) {
            lookupConsultationFee();
        }
    }
});
</script>
@endsection