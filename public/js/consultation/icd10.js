/**
 * ICD-10 Diagnosis Management Module
 * Handles ICD-10 code search, selection, and diagnosis management
 */

// ICD-10 Code Search and Selection Functionality
let selectedIcdCodes = [];

// Initialize ICD-10 functionality
function initializeIcd10Module() {
    console.log('Initializing ICD-10 module...');
    
    // Initialize ICD-10 autocomplete
    initializeIcd10Search();
    
    // Load existing ICD diagnoses
    loadExistingIcdDiagnoses();
    
    // Initialize diagnosis change tracking
    initializeDiagnosisChangeTracking();
}

// Initialize diagnosis change tracking
function initializeDiagnosisChangeTracking() {
    $('#provisional_diagnosis, #final_diagnosis').on('input', function() {
        const formConfig = { id: 'diagnosisForm', tab: 'diagnosis', button: 'saveDiagnosisBtn' };
        checkFormChanges(formConfig);
    });
}

// Initialize ICD-10 search functionality
function initializeIcd10Search() {
    // Setup autocomplete for ICD-10 code input
    $('#icd10_code').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchIcd10Codes(query, 'code');
        } else {
            hideIcd10Suggestions();
        }
    });

    // Setup autocomplete for ICD-10 description input
    $('#icd10_description').on('input', function() {
        const query = $(this).val();
        if (query.length >= 3) {
            searchIcd10Codes(query, 'description');
        } else {
            hideIcd10Suggestions();
        }
    });

    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.icd10-search-container').length) {
            hideIcd10Suggestions();
        }
    });

    // Wrap input fields in search containers
    if ($('.icd10-search-container').length === 0) {
        $('#icd10_code, #icd10_description').wrap('<div class="icd10-search-container position-relative"></div>');
    }
}

// Search ICD-10 codes via AJAX
function searchIcd10Codes(query, searchType) {
    $.ajax({
        url: '/api/icd10/search',
        method: 'GET',
        data: {
            query: query,
            type: searchType,
            limit: 10
        },
        success: function(response) {
            console.log('ICD-10 search response:', response);
            
            // Handle different response formats
            let codes = [];
            if (response.success && response.codes) {
                codes = response.codes;
            } else if (response.success && response.data) {
                codes = response.data;
            } else if (response.data) {
                codes = response.data;
            } else if (Array.isArray(response)) {
                codes = response;
            }
            
            if (codes.length > 0) {
                showIcd10Suggestions(codes, searchType);
            } else {
                hideIcd10Suggestions();
            }
        },
        error: function(xhr) {
            console.error('ICD-10 search error:', xhr.responseJSON);
            hideIcd10Suggestions();
        }
    });
}

// Show ICD-10 suggestions dropdown
function showIcd10Suggestions(codes, searchType) {
    const inputField = searchType === 'code' ? '#icd10_code' : '#icd10_description';
    const container = $(inputField).parent('.icd10-search-container');
    
    // Remove existing suggestions
    container.find('.icd10-suggestions').remove();
    
    if (codes.length === 0) {
        return;
    }
    
    // Create suggestions dropdown
    const suggestionsHtml = codes.map(code => `
        <div class="icd10-suggestion-item p-2 border-bottom cursor-pointer" 
             data-code="${code.code}" 
             data-description="${code.description}"
             data-category="${code.category || ''}"
             data-subcategory="${code.subcategory || ''}">
            <strong>${code.code}</strong> - ${code.description}
            ${code.category ? `<br><small class="text-muted">${code.category}</small>` : ''}
        </div>
    `).join('');
    
    const suggestionsContainer = `
        <div class="icd10-suggestions position-absolute w-100 bg-white border border-top-0 shadow-lg" style="z-index: 1000; max-height: 250px; overflow-y: auto; border-radius: 0 0 5px 5px;">
            ${suggestionsHtml}
        </div>
    `;
    
    container.append(suggestionsContainer);
    
    // Add click handlers for suggestions
    container.find('.icd10-suggestion-item').on('click', function() {
        const code = $(this).data('code');
        const description = $(this).data('description');
        const category = $(this).data('category');
        const subcategory = $(this).data('subcategory');
        
        // Fill both input fields
        $('#icd10_code').val(code);
        $('#icd10_description').val(description);
        
        // Store additional data for adding to diagnosis
        $('#icd10_code').data('category', category);
        $('#icd10_code').data('subcategory', subcategory);
        
        hideIcd10Suggestions();
        
        // Enable add button
        $('#addIcdBtn').prop('disabled', false);
    });
    
    // Add hover effects
    container.find('.icd10-suggestion-item').on('mouseenter', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', function() {
        $(this).removeClass('bg-light');
    });
}

