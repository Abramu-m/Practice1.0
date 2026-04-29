/**
 * Lab Investigation Modal JavaScript
 * Provides functionality for ordering lab investigations/procedures from any view
 * 
 * Dependencies:
 * - jQuery
 * - Bootstrap 5
 * - Toastr (for notifications)
 * 
 * Usage:
 * 1. Include the modal partial: @include('partials.lab_investigation_modal')
 * 2. Include this script: <script src="{{ asset('js/lab-investigation-modal.js') }}"></script>
 * 3. Include the CSS: <link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">
 * 4. Call openLabModal(patientId, visitId, patientName, context) to open the modal
 * 
 * Context can be: 'visit' (default) or 'consultation'
 */

// Global modal context
window.labModalContext = {
    mode: 'visit', // 'visit' or 'consultation'
    visitId: null,
    consultationId: null,
    patientId: null,
    patientName: ''
};

// Initialize on document ready
$(document).ready(function() {
    // CSRF Token setup for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initialize medical service search
    initializeServiceSearch();
    
    // Configure toastr if available
    configureinvestigation();
});

/**
 * Open the lab investigation modal
 * @param {number} patientId - The patient ID
 * @param {number} visitId - The visit ID
 * @param {number} consultationIdOrName - Consultation ID if context is 'consultation', otherwise patient name
 * @param {string} patientNameOrContext - Patient name if consultation, otherwise context ('visit')
 * @param {string} context - 'visit' (default) or 'consultation' (only when 4 params)
 */
window.openLabModal = function openLabModal(patientId, visitId, consultationIdOrName, patientNameOrContext, context) {
    // Handle different parameter combinations
    let actualVisitId, consultationId, patientName, actualContext;
    
    if (arguments.length === 5) {
        // Called with: patientId, visitId, consultationId, patientName, 'consultation'
        actualVisitId = visitId;
        consultationId = consultationIdOrName;
        patientName = patientNameOrContext;
        actualContext = context;
    } else if (arguments.length === 4) {
        // Could be old signature: patientId, visitId, patientName, 'consultation'
        actualVisitId = visitId;
        consultationId = null;
        patientName = consultationIdOrName;
        actualContext = patientNameOrContext;
    } else {
        // Called with: patientId, visitId, patientName
        actualVisitId = visitId;
        consultationId = null;
        patientName = consultationIdOrName;
        actualContext = 'visit';
    }
    
    console.log('openLabModal called', { patientId, visitId: actualVisitId, consultationId, patientName, context: actualContext });
    
    // Set modal context
    window.labModalContext.mode = actualContext;
    window.labModalContext.patientId = patientId;
    window.labModalContext.patientName = patientName;
    window.labModalContext.visitId = actualVisitId;
    window.labModalContext.consultationId = consultationId;
    
    // Set patient and visit information
    $('#modal_patient_id').val(patientId);
    $('#modal_visit_id').val(actualVisitId);
    $('#modal_patient_name').text(patientName);
    
    // Always use visit category endpoint
    const categoryUrl = `/patient-visits/${actualVisitId}/category`;
        
    $.ajax({
        url: categoryUrl,
        method: 'GET',
        success: function(response) {
            $('#modal_patient_category_id').val(response.category_id);
        },
        error: function(xhr) {
            console.error('Failed to get patient category:', xhr);
            $('#modal_patient_category_id').val('1'); // Default fallback
        }
    });
    
    // Reset form
    resetInvestigationForm();
    
    // Load existing investigations (pass actualVisitId and actualContext, backend handles lookup)
    loadExistingInvestigations(actualVisitId, actualContext);
    
    // Show modal
    $('#labInvestigationModal').modal('show');

    // Auto-load form if a service is pre-selected
    autoLoadPreselectedServiceForm();
}

/**
 * Reset the investigation form to its initial state
 */
function resetInvestigationForm() {
    $('#labInvestigationForm')[0].reset();
    $('#modal_service_search').val('');
    $('#modal_selected_service_id').val('');
    $('#modal-service-info').hide();
    hideModalServiceSuggestions();
    hideFormTypeInfo();
}

/**
 * Auto-load form for pre-selected service (if any)
 */
