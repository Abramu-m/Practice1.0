/**
 * Prescription Management Module
 * Handles prescription editing, status updates, and form submissions
 */

// Update prescription status function
function updatePrescriptionStatus(prescriptionId) {
    
    // Clear any previous modal content
    $('#editPrescriptionModalContent').html('<div class="p-4 text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    // Show modal immediately
    const modal = new bootstrap.Modal(document.getElementById('editPrescriptionModal'));
    modal.show();
    
    // Load prescription edit form via AJAX
    $.ajax({
        url: `/prescriptions/${prescriptionId}/edit`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#editPrescriptionModalContent').html(response);
            
            // Initialize form validation or any other necessary scripts
            initializePrescriptionEditForm();
        },
        error: function(xhr) {
            console.error('Error loading prescription edit form:', xhr.responseJSON);
            const errorMessage = xhr.responseJSON?.message || 'Failed to load prescription details.';
            $('#editPrescriptionModalContent').html(`
                <div class="modal-header">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${errorMessage}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            `);
        }
    });
}

// Initialize prescription edit form after loading
function initializePrescriptionEditForm() {
    
    // Add any specific initialization for the prescription edit form
    // For example, setting up medication search, frequency dropdowns, etc.
    
    // Initialize medication search if needed
    if (typeof initializeMedicationSearch === 'function') {
        initializeMedicationSearch();
    }
    
    // Set up form validation
    const form = $('#editPrescriptionForm');
    if (form.length) {
        form.on('submit', function(e) {
            e.preventDefault();
            submitPrescriptionUpdate();
        });
    }
}

// Submit prescription update
function submitPrescriptionUpdate() {
    
    const form = $('#editPrescriptionForm');
    const submitButton = $('#submitPrescriptionUpdate');
    
    if (!form.length) {
        console.error('editPrescriptionForm not found');
        toastr.error('Prescription form not found');
        return;
    }
    
    // Show loading state
    submitButton.prop('disabled', true);
    const originalText = submitButton.html();
    submitButton.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Get form data
    const formData = form.serialize() + '&_method=PUT';
    const prescriptionId = form.data('prescription-id');
    
    
    if (!prescriptionId) {
        console.error('Prescription ID not found');
        toastr.error('Prescription ID not found');
        submitButton.prop('disabled', false);
        submitButton.html(originalText);
        return;
    }
    
    // Submit via AJAX
    $.ajax({
        url: `/prescriptions/${prescriptionId}`,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('Prescription updated successfully!');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editPrescriptionModal'));
            modal.hide();
            
            // Refresh prescriptions list
            loadPrescriptions();
        },
        error: function(xhr) {
            console.error('Prescription update error:', xhr.responseJSON);
            
            // Handle validation errors
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Validation errors:\n';
                
                Object.keys(errors).forEach(field => {
                    errorMessage += `• ${errors[field].join(', ')}\n`;
                });
                
                toastr.error(errorMessage);
            } else {
                const errorMessage = xhr.responseJSON?.message || 'Failed to update prescription.';
                toastr.error(errorMessage);
            }
        },
        complete: function() {
            // Reset button state
            submitButton.prop('disabled', false);
            submitButton.html(originalText);
        }
    });
}

// Load prescriptions list
function loadPrescriptions() {
    
    $.ajax({
        url: `/consultations/${window.consultationId}/prescriptions-partial-html`,
        method: 'GET',
        success: function(response) {
            $('#prescriptions-list').html(response);
            // Ensure the Treatment tab is deactivated if this was called after a save
            try { if (typeof markPaneSaved === 'function') markPaneSaved('treatment'); } catch (e) { console.error(e); }
        },
        error: function(xhr) {
            console.error('Error loading prescriptions:', xhr.responseJSON);
            toastr.error('Failed to refresh prescriptions list.');
        }
    });
}

// Close prescription modal
function closePrescriptionModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('editPrescriptionModal'));
    if (modal) {
        modal.hide();
    }
}

// Initialize prescription module
function initializePrescriptionModule() {
    
    // Initialize medication search functionality
    // NOTE: Medication search is now handled by prescription-modal.js
    // The inline prescription form has been replaced with a modal
    // initializeMedicationSearch();
    
    // Set up modal event handlers
    const modalElement = document.getElementById('editPrescriptionModal');
    modalElement.addEventListener('hidden.bs.modal', function () {
        // Clear modal content when closed
        $('#editPrescriptionModalContent').html('');
    });
    
    // Set up any other prescription-related event handlers
    $(document).on('click', '.prescription-delete-btn', function() {
        const prescriptionId = $(this).data('prescription-id');
        if (confirm('Are you sure you want to delete this prescription?')) {
            deletePrescription(prescriptionId);
        }
    });
}

// Delete prescription
function deletePrescription(prescriptionId) {
    
    $.ajax({
        url: `/prescriptions/${prescriptionId}`,
        method: 'DELETE',
        success: function(response) {
            toastr.success('Prescription deleted successfully!');
            
            // Refresh prescriptions list
            loadPrescriptions();
            
            // Also refresh the modal's prescriptions list if it's open
            const modalVisitId = $('#modal_prescription_visit_id').val();
            if (modalVisitId && typeof window.loadExistingPrescriptions === 'function') {
                window.loadExistingPrescriptions(modalVisitId, 'consultation');
            }
        },
        error: function(xhr) {
            console.error('Error deleting prescription:', xhr.responseJSON);
            const errorMessage = xhr.responseJSON?.message || 'Failed to delete prescription.';
            toastr.error(errorMessage);
        }
    });
}

// Medication search functionality (if needed in prescriptions)
let medicationSearchTimeout;

function initializeMedicationSearch() {
    
    // Check if the medication search element exists
    const medicationSearchElement = $('#medication_search');
    if (medicationSearchElement.length === 0) {
        console.warn('Medication search element #medication_search not found!');
        return;
    } else {
    }
    
    // Set up medication search autocomplete
    medicationSearchElement.on('input', function() {
        const query = $(this).val();
        
        // Clear previous timeout
        if (medicationSearchTimeout) {
            clearTimeout(medicationSearchTimeout);
        }
        
        // Set new timeout
        medicationSearchTimeout = setTimeout(() => {
            if (query.length >= 2) {
                searchMedications(query);
            } else {
                hideMedicationSuggestions();
            }
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#medication_search').length && !$(e.target).closest('#medication_suggestions').length) {
            hideMedicationSuggestions();
        }
    });
    
}

function searchMedications(query) {
    
    $.ajax({
        url: '/medications/api/list',
        method: 'GET',
        data: { 
            search: query, 
            limit: 10 
        },
        success: function(response) {
            
            // The response should be an array of medications
            let medications = [];
            if (Array.isArray(response)) {
                medications = response;
            } else if (response.data && Array.isArray(response.data)) {
                medications = response.data;
            }
            
            if (medications.length > 0) {
                showMedicationSuggestions(medications);
            } else {
                hideMedicationSuggestions();
            }
        },
        error: function(xhr) {
            console.error('Medication search error:', xhr.responseJSON);
            hideMedicationSuggestions();
        }
    });
}

function showMedicationSuggestions(medications) {
    
    const container = $('#medication_suggestions');
    
    if (medications.length === 0) {
        container.addClass('d-none');
        return;
    }
    
    const suggestionsHtml = medications.map(med => {
        const displayName = med.name || med.generic_name || 'Unknown';
        const genericName = med.generic_name || '';
        const brandName = med.brand_name || '';
        const strength = med.strength || '';
        const formulation = med.formulation || '';
        const stock_avalability = med.stock_quantity || 0;

        //if (stock_avalability <= 0) add in brackets (<span class="text-danger">Out of Stock</span>) to the display name
        // If out of stock, add (Out of Stock) in red next to the display name
        const outOfStockLabel = stock_avalability <= 0
            ? ' <span class="text-danger">(Out of Stock)</span>'
            : '';

        return `
            <div class="medication-suggestion-item p-2 border-bottom cursor-pointer" 
             data-medication-id="${med.id}" 
             data-medication-name="${displayName}"
             data-medication-generic="${genericName}"
             data-medication-brand="${brandName}"
             data-medication-strength="${strength}">
            <strong>${displayName}${outOfStockLabel}</strong>
            ${brandName && brandName !== displayName ? `<br><small class="text-muted">Brand: ${brandName}</small>` : ''}
            ${genericName && genericName !== displayName ? `<br><small class="text-info">Generic: ${genericName}</small>` : ''}
            ${strength ? `<br><small class="text-success">Strength: ${strength}</small>` : ''}
            ${formulation ? `<br><small class="text-secondary">Form: ${formulation}</small>` : ''}
            </div>
        `;
    }).join('');
    
    container.html(suggestionsHtml);
    container.removeClass('d-none');
    
    // Add click handler for suggestions
    container.find('.medication-suggestion-item').on('click', function() {
        const medicationId = $(this).data('medication-id');
        const medicationName = $(this).data('medication-name');
        
        // Fill the search input and hidden field
        $('#medication_search').val(medicationName);
        $('#selected_medication_id').val(medicationId);
        
        hideMedicationSuggestions();
    });
    
    // Add hover effects
    container.find('.medication-suggestion-item').on('mouseenter', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', function() {
        $(this).removeClass('bg-light');
    });
}

