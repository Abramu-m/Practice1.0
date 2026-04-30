/**
 * Past Medical History Modal - Reusable Component
 * Handles all medical history functionality in a modal context
 */

(function() {
    'use strict';

    // Modal context to track current patient
    window.medicalHistoryModalContext = {
        patientId: null,
        patientName: null
    };

    // Drug allergies management array
    let allergies = [];

    /**
     * Open the medical history modal for a patient
     * @param {Object} patient - Patient object with id and name
     */
    window.openMedicalHistoryModal = function(patient) {
        // console.log('[Medical History Modal] Opening for patient:', patient);
        
        // Store context
        window.medicalHistoryModalContext.patientId = patient.id;
        window.medicalHistoryModalContext.patientName = patient.name;
        
        // Update modal UI
        $('#pastMedicalHistoryPatientName').text(patient.name);
        $('#modalPatientId').val(patient.id);
        
        // Initialize drug allergy Select2 with AJAX
        initializeDrugAllergySelect();
        
        // Load existing medical history for this patient
        loadPatientMedicalHistory(patient.id);
        
        // Load existing drug allergies
        fetchDrugAllergiesList(patient.id);
        
        // Show modal
        const modalElement = document.getElementById('pastMedicalHistoryModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    };

    /**
     * Initialize Select2 for drug allergy dropdown with AJAX search
     */
    function initializeDrugAllergySelect() {
        const selectEl = $('#modalDrugAllergiesSelect');
        
        // Destroy existing Select2 if present
        if (selectEl.data('select2')) {
            selectEl.select2('destroy');
        }
        
        // Initialize with AJAX
        selectEl.select2({
            placeholder: 'Search medications...',
            allowClear: true,
            dropdownParent: $('#pastMedicalHistoryModal'),
            width: '100%',
            minimumInputLength: 2,
            ajax: {
                url: '/api/medications/search',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        query: params.term,
                        limit: 30
                    };
                },
                processResults: function(response) {
                    if (response.success && response.data) {
                        return {
                            results: response.data.map(function(med) {
                                return {
                                    id: med.id,
                                    text: med.generic_name + (med.brand_name ? ' (' + med.brand_name + ')' : '') + 
                                          (med.strength ? ' - ' + med.strength : ''),
                                    medication: med
                                };
                            })
                        };
                    }
                    return { results: [] };
                },
                cache: true
            },
            templateResult: function(item) {
                if (!item.medication) return item.text;
                
                const med = item.medication;
                return $('<div>' +
                    '<div><strong>' + med.generic_name + '</strong>' + 
                    (med.brand_name ? ' <small class="text-muted">(' + med.brand_name + ')</small>' : '') + '</div>' +
                    '<div class="small text-muted">' + 
                    (med.strength ? med.strength + ' ' : '') + 
                    (med.formulation ? med.formulation : '') + 
                    ' <span class="badge bg-info">' + med.stock_quantity + ' in stock</span>' +
                    '</div>' +
                    '</div>');
            },
            templateSelection: function(item) {
                return item.text;
            }
        });
    }

    /**
     * Load existing medical history for a patient
     */
    function loadPatientMedicalHistory(patientId) {
        $.ajax({
            url: `/patients/${patientId}/medical-history`,
            method: 'GET'
        }).done(function(response) {
            // console.log('[Medical History] Loaded:', response);
            
            if (response && response.data) {
                const history = response.data;
                
                // Populate form fields
                $('#modalAllergies').val(history.allergies || '');
                $('#modalChronicConditions').val(history.chronic_conditions || '');
                $('#modalCurrentMedications').val(history.current_medications || '');
                $('#modalPreviousSurgeries').val(history.previous_surgeries || '');
                $('#modalSmokingStatus').val(history.smoking_status || '');
                $('#modalAlcoholUse').val(history.alcohol_use || '');
                $('#modalSocialHistory').val(history.social_history || '');
                $('#modalOccupationalHistory').val(history.occupational_history || '');
                $('#modalFamilyHistory').val(history.family_history || '');
                $('#modalImmunizationHistory').val(history.immunization_history || '');
                $('#modalReproductiveHistory').val(history.reproductive_history || '');
            } else {
                // Clear form if no history exists
                clearMedicalHistoryForm();
            }
        }).fail(function(xhr) {
            console.error('[Medical History] Failed to load:', xhr);
            // If 404, it means no history exists yet - clear the form
            if (xhr.status === 404) {
                clearMedicalHistoryForm();
            } else {
                toastr.error('Failed to load medical history');
            }
        });
    }

    /**
     * Clear all medical history form fields
     */
    function clearMedicalHistoryForm() {
        $('#modalAllergies').val('');
        $('#modalChronicConditions').val('');
        $('#modalCurrentMedications').val('');
        $('#modalPreviousSurgeries').val('');
        $('#modalSmokingStatus').val('');
        $('#modalAlcoholUse').val('');
        $('#modalSocialHistory').val('');
        $('#modalOccupationalHistory').val('');
        $('#modalFamilyHistory').val('');
        $('#modalImmunizationHistory').val('');
        $('#modalReproductiveHistory').val('');
    }

    /**
     * Fetch drug allergies list for the patient
     */
    function fetchDrugAllergiesList(patientId) {
        $.getJSON(`/patients/${patientId}/allergies`, function(resp) {
            // console.log('[Drug Allergies] Fetch response:', resp);
            if (resp && resp.data) {
                allergies = resp.data.map(a => ({
                    ...a, 
                    is_active: a.is_active === 1 || a.is_active === true
                }));
                renderDrugAllergyTags();
            } else {
                console.warn('[Drug Allergies] No data in response');
                allergies = [];
                renderDrugAllergyTags();
            }
        }).fail(function() {
            console.warn('[Drug Allergies] Failed to fetch, initializing empty');
            allergies = [];
            renderDrugAllergyTags();
        });
    }

    /**
     * Get active allergies only
     */
    function activeAllergies() {
        return allergies.filter(a => a.is_active !== false);
    }

    /**
     * Render drug allergy tags
     */
    function renderDrugAllergyTags() {
        // console.log('[Drug Allergies] Rendering tags. Full allergies array:', allergies);
        const actives = activeAllergies();
        const tagsWrap = $('#modalDrugAllergyTags');
        
        tagsWrap.html(actives.map((a) => `
            <span class="badge bg-danger me-1 mb-1 drug-allergy-tag" 
                  data-edit-id="${a.id}" 
                  style="cursor:pointer;font-size:0.8rem;" 
                  title="Click to edit | ${a.reaction || 'No reaction specified'}${a.severity ? ' | ' + a.severity : ''}">
                <span class="me-1">${a.substance_name}</span>
                ${a.severity ? `<span class='badge bg-light text-dark me-1'>${a.severity.charAt(0).toUpperCase() + a.severity.slice(1)}</span>` : ''}
                <button type="button" 
                        class="btn btn-sm btn-link text-white p-0" 
                        data-id="${a.id}" 
                        data-action="remove" 
                        style="line-height:1;">&times;</button>
            </span>
        `).join(''));
        
        // Update hidden input
        $('#modalDrugAllergiesInput').val(actives.map(a => a.substance_name).join(','));
    }

    /**
     * Handle drug allergy tag click events (edit or remove)
     */
    $(document).on('click', '#modalDrugAllergyTags .drug-allergy-tag', function(e) {
        const removeBtn = $(e.target).closest('button[data-action="remove"][data-id]');
        
        if (removeBtn.length) {
            // Remove button clicked
            const id = removeBtn.attr('data-id');
            const allergy = allergies.find(a => a.id == id);
            if (!allergy) return;
            
            if (!confirm(`Deactivate allergy: ${allergy.substance_name}?`)) return;
            
            $.post(`/allergies/${id}/deactivate`, {
                _token: $('input[name="_token"]').val()
            }).done(resp => {
                allergy.is_active = false;
                renderDrugAllergyTags();
                toastr.info('Allergy deactivated');
                
                // Refresh main page display if function exists
                if (typeof window.loadMedicalHistoryDisplay === 'function') {
                    window.loadMedicalHistoryDisplay();
                }
            }).fail(xhr => {
                toastr.error('Failed to deactivate allergy');
            });
        } else {
            // Tag itself clicked - edit mode
            const tag = $(e.target).closest('.drug-allergy-tag[data-edit-id]');
            if (tag.length) {
                const id = tag.attr('data-edit-id');
                const allergy = allergies.find(a => a.id == id);
                if (!allergy) return;
                
                // Open detail modal for editing
                openDrugAllergyDetailModal(allergy, true);
            }
        }
    });

    /**
     * Handle add drug allergy button click
     */
    $(document).on('click', '#modalAddDrugAllergyBtn', function() {
        const selectEl = $('#modalDrugAllergiesSelect');
        const medicationId = selectEl.val();
        
        if (!medicationId) {
            toastr.warning('Please select a medication');
            return;
        }
        
        // Get the selected option's text (medication name)
        const selectedData = selectEl.select2('data')[0];
        const medicationName = selectedData ? selectedData.text : '';
        
        if (!medicationName) {
            toastr.warning('Please select a medication');
            return;
        }
        
        // Check for duplicates
        if (activeAllergies().some(a => a.substance_name.toLowerCase() === medicationName.toLowerCase())) {
            toastr.warning('Drug allergy already recorded.');
            return;
        }
        
        // Clear the select
        selectEl.val(null).trigger('change');
        
        // Open detail modal for new allergy
        openDrugAllergyDetailModal({substance_name: medicationName}, false);
    });

    /**
     * Open drug allergy detail modal for adding reaction/severity
     */
    function openDrugAllergyDetailModal(allergy, isEdit) {
        $('#modalDrugName').text(allergy.substance_name + (isEdit ? ' (edit)' : ''));
        $('#modalReaction').val(isEdit ? (allergy.reaction || '') : '');
        $('#modalSeverity').val(isEdit ? (allergy.severity || '') : '');
        
        const saveBtn = $('#saveModalDrugAllergyBtn');
        saveBtn.attr('data-update-id', isEdit ? allergy.id : '');
        saveBtn.find('.btn-text').text(isEdit ? 'Update Allergy' : 'Add Allergy');
        saveBtn.data('drug-name', allergy.substance_name);
        
        const detailModal = new bootstrap.Modal(document.getElementById('modalDrugAllergyModal'));
        detailModal.show();
    }

    /**
     * Save drug allergy from detail modal
     */
    $(document).on('click', '#saveModalDrugAllergyBtn', function() {
        const saveBtn = $(this);
        const drugName = saveBtn.data('drug-name');
        const reaction = $('#modalReaction').val().trim();
        const severity = $('#modalSeverity').val();
        
        // Validation: severe allergies must have a reaction
        if (severity === 'severe' && !reaction) {
            toastr.error('Reaction is required for severe allergies.');
            return;
        }
        
        saveBtn.prop('disabled', true);
        const btnText = saveBtn.find('.btn-text');
        const origText = btnText.text();
        btnText.text('Saving...');
        
        const patientId = window.medicalHistoryModalContext.patientId;
        const updateId = saveBtn.attr('data-update-id');
        const payload = {
            _token: $('input[name="_token"]').val(),
            substance_name: drugName,
            reaction: reaction,
            severity: severity
        };
        
        let ajaxOpts;
        if (updateId) {
            ajaxOpts = {url: `/allergies/${updateId}`, method: 'PUT', data: payload};
        } else {
            ajaxOpts = {url: `/patients/${patientId}/allergies`, method: 'POST', data: payload};
        }
        
        $.ajax(ajaxOpts).done(resp => {
            if (resp && resp.data) {
                if (updateId) {
                    const idx = allergies.findIndex(a => a.id == updateId);
                    if (idx !== -1) {
                        allergies[idx] = resp.data;
                    } else {
                        allergies.unshift(resp.data);
                    }
                    toastr.success('Allergy updated');
                } else {
                    allergies.unshift(resp.data);
                    toastr.success('Drug allergy added');
                }
                renderDrugAllergyTags();
                
                // Refetch to normalize
                setTimeout(() => {
                    fetchDrugAllergiesList(patientId);
                }, 300);
                
                // Refresh main page display
                if (typeof window.loadMedicalHistoryDisplay === 'function') {
                    window.loadMedicalHistoryDisplay();
                }
            }
            
            // Close detail modal
            bootstrap.Modal.getInstance(document.getElementById('modalDrugAllergyModal')).hide();
        }).fail(xhr => {
            let msg = 'Failed to save allergy';
            if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(msg);
        }).always(() => {
            saveBtn.prop('disabled', false);
            btnText.text(origText);
            $('#modalDrugAllergiesSelect').val('').trigger('change.select2');
            saveBtn.removeAttr('data-update-id');
            saveBtn.removeData('drug-name');
        });
    });

    /**
     * Save medical history from modal
     */
    $(document).on('click', '#savePastMedicalHistoryBtn', function() {
        const button = $(this);
        const form = $('#pastMedicalHistoryForm');
        
        if (form.length === 0) {
            console.error('pastMedicalHistoryForm not found');
            toastr.error('Form not found');
            return;
        }
        
        // Show saving state
        button.prop('disabled', true);
        const buttonText = button.find('.btn-text');
        const originalText = buttonText.text();
        buttonText.text('Saving...');
        
        const formData = form.serialize();
        
        $.ajax({
            url: '/past-medical-history',
            method: 'POST',
            data: formData
        }).done(function(response) {
            toastr.success('Past medical history saved successfully.');
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            // Refresh all medical history displays (profile tab + clinical info tab)
            if (typeof window.refreshAllMedicalHistory === 'function') {
                window.refreshAllMedicalHistory();
            } else if (typeof window.loadMedicalHistoryDisplay === 'function') {
                // Fallback to old function if new one doesn't exist
                window.loadMedicalHistoryDisplay();
            }
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('pastMedicalHistoryModal')).hide();
            
            // console.log('[Medical History] Saved successfully:', response);
        }).fail(function(xhr) {
            console.error('[Medical History] AJAX Error:', xhr);
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            let message = 'Failed to save medical history';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                message = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(message);
        });
    });

    /**
     * Clean up Select2 when modal is hidden
     */
    $(document).on('hidden.bs.modal', '#pastMedicalHistoryModal', function() {
        // Destroy Select2 to prevent memory leaks
        const selectEl = $('#modalDrugAllergiesSelect');
        if (selectEl.data('select2')) {
            selectEl.select2('destroy');
        }
    });

    // console.log('[Medical History Modal] Component initialized');

})();
