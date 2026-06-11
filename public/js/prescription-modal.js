/**
 * Prescription Modal Module
 * Reusable prescription modal for both patient visits and consultations
 */

// Global prescription modal context
window.prescriptionModalContext = {
    mode: 'visit', // 'visit' or 'consultation'
    visitId: null,
    consultationId: null,
    patientId: null,
    patientName: null, 
    visitCategoryId: null
};

// Medication search timeout for debouncing (modal-specific)
let modalMedicationSearchTimeout;

/**
 * Open prescription modal
 * @param {object} visit - Visit object containing ID and related information
 * @param {string} context - 'visit' (default) or 'consultation'
 */
window.openPrescriptionModal = function openPrescriptionModal(visit, context = 'visit') {

    console.log('Opening prescription modal with context:', context, 'and visit:', visit);
    
    // Set modal context
    window.prescriptionModalContext.mode = context;
    window.prescriptionModalContext.patientId = visit.patient;
    window.prescriptionModalContext.patientName = visit.patient_info ? `${visit.patient_info.first_name || ''} ${visit.patient_info.middle_name || ''} ${visit.patient_info.last_name || ''}`.trim() : 'Unknown Patient';
    window.prescriptionModalContext.visitId = visit.id;
    window.prescriptionModalContext.consultationId = visit.consultation.id;
    window.prescriptionModalContext.visitCategoryId = visit.visit_category.id;
    
    // Set patient and visit information
    $('#modal_prescription_patient_id').val(visit.patient);
    $('#modal_prescription_visit_id').val(visit.id);
    $('#modal_prescription_consultation_id').val(visit.consultation.id);
    $('#modal_prescription_patient_name').text(visit.patient_info ? `${visit.patient_info.first_name || ''} ${visit.patient_info.middle_name || ''} ${visit.patient_info.last_name || ''}`.trim() : 'Unknown Patient');
    $('#modal_prescription_patient_category_id').val(visit.visit_category.id);
    
    // Reset form
    resetPrescriptionModalForm();
    
    // Load existing prescriptions
    loadExistingPrescriptions(visit.consultation.id, context);
    
    // Show modal
    $('#prescriptionModal').modal('show');
}

/**
 * Reset the prescription modal form
 */
function resetPrescriptionModalForm() {
    $('#prescriptionModalForm')[0].reset();
    $('#modal_selected_medication_id').val('');
    $('#modal_medication_search').val('');
    $('#modal_medication_suggestions').addClass('d-none').empty();
}

/**
 * Load existing prescriptions for the visit
 * @param {number} id - Visit or Consultation ID depending on context
 * @param {string} context - 'visit' or 'consultation'
 */
function loadExistingPrescriptions(id, context = 'visit') {
    // Show loading state
    $('#modal_current_prescriptions_section').html(`
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin"></i> Loading prescriptions...
        </div>
    `);
    $('#modal_prescriptions_count').text('0');
    
    // Determine URL based on context
    const url = context === 'consultation'
        ? `/consultations/${id}/prescriptions-partial`
        : `/consultations/${id}/prescriptions-partial`;

    // Make AJAX call to get prescriptions
    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#modal_current_prescriptions_section').html(response.html);
                $('#modal_prescriptions_count').text(response.count);
            } else {
                $('#modal_current_prescriptions_section').html(`
                    <div class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-triangle"></i> Failed to load prescriptions
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Failed to load prescriptions:', xhr);
            $('#modal_current_prescriptions_section').html(`
                <div class="text-center text-danger py-3">
                    <i class="fas fa-exclamation-triangle"></i> Error loading prescriptions
                </div>
            `);
        }
    });
}

// Make loadExistingPrescriptions globally accessible
window.loadExistingPrescriptions = loadExistingPrescriptions;

/**
 * Refresh the consultation Treatment tab prescriptions table, if present on the page.
 * No-op on pages without that table (e.g. patient_visits index).
 */
window.loadPrescriptions = function loadPrescriptions() {
    if (!$('#prescriptions-list').length || !window.consultationId) {
        return;
    }

    $.ajax({
        url: `/consultations/${window.consultationId}/prescriptions-partial`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#prescriptions-list').html(response.html);
            } else {
                toastr.error('Failed to load prescriptions');
            }
        },
        error: function(xhr) {
            console.error('Failed to load prescriptions:', xhr);
            toastr.error('Failed to refresh prescriptions list');
        }
    });
};

/**
 * Initialize medication search functionality
 */
function initializeMedicationSearch() {
    const medicationSearchElement = $('#modal_medication_search');
    
    if (medicationSearchElement.length === 0) {
        console.warn('Medication search element not found!');
        return;
    }
    
    // Set up medication search autocomplete
    medicationSearchElement.on('input', function() {
        const query = $(this).val();
        
        // Clear previous timeout
        if (modalMedicationSearchTimeout) {
            clearTimeout(modalMedicationSearchTimeout);
        }
        
        // Set new timeout for debouncing
        modalMedicationSearchTimeout = setTimeout(() => {
            if (query.length >= 2) {
                searchMedications(query);
            } else {
                hideMedicationSuggestions();
            }
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#modal_medication_search').length && 
            !$(e.target).closest('#modal_medication_suggestions').length) {
            hideMedicationSuggestions();
        }
    });
}

/**
 * Search medications via AJAX
 * @param {string} query - Search query
 */
function searchMedications(query) {

    $.ajax({
        url: '/medications/api/list',
        method: 'GET',
        data: {
            search: query,
            limit: 10,
            patient_category_id: $('#modal_prescription_patient_category_id').val()
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
                showNoMedicationsFound();
            }
        },
        error: function(xhr) {
            console.error('Error searching medications:', xhr);
            hideMedicationSuggestions();
        }
    });
}

/**
 * Show medication suggestions
 * @param {Array} medications - Array of medication objects
 */
function showMedicationSuggestions(medications) {
    const suggestionsDiv = $('#modal_medication_suggestions');
    
    let html = '<div class="list-group">';
    medications.forEach(function(medication) {
        const displayName = medication.generic_name || medication.name || 'Unknown';
        const brandName = medication.brand_name ? `<br><small class="text-muted">${medication.brand_name}</small>` : '';
        const strength = medication.strength ? `<small class="text-info"> - ${medication.strength}</small>` : '';

        const price = medication.cash_amount ?? medication.selling_price;
        const priceBadge = (price !== null && price !== undefined)
            ? `<span class="badge bg-success-subtle text-success fw-bold float-end">${parseFloat(price).toLocaleString('sw-TZ', {style: 'currency', currency: 'TZS'})}</span>`
            : '';

        html += `
            <button type="button" class="list-group-item list-group-item-action medication-suggestion-item"
                    data-medication-id="${medication.id}"
                    data-medication-name="${displayName}">
                ${priceBadge}
                <strong>${displayName}</strong>${strength}${brandName}
            </button>
        `;
    });
    html += '</div>';
    
    suggestionsDiv.html(html).removeClass('d-none');
    
    // Bind click event to suggestions
    $('.medication-suggestion-item').on('click', function() {
        const medicationId = $(this).data('medication-id');
        const medicationName = $(this).data('medication-name');
        
        // Set the hidden field and search input
        $('#modal_selected_medication_id').val(medicationId);
        $('#modal_medication_search').val(medicationName);
        
        // Hide suggestions
        hideMedicationSuggestions();
        
    });
}

/**
 * Show no medications found message
 */
function showNoMedicationsFound() {
    const suggestionsDiv = $('#modal_medication_suggestions');
    suggestionsDiv.html(`
        <div class="p-2 text-muted text-center">
            <i class="fas fa-search"></i> No medications found
        </div>
    `).removeClass('d-none');
}

/**
 * Hide medication suggestions
 */
function hideMedicationSuggestions() {
    $('#modal_medication_suggestions').addClass('d-none').empty();
}

/**
 * Save prescription from modal
 */
window.savePrescriptionFromModal = function savePrescriptionFromModal() {
    const form = $('#prescriptionModalForm');
    const button = $('#savePrescriptionModalBtn');
    
    // Basic validation
    if (!$('#modal_selected_medication_id').val()) {
        toastr.error('Please select a medication');
        return;
    }
    
    const dosage = form.find('input[name="dosage"]').val();
    const frequencyId = form.find('select[name="frequency_id"]').val();
    const duration = form.find('input[name="duration_days"]').val();
    const quantity = form.find('input[name="quantity"]').val();
    
    if (!dosage || !frequencyId || !duration || !quantity) {
        toastr.warning('Please fill in all required fields.');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const originalText = button.html();
    button.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Get context
    const context = window.prescriptionModalContext.mode;
    const consultationId = window.prescriptionModalContext.consultationId;
    const visitId = window.prescriptionModalContext.visitId;
    
    // Determine URL based on context
    const url = context === 'consultation' 
        ? `/consultations/${consultationId}/prescriptions`
        : `/consultations/${consultationId}/prescriptions`;
    
    // Serialize form data
    const formData = form.serialize();
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        success: function(response) {
            console.log('Prescription save response:', response);
            if (response.success) {
                toastr.success('Prescription added successfully!');
                
                // Refresh the prescriptions list in the modal
                if (consultationId) {
                    loadExistingPrescriptions(consultationId, context);
                }
                
                // If in consultation context, trigger form tracking and page refresh
                if (context === 'consultation' && typeof markFormAsSaved === 'function') {
                    markFormAsSaved('prescriptionFormElement', 'savePrescriptionBtn', 'treatment');
                }
                
                // If in consultation context and there's a loadPrescriptions function, call it
                if (context === 'consultation' && typeof window.loadPrescriptions === 'function') {
                    window.loadPrescriptions();
                }
                
                // Update CDS drawer if provided
                if (response.cds_drawer_html !== undefined && context === 'consultation') {
                    updateCDSDrawer(response);
                }

                // Show CDS interstitial modal if new alerts were triggered
                if (context === 'consultation' && response.cds_alerts && response.cds_alerts.length > 0) {
                    showCdsAlertInterstitial(response.cds_alerts, visitId);
                }
                
                // Reset the form but keep the modal open
                resetPrescriptionModalForm();
            } else {
                toastr.error(response.message || 'Failed to add prescription.');
            }
        },
        error: function(xhr) {
            console.error('Prescription save error:', xhr);
            let errorMessage = 'Failed to add prescription.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
        },
        complete: function() {
            button.prop('disabled', false);
            button.html(originalText);
        }
    });
}

/**
 * Update CDS drawer with new alerts
 * @param {Object} response - Response from server containing CDS data
 */
function updateCDSDrawer(response) {
    try {
        if (response.cds_drawer_html !== undefined) {
            let alertsBody = $('#cds-alerts-body');
            if (alertsBody.length > 0) {
                alertsBody.html(response.cds_drawer_html);
                
                // Update the count badge
                if (response.cds_alerts_count !== undefined) {
                    $('#cds-alert-count-badge').text(response.cds_alerts_count);
                }
                
                // Update header color based on alert count
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
}

/**
 * Show CDS alert interstitial modal stacked on top of the prescription modal.
 * @param {Array} alerts  - Array of {id, severity, message, rationale}
 * @param {number} consultationId - Consultation / visit ID used for the ACK endpoint
 */
function showCdsAlertInterstitial(alerts, consultationId) {
    window._cdsInterstitialAlerts = alerts;
    window._cdsInterstitialConsultationId = consultationId;

    var severityOrder = ['critical', 'high', 'medium', 'low', 'info'];
    var severityColors = {
        critical: '#dc3545',
        high: '#fd7e14',
        medium: '#e0a800',
        low: '#17a2b8',
        info: '#6c757d'
    };

    // Determine highest severity for the header colour
    var topSeverity = 'info';
    alerts.forEach(function (alert) {
        if (severityOrder.indexOf(alert.severity) < severityOrder.indexOf(topSeverity)) {
            topSeverity = alert.severity;
        }
    });

    document.getElementById('cdsAlertInterstitialHeader').style.backgroundColor = severityColors[topSeverity] || '#dc3545';

    // Build alert list
    var html = '<p class="mb-3 text-muted small">The following safety alerts were triggered by this prescription. Please review and acknowledge before continuing.</p>';
    alerts.forEach(function (alert) {
        var color = severityColors[alert.severity] || '#6c757d';
        html += '<div class="border rounded p-3 mb-2" style="border-left: 4px solid ' + color + ' !important;">'
            + '<div class="d-flex align-items-start gap-2">'
            + '<span class="badge text-white" style="background-color:' + color + ';white-space:nowrap;">' + alert.severity.toUpperCase() + '</span>'
            + '<div><div class="fw-semibold">' + alert.message + '</div>'
            + (alert.rationale ? '<div class="text-muted small mt-1">' + alert.rationale + '</div>' : '')
            + '</div></div></div>';
    });
    document.getElementById('cdsAlertInterstitialBody').innerHTML = html;

    // Reset override state
    document.getElementById('cdsInterstitialOverrideGroup').classList.add('d-none');
    document.getElementById('cdsInterstitialConfirmOverride').classList.add('d-none');
    document.getElementById('cdsInterstitialOverrideReason').value = '';

    // Show the modal
    var interstitialEl = document.getElementById('cdsAlertInterstitialModal');
    var modal = new bootstrap.Modal(interstitialEl);
    modal.show();

    // After showing, lift the new backdrop above the prescription modal
    interstitialEl.addEventListener('shown.bs.modal', function onShown() {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
            backdrops[backdrops.length - 1].style.zIndex = '1060';
        }
        interstitialEl.removeEventListener('shown.bs.modal', onShown);
    });
}

/**
 * Toggle the override reason textarea visibility.
 */
window.toggleCdsInterstitialOverride = function toggleCdsInterstitialOverride() {
    var group   = document.getElementById('cdsInterstitialOverrideGroup');
    var confirm = document.getElementById('cdsInterstitialConfirmOverride');
    var show = group.classList.contains('d-none');
    group.classList.toggle('d-none', !show);
    confirm.classList.toggle('d-none', !show);
};

/**
 * Handle accept / override / dismiss for the CDS interstitial.
 * @param {string} action - 'accept' | 'override' | 'dismiss'
 */
window.handleCdsInterstitialAction = async function handleCdsInterstitialAction(action) {
    var alerts         = window._cdsInterstitialAlerts || [];
    var consultationId = window._cdsInterstitialConsultationId;
    var reason         = null;

    if (action === 'override') {
        reason = document.getElementById('cdsInterstitialOverrideReason').value.trim();
        if (!reason) {
            toastr.warning('Please provide a reason for overriding this alert.');
            return;
        }
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    try {
        var promises = alerts.map(function (alert) {
            return $.ajax({
                url: '/consultations/' + consultationId + '/cds-alerts/' + alert.id + '/ack',
                method: 'POST',
                data: { action: action, reason: reason, _token: csrfToken }
            });
        });

        await Promise.all(promises);

        var interstitialEl = document.getElementById('cdsAlertInterstitialModal');
        var modal = bootstrap.Modal.getInstance(interstitialEl);
        if (modal) modal.hide();

        var labels = { accept: 'accepted', override: 'overridden', dismiss: 'dismissed' };
        toastr.info('Alert ' + (labels[action] || 'acknowledged') + '.');

        window._cdsInterstitialAlerts         = null;
        window._cdsInterstitialConsultationId = null;
    } catch (e) {
        console.error('Failed to acknowledge CDS alert:', e);
        toastr.error('Failed to acknowledge alert. Please try again.');
    }
};

/**
 * Delete prescription from modal list
 * @param {number} prescriptionId - Prescription ID to delete
 */
window.deletePrescriptionFromModal = function deletePrescriptionFromModal(prescriptionId) {
    if (!confirm('Are you sure you want to delete this prescription?')) {
        return;
    }
    
    $.ajax({
        url: `/prescriptions/${prescriptionId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('Prescription deleted successfully!');
            
            // Refresh the prescriptions list in the modal
            const consultationId = $('#modal_prescription_consultation_id').val();
            const context = window.prescriptionModalContext.mode;
            if (consultationId) {
                loadExistingPrescriptions(consultationId, context);
            }
            
            // Also refresh the main page prescriptions table if in consultation context
            if (context === 'consultation' && typeof window.loadPrescriptions === 'function') {
                window.loadPrescriptions();
            }
        },
        error: function(xhr) {
            console.error('Failed to delete prescription:', xhr);
            toastr.error(xhr.responseJSON?.message || 'Failed to delete prescription');
        }
    });
}

