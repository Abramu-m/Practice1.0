/**
 * Investigations Module for Consultation
 * Handles medical service search and investigation management
 */

// Debug: confirm this script is loaded
console.log('Loaded: public/js/consultation/investigations.js');

// Initialize investigations functionality
function initializeInvestigationsModule() {
    console.log('Initializing investigations module...');
    
    // Initialize medical service search
    initializeMedicalServiceSearch();
    
    // Add service selection change handler
    $('#selected_service_id').on('change', function() {
        const serviceId = $(this).val();
        if (serviceId) {
            const requiresForm = $(this).data('requires-form');
            const formType = $(this).data('form-type');
            const servicePrice = $(this).data('service-price');
            const hasPricing = $(this).data('has-pricing');
            showServiceInfo(serviceId, null, servicePrice, null, requiresForm, formType, hasPricing);
        }
    });
    
    // Add quantity change handler to update total price
    $(document).on('input change', '#investigation_quantity', function() {
        updateTotalPrice();
    });
}

// Initialize medical service search functionality
function initializeMedicalServiceSearch() {
    console.log('Initializing medical service search...');
    
    // Check if the service search element exists
    const serviceSearchElement = $('#service_search');
    if (serviceSearchElement.length === 0) {
        console.warn('Service search element #service_search not found!');
        return;
    } else {
        console.log('Found service search element:', serviceSearchElement);
    }
    
    // Set up medical service search autocomplete
    serviceSearchElement.on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchMedicalServices(query);
        } else {
            hideServiceSuggestions();
        }
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#service_search').length && !$(e.target).closest('#service_suggestions').length) {
            hideServiceSuggestions();
        }
    });
    
    console.log('Medical service search initialization complete');
}

// Search medical services via AJAX
function searchMedicalServices(query) {
    console.log('Searching medical services for:', query);
    
    // Get patient category ID from the hidden field
    const patientCategoryId = $('#patient_category_id').val();
    
    $.ajax({
        url: '/api/medical-services/search',
        method: 'GET',
        data: { 
            query: query, 
            limit: 10,
            patient_category_id: patientCategoryId
        },
        success: function(response) {
            console.log('Medical services search response:', response);
            
            if (response.success && response.data && response.data.length > 0) {
                showServiceSuggestions(response.data);
            } else {
                hideServiceSuggestions();
            }
        },
        error: function(xhr) {
            console.error('Medical services search error:', xhr.responseJSON);
            hideServiceSuggestions();
        }
    });
}