// Hide ICD-10 suggestions
function hideIcd10Suggestions() {
    $('.icd10-suggestions').remove();
}

// Add ICD diagnosis to the list
function addIcdDiagnosis() {
    const icdType = $('#icd_type').val();
    const icdCode = $('#icd10_code').val().trim();
    const icdDescription = $('#icd10_description').val().trim();
    
    if (!icdCode || !icdDescription) {
        toastr.warning('Please select both ICD-10 code and description.');
        return;
    }
    
    // Check if this ICD code already exists for this consultation
    const existingCode = selectedIcdCodes.find(item => 
        item.code === icdCode && item.type === icdType
    );
    
    if (existingCode) {
        toastr.warning('This ICD-10 code is already added for this diagnosis type.');
        return;
    }
    
    // Show loading
    const addBtn = $('#addIcdBtn');
    const originalText = addBtn.html();
    addBtn.html('<i class="fas fa-spinner fa-spin"></i> Adding...').prop('disabled', true);
    
    // Prepare data for saving
    const icdData = {
        consultation_id: window.consultationId,
        icd_code: icdCode,
        description: icdDescription,
        type: icdType,
        category: $('#icd10_code').data('category') || '',
        subcategory: $('#icd10_code').data('subcategory') || '',
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    // Save to database
    $.ajax({
        url: `/consultations/${window.consultationId}/icd-diagnoses`,
        method: 'POST',
        data: icdData,
        success: function(response) {
            console.log('Add ICD diagnosis response:', response);
            
            if (response.success) {
                toastr.success('ICD-10 diagnosis added successfully!');
                
                // Handle different response structures
                let diagnosisId = null;
                if (response.icd_diagnosis && response.icd_diagnosis.id) {
                    diagnosisId = response.icd_diagnosis.id;
                } else if (response.data && response.data.id) {
                    diagnosisId = response.data.id;
                } else if (response.id) {
                    diagnosisId = response.id;
                } else {
                    // Generate a temporary ID if none provided
                    diagnosisId = Date.now();
                }
                
                // Add to local array and reload from server to ensure consistency
                selectedIcdCodes.push({
                    id: diagnosisId,
                    code: icdCode,
                    description: icdDescription,
                    type: icdType,
                    category: icdData.category,
                    subcategory: icdData.subcategory
                });
                
                // Reload ICD diagnoses from server to ensure consistency
                loadExistingIcdDiagnoses();
                
                // Add to diagnosis text
                addDescriptionToDiagnosisText(icdType, icdCode, icdDescription);
                
                // Mark diagnosis form as having changes
                if (typeof markFormAsUnsaved === 'function') {
                    markFormAsUnsaved('diagnosisForm', 'saveDiagnosisBtn', 'diagnosis');
                }
                
                // Clear inputs
                clearIcdInputs();
            } else {
                toastr.error(response.message || 'Failed to add ICD-10 diagnosis.');
            }
        },
        error: function(xhr) {
            console.error('Add ICD diagnosis error:', xhr.responseJSON);
            const errorMessage = xhr.responseJSON?.message || 'Failed to add ICD-10 diagnosis.';
            toastr.error(errorMessage);
        },
        complete: function() {
            // Reset button
            addBtn.html(originalText).prop('disabled', false);
        }
    });
}

// Add ICD description to diagnosis textarea
function addDescriptionToDiagnosisText(icdType, icdCode, icdDescription) {
    const textareaId = icdType === 'provisional' ? '#provisional_diagnosis' : '#final_diagnosis';
    const textarea = $(textareaId);
    
    if (!textarea.length) {
        console.warn(`Textarea ${textareaId} not found`);
        return;
    }
    
    const currentText = textarea.val().trim();
    const newEntry = `${icdCode} - ${icdDescription}`;
    
    // Check if the entry already exists
    if (currentText.includes(newEntry)) {
        toastr.info('This ICD diagnosis is already present in the text.');
        return;
    }
    
    let updatedText;
    if (currentText === '') {
        updatedText = newEntry;
    } else {
        updatedText = currentText + '\n' + newEntry;
    }
    
    // Update the textarea
    textarea.val(updatedText);
    
    // Trigger change event to update the change tracking
    textarea.trigger('input');
    
    // Show visual feedback
    textarea.addClass('border-success');
    setTimeout(() => {
        textarea.removeClass('border-success');
    }, 2000);
    
    // Show success message
    const diagnosisType = icdType === 'provisional' ? 'Provisional' : 'Final';
    toastr.success(`ICD diagnosis added to ${diagnosisType} diagnosis text.`);
}

// Remove ICD diagnosis
function removeIcdDiagnosis(icdId) {
    if (!confirm('Are you sure you want to remove this ICD-10 diagnosis?')) {
        return;
    }
    
    $.ajax({
        url: `/consultations/icd-diagnoses/${icdId}`,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                toastr.success('ICD-10 diagnosis removed successfully!');
                
                // Remove from local array
                const index = selectedIcdCodes.findIndex(item => item.id === icdId);
                if (index !== -1) {
                    const removedCode = selectedIcdCodes[index];
                    // Remove from diagnosis text
                    removeDescriptionFromDiagnosisText(removedCode.type, removedCode.code, removedCode.description);
                }
                
                // Reload ICD diagnoses from server to ensure consistency
                loadExistingIcdDiagnoses();
                
                // Mark diagnosis form as having changes
                if (typeof markFormAsUnsaved === 'function') {
                    markFormAsUnsaved('diagnosisForm', 'saveDiagnosisBtn', 'diagnosis');
                }
            } else {
                toastr.error(response.message || 'Failed to remove ICD-10 diagnosis.');
            }
        },
        error: function(xhr) {
            console.error('Remove ICD diagnosis error:', xhr.responseJSON);
            const errorMessage = xhr.responseJSON?.message || 'Failed to remove ICD-10 diagnosis.';
            toastr.error(errorMessage);
        }
    });
}