/**
 * Update prescription status (for editing existing prescriptions)
 * @param {number} prescriptionId - Prescription ID to edit
 */
window.updatePrescriptionStatus = function updatePrescriptionStatus(prescriptionId) {
    
    // Clear any previous modal content
    $('#editPrescriptionModalContent').html('<div class="p-4 text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

    // Show modal immediately
    const editModalEl = document.getElementById('editPrescriptionModal');
    const modal = new bootstrap.Modal(editModalEl);
    modal.show();

    // If stacked on top of the prescription modal, lift this modal's backdrop above it
    editModalEl.addEventListener('shown.bs.modal', function onShown() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
            backdrops[backdrops.length - 1].style.zIndex = '1060';
        }
        editModalEl.removeEventListener('shown.bs.modal', onShown);
    });

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

/**
 * Initialize prescription edit form after loading
 */
function initializePrescriptionEditForm() {
    
    // Set up form validation
    const form = $('#editPrescriptionForm');
    if (form.length) {
        form.on('submit', function(e) {
            e.preventDefault();
            submitPrescriptionUpdate();
        });
    }
}

/**
 * Submit prescription update
 */
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
            
            // Refresh the main page prescriptions table if present
            if (typeof window.loadPrescriptions === 'function') {
                window.loadPrescriptions();
            }

            // Refresh the prescription modal's list if it's open
            const modalConsultationId = $('#modal_prescription_consultation_id').val();
            if (modalConsultationId) {
                loadExistingPrescriptions(modalConsultationId, window.prescriptionModalContext.mode);
            }
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