function hideMedicationSuggestions() {
    $('#medication_suggestions').addClass('d-none');
}

// Save prescription function
function savePrescription() {
    
    const button = $('button[onclick="savePrescription()"]');
    const form = $('#prescriptionFormElement');
    
    if (form.length === 0) {
        console.error('prescriptionFormElement not found');
        toastr.error('Prescription form not found');
        return;
    }
    
    // Validate required fields
    const medicationId = form.find('#selected_medication_id').val();
    const dosage = form.find('input[name="dosage"]').val();
    const frequencyId = form.find('select[name="frequency_id"]').val();
    const duration = form.find('input[name="duration_days"]').val();
    const quantity = form.find('input[name="quantity"]').val();
    
    if (!medicationId || !dosage || !frequencyId || !duration || !quantity) {
        toastr.warning('Please fill in all required fields.');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.length ? buttonText.text() : button.text();
    
    if (buttonText.length) {
        buttonText.text('Saving...');
    } else {
        button.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    }
    
    // Process prescription
    const formData = form.serialize();
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}/prescriptions`,
            method: 'POST',
            data: formData
    }).done(function(response) {
            toastr.success('Prescription added successfully!');
            // Reset form
            form[0].reset();
            $('#selected_medication_id').val('');
            $('#medication_search').val('');
            // Close form
            $('#prescriptionForm').collapse('hide');
            // Refresh prescriptions list
            loadPrescriptions();
            // Update CDS drawer if provided
            try {
                if (response && response.cds_drawer_html !== undefined) {
                    // Target the CDS alerts body in the treatment section
                    let alertsBody = $('#cds-alerts-body');
                    if (alertsBody.length > 0) {
                        alertsBody.html(response.cds_drawer_html);
                        // Update the count badge
                        if (response.cds_alerts_count !== undefined) {
                            $('#cds-alert-count-badge').text(response.cds_alerts_count);
                        }
                        // Update header color to red when alerts are present
                        let header = $('#cds-alerts-header');
                        if (response.cds_alerts_count > 0) {
                            header.css('background-color', '#dc3545');
                            header.removeClass('bg-success');
                            header.addClass('text-white');
                        } else {
                            header.css('background-color', '#28a745');
                            header.removeClass('text-dark');
                            header.addClass('text-white');
                        }
                    }
                }
            } catch (e) {
                console.warn('CDS drawer update failed:', e);
            }
            try { if (typeof markPaneSaved === 'function') markPaneSaved('treatment'); } catch (e) { console.error(e); }
            resolve(response);
        }).fail(function(xhr) {
            console.error('Prescription save error:', xhr.responseJSON);
            let errorMessage = 'Failed to add prescription.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
            reject(xhr);
        }).always(function() {
            // Reset button state
            button.prop('disabled', false);
            const buttonText = button.find('.btn-text');
            if (buttonText.length) {
                buttonText.text('Add Prescription');
            } else {
                button.html('<i class="fas fa-save"></i> Add Prescription');
            }
        });
    });
}