// Show service suggestions dropdown
function showServiceSuggestions(services) {
    console.log('Showing service suggestions:', services);
    
    const container = $('#service_suggestions');
    
    if (services.length === 0) {
        container.addClass('d-none');
        return;
    }
    
    const suggestionsHtml = services.map(service => `
        <div class="service-suggestion-item p-2 border-bottom cursor-pointer" 
             data-service-id="${service.id}" 
             data-service-name="${service.name}"
             data-service-category="${service.category || ''}"
             data-service-price="${service.price || 0}"
             data-has-pricing="${service.has_pricing || false}"
             data-requires-form="${service.requires_form || false}"
             data-form-type="${service.form_type || ''}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong>${service.name}</strong>
                    ${service.category ? `<br><small class="text-muted">${service.category}</small>` : ''}
                    ${service.requires_form ? `<br><small class="text-warning"><i class="fas fa-form"></i> Requires Form: ${service.form_type || 'Form Required'}</small>` : ''}
                </div>
                <div class="text-end">
                    ${service.has_pricing && service.price ? 
                        `<span class="badge badge-success text-white">TSh ${parseFloat(service.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>` : 
                        `<span class="badge badge-secondary text-white">No Price</span>`
                    }
                </div>
            </div>
        </div>
    `).join('');
    
    container.html(suggestionsHtml);
    container.removeClass('d-none');
    
    // Add click handler for suggestions
    container.find('.service-suggestion-item').on('click', function() {
        const serviceId = $(this).data('service-id');
        const serviceName = $(this).data('service-name');
        const serviceCategory = $(this).data('service-category');
        const servicePrice = $(this).data('service-price');
        const hasPricing = $(this).data('has-pricing');
        const requiresForm = $(this).data('requires-form');
        const formType = $(this).data('form-type');
        
        // Fill the search input and hidden field
        $('#service_search').val(serviceName);
        $('#selected_service_id').val(serviceId);
        
        // Store form requirements data
        $('#selected_service_id').data('requires-form', requiresForm);
        $('#selected_service_id').data('form-type', formType);
        $('#selected_service_id').data('service-price', servicePrice);
        $('#selected_service_id').data('has-pricing', hasPricing);
        
        // Show service info with pricing
        showServiceInfo(serviceId, serviceName, servicePrice, serviceCategory, requiresForm, formType, hasPricing);
        
        hideServiceSuggestions();
    });
    
    // Add hover effects
    container.find('.service-suggestion-item').on('mouseenter', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', function() {
        $(this).removeClass('bg-light');
    });
}

// Hide service suggestions
function hideServiceSuggestions() {
    $('#service_suggestions').addClass('d-none');
}

// Show service information
function showServiceInfo(serviceId, serviceName, servicePrice, serviceCategory, requiresForm = false, formType = null, hasPricing = false) {
    const serviceInfo = $('#service-info');
    
    if (serviceId) {
        let priceDisplay = '';
        if (hasPricing && servicePrice && parseFloat(servicePrice) > 0) {
            priceDisplay = `<span class="badge badge-success text-white">TSh ${parseFloat(servicePrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        } else {
            priceDisplay = `<span class="badge badge-warning text-dark">Price not set</span>`;
        }
        
        let infoHtml = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Selected Service:</strong> ${serviceName || 'Loading...'}
                    ${serviceCategory ? `<br><small class="text-muted">Category: ${serviceCategory}</small>` : ''}
                    ${requiresForm ? `<br><small class="text-warning"><i class="fas fa-form"></i> This service requires form: <strong>${formType || 'Custom Form'}</strong></small>` : ''}
                </div>
                <div>
                    ${priceDisplay}
                </div>
            </div>
        `;
        
        serviceInfo.html(infoHtml).show();
        
        // Update total price when quantity changes
        updateTotalPrice();
        
        // Show form type information instead of dynamic form
        if (requiresForm && formType) {
            showFormTypeInfo(formType);
        } else {
            hideFormTypeInfo();
        }
    } else {
        serviceInfo.hide();
        hideFormTypeInfo();
    }
}

// Show dynamic form for services that require additional data
function showDynamicForm(serviceId) {
    let formFields = $('#selected_service_id').data('form-fields') || [];
    const dynamicFormContainer = $('#dynamic-form-container');
    
    if (!dynamicFormContainer.length) {
        // Create the dynamic form container if it doesn't exist
        const containerHtml = `
            <div id="dynamic-form-container" class="mt-3 p-3 border rounded bg-light">
                <h6><i class="fas fa-form"></i> Additional Information Required</h6>
                <div id="dynamic-form-fields"></div>
            </div>
        `;
        $('#investigationFormElement').find('.text-end').before(containerHtml);
    }
    
    // Handle different form field structures
    if (formFields && typeof formFields === 'object') {
        // Check if it's the nested structure with .fields property
        if (formFields.fields && Array.isArray(formFields.fields)) {
            formFields = formFields.fields;
        } 
        // Check if it's already an array
        else if (!Array.isArray(formFields)) {
            // If it's an object but not an array, convert to array
            formFields = Object.values(formFields);
        }
    } else {
        formFields = [];
    }
    
    // Generate form fields
    let formFieldsHtml = '';
    if (formFields && formFields.length > 0) {
        formFields.forEach(field => {
            formFieldsHtml += generateFormField(field);
        });
    } else {
        // Default form for services that require forms but don't have specific fields defined
        formFieldsHtml = `
            <div class="form-group mb-3">
                <label class="form-label">Clinical Indication <span class="text-danger">*</span></label>
                <textarea class="form-control" name="clinical_indication" rows="2" 
                          placeholder="Please provide clinical indication for this investigation..." required></textarea>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Patient History</label>
                <textarea class="form-control" name="patient_history" rows="2" 
                          placeholder="Relevant patient history..."></textarea>
            </div>
        `;
    }
    
    $('#dynamic-form-fields').html(formFieldsHtml);
    $('#dynamic-form-container').show().addClass('show');
}

// Hide dynamic form
function hideDynamicForm() {
    const container = $('#dynamic-form-container');
    container.hide().removeClass('show');
    
    // Clear any validation errors
    container.find('.form-control').removeClass('is-invalid');
    container.find('.invalid-feedback').remove();
}

// Show form type information
function showFormTypeInfo(formType) {
    const formTypeContainer = $('#form-type-info-container');
    
    if (!formTypeContainer.length) {
        // Create the form type info container if it doesn't exist
        const containerHtml = `
            <div id="form-type-info-container" class="mt-3 p-3 border rounded bg-info bg-opacity-10 border-info">
                <h6><i class="fas fa-info-circle text-info"></i> Form Required</h6>
                <div id="form-type-info">a</div>
                <div id="form-display-container" class="mt-3"></div>
            </div>
        `;
        $('#investigationFormElement').find('.text-end').before(containerHtml);
    }
    
    const infoHtml = `
        <div class="alert alert-info mb-0">
            <i class="fas fa-file-alt"></i> 
            This investigation requires the <strong>${formType}</strong> form to be completed.
            <br><small class="text-muted">The form is displayed below and will be included during the investigation process.</small>
            <br>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="toggleFormDisplay('${formType}')">
                <i class="fas fa-eye-slash"></i> Hide Form
            </button>
        </div>
    `;
    
    $('#form-type-info').html(infoHtml);
    $('#form-type-info-container').show().addClass('show');
    
    // Automatically load and show the form
    loadFormDisplay(formType);
}

// Hide form type information
function hideFormTypeInfo() {
    const container = $('#form-type-info-container');
    const formContainer = $('#form-display-container');
    
    // Hide both the info and the form
    container.hide().removeClass('show');
    formContainer.hide().empty();
}

// Load and display form
function loadFormDisplay(formType) {
    const formContainer = $('#form-display-container');
    const button = $('button[onclick*="toggleFormDisplay"]');
    
    // Show loading state
    if (button.length) {
        button.html('<i class="fas fa-spinner fa-spin"></i> Loading Form...');
    }
    
    // Get consultation ID from the window variable
    const consultationId = window.consultationId || null;
    
    // Prepare URL with consultation ID if available
    let url = '/api/investigation-form/' + formType;
    if (consultationId) {
        url += '?consultation_id=' + consultationId;
    }
    
    // Load the form via AJAX
    $.get(url, function(data) {
        formContainer.html(data).show();
        if (button.length) {
            button.html('<i class="fas fa-eye-slash"></i> Hide Form');
        }
    }).fail(function() {
        // Fallback: show a placeholder
        const placeholderHtml = `
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Investigation Form: ${formType}</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Form preview not available. The <strong>${formType}</strong> form will be displayed during the actual investigation process.
                    </div>
                </div>
            </div>
        `;
        formContainer.html(placeholderHtml).show();
        if (button.length) {
            button.html('<i class="fas fa-eye-slash"></i> Hide Form');
        }
    });
}

// Toggle form display
function toggleFormDisplay(formType) {
    const formContainer = $('#form-display-container');
    const button = $('button[onclick*="toggleFormDisplay"]');
    
    if (formContainer.is(':visible')) {
        // Hide form
        formContainer.slideUp();
        button.html('<i class="fas fa-eye"></i> Show Form');
    } else {
        // Show form
        loadFormDisplay(formType);
    }
}

// Generate form field HTML based on field configuration
function generateFormField(field) {
    const fieldId = `dynamic_field_${field.name}`;
    const required = field.required ? 'required' : '';
    const requiredStar = field.required ? '<span class="text-danger">*</span>' : '';
    
    switch (field.type) {
        case 'text':
            return `
                <div class="form-group mb-3">
                    <label for="${fieldId}" class="form-label">${field.label} ${requiredStar}</label>
                    <input type="text" class="form-control" id="${fieldId}" name="${field.name}" 
                           placeholder="${field.placeholder || ''}" ${required}>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        case 'textarea':
            return `
                <div class="form-group mb-3">
                    <label for="${fieldId}" class="form-label">${field.label} ${requiredStar}</label>
                    <textarea class="form-control" id="${fieldId}" name="${field.name}" rows="${field.rows || 3}"
                              placeholder="${field.placeholder || ''}" ${required}></textarea>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        case 'select':
            let optionsHtml = '<option value="">Select an option</option>';
            if (field.options) {
                // Handle both array and object formats for options
                if (Array.isArray(field.options)) {
                    field.options.forEach(option => {
                        optionsHtml += `<option value="${option}">${option}</option>`;
                    });
                } else if (typeof field.options === 'object') {
                    Object.entries(field.options).forEach(([value, label]) => {
                        optionsHtml += `<option value="${value}">${label}</option>`;
                    });
                }
            }
            return `
                <div class="form-group mb-3">
                    <label for="${fieldId}" class="form-label">${field.label} ${requiredStar}</label>
                    <select class="form-control" id="${fieldId}" name="${field.name}" ${required}>
                        ${optionsHtml}
                    </select>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        case 'checkbox_group':
            let checkboxHtml = '';
            if (field.options && Array.isArray(field.options)) {
                field.options.forEach((option, index) => {
                    const checkboxId = `${fieldId}_${index}`;
                    checkboxHtml += `
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="${checkboxId}" 
                                   name="${field.name}[]" value="${option}">
                            <label class="form-check-label" for="${checkboxId}">
                                ${option}
                            </label>
                        </div>
                    `;
                });
            }
            return `
                <div class="form-group mb-3">
                    <label class="form-label">${field.label} ${requiredStar}</label>
                    <div class="mt-2">
                        ${checkboxHtml}
                    </div>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        case 'number':
            return `
                <div class="form-group mb-3">
                    <label for="${fieldId}" class="form-label">${field.label} ${requiredStar}</label>
                    <input type="number" class="form-control" id="${fieldId}" name="${field.name}" 
                           placeholder="${field.placeholder || ''}" ${required}
                           min="${field.min || ''}" max="${field.max || ''}">
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        case 'date':
            return `
                <div class="form-group mb-3">
                    <label for="${fieldId}" class="form-label">${field.label} ${requiredStar}</label>
                    <input type="date" class="form-control" id="${fieldId}" name="${field.name}" ${required}>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        case 'checkbox':
            return `
                <div class="form-group mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="${fieldId}" name="${field.name}" value="1" ${required}>
                        <label class="form-check-label" for="${fieldId}">
                            ${field.label} ${requiredStar}
                        </label>
                    </div>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
        default:
            return `
                <div class="form-group mb-3">
                    <label for="${fieldId}" class="form-label">${field.label} ${requiredStar}</label>
                    <input type="text" class="form-control" id="${fieldId}" name="${field.name}" 
                           placeholder="${field.placeholder || ''}" ${required}>
                    ${field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : ''}
                </div>
            `;
    }
}

// Save investigation function
function saveInvestigation() {
    console.log('saveInvestigation() function called');
    const button = $('.btn-warning[onclick="saveInvestigation()"]');
    const form = $('#investigationFormElement');
    
    if (form.length === 0) {
        console.error('investigationFormElement not found');
        toastr.error('Investigation form not found');
        return;
    }
    
    // Validate required fields
    const serviceId = form.find('#selected_service_id').val();
    const quantity = form.find('input[name="quantity"]').val();
    
    if (!serviceId || !quantity) {
        toastr.warning('Please select a service and enter quantity.');
        return;
    }
    
    // Check if service requires form and validate required form fields
    const requiresForm = $('#selected_service_id').data('requires-form');
    if (requiresForm) {
        const dynamicFormContainer = $('#dynamic-form-container');
        const formDisplayContainer = $('#form-display-container');
        
        // Function to validate required fields in a container
        function validateContainer(container) {
            let hasErrors = false;
            const processed = new Set();

            container.find('[required]').each(function() {
                const field = $(this);
                const name = field.attr('name');
                const type = field.attr('type');

                // If no name, treat as individual required field
                if (!name) {
                    const value = field.val() ? field.val().toString().trim() : '';
                    if (!value) {
                        hasErrors = true;
                        field.addClass('is-invalid');
                        if (!field.next('.invalid-feedback').length) {
                            field.after('<div class="invalid-feedback">This field is required.</div>');
                        }
                    } else {
                        field.removeClass('is-invalid');
                        field.next('.invalid-feedback').remove();
                    }
                    return;
                }

                // Avoid validating the same named group multiple times
                if (processed.has(name)) {
                    return;
                }
                processed.add(name);

                // Radio group validation
                if (type === 'radio') {
                    const checked = container.find('input[name="' + name + '"]:checked').length > 0;
                    const group = container.find('input[name="' + name + '"]');
                    if (!checked) {
                        hasErrors = true;
                        group.addClass('is-invalid');
                        // add message after the last element in the group
                        const last = group.last();
                        if (!last.next('.invalid-feedback').length) {
                            last.after('<div class="invalid-feedback">Please select an option.</div>');
                        }
                    } else {
                        group.removeClass('is-invalid');
                        group.last().next('.invalid-feedback').remove();
                    }
                    return;
                }

                // Checkbox group validation (name ending with [])
                if (type === 'checkbox' && name.endsWith('[]')) {
                    const checked = container.find('input[name="' + name + '"]:checked').length > 0;
                    const group = container.find('input[name="' + name + '"]');
                    if (!checked) {
                        hasErrors = true;
                        group.addClass('is-invalid');
                        const last = group.last();
                        if (!last.next('.invalid-feedback').length) {
                            last.after('<div class="invalid-feedback">Please select at least one option.</div>');
                        }
                    } else {
                        group.removeClass('is-invalid');
                        group.last().next('.invalid-feedback').remove();
                    }
                    return;
                }

                // Single checkbox required
                if (type === 'checkbox') {
                    if (!field.is(':checked')) {
                        hasErrors = true;
                        field.addClass('is-invalid');
                        if (!field.next('.invalid-feedback').length) {
                            field.after('<div class="invalid-feedback">This checkbox is required.</div>');
                        }
                    } else {
                        field.removeClass('is-invalid');
                        field.next('.invalid-feedback').remove();
                    }
                    return;
                }

                // Default: text/select/textarea
                const value = field.val() ? field.val().toString().trim() : '';
                if (!value) {
                    hasErrors = true;
                    field.addClass('is-invalid');
                    if (!field.next('.invalid-feedback').length) {
                        field.after('<div class="invalid-feedback">This field is required.</div>');
                    }
                } else {
                    field.removeClass('is-invalid');
                    field.next('.invalid-feedback').remove();
                }
            });

            return hasErrors;
        }
        
        let hasErrors = false;
        
        // Check dynamic form container
        if (dynamicFormContainer.is(':visible')) {
            hasErrors = validateContainer(dynamicFormContainer) || hasErrors;
        }
        
        // Check form display container
        if (formDisplayContainer.is(':visible')) {
            hasErrors = validateContainer(formDisplayContainer) || hasErrors;
        }
        
        // Check if we have any form displayed at all
        if (!dynamicFormContainer.is(':visible') && !formDisplayContainer.is(':visible')) {
            console.log('No form container visible - requiresForm:', requiresForm);
            toastr.error('This service requires additional form data. Please fill in all required fields.');
            return;
        }
        
        if (hasErrors) {
            console.log('Form validation errors found');
            toastr.error('Please fill in all required form fields.');
            return;
        }
        
        console.log('Form validation passed - continuing with submission');
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
    
    // Collect form data including dynamic form fields
    const formData = collectInvestigationFormData(form);
    
    // Debug logging
    console.log('Collected form data:', formData);
    if (formData.clinical_data) {
        console.log('Clinical data JSON:', formData.clinical_data);
        try {
            const parsedData = JSON.parse(formData.clinical_data);
            console.log('Parsed clinical data:', parsedData);
        } catch (e) {
            console.error('Error parsing clinical data:', e);
        }
    }
    
    // Process investigation order
    processInvestigationOrder(form, button, formData);
}

// Collect all form data including dynamic fields
function collectInvestigationFormData(form) {
    const formData = {};
    
    // Collect basic form data (include empty fields)
    form.find('input, select, textarea').not('#dynamic-form-container input, #dynamic-form-container select, #dynamic-form-container textarea').each(function() {
        const field = $(this);
        const name = field.attr('name');
        const type = field.attr('type');
        let value = field.val();

        if (!name) return;

        // Handle checkboxes
        if (type === 'checkbox') {
            if (name.endsWith('[]')) {
                const baseName = name.replace('[]', '');
                if (!formData[baseName]) {
                    formData[baseName] = [];
                }
                if (field.is(':checked')) {
                    formData[baseName].push(field.val());
                }
            } else {
                formData[name] = field.is(':checked') ? field.val() : '';
            }
            return;
        }

        // Handle radios: set when checked, ensure a placeholder exists
        if (type === 'radio') {
            if (field.is(':checked')) {
                formData[name] = value;
            } else if (!(name in formData)) {
                formData[name] = '';
            }
            return;
        }

        // Other inputs (include empty string if empty)
        formData[name] = (value === null || value === undefined) ? '' : value;
    });
    
    // Collect dynamic form data if present - check both containers
    const dynamicFormContainer = $('#dynamic-form-container');
    const formDisplayContainer = $('#form-display-container');
    let dynamicData = {};
    
    // Function to collect data from a container
    function collectFromContainer(container) {
        // Handle regular form fields
        container.find('input, select, textarea').each(function() {
            const field = $(this);
            const name = field.attr('name');
            let value = field.val();
            
            if (name) {
                // Handle checkboxes
                if (field.attr('type') === 'checkbox') {
                    // Handle checkbox groups (name ends with [])
                    if (name.endsWith('[]')) {
                        const baseName = name.replace('[]', '');
                        if (field.is(':checked')) {
                            if (!dynamicData[baseName]) {
                                dynamicData[baseName] = [];
                            }
                            dynamicData[baseName].push(field.val());
                        }
                    } else {
                        // Handle single checkboxes
                        value = field.is(':checked') ? field.val() : null;
                        if (value !== null) {
                            dynamicData[name] = value;
                        }
                    }
                } else {
                    // Handle radio buttons specially: only take the value if the radio is checked
                    if (field.attr('type') === 'radio') {
                        if (field.is(':checked')) {
                            dynamicData[name] = value;
                        }
                    } else {
                        // Handle other field types
                        if (value !== null && value !== '') {
                            dynamicData[name] = value;
                        }
                    }
                }
            }
        });
    }
    
    // Check both possible form containers
    if (dynamicFormContainer.is(':visible')) {
        collectFromContainer(dynamicFormContainer);
    }
    
    if (formDisplayContainer.is(':visible')) {
        collectFromContainer(formDisplayContainer);
    }
    
    // Add dynamic data as JSON string
    if (Object.keys(dynamicData).length > 0) {
        formData['clinical_data'] = JSON.stringify(dynamicData);
    }
    
    return formData;
}

// Process investigation order
function processInvestigationOrder(form, button, additionalData) {
    let formData;
    
    // If additionalData is provided (from collectInvestigationFormData), use it
    if (additionalData && Object.keys(additionalData).length > 0) {
        formData = $.param(additionalData);
    } else {
        formData = form.serialize();
    }
    
    // Debug logging
    console.log('Form data being sent to server:', formData);
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}/investigations`,
            method: 'POST',
            data: formData,
            beforeSend: function(xhr) {
                console.log('Sending AJAX request with data:', formData);
            }
        }).done(function(response) {
            console.log('Investigation saved successfully:', response);
            toastr.success('Investigation ordered successfully!');
            // Mark the tab as saved to remove the yellow dot
            markFormAsSaved('investigationFormElement', 'saveInvestigationBtn', 'investigations');
            // Reset form
            form[0].reset();
            $('#selected_service_id').val('');
            $('#selected_service_id').removeData('requires-form');
            $('#selected_service_id').removeData('form-type');
            $('#service_search').val('');
            $('#service-info').hide();
            // Hide form type info
            hideFormTypeInfo();
            // Close form
            $('#investigationForm').collapse('hide');
            // Refresh investigations table dynamically
            loadInvestigations();
            resolve(response);
        }).fail(function(xhr) {
            console.error('Investigation save error:', xhr);
            console.error('Response status:', xhr.status);
            console.error('Response text:', xhr.responseText);
            console.error('Response JSON:', xhr.responseJSON);
            let errorMessage = 'Failed to order investigation.';
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
                buttonText.text('Order Investigation');
            } else {
                button.html('<i class="fas fa-save"></i> Order Investigation');
            }
        });
    });
}

