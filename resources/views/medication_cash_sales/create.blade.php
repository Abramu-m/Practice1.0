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
                        <h6 class="m-0 fw-bold text-primary">Sale Information</h6>
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
                                <div class="mb-3">
                                    <label for="sale_type">Sale Type *</label>
                                    <select name="sale_type" id="sale_type" class="form-control" required>
                                        <option value="">Select Sale Type</option>
                                        <option value="otc" {{ old('sale_type') == 'otc' ? 'selected' : '' }}>Over-the-Counter (OTC)</option>
                                        <option value="external_prescription" {{ old('sale_type') == 'external_prescription' ? 'selected' : '' }}>External Prescription</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
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
                                <div class="mb-3">
                                    <label for="external_prescription_details">External Prescription Details *</label>
                                    <textarea name="external_prescription_details" id="external_prescription_details" 
                                            class="form-control" rows="3" 
                                            placeholder="Enter details about the external prescription (Doctor name, hospital, prescription date, etc.)">{{ old('external_prescription_details') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
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
                        <h6 class="m-0 fw-bold text-primary">Add Medications</h6>
                        <span class="badge bg-info" id="medication-count">0 items</span>
                    </div>
                    <div class="card-body">
                        <!-- Search and Add Form -->
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-6">
                                <label for="medication_search">Search Medication *</label>
                                <select id="medication_search" class="form-control" style="width:100%">
                                    <option value=""></option>
                                </select>
                                <input type="hidden" id="selected_medication_id">
                                <input type="hidden" id="selected_medication_name">
                            </div>
                            <div class="col-md-auto">
                                <button type="button" id="add-medication-btn" class="btn btn-success" disabled>
                                    <i class="fas fa-plus"></i> Add Medication
                                </button>
                            </div>
                        </div>

                        <!-- Medication Details Form (Hidden until medication selected) -->
                        <div id="medication-details-form" style="display: none;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="quantity">Quantity *</label>
                                        <input type="number" id="quantity" class="form-control" 
                                               placeholder="0" min="0.1" step="0.1" max="999999.99">
                                        <small class="form-text text-muted">Maximum: 999,999.99</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="dosage">Dosage</label>
                                        <input type="text" id="dosage" class="form-control" 
                                               placeholder="e.g., 500mg">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
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
                                    <div class="mb-3">
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
                                    <div class="mb-3">
                                        <label for="duration_days">Duration (Days)</label>
                                        <input type="number" id="duration_days" class="form-control" 
                                               placeholder="e.g., 7" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="instructions">Instructions</label>
                                        <input type="text" id="instructions" class="form-control" 
                                               placeholder="Special instructions">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="unit_price_display">Unit Price</label>
                                        <input type="text" id="unit_price_display" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medications Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">Selected Medications</h6>
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
                                        <tr class="fw-bold">
                                            <td colspan="7" class="text-end">Total Amount:</td>
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
                                <div class="mb-3">
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

                        <div class="mb-3 text-end mt-4">
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
.medication-row { background-color: #f8f9fa; border-left: 4px solid #007bff; }
.stock-available { color: #28a745; }
.stock-low       { color: #ffc107; }
.stock-out       { color: #dc3545; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    let medicationIndex = 0;
    let selectedMedications = [];

    // Show/hide external prescription field
    $('#sale_type').on('change', function () {
        var isExternal = $(this).val() === 'external_prescription';
        $('#external_prescription_row').toggle(isExternal);
        $('#external_prescription_details').prop('required', isExternal);
    }).trigger('change');

    // Select2 medication search via AJAX
    $('#medication_search').select2({
        placeholder: 'Type at least 2 characters to search...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: '{{ route("medication-cash-sales.get-pricing") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { search: params.term, category_id: $('#patient_category_id').val() };
            },
            processResults: function (data) {
                return {
                    results: (data.medications || []).map(function (m) {
                        var stockClass = m.available_stock > 10 ? 'stock-available'
                                       : m.available_stock > 0  ? 'stock-low' : 'stock-out';
                        return {
                            id:              m.id,
                            text:            m.generic_name + (m.strength ? ' ' + m.strength : '') + (m.brand_name ? ' (' + m.brand_name + ')' : ''),
                            unit_price:      m.unit_price || 0,
                            available_stock: m.available_stock || 0,
                            stock_class:     stockClass
                        };
                    })
                };
            },
            templateResult: function (m) {
                if (m.loading) return m.text;
                return $('<div class="d-flex justify-content-between">'
                    + '<div><strong>' + m.text + '</strong></div>'
                    + '<div class="text-end"><span class="fw-bold">TSh ' + parseFloat(m.unit_price).toFixed(2) + '</span>'
                    + '<br><small class="' + m.stock_class + '">Stock: ' + m.available_stock + '</small></div>'
                    + '</div>');
            }
        }
    });

    // When a medication is selected
    $('#medication_search').on('select2:select', function (e) {
        var m = e.params.data;
        $('#selected_medication_id').val(m.id);
        $('#selected_medication_name').val(m.text);
        $('#unit_price_display').val('TSh ' + parseFloat(m.unit_price).toFixed(2));
        $('#medication-details-form').show();
        $('#add-medication-btn').prop('disabled', false);
        $('#quantity').focus();
    });

    // When selection is cleared
    $('#medication_search').on('select2:clear', function () {
        $('#selected_medication_id').val('');
        $('#selected_medication_name').val('');
        $('#medication-details-form').hide();
        $('#add-medication-btn').prop('disabled', true);
    });

    // Add medication to table
    $('#add-medication-btn').on('click', function () {
        var medicationId   = $('#selected_medication_id').val();
        var medicationName = $('#selected_medication_name').val();
        var quantity       = parseFloat($('#quantity').val()) || 0;
        var unitPrice      = parseFloat($('#unit_price_display').val().replace('TSh ', '')) || 0;

        if (!medicationId || quantity <= 0) {
            alert('Please select a medication and enter a valid quantity');
            return;
        }
        if (quantity > 999999.99) {
            alert('Quantity cannot exceed 999,999.99');
            return;
        }
        if (selectedMedications.some(function (med) { return med.medication_id == medicationId; })) {
            alert('This medication is already added');
            return;
        }

        var medication = {
            index:                    medicationIndex++,
            medication_id:            medicationId,
            medication_name:          medicationName,
            quantity:                 quantity,
            dosage:                   $('#dosage').val(),
            medication_frequency_id:  $('#medication_frequency_id').val(),
            frequency_name:           $('#medication_frequency_id option:selected').text().replace('Select Frequency', ''),
            administration_route_id:  $('#administration_route_id').val(),
            route_name:               $('#administration_route_id option:selected').text().replace('Select Route', ''),
            duration_days:            $('#duration_days').val(),
            instructions:             $('#instructions').val(),
            unit_price:               unitPrice,
            total:                    quantity * unitPrice
        };

        selectedMedications.push(medication);
        addMedicationRow(medication);
        updateTotals();
        resetMedicationForm();
    });

    function addMedicationRow(medication) {
        $('#medications-tbody').append(
            '<tr data-index="' + medication.index + '">'
            + '<td><strong>' + medication.medication_name + '</strong>'
            + (medication.instructions ? '<br><small class="text-muted">' + medication.instructions + '</small>' : '')
            + '</td>'
            + '<td>' + (medication.dosage || '-') + '</td>'
            + '<td>' + medication.quantity + '</td>'
            + '<td>' + (medication.frequency_name || '-') + '</td>'
            + '<td>' + (medication.route_name || '-') + '</td>'
            + '<td>' + (medication.duration_days ? medication.duration_days + ' days' : '-') + '</td>'
            + '<td>TSh ' + medication.unit_price.toFixed(2) + '</td>'
            + '<td>TSh ' + medication.total.toFixed(2) + '</td>'
            + '<td><button type="button" class="btn btn-sm btn-danger remove-medication" data-index="' + medication.index + '"><i class="fas fa-trash"></i></button></td>'
            + '</tr>'
        );
        $('#no-medications-message').hide();
        $('#medications-table').show();
        updateMedicationCount();
        generateHiddenInputs();
    }

    // Remove medication row
    $('#medications-tbody').on('click', '.remove-medication', function () {
        var index = parseInt($(this).data('index'));
        selectedMedications = selectedMedications.filter(function (m) { return m.index !== index; });
        $(this).closest('tr').remove();
        if (selectedMedications.length === 0) {
            $('#medications-table').hide();
            $('#no-medications-message').show();
        }
        updateTotals();
        updateMedicationCount();
        generateHiddenInputs();
    });

    function updateTotals() {
        var subtotal   = selectedMedications.reduce(function (s, m) { return s + m.total; }, 0);
        var discount   = parseFloat($('#discount_amount').val()) || 0;
        var finalTotal = Math.max(0, subtotal - discount);
        $('#total-amount').text('TSh ' + subtotal.toFixed(2));
        $('#subtotal-display').text('TSh ' + subtotal.toFixed(2));
        $('#final-total-display').text('TSh ' + finalTotal.toFixed(2));
        $('#submit-btn').prop('disabled', selectedMedications.length === 0);
    }

    function updateMedicationCount() {
        var n = selectedMedications.length;
        $('#medication-count').text(n + ' item' + (n !== 1 ? 's' : ''));
    }

    function resetMedicationForm() {
        $('#medication_search').val(null).trigger('change');
        $('#selected_medication_id, #selected_medication_name').val('');
        $('#quantity, #dosage, #duration_days, #instructions, #unit_price_display').val('');
        $('#medication_frequency_id, #administration_route_id').val('');
        $('#medication-details-form').hide();
        $('#add-medication-btn').prop('disabled', true);
    }

    function generateHiddenInputs() {
        var html = '';
        selectedMedications.forEach(function (m, i) {
            html += '<input type="hidden" name="medications[' + i + '][medication_id]" value="' + m.medication_id + '">'
                  + '<input type="hidden" name="medications[' + i + '][quantity]" value="' + m.quantity + '">'
                  + '<input type="hidden" name="medications[' + i + '][dosage]" value="' + m.dosage + '">'
                  + '<input type="hidden" name="medications[' + i + '][medication_frequency_id]" value="' + m.medication_frequency_id + '">'
                  + '<input type="hidden" name="medications[' + i + '][administration_route_id]" value="' + m.administration_route_id + '">'
                  + '<input type="hidden" name="medications[' + i + '][duration_days]" value="' + m.duration_days + '">'
                  + '<input type="hidden" name="medications[' + i + '][instructions]" value="' + m.instructions + '">';
        });
        $('#medications-data').html(html);
    }

    $('#discount_amount').on('input', updateTotals);

    $('#cashSaleForm').on('submit', function (e) {
        if (selectedMedications.length === 0) {
            e.preventDefault();
            alert('Please add at least one medication before submitting.');
        }
    });
});
</script>
@endsection
