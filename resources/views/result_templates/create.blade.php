@extends('layouts.app_main_layout')

@section('page_title', 'Create Result Template')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Create New Result Template</h3>
                    <a href="{{ route('result-templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Templates
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('result-templates.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Template Name -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" 
                                           placeholder="e.g., Simple Lab Results"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Template Code -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Template Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="code" 
                                           id="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code') }}" 
                                           placeholder="e.g., cd4, simple_lab, imaging"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Enter a unique code. The system will look for a Blade template file named <strong>[code].blade.php</strong> during result entry.
                                    </small>
                                </div>
                            </div>

                            <!-- Service Category -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="service_category_id" class="form-label">Service Category</label>
                                    <select name="service_category_id" 
                                            id="service_category_id" 
                                            class="form-select @error('service_category_id') is-invalid @enderror">
                                        <option value="">All Categories</option>
                                        @foreach($serviceCategories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('service_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Link to a service category to filter templates during medical service creation/editing.
                                    </small>
                                </div>
                            </div>

                            <!-- Investigation Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="investigation_type" class="form-label">Investigation Type</label>
                                    <input type="text" 
                                           name="investigation_type" 
                                           id="investigation_type"
                                           class="form-control @error('investigation_type') is-invalid @enderror"
                                           value="{{ old('investigation_type') }}" 
                                           placeholder="e.g., Laboratory, Radiology, Procedure">
                                    @error('investigation_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Specify the type of investigation this template is designed for.
                                    </small>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" 
                                              id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Detailed description of when and how to use this template">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Template Fields (JSON) -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="template_fields" class="form-label">Template Fields (JSON)</label>
                                    
                                    <!-- Input method selection -->
                                    <div class="mb-2">
                                        <div class="btn-group" role="group" aria-label="JSON Input Method">
                                            <input type="radio" class="btn-check" name="json_input_method" id="json_type" value="type" checked>
                                            <label class="btn btn-outline-primary btn-sm" for="json_type">
                                                <i class="fas fa-keyboard"></i> Type JSON
                                            </label>

                                            <input type="radio" class="btn-check" name="json_input_method" id="json_upload" value="upload">
                                            <label class="btn btn-outline-primary btn-sm" for="json_upload">
                                                <i class="fas fa-upload"></i> Upload JSON File
                                            </label>

                                            <input type="radio" class="btn-check" name="json_input_method" id="json_template" value="template">
                                            <label class="btn btn-outline-primary btn-sm" for="json_template">
                                                <i class="fas fa-code"></i> Use Template
                                            </label>
                                        </div>
                                    </div>

                                    <!-- JSON Textarea (default) -->
                                    <div id="json_type_container">
                                        <textarea name="template_fields" 
                                                  id="template_fields" 
                                                  class="form-control @error('template_fields') is-invalid @enderror" 
                                                  rows="8" 
                                                  placeholder='{"fields": [{"name": "field_name", "type": "text", "required": true}]}'
                                                  style="font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 13px;">{{ old('template_fields') }}</textarea>
                                    </div>

                                    <!-- JSON File Upload -->
                                    <div id="json_upload_container" style="display: none;">
                                        <div class="input-group">
                                            <input type="file" 
                                                   class="form-control" 
                                                   id="json_file" 
                                                   accept=".json,.txt" 
                                                   aria-describedby="json_file_help">
                                            <button class="btn btn-outline-secondary" type="button" id="load_json_btn">
                                                <i class="fas fa-upload"></i> Load File
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Upload a JSON file (.json, .txt)</small>
                                    </div>

                                    <!-- JSON Templates -->
                                    <div id="json_template_container" style="display: none;">
                                        <select class="form-select mb-2" id="json_template_select">
                                            <option value="">Select a template...</option>
                                            <option value="simple_form">Simple Form Fields</option>
                                            <option value="vital_signs">Vital Signs Form</option>
                                            <option value="lab_results">Laboratory Results</option>
                                            <option value="imaging_report">Imaging Report</option>
                                            <option value="procedure_notes">Procedure Notes</option>
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm" type="button" id="apply_template_btn">
                                            <i class="fas fa-magic"></i> Apply Template
                                        </button>
                                    </div>

                                    @error('template_fields')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <!-- JSON Validation Status -->
                                    <div id="json_validation_status" class="mt-1" style="display: none;">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Valid JSON
                                        </small>
                                    </div>
                                    <div id="json_validation_error" class="mt-1" style="display: none;">
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> <span id="json_error_message">Invalid JSON</span>
                                        </small>
                                    </div>

                                    <small class="form-text text-muted">
                                        Optional: JSON structure defining the fields and layout for this template.
                                        <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#jsonHelpModal">
                                            View examples <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </small>
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" 
                                           name="sort_order" 
                                           id="sort_order"
                                           class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', 0) }}" 
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Templates with lower sort order appear first in lists.
                                    </small>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               id="is_active"
                                               class="form-check-input" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Only active templates can be selected when creating/editing medical services.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Template
                                </button>
                                <a href="{{ route('result-templates.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JSON Help Modal -->
<div class="modal fade" id="jsonHelpModal" tabindex="-1" aria-labelledby="jsonHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jsonHelpModalLabel">JSON Template Examples</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="accordion" id="jsonExamplesAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSimple">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSimple">
                                Simple Form Fields
                            </button>
                        </h2>
                        <div id="collapseSimple" class="accordion-collapse collapse" data-bs-parent="#jsonExamplesAccordion">
                            <div class="accordion-body">
                                <pre><code class="json">{
  "fields": [
    {
      "name": "findings",
      "label": "Findings",
      "type": "textarea",
      "required": true,
      "placeholder": "Describe the findings..."
    },
    {
      "name": "impression",
      "label": "Impression",
      "type": "text",
      "required": true
    },
    {
      "name": "recommendations",
      "label": "Recommendations",
      "type": "textarea",
      "required": false
    }
  ]
}</code></pre>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingVitals">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVitals">
                                Vital Signs Form
                            </button>
                        </h2>
                        <div id="collapseVitals" class="accordion-collapse collapse" data-bs-parent="#jsonExamplesAccordion">
                            <div class="accordion-body">
                                <pre><code class="json">{
  "fields": [
    {
      "name": "blood_pressure_systolic",
      "label": "Systolic BP (mmHg)",
      "type": "number",
      "required": true,
      "min": 50,
      "max": 300
    },
    {
      "name": "blood_pressure_diastolic", 
      "label": "Diastolic BP (mmHg)",
      "type": "number",
      "required": true,
      "min": 30,
      "max": 200
    },
    {
      "name": "pulse_rate",
      "label": "Pulse Rate (bpm)",
      "type": "number",
      "required": true,
      "min": 30,
      "max": 200
    },
    {
      "name": "temperature",
      "label": "Temperature (°C)",
      "type": "number",
      "step": "0.1",
      "required": true,
      "min": 30,
      "max": 45
    }
  ]
}</code></pre>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingLab">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLab">
                                Laboratory Results
                            </button>
                        </h2>
                        <div id="collapseLab" class="accordion-collapse collapse" data-bs-parent="#jsonExamplesAccordion">
                            <div class="accordion-body">
                                <pre><code class="json">{
  "fields": [
    {
      "name": "test_name",
      "label": "Test Name",
      "type": "text",
      "required": true
    },
    {
      "name": "result_value",
      "label": "Result Value",
      "type": "text",
      "required": true
    },
    {
      "name": "reference_range",
      "label": "Reference Range",
      "type": "text",
      "required": false
    },
    {
      "name": "units",
      "label": "Units",
      "type": "text",
      "required": false
    },
    {
      "name": "flag",
      "label": "Flag",
      "type": "select",
      "options": ["Normal", "High", "Low", "Critical"],
      "required": false
    }
  ]
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // JSON input method switching
        const jsonInputMethods = document.querySelectorAll('input[name="json_input_method"]');
        const jsonTypeContainer = document.getElementById('json_type_container');
        const jsonUploadContainer = document.getElementById('json_upload_container');
        const jsonTemplateContainer = document.getElementById('json_template_container');
        const templateFieldsTextarea = document.getElementById('template_fields');
        
        jsonInputMethods.forEach(method => {
            method.addEventListener('change', function() {
                // Hide all containers
                jsonTypeContainer.style.display = 'none';
                jsonUploadContainer.style.display = 'none';
                jsonTemplateContainer.style.display = 'none';
                
                // Show selected container
                switch(this.value) {
                    case 'type':
                        jsonTypeContainer.style.display = 'block';
                        break;
                    case 'upload':
                        jsonUploadContainer.style.display = 'block';
                        break;
                    case 'template':
                        jsonTemplateContainer.style.display = 'block';
                        break;
                }
            });
        });
        
        // File upload handler
        const jsonFileInput = document.getElementById('json_file');
        const loadJsonBtn = document.getElementById('load_json_btn');
        
        loadJsonBtn.addEventListener('click', function() {
            const file = jsonFileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        // Parse to validate JSON
                        const jsonContent = JSON.parse(e.target.result);
                        // Format the JSON nicely
                        templateFieldsTextarea.value = JSON.stringify(jsonContent, null, 2);
                        validateJSON();
                        // Switch back to type view to show the loaded content
                        document.getElementById('json_type').checked = true;
                        jsonTypeContainer.style.display = 'block';
                        jsonUploadContainer.style.display = 'none';
                    } catch (error) {
                        alert('Invalid JSON file: ' + error.message);
                    }
                };
                reader.readAsText(file);
            } else {
                alert('Please select a JSON file first.');
            }
        });
        
        // Template selector
        const jsonTemplateSelect = document.getElementById('json_template_select');
        const applyTemplateBtn = document.getElementById('apply_template_btn');
        
        const jsonTemplates = {
            'simple_form': {
                "fields": [
                    {
                        "name": "findings",
                        "label": "Findings",
                        "type": "textarea",
                        "required": true,
                        "placeholder": "Describe the findings..."
                    },
                    {
                        "name": "impression",
                        "label": "Impression",
                        "type": "text",
                        "required": true
                    },
                    {
                        "name": "recommendations",
                        "label": "Recommendations",
                        "type": "textarea",
                        "required": false
                    }
                ]
            },
            'vital_signs': {
                "fields": [
                    {
                        "name": "blood_pressure_systolic",
                        "label": "Systolic BP (mmHg)",
                        "type": "number",
                        "required": true,
                        "min": 50,
                        "max": 300
                    },
                    {
                        "name": "blood_pressure_diastolic",
                        "label": "Diastolic BP (mmHg)",
                        "type": "number",
                        "required": true,
                        "min": 30,
                        "max": 200
                    },
                    {
                        "name": "pulse_rate",
                        "label": "Pulse Rate (bpm)",
                        "type": "number",
                        "required": true,
                        "min": 30,
                        "max": 200
                    },
                    {
                        "name": "temperature",
                        "label": "Temperature (°C)",
                        "type": "number",
                        "step": "0.1",
                        "required": true,
                        "min": 30,
                        "max": 45
                    }
                ]
            },
            'lab_results': {
                "fields": [
                    {
                        "name": "test_name",
                        "label": "Test Name",
                        "type": "text",
                        "required": true
                    },
                    {
                        "name": "result_value",
                        "label": "Result Value",
                        "type": "text",
                        "required": true
                    },
                    {
                        "name": "reference_range",
                        "label": "Reference Range",
                        "type": "text",
                        "required": false
                    },
                    {
                        "name": "units",
                        "label": "Units",
                        "type": "text",
                        "required": false
                    },
                    {
                        "name": "flag",
                        "label": "Flag",
                        "type": "select",
                        "options": ["Normal", "High", "Low", "Critical"],
                        "required": false
                    }
                ]
            },
            'imaging_report': {
                "fields": [
                    {
                        "name": "technique",
                        "label": "Technique",
                        "type": "textarea",
                        "required": true,
                        "placeholder": "Describe the imaging technique used..."
                    },
                    {
                        "name": "findings",
                        "label": "Findings",
                        "type": "textarea",
                        "required": true,
                        "placeholder": "Detailed imaging findings..."
                    },
                    {
                        "name": "impression",
                        "label": "Impression",
                        "type": "textarea",
                        "required": true,
                        "placeholder": "Clinical impression..."
                    },
                    {
                        "name": "recommendation",
                        "label": "Recommendation",
                        "type": "textarea",
                        "required": false,
                        "placeholder": "Clinical recommendations..."
                    }
                ]
            },
            'procedure_notes': {
                "fields": [
                    {
                        "name": "indication",
                        "label": "Indication",
                        "type": "textarea",
                        "required": true,
                        "placeholder": "Indication for the procedure..."
                    },
                    {
                        "name": "procedure_performed",
                        "label": "Procedure Performed",
                        "type": "textarea",
                        "required": true,
                        "placeholder": "Detailed description of procedure..."
                    },
                    {
                        "name": "complications",
                        "label": "Complications",
                        "type": "select",
                        "options": ["None", "Minor", "Major"],
                        "required": true
                    },
                    {
                        "name": "post_procedure_plan",
                        "label": "Post-Procedure Plan",
                        "type": "textarea",
                        "required": false,
                        "placeholder": "Follow-up care and instructions..."
                    }
                ]
            }
        };
        
        applyTemplateBtn.addEventListener('click', function() {
            const selectedTemplate = jsonTemplateSelect.value;
            if (selectedTemplate && jsonTemplates[selectedTemplate]) {
                templateFieldsTextarea.value = JSON.stringify(jsonTemplates[selectedTemplate], null, 2);
                validateJSON();
                // Switch back to type view
                document.getElementById('json_type').checked = true;
                jsonTypeContainer.style.display = 'block';
                jsonTemplateContainer.style.display = 'none';
            } else {
                alert('Please select a template first.');
            }
        });
        
        // JSON validation function
        function validateJSON() {
            const validationStatus = document.getElementById('json_validation_status');
            const validationError = document.getElementById('json_validation_error');
            const errorMessage = document.getElementById('json_error_message');
            
            if (!templateFieldsTextarea.value.trim()) {
                validationStatus.style.display = 'none';
                validationError.style.display = 'none';
                templateFieldsTextarea.classList.remove('is-invalid', 'is-valid');
                return;
            }
            
            try {
                JSON.parse(templateFieldsTextarea.value);
                templateFieldsTextarea.classList.remove('is-invalid');
                templateFieldsTextarea.classList.add('is-valid');
                validationStatus.style.display = 'block';
                validationError.style.display = 'none';
            } catch (e) {
                templateFieldsTextarea.classList.remove('is-valid');
                templateFieldsTextarea.classList.add('is-invalid');
                validationStatus.style.display = 'none';
                validationError.style.display = 'block';
                errorMessage.textContent = 'Invalid JSON: ' + e.message;
            }
        }
        
        // Real-time JSON validation
        templateFieldsTextarea.addEventListener('input', validateJSON);
        templateFieldsTextarea.addEventListener('blur', validateJSON);
        
        // Format JSON button functionality
        templateFieldsTextarea.addEventListener('keydown', function(e) {
            // Ctrl+Shift+F to format JSON
            if (e.ctrlKey && e.shiftKey && e.key === 'F') {
                e.preventDefault();
                try {
                    const parsed = JSON.parse(this.value);
                    this.value = JSON.stringify(parsed, null, 2);
                    validateJSON();
                } catch (error) {
                    // Don't format if invalid JSON
                }
            }
        });
        
        // Initial validation
        validateJSON();
    });
</script>
@endsection