// Remove investigation function
function removeInvestigation(investigationId) {
    if (!confirm('Are you sure you want to remove this investigation?')) {
        return;
    }
    
    console.log('Removing investigation:', investigationId);
    
    $.ajax({
        url: `/consultations/investigations/${investigationId}`,
        method: 'DELETE',
        success: function(response) {
            console.log('Investigation removed successfully:', response);
            toastr.success('Investigation removed successfully!');
            
            // Mark the tab as saved to remove the yellow dot
            markFormAsSaved('investigationFormElement', 'saveInvestigationBtn', 'investigations');
            
            // Refresh investigations table
            loadInvestigations();
        },
        error: function(xhr) {
            console.error('Remove investigation error:', xhr.responseJSON);
            
            let errorMessage = 'Failed to remove investigation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            
            toastr.error(errorMessage);
        }
    });
}

// View investigation function
function viewInvestigation(investigationId) {
    console.log('Viewing investigation:', investigationId);
    
    // Validate investigation ID
    if (!investigationId || investigationId <= 0) {
        toastr.error('Invalid investigation ID');
        return;
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('investigationDetailsModal'));
    modal.show();
    
    // Show loading state
    const contentDiv = document.getElementById('investigationDetailsContent');
    contentDiv.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading investigation details...</p>
            </div>
        </div>
    `;
    
    // Update the "View Full Details" button link
    document.getElementById('viewFullInvestigation').href = `/investigations/${investigationId}`;
    
    // Fetch investigation details via AJAX
    $.ajax({
        url: `/investigations/${investigationId}`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 10000, // 10 seconds timeout
        success: function(response) {
            console.log('Investigation details loaded:', response);
            
            if (response.success && response.data) {
                const investigation = response.data;
                
                // Validate essential data
                if (!investigation.service_name) {
                    contentDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Investigation data is incomplete. Some information may not be available.
                        </div>
                    `;
                    return;
                }
                
                // Format the status badge
                const statusBadge = getStatusBadge(investigation.status);
                const priorityBadge = getPriorityBadge(investigation.priority);
                
                // Format the created date
                const createdDate = new Date(investigation.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Build the content HTML
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-flask"></i> Investigation Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td>#${investigation.id}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Service:</strong></td>
                                            <td>
                                                ${investigation.service_name}
                                                ${investigation.service_code ? '<br><small class="text-muted">Code: ' + investigation.service_code + '</small>' : ''}
                                                ${investigation.service_category ? '<br><small class="text-muted">Category: ' + investigation.service_category + '</small>' : ''}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quantity:</strong></td>
                                            <td>${investigation.quantity}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Priority:</strong></td>
                                            <td>${priorityBadge}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>${statusBadge}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ordered:</strong></td>
                                            <td>${createdDate}</td>
                                        </tr>
                                        ${investigation.doctor ? `
                                        <tr>
                                            <td><strong>Ordered by:</strong></td>
                                            <td>${investigation.doctor.name}</td>
                                        </tr>
                                        ` : ''}
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-dollar-sign"></i> Pricing & Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td><strong>Unit Price:</strong></td>
                                            <td>$${parseFloat(investigation.price || 0).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total:</strong></td>
                                            <td><strong>$${parseFloat(investigation.total_price || 0).toFixed(2)}</strong></td>
                                        </tr>
                                        ${investigation.collected_at ? `
                                        <tr>
                                            <td><strong>Collected:</strong></td>
                                            <td><small>${new Date(investigation.collected_at).toLocaleDateString('en-US', {
                                                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                                            })}</small></td>
                                        </tr>
                                        ` : ''}
                                        ${investigation.resulted_at ? `
                                        <tr>
                                            <td><strong>Resulted:</strong></td>
                                            <td><small>${new Date(investigation.resulted_at).toLocaleDateString('en-US', {
                                                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                                            })}</small></td>
                                        </tr>
                                        ` : ''}
                                    </table>
                                    
                                    ${investigation.notes ? `
                                        <div class="mt-3">
                                            <strong>Clinical Notes:</strong>
                                            <div class="alert alert-info mt-2">
                                                ${investigation.notes}
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${investigation.has_results ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> Results Available</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-success mb-2">
                                            <i class="fas fa-check-circle"></i> 
                                            ${investigation.results} result${investigation.results > 1 ? 's are' : ' is'} available for this investigation.
                                        </p>
                                        <small class="text-muted">Click "View Full Details" to see complete results and report.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : investigation.status === 'resulted' ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-clock"></i> Results Pending</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-warning mb-0">
                                            <i class="fas fa-hourglass-half"></i> 
                                            This investigation is marked as resulted but detailed results are not yet available.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                `;
                
                contentDiv.innerHTML = content;
                
                // Check if investigation has form data and display the form
                if (investigation.form_type || investigation.clinical_data) {
                    displayInvestigationForm(investigation);
                }
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Failed to load investigation details.
                    </div>
                `;
            }
        },
        error: function(xhr) {
            console.error('Failed to load investigation details:', xhr.responseJSON);
            
            let errorMessage = 'Failed to load investigation details.';
            let errorClass = 'alert-danger';
            
            if (xhr.status === 404) {
                errorMessage = 'Investigation not found. It may have been deleted or you may not have permission to view it.';
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to view this investigation.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred while loading investigation details. Please try again later.';
            } else if (xhr.status === 0) {
                errorMessage = 'Network error. Please check your internet connection and try again.';
                errorClass = 'alert-warning';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            contentDiv.innerHTML = `
                <div class="alert ${errorClass}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error:</strong> ${errorMessage}
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="viewInvestigation(${investigationId})">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            `;
        },
        timeout: function() {
            contentDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    <strong>Timeout:</strong> Request timed out. Please check your connection and try again.
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="viewInvestigation(${investigationId})">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            `;
        }
    });
}