// Update ICD diagnoses list display
function updateIcdDiagnosesList() {
    const container = $('#icd_diagnoses_list');
    
    if (selectedIcdCodes.length === 0) {
        container.html('<p class="text-muted">No ICD-10 diagnoses added yet.</p>');
        return;
    }
    
    const tableHtml = `
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>ICD Code</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Date Added</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${selectedIcdCodes.map(code => `
                        <tr>
                            <td>
                                <span class="badge bg-${code.type === 'provisional' ? 'warning' : 'success'}">
                                    ${code.type.charAt(0).toUpperCase() + code.type.slice(1)}
                                </span>
                            </td>
                            <td><strong class="text-primary">${code.icd_code || code.code}</strong></td>
                            <td>${code.description}</td>
                            <td>
                                <small class="text-muted">${code.category || 'N/A'}</small>
                                ${code.subcategory ? `<br><small class="text-info">${code.subcategory}</small>` : ''}
                            </td>
                            <td><small>${code.created_at ? new Date(code.created_at).toLocaleDateString('en-GB') : 'N/A'}</small></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="removeIcdDiagnosis(${code.id})"
                                        title="Remove this diagnosis">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.html(tableHtml);
}

// Clear ICD input fields
function clearIcdInputs() {
    $('#icd10_code').val('').removeData('category').removeData('subcategory');
    $('#icd10_description').val('');
    $('#icd_type').val('provisional');
    $('#addIcdBtn').prop('disabled', true);
}

