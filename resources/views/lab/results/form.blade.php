@extends('layouts.app_main_layout')

@section('page_title', 'Lab Results - ' . $investigation->medicalService->name)

@section('main_content')
<div class="container-fluid">
    <!-- Investigation Information Header -->
    <div class="alert alert-primary mb-4">
        <div class="row">
            <div class="col-md-8">
                <h6><i class="fas fa-vial"></i> Investigation Details</h6>
                <strong>{{ $investigation->medicalService->name }}</strong>
                @if($investigation->medicalService->code)
                    <span class="badge bg-light text-dark ms-2">{{ $investigation->medicalService->code }}</span>
                @endif
                <br>
                <small>
                    Patient: <strong>{{ $investigation->patient->first_name }} {{ $investigation->patient->last_name }}</strong> |
                    Investigation ID: {{ $investigation->id }} |
                    Priority: 
                    <span class="badge bg-{{ $investigation->priority === 'stat' ? 'danger' : ($investigation->priority === 'urgent' ? 'warning' : 'secondary') }}">
                        {{ strtoupper($investigation->priority) }}
                    </span>
                </small>
            </div>
            <div class="col-md-4 text-end">
                <div>
                    <strong>Ordered:</strong> {{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}<br>
                    <strong>Doctor:</strong> 
                    @if($investigation->doctor && $investigation->doctor->user)
                        Dr. {{ $investigation->doctor->user->first_name }} {{ $investigation->doctor->user->last_name }}
                    @else
                        <span class="text-muted">Not specified</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="d-flex justify-content-end align-items-center mb-4">
        <div>
            @if($investigation->templateResults->count() > 0)
                <span class="badge bg-info">{{ $investigation->templateResults->count() }} existing result(s)</span>
            @endif
        </div>
    </div>

    <!-- Results Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit text-primary"></i> 
                Add/Update Lab Results
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('lab.results.store', $investigation->id) }}" method="POST" id="resultsForm">
                @csrf
                
                {{-- Hidden fields for return navigation --}}
                @if(isset($returnTo))
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @endif
                @if(isset($investigationIdForReturn))
                    <input type="hidden" name="investigation_id" value="{{ $investigationIdForReturn }}">
                @endif
                
                {{-- Result Template Section --}}
                <input type="hidden" name="selected_template_code" id="selected_template_code"
                       value="{{ $investigation->medicalService->resultTemplate->code ?? '' }}">

                @if(!$investigation->medicalService->resultTemplate)
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-3" id="template_selector_card">
                    <i class="fas fa-exclamation-triangle fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <strong>No result template assigned to this investigation.</strong>
                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                            <label class="mb-0 text-nowrap">Select template:</label>
                            <select id="manual_template_select" class="form-select form-select-sm" style="width:auto; min-width:240px">
                                <option value="">— Choose a template —</option>
                                @foreach($availableTemplates as $t)
                                    <option value="{{ $t->code }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Custom Template Form Container --}}
                <div id="custom_template_form" class="result-form border rounded p-3 mb-4" style="display: none; background-color: #f8f9fa;">
                    <div id="template_content_container">
                        <div class="text-center p-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading template...</span>
                            </div>
                            <p class="mt-2 mb-0">Loading result template...</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="action" id="action_draft" value="draft">
                                    <label class="form-check-label" for="action_draft">
                                        <i class="fas fa-save text-warning"></i> Save as Draft
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="action" id="action_preliminary" value="preliminary">
                                    <label class="form-check-label" for="action_preliminary">
                                        <i class="fas fa-eye text-info"></i> Preliminary Report
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="action" id="action_final" value="final" checked>
                                    <label class="form-check-label" for="action_final">
                                        <i class="fas fa-check text-success"></i> Final Report
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Results
                                </button>
                                @if($investigation->consultation && $investigation->consultation->visit)
                                    <a href="{{ route('lab.visits.investigations', $investigation->consultation->visit->id) }}" 
                                       class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                @else
                                    <a href="{{ route('lab.visits.index') }}" 
                                       class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

<script>
let parameterCount = {{ $investigation->templateResults->count() > 0 ? $investigation->templateResults->count() : 1 }};

document.addEventListener('DOMContentLoaded', function() {
    const manualSelect = document.getElementById('manual_template_select');
    if (manualSelect) {
        // No template assigned — wait for user to pick one
        manualSelect.addEventListener('change', function() {
            if (this.value) loadResultTemplate(this.value);
        });
    } else {
        // Template pre-assigned — auto-load
        setTimeout(loadResultTemplate, 500);
    }
});

// Form validation
const resultsForm = document.getElementById('resultsForm');
if (resultsForm) {
    resultsForm.addEventListener('submit', function(e) {
        // Template-based form validation
        const templateFields = document.querySelectorAll('#template_content_container input[required], #template_content_container select[required], #template_content_container textarea[required]');
        let hasInvalidFields = false;
        
        templateFields.forEach(field => {
            if (!field.value.trim()) {
                hasInvalidFields = true;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (hasInvalidFields) {
            e.preventDefault();
            alert('Please fill in all required fields in the template.');
            return;
        }
    });
}

// Custom Result Template Functions
function loadResultTemplate(overrideCode = null) {
    const templateContainer = document.getElementById('custom_template_form');
    const contentContainer = document.getElementById('template_content_container');

    // Determine which template to load
    let templateName = overrideCode || '{{ $investigation->medicalService->resultTemplate->code ?? "" }}';

    if (!templateName || templateName === 'none' || templateName === '') {
        return; // No template — wait for user to select
    }

    // Update the hidden input so storeResults receives the chosen template
    const hiddenCode = document.getElementById('selected_template_code');
    if (hiddenCode) hiddenCode.value = templateName;

    // Show the template container
    templateContainer.style.display = 'block';

    // Show loading state
    contentContainer.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading template...</span>
            </div>
            <p class="mt-2 mb-0">Loading result template...</p>
        </div>
    `;
    
    // Load the template via AJAX
    const url = `/api/result-template/${templateName}?investigation_id={{ $investigation->id }}`;
    
    fetch(url)
        .then(response => response.text())
        .then(data => {
            contentContainer.innerHTML = `
                <div class="result-template-container">
                    ${data}
                </div>
            `;

            // Hide the selector card once a template is loaded
            const selectorCard = document.getElementById('template_selector_card');
            if (selectorCard) selectorCard.style.display = 'none';

            // Execute any scripts in the loaded template
            executeTemplateScripts(contentContainer);
            
            // Populate analyzed_by with current user (template is loaded via AJAX, no session access)
            const analyzedByInput = contentContainer.querySelector('input[name="analyzed_by"]');
            if (analyzedByInput) {
                analyzedByInput.value = '{{ auth()->user()->name ?? "" }}';
            }

            // Expose current user name so template JS can auto-fill "received by" fields
            const tbForm = contentContainer.querySelector('#tb-investigation-form');
            if (tbForm) {
                tbForm.dataset.receivedBy = '{{ trim((auth()->user()->first_name ?? "") . " " . (auth()->user()->last_name ?? "")) }}';
            }

            // Make the template form fields interactive
            makeTemplateFieldsInteractive();
        })
        .catch(error => {
            console.error('Error loading template:', error);
            contentContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Template Not Found:</strong> The result template "${templateName}" could not be loaded.
                    <br><small class="text-muted">This template may not exist or may not be configured correctly.</small>
                </div>
                <div class="mt-3">
                    <h6>Available Templates:</h6>
                    <div class="list-group">
                        <div class="list-group-item">
                            <strong>simple</strong> - Simple Lab Values Template
                        </div>
                        <div class="list-group-item">
                            <strong>tb</strong> - TB Investigation Result Template
                        </div>
                        <div class="list-group-item">
                            <strong>general</strong> - General Laboratory Result Template
                        </div>
                        <div class="list-group-item">
                            <strong>blood_count</strong> - Complete Blood Count Template
                        </div>
                        <div class="list-group-item">
                            <strong>chemistry</strong> - Blood Chemistry Panel Template
                        </div>
                    </div>
                </div>
            `;
        });
}

function executeTemplateScripts(container) {
    // Find all script tags in the loaded template
    const scripts = container.querySelectorAll('script');
    
    scripts.forEach(script => {
        // Create a new script element
        const newScript = document.createElement('script');
        
        // Copy attributes
        for (let attr of script.attributes) {
            newScript.setAttribute(attr.name, attr.value);
        }
        
        // Copy the script content
        newScript.textContent = script.textContent;
        
        // Replace the old script with the new one
        script.parentNode.replaceChild(newScript, script);
    });
    
    console.log('Template scripts executed');
}

function hideResultTemplate() {
    const templateContainer = document.getElementById('custom_template_form');
    templateContainer.style.display = 'none';
}

function makeTemplateFieldsInteractive() {
    // Find all form fields in the template and make them submittable with the main form
    const templateContainer = document.getElementById('template_content_container');
    const templateFields = templateContainer.querySelectorAll('input, select, textarea');
    
    templateFields.forEach(field => {
        // Add a prefix to field names to identify them as template fields
        if (field.name && !field.name.startsWith('template_')) {
            field.name = 'template_' + field.name;
        }

        // Add some styling to make them look integrated
        field.classList.add('form-control');
        if (field.tagName.toLowerCase() === 'select') {
            field.classList.add('form-select');
        }

        // Preserve readonly/disabled state set by the template (e.g. final results)
    });
    

}

// Function to view existing template result
function viewTemplateResult(resultId) {
    // Navigate to the template result view page
    const url = `/lab/template-results/${resultId}`;
    window.location.href = url;
}
</script>
