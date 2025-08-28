/**
 * Form Save Functions for Consultation Module
 * Handles AJAX saves for all consultation forms
 */

// Enhanced save consultation function
function saveConsultation() {
    console.log('saveConsultation() function called');
    
    const form = $('#consultationForm');
    const button = $('#saveConsultationBtn');
    
    if (form.length === 0) {
        console.error('consultationForm not found');
        toastr.error('Form not found');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Saving...');
    
    const formData = form.serialize();
    console.log('Serialized form data:', formData);
    
    return new Promise((resolve, reject) => {
        const jq = $.ajax({
            url: `/consultations/${window.consultationId}`,
            method: 'PUT',
            data: formData
        }).done(function(response) {
            console.log('AJAX success:', response);
            toastr.success('Consultation information saved successfully.');
            // Mark as saved and restore button
            markTabAsSaved('clinical-information', 'saveConsultationBtn');
            button.prop('disabled', false);
            buttonText.text(originalText);
            resolve(response);
        }).fail(function(xhr) {
            console.error('AJAX Error:', xhr);
            console.error('Response Text:', xhr.responseText);
            let errorMessage = 'An error occurred while saving consultation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
            // Restore button
            button.prop('disabled', false);
            buttonText.text(originalText);
            reject(xhr);
        });
    });
}

// Enhanced save diagnosis function
function saveDiagnosis() {
    console.log('saveDiagnosis() function called');
    const button = $('#saveDiagnosisBtn');
    const form = $('#diagnosisForm');
    
    if (form.length === 0) {
        console.error('diagnosisForm not found');
        toastr.error('Form not found');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Saving...');
    
    const formData = form.serialize();
    console.log('Serialized diagnosis form data:', formData);
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}`,
            method: 'PUT',
            data: formData
        }).done(function(response) {
            console.log('Diagnosis AJAX success:', response);
            toastr.success('Diagnosis saved successfully.');
            markTabAsSaved('diagnosis', 'saveDiagnosisBtn');
            button.prop('disabled', false);
            buttonText.text(originalText);
            resolve(response);
        }).fail(function(xhr) {
            console.error('Diagnosis AJAX Error:', xhr);
            console.error('Response Text:', xhr.responseText);
            let errorMessage = 'An error occurred while saving diagnosis.';
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

// Enhanced save remarks function
function saveRemarks() {
    const button = $('#saveRemarksBtn');
    const form = $('#remarksForm');

    if (form.length === 0) {
        console.error('remarksForm not found');
        toastr.error('Form not found');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Saving...');
    console.log('saveRemarks() function called');

    // Serialize form data
    const formData = form.serialize();
    console.log('Serialized form data:', formData);
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}`,
            method: 'PUT',
            data: formData
        }).done(function(response) {
            console.log('AJAX success:', response);
            toastr.success('Remarks information saved successfully.');
            markTabAsSaved('remarks', 'saveRemarksBtn');
            button.prop('disabled', false);
            buttonText.text(originalText);
            resolve(response);
        }).fail(function(xhr) {
            console.error('AJAX Error:', xhr);
            console.error('Response Text:', xhr.responseText);
            let errorMessage = 'An error occurred while saving remarks.';
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

// Enhanced save medical history function
function saveMedicalHistory() {
    console.log('saveMedicalHistory() function called');
    const button = $('#saveMedicalHistoryBtn');
    const form = $('#medicalHistoryForm');
    
    if (form.length === 0) {
        console.error('medicalHistoryForm not found');
        toastr.error('Form not found');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const buttonText = button.find('.btn-text');
    const originalText = buttonText.text();
    buttonText.text('Saving...');
    
    const formData = form.serialize();
    console.log('Serialized medical history form data:', formData);
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/past-medical-history',
            method: 'POST',
            data: formData
        }).done(function(response) {
            console.log('Medical history AJAX success:', response);
            toastr.success('Past medical history saved successfully.');
            // Mark as saved and restore button
            markFormAsSaved('medicalHistoryForm', 'saveMedicalHistoryBtn', 'clinical-information');
            button.prop('disabled', false);
            buttonText.text(originalText);
            // Update the medical history displays with the new data
            updateMedicalHistoryDisplays(response.data);
            // Collapse the form after successful save
            $('#editMedicalHistory').collapse('hide');
            resolve(response);
        }).fail(function(xhr) {
            console.error('Medical history AJAX Error:', xhr);
            console.error('Response Text:', xhr.responseText);
            let errorMessage = 'An error occurred while saving medical history.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
            // Restore button
            button.prop('disabled', false);
            buttonText.text(originalText);
            reject(xhr);
        });
    });
}