function autoLoadPreselectedServiceForm() {
    try {
        const preServiceId = $('#modal_selected_service_id').val();
        const preRequires = $('#modal_service_search').data('requires-form') || $('#modal_selected_service_id').data('requires-form');
        const preFormType = $('#modal_service_search').data('form-type') || $('#modal_selected_service_id').data('form-type');
        const prePrice = $('#modal_service_search').data('price') || $('#modal_selected_service_id').data('service-price') || 0;
        const preCategory = $('#modal_service_search').data('category') || '';
        const preHasPricing = $('#modal_service_search').data('has-pricing') || false;

        if ((preRequires === true || preRequires === 'true') && preFormType) {
            showModalServiceInfo(preServiceId || null, $('#modal_service_search').val() || '', prePrice, preCategory, preHasPricing, true, preFormType);
        }
    } catch (e) {
        console.error('Error auto-loading preselected service form:', e);
    }
}

/**
 * Load existing investigations for a visit or consultation
 * @param {number} id - The visit or consultation ID
 * @param {string} context - 'visit' or 'consultation'
 */
function loadExistingInvestigations(id, context = 'visit') {
    // Show loading state
    $('#current_investigations_section').html(`
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin"></i> Loading investigations...
        </div>
    `);
    $('#investigations_count').text('0');
    
    // Determine URL based on context
    const url = context === 'consultation' 
        ? `/consultations/${id}/investigations-partial`
        : `/patient-visits/${id}/investigations-partial`;
    
    // Make AJAX call to get investigations
    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#current_investigations_section').html(response.html);
                $('#investigations_count').text(response.count);
            } else {
                $('#current_investigations_section').html(`
                    <div class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-triangle"></i> Failed to load investigations
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Failed to load investigations:', xhr);
            $('#current_investigations_section').html(`
                <div class="text-center text-danger py-3">
                    <i class="fas fa-exclamation-triangle"></i> Error loading investigations
                </div>
            `);
        }
    });
}

// Make loadExistingInvestigations globally accessible
window.loadExistingInvestigations = loadExistingInvestigations;
/**
 * Delete an investigation
 * @param {number} investigationId - The investigation ID to delete
 */
function deleteInvestigation(investigationId) {
    if (!confirm('Are you sure you want to delete this investigation? This action cannot be undone.')) {
        return;
    }
    
    // Show loading state on the button
    const button = $(`button[onclick="deleteInvestigation(${investigationId})"]`);
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: `/investigations/${investigationId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Investigation deleted successfully!');
                
                // Refresh the investigations list in the modal
                const visitId = $('#modal_visit_id').val();
                const context = window.labModalContext ? window.labModalContext.mode : 'visit';
                if (visitId) {
                    loadExistingInvestigations(visitId, context);
                }
                
                // Also refresh the main page investigations table if in consultation context
                if (context === 'consultation' && typeof window.loadInvestigations === 'function') {
                    window.loadInvestigations();
                }
            } else {
                toastr.error(response.message || 'Failed to delete investigation.');
                button.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            console.error('Investigation deletion error:', xhr);
            let errorMessage = 'Failed to delete investigation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            toastr.error(errorMessage);
            button.prop('disabled', false).html(originalHtml);
        }
    });
}

/**
 * Cancel an investigation (set status to cancelled)
 * @param {number} investigationId - The investigation ID to cancel
 */
function cancelInvestigation(investigationId) {
    if (!confirm('Are you sure you want to cancel this investigation? This will set its status to cancelled.')) {
        return;
    }
    
    // Show loading state on the button
    const button = $(`button[onclick="cancelInvestigation(${investigationId})"]`);
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: `/investigations/${investigationId}/cancel`,
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Investigation cancelled successfully!');
                
                // Refresh the investigations list in the modal
                const visitId = $('#modal_visit_id').val();
                const context = window.labModalContext ? window.labModalContext.mode : 'visit';
                if (visitId) {
                    loadExistingInvestigations(visitId, context);
                }
                
                // Also refresh the main page investigations table if in consultation context
                if (context === 'consultation' && typeof window.loadInvestigations === 'function') {
                    window.loadInvestigations();
                }
            } else {
                toastr.error(response.message || 'Failed to cancel investigation.');
                button.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            console.error('Investigation cancellation error:', xhr);
            let errorMessage = 'Failed to cancel investigation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            toastr.error(errorMessage);
            button.prop('disabled', false).html(originalHtml);
        }
    });
}

