/**
 * Vitals Modal - Reusable Component
 * Handles full vitals display, history, and updates
 */

(function() {
    'use strict';

    // Modal context to track current visit
    window.vitalsModalContext = {
        visitId: null,
        patientId: null,
        patientName: null,
        currentVitals: null
    };

    /**
     * Open the vitals modal for a visit
     * @param {Object} visit - Visit object with id
     * @param {Object} patient - Patient object with id and name
     */
    window.openVitalsModal = function(visit, patient) {
        // console.log('[Vitals Modal] Opening for visit:', visit, 'patient:', patient);
        
        // Store context
        window.vitalsModalContext.visitId = visit.id;
        window.vitalsModalContext.patientId = patient.id;
        window.vitalsModalContext.patientName = patient.name;
        
        // Update modal UI
        $('#vitalsPatientName').text(patient.name);
        $('#modal_vitals_visit_id').val(visit.id);
        
        // Hide form initially
        $('#vitalsFormContainer').hide();
        
        // Load current vitals and history
        loadCurrentVitals(visit.id);
        loadVitalsHistory(visit.id);
        
        // Show modal
        const modalElement = document.getElementById('vitalsModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    };

    /**
     * Load current vitals for the visit
     */
    function loadCurrentVitals(visitId) {
        $.ajax({
            url: `/vitals/visit/${visitId}/current`,
            method: 'GET',
            global: false // Don't trigger global error handler - we handle 404 explicitly
        }).done(function(response) {
            // console.log('[Vitals] Current vitals loaded:', response);
            
            if (response && response.vitals) {
                window.vitalsModalContext.currentVitals = response.vitals;
                displayCurrentVitals(response.vitals);
            } else {
                $('#currentVitalsDisplay').html(`
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i> No vitals recorded for this visit yet.
                    </div>
                `);
            }
        }).fail(function(xhr) {
            // 404 is expected when no vitals exist - not an error
            if (xhr.status === 404) {
                // console.log('[Vitals] No vitals recorded yet for this visit');
                $('#currentVitalsDisplay').html(`
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No vitals recorded for this visit yet. Click "Add New Vitals" below to record measurements.
                    </div>
                `);
            } else {
                // Actual error
                console.error('[Vitals] Failed to load current vitals:', xhr);
                $('#currentVitalsDisplay').html(`
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-times-circle"></i> Failed to load current vitals. Please try again.
                    </div>
                `);
            }
        });
    }

    /**
     * Display current vitals in a formatted table
     */
    function displayCurrentVitals(vitals) {
        const recordedAt = vitals.recorded_at ? new Date(vitals.recorded_at).toLocaleString() : 
                          (vitals.created_at ? new Date(vitals.created_at).toLocaleString() : 'N/A');
        
        const html = `
            <div class="row">
                <div class="col-md-12 mb-2">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Recorded: ${recordedAt}
                        ${vitals.recorded_by_name ? `| By: ${vitals.recorded_by_name}` : ''}
                    </small>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-danger" style="font-size: 2rem;">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Blood Pressure</small>
                            <h5 class="mb-0">${vitals.systolic_bp || '-'}/${vitals.diastolic_bp || '-'} <small>mmHg</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-danger" style="font-size: 2rem;">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Pulse Rate</small>
                            <h5 class="mb-0">${vitals.pulse_rate || '-'} <small>bpm</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-warning" style="font-size: 2rem;">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Temperature</small>
                            <h5 class="mb-0">${vitals.temperature || '-'} <small>°C</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-info" style="font-size: 2rem;">
                            <i class="fas fa-lungs"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Respiratory Rate</small>
                            <h5 class="mb-0">${vitals.respiratory_rate || '-'} <small>/min</small></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-primary" style="font-size: 2rem;">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Weight</small>
                            <h5 class="mb-0">${vitals.weight || '-'} <small>kg</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-primary" style="font-size: 2rem;">
                            <i class="fas fa-ruler-vertical"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Height</small>
                            <h5 class="mb-0">${vitals.height || '-'} <small>cm</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-info" style="font-size: 2rem;">
                            <i class="fas fa-wind"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Oxygen Saturation</small>
                            <h5 class="mb-0">${vitals.oxygen_saturation || '-'} <small>%</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-success" style="font-size: 2rem;">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">BMI</small>
                            <h5 class="mb-0">${vitals.bmi || '-'} <small>kg/m²</small></h5>
                        </div>
                    </div>
                </div>
            </div>
            ${vitals.notes ? `
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="alert alert-light mb-0">
                        <strong><i class="fas fa-notes-medical"></i> Notes:</strong>
                        <p class="mb-0 mt-1">${vitals.notes}</p>
                    </div>
                </div>
            </div>
            ` : ''}
        `;
        
        $('#currentVitalsDisplay').html(html);
    }

    /**
     * Load vitals history for the visit
     */
    function loadVitalsHistory(visitId) {
        $.ajax({
            url: `/vitals/visit/${visitId}/history`,
            method: 'GET'
        }).done(function(response) {
            // console.log('[Vitals] History loaded:', response);
            
            if (response && response.history && response.history.length > 0) {
                displayVitalsHistory(response.history);
            } else {
                $('#vitalsHistoryTable').html(`
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No vitals history available for this visit.
                    </div>
                `);
            }
        }).fail(function(xhr) {
            console.error('[Vitals] Failed to load history:', xhr);
            $('#vitalsHistoryTable').html(`
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load vitals history.
                </div>
            `);
        });
    }

    /**
     * Display vitals history in a table
     */
    function displayVitalsHistory(history) {
        let html = `
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Time</th>
                            <th>BP</th>
                            <th>Pulse</th>
                            <th>Temp</th>
                            <th>RR</th>
                            <th>SpO2</th>
                            <th>Weight</th>
                            <th>Height</th>
                            <th>BMI</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        history.forEach(function(vitals) {
            const recordedAt = vitals.recorded_at ? new Date(vitals.recorded_at).toLocaleString() : 
                              (vitals.created_at ? new Date(vitals.created_at).toLocaleString() : 'N/A');
            
            html += `
                <tr>
                    <td><small>${recordedAt}</small></td>
                    <td>${vitals.systolic_bp || '-'}/${vitals.diastolic_bp || '-'}</td>
                    <td>${vitals.pulse_rate || '-'}</td>
                    <td>${vitals.temperature || '-'}</td>
                    <td>${vitals.respiratory_rate || '-'}</td>
                    <td>${vitals.oxygen_saturation || '-'}</td>
                    <td>${vitals.weight || '-'}</td>
                    <td>${vitals.height || '-'}</td>
                    <td>${vitals.bmi || '-'}</td>
                    <td><small>${vitals.recorded_by_name || 'N/A'}</small></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        $('#vitalsHistoryTable').html(html);
    }

    /**
     * Toggle vitals form visibility
     */
    window.toggleVitalsForm = function() {
        const formContainer = $('#vitalsFormContainer');
        
        if (formContainer.is(':visible')) {
            formContainer.slideUp();
            clearVitalsForm();
        } else {
            formContainer.slideDown();
        }
    };

    /**
     * Clear vitals form
     */
    function clearVitalsForm() {
        $('#vitalsForm')[0].reset();
        $('#modal_vitals_visit_id').val(window.vitalsModalContext.visitId);
    }

    /**
     * Auto-calculate BMI when weight or height changes
     */
    $(document).on('input', '#modal_weight, #modal_height', function() {
        const weight = parseFloat($('#modal_weight').val());
        const height = parseFloat($('#modal_height').val()) / 100; // Convert cm to meters
        
        if (weight && height && height > 0) {
            const bmi = weight / (height * height);
            $('#modal_bmi').val(Math.round(bmi * 10) / 10); // Round to 1 decimal place
        } else {
            $('#modal_bmi').val('');
        }
    });

    /**
     * Save vitals from form
     */
    $(document).on('click', '#saveVitalsBtn', function() {
        const button = $(this);
        const form = $('#vitalsForm');
        const visitId = window.vitalsModalContext.visitId;
        
        if (!visitId) {
            toastr.error('Visit ID not found');
            return;
        }
        
        // Show saving state
        button.prop('disabled', true);
        const buttonText = button.find('.btn-text');
        const originalText = buttonText.text();
        buttonText.text('Saving...');
        
        const formData = form.serialize();
        
        $.ajax({
            url: `/vitals/${visitId}`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            }
        }).done(function(response) {
            // console.log('[Vitals] Saved successfully:', response);
            toastr.success('Vital signs saved successfully');
            
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            // Hide form and clear it
            $('#vitalsFormContainer').slideUp();
            clearVitalsForm();
            
            // Reload current vitals and history
            loadCurrentVitals(visitId);
            loadVitalsHistory(visitId);
            
            // Refresh main page vitals display if function exists
            if (typeof window.loadConsultationVitals === 'function') {
                window.loadConsultationVitals();
            }
        }).fail(function(xhr) {
            console.error('[Vitals] Failed to save:', xhr);
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            let message = 'Failed to save vital signs';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                message = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(message);
        });
    });

    /**
     * Clean up when modal is hidden
     */
    $(document).on('hidden.bs.modal', '#vitalsModal', function() {
        // Clear form and hide it
        $('#vitalsFormContainer').hide();
        clearVitalsForm();
    });

    // console.log('[Vitals Modal] Component initialized');

})();
