<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addItemModalLabel">
                    <i class="fas fa-plus mr-2"></i>Add Item to GRN
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="addItemForm" method="POST" action="/medications/goods-received-notes/{{ $grn->id }}/add-item">
                @csrf
                <!-- Hidden field for item type - defaulting to medication -->
                <input type="hidden" name="item_type" value="medication">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Item Selection -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="item_id" class="font-weight-bold">Select Medication <span class="text-danger">*</span></label>
                                <select name="item_id" id="item_id" class="form-control" required>
                                    <option value="">Select Medication</option>
                                </select>
                                <small class="form-text text-muted">Search by medication name, brand, or strength</small>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Configuration Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-balance-scale mr-2"></i>Unit Configuration</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="store_unit_id" class="font-weight-bold">Store Unit <span class="text-danger">*</span></label>
                                                <select name="store_unit_id" id="store_unit_id" class="form-control" required>
                                                    <option value="">Select Store Unit</option>
                                                </select>
                                                <small class="form-text text-muted">How the item was received (e.g., box, carton)</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="dispensing_unit_id" class="font-weight-bold">Dispensing Unit <span class="text-danger">*</span></label>
                                                <select name="dispensing_unit_id" id="dispensing_unit_id" class="form-control" required>
                                                    <option value="">Select Dispensing Unit</option>
                                                </select>
                                                <small class="form-text text-muted">How it will be issued (e.g., tablet, capsule)</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="conversion_factor" class="font-weight-bold">Conversion Factor <span class="text-danger">*</span></label>
                                                <input type="number" name="conversion_factor" id="conversion_factor" 
                                                       class="form-control" step="0.0001" min="0.0001" required>
                                                <small class="form-text text-muted">Dispensing units per store unit</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0" id="conversionDisplay" style="display: none;">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <span id="conversionText"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Batch Information -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="batch_number" class="font-weight-bold">Batch Number <span class="text-danger">*</span></label>
                                <input type="text" name="batch_number" id="batch_number" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="manufacture_date" class="font-weight-bold">Manufacture Date</label>
                                <input type="date" name="manufacture_date" id="manufacture_date" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date" class="font-weight-bold">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Quantity and Cost -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="received_quantity" class="font-weight-bold">Store Unit Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="received_quantity" id="received_quantity" 
                                       class="form-control" step="0.01" min="0" required>
                                <small class="form-text text-muted">Enter quantity in store units (e.g., number of boxes)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_cost" class="font-weight-bold">Store Unit Cost <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="unit_cost" id="unit_cost" 
                                           class="form-control" step="0.01" min="0" required>
                                </div>
                                <small class="form-text text-muted">Cost per store unit (e.g., $50 per box)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Discount and Tax -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount_percentage" class="font-weight-bold">Discount %</label>
                                <input type="number" name="discount_percentage" id="discount_percentage" 
                                       class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_percentage" class="font-weight-bold">Tax %</label>
                                <input type="number" name="tax_percentage" id="tax_percentage" 
                                       class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calculation Summary -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Cost Breakdown</h6>
                            <div class="row text-sm">
                                <div class="col-md-3">
                                    <strong>Total Cost:</strong><br>
                                    <span id="total_cost_display" class="text-info">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Discount:</strong><br>
                                    <span id="discount_amount_display" class="text-warning">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Tax:</strong><br>
                                    <span id="tax_amount_display" class="text-primary">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Net Amount:</strong><br>
                                    <span id="net_amount_display" class="text-success font-weight-bold">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes" class="font-weight-bold">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Any additional notes about this item..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Add Item Modal JavaScript loaded');
    
    // Test modal functionality
    $('#addItemModal').on('show.bs.modal', function (e) {
        console.log('Add Item Modal is opening');
    });
    
    $('#addItemModal').on('shown.bs.modal', function (e) {
        console.log('Add Item Modal is now visible');
    });
    
    // Initialize searchable select for medications
    function initializeItemSelect() {
        const itemSelect = $('#item_id');
        
        // Initialize Select2 for searchable dropdown
        itemSelect.select2({
            placeholder: 'Search for medication...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addItemModal')
        });
        
        // Load medications
        loadMedications();
    }
    
    // Load medications function
    function loadMedications() {
        const itemSelect = document.getElementById('item_id');
        
        // Show loading state
        itemSelect.innerHTML = '<option value="">Loading medications...</option>';
        
        // Fetch medications using the existing route
        const url = '/medications/stock/items/medications';
        console.log('Fetching medications from URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received medications data:', data);
                itemSelect.innerHTML = '<option value="">Select Medication</option>';
                
                // Store medication data for later use
                window.medicationData = {};
                
                data.forEach(medication => {
                    const option = document.createElement('option');
                    option.value = medication.id;
                    option.textContent = `${medication.generic_name} - ${medication.brand_name || 'N/A'} (${medication.strength || 'N/A'})`;
                    itemSelect.appendChild(option);
                    
                    // Store medication data including dispensing_unit_id
                    window.medicationData[medication.id] = medication;
                });
                
                // Reinitialize Select2 after loading data
                $('#item_id').select2('destroy').select2({
                    placeholder: 'Search for medication...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#addItemModal')
                });
                
                // Add event listener for medication selection
                $('#item_id').on('change', function() {
                    const selectedMedicationId = this.value;
                    if (selectedMedicationId && window.medicationData[selectedMedicationId]) {
                        const medication = window.medicationData[selectedMedicationId];
                        
                        // Auto-select the dispensing unit if it exists
                        if (medication.dispensing_unit_id) {
                            const dispensingUnitSelect = document.getElementById('dispensing_unit_id');
                            dispensingUnitSelect.value = medication.dispensing_unit_id;
                            
                            // Trigger change event to update conversion display
                            dispensingUnitSelect.dispatchEvent(new Event('change'));
                            
                            console.log('Auto-selected dispensing unit:', medication.dispensing_unit_id);
                        }
                    } else {
                        // Clear dispensing unit selection if no medication selected
                        document.getElementById('dispensing_unit_id').value = '';
                    }
                    
                    // Update conversion display
                    updateConversionDisplay();
                });
                
                console.log('Medications loaded successfully');
            })
            .catch(error => {
                console.error('Error loading medications:', error);
                itemSelect.innerHTML = '<option value="">Error loading medications</option>';
                alert(`Error loading medications: ${error.message}`);
            });
    }
    
    // Load store units and dispensing units
    function loadUnits() {
        // Load store units
        fetch('/medications/stock/units/store')
            .then(response => response.json())
            .then(data => {
                const storeUnitSelect = document.getElementById('store_unit_id');
                storeUnitSelect.innerHTML = '<option value="">Select Store Unit</option>';
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.name} (${unit.code})`;
                    storeUnitSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading store units:', error);
            });
        
        // Load dispensing units
        fetch('/medications/stock/units/dispensing')
            .then(response => response.json())
            .then(data => {
                const dispensingUnitSelect = document.getElementById('dispensing_unit_id');
                dispensingUnitSelect.innerHTML = '<option value="">Select Dispensing Unit</option>';
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.name} (${unit.code})`;
                    dispensingUnitSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading dispensing units:', error);
            });
    }
    
    // Update conversion display
    function updateConversionDisplay() {
        const storeUnitSelect = document.getElementById('store_unit_id');
        const dispensingUnitSelect = document.getElementById('dispensing_unit_id');
        const conversionFactorInput = document.getElementById('conversion_factor');
        const quantityInput = document.getElementById('received_quantity');
        const unitCostInput = document.getElementById('unit_cost');
        const conversionDisplay = document.getElementById('conversionDisplay');
        const conversionText = document.getElementById('conversionText');
        
        const storeUnitText = storeUnitSelect.options[storeUnitSelect.selectedIndex]?.text || '';
        const dispensingUnitText = dispensingUnitSelect.options[dispensingUnitSelect.selectedIndex]?.text || '';
        const conversionFactor = parseFloat(conversionFactorInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitCost = parseFloat(unitCostInput.value) || 0;
        
        if (storeUnitText && dispensingUnitText && conversionFactor) {
            let conversionInfo = `<strong>Unit Conversion:</strong> 1 ${storeUnitText} = ${conversionFactor} ${dispensingUnitText}`;
            
            if (quantity && unitCost && conversionFactor) {
                const totalDispensingUnits = quantity * conversionFactor;
                const dispensingUnitCost = unitCost / conversionFactor;
                conversionInfo += `<br><strong>Your Entry:</strong> ${quantity} ${storeUnitText} × $${unitCost} each = ${totalDispensingUnits} ${dispensingUnitText} × $${dispensingUnitCost.toFixed(4)} each`;
                conversionInfo += `<br><strong>Stock will increase by:</strong> ${totalDispensingUnits} ${dispensingUnitText}`;
            }
            
            conversionText.innerHTML = conversionInfo;
            conversionDisplay.style.display = 'block';
        } else {
            conversionDisplay.style.display = 'none';
        }
    }
    
    // Add event listeners for unit and value changes
    document.getElementById('store_unit_id').addEventListener('change', updateConversionDisplay);
    document.getElementById('dispensing_unit_id').addEventListener('change', updateConversionDisplay);
    document.getElementById('conversion_factor').addEventListener('input', updateConversionDisplay);
    document.getElementById('received_quantity').addEventListener('input', updateConversionDisplay);
    document.getElementById('unit_cost').addEventListener('input', updateConversionDisplay);
    
    // Load units and initialize item select when modal opens
    $('#addItemModal').on('show.bs.modal', function (e) {
        console.log('Loading units and initializing item select for add modal');
        loadUnits();
        initializeItemSelect();
    });
    
    // Auto-set expiry date to two years from manufacture date if manufacture date is set
    document.getElementById('manufacture_date').addEventListener('change', function() {
        const mfgDate = new Date(this.value);
        if (mfgDate && !document.getElementById('expiry_date').value) {
            const expiryDate = new Date(mfgDate);
            expiryDate.setFullYear(mfgDate.getFullYear() + 2); // Default 2 years shelf life
            document.getElementById('expiry_date').value = expiryDate.toISOString().split('T')[0];
        }
    });
    
    // Validate expiry date is after manufacture date
    document.getElementById('expiry_date').addEventListener('change', function() {
        const mfgDate = document.getElementById('manufacture_date').value;
        const expDate = this.value;
        
        if (mfgDate && expDate && new Date(expDate) <= new Date(mfgDate)) {
            alert('Expiry date must be after manufacture date');
            this.value = '';
        }
    });
    
    // Calculate totals function for add form
    function calculateAddTotals() {
        const quantity = parseFloat(document.getElementById('received_quantity').value) || 0;
        const unitCost = parseFloat(document.getElementById('unit_cost').value) || 0;
        const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
        const taxPercentage = parseFloat(document.getElementById('tax_percentage').value) || 0;
        
        const totalCost = quantity * unitCost;
        const discountAmount = (totalCost * discountPercentage) / 100;
        const subtotal = totalCost - discountAmount;
        const taxAmount = (subtotal * taxPercentage) / 100;
        const netAmount = subtotal + taxAmount;
        
        document.getElementById('total_cost_display').textContent = 'Tsh' + totalCost.toFixed(2);
        document.getElementById('discount_amount_display').textContent = 'Tsh' + discountAmount.toFixed(2);
        document.getElementById('tax_amount_display').textContent = 'Tsh' + taxAmount.toFixed(2);
        document.getElementById('net_amount_display').textContent = 'Tsh' + netAmount.toFixed(2);
    }
    
    // Add event listeners for calculation
    ['received_quantity', 'unit_cost', 'discount_percentage', 'tax_percentage'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', calculateAddTotals);
        }
    });
    
    // Initial calculation
    calculateAddTotals();
});
</script>
