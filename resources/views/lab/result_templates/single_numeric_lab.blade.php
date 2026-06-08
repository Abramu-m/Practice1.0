{{-- Simple Lab Values Result Template --}}
<div class="result-template-container" style="background-color: #fff; padding: 15px; border-radius: 5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-list-ol"></i>
            Lab Values Entry
        </h6>
        <small class="text-muted">Enter laboratory parameter values and results</small>
    </div>

    {{-- Hidden reference data for JavaScript --}}
    @if(isset($investigation) && $investigation->medicalService)
    <script>
        window.medicalServiceData = {
            name: '{{ $investigation->medicalService->name }}',
            min_value: {{ $investigation->medicalService->min_value ? $investigation->medicalService->min_value : 'null' }},
            max_value: {{ $investigation->medicalService->max_value ? $investigation->medicalService->max_value : 'null' }},
            unit: '{{ $investigation->medicalService->unit ?? '' }}'
        };
    </script>
    @endif

    {{-- Lab Parameters Table --}}
    <div class="table-responsive">
        <table class="table table-bordered" id="simpleParametersTable">
            <thead class="table-light">
                <tr>
                    <th width="20%">Parameter</th>
                    <th width="15%">Value</th>
                    <th width="10%">Unit</th>
                    <th width="15%">Normal Range</th>
                    <th width="15%">Status</th>
                    <th width="20%">Remarks</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody id="simpleParametersBody">
                @if(isset($investigation) && $investigation->results->count() > 0)
                    @foreach($investigation->results as $result)
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm" 
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $loop->index }}][parameter_name]" 
                                   value="{{ $result->parameter_name }}" required readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm value-input" 
                                   name="parameters[{{ $loop->index }}][value]" 
                                   value="{{ $result->value }}" 
                                   placeholder="e.g., 12.5" required
                                   data-row-index="{{ $loop->index }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $loop->index }}][unit]" 
                                   value="{{ $result->unit ?: ($investigation->medicalService->unit ?? '') }}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $loop->index }}][normal_range]" 
                                   value="{{ $result->normal_range ?: ($investigation->medicalService->min_value && $investigation->medicalService->max_value ? $investigation->medicalService->min_value . ' - ' . $investigation->medicalService->max_value : '') }}" readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm status-select"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $loop->index }}][status]">
                                <option value="normal" {{ $result->status === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ $result->status === 'high' ? 'selected' : '' }}>High</option>
                                <option value="low" {{ $result->status === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="critical" {{ $result->status === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" 
                                   name="parameters[{{ $loop->index }}][remarks]" 
                                   value="{{ $result->remarks }}" 
                                   placeholder="Optional remarks">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSimpleParameter(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[0][parameter_name]" 
                                   value="{{ isset($investigation) && $investigation->medicalService ? $investigation->medicalService->name : '' }}"
                                   required readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm value-input" 
                                   name="parameters[0][value]" 
                                   placeholder="e.g., 12.5" required
                                   data-row-index="0">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[0][unit]" 
                                   value="{{ isset($investigation) && $investigation->medicalService ? $investigation->medicalService->unit : '' }}"
                                   readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[0][normal_range]" 
                                   value="{{ isset($investigation) && $investigation->medicalService && $investigation->medicalService->min_value && $investigation->medicalService->max_value ? $investigation->medicalService->min_value . ' - ' . $investigation->medicalService->max_value : '' }}"
                                   readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm status-select"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[0][status]">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                                <option value="critical">Critical</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" 
                                   name="parameters[0][remarks]" 
                                   placeholder="Optional remarks">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSimpleParameter(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Add Parameter Button --}}
    <div class="mt-3">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSimpleParameter()">
            <i class="fas fa-plus"></i> Add Parameter
        </button>
    </div>

    {{-- Quality Control Section --}}
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Quality Control</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analyzed By:</strong></label>
                    <input type="text" class="form-control form-control-sm lab-readonly" name="analyzed_by"
                           value="{{ isset($currentUser) ? $currentUser->name : (auth()->user()->name ?? '') }}" readonly>
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analysis Date:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="analysis_date"
                           value="{{ now()->format('Y-m-d\TH:i') }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
            </div>
            <div class="row g-2 align-items-start mt-2">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Additional Comments:</strong></label>
                    <textarea class="form-control form-control-sm" name="additional_comments" rows="2"
                              placeholder="Any additional observations or comments..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let simpleParameterCount = {{ isset($investigation) && $investigation->results->count() > 0 ? $investigation->results->count() : 1 }};
let statusCheckTimeout;

// Global functions for inline event handlers
function addSimpleParameter() {
    const tbody = document.getElementById('simpleParametersBody');
    const row = document.createElement('tr');
    
    // Get default values from medical service data if available
    const defaultUnit = window.medicalServiceData ? window.medicalServiceData.unit : '';
    const defaultRange = (window.medicalServiceData && window.medicalServiceData.min_value && window.medicalServiceData.max_value) 
        ? `${window.medicalServiceData.min_value} - ${window.medicalServiceData.max_value}` : '';
    const defaultParameter = window.medicalServiceData ? window.medicalServiceData.name : '';
    
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm"
                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                   name="parameters[${simpleParameterCount}][parameter_name]" 
                   value="${defaultParameter}"
                   required readonly>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm value-input" 
                   name="parameters[${simpleParameterCount}][value]" 
                   placeholder="Value" required
                   data-row-index="${simpleParameterCount}">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm"
                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                   name="parameters[${simpleParameterCount}][unit]" 
                   value="${defaultUnit}"
                   readonly>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm"
                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                   name="parameters[${simpleParameterCount}][normal_range]" 
                   value="${defaultRange}"
                   readonly>
        </td>
        <td>
            <select class="form-select form-select-sm status-select"
                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                    name="parameters[${simpleParameterCount}][status]">
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="low">Low</option>
                <option value="critical">Critical</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" 
                   name="parameters[${simpleParameterCount}][remarks]" 
                   placeholder="Remarks">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSimpleParameter(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    
    // Find the newly added value input
    const newValueInput = row.querySelector('.value-input');


    
    simpleParameterCount++;

    
    // Test if the new input responds to events
    setTimeout(() => {

        if (newValueInput) {
            // Manually trigger an input event to test
            newValueInput.focus();

        }
    }, 100);
}

function removeSimpleParameter(button) {
    const tbody = document.getElementById('simpleParametersBody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
    } else {
        alert('At least one parameter is required.');
    }
}

/**
 * Check if entered value is normal, high, or low based on reference ranges
 */
function checkValueStatus(valueInput) {
    // Clear any existing timeout to debounce rapid typing
    clearTimeout(statusCheckTimeout);
    
    statusCheckTimeout = setTimeout(() => {
        performStatusCheck(valueInput);
    }, 300); // Wait 300ms after user stops typing
}

function performStatusCheck(valueInput) {
    
    if (!valueInput) {

        return;
    }
    
    const row = valueInput.closest('tr');
    if (!row) {

        return;
    }

    
    const statusSelect = row.querySelector('.status-select');
    const remarksInput = row.querySelector('input[name*="[remarks]"]');
    const normalRangeInput = row.querySelector('input[name*="[normal_range]"]');
    





    
    if (!statusSelect || !remarksInput || !normalRangeInput) {


        return;
    }
    
    const enteredValue = parseFloat(valueInput.value);
    const normalRange = normalRangeInput.value.trim();
    




    
    // Clear any previous validation styling
    valueInput.classList.remove('border-success', 'border-warning', 'border-danger');
    statusSelect.classList.remove('border-success', 'border-warning', 'border-danger', 'text-success', 'text-warning', 'text-danger');

    
    // If value is empty or not a number, reset to default state
    if (!valueInput.value.trim() || isNaN(enteredValue)) {

        statusSelect.value = 'normal';
        if (!valueInput.value.trim()) {
            remarksInput.value = '';
        }
        return;
    }
    
    // If no range is set, don't auto-determine status but keep the value valid
    if (!normalRange) {

        valueInput.classList.add('border-success');
        statusSelect.value = 'normal';
        return;
    }
    
    // Parse the normal range (supports formats like "12-16", "12 - 16", "12 to 16", "> 10", "< 5")
    let minValue = null;
    let maxValue = null;
    let status = 'normal';
    let autoRemark = '';
    

    
    // Handle different range formats
    if (normalRange.includes('-')) {
        const parts = normalRange.split('-').map(part => parseFloat(part.trim()));

        if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
            minValue = parts[0];
            maxValue = parts[1];
        }
    } else if (normalRange.includes(' to ')) {
        const parts = normalRange.split(' to ').map(part => parseFloat(part.trim()));

        if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
            minValue = parts[0];
            maxValue = parts[1];
        }
    } else if (normalRange.startsWith('>')) {
        minValue = parseFloat(normalRange.replace('>', '').trim());
        maxValue = null;

    } else if (normalRange.startsWith('<')) {
        minValue = null;
        maxValue = parseFloat(normalRange.replace('<', '').trim());

    } else {
        // Try to use medical service data if available

        if (window.medicalServiceData && window.medicalServiceData.min_value && window.medicalServiceData.max_value) {
            minValue = window.medicalServiceData.min_value;
            maxValue = window.medicalServiceData.max_value;

        }
    }
    

    
    // Determine status based on parsed values
    if (minValue !== null && maxValue !== null) {

        if (enteredValue < minValue) {
            status = 'low';
            autoRemark = `Below normal range (${normalRange})`;
            valueInput.classList.add('border-warning');

        } else if (enteredValue > maxValue) {
            status = 'high';
            autoRemark = `Above normal range (${normalRange})`;
            valueInput.classList.add('border-warning');

        } else {
            status = 'normal';
            autoRemark = 'Within normal range';
            valueInput.classList.add('border-success');

        }
    } else if (minValue !== null && enteredValue < minValue) {
        status = 'low';
        autoRemark = `Below minimum (${normalRange})`;
        valueInput.classList.add('border-warning');

    } else if (maxValue !== null && enteredValue > maxValue) {
        status = 'high';
        autoRemark = `Above maximum (${normalRange})`;
        valueInput.classList.add('border-warning');

    } else {
        status = 'normal';
        valueInput.classList.add('border-success');

    }
    
    // Check for critical values (very high or very low)
    if (minValue !== null && maxValue !== null) {
        const range = maxValue - minValue;
        const criticalLow = minValue - (range * 0.3); // 30% below minimum
        const criticalHigh = maxValue + (range * 0.3); // 30% above maximum
        

        
        if (enteredValue < criticalLow || enteredValue > criticalHigh) {
            status = 'critical';
            autoRemark = 'CRITICAL: Value significantly outside normal range - Requires immediate attention';
            valueInput.classList.remove('border-warning', 'border-success');
            valueInput.classList.add('border-danger');

        }
    }
    


    
    // Update the status select directly (CSS prevents user interaction)
    statusSelect.value = status;

    
    // Update remarks if it's empty or contains a previous auto-remark
    const currentRemarks = remarksInput.value.trim();

    
    if (!currentRemarks || currentRemarks.includes('normal range') || currentRemarks.includes('CRITICAL:') || currentRemarks.includes('Below') || currentRemarks.includes('Above') || currentRemarks.includes('Within normal')) {
        remarksInput.value = autoRemark;

    } else {

    }
    
    // Add visual feedback with color-coded status — always restore inline readonly style
    statusSelect.className = `form-select form-select-sm status-select`;
    statusSelect.style.cssText = 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;';
    if (status === 'critical') {
        statusSelect.style.color = '#dc3545';
        statusSelect.style.borderColor = '#dc3545';
    } else if (status === 'high' || status === 'low') {
        statusSelect.style.color = '#856404';
        statusSelect.style.borderColor = '#ffc107';
    } else {
        statusSelect.style.color = '#155724';
        statusSelect.style.borderColor = '#28a745';
    }

}

// Initialize immediately when script executes (not waiting for DOMContentLoaded)



// DEBUG: Log the actual medical service values
if (window.medicalServiceData) {






}

// Count existing value inputs
const existingInputs = document.querySelectorAll('.value-input');


// Auto-populate empty normal range fields with medical service data
existingInputs.forEach(function(input, index) {
    const row = input.closest('tr');
    if (row) {
        const normalRangeInput = row.querySelector('input[name*="[normal_range]"]');
        if (normalRangeInput && !normalRangeInput.value.trim() && window.medicalServiceData) {
            if (window.medicalServiceData.min_value && window.medicalServiceData.max_value) {
                const range = `${window.medicalServiceData.min_value} - ${window.medicalServiceData.max_value}`;
                normalRangeInput.value = range;

            }
        }
    }
});

// Check existing values
existingInputs.forEach(function(input, index) {

    if (input.value.trim()) {

        performStatusCheck(input);
    }
});



// ENHANCED DEBUG: Test if event delegation works at all
document.addEventListener('input', function(e) {






    
    if (e.target.classList.contains('value-input')) {


        checkValueStatus(e.target);
    } else {

    }
});

// Also add a catch-all event listener to see ALL events
document.addEventListener('keyup', function(e) {
    if (e.target.classList.contains('value-input')) {

    }
});



// Also handle change events with logging
document.addEventListener('change', function(e) {

    if (e.target.classList.contains('value-input')) {

        performStatusCheck(e.target);
    }
});

// Also listen for paste events
document.addEventListener('paste', function(e) {

    if (e.target.classList.contains('value-input')) {

        setTimeout(() => {

            checkValueStatus(e.target);
        }, 10);
    }
});

// Test event delegation

setTimeout(() => {
    const testInput = document.querySelector('.value-input');
    if (testInput) {


        
        // Test manual trigger

        testInput.addEventListener('input', function() {

        });
        
        // Focus the input to test
        testInput.focus();

    }
}, 1000);
</script>

<style>
/* Readonly fields styling */
.lab-readonly,
input[readonly].form-control,
input[readonly].form-control-sm {
    background-color: #f0f0f0 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    color: #555;
    user-select: none !important;
    -webkit-user-select: none !important;
}

.status-select-readonly {
    background-color: #f0f0f0 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    color: #555;
    appearance: none;
    -webkit-appearance: none;
    user-select: none !important;
    -webkit-user-select: none !important;
}

/* Enhanced styling for value status indicators */
.value-input {
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.value-input.border-success {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.1rem rgba(40, 167, 69, 0.25);
}

.value-input.border-warning {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.1rem rgba(255, 193, 7, 0.25);
}

.value-input.border-danger {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25);
    animation: pulse-danger 2s infinite;
}

.status-select.border-success {
    border-color: #28a745 !important;
}

.status-select.border-warning {
    border-color: #ffc107 !important;
}

.status-select.border-danger {
    border-color: #dc3545 !important;
    font-weight: 600;
}

.text-success {
    color: #28a745 !important;
    font-weight: 500;
}

.text-warning {
    color: #856404 !important;
    font-weight: 500;
}

.text-danger {
    color: #dc3545 !important;
    font-weight: 600;
}

/* Pulse animation for critical values */
@keyframes pulse-danger {
    0% {
        box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 0.3rem rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0);
    }
}

/* Status badges in select dropdown */
.status-select option[value="normal"] {
    background-color: #d4edda;
    color: #155724;
}

.status-select option[value="high"], .status-select option[value="low"] {
    background-color: #fff3cd;
    color: #856404;
}

.status-select option[value="critical"] {
    background-color: #f8d7da;
    color: #721c24;
    font-weight: 600;
}

/* Table enhancements */
.table th {
    background-color: #f8f9fa;
    border-top: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .form-control-sm, .form-select-sm {
        font-size: 0.75rem;
    }
}
</style>