/**
 * Initialize medical service search functionality
 */
function initializeServiceSearch() {
    $('#modal_service_search').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchModalMedicalServices(query);
        } else {
            hideModalServiceSuggestions();
        }
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#modal_service_search').length && 
            !$(e.target).closest('#modal_service_suggestions').length) {
            hideModalServiceSuggestions();
        }
    });
}

/**
 * Search medical services
 * @param {string} query - The search query
 */
function searchModalMedicalServices(query) {
    const patientCategoryId = $('#modal_patient_category_id').val() || '1';
    
    $.ajax({
        url: '/api/medical-services/search',
        method: 'GET',
        data: { 
            query: query, 
            limit: 10,
            patient_category_id: patientCategoryId
        },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                showModalServiceSuggestions(response.data);
            } else {
                hideModalServiceSuggestions();
            }
        },
        error: function(xhr) {
            console.error('Medical service search error:', xhr);
            hideModalServiceSuggestions();
        }
    });
}

/**
 * Display service suggestions dropdown
 * @param {Array} services - Array of service objects
 */
function showModalServiceSuggestions(services) {
    const container = $('#modal_service_suggestions');
    
    let html = '';
    
    services.forEach(function(service) {
        const price = service.price || 0;
        const categoryName = service.category || 'General';
        const hasPricing = service.has_pricing || false;
        const formattedPrice = parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        html += `
            <div class="service-suggestion-item p-3 border-bottom bg-white" 
                 data-service-id="${service.id}" 
                 data-service-name="${service.name}"
                 data-service-code="${service.code || ''}"
                 data-service-price="${price}"
                 data-service-category="${categoryName}"
                 data-has-pricing="${hasPricing ? 'true' : 'false'}"
                 data-requires-form="${service.requires_form ? 'true' : 'false'}"
                 data-form-type="${service.form_type || ''}"
                 style="border: 1px solid #dee2e6; margin-bottom: 1px;">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${service.name}</strong>
                        ${service.code ? `<small class="text-muted d-block">(${service.code})</small>` : ''}
                        <small class="text-muted">${categoryName}</small>
                        ${service.requires_form ? `<br><small class="text-warning"><i class="fas fa-file-alt"></i> Requires form: ${service.form_type || 'Form'}</small>` : ''}
                    </div>
                    <div class="text-end">
                        <strong class="text-primary">TSh ${formattedPrice}</strong>
                        ${!hasPricing ? '<br><small class="text-warning">No pricing</small>' : ''}
                    </div>
                 </div>
            </div>
        `;
    });
    
    container.html(html);
    
    // Add click handlers
    container.find('.service-suggestion-item').on('click', function() {
        const serviceId = $(this).data('service-id');
        const serviceName = $(this).data('service-name');
        const serviceCode = $(this).data('service-code');
        const servicePrice = $(this).data('service-price');
        const serviceCategory = $(this).data('service-category');
        const hasPricing = $(this).data('has-pricing');
        const requiresForm = $(this).data('requires-form');
        const formType = $(this).data('form-type') || null;

        // Set selected service
        $('#modal_service_search').val(serviceName + (serviceCode ? ` (${serviceCode})` : ''));
        $('#modal_selected_service_id').val(serviceId);

        // Store metadata
        $('#modal_service_search').data('price', servicePrice);
        $('#modal_service_search').data('category', serviceCategory);
        $('#modal_service_search').data('has-pricing', hasPricing);
        $('#modal_service_search').data('requires-form', requiresForm ? 'true' : 'false');
        $('#modal_service_search').data('form-type', formType || '');

        // Show service info and form preview if required
        showModalServiceInfo(serviceId, serviceName, servicePrice, serviceCategory, hasPricing, requiresForm, formType);

        // Hide suggestions
        hideModalServiceSuggestions();
    });
    
    // Add hover effects
    container.find('.service-suggestion-item').on('mouseenter', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', function() {
        $(this).removeClass('bg-light');
    });
}

/**
 * Hide service suggestions dropdown
 */
function hideModalServiceSuggestions() {
    $('#modal_service_suggestions').empty();
}