// Function to update medical history displays
function updateMedicalHistoryDisplays(medicalHistory) {
    console.log('Updating medical history displays with:', medicalHistory);
    
    // Helper function to get smoking status badge class
    const getSmokingBadgeClass = (status) => {
        return status === 'non_smoker' ? 'success' : 'warning';
    };
    
    // Helper function to get alcohol use badge class
    const getAlcoholBadgeClass = (use) => {
        return use === 'none' ? 'success' : 'warning';
    };
    
    // Helper function to limit text length
    const limitText = (text, limit = 50) => {
        if (!text) return '';
        return text.length > limit ? text.substring(0, limit) + '...' : text;
    };
    
    // Update the "Display Current Past Medical History" section
    const medicalHistoryDisplay = $('#medicalHistoryDisplay');
    
    if (medicalHistoryDisplay.length > 0 && medicalHistory) {
        const newMedicalHistoryDisplay = `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-success mb-0"><i class="fas fa-check-circle"></i> Medical History on File</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editMedicalHistory">
                        <i class="fas fa-edit"></i> Update
                    </button>
                </div>
                
                <!-- Quick Summary -->
                <div class="row">
                    <div class="col-6">
                        <small><strong>Allergies:</strong> 
                            ${medicalHistory.allergies ? 
                                `<span class="text-danger">${limitText(medicalHistory.allergies, 50)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Chronic Conditions:</strong> 
                            ${medicalHistory.chronic_conditions ? 
                                `<span class="text-warning">${limitText(medicalHistory.chronic_conditions, 50)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Smoking:</strong> 
                            <span class="badge bg-${getSmokingBadgeClass(medicalHistory.smoking_status)}">
                                ${medicalHistory.smoking_status ? 
                                    medicalHistory.smoking_status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 
                                    'Unknown'
                                }
                            </span>
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Alcohol:</strong> 
                            <span class="badge bg-${getAlcoholBadgeClass(medicalHistory.alcohol_use)}">
                                ${medicalHistory.alcohol_use ? 
                                    medicalHistory.alcohol_use.charAt(0).toUpperCase() + medicalHistory.alcohol_use.slice(1) : 
                                    'Unknown'
                                }
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        medicalHistoryDisplay.html(newMedicalHistoryDisplay);
    }
    
    // Update the "Medical History & Alerts" section (in Patient Profile tab)
    updateMedicalAlertsSection(medicalHistory);
}

// Function to update the Medical Alerts section using specific IDs
function updateMedicalAlertsSection(medicalHistory) {
    // Update Allergies section
    const allergiesSection = $('#allergiesSection');
    if (allergiesSection.length > 0) {
        const newAllergiesHtml = `
            <h6 class="text-danger"><i class="fas fa-exclamation-circle"></i> Allergies:</h6>
            ${medicalHistory && medicalHistory.allergies ? 
                `<div class="alert alert-danger py-2">
                    <strong>⚠️ ALLERGIC TO:</strong> ${medicalHistory.allergies}
                </div>` : 
                '<p class="text-muted mb-2">No known allergies</p>'
            }
        `;
        allergiesSection.html(newAllergiesHtml);
    }
    
    // Update Chronic Conditions section
    const chronicConditionsSection = $('#chronicConditionsSection');
    if (chronicConditionsSection.length > 0) {
        const newChronicHtml = `
            <h6 class="text-warning"><i class="fas fa-heartbeat"></i> Chronic Conditions:</h6>
            ${medicalHistory && medicalHistory.chronic_conditions ? 
                `<div class="alert alert-warning py-2">
                    ${medicalHistory.chronic_conditions}
                </div>` : 
                '<p class="text-muted mb-2">No chronic conditions recorded</p>'
            }
        `;
        chronicConditionsSection.html(newChronicHtml);
    }
    
    // Update Current Medications section
    const currentMedicationsSection = $('#currentMedicationsSection');
    if (currentMedicationsSection.length > 0) {
        const newMedicationsHtml = `
            <h6 class="text-info"><i class="fas fa-pills"></i> Current Medications:</h6>
            ${medicalHistory && medicalHistory.current_medications ? 
                `<div class="alert alert-info py-2">
                    ${medicalHistory.current_medications}
                </div>` : 
                '<p class="text-muted mb-2">No current medications</p>'
            }
        `;
        currentMedicationsSection.html(newMedicationsHtml);
    }
    
    // Update Previous Surgeries section
    const previousSurgeriesSection = $('#previousSurgeriesSection');
    if (previousSurgeriesSection.length > 0) {
        const newSurgeriesHtml = `
            <h6 class="text-secondary"><i class="fas fa-cut"></i> Previous Surgeries:</h6>
            ${medicalHistory && medicalHistory.previous_surgeries ? 
                `<div class="alert alert-secondary py-2">
                    ${medicalHistory.previous_surgeries}
                </div>` : 
                '<p class="text-muted mb-2">No previous surgeries</p>'
            }
        `;
        previousSurgeriesSection.html(newSurgeriesHtml);
    }
    
    // Update Family History section
    const familyHistorySection = $('#familyHistorySection');
    if (familyHistorySection.length > 0) {
        const newFamilyHtml = `
            <h6 class="text-primary"><i class="fas fa-users"></i> Family History:</h6>
            ${medicalHistory && medicalHistory.family_history ? 
                `<div class="alert alert-light border py-2">
                    ${medicalHistory.family_history}
                </div>` : 
                '<p class="text-muted mb-2">No significant family history</p>'
            }
        `;
        familyHistorySection.html(newFamilyHtml);
    }
    
    // Update Social History section
    const socialHistorySection = $('#socialHistorySection');
    if (socialHistorySection.length > 0) {
        const newSocialHtml = `
            <h6 class="text-dark"><i class="fas fa-user-friends"></i> Social History:</h6>
            ${medicalHistory && (medicalHistory.smoking_status || medicalHistory.alcohol_use || medicalHistory.social_history) ? 
                `<div class="row">
                    ${medicalHistory.smoking_status ? 
                        `<div class="col-6">
                            <small><strong>Smoking:</strong> 
                                <span class="badge bg-${medicalHistory.smoking_status === 'non_smoker' ? 'success' : 'warning'}">
                                    ${medicalHistory.smoking_status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                            </small>
                        </div>` : ''
                    }
                    ${medicalHistory.alcohol_use ? 
                        `<div class="col-6">
                            <small><strong>Alcohol:</strong> 
                                <span class="badge bg-${medicalHistory.alcohol_use === 'none' ? 'success' : 'warning'}">
                                    ${medicalHistory.alcohol_use.charAt(0).toUpperCase() + medicalHistory.alcohol_use.slice(1)}
                                </span>
                            </small>
                        </div>` : ''
                    }
                    ${medicalHistory.social_history ? 
                        `<div class="col-12 mt-2">
                            <small>${medicalHistory.social_history}</small>
                        </div>` : ''
                    }
                </div>` : 
                '<p class="text-muted mb-2">No social history recorded</p>'
            }
        `;
        socialHistorySection.html(newSocialHtml);
    }
}
