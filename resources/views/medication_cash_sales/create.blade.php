@extends('layouts.app_main_layout')

@section('page_title', 'Create Cash Sale')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Create Cash Sale</h1>
                <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Sales
                </a>
            </div>

            <form method="POST" action="{{ route('medication-cash-sales.store') }}" id="cashSaleForm">
                @csrf

                <!-- Sale Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Sale Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($errors->any())
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale_type">Sale Type *</label>
                                    <select name="sale_type" id="sale_type" class="form-control" required>
                                        <option value="">Select Sale Type</option>
                                        <option value="otc" {{ old('sale_type') == 'otc' ? 'selected' : '' }}>Over-the-Counter (OTC)</option>
                                        <option value="external_prescription" {{ old('sale_type') == 'external_prescription' ? 'selected' : '' }}>External Prescription</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_category_id">Patient Category *</label>
                                    <select name="patient_category_id" id="patient_category_id" class="form-control" required>
                                        <option value="">Select Patient Category</option>
                                        @foreach($cashCategories as $category)
                                            <option value="{{ $category->id }}" {{ old('patient_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="external_prescription_row" style="display: none;">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="external_prescription_details">External Prescription Details *</label>
                                    <textarea name="external_prescription_details" id="external_prescription_details" 
                                            class="form-control" rows="3" 
                                            placeholder="Enter details about the external prescription (Doctor name, hospital, prescription date, etc.)">{{ old('external_prescription_details') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">General Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2" 
                                            placeholder="Any additional notes about this sale">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medication Selection Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Add Medications</h6>
                        <span class="badge badge-info" id="medication-count">0 items</span>
                    </div>
                    <div class="card-body">
                        <!-- Search and Add Form -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="medication_search">Search Medication *</label>
                                    <div class="input-group">
                                        <input type="text" id="medication_search" class="form-control" 
                                               placeholder="Type medication name..." autocomplete="off">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <div id="medication-suggestions" class="list-group" style="position: absolute; z-index: 1000; width: 100%; max-height: 300px; overflow-y: auto; display: none;"></div>
                                    <input type="hidden" id="selected_medication_id">
                                </div>
                            </div>
                        </div>

                        <!-- Medication Details Form (Hidden until medication selected) -->
                        <div id="medication-details-form" style="display: none;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="quantity">Quantity *</label>
                                        <input type="number" id="quantity" class="form-control" 
                                               placeholder="0" min="0.1" step="0.1" max="999999.99">
                                        <small class="form-text text-muted">Maximum: 999,999.99</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="dosage">Dosage</label>
                                        <input type="text" id="dosage" class="form-control" 
                                               placeholder="e.g., 500mg">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="medication_frequency_id">Frequency</label>
                                        <select id="medication_frequency_id" class="form-control">
                                            <option value="">Select Frequency</option>
                                            @foreach($medicationFrequencies as $frequency)
                                                <option value="{{ $frequency->id }}">{{ $frequency->frequency_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="administration_route_id">Route</label>
                                        <select id="administration_route_id" class="form-control">
                                            <option value="">Select Route</option>
                                            @foreach($administrationRoutes as $route)
                                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="duration_days">Duration (Days)</label>
                                        <input type="number" id="duration_days" class="form-control" 
                                               placeholder="e.g., 7" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="instructions">Instructions</label>
                                        <input type="text" id="instructions" class="form-control" 
                                               placeholder="Special instructions">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="unit_price_display">Unit Price</label>
                                        <input type="text" id="unit_price_display" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" id="add-medication-btn" class="btn btn-success btn-block" disabled>
                                    <i class="fas fa-plus"></i> Add Medication
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medications Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Selected Medications</h6>
                    </div>
                    <div class="card-body">
                        <div id="no-medications-message" class="text-center text-muted py-4">
                            <i class="fas fa-pills fa-3x mb-3"></i>
                            <p>No medications added yet. Search and add medications above.</p>
                        </div>
                        
                        <div id="medications-table" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Medication</th>
                                            <th>Dosage</th>
                                            <th>Quantity</th>
                                            <th>Frequency</th>
                                            <th>Route</th>
                                            <th>Duration</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="medications-tbody">
                                        <!-- Dynamic content -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="7" class="text-right">Total Amount:</td>
                                            <td id="total-amount">TSh 0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Details and Submit -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="discount_amount">Discount Amount</label>
                                    <input type="number" name="discount_amount" id="discount_amount" 
                                           class="form-control" placeholder="0.00" min="0" step="0.01" value="{{ old('discount_amount', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Subtotal:</strong> <span id="subtotal-display">TSh 0.00</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Final Total:</strong> <span id="final-total-display">TSh 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right mt-4">
                            <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                <i class="fas fa-save"></i> Create Cash Sale
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden medications data -->
                <div id="medications-data"></div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.medication-suggestion-item {
    cursor: pointer;
    padding: 10px;
    border-bottom: 1px solid #dee2e6;
}

.medication-suggestion-item:hover {
    background-color: #f8f9fa;
}

.medication-suggestion-item.active {
    background-color: #007bff;
    color: white;
}

.medication-row {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
}

.stock-info {
    font-size: 0.85em;
    color: #6c757d;
}

.stock-available {
    color: #28a745;
}

.stock-low {
    color: #ffc107;
}

.stock-out {
    color: #dc3545;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let medicationIndex = 0;
    let selectedMedications = [];

    // Handle sale type change
    $('#sale_type').change(function() {
        if ($(this).val() === 'external_prescription') {
            $('#external_prescription_row').show();
            $('#external_prescription_details').prop('required', true);
        } else {
            $('#external_prescription_row').hide();
            $('#external_prescription_details').prop('required', false);
        }
    });

    // Medication search functionality
    $('#medication_search').on('input', function() {
        const query = $(this).val();
        const categoryId = $('#patient_category_id').val();

        if (query.length < 2) {
            hideMedicationSuggestions();
            return;
        }

        if (!categoryId) {
            alert('Please select a patient category first');
            return;
        }

        searchMedications(query, categoryId);
    });

    // Search medications
    function searchMedications(query, categoryId) {
        $.get('{{ route("medication-cash-sales.get-pricing") }}', {
            search: query,
            category_id: categoryId
        }).done(function(response) {
            console.log(response);
            showMedicationSuggestions(response.medications || []);
        }).fail(function() {
            hideMedicationSuggestions();
        });
    }

    // Show medication suggestions
    function showMedicationSuggestions(medications) {
        const container = $('#medication-suggestions');
        container.empty();

        if (medications.length === 0) {
            container.append('<div class="list-group-item">No medications found</div>');
        } else {
            medications.forEach(function(medication) {
                const stockClass = medication.available_stock > 10 ? 'stock-available' : 
                                 medication.available_stock > 0 ? 'stock-low' : 'stock-out';
                
                const item = $(`
                    <div class="list-group-item medication-suggestion-item" 
                         data-medication-id="${medication.id}"
                         data-medication-name="${medication.generic_name}"
                         data-unit-price="${medication.unit_price || 0}"
                         data-available-stock="${medication.available_stock || 0}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${medication.generic_name}</strong>
                                ${medication.brand_name ? `<small class="text-muted"> (${medication.brand_name})</small>` : ''}
                                <br>
                                <small class="text-muted">${medication.strength || ''} ${medication.formulation?.name || ''}</small>
                            </div>
                            <div class="text-right">
                                <div class="font-weight-bold">TSh ${parseFloat(medication.unit_price || 0).toFixed(2)}</div>
                                <small class="${stockClass}">Stock: ${medication.available_stock || 0}</small>
                            </div>
                        </div>
                    </div>
                `);
                container.append(item);
            });
        }

        container.show();
    }

    // Hide suggestions
    function hideMedicationSuggestions() {
        $('#medication-suggestions').hide();
    }

    // Handle medication selection from suggestions
    $(document).on('click', '.medication-suggestion-item', function() {
        const medicationId = $(this).data('medication-id');
        const medicationName = $(this).data('medication-name');
        const unitPrice = $(this).data('unit-price');
        const availableStock = $(this).data('available-stock');

        $('#medication_search').val(medicationName);
        $('#selected_medication_id').val(medicationId);
        $('#unit_price_display').val('TSh ' + parseFloat(unitPrice).toFixed(2));
        
        // Show stock info
        const stockClass = availableStock > 10 ? 'stock-available' : 
                          availableStock > 0 ? 'stock-low' : 'stock-out';
        
        hideMedicationSuggestions();
        $('#medication-details-form').show();
        $('#add-medication-btn').prop('disabled', false);
        
        // Focus on quantity input
        $('#quantity').focus();
    });

    // Add medication to table
    $('#add-medication-btn').click(function() {
        const medicationId = $('#selected_medication_id').val();
        const medicationName = $('#medication_search').val();
        const quantity = parseFloat($('#quantity').val()) || 0;
        const dosage = $('#dosage').val();
        const frequencyId = $('#medication_frequency_id').val();
        const frequencyName = $('#medication_frequency_id option:selected').text();
        const routeId = $('#administration_route_id').val();
        const routeName = $('#administration_route_id option:selected').text();
        const durationDays = $('#duration_days').val();
        const instructions = $('#instructions').val();
        const unitPrice = parseFloat($('#unit_price_display').val().replace('TSh ', '')) || 0;

        if (!medicationId || quantity <= 0) {
            alert('Please select a medication and enter a valid quantity');
            return;
        }

        // Validate quantity range
        if (quantity > 999999.99) {
            alert('Quantity cannot exceed 999,999.99');
            return;
        }

        // Check for duplicates
        if (selectedMedications.some(med => med.medication_id === medicationId)) {
            alert('This medication is already added');
            return;
        }

        const total = quantity * unitPrice;
        const medication = {
            index: medicationIndex++,
            medication_id: medicationId,
            medication_name: medicationName,
            quantity: quantity,
            dosage: dosage,
            medication_frequency_id: frequencyId,
            frequency_name: frequencyName !== 'Select Frequency' ? frequencyName : '',
            administration_route_id: routeId,
            route_name: routeName !== 'Select Route' ? routeName : '',
            duration_days: durationDays,
            instructions: instructions,
            unit_price: unitPrice,
            total: total
        };

        selectedMedications.push(medication);
        addMedicationRow(medication);
        updateTotals();
        resetMedicationForm();
    });

    // Add medication row to table
    function addMedicationRow(medication) {
        const row = $(`
            <tr data-index="${medication.index}">
                <td>
                    <strong>${medication.medication_name}</strong>
                    ${medication.instructions ? `<br><small class="text-muted">${medication.instructions}</small>` : ''}
                </td>
                <td>${medication.dosage || '-'}</td>
                <td>${medication.quantity}</td>
                <td>${medication.frequency_name || '-'}</td>
                <td>${medication.route_name || '-'}</td>
                <td>${medication.duration_days ? medication.duration_days + ' days' : '-'}</td>
                <td>TSh ${medication.unit_price.toFixed(2)}</td>
                <td>TSh ${medication.total.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-medication" data-index="${medication.index}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        
        $('#medications-tbody').append(row);
        $('#no-medications-message').hide();
        $('#medications-table').show();
        updateMedicationCount();
        generateHiddenInputs();
    }

    // Remove medication
    $(document).on('click', '.remove-medication', function() {
        const index = parseInt($(this).data('index'));
        selectedMedications = selectedMedications.filter(med => med.index !== index);
        $(this).closest('tr').remove();
        
        if (selectedMedications.length === 0) {
            $('#medications-table').hide();
            $('#no-medications-message').show();
        }
        
        updateTotals();
        updateMedicationCount();
        generateHiddenInputs();
    });

    // Update totals
    function updateTotals() {
        const subtotal = selectedMedications.reduce((sum, med) => sum + med.total, 0);
        const discount = parseFloat($('#discount_amount').val()) || 0;
        const finalTotal = Math.max(0, subtotal - discount);

        $('#total-amount').text('TSh ' + subtotal.toFixed(2));
        $('#subtotal-display').text('TSh ' + subtotal.toFixed(2));
        $('#final-total-display').text('TSh ' + finalTotal.toFixed(2));

        // Enable/disable submit button
        $('#submit-btn').prop('disabled', selectedMedications.length === 0);
    }

    // Update medication count
    function updateMedicationCount() {
        $('#medication-count').text(selectedMedications.length + ' item' + (selectedMedications.length !== 1 ? 's' : ''));
    }

    // Reset medication form
    function resetMedicationForm() {
        $('#medication_search').val('');
        $('#selected_medication_id').val('');
        $('#quantity').val('');
        $('#dosage').val('');
        $('#medication_frequency_id').val('');
        $('#administration_route_id').val('');
        $('#duration_days').val('');
        $('#instructions').val('');
        $('#unit_price_display').val('');
        $('#medication-details-form').hide();
        $('#add-medication-btn').prop('disabled', true);
    }

    // Generate hidden inputs for form submission
    function generateHiddenInputs() {
        $('#medications-data').empty();
        
        selectedMedications.forEach((medication, index) => {
            $('#medications-data').append(`
                <input type="hidden" name="medications[${index}][medication_id]" value="${medication.medication_id}">
                <input type="hidden" name="medications[${index}][quantity]" value="${medication.quantity}">
                <input type="hidden" name="medications[${index}][dosage]" value="${medication.dosage}">
                <input type="hidden" name="medications[${index}][medication_frequency_id]" value="${medication.medication_frequency_id}">
                <input type="hidden" name="medications[${index}][administration_route_id]" value="${medication.administration_route_id}">
                <input type="hidden" name="medications[${index}][duration_days]" value="${medication.duration_days}">
                <input type="hidden" name="medications[${index}][instructions]" value="${medication.instructions}">
            `);
        });
    }

    // Update totals when discount changes
    $('#discount_amount').on('input', updateTotals);

    // Hide suggestions when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#medication_search, #medication-suggestions').length) {
            hideMedicationSuggestions();
        }
    });

    // Form submission validation
    $('#cashSaleForm').on('submit', function(e) {
        if (selectedMedications.length === 0) {
            e.preventDefault();
            alert('Please add at least one medication before submitting the cash sale.');
            return false;
        }
    });

    // Trigger initial check for sale type
    $('#sale_type').trigger('change');
});
</script>
@endsection