// Display investigation form in the modal
function displayInvestigationForm(investigation) {
    console.log('Displaying investigation form for:', investigation);
    
    // Create form display section if it doesn't exist
    let formSection = document.getElementById('investigation-form-display');
    if (!formSection) {
        formSection = document.createElement('div');
        formSection.id = 'investigation-form-display';
        formSection.className = 'mt-4';
        document.getElementById('investigationDetailsContent').appendChild(formSection);
    }
    
    // If there's clinical data, try to parse and display it
    if (investigation.clinical_data) {
        try {
            const clinicalData = typeof investigation.clinical_data === 'string' 
                ? JSON.parse(investigation.clinical_data) 
                : investigation.clinical_data;
            
            if (Object.keys(clinicalData).length > 0) {
                displayClinicalDataPreview(formSection, clinicalData);
            }
        } catch (e) {
            console.error('Error parsing clinical data:', e);
        }
    }
    
    // If there's a form_type, load and display the form
    if (investigation.form_type || investigation.service_form_type) {
        const formType = investigation.form_type || investigation.service_form_type;
        loadInvestigationFormInModal(formSection, formType, investigation);
    }
}

// Display clinical data preview
function displayClinicalDataPreview(container, clinicalData) {
    const dataHtml = `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-file-medical"></i> Clinical Data Submitted</h6>
                    </div>
                    <div class="card-body">
                        <div class="clinical-data-display">
                            ${formatClinicalDataForDisplay(clinicalData)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = dataHtml + container.innerHTML;
}

// Format clinical data for display
function formatClinicalDataForDisplay(data) {
    let html = '<div class="row">';
    
    Object.entries(data).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            let displayValue = value;
            
            // Handle different value types
            if (Array.isArray(value)) {
                displayValue = value.join(', ');
            } else if (typeof value === 'boolean') {
                displayValue = value ? 'Yes' : 'No';
            } else if (key.toLowerCase().includes('date') && value) {
                displayValue = new Date(value).toLocaleDateString();
            }
            
            html += `
                <div class="col-md-6 mb-2">
                    <strong>${label}:</strong> ${displayValue}
                </div>
            `;
        }
    });
    
    html += '</div>';
    return html;
}

// Load investigation form in modal
function loadInvestigationFormInModal(container, formType, investigation) {
    console.log('Loading form in modal:', formType);
    
    // Show loading state
    const loadingHtml = `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-file-alt"></i> Investigation Form: ${formType}</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Loading form...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML += loadingHtml;
    
    // Prepare URL with investigation ID for context
    let url = '/api/investigation-form/' + formType;
    if (investigation.consultation_id) {
        url += '?consultation_id=' + investigation.consultation_id;
    }
    if (investigation.id) {
        url += (url.includes('?') ? '&' : '?') + 'investigation_id=' + investigation.id;
    }
    
    // Load the form via AJAX
    $.get(url)
        .done(function(data) {
            // Replace loading with actual form
            const formHtml = `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-secondary">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-file-alt"></i> Investigation Form: ${formType}</h6>
                            </div>
                            <div class="card-body">
                                <div class="investigation-form-readonly">
                                    ${data}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Replace the loading section
            const loadingCard = container.querySelector('.card.border-secondary');
            if (loadingCard) {
                loadingCard.outerHTML = formHtml;
            } else {
                container.innerHTML += formHtml;
            }
            
            // Make the form read-only (disable all inputs)
            makeFormReadOnly(container);
            
            // If we have clinical data, populate the form
            if (investigation.clinical_data) {
                try {
                    const clinicalData = typeof investigation.clinical_data === 'string' 
                        ? JSON.parse(investigation.clinical_data) 
                        : investigation.clinical_data;
                    populateFormWithData(container, clinicalData);
                } catch (e) {
                    console.error('Error populating form with clinical data:', e);
                }
            }
        })
        .fail(function() {
            // Show fallback message
            const fallbackHtml = `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Form Preview Not Available</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    The <strong>${formType}</strong> form template could not be loaded for preview.
                                    The form was used during the investigation process.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Replace the loading section
            const loadingCard = container.querySelector('.card.border-secondary');
            if (loadingCard) {
                loadingCard.outerHTML = fallbackHtml;
            } else {
                container.innerHTML += fallbackHtml;
            }
        });
}

