/**
 * Change Tracking System for Consultation Forms
 * Handles unsaved changes detection and visual indicators
 */

// Change tracking system
let unsavedChanges = {};
let originalFormData = {};

// Initialize change tracking when document is ready
function initializeChangeTracking() {
    const trackedForms = [
        { id: 'consultationForm', tab: 'clinical-information', button: 'saveConsultationBtn' },
        { id: 'medicalHistoryForm', tab: 'clinical-information', button: 'saveMedicalHistoryBtn' },
        { id: 'diagnosisForm', tab: 'diagnosis', button: 'saveDiagnosisBtn' },
        { id: 'investigationFormElement', tab: 'investigations', button: 'saveInvestigationBtn' },
        { id: 'remarksForm', tab: 'remarks', button: 'saveRemarksBtn' },
        { id: 'quickVitalsFormElement', tab: 'examinations', button: 'saveQuickVitalsBtn' },
        { id: 'systemicExaminationForm', tab: 'examinations', button: 'saveSystemicExamBtn' }
    ];
    
    trackedForms.forEach(form => {
        const formElement = document.getElementById(form.id);
        if (formElement && formElement instanceof HTMLFormElement) {
            console.log('Initializing tracking for form:', form.id);
            // Store original form data
            originalFormData[form.id] = new FormData(formElement);
            
            // Initialize unsaved state for tab if not already set
            if (unsavedChanges[form.tab] === undefined) {
                unsavedChanges[form.tab] = false;
            }
            
            // Add change listeners to all form inputs
            const inputs = formElement.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    console.log('Input change detected in form:', form.id, 'input:', input.name);
                    checkFormChanges(form);
                });
                input.addEventListener('change', () => {
                    console.log('Change event detected in form:', form.id, 'input:', input.name);
                    checkFormChanges(form);
                });
            });
            
            console.log('Change tracking initialized for ' + form.id);
        } else {
            console.warn('Form not found or not a form element:', form.id, formElement);
        }
    });
}

// Check if a specific form has changes
function checkFormChanges(formConfig) {
    const formElement = document.getElementById(formConfig.id);
    if (!formElement) return;
    
    const currentData = new FormData(formElement);
    const originalData = originalFormData[formConfig.id];
    
    let hasChanges = false;
    
    // Compare current data with original
    for (let [key, value] of currentData.entries()) {
        if (originalData.get(key) !== value) {
            hasChanges = true;
            break;
        }
    }
    
    // Check if any original fields are missing in current data
    if (!hasChanges) {
        for (let [key, value] of originalData.entries()) {
            if (currentData.get(key) !== value) {
                hasChanges = true;
                break;
            }
        }
    }
    
    updateChangeState(formConfig.tab, formConfig.button, hasChanges);
}

// Update the visual state of tabs and buttons
function updateChangeState(tabName, buttonId, hasChanges) {
    // For tabs with multiple forms (like examinations), we need to check if any form has changes
    const formsInTab = getFormsInTab(tabName);
    let tabHasChanges = hasChanges;
    
    // If this is a multi-form tab, check if any other forms in the tab have changes
    if (formsInTab.length > 1) {
        tabHasChanges = formsInTab.some(form => {
            if (form.button === buttonId) {
                return hasChanges; // This is the current form being checked
            }
            // Check if other forms in the tab have unsaved changes
            const otherButton = document.getElementById(form.button);
            if (otherButton) {
                const otherUnsavedText = otherButton.querySelector('.unsaved-text');
                return otherUnsavedText && !otherUnsavedText.classList.contains('d-none');
            }
            return false;
        });
    }
    
    unsavedChanges[tabName] = tabHasChanges;
    
    // Update tab indicator
    const tabLink = document.querySelector(`[data-tab="${tabName}"]`);
    if (tabLink) {
        const indicator = tabLink.querySelector('.unsaved-indicator');
        if (indicator) {
            if (tabHasChanges) {
                indicator.classList.remove('d-none');
            } else {
                indicator.classList.add('d-none');
            }
        }
    }
    
    // Update button state
    const button = document.getElementById(buttonId);
    if (button) {
        const unsavedText = button.querySelector('.unsaved-text');
        if (unsavedText) {
            if (hasChanges) {
                unsavedText.classList.remove('d-none');
                button.classList.remove('btn-primary', 'btn-info', 'btn-success');
                button.classList.add('btn-warning');
            } else {
                unsavedText.classList.add('d-none');
                button.classList.remove('btn-warning');
                // Restore original button class based on button ID
                if (buttonId.includes('Vitals')) {
                    button.classList.add('btn-info');
                } else if (buttonId.includes('Exam') || buttonId.includes('MedicalHistory')) {
                    button.classList.add('btn-success');
                } else {
                    button.classList.add('btn-primary');
                }
            }
        }
    }
    
    console.log('Change state updated: ' + tabName + ' = ' + tabHasChanges + ', button ' + buttonId + ' = ' + hasChanges);
}