/**
 * Show service information and load form if required
 */
function showModalServiceInfo(serviceId, serviceName, servicePrice, serviceCategory, hasPricing = false, requiresForm = false, formType = null) {
    console.log('showModalServiceInfo called', { serviceId: serviceId, serviceName: serviceName, requiresForm: requiresForm, formType: formType });
    const info = $('#modal-service-info');
    const quantity = parseInt($('#modal_investigation_quantity').val()) || 1;
    const total = (parseFloat(servicePrice) * quantity).toFixed(2);

    let html = `
        <div class="row">
            <div class="col-md-8">
                <strong>${serviceName}</strong><br>
                <small class="text-muted">Category: ${serviceCategory}</small>
            </div>
            <div class="col-md-4 text-end">
                <strong>Unit Price: TSh ${parseFloat(servicePrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong><br>
                <strong>Total: TSh ${parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
            </div>
        </div>
    `;

    if (!hasPricing) {
        html += '<div class="mt-2"><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> No pricing information available</small></div>';
    }

    info.html(html).show();

    // If the service requires a form, show info and load the form
    if (requiresForm && formType) {
        showFormTypeInfo(formType);
        console.log('showModalServiceInfo: requesting loadFormDisplay for', formType);
        loadFormDisplay(formType);
    } else {
        hideFormTypeInfo();
    }
}

/**
 * Show form type information
 */
function showFormTypeInfo(formType) {
    const container = $('#form-type-info-container');
    const formDisplay = $('#form-display-container');

    const infoHtml = `
        <div class="alert alert-info">
            <i class="fas fa-file-alt"></i>
            This investigation requires the <strong>${formType}</strong> form to be completed. The form will be included when ordering.
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="toggleFormDisplay('${formType}')">Toggle Form</button>
        </div>
    `;

    container.html(infoHtml).show();
    formDisplay.show();
}

/**
 * Hide form type information
 */
function hideFormTypeInfo() {
    $('#form-type-info-container').hide().empty();
    $('#form-display-container').hide().empty();
}

/**
 * Load and display a consultation investigation form
 */
function loadFormDisplay(formType) {
    const formContainer = $('#form-display-container');
    formContainer.html('<div class="text-center py-3"><div class="spinner-border"></div></div>');

    const url = '/api/investigation-form/' + encodeURIComponent(formType);
    console.log('loadFormDisplay called for formType:', formType, 'url:', url);

    $.get(url, function(data) {
        console.log('loadFormDisplay: received response for', formType);
        formContainer.html(data).show();
        
        // Visual highlight for debugging
        formContainer.css('outline', '3px solid rgba(0,123,255,0.25)');
        setTimeout(function() { formContainer.css('outline', 'none'); }, 2000);
        
        try {
            // Force container visible
            formContainer.css('display', 'block');

            if (!formContainer.is(':visible')) {
                formContainer.slideDown('fast');
            }

            // Scroll to form
            scrollToForm(formContainer);

            // Focus first input
            setTimeout(function() {
                const firstField = formContainer.find('input, select, textarea').filter(':visible').first();
                if (firstField && firstField.length) {
                    try { firstField.focus(); } catch (e) {}
                }
            }, 50);

            console.log('Form loaded and displayed for formType:', formType);
        } catch (e) {
            console.error('Error showing loaded form:', e);
        }
    }).fail(function(xhr, status, err) {
        console.error('Failed to load form from', url, 'status:', status, 'error:', err);
        formContainer.html(`<div class="alert alert-warning">Form preview not available for <strong>${formType}</strong>. Check console for details.</div>`).show();
    });
}

/**
 * Scroll modal body to show the form
 */
function scrollToForm(formContainer) {
    const modalBody = formContainer.closest('.modal-body');
    if (modalBody && modalBody.length) {
        try {
            const top = formContainer.position().top + modalBody.scrollTop() - 20;
            modalBody.animate({ scrollTop: top }, 250);
        } catch (err) {
            try { formContainer[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
        }
    } else {
        try { formContainer[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
    }
}

/**
 * Toggle form display visibility
 */
function toggleFormDisplay(formType) {
    const formContainer = $('#form-display-container');
    if (formContainer.is(':visible') && formContainer.children().length > 0) {
        formContainer.slideUp();
    } else {
        loadFormDisplay(formType);
        formContainer.slideDown('fast');
    }
}

/**
 * Save lab investigation
 */
/**
 * Save lab investigation
 */
function saveLabInvestigation() {
    const form = $('#labInvestigationForm');
    const button = $('#saveLabInvestigationBtn');
    
    // Basic validation
    if (!$('#modal_selected_service_id').val()) {
        toastr.error('Please select a medical service');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const originalText = button.html();
    button.html('<i class="fas fa-spinner fa-spin"></i> Ordering...');
    
    // Get context
    const context = window.labModalContext.mode;
    const visitId = window.labModalContext.visitId;
    
    // Determine URL based on context (pass visitId for consultation, backend handles consultation lookup)
    const url = context === 'consultation' 
        ? `/consultations/${visitId}/investigations`
        : `/investigations`;
    
    // Serialize main form inputs, excluding the dynamically loaded clinical form fields
    const mainFormData = $('#labInvestigationForm').find('input, select, textarea')
        .not(':disabled')
        .not('#form-display-container input, #form-display-container select, #form-display-container textarea')
        .serialize();

    // Collect clinical form fields and bundle as a JSON string in `clinical_data`
    let clinicalDataParam = '';
    const formDisplay = $('#form-display-container');
    if (formDisplay.is(':visible') && formDisplay.find('input, select, textarea').length) {
        const clinicalFields = {};
        formDisplay.find('input, select, textarea').not(':disabled').each(function() {
            const el = $(this);
            const name = el.attr('name');
            if (!name) return;
            const type = (el.attr('type') || '').toLowerCase();
            if (type === 'radio') {
                if (el.is(':checked')) {
                    clinicalFields[name] = el.val();
                }
            } else if (type === 'checkbox') {
                const key = name.replace(/\[\]$/, '');
                if (el.is(':checked')) {
                    if (Array.isArray(clinicalFields[key])) {
                        clinicalFields[key].push(el.val());
                    } else {
                        clinicalFields[key] = [el.val()];
                    }
                }
            } else {
                clinicalFields[name] = el.val();
            }
        });
        clinicalDataParam = '&clinical_data=' + encodeURIComponent(JSON.stringify(clinicalFields));
    }

    const formData = mainFormData + clinicalDataParam;
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                toastr.success('Lab investigation ordered successfully!');
                
                // Refresh the investigations list in the modal (pass visitId, backend handles lookup)
                if (visitId) {
                    loadExistingInvestigations(visitId, context);
                }
                
                // If in consultation context, trigger form tracking
                if (context === 'consultation' && typeof markFormAsSaved === 'function') {
                    markFormAsSaved('investigationFormElement', 'saveInvestigationBtn', 'investigations');
                }
                
                // If in consultation context and there's a loadInvestigations function, call it
                if (context === 'consultation' && typeof window.loadInvestigations === 'function') {
                    window.loadInvestigations();
                }
                
                // Reset the form but keep the modal open
                resetInvestigationForm();
            } else {
                toastr.error(response.message || 'Failed to order investigation.');
            }
        },
        error: function(xhr) {
            console.error('Investigation save error:', xhr);
            let errorMessage = 'Failed to order investigation.';
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
 * Configure toastr notifications
 */
function configureinvestigation() {
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": 5000
        };
    }
}

/**
 * MutationObserver to handle dynamically inserted forms
 */
(function() {
    try {
        const target = document.getElementById('form-display-container');
        if (!target) return;

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.addedNodes && m.addedNodes.length > 0) {
                    try {
                        const $container = $('#form-display-container');
                        if ($container.length) {
                            if (!$container.is(':visible')) {
                                $container.slideDown('fast');
                            }
                            // Focus first input and scroll into view
                            const first = $container.find('input, select, textarea').filter(':visible').first();
                            if (first && first.length) {
                                first.focus();
                                scrollToForm($container);
                            }
                            console.log('MutationObserver: form inserted, ensured visible and focused');
                        }
                    } catch (err) { 
                        console.error('Observer error', err); 
                    }
                }
            });
        });

        observer.observe(target, { childList: true, subtree: true });
    } catch (e) {
        console.error('Failed to initialize form insertion observer', e);
    }
})();
