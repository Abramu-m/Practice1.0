@extends('layouts.app_main_layout')

@section('page_title', 'Create Store Requisition')
@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create New Requisition</h5>
                    <a href="{{ route('store.requisitions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('store.requisitions.store') }}" method="POST" id="requisitionForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="requesting_location_id">Requesting Location *</label>
                                    <select name="requesting_location_id" id="requesting_location_id" class="form-control @error('requesting_location_id') is-invalid @enderror" required>
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ old('requesting_location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('requesting_location_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority">Priority</label>
                                    <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror">
                                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="emergency" {{ old('priority') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="purpose">Purpose/Notes</label>
                                    <textarea name="purpose" id="purpose" class="form-control @error('purpose') is-invalid @enderror" rows="3" placeholder="Describe the purpose of this requisition...">{{ old('purpose') }}</textarea>
                                    @error('purpose')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Add Items</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="material_name">Material Name *</label>
                                            <input type="text" id="material_name" class="form-control" placeholder="Type at least 3 characters" minlength="3">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="item_unit">Item Unit</label>
                                            <select id="item_unit" class="form-control" disabled>
                                                <option value="">Select Unit</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="indent_qty">Indent Qty *</label>
                                            <input type="number" id="indent_qty" class="form-control" placeholder="qty" min="1" step="1">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="justification">Justification</label>
                                            <textarea id="justification" class="form-control" rows="1" placeholder="Optional notes..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-primary w-100" id="addItemBtn">
                                                Add Item
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="card mb-4" id="itemsTableCard" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">Requisition Items</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="itemsTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="30%">Material Name</th>
                                                <th width="15%">Unit</th>
                                                <th width="10%">Quantity</th>
                                                <th width="15%">Unit Cost</th>
                                                <th width="15%">Total Cost</th>
                                                <th width="10%">Justification</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsContainer">
                                            <!-- Items will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Total Cost -->
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Estimated Cost:</strong>
                                            <strong id="totalCost">$0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('store.requisitions.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary">Save as Draft</button>
                                    <button type="submit" name="action" value="submit" class="btn btn-primary">Submit Requisition</button>
                                </div>
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
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
    let itemIndex = 0;
    let medicationsData = @json($medications);
    
    console.log('Medications data:', medicationsData); // Debug line
    console.log('Total medications:', medicationsData.length); // Debug line

    // Setup autocomplete for material name
    $('#material_name').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        if (searchTerm.length >= 3) {
            // Filter medications based on search term
            const filteredMeds = medicationsData.filter(med => 
                med.generic_name.toLowerCase().includes(searchTerm) ||
                (med.brand_name && med.brand_name.toLowerCase().includes(searchTerm))
            );
            
            // Show suggestions (you can implement a dropdown here)
            console.log('Filtered medications:', filteredMeds);
        }
    });

    // Add new item
    $('#addItemBtn').click(function() {
        const materialName = $('#material_name').val().trim();
        const indentQty = $('#indent_qty').val();
        const justification = $('#justification').val().trim();
        const medicationId = $('#material_name').data('medication-id');

        console.log('Add item clicked:'); // Debug
        console.log('Material name:', materialName); // Debug
        console.log('Medication ID from data:', medicationId); // Debug

        if (!materialName || materialName.length < 3) {
            alert('Please enter a material name (at least 3 characters)');
            return;
        }

        if (!indentQty || indentQty <= 0) {
            alert('Please enter a valid quantity');
            return;
        }

        // Find matching medication by ID or name
        let medication = null;
        
        if (medicationId) {
            console.log('Looking for medication by ID:', medicationId); // Debug
            // If medication was selected from dropdown, find by ID
            medication = medicationsData.find(med => med.id == medicationId);
            console.log('Found by ID:', medication); // Debug
        } else {
            console.log('Looking for medication by name:', materialName); // Debug
            // If typed manually, try to find by name (for backwards compatibility)
            medication = medicationsData.find(med => 
                med.generic_name.toLowerCase() === materialName.toLowerCase() ||
                (med.brand_name && med.brand_name.toLowerCase() === materialName.toLowerCase())
            );
            console.log('Found by name:', medication); // Debug
        }

        if (!medication) {
            console.log('No medication found!'); // Debug
            alert('Medication not found. Please select a valid medication from the dropdown.');
            return;
        }

        const unitCost = parseFloat(medication.unit_cost || 0);
        const totalCost = parseFloat(indentQty) * unitCost;

        // Create table row
        const row = `
            <tr data-index="${itemIndex}">
                <td>
                    ${medication.generic_name}
                    ${medication.brand_name ? ' - ' + medication.brand_name : ''}
                    ${medication.strength ? ' (' + medication.strength + ')' : ''}
                    <input type="hidden" name="items[${itemIndex}][item_id]" value="${medication.id}">
                    <input type="hidden" name="items[${itemIndex}][item_type]" value="medication">
                </td>
                <td>
                    ${medication.unit || 'Unit'}
                </td>
                <td>
                    ${indentQty}
                    <input type="hidden" name="items[${itemIndex}][requested_quantity]" value="${indentQty}">
                </td>
                <td>
                    $${unitCost.toFixed(2)}
                    <input type="hidden" name="items[${itemIndex}][unit_cost]" value="${unitCost.toFixed(2)}">
                </td>
                <td class="total-cost">
                    $${totalCost.toFixed(2)}
                </td>
                <td>
                    ${justification || '-'}
                    <input type="hidden" name="items[${itemIndex}][justification]" value="${justification}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-index="${itemIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#itemsContainer').append(row);
        $('#itemsTableCard').show();
        
        // Clear form
        $('#material_name').val('').removeData('medication-id');
        $('#indent_qty').val('');
        $('#justification').val('');
        $('#item_unit').val('').prop('disabled', true);

        itemIndex++;
        updateTotalCost();
    });

    // Remove item
    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('tr').remove();
        if ($('#itemsContainer tr').length === 0) {
            $('#itemsTableCard').hide();
        }
        updateTotalCost();
    });

    // Update overall total cost
    function updateTotalCost() {
        let total = 0;
        $('#itemsContainer .total-cost').each(function() {
            const costText = $(this).text().replace('Tsh', '');
            total += parseFloat(costText) || 0;
        });
        $('#totalCost').text('Tsh' + total.toFixed(2));
    }

    // Form validation
    $('#requisitionForm').submit(function(e) {
        if ($('#itemsContainer tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the requisition.');
            return false;
        }
    });

    // Enhanced material name autocomplete with dropdown
    let searchTimeout;
    $('#material_name').on('input', function() {
        const $this = $(this);
        const searchTerm = $this.val().toLowerCase();
        
        // Remove existing dropdown
        $('.medication-dropdown').remove();
        
        if (searchTerm.length >= 3) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const filteredMeds = medicationsData.filter(med => 
                    med.generic_name.toLowerCase().includes(searchTerm) ||
                    (med.brand_name && med.brand_name.toLowerCase().includes(searchTerm))
                ).slice(0, 10); // Limit to 10 results

                if (filteredMeds.length > 0) {
                    console.log('Creating dropdown with', filteredMeds.length, 'items'); // Debug
                    let dropdown = '<div class="medication-dropdown" style="position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; width: 100%;">';
                    
                    filteredMeds.forEach(med => {
                        const displayName = med.generic_name + 
                            (med.brand_name ? ' - ' + med.brand_name : '') +
                            (med.strength ? ' (' + med.strength + ')' : '');
                        dropdown += `<div class="dropdown-item" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="${med.id}" data-name="${displayName}" data-generic="${med.generic_name}">${displayName}</div>`;
                    });
                    
                    dropdown += '</div>';
                    
                    $this.parent().css('position', 'relative').append(dropdown);
                    console.log('Dropdown appended to parent'); // Debug
                } else {
                    console.log('No medications found for search term:', searchTerm); // Debug
                }
            }, 300);
        }
    });

    // Handle dropdown selection
    $(document).on('click', '.dropdown-item', function() {
        const selectedId = $(this).data('id');
        const selectedName = $(this).data('name');
        console.log('Dropdown item clicked:', selectedId, selectedName); // Debug
        $('#material_name').val(selectedName).data('medication-id', selectedId);
        // set item unit based on selected medication
        const medication = medicationsData.find(m => m.id == selectedId);
        if (medication && medication.unit) {
            // populate unit select and enable it
            $('#item_unit').empty().append(`<option value="${medication.unit}">${medication.unit}</option>`).val(medication.unit).prop('disabled', false);
            // store selected unit on the material input for reference if needed
            $('#material_name').data('medication-unit', medication.unit);
            console.log('Set item unit to:', medication.unit);
        } else {
            // no unit available - clear and disable
            $('#item_unit').empty().append('<option value="">Select Unit</option>').val('').prop('disabled', true);
            $('#material_name').removeData('medication-unit');
            console.log('No unit available for selected medication');
        }
        console.log('Set medication-id to:', $('#material_name').data('medication-id')); // Debug
        $('.medication-dropdown').remove();
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#material_name, .medication-dropdown').length) {
            $('.medication-dropdown').remove();
        }
    });
});
</script>
@endsection