// Load existing ICD diagnoses
function loadExistingIcdDiagnoses() {
    $.ajax({
        url: `/consultations/${window.consultationId}/icd-diagnoses`,
        method: 'GET',
        success: function(response) {
            console.log('Load existing ICD diagnoses response:', response);
            
            if (response.success) {
                // Handle different response structures
                let diagnoses = [];
                if (response.icd_diagnoses) {
                    diagnoses = response.icd_diagnoses;
                } else if (response.data) {
                    diagnoses = response.data;
                } else if (Array.isArray(response)) {
                    diagnoses = response;
                }
                
                selectedIcdCodes = diagnoses;
                updateIcdDiagnosesList();
            }
        },
        error: function(xhr) {
            console.error('Load existing ICD diagnoses error:', xhr.responseJSON);
        }
    });
}

// Enable/disable Add button based on input
function setupIcdInputValidation() {
    $('#icd10_code, #icd10_description').on('input', function() {
        const code = $('#icd10_code').val().trim();
        const description = $('#icd10_description').val().trim();
        $('#addIcdBtn').prop('disabled', !(code && description));
    });
}

// Remove ICD description from diagnosis textarea
function removeDescriptionFromDiagnosisText(icdType, icdCode, icdDescription) {
    const textareaId = icdType === 'provisional' ? '#provisional_diagnosis' : '#final_diagnosis';
    const textarea = $(textareaId);
    
    if (!textarea.length) {
        console.warn(`Textarea ${textareaId} not found`);
        return;
    }
    
    const currentText = textarea.val();
    const entryToRemove = `${icdCode} - ${icdDescription}`;
    
    // Check if the entry exists
    if (!currentText.includes(entryToRemove)) {
        return;
    }
    
    // Remove the entry and any associated newlines
    let updatedText = currentText
        .replace(new RegExp(escapeRegExp(entryToRemove) + '\\n?', 'g'), '')
        .replace(/\n\n+/g, '\n')
        .trim();
    
    // Update the textarea
    textarea.val(updatedText);
    
    // Trigger change event to update the change tracking
    textarea.trigger('input');
    
    // Show visual feedback
    textarea.addClass('border-warning');
    setTimeout(() => {
        textarea.removeClass('border-warning');
    }, 2000);
    
    // Show info message
    const diagnosisType = icdType === 'provisional' ? 'Provisional' : 'Final';
    toastr.info(`ICD diagnosis removed from ${diagnosisType} diagnosis text.`);
}

// Helper function to escape special regex characters
function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Add keyboard navigation for ICD suggestions
$(document).on('keydown', '#icd10_code, #icd10_description', function(e) {
    const $suggestions = $(this).parent().find('.icd10-suggestions');
    const $items = $suggestions.find('.icd10-suggestion-item');
    
    if ($items.length === 0) return;
    
    let $current = $items.filter('.bg-light');
    
    if (e.keyCode === 40) { // Down arrow
        e.preventDefault();
        if ($current.length === 0) {
            $items.first().addClass('bg-light');
        } else {
            $current.removeClass('bg-light');
            const $next = $current.next();
            if ($next.length) {
                $next.addClass('bg-light');
            } else {
                $items.first().addClass('bg-light');
            }
        }
    } else if (e.keyCode === 38) { // Up arrow
        e.preventDefault();
        if ($current.length === 0) {
            $items.last().addClass('bg-light');
        } else {
            $current.removeClass('bg-light');
            const $prev = $current.prev();
            if ($prev.length) {
                $prev.addClass('bg-light');
            } else {
                $items.last().addClass('bg-light');
            }
        }
    } else if (e.keyCode === 13) { // Enter
        e.preventDefault();
        if ($current.length) {
            $current.click();
        }
    }
});