/**
 * Delete prescription (global function for consultation page)
 * @param {number} prescriptionId - Prescription ID to delete
 */
window.deletePrescription = function deletePrescription(prescriptionId) {
    if (!confirm('Are you sure you want to delete this prescription?')) {
        return;
    }
    
    
    $.ajax({
        url: `/prescriptions/${prescriptionId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('Prescription deleted successfully!');
            
            // Refresh prescriptions list
            if (typeof window.loadPrescriptions === 'function') {
                window.loadPrescriptions();
            }
            
            // Also refresh the modal's prescriptions list if it's open
            const modalConsultationId = $('#modal_prescription_consultation_id').val();
            if (modalConsultationId && typeof loadExistingPrescriptions === 'function') {
                loadExistingPrescriptions(modalConsultationId, 'consultation');
            }
        },
        error: function(xhr) {
            console.error('Error deleting prescription:', xhr.responseJSON);
            const errorMessage = xhr.responseJSON?.message || 'Failed to delete prescription.';
            toastr.error(errorMessage);
        }
    });
}

/**
 * Initialize prescription module
 */
function initializePrescriptionModule() {
    
    // Set up modal event handlers for edit prescription modal
    const editModalElement = document.getElementById('editPrescriptionModal');
    if (editModalElement) {
        editModalElement.addEventListener('hidden.bs.modal', function () {
            // Clear modal content when closed
            $('#editPrescriptionModalContent').html('');
        });
    }
}

/**
 * Initialize the prescription modal on document ready
 */
$(document).ready(function() {
    // Initialize medication search
    initializeMedicationSearch();
    
    // Initialize prescription module
    initializePrescriptionModule();
    
    // Toastr configuration
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": 5000
        };
    }
});
