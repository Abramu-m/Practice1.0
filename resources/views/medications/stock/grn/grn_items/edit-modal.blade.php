<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editItemModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit GRN Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="item_id" id="edit_item_id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Item Selection (Read-only in edit mode) -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_item_display" class="fw-bold">Selected Item</label>
                                <input type="text" id="edit_item_display" class="form-control" readonly>
                                <input type="hidden" name="item_id" id="edit_item_id_hidden">
                                <small class="text-muted">Item cannot be changed after adding</small>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Configuration Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Unit Configuration</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="edit_store_unit_id" class="fw-bold">Store Unit <span class="text-danger">*</span></label>
                                                <select name="store_unit_id" id="edit_store_unit_id" class="form-control" required>
                                                    <option value="">Select Store Unit</option>
                                                </select>
                                                <small class="form-text text-muted">How the item was received</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="edit_dispensing_unit_id" class="fw-bold">Dispensing Unit <span class="text-danger">*</span></label>
                                                <select name="dispensing_unit_id" id="edit_dispensing_unit_id" class="form-control" required>
                                                    <option value="">Select Dispensing Unit</option>
                                                </select>
                                                <small class="form-text text-muted">How it will be issued</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="edit_conversion_factor" class="fw-bold">Conversion Factor <span class="text-danger">*</span></label>
                                                <input type="number" name="conversion_factor" id="edit_conversion_factor" 
                                                       class="form-control" step="0.0001" min="0.0001" required>
                                                <small class="form-text text-muted">Dispensing units per store unit</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0" id="editConversionDisplay" style="display: none;">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <span id="editConversionText"></span>
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
                            <div class="mb-3">
                                <label for="edit_batch_number" class="fw-bold">Batch Number <span class="text-danger">*</span></label>
                                <input type="text" name="batch_number" id="edit_batch_number" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_manufacture_date" class="fw-bold">Manufacture Date</label>
                                <input type="date" name="manufacture_date" id="edit_manufacture_date" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_expiry_date" class="fw-bold">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Quantity and Cost -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_received_quantity" class="fw-bold">Received Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="received_quantity" id="edit_received_quantity" 
                                       class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_unit_cost" class="fw-bold">Unit Cost <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="unit_cost" id="edit_unit_cost" 
                                           class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Discount and Tax -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_discount_percentage" class="fw-bold">Discount %</label>
                                <input type="number" name="discount_percentage" id="edit_discount_percentage" 
                                       class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_tax_percentage" class="fw-bold">Tax %</label>
                                <input type="number" name="tax_percentage" id="edit_tax_percentage" 
                                       class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calculation Summary -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Updated Cost Breakdown</h6>
                            <div class="row text-sm">
                                <div class="col-md-3">
                                    <strong>Total Cost:</strong><br>
                                    <span id="edit_total_cost_display" class="text-info">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Discount:</strong><br>
                                    <span id="edit_discount_amount_display" class="text-warning">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Tax:</strong><br>
                                    <span id="edit_tax_amount_display" class="text-primary">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Net Amount:</strong><br>
                                    <span id="edit_net_amount_display" class="text-success fw-bold">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="edit_notes" class="fw-bold">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="3" 
                                  placeholder="Any additional notes about this item..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate expiry date is after manufacture date in edit form
    document.getElementById('edit_expiry_date').addEventListener('change', function() {
        const mfgDate = document.getElementById('edit_manufacture_date').value;
        const expDate = this.value;
        
        if (mfgDate && expDate && new Date(expDate) <= new Date(mfgDate)) {
            alert('Expiry date must be after manufacture date');
            this.value = '';
        }
    });
    
    // Auto-set expiry date to two years from manufacture date if manufacture date is changed
    document.getElementById('edit_manufacture_date').addEventListener('change', function() {
        const mfgDate = new Date(this.value);
        const currentExpiryDate = document.getElementById('edit_expiry_date').value;
        
        if (mfgDate && !currentExpiryDate) {
            const expiryDate = new Date(mfgDate);
            expiryDate.setFullYear(mfgDate.getFullYear() + 2); // Default 2 years shelf life
            document.getElementById('edit_expiry_date').value = expiryDate.toISOString().split('T')[0];
        }
    });
    
    // Load units for edit modal
    function loadEditUnits() {
        // Load store units
        fetch('/medications/stock/units/store')
            .then(response => response.json())
            .then(data => {
                const storeUnitSelect = document.getElementById('edit_store_unit_id');
                storeUnitSelect.innerHTML = '<option value="">Select Store Unit</option>';
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.name} (${unit.code})`;
                    storeUnitSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading store units for edit:', error);
            });
        
        // Load dispensing units
        fetch('/medications/stock/units/dispensing')
            .then(response => response.json())
            .then(data => {
                const dispensingUnitSelect = document.getElementById('edit_dispensing_unit_id');
                dispensingUnitSelect.innerHTML = '<option value="">Select Dispensing Unit</option>';
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.name} (${unit.code})`;
                    dispensingUnitSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading dispensing units for edit:', error);
            });
    }
    
    // Update conversion display for edit modal
    function updateEditConversionDisplay() {
        const storeUnitSelect = document.getElementById('edit_store_unit_id');
        const dispensingUnitSelect = document.getElementById('edit_dispensing_unit_id');
        const conversionFactorInput = document.getElementById('edit_conversion_factor');
        const conversionDisplay = document.getElementById('editConversionDisplay');
        const conversionText = document.getElementById('editConversionText');
        
        const storeUnitText = storeUnitSelect.options[storeUnitSelect.selectedIndex]?.text || '';
        const dispensingUnitText = dispensingUnitSelect.options[dispensingUnitSelect.selectedIndex]?.text || '';
        const conversionFactor = conversionFactorInput.value;
        
        if (storeUnitText && dispensingUnitText && conversionFactor) {
            conversionText.textContent = `1 ${storeUnitText} = ${conversionFactor} ${dispensingUnitText}`;
            conversionDisplay.style.display = 'block';
        } else {
            conversionDisplay.style.display = 'none';
        }
    }
    
    // Add event listeners for edit form unit changes
    document.getElementById('edit_store_unit_id').addEventListener('change', updateEditConversionDisplay);
    document.getElementById('edit_dispensing_unit_id').addEventListener('change', updateEditConversionDisplay);
    document.getElementById('edit_conversion_factor').addEventListener('input', updateEditConversionDisplay);
    
    // Handle form submission to update action URL
    document.getElementById('editItemForm').addEventListener('submit', function(e) {
        const itemId = document.getElementById('edit_item_id').value;
        this.action = `/medications/stock/grn/{{ $grn->id }}/items/${itemId}`;
    });
});
</script>