// Make form read-only
function makeFormReadOnly(container) {
    const formContainer = container.querySelector('.investigation-form-readonly');
    if (formContainer) {
        // Disable all form inputs
        formContainer.querySelectorAll('input, select, textarea, button').forEach(element => {
            if (element.tagName.toLowerCase() !== 'button' || element.type === 'submit') {
                element.disabled = true;
                element.readOnly = true;
            }
        });
        
        // Hide any action buttons that aren't needed for viewing
        formContainer.querySelectorAll('button[type="submit"], .btn-warning, .btn-success').forEach(button => {
            button.style.display = 'none';
        });
        
        // Add a visual indicator that this is read-only
        const indicator = document.createElement('div');
        indicator.className = 'alert alert-info alert-sm mb-3';
        indicator.innerHTML = '<i class="fas fa-eye"></i> <strong>View Mode:</strong> This form is displayed as it was submitted during the investigation.';
        formContainer.insertBefore(indicator, formContainer.firstChild);
    }
}

// Populate form with clinical data
function populateFormWithData(container, data) {
    const formContainer = container.querySelector('.investigation-form-readonly');
    if (!formContainer) return;
    
    Object.entries(data).forEach(([key, value]) => {
        // Find form elements by name
        const elements = formContainer.querySelectorAll(`[name="${key}"], [name="${key}[]"]`);
        
        elements.forEach(element => {
            if (element.type === 'checkbox' || element.type === 'radio') {
                if (Array.isArray(value)) {
                    element.checked = value.includes(element.value);
                } else {
                    element.checked = element.value === value || value === true || value === 'on';
                }
            } else if (element.tagName.toLowerCase() === 'select') {
                element.value = value;
            } else if (element.type === 'textarea' || element.type === 'text' || element.type === 'number') {
                element.value = value;
            }
        });
    });
}

// Helper function to get status badge HTML
function getStatusBadge(status) {
    const statusMap = {
        'ordered': { class: 'bg-primary', text: 'Ordered' },
        'paid': { class: 'bg-info', text: 'Paid' },
        'collected': { class: 'bg-warning text-dark', text: 'Sample Collected' },
        'processing': { class: 'bg-warning text-dark', text: 'Processing' },
        'resulted': { class: 'bg-success', text: 'Results Available' },
        'cancelled': { class: 'bg-danger', text: 'Cancelled' }
    };
    
    const statusInfo = statusMap[status] || { class: 'bg-secondary', text: 'Unknown' };
    return `<span class="badge ${statusInfo.class}">${statusInfo.text}</span>`;
}

// Helper function to get priority badge HTML
function getPriorityBadge(priority) {
    const priorityMap = {
        'routine': { class: 'bg-secondary', text: 'Routine' },
        'urgent': { class: 'bg-warning text-dark', text: 'Urgent' },
        'stat': { class: 'bg-danger', text: 'STAT' }
    };
    
    const priorityInfo = priorityMap[priority] || { class: 'bg-secondary', text: 'Routine' };
    return `<span class="badge ${priorityInfo.class}">${priorityInfo.text}</span>`;
}

// Load investigations dynamically
function loadInvestigations() {
    console.log('Loading investigations...');
    
    $.ajax({
        url: `/consultations/${window.consultationId}/investigations-partial`,
        method: 'GET',
        success: function(response) {
            console.log('Investigations loaded:', response);
            
            if (response.success && response.html) {
                // Update the investigations container with the HTML
                $('#investigations-table-container').html(response.html);
            } else {
                console.error('Invalid response format:', response);
                toastr.error('Failed to load investigations');
            }
        },
        error: function(xhr) {
            console.error('Load investigations error:', xhr.responseJSON);
            toastr.error('Failed to load investigations');
        }
    });
}

