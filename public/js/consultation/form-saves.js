/**
 * Form Save Functions for Consultation Module
 * Handles AJAX saves for all consultation forms
 */

// Global helper: convert values like "non_smoker" -> "Non smoker"
function toSentenceCase(text) {
    if (!text) return '';
    const s = String(text).replace(/_/g, ' ').toLowerCase();
    return s.charAt(0).toUpperCase() + s.slice(1);
}

// Enhanced save consultation function
function saveConsultation() {
    
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
    
    return new Promise((resolve, reject) => {
        const jq = $.ajax({
            url: `/consultations/${window.consultationId}`,
            method: 'PUT',
            data: formData
        }).done(function(response) {
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
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}`,
            method: 'PUT',
            data: formData
        }).done(function(response) {
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

    // Serialize form data
    const formData = form.serialize();
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/consultations/${window.consultationId}`,
            method: 'PUT',
            data: formData
        }).done(function(response) {
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
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/past-medical-history',
            method: 'POST',
            data: formData
        }).done(function(response) {
            toastr.success('Past medical history saved successfully.');
            // Mark as saved and restore button
            markFormAsSaved('medicalHistoryForm', 'saveMedicalHistoryBtn', 'clinical-information');
            button.prop('disabled', false);
            buttonText.text(originalText);
            // Update the medical history displays with the new data
            updateMedicalHistoryDisplays(response.data);
            // Close modal after successful save
            if (typeof hideModal === 'function') {
                hideModal('editMedicalHistoryModal');
            } else {
                // Legacy fallback (if global helper not loaded for some reason)
                const $modal = $('#editMedicalHistoryModal');
                if ($modal.length && typeof $modal.modal === 'function') { $modal.modal('hide'); }
            }
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


// Initialize controls when the medical history modal opens
$(document).on('shown.bs.modal', '#editMedicalHistoryModal', function () {
    // Initialize Select2 within modal context to ensure dropdown overlays correctly
    if ($.fn && $.fn.select2) {
        const $sel = $('#drugAllergiesSelect');
        if ($sel.length) {
            $sel.select2({
                dropdownParent: $('#editMedicalHistoryModal'),
                width: '100%',
                allowClear: true,
                placeholder: $sel.data('placeholder') || 'Search and select medication'
            });
        }
    }
});

// Function to update medical history displays
function updateMedicalHistoryDisplays(medicalHistory) {
    
    // Helper function to get smoking status badge class
    const getSmokingBadgeClass = (status) => {
        return status === 'non_smoker' ? 'success' : 'warning';
    };
    
    // Helper function to get alcohol use badge class
    const getAlcoholBadgeClass = (use) => {
        return use === 'none' ? 'success' : 'warning';
    };
    
    // toSentenceCase is now a global helper (see top of file)

    // Helper function to limit text length
    const limitText = (text, limit = 50) => {
        if (!text) return '';
        return text.length > limit ? text.substring(0, limit) + '...' : text;
    };
    
    // Update the "Display Current Past Medical History" section
    const medicalHistoryDisplay = $('#medicalHistoryDisplay');
    
    if (medicalHistoryDisplay.length > 0 && medicalHistory) {
        // Build Drug Allergies summary from response; include severity if available.
        // Fallbacks: parse hidden input (names only) or DOM tags/titles for severity.
        let drugAllergies = [];
        if (Array.isArray(medicalHistory.drug_allergies)) {
            // Accept either array of strings or array of objects
            drugAllergies = medicalHistory.drug_allergies
                .filter(Boolean)
                .map(a => {
                    if (typeof a === 'string') return { name: a.trim(), severity: null };
                    // object case: {substance_name, severity}
                    const name = (a.substance_name || a.name || '').trim();
                    const sev = (a.severity || '').trim() || null;
                    return name ? { name, severity: sev } : null;
                })
                .filter(Boolean);
        } else if (typeof medicalHistory.drug_allergies === 'string') {
            drugAllergies = medicalHistory.drug_allergies.split(',').map(s => ({ name: s.trim(), severity: null })).filter(a => a.name);
        }

        if (drugAllergies.length === 0) {
            // Fallback 1: hidden input (names only)
            const hidden = $('#drugAllergiesInput').val();
            if (hidden && typeof hidden === 'string' && hidden.trim().length) {
                drugAllergies = hidden.split(',').map(s => ({ name: s.trim(), severity: null })).filter(a => a.name);
            }
        }

        if (drugAllergies.length === 0) {
            // Fallback 2: parse DOM tags for name and severity
            const $tags = $('#drugAllergyTags .drug-allergy-tag');
            if ($tags.length) {
                drugAllergies = $tags.map(function(){
                    const $tag = $(this);
                    const name = $tag.find('span.me-1').first().text().trim();
                    const sevText = $tag.find('.badge.bg-light.text-dark').first().text().trim();
                    const severity = sevText ? sevText.toLowerCase() : null;
                    return name ? { name, severity } : null;
                }).get().filter(Boolean);
            } else {
                // Fallback 3: parse list titles (name present; severity in title as "| severity")
                const $listBadges = $('#drugAllergiesList span[title]');
                if ($listBadges.length) {
                    drugAllergies = $listBadges.map(function(){
                        const $el = $(this);
                        const name = $el.text().trim();
                        const title = ($el.attr('title') || '').toLowerCase();
                        const parts = title.split('|').map(s => s.trim());
                        const severity = parts.length >= 2 ? (parts[1] || '').trim() : null;
                        return name ? { name, severity: severity || null } : null;
                    }).get().filter(Boolean);
                }
            }
        }

        // Format summary entries with optional (Severity) like Blade
        const formatAllergy = a => a.name + (a.severity ? ` (${toSentenceCase(a.severity)})` : '');

        const displayCount = 4; // match Blade summary count
        const drugSummaryItems = drugAllergies.slice(0, displayCount).map(formatAllergy);
        const drugOverflow = Math.max(drugAllergies.length - displayCount, 0);
        const hasDrugSummary = drugSummaryItems.length > 0;
        const hasOtherAllergies = !!(medicalHistory.allergies && medicalHistory.allergies.trim().length > 0);

        // Compose Allergies block mirroring Blade
        const allergiesBlock = (() => {
            if (!hasDrugSummary && !hasOtherAllergies) {
                return `
                    <small>
                        <strong>Allergies:</strong>
                        <span class="text-muted">None</span>
                    </small>
                `;
            }
            const drugLine = hasDrugSummary
                ? `<p>
                        Drugs: 
                        <span id="quickDrugAllergySummary" class="text-danger" title="Full drug allergy list">
                            ${drugSummaryItems.join(', ')}${drugOverflow > 0 ? ` +${drugOverflow} more ` : ''}
                        </span>
                   </p>`
        : `<p>
            Drugs:
            <span id="quickDrugAllergySummary" class="text-muted">None</span>
           </p>`;
            const otherLine = hasOtherAllergies
                ? `<p>Other:<span class="text-danger ms-1" title="Other allergies full text"> ${limitText(medicalHistory.allergies, 50)}</span></p>`
                : `<p>Other:<span class="text-muted ms-1"> None</span></p>`;
            return `
                <small>
                    <p><strong>Allergies:</strong></p>
                    ${drugLine}
                    ${otherLine}
                </small>
            `;
        })();

        const newMedicalHistoryDisplay = `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-success mb-0"><i class="fas fa-check-circle"></i> Medical History on File</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showModal('editMedicalHistoryModal')">
                        <i class="fas fa-edit"></i> Update
                    </button>
                </div>
                
                <!-- Compact Full PMH Display -->
                <div class="row g-2">
                    <div class="col-12">
                        ${allergiesBlock}
                    </div>

                    <div class="col-6">
                        <small><strong>Chronic Conditions:</strong>
                            ${medicalHistory.chronic_conditions ? 
                                `<span class="text-warning">${limitText(medicalHistory.chronic_conditions, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Current Medications:</strong>
                            ${medicalHistory.current_medications ? 
                                `<span class="text-info">${limitText(medicalHistory.current_medications, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>

                    <div class="col-6">
                        <small><strong>Previous Surgeries:</strong>
                            ${medicalHistory.previous_surgeries ? 
                                `<span class="text-secondary">${limitText(medicalHistory.previous_surgeries, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Family History:</strong>
                            ${medicalHistory.family_history ? 
                                `<span class="text-dark">${limitText(medicalHistory.family_history, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>

                    <div class="col-6">
                        <small>
                            <strong>Smoking:</strong>
                            <span class="badge bg-${getSmokingBadgeClass(medicalHistory.smoking_status)}">
                                ${medicalHistory.smoking_status ? toSentenceCase(medicalHistory.smoking_status) : 'Unknown'}
                            </span>
                            <span class="ms-2"><strong>Alcohol:</strong>
                                <span class="badge bg-${getAlcoholBadgeClass(medicalHistory.alcohol_use)}">
                                    ${medicalHistory.alcohol_use ? toSentenceCase(medicalHistory.alcohol_use) : 'Unknown'}
                                </span>
                            </span>
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Social History:</strong>
                            ${medicalHistory.social_history ? 
                                `<span>${limitText(medicalHistory.social_history, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>

                    <div class="col-6">
                        <small><strong>Occupational History:</strong>
                            ${medicalHistory.occupational_history ? 
                                `<span>${limitText(medicalHistory.occupational_history, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>
                    <div class="col-6">
                        <small><strong>Immunizations:</strong>
                            ${medicalHistory.immunization_history ? 
                                `<span>${limitText(medicalHistory.immunization_history, 80)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>

                    <div class="col-12">
                        <small><strong>Reproductive History:</strong>
                            ${medicalHistory.reproductive_history ? 
                                `<span>${limitText(medicalHistory.reproductive_history, 120)}</span>` : 
                                '<span class="text-muted">None</span>'
                            }
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        medicalHistoryDisplay.html(newMedicalHistoryDisplay);
    }
    
    // Update the "Medical History & Alerts" section (in Patient Profile tab)
    updateMedicalAlertsSection(medicalHistory);

    // After allergies list is (re)fetched, refresh the quick summary from DOM to include severities.
    // This covers cases where the save response doesn't include structured drug allergy data.
    const refreshDrugSummaryFromDom = () => {
        const $summarySpan = $('#quickDrugAllergySummary');
        if ($summarySpan.length === 0) return;
        const $listBadges = $('#drugAllergiesList span[title]');
        if ($listBadges.length === 0) return;

        // Build array of { name, severity }
        const entries = $listBadges.map(function(){
            const $el = $(this);
            const name = $el.text().trim();
            const title = ($el.attr('title') || '').toLowerCase();
            const parts = title.split('|').map(s => s.trim());
            const severity = parts.length >= 2 ? (parts[1] || '').trim() : null;
            return name ? { name, severity: severity || null } : null;
        }).get().filter(Boolean);

        if (entries.length === 0) return;

        const format = a => a.name + (a.severity ? ` (${toSentenceCase(a.severity)})` : '');
        const displayCount = 4;
        const items = entries.slice(0, displayCount).map(format);
        const overflow = Math.max(entries.length - displayCount, 0);
        const text = items.join(', ') + (overflow > 0 ? ` +${overflow} more ` : '');
        // Use text() to avoid HTML injection; keep styling from parent span
        $summarySpan.text(text);
    };

    // Observe changes to the allergies list and refresh summary when items are populated
    const attachObserver = () => {
        const listEl = document.getElementById('drugAllergiesList');
        if (!listEl) return;
        if (listEl.getAttribute('data-summary-observer') === '1') {
            // Already observing in this lifecycle
            return;
        }
        listEl.setAttribute('data-summary-observer', '1');
        const observer = new MutationObserver(() => refreshDrugSummaryFromDom());
        observer.observe(listEl, { childList: true, subtree: true, characterData: true });
        // Try an initial refresh after a short delay to catch first render
        setTimeout(refreshDrugSummaryFromDom, 600);
    };

    // Defer attaching the observer slightly to allow updateMedicalAlertsSection to inject DOM
    setTimeout(attachObserver, 0);
}

// Function to update the Medical Alerts section using specific IDs
function updateMedicalAlertsSection(medicalHistory) {
    // Update Allergies section
    const allergiesSection = $('#allergiesSection');
    if (allergiesSection.length > 0) {
        const otherAllergiesHtml = medicalHistory && medicalHistory.allergies ? 
            `<div class="alert alert-danger py-2 mt-1 mb-0">
                <strong>⚠️</strong> ${medicalHistory.allergies}
            </div>` : '<p class="text-muted mb-2 mt-1">None recorded</p>';
        const newAllergiesHtml = `
            <h6 class="text-danger mb-1"><i class="fas fa-exclamation-circle"></i> Allergies</h6>
            <div id="drugAllergiesDisplay" class="mb-2">
                <strong class="text-danger">Drug Allergies:</strong>
                <div class="mt-1" id="drugAllergiesList"><span class="text-muted">Loading...</span></div>
            </div>
            <div id="otherAllergiesDisplay">
                <strong class="text-danger">Other Allergies:</strong>
                ${otherAllergiesHtml}
            </div>
        `;
        allergiesSection.html(newAllergiesHtml);
        // Refetch drug allergies list
        if(window.fetchDrugAllergiesList) { window.fetchDrugAllergiesList(); }
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
                                    ${toSentenceCase(medicalHistory.smoking_status)}
                                </span>
                            </small>
                        </div>` : ''
                    }
                    ${medicalHistory.alcohol_use ? 
                        `<div class="col-6">
                            <small><strong>Alcohol:</strong> 
                                <span class="badge bg-${medicalHistory.alcohol_use === 'none' ? 'success' : 'warning'}">
                                    ${toSentenceCase(medicalHistory.alcohol_use)}
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