// Helper function to get all forms in a specific tab
function getFormsInTab(tabName) {
    const allForms = [
        { id: 'consultationForm', tab: 'clinical-information', button: 'saveConsultationBtn' },
        { id: 'medicalHistoryForm', tab: 'clinical-information', button: 'saveMedicalHistoryBtn' },
        { id: 'diagnosisForm', tab: 'diagnosis', button: 'saveDiagnosisBtn' },
        { id: 'investigationFormElement', tab: 'investigations', button: 'saveInvestigationBtn' },
        { id: 'remarksForm', tab: 'remarks', button: 'saveRemarksBtn' },
        { id: 'quickVitalsFormElement', tab: 'examinations', button: 'saveQuickVitalsBtn' },
        { id: 'systemicExaminationForm', tab: 'examinations', button: 'saveSystemicExamBtn' }
    ];
    return allForms.filter(form => form.tab === tabName);
}

// Check if there are any unsaved changes across all forms
function hasUnsavedChanges() {
    return Object.values(unsavedChanges).some(changed => changed === true);
}

// Mark a tab as saved (remove unsaved indicators)
function markTabAsSaved(tabName, buttonId) {
    const formConfig = { tab: tabName, button: buttonId };
    
    // Update original form data
    const formId = getFormIdFromTab(tabName);
    if (formId) {
        const formElement = document.getElementById(formId);
        if (formElement) {
            originalFormData[formId] = new FormData(formElement);
        }
    }
    
    updateChangeState(tabName, buttonId, false);
}

// Mark a specific form as saved (for multi-form tabs like examinations)
function markFormAsSaved(formId, buttonId, tabName) {
    // Update original form data for this specific form
    const formElement = document.getElementById(formId);
    if (formElement) {
        originalFormData[formId] = new FormData(formElement);
    }
    
    // Update button state to not show unsaved
    const button = document.getElementById(buttonId);
    if (button) {
        const unsavedText = button.querySelector('.unsaved-text');
        if (unsavedText) {
            unsavedText.classList.add('d-none');
            button.classList.remove('btn-warning');
            // Restore original button class
            if (buttonId.includes('Vitals')) {
                button.classList.add('btn-info');
            } else if (buttonId.includes('Exam') || buttonId.includes('MedicalHistory')) {
                button.classList.add('btn-success');
            } else {
                button.classList.add('btn-primary');
            }
        }
    }
    
    // Check if tab still has other unsaved forms
    const formsInTab = getFormsInTab(tabName);
    const tabHasChanges = formsInTab.some(form => {
        if (form.button === buttonId) {
            return false; // This form is now saved
        }
        // Check if other forms in the tab have unsaved changes
        const otherButton = document.getElementById(form.button);
        if (otherButton) {
            const otherUnsavedText = otherButton.querySelector('.unsaved-text');
            return otherUnsavedText && !otherUnsavedText.classList.contains('d-none');
        }
        return false;
    });
    
    unsavedChanges[tabName] = tabHasChanges;
    
    // Update tab indicator
    const tabLink = document.querySelector(`[data-tab="${tabName}"]`);
    if (tabLink) {
        const indicator = tabLink.querySelector('.unsaved-indicator');
        if (indicator) {
            if (tabHasChanges) {
                indicator.classList.remove('d-none');
            } else {
                indicator.classList.add('d-none');
            }
        }
    }
    
    console.log('Form ' + formId + ' marked as saved, tab ' + tabName + ' has changes: ' + tabHasChanges);
}

// Helper function to get form ID from tab name (for single-form tabs)
function getFormIdFromTab(tabName) {
    const mapping = {
        'clinical-information': 'consultationForm',
        'diagnosis': 'diagnosisForm',
        'investigations': 'investigationFormElement', 
        'remarks': 'remarksForm'
        // Note: examinations tab has multiple forms, handle separately
    };
    return mapping[tabName];
}

// Add manual change detection trigger for dynamic content
function triggerChangeDetection() {
    const trackedForms = [
        { id: 'consultationForm', tab: 'clinical-information', button: 'saveConsultationBtn' },
        { id: 'medicalHistoryForm', tab: 'clinical-information', button: 'saveMedicalHistoryBtn' },
        { id: 'diagnosisForm', tab: 'diagnosis', button: 'saveDiagnosisBtn' },
        { id: 'investigationFormElement', tab: 'investigations', button: 'saveInvestigationBtn' },
        { id: 'remarksForm', tab: 'remarks', button: 'saveRemarksBtn' },
        { id: 'quickVitalsFormElement', tab: 'examinations', button: 'saveQuickVitalsBtn' },
        { id: 'systemicExaminationForm', tab: 'examinations', button: 'saveSystemicExamBtn' }
    ];
    
    trackedForms.forEach(form => {
        checkFormChanges(form);
    });
}
