/**
 * Systemic Examination Modal Component
 * Handles systemic examination management in a modal interface
 */

(function() {
    'use strict';

    // Module state
    window.systemicExaminationModalContext = {
        visitId: null,
        consultationId: null,
        patientId: null,
        patientName: '',
        mode: 'visit', // 'visit' or 'consultation'
        editingExaminationId: null
    };

    /**
     * Open systemic examination modal
     * @param {Object} visit - Visit object {id}
     * @param {string} patientName - Patient name for display
     * @param {string} mode - 'visit' or 'consultation'
     * @param {number} consultationId - Optional consultation ID
     */
    window.openSystemicExaminationModal = function(visit, patientName, mode = 'visit', consultationId = null) {
        
        // Store context
        window.systemicExaminationModalContext.visitId = visit.id || visit;
        window.systemicExaminationModalContext.patientName = patientName;
        window.systemicExaminationModalContext.mode = mode;
        window.systemicExaminationModalContext.consultationId = consultationId;
        window.systemicExaminationModalContext.editingExaminationId = null;
        
        // Update modal header with patient info
        $('#systemicExaminationPatientBadge').text(patientName);
        
        // Clear and reset form
        clearSystemicExaminationForm();
        
        // Load existing examinations
        loadExistingExaminations(window.systemicExaminationModalContext.visitId);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('systemicExaminationModal'));
        modal.show();
    };

    /**
     * Close modal
     */
    window.closeSystemicExaminationModal = function() {
        const modalElement = document.getElementById('systemicExaminationModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    };

    /**
     * Load existing examinations for the visit
     */
    function loadExistingExaminations(visitId) {
        $.ajax({
            url: `/patient-visits/${visitId}/examinations`,
            method: 'GET'
        }).done(function(response) {
            
            if (response.success && response.examinations) {
                displayExaminations(response.examinations);
            } else {
                $('#modalExaminationsList').html(`
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No examinations recorded yet.
                    </div>
                `);
            }
        }).fail(function(xhr) {
            console.error('[Systemic Examination] Failed to load examinations:', xhr);
            $('#modalExaminationsList').html(`
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load examinations.
                </div>
            `);
        });
    }

    /**
     * Display examinations in the sidebar
     */
    function displayExaminations(examinations) {
        if (!examinations || examinations.length === 0) {
            $('#modalExaminationsList').html(`
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> No examinations recorded yet.
                </div>
            `);
            return;
        }

        let html = '';
        examinations.forEach(exam => {
            const examDate = exam.created_at ? new Date(exam.created_at).toLocaleString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : 'N/A';

            html += `
                <div class="card mb-2 examination-item" data-examination-id="${exam.id}">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="text-success">${exam.examination_type || 'Systemic Examination'}</strong>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.editSystemicExamination(${exam.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mb-2">${examDate}</small>
                        
                        ${exam.general_findings ? `
                        <div class="mb-1">
                            <small class="text-muted">General:</small>
                            <small class="d-block">${exam.general_findings.substring(0, 60)}${exam.general_findings.length > 60 ? '...' : ''}</small>
                        </div>
                        ` : ''}
                        
                        <div class="small">
                            ${exam.cardiovascular_system ? '<span class="badge bg-danger me-1">CVS</span>' : ''}
                            ${exam.respiratory_system ? '<span class="badge bg-info me-1">Resp</span>' : ''}
                            ${exam.gastrointestinal_system ? '<span class="badge bg-warning me-1">GI</span>' : ''}
                            ${exam.nervous_system ? '<span class="badge bg-primary me-1">CNS</span>' : ''}
                            ${exam.musculoskeletal_system ? '<span class="badge bg-secondary me-1">MSK</span>' : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        $('#modalExaminationsList').html(html);
    }

    /**
     * Edit examination
     */
    window.editSystemicExamination = function(examinationId) {
        
        $.ajax({
            url: `/consultations/examinations/${examinationId}`,
            method: 'GET'
        }).done(function(response) {
            if (response.success && response.examination) {
                const exam = response.examination;
                
                // Populate form
                $('#systemicExaminationModalForm select[name="examination_type"]').val(exam.examination_type || 'Systemic');
                $('#systemicExaminationModalForm textarea[name="general_findings"]').val(exam.general_findings || '');
                $('#systemicExaminationModalForm textarea[name="cardiovascular_system"]').val(exam.cardiovascular_system || '');
                $('#systemicExaminationModalForm textarea[name="respiratory_system"]').val(exam.respiratory_system || '');
                $('#systemicExaminationModalForm textarea[name="gastrointestinal_system"]').val(exam.gastrointestinal_system || '');
                $('#systemicExaminationModalForm textarea[name="nervous_system"]').val(exam.nervous_system || '');
                $('#systemicExaminationModalForm textarea[name="musculoskeletal_system"]').val(exam.musculoskeletal_system || '');
                $('#systemicExaminationModalForm textarea[name="genitourinary_system"]').val(exam.genitourinary_system || '');
                $('#systemicExaminationModalForm textarea[name="skin_examination"]').val(exam.skin_examination || '');
                $('#systemicExaminationModalForm textarea[name="psychiatric_assessment"]').val(exam.psychiatric_assessment || '');
                $('#systemicExaminationModalForm textarea[name="notes"]').val(exam.notes || '');
                
                // Store examination ID for update
                $('#modalExaminationId').val(examinationId);
                window.systemicExaminationModalContext.editingExaminationId = examinationId;
                
                // Update button text
                $('#modalSaveSystemicExamBtn .btn-text').text('Update Examination');
                $('#modalDeleteSystemicExamBtn').show();
                
                // Highlight the selected examination
                $('.examination-item').removeClass('border-primary');
                $(`.examination-item[data-examination-id="${examinationId}"]`).addClass('border-primary border-2');
            }
        }).fail(function(xhr) {
            toastr.error('Failed to load examination data');
            console.error('[Systemic Examination] Failed to load for edit:', xhr);
        });
    };

    /**
     * Save or update examination
     */
    $('#modalSaveSystemicExamBtn').on('click', function() {
        const button = $(this);
        const examinationId = window.systemicExaminationModalContext.editingExaminationId;
        
        if (examinationId) {
            updateSystemicExaminationFromModal(button);
        } else {
            saveSystemicExaminationFromModal(button);
        }
    });

    /**
     * Save new examination
     */
    function saveSystemicExaminationFromModal(button) {
        const visitId = window.systemicExaminationModalContext.visitId;
        
        if (!visitId) {
            toastr.error('Visit ID not found');
            return;
        }
        
        // Show saving state
        button.prop('disabled', true);
        const buttonText = button.find('.btn-text');
        const originalText = buttonText.text();
        buttonText.text('Saving...');
        
        const formData = $('#systemicExaminationModalForm').serialize();
        
        $.ajax({
            url: `/patient-visits/${visitId}/examinations`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            }
        }).done(function(response) {
            toastr.success('Examination recorded successfully');
            
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            // Reload examinations list
            loadExistingExaminations(visitId);
            
            // Clear form
            clearSystemicExaminationForm();
            
            // Refresh main page display if function exists
            if (typeof window.refreshExaminationsList === 'function') {
                window.refreshExaminationsList();
            }
        }).fail(function(xhr) {
            console.error('[Systemic Examination] Failed to save:', xhr);
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            let message = 'Failed to save examination';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
        });
    }

    /**
     * Update existing examination
     */
    function updateSystemicExaminationFromModal(button) {
        const examinationId = window.systemicExaminationModalContext.editingExaminationId;
        
        if (!examinationId) {
            toastr.error('Examination ID not found');
            return;
        }
        
        // Show updating state
        button.prop('disabled', true);
        const buttonText = button.find('.btn-text');
        const originalText = buttonText.text();
        buttonText.text('Updating...');
        
        const formData = $('#systemicExaminationModalForm').serialize() + '&_method=PUT';
        
        $.ajax({
            url: `/consultations/examinations/${examinationId}`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            }
        }).done(function(response) {
            toastr.success('Examination updated successfully');
            
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            // Reload examinations list
            loadExistingExaminations(window.systemicExaminationModalContext.visitId);
            
            // Clear form
            clearSystemicExaminationForm();
            
            // Refresh main page display if function exists
            if (typeof window.refreshExaminationsList === 'function') {
                window.refreshExaminationsList();
            }
        }).fail(function(xhr) {
            console.error('[Systemic Examination] Failed to update:', xhr);
            button.prop('disabled', false);
            buttonText.text(originalText);
            
            let message = 'Failed to update examination';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
        });
    }

    /**
     * Delete examination
     */
    $('#modalDeleteSystemicExamBtn').on('click', function() {
        const examinationId = window.systemicExaminationModalContext.editingExaminationId;
        
        if (!examinationId) {
            toastr.warning('No examination selected for deletion');
            return;
        }
        
        if (!confirm('Delete this examination? This cannot be undone.')) {
            return;
        }
        
        const button = $(this);
        button.prop('disabled', true);
        
        $.ajax({
            url: `/consultations/examinations/${examinationId}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(response) {
            toastr.success('Examination deleted successfully');
            
            button.prop('disabled', false);
            
            // Reload examinations list
            loadExistingExaminations(window.systemicExaminationModalContext.visitId);
            
            // Clear form
            clearSystemicExaminationForm();
            
            // Refresh main page display if function exists
            if (typeof window.refreshExaminationsList === 'function') {
                window.refreshExaminationsList();
            }
        }).fail(function(xhr) {
            console.error('[Systemic Examination] Failed to delete:', xhr);
            button.prop('disabled', false);
            toastr.error('Failed to delete examination');
        });
    });

    /**
     * Clear form
     */
    function clearSystemicExaminationForm() {
        $('#systemicExaminationModalForm')[0].reset();
        $('#modalExaminationId').val('');
        window.systemicExaminationModalContext.editingExaminationId = null;
        $('#modalSaveSystemicExamBtn .btn-text').text('Save Examination');
        $('#modalDeleteSystemicExamBtn').hide();
        $('.examination-item').removeClass('border-primary border-2');
    }

    /**
     * Reset modal on close
     */
    $('#systemicExaminationModal').on('hidden.bs.modal', function() {
        clearSystemicExaminationForm();
        window.systemicExaminationModalContext = {
            visitId: null,
            consultationId: null,
            patientId: null,
            patientName: '',
            mode: 'visit',
            editingExaminationId: null
        };
    });


})();