// Helper function to get priority color
function getPriorityColor(priority) {
    switch (priority.toLowerCase()) {
        case 'stat':
            return 'danger';
        case 'urgent':
            return 'warning';
        case 'routine':
        default:
            return 'info';
    }
}

// Helper function to get status color
function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Initialize investigations tracking and loading
$(document).ready(function() {
    // Initialize investigations tracking if the module is loaded
    if (typeof initializeInvestigationsTracking === 'function') {
        initializeInvestigationsTracking();
    }
    
    // Load investigations when the investigations tab is clicked
    // $('a[href="#investigations"]').on('click', function() {
    //     setTimeout(() => {
    //         loadInvestigations();
    //     }, 100);
    // });
});

// Update total price when quantity changes
function updateTotalPrice() {
    const quantity = parseFloat($('#investigation_quantity').val()) || 1;
    const servicePrice = parseFloat($('#selected_service_id').data('service-price')) || 0;
    const hasPricing = $('#selected_service_id').data('has-pricing');
    
    if (hasPricing && servicePrice > 0) {
        const totalPrice = quantity * servicePrice;
        
        // Update the service info display to show total price
        const serviceInfo = $('#service-info');
        const currentHtml = serviceInfo.html();
        
        if (currentHtml) {
            // Update the price badge to show total
            const updatedHtml = currentHtml.replace(
                /TSh [\d,]+\.?\d*/,
                `TSh ${servicePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} × ${quantity} = TSh ${totalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
            );
            serviceInfo.html(updatedHtml);
        }
    }
}
