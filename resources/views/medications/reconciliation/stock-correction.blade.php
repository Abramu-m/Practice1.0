@extends('layouts.app_main_layout')

@section('page_title', 'Manual Stock Correction')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-wrench text-warning me-2"></i>
                        Manual Stock Correction
                    </h1>
                    <p class="text-muted mb-0">Correct stock discrepancies and update inventory records</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    <a href="{{ route('medications.reconciliation.audit-trail') }}" class="btn btn-outline-info">
                        <i class="fas fa-history me-2"></i>
                        View Audit Trail
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stock Correction Form --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Correction Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('medications.reconciliation.process-correction') }}" id="correctionForm">
                        @csrf
                        
                        {{-- Step 1: Select Medication and Location --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="medication_id" class="form-label">
                                    <i class="fas fa-pills me-1"></i>
                                    Medication <span class="text-danger">*</span>
                                </label>
                                <select name="medication_id" id="medication_id" class="form-select" required>
                                    <option value="">Select medication...</option>
                                    @foreach($medications as $medication)
                                        <option value="{{ $medication->id }}" 
                                            {{ old('medication_id') == $medication->id ? 'selected' : '' }}>
                                            {{ $medication->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="location_id" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Location <span class="text-danger">*</span>
                                </label>
                                <select name="location_id" id="location_id" class="form-select" required>
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Current Stock Display --}}
                        <div id="currentStockInfo" class="alert alert-info" style="display: none;">
                            <h6><i class="fas fa-info-circle me-2"></i>Current Stock Information</h6>
                            <div id="stockDetails"></div>
                        </div>

                        {{-- Step 2: Correction Type --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="correction_type" class="form-label">
                                    <i class="fas fa-cogs me-1"></i>
                                    Correction Type <span class="text-danger">*</span>
                                </label>
                                <select name="correction_type" id="correction_type" class="form-select" required>
                                    <option value="">Select correction type...</option>
                                    <option value="ledger" {{ old('correction_type') == 'ledger' ? 'selected' : '' }}>
                                        Ledger Correction
                                    </option>
                                    <option value="location_stock" {{ old('correction_type') == 'location_stock' ? 'selected' : '' }}>
                                        Location Stock Correction
                                    </option>
                                </select>
                                <div class="form-text">
                                    <small>
                                        <strong>Ledger:</strong> Update medication ledger balance<br>
                                        <strong>Location Stock:</strong> Update physical stock at location
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="field_to_correct" class="form-label">
                                    <i class="fas fa-list me-1"></i>
                                    Field to Correct <span class="text-danger">*</span>
                                </label>
                                <select name="field_to_correct" id="field_to_correct" class="form-select" required>
                                    <option value="">Select field...</option>
                                    <option value="quantity" {{ old('field_to_correct') == 'quantity' ? 'selected' : '' }}>
                                        Quantity
                                    </option>
                                    <option value="expiry_date" {{ old('field_to_correct') == 'expiry_date' ? 'selected' : '' }}>
                                        Expiry Date
                                    </option>
                                    <option value="batch_number" {{ old('field_to_correct') == 'batch_number' ? 'selected' : '' }}>
                                        Batch Number
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Step 3: Current and Corrected Values --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="current_value" class="form-label">
                                    <i class="fas fa-eye me-1"></i>
                                    Current Value <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="current_value" id="current_value" 
                                       class="form-control" value="{{ old('current_value') }}" required
                                       placeholder="Enter current value...">
                                <div class="form-text">The current value in the system</div>
                            </div>
                            <div class="col-md-6">
                                <label for="corrected_value" class="form-label">
                                    <i class="fas fa-edit me-1"></i>
                                    Corrected Value <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="corrected_value" id="corrected_value" 
                                       class="form-control" value="{{ old('corrected_value') }}" required
                                       placeholder="Enter corrected value...">
                                <div class="form-text">The correct value to update to</div>
                            </div>
                        </div>

                        {{-- Step 4: Reason and Notes --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="reason" class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    Reason for Correction <span class="text-danger">*</span>
                                </label>
                                <textarea name="reason" id="reason" class="form-control" rows="3" 
                                          required placeholder="Explain why this correction is needed..." maxlength="500">{{ old('reason') }}</textarea>
                                <div class="form-text">Maximum 500 characters</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>
                                    Additional Notes (Optional)
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="2" 
                                          placeholder="Any additional information..." maxlength="1000">{{ old('notes') }}</textarea>
                                <div class="form-text">Maximum 1000 characters</div>
                            </div>
                        </div>

                        {{-- Confirmation --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirmCorrection" required>
                                    <label class="form-check-label text-warning" for="confirmCorrection">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        I understand that this correction will permanently modify stock records and cannot be easily reversed.
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>
                                        Apply Correction
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>
                                        Reset Form
                                    </button>
                                    <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Instructions Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Correction Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                        <p class="mb-0">Stock corrections affect inventory records permanently. Ensure all values are accurate before submission.</p>
                    </div>

                    <h6>When to Use Corrections:</h6>
                    <ul class="small">
                        <li>Physical stock count discrepancies</li>
                        <li>Data entry errors</li>
                        <li>System migration adjustments</li>
                        <li>Expired medication write-offs</li>
                    </ul>

                    <h6>Correction Types:</h6>
                    <dl class="small">
                        <dt>Ledger Correction</dt>
                        <dd>Updates the main medication ledger balance</dd>
                        
                        <dt>Location Stock</dt>
                        <dd>Updates physical stock at specific location</dd>
                    </dl>

                    <h6>Required Documentation:</h6>
                    <ul class="small">
                        <li>Clear reason for correction</li>
                        <li>Current vs corrected values</li>
                        <li>Supporting documentation (if any)</li>
                    </ul>
                </div>
            </div>

            {{-- Recent Corrections --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Recent Corrections
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <p>Recent corrections will be displayed here.</p>
                        <a href="{{ route('medications.reconciliation.audit-trail') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-history me-1"></i>
                            View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const medicationSelect = document.getElementById('medication_id');
    const locationSelect = document.getElementById('location_id');
    const currentStockInfo = document.getElementById('currentStockInfo');
    const stockDetails = document.getElementById('stockDetails');
    const fieldSelect = document.getElementById('field_to_correct');
    const currentValueInput = document.getElementById('current_value');

    // Load current stock when medication and location are selected
    function loadCurrentStock() {
        const medicationId = medicationSelect.value;
        const locationId = locationSelect.value;

        if (medicationId && locationId) {
            // Show loading
            stockDetails.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Loading current stock...';
            currentStockInfo.style.display = 'block';

            // Simulate API call (replace with actual endpoint)
            setTimeout(() => {
                stockDetails.innerHTML = `
                    <div class="row g-2">
                        <div class="col-md-4">
                            <strong>Current Quantity:</strong> 150 units
                        </div>
                        <div class="col-md-4">
                            <strong>Batch Number:</strong> BATCH001
                        </div>
                        <div class="col-md-4">
                            <strong>Expiry Date:</strong> 2024-12-31
                        </div>
                    </div>
                `;
            }, 1000);
        } else {
            currentStockInfo.style.display = 'none';
        }
    }

    medicationSelect.addEventListener('change', loadCurrentStock);
    locationSelect.addEventListener('change', loadCurrentStock);

    // Update input type based on field selection
    fieldSelect.addEventListener('change', function() {
        const field = this.value;
        
        if (field === 'quantity') {
            currentValueInput.type = 'number';
            currentValueInput.step = '0.01';
            document.getElementById('corrected_value').type = 'number';
            document.getElementById('corrected_value').step = '0.01';
        } else if (field === 'expiry_date') {
            currentValueInput.type = 'date';
            document.getElementById('corrected_value').type = 'date';
        } else {
            currentValueInput.type = 'text';
            document.getElementById('corrected_value').type = 'text';
        }
    });

    // Form validation
    document.getElementById('correctionForm').addEventListener('submit', function(e) {
        const confirmation = document.getElementById('confirmCorrection');
        
        if (!confirmation.checked) {
            e.preventDefault();
            alert('Please confirm that you understand the implications of this correction.');
            return false;
        }

        const currentValue = document.getElementById('current_value').value;
        const correctedValue = document.getElementById('corrected_value').value;

        if (currentValue === correctedValue) {
            e.preventDefault();
            alert('Current value and corrected value cannot be the same.');
            return false;
        }

        // Show confirmation dialog
        const medicationName = medicationSelect.options[medicationSelect.selectedIndex].text;
        const locationName = locationSelect.options[locationSelect.selectedIndex].text;
        const fieldName = fieldSelect.options[fieldSelect.selectedIndex].text;

        const confirmMessage = `Are you sure you want to correct ${fieldName} for ${medicationName} at ${locationName} from "${currentValue}" to "${correctedValue}"?`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
