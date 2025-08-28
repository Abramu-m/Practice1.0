@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="fas fa-flask text-primary me-2"></i>
                                {{ $medicalService->name }} - Consumable Requirements
                            </h4>
                            <span class="badge bg-info">{{ $medicalService->serviceCategory->name ?? 'No Category' }}</span>
                        </div>
                        <a href="{{ route('lab.service-consumables.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Services
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Service Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>Service Details</strong></h6>
                            <p><strong>Name:</strong> {{ $medicalService->name }}</p>
                            <p><strong>Description:</strong> {{ $medicalService->description ?: 'No description available' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Requirements</strong></h6>
                            <p><strong>Requires Sample:</strong> {{ $medicalService->requires_sample ? 'Yes' : 'No' }}</p>
                            <p><strong>Sample Type:</strong> {{ $medicalService->sample_type ?: 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Current Consumables -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Current Consumable Requirements</h5>
                                <button class="btn btn-primary" onclick="showAddForm()">
                                    <i class="fas fa-plus"></i> Add Consumable Requirement
                                </button>
                            </div>

                            <div id="consumables-table">
                                @include('lab.investigation_consumables.partials.consumables-table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Consumable Modal -->
<div class="modal fade" id="addConsumableModal" tabindex="-1" aria-labelledby="addConsumableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addConsumableModalLabel">
                    <i class="fas fa-plus me-2"></i>Add Consumable Requirement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addConsumableForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_medication_id" class="form-label">Consumable/Medication *</label>
                        <select class="form-select" id="add_medication_id" name="medication_id" required>
                            <option value="">Select consumable</option>
                            @foreach($medications as $medication)
                                <option value="{{ $medication->id }}">
                                    {{ $medication->generic_name }} 
                                    @if($medication->brand_name)
                                        ({{ $medication->brand_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="add_quantity_required" class="form-label">Quantity Required *</label>
                        <input type="number" class="form-control" id="add_quantity_required" name="quantity_required" 
                               step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_is_optional" name="is_optional" value="1">
                            <label class="form-check-label" for="add_is_optional">
                                This is an optional item
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="add_notes" name="notes" rows="3" 
                                  placeholder="Additional notes or instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Add Requirement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Consumable Modal -->
<div class="modal fade" id="editConsumableModal" tabindex="-1" aria-labelledby="editConsumableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editConsumableModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Consumable Requirement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editConsumableForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_consumable_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_medication_id" class="form-label">Consumable/Medication *</label>
                        <select class="form-select" id="edit_medication_id" name="medication_id" required>
                            <option value="">Select consumable</option>
                            @foreach($medications as $medication)
                                <option value="{{ $medication->id }}">
                                    {{ $medication->generic_name }} 
                                    @if($medication->brand_name)
                                        ({{ $medication->brand_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_quantity_required" class="form-label">Quantity Required *</label>
                        <input type="number" class="form-control" id="edit_quantity_required" name="quantity_required" 
                               step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_optional" name="is_optional" value="1">
                            <label class="form-check-label" for="edit_is_optional">
                                This is an optional item
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3" 
                                  placeholder="Additional notes or instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Requirement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.modal-body .form-label {
    font-weight: 600;
    color: #495057;
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.btn:disabled {
    opacity: 0.7;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.badge {
    font-weight: 500;
}
</style>
@endpush

@section('scripts')
<script>
const serviceId = {{ $medicalService->id }};

// Show add form modal
function showAddForm() {
    // Clear form and remove any validation errors
    $('#addConsumableForm')[0].reset();
    $('#addConsumableForm .is-invalid').removeClass('is-invalid');
    $('#addConsumableForm .invalid-feedback').remove();
    $('#addConsumableModal').modal('show');
}

// Handle add form submission
$('#addConsumableForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');
    
    $.ajax({
        url: `{{ route('lab.service-consumables.individual.store', $medicalService) }}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                $('#addConsumableModal').modal('hide');
                $('#addConsumableForm')[0].reset();
                refreshConsumablesTable();
                showAlert('success', response.message);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function(xhr) {
            let message = 'An error occurred';
            
            // Clear previous validation errors
            $('#addConsumableForm .is-invalid').removeClass('is-invalid');
            $('#addConsumableForm .invalid-feedback').remove();
            
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                // Show field-specific validation errors
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(field => {
                    const input = $(`#add_${field}`);
                    if (input.length) {
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                    }
                });
                message = 'Please correct the errors below';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            showAlert('danger', message);
        },
        complete: function() {
            // Reset button state
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

// Edit consumable
function editConsumable(id) {
    $.ajax({
        url: `/lab/medical-services/{{ $medicalService->id }}/consumables/${id}/edit`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                const consumable = response.data.consumable;
                
                // Clear any previous validation errors
                $('#editConsumableForm .is-invalid').removeClass('is-invalid');
                $('#editConsumableForm .invalid-feedback').remove();
                
                $('#edit_consumable_id').val(consumable.id);
                $('#edit_medication_id').val(consumable.medication_id);
                $('#edit_quantity_required').val(consumable.quantity_required);
                $('#edit_is_optional').prop('checked', consumable.is_optional);
                $('#edit_notes').val(consumable.notes);
                
                $('#editConsumableModal').modal('show');
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'Failed to load consumable data');
        }
    });
}

// Handle edit form submission
$('#editConsumableForm').on('submit', function(e) {
    e.preventDefault();
    
    const consumableId = $('#edit_consumable_id').val();
    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
    
    $.ajax({
        url: `/lab/medical-services/{{ $medicalService->id }}/consumables/${consumableId}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                $('#editConsumableModal').modal('hide');
                refreshConsumablesTable();
                showAlert('success', response.message);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function(xhr) {
            let message = 'An error occurred';
            
            // Clear previous validation errors
            $('#editConsumableForm .is-invalid').removeClass('is-invalid');
            $('#editConsumableForm .invalid-feedback').remove();
            
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                // Show field-specific validation errors
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(field => {
                    const input = $(`#edit_${field}`);
                    if (input.length) {
                        input.addClass('is-invalid');
                        input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                    }
                });
                message = 'Please correct the errors below';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            showAlert('danger', message);
        },
        complete: function() {
            // Reset button state
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

// Delete consumable
function deleteConsumable(id) {
    if (confirm('Are you sure you want to delete this consumable requirement?')) {
        $.ajax({
            url: `/lab/medical-services/{{ $medicalService->id }}/consumables/${id}`,
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    refreshConsumablesTable();
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Failed to delete consumable requirement');
            }
        });
    }
}

// Refresh consumables table
function refreshConsumablesTable() {
    $.ajax({
        url: `{{ route('lab.service-consumables.individual.show', $medicalService) }}?refresh=1`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.html) {
                $('#consumables-table').html(response.html);
            }
        },
        error: function() {
            console.error('Failed to refresh table');
        }
    });
}

// Show alert message
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top
    $('.container-fluid').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection