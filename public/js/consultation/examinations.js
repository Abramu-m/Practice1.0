/**
 * Examination Module for Consultation
 * Handles Quick Vitals and Systemic Examinations
 */

// Enhanced save quick vitals function
function saveQuickVitals() {
    const button = $('#saveQuickVitalsBtn');
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Saving...');

    // Get form data
    const formData = {
        temperature: $('input[name="temperature"]').val(),
        pulse_rate: $('input[name="pulse_rate"]').val(),
        respiratory_rate: $('input[name="respiratory_rate"]').val(),
        systolic_bp: $('input[name="systolic_bp"]').val(),
        diastolic_bp: $('input[name="diastolic_bp"]').val(),
        oxygen_saturation: $('input[name="oxygen_saturation"]').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    // Make AJAX call and return a Promise
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}/quick-vitals`,
            method: 'POST',
            data: formData
        }).done(function(response) {
            toastr.success('Vital signs saved successfully.');
            markFormAsSaved('quickVitalsFormElement', 'saveQuickVitalsBtn', 'examinations');
            button.prop('disabled', false);
            buttonText.text(originalText);
            updateVitalsPreview(response.vitals);
            $('#quickVitalsForm').collapse('hide');
            resolve(response);
        }).fail(function(xhr) {
            console.error('Quick vitals AJAX Error:', xhr);
            console.error('Response Text:', xhr.responseText);
            let errorMessage = 'An error occurred while saving vital signs.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
            button.prop('disabled', false);
            buttonText.text(originalText);
            reject(xhr);
        });
    });
}

// Function to update vitals preview display
function updateVitalsPreview(vitals) {
    
    if (!vitals) {
        console.warn('No vitals data provided to updateVitalsPreview');
        return;
    }
    
    // Helper function to format values or show default
    const formatValue = (value, defaultText = 'N/A') => {
        return value && value !== '' && value !== null ? value : defaultText;
    };
    
    // Check if we need to replace the "no vitals" section with the vitals table
    const noVitalsAlert = $('.card-body').find('.alert-info:contains("No vital signs recorded yet")');
    if (noVitalsAlert.length > 0) {
        // Replace the entire "no vitals" section with the vitals table
        const vitalsCardBody = noVitalsAlert.closest('.card-body');
        
        // Format the timestamp
        let formattedTimestamp = 'Just recorded';
        if (vitals.recorded_at || vitals.created_at) {
            const timestamp = vitals.recorded_at || vitals.created_at;
            const dateObj = new Date(timestamp);
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            const hours = String(dateObj.getHours()).padStart(2, '0');
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            formattedTimestamp = `${day}/${month}/${year} ${hours}:${minutes}`;
        }
        
        // Format blood pressure
        const systolic = formatValue(vitals.systolic_bp, '');
        const diastolic = formatValue(vitals.diastolic_bp, '');
        let bpDisplay = 'N/A';
        if (systolic && diastolic) {
            bpDisplay = `${systolic}/${diastolic}`;
        } else if (systolic || diastolic) {
            bpDisplay = `${systolic || '?'}/${diastolic || '?'}`;
        }
        
        const newVitalsHtml = `
            <div class="table-responsive mb-3">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Temperature:</strong></td>
                        <td><span id="quick_temperature">${formatValue(vitals.temperature)}</span> °C</td>
                    </tr>
                    <tr>
                        <td><strong>Blood Pressure:</strong></td>
                        <td><span id="quick_bp">${bpDisplay}</span> mmHg</td>
                    </tr>
                    <tr>
                        <td><strong>Pulse Rate:</strong></td>
                        <td><span id="quick_pulse">${formatValue(vitals.pulse_rate)}</span> bpm</td>
                    </tr>
                    <tr>
                        <td><strong>Resp. Rate:</strong></td>
                        <td><span id="quick_respiratory">${formatValue(vitals.respiratory_rate)}</span>/min</td>
                    </tr>
                    <tr>
                        <td><strong>Oxygen Saturation:</strong></td>
                        <td><span id="quick_spo2">${formatValue(vitals.oxygen_saturation)}</span> %</td>
                    </tr>
                    ${vitals.bmi ? `
                    <tr>
                        <td><strong>BMI:</strong></td>
                        <td><span id="quick_bmi">${vitals.bmi}</span></td>
                    </tr>
                    ` : ''}
                </table>
                <small class="text-muted" id="quick_created_at">
                    Recorded: ${formattedTimestamp}
                </small>
            </div>
            
            <div class="d-grid gap-2">
                <a href="/vitals/show/${vitals.visit_id || 'unknown'}" class="btn btn-info btn-sm" target="_blank">
                    <i class="fas fa-chart-line"></i> View Full Vitals & History
                </a>
                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#quickVitalsForm">
                    <i class="fas fa-edit"></i> Quick Update Vitals
                </button>
            </div>
        `;
        
        // Replace the alert and buttons with the vitals table
        noVitalsAlert.nextAll('.d-grid').remove(); // Remove the buttons section
        noVitalsAlert.replaceWith(newVitalsHtml);
        
        // Update the form fields with the saved values
        updateVitalsFormFields(vitals);
        
        return; // Exit early since we've replaced the entire section
    }
    
    // If vitals table already exists, just update the values
    updateExistingVitalsDisplay(vitals);
    
    // Update form fields with the saved values
    updateVitalsFormFields(vitals);
}

// Function to update existing vitals display elements
function updateExistingVitalsDisplay(vitals) {
    // Helper function to format values or show default
    const formatValue = (value, defaultText = 'N/A') => {
        return value && value !== '' && value !== null ? value : defaultText;
    };
    
    // Update individual vital sign displays
    if ($('#quick_temperature').length > 0) {
        $('#quick_temperature').text(formatValue(vitals.temperature));
    }
    
    if ($('#quick_pulse').length > 0) {
        $('#quick_pulse').text(formatValue(vitals.pulse_rate));
    }
    
    if ($('#quick_respiratory').length > 0) {
        $('#quick_respiratory').text(formatValue(vitals.respiratory_rate));
    }
    
    if ($('#quick_bp').length > 0) {
        const systolic = formatValue(vitals.systolic_bp, '');
        const diastolic = formatValue(vitals.diastolic_bp, '');
        if (systolic && diastolic) {
            $('#quick_bp').text(`${systolic}/${diastolic}`);
        } else if (systolic || diastolic) {
            $('#quick_bp').text(`${systolic || '?'}/${diastolic || '?'}`);
        } else {
            $('#quick_bp').text('N/A');
        }
    }
    
    if ($('#quick_spo2').length > 0) {
        $('#quick_spo2').text(formatValue(vitals.oxygen_saturation));
    }
    
    if ($('#quick_bmi').length > 0) {
        $('#quick_bmi').text(formatValue(vitals.bmi));
    }
    
    // Update the recorded timestamp
    if ($('#quick_created_at').length > 0) {
        if (vitals.recorded_at || vitals.created_at) {
            const timestamp = vitals.recorded_at || vitals.created_at;
            const dateObj = new Date(timestamp);
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            const hours = String(dateObj.getHours()).padStart(2, '0');
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            $('#quick_created_at').text(`Recorded: ${day}/${month}/${year} ${hours}:${minutes}`);
        }
    }
}

// Function to update form fields with saved values
function updateVitalsFormFields(vitals) {
    // Update form fields with the saved values (in case there were any modifications by the server)
    if (vitals.temperature !== undefined) {
        $('input[name="temperature"]').val(vitals.temperature);
    }
    if (vitals.pulse_rate !== undefined) {
        $('input[name="pulse_rate"]').val(vitals.pulse_rate);
    }
    if (vitals.respiratory_rate !== undefined) {
        $('input[name="respiratory_rate"]').val(vitals.respiratory_rate);
    }
    if (vitals.systolic_bp !== undefined) {
        $('input[name="systolic_bp"]').val(vitals.systolic_bp);
    }
    if (vitals.diastolic_bp !== undefined) {
        $('input[name="diastolic_bp"]').val(vitals.diastolic_bp);
    }
    if (vitals.oxygen_saturation !== undefined) {
        $('input[name="oxygen_saturation"]').val(vitals.oxygen_saturation);
    }
}

// Enhanced save systemic examination function
function saveSystemicExamination() {
    const button = $('#saveSystemicExamBtn');
    const form = $('#systemicExaminationForm');
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Saving...');

    // Get form data
    const formData = {
        examination_type: form.find('select[name="examination_type"]').val(),
        general_findings: form.find('textarea[name="general_findings"]').val(),
        cardiovascular_system: form.find('textarea[name="cardiovascular_system"]').val(),
        respiratory_system: form.find('textarea[name="respiratory_system"]').val(),
        gastrointestinal_system: form.find('textarea[name="gastrointestinal_system"]').val(),
        nervous_system: form.find('textarea[name="nervous_system"]').val(),
        musculoskeletal_system: form.find('textarea[name="musculoskeletal_system"]').val(),
        genitourinary_system: form.find('textarea[name="genitourinary_system"]').val(),
        endocrine_system: form.find('textarea[name="endocrine_system"]').val(),
        skin_examination: form.find('textarea[name="skin_examination"]').val(),
        psychiatric_assessment: form.find('textarea[name="psychiatric_assessment"]').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    // Make AJAX call and return a Promise
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}/examinations`,
            method: 'POST',
            data: formData
        }).done(function(response) {
            if (response.success) {
                toastr.success(response.message || 'Systemic examination saved successfully.');
                markFormAsSaved('systemicExaminationForm', 'saveSystemicExamBtn', 'examinations');
                button.removeClass('btn-success btn-warning').addClass('btn-success');
            }
            resolve(response);
        }).fail(function(xhr) {
            const errorMessage = xhr.responseJSON?.message || 'Failed to save systemic examination.';
            toastr.error(errorMessage);
            console.error('Systemic examination save error:', xhr.responseJSON);
            reject(xhr);
        }).always(function() {
            // Reset button state
            button.prop('disabled', false);
            buttonText.text(originalText);
        });
    });
}

// Edit existing systemic examination
function editExamination(examinationId) {
    
    // Get examination data via AJAX
    $.ajax({
        url: '/consultations/examinations/' + examinationId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const exam = response.examination;
                
                // Populate the form with existing data
                const form = $('#systemicExaminationForm');
                form.find('select[name="examination_type"]').val(exam.examination_type);
                form.find('textarea[name="general_findings"]').val(exam.general_findings);
                form.find('textarea[name="cardiovascular_system"]').val(exam.cardiovascular_system);
                form.find('textarea[name="respiratory_system"]').val(exam.respiratory_system);
                form.find('textarea[name="gastrointestinal_system"]').val(exam.gastrointestinal_system);
                form.find('textarea[name="nervous_system"]').val(exam.nervous_system);
                form.find('textarea[name="musculoskeletal_system"]').val(exam.musculoskeletal_system);
                form.find('textarea[name="genitourinary_system"]').val(exam.genitourinary_system);
                form.find('textarea[name="endocrine_system"]').val(exam.endocrine_system);
                form.find('textarea[name="skin_examination"]').val(exam.skin_examination);
                form.find('textarea[name="psychiatric_assessment"]').val(exam.psychiatric_assessment);
                
                // Store examination ID for update
                form.data('examination-id', examinationId);
                
                // Update button text and function
                const saveBtn = $('#saveSystemicExamBtn');
                saveBtn.find('.btn-text').text('Update Examination');
                saveBtn.attr('onclick', 'updateSystemicExamination()');
                
                // Show the form
                $('#systemicExamForm').collapse('show');
                
                // Update original form data for change tracking
                originalFormData['systemicExaminationForm'] = new FormData(form[0]);
            }
        },
        error: function(xhr) {
            toastr.error('Failed to load examination data for editing.');
            console.error('Edit examination error:', xhr.responseJSON);
        }
    });
}

// Update existing systemic examination
function updateSystemicExamination() {
    const button = $('#saveSystemicExamBtn');
    const form = $('#systemicExaminationForm');
    const examinationId = form.data('examination-id');
    
    if (!examinationId) {
        toastr.error('No examination ID found for update');
        return Promise.reject(new Error('No examination ID'));
    }
    
    // Show updating state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Updating...');

    // Get form data
    const formData = {
        examination_type: form.find('select[name="examination_type"]').val(),
        general_findings: form.find('textarea[name="general_findings"]').val(),
        cardiovascular_system: form.find('textarea[name="cardiovascular_system"]').val(),
        respiratory_system: form.find('textarea[name="respiratory_system"]').val(),
        gastrointestinal_system: form.find('textarea[name="gastrointestinal_system"]').val(),
        nervous_system: form.find('textarea[name="nervous_system"]').val(),
        musculoskeletal_system: form.find('textarea[name="musculoskeletal_system"]').val(),
        genitourinary_system: form.find('textarea[name="genitourinary_system"]').val(),
        endocrine_system: form.find('textarea[name="endocrine_system"]').val(),
        skin_examination: form.find('textarea[name="skin_examination"]').val(),
        psychiatric_assessment: form.find('textarea[name="psychiatric_assessment"]').val(),
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: 'PUT'
    };

    // Make AJAX call and return a Promise
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/consultations/examinations/' + examinationId,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Systemic examination updated successfully.');
                    markFormAsSaved('systemicExaminationForm', 'saveSystemicExamBtn', 'examinations');
                    
                    // Reset form for new examination
                    resetExaminationForm();
                    
                    // Refresh examinations list partial to show updated examination without reloading
                    if (typeof refreshExaminationsList === 'function') {
                        refreshExaminationsList();
                    }
                }
                resolve(response);
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Failed to update systemic examination.';
                toastr.error(errorMessage);
                console.error('Systemic examination update error:', xhr.responseJSON);
                reject(xhr);
            },
            complete: function() {
                // Reset button state
                button.prop('disabled', false);
                buttonText.text(originalText);
            }
        });
    });
}

// Reset examination form to new mode
function resetExaminationForm() {
    const form = $('#systemicExaminationForm');
    const saveBtn = $('#saveSystemicExamBtn');
    
    // Clear form data
    form[0].reset();
    form.removeData('examination-id');
    
    // Reset button
    saveBtn.find('.btn-text').text('Save Examination');
    saveBtn.attr('onclick', 'saveSystemicExamination()');
    
    // Hide form
    $('#systemicExamForm').collapse('hide');
}

// Initialize examination form collapse handlers
function initializeExaminationHandlers() {
    // Add event listener for when systemic examination form collapse is shown
    $('#systemicExamForm').on('shown.bs.collapse', function () {
        // Reinitialize tracking for the systemic examination form
        const formConfig = { id: 'systemicExaminationForm', tab: 'examinations', button: 'saveSystemicExamBtn' };
        const formElement = document.getElementById(formConfig.id);
        if (formElement) {
            // Store original form data
            originalFormData[formConfig.id] = new FormData(formElement);
            
            // Add change listeners to all form inputs
            const inputs = formElement.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    checkFormChanges(formConfig);
                });
                input.addEventListener('change', () => {
                    checkFormChanges(formConfig);
                });
            });
            
        }
    });
}
