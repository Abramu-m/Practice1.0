/**
 * Main Consultation Application
 * Initializes all modules and handles global setup
 */

// Initialize consultation application
$(document).ready(function() {
    
    // Setup CSRF token for all jQuery AJAX requests
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }
    
    // Initialize all modules
    initializeConsultationApp();
    
    // Add beforeunload event to warn about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (typeof hasUnsavedChanges === 'function' && hasUnsavedChanges()) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
});

// Initialize all consultation modules
function initializeConsultationApp() {
    
    // Initialize change tracking system
    if (typeof initializeChangeTracking === 'function') {
        initializeChangeTracking();
    }
    
    // Initialize examination handlers
    if (typeof initializeExaminationHandlers === 'function') {
        initializeExaminationHandlers();
    }
    
    // NOTE: Prescription module is now initialized by prescription-modal.js
    // No need to call initializePrescriptionModule() here
    
    // Initialize ICD-10 search
    if (typeof initializeIcd10Search === 'function') {
        initializeIcd10Search();
    }
    
    // Initialize investigation handlers
    if (typeof initializeInvestigationsModule === 'function') {
        initializeInvestigationsModule();
    }
    
    // Fix collapse element initialization
    initializeCollapseElements();
    
}

// Initialize collapse elements to prevent flashing
function initializeCollapseElements() {
    
    // Get all collapse elements
    const collapseElements = document.querySelectorAll('.collapse');
    
    collapseElements.forEach(element => {
        // Ensure proper initial state
        if (!element.classList.contains('show')) {
            element.style.display = 'none';
        }
        
        // Add event listeners to handle visibility properly
        element.addEventListener('show.bs.collapse', function() {
            this.style.display = 'block';
            const forms = this.querySelectorAll('form');
            forms.forEach(form => {
                form.style.visibility = 'visible';
            });
        });
        
        element.addEventListener('hide.bs.collapse', function() {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => {
                form.style.visibility = 'hidden';
            });
        });
        
        element.addEventListener('hidden.bs.collapse', function() {
            this.style.display = 'none';
        });
    });
    
}

// Load initial data for the consultation
function loadInitialData() {
    
    // Load prescriptions if the treatment tab is visible and function is available
    if ($('#prescriptions-list').length > 0 && typeof window.loadPrescriptions === 'function') {
        window.loadPrescriptions();
    }
    
    // Load any other initial data as needed
}

// Global utility functions
function dischargePatient() {
    if (confirm('Are you sure you want to discharge this patient?')) {
        // Implement discharge logic
        toastr.info('Patient discharge functionality to be implemented');
    }
}

// printConsultation removed (use browser Print or specific report routes)

// Global error handler
window.onerror = function(message, source, lineno, colno, error) {
    console.error('Global error:', {
        message: message,
        source: source,
        line: lineno,
        column: colno,
        error: error
    });
    
    // Show user-friendly error message
    toastr.error('An unexpected error occurred. Please refresh the page and try again.');
    
    return false;
};

// Global AJAX error handler
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    console.error('AJAX Error:', {
        url: settings.url,
        method: settings.type,
        status: xhr.status,
        responseText: xhr.responseText,
        thrownError: thrownError
    });
    
    // Don't show error for aborted requests
    if (xhr.status === 0 || xhr.readyState === 0) {
        return;
    }
    
    // Show appropriate error message based on status
    let errorMessage = 'An error occurred while processing your request.';
    
    switch (xhr.status) {
        case 401:
            errorMessage = 'Session expired. Please refresh the page and log in again.';
            break;
        case 403:
            errorMessage = 'Access denied. You do not have permission to perform this action.';
            break;
        case 404:
            errorMessage = 'The requested resource was not found.';
            break;
        case 422:
            errorMessage = 'Please check your input and try again.';
            break;
        case 500:
            errorMessage = 'Internal server error. Please try again later.';
            break;
    }
    
    toastr.error(errorMessage);
});

// Global modal utilities
function hideModal(modalNameOrSelector) {
    try {
        if (!modalNameOrSelector) return false;
        // Support id without #, or full selector
        const selector = modalNameOrSelector.startsWith('#') ? modalNameOrSelector : `#${modalNameOrSelector}`;
        const $modal = $(selector);
        if ($modal.length === 0) return false;

        // Bootstrap 5 API
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            let instance = bootstrap.Modal.getInstance($modal[0]);
            if (!instance) instance = new bootstrap.Modal($modal[0]);
            instance.hide();
            return true;
        }
        // jQuery plugin API (Bootstrap 4 style)
        if (typeof $modal.modal === 'function') {
            $modal.modal('hide');
            return true;
        }
        // Manual fallback
        $modal.removeClass('show').attr('aria-hidden', 'true').css('display', 'none');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
        return true;
    } catch (e) {
        console.warn('hideModal failed:', e);
        return false;
    }
}

function showModal(modalNameOrSelector) {
    try {
        if (!modalNameOrSelector) return false;
        const selector = modalNameOrSelector.startsWith('#') ? modalNameOrSelector : `#${modalNameOrSelector}`;
        const $modal = $(selector);
        if ($modal.length === 0) return false;

        // Bootstrap 5 API
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            let instance = bootstrap.Modal.getInstance($modal[0]);
            if (!instance) instance = new bootstrap.Modal($modal[0]);
            instance.show();
            return true;
        }
        // jQuery plugin API (Bootstrap 4 style)
        if (typeof $modal.modal === 'function') {
            $modal.modal('show');
            return true;
        }
        // Manual fallback
        // Add backdrop if missing
        if ($('.modal-backdrop').length === 0) {
            $('<div class="modal-backdrop fade show"></div>').appendTo(document.body);
        }
        $modal.addClass('show').attr('aria-hidden', 'false').css('display', 'block');
        $('body').addClass('modal-open');
        return true;
    } catch (e) {
        console.warn('showModal failed:', e);
        return false;
    }
}

// Export functions for global access
window.consultationApp = {
    dischargePatient: dischargePatient,
    initializeConsultationApp: initializeConsultationApp,
    hideModal: hideModal,
    showModal: showModal
};

// Also expose as a simple global for convenience
window.hideModal = hideModal;
window.showModal = showModal;
