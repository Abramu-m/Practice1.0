@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">➕ Create New CDS Rule</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.rules.index') }}">Rules</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Rules
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.cds.rules.store') }}" method="POST" id="ruleForm">
        @csrf
        
        <div class="row">
            <!-- Main Rule Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📝 Rule Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rule_type_id">Rule Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('rule_type_id') is-invalid @enderror"
                                            id="rule_type_id" name="rule_type_id" required>
                                        <option value="">Select Rule Type</option>
                                        @foreach($ruleTypes as $type)
                                            <option value="{{ $type->id }}" data-rule-type-name="{{ $type->name }}" {{ old('rule_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->display_name }} ({{ $type->category->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('rule_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Category</label>
                                    <input type="text" class="form-control" id="category_display" 
                                           value="" readonly placeholder="Select rule type first">
                                    <small class="form-text text-muted">Category is determined by rule type</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rule Conditions -->
                <div class="card mt-3" id="generic-conditions-section">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🎯 Rule Conditions</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">These are the checks evaluated automatically. If all conditions match, the rule fires.</p>
                        <div id="conditions-container">
                            <div class="condition-group" data-index="0">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Field</label>
                                        <select class="form-control" name="conditions[0][field]">
                                            <option value="">Select Field</option>
                                            <option value="medication_name">Medication Name</option>
                                            <option value="patient_age">Patient Age</option>
                                            <option value="patient_weight">Patient Weight</option>
                                            <option value="dose_amount">Dose Amount</option>
                                            <option value="diagnosis">Diagnosis</option>
                                            <option value="allergy_history">Allergy History</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Operator</label>
                                        <select class="form-control" name="conditions[0][operator]">
                                            <option value="equals">Equals</option>
                                            <option value="not_equals">Not Equals</option>
                                            <option value="contains">Contains</option>
                                            <option value="not_contains">Not Contains</option>
                                            <option value="greater_than">Greater Than</option>
                                            <option value="less_than">Less Than</option>
                                            <option value="greater_equal">Greater or Equal</option>
                                            <option value="less_equal">Less or Equal</option>
                                            <option value="in">In List</option>
                                            <option value="not_in">Not In List</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Value Type</label>
                                        <select class="form-control" name="conditions[0][value_type]">
                                            <option value="string">String</option>
                                            <option value="integer">Integer</option>
                                            <option value="float">Float</option>
                                            <option value="boolean">Boolean</option>
                                            <option value="array">Array</option>
                                            <option value="json">JSON</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Value</label>
                                        <input type="text" class="form-control" name="conditions[0][value]" placeholder="Enter value">
                                    </div>
                                    <div class="col-md-2 align-self-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-condition" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <hr class="my-3">
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-condition">
                            <i class="fas fa-plus"></i> Add Condition
                        </button>
                    </div>
                </div>

                <!-- Lab Critical Value Builder -->
                <div class="card mt-3" id="lab-critical-section" style="display:none;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🧪 Lab Critical Value</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Pick the lab test and the result parameter to monitor. The rule fires when that parameter crosses the threshold for this exact test.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Lab Test</label>
                                    <select class="form-control" id="lab_test_picker"></select>
                                    <input type="hidden" name="lab_medical_service_id" id="lab_medical_service_id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Parameter</label>
                                    <select class="form-control" name="lab_parameter_key" id="lab_parameter_key" disabled>
                                        <option value="">Select a lab test first</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Operator</label>
                                    <select class="form-control" name="lab_operator" id="lab_operator">
                                        <option value="less_than">Less than (&lt;)</option>
                                        <option value="less_equal">Less than or equal (&le;)</option>
                                        <option value="greater_than">Greater than (&gt;)</option>
                                        <option value="greater_equal">Greater than or equal (&ge;)</option>
                                        <option value="equals">Equals (=)</option>
                                        <option value="not_equals">Not equals (&ne;)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Threshold</label>
                                    <input type="number" step="any" class="form-control" name="lab_threshold" id="lab_threshold" placeholder="e.g., 7">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Unit</label>
                                    <input type="text" class="form-control" id="lab_parameter_unit" readonly placeholder="—">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Content -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">💬 Alert Content (shown to clinician)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">This is the message and recommendation the clinician will see when this rule fires. It is <strong>not</strong> evaluated &mdash; see Conditions above for what triggers the alert.</p>
                        <div class="mb-3">
                            <label for="alert_message">Alert Message</label>
                            <textarea class="form-control @error('alert_message') is-invalid @enderror"
                                      id="alert_message" name="alert_message" rows="2">{{ old('alert_message') }}</textarea>
                            @error('alert_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="recommendation">Recommendation / Clinical Guidance</label>
                            <textarea class="form-control @error('recommendation') is-invalid @enderror"
                                      id="recommendation" name="recommendation" rows="3">{{ old('recommendation') }}</textarea>
                            @error('recommendation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- Settings Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">⚡ Rule Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="priority">Priority (1-10)</label>
                            <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                   id="priority" name="priority" min="1" max="10" value="{{ old('priority', 5) }}">
                            <small class="form-text text-muted">Higher numbers = higher priority</small>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="severity">Severity</label>
                            <select class="form-control @error('severity') is-invalid @enderror" 
                                    id="severity" name="severity">
                                <option value="info" {{ old('severity') == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="warning" {{ old('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('severity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">💾 Actions</h5>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Create Rule
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="previewRule()">
                            <i class="fas fa-eye"></i> Preview Rule
                        </button>
                        <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-danger w-100">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let conditionIndex = 1;

$(document).ready(function() {
    // Update category display when rule type changes
    $('#rule_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const text = selectedOption.text();
        const categoryMatch = text.match(/\(([^)]+)\)$/);
        const category = categoryMatch ? categoryMatch[1] : '';
        $('#category_display').val(category);
    });

    // Show the Lab Critical Value builder instead of the generic conditions
    // builder when "Lab Critical Value" is selected as the rule type.
    function toggleRuleTypeSections() {
        const ruleTypeName = $('#rule_type_id').find('option:selected').data('rule-type-name');
        if (ruleTypeName === 'lab_critical') {
            $('#generic-conditions-section').hide();
            $('#lab-critical-section').show();
        } else {
            $('#generic-conditions-section').show();
            $('#lab-critical-section').hide();
        }
    }
    $('#rule_type_id').on('change', toggleRuleTypeSections);
    toggleRuleTypeSections();

    // Lab Test picker (Select2 AJAX, reuses /api/medical-services/search)
    $('#lab_test_picker').select2({
        placeholder: 'Search lab test…',
        minimumInputLength: 2,
        ajax: {
            url: '/api/medical-services/search',
            dataType: 'json',
            data: params => ({ query: params.term, lab_only: true, limit: 20 }),
            processResults: res => ({
                results: (res.data || []).map(s => ({ id: s.id, text: s.name }))
            })
        }
    });

    // When a lab test is picked, lock its medical_service_id and load the
    // constrained parameter list for that test's result template.
    $('#lab_test_picker').on('change', function() {
        const serviceId = $(this).val();
        const $paramSelect = $('#lab_parameter_key');
        $('#lab_medical_service_id').val(serviceId || '');
        $('#lab_parameter_unit').val('');

        if (!serviceId) {
            $paramSelect.html('<option value="">Select a lab test first</option>').prop('disabled', true);
            return;
        }

        $paramSelect.html('<option value="">Loading…</option>').prop('disabled', true);

        $.getJSON('{{ route('admin.cds.rules.lab-parameters') }}', { medical_service_id: serviceId })
            .done(function(response) {
                const params = response.parameters || [];
                if (params.length === 0) {
                    $paramSelect.html('<option value="">No parameters available for this test</option>');
                    return;
                }
                let options = '<option value="">Select Parameter</option>';
                params.forEach(p => {
                    options += `<option value="${p.key}" data-unit="${p.unit ?? ''}">${p.label}${p.unit ? ' (' + p.unit + ')' : ''}</option>`;
                });
                $paramSelect.html(options).prop('disabled', false);
            });
    });

    // Reflect the selected parameter's unit next to the threshold input
    $('#lab_parameter_key').on('change', function() {
        $('#lab_parameter_unit').val($(this).find('option:selected').data('unit') || '');
    });

    // Add condition
    $('#add-condition').click(function() {
        const conditionHtml = `
            <div class="condition-group" data-index="${conditionIndex}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Field</label>
                        <select class="form-control" name="conditions[${conditionIndex}][field]">
                            <option value="">Select Field</option>
                            <option value="medication_name">Medication Name</option>
                            <option value="patient_age">Patient Age</option>
                            <option value="patient_weight">Patient Weight</option>
                            <option value="dose_amount">Dose Amount</option>
                            <option value="diagnosis">Diagnosis</option>
                            <option value="allergy_history">Allergy History</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Operator</label>
                        <select class="form-control" name="conditions[${conditionIndex}][operator]">
                            <option value="equals">Equals</option>
                            <option value="not_equals">Not Equals</option>
                            <option value="contains">Contains</option>
                            <option value="not_contains">Not Contains</option>
                            <option value="greater_than">Greater Than</option>
                            <option value="less_than">Less Than</option>
                            <option value="greater_equal">Greater or Equal</option>
                            <option value="less_equal">Less or Equal</option>
                            <option value="in">In List</option>
                            <option value="not_in">Not In List</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Value Type</label>
                        <select class="form-control" name="conditions[${conditionIndex}][value_type]">
                            <option value="string">String</option>
                            <option value="integer">Integer</option>
                            <option value="float">Float</option>
                            <option value="boolean">Boolean</option>
                            <option value="array">Array</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Value</label>
                        <input type="text" class="form-control" name="conditions[${conditionIndex}][value]" placeholder="Enter value">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-condition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <hr class="my-3">
            </div>`;

        $('#conditions-container').append(conditionHtml);
        updateRemoveButtons();
        conditionIndex++;
    });

    // Remove condition
    $(document).on('click', '.remove-condition', function() {
        $(this).closest('.condition-group').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        $('.remove-condition').prop('disabled', $('.condition-group').length <= 1);
    }
});

function previewRule() {
    // Collect form data and show preview
    const formData = new FormData(document.getElementById('ruleForm'));
    
    let preview = "Rule Preview:\n\n";
    preview += `Name: ${formData.get('name') || 'Untitled Rule'}\n`;
    preview += `Description: ${formData.get('description') || 'No description'}\n`;
    preview += `Priority: ${formData.get('priority')}\n`;
    preview += `Severity: ${formData.get('severity')}\n`;
    preview += `Status: ${formData.get('is_active') ? 'Active' : 'Inactive'}\n\n`;
    
    // Add conditions
    preview += "Conditions:\n";
    const conditions = document.querySelectorAll('.condition-group');
    conditions.forEach((condition, index) => {
        const field = condition.querySelector(`[name="conditions[${index}][field]"]`)?.value;
        const operator = condition.querySelector(`[name="conditions[${index}][operator]"]`)?.value;
        const value = condition.querySelector(`[name="conditions[${index}][value]"]`)?.value;
        
        if (field && operator && value) {
            preview += `- ${field} ${operator} ${value}\n`;
        }
    });

    alert(preview);
}
</script>
@endpush
@endsection