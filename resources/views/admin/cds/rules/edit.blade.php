@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3">✏️ Edit CDS Rule</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.rules.index') }}">Rules</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.rules.show', $rule) }}">{{ $rule->name }}</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.cds.rules.show', $rule) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> View Rule
                    </a>
                    <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Rules
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.cds.rules.update', $rule) }}" method="POST" id="ruleForm">
        @csrf
        @method('PUT')
        
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
                                   id="name" name="name" value="{{ old('name', $rule->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $rule->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_display">Current Category</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $rule->ruleType->category->name ?? 'No Category' }}" 
                                           readonly>
                                    <small class="form-text text-muted">Category is determined by rule type</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rule_type_id">Rule Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('rule_type_id') is-invalid @enderror" 
                                            id="rule_type_id" name="rule_type_id" required>
                                        <option value="">Select Rule Type</option>
                                        @foreach($ruleTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('rule_type_id', $rule->rule_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('rule_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rule Conditions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🎯 Rule Conditions</h5>
                    </div>
                    <div class="card-body">
                        <div id="conditions-container">
                            @forelse(old('conditions', $rule->conditions->toArray()) as $index => $condition)
                                <div class="condition-group" data-index="{{ $index }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Field</label>
                                            <select class="form-control" name="conditions[{{ $index }}][field]">
                                                <option value="">Select Field</option>
                                                <option value="medication_name" {{ (isset($condition['field']) ? $condition['field'] : $condition->field ?? '') == 'medication_name' ? 'selected' : '' }}>Medication Name</option>
                                                <option value="patient_age" {{ (isset($condition['field']) ? $condition['field'] : $condition->field ?? '') == 'patient_age' ? 'selected' : '' }}>Patient Age</option>
                                                <option value="patient_weight" {{ (isset($condition['field']) ? $condition['field'] : $condition->field ?? '') == 'patient_weight' ? 'selected' : '' }}>Patient Weight</option>
                                                <option value="dose_amount" {{ (isset($condition['field']) ? $condition['field'] : $condition->field ?? '') == 'dose_amount' ? 'selected' : '' }}>Dose Amount</option>
                                                <option value="diagnosis" {{ (isset($condition['field']) ? $condition['field'] : $condition->field ?? '') == 'diagnosis' ? 'selected' : '' }}>Diagnosis</option>
                                                <option value="allergy_history" {{ (isset($condition['field']) ? $condition['field'] : $condition->field ?? '') == 'allergy_history' ? 'selected' : '' }}>Allergy History</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Operator</label>
                                            <select class="form-control" name="conditions[{{ $index }}][operator]">
                                                <option value="equals" {{ (isset($condition['operator']) ? $condition['operator'] : $condition->operator ?? '') == 'equals' ? 'selected' : '' }}>Equals</option>
                                                <option value="contains" {{ (isset($condition['operator']) ? $condition['operator'] : $condition->operator ?? '') == 'contains' ? 'selected' : '' }}>Contains</option>
                                                <option value="greater_than" {{ (isset($condition['operator']) ? $condition['operator'] : $condition->operator ?? '') == 'greater_than' ? 'selected' : '' }}>Greater Than</option>
                                                <option value="less_than" {{ (isset($condition['operator']) ? $condition['operator'] : $condition->operator ?? '') == 'less_than' ? 'selected' : '' }}>Less Than</option>
                                                <option value="between" {{ (isset($condition['operator']) ? $condition['operator'] : $condition->operator ?? '') == 'between' ? 'selected' : '' }}>Between</option>
                                                <option value="in" {{ (isset($condition['operator']) ? $condition['operator'] : $condition->operator ?? '') == 'in' ? 'selected' : '' }}>In List</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Value</label>
                                            <input type="text" class="form-control" 
                                                   name="conditions[{{ $index }}][value]" 
                                                   value="{{ isset($condition['value']) ? $condition['value'] : $condition->value ?? '' }}"
                                                   placeholder="Enter value">
                                        </div>
                                        <div class="col-md-2 align-self-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-condition" {{ $index == 0 && count(old('conditions', $rule->conditions->toArray())) <= 1 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <hr class="my-3">
                                </div>
                            @empty
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
                                        <div class="col-md-3">
                                            <label>Operator</label>
                                            <select class="form-control" name="conditions[0][operator]">
                                                <option value="equals">Equals</option>
                                                <option value="contains">Contains</option>
                                                <option value="greater_than">Greater Than</option>
                                                <option value="less_than">Less Than</option>
                                                <option value="between">Between</option>
                                                <option value="in">In List</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
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
                            @endforelse
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-condition">
                            <i class="fas fa-plus"></i> Add Condition
                        </button>
                    </div>
                </div>

                <!-- Rule Parameters -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">⚙️ Rule Parameters</h5>
                    </div>
                    <div class="card-body">
                        <div id="parameters-container">
                            @forelse(old('parameters', $rule->parameters->toArray()) as $index => $parameter)
                                <div class="parameter-group" data-index="{{ $index }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Parameter Name</label>
                                            <input type="text" class="form-control" 
                                                   name="parameters[{{ $index }}][name]" 
                                                   value="{{ isset($parameter['name']) ? $parameter['name'] : $parameter->name ?? '' }}"
                                                   placeholder="e.g., max_dose">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Value</label>
                                            <input type="text" class="form-control" 
                                                   name="parameters[{{ $index }}][value]" 
                                                   value="{{ isset($parameter['value']) ? $parameter['value'] : $parameter->value ?? '' }}"
                                                   placeholder="Parameter value">
                                        </div>
                                        <div class="col-md-2 align-self-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-parameter" {{ $index == 0 && count(old('parameters', $rule->parameters->toArray())) <= 1 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <hr class="my-3">
                                </div>
                            @empty
                                <div class="parameter-group" data-index="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Parameter Name</label>
                                            <input type="text" class="form-control" name="parameters[0][name]" placeholder="e.g., max_dose">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Value</label>
                                            <input type="text" class="form-control" name="parameters[0][value]" placeholder="Parameter value">
                                        </div>
                                        <div class="col-md-2 align-self-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-parameter" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <hr class="my-3">
                                </div>
                            @endforelse
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-parameter">
                            <i class="fas fa-plus"></i> Add Parameter
                        </button>
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
                                   id="priority" name="priority" min="1" max="10" value="{{ old('priority', $rule->priority) }}">
                            <small class="form-text text-muted">Higher numbers = higher priority</small>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="severity">Severity</label>
                            <select class="form-control @error('severity') is-invalid @enderror" 
                                    id="severity" name="severity">
                                <option value="info" {{ old('severity', $rule->severity) == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="warning" {{ old('severity', $rule->severity) == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="critical" {{ old('severity', $rule->severity) == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('severity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message">Alert Message</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="3">{{ old('message', $rule->message) }}</textarea>
                            <small class="form-text text-muted">Message to display when rule is triggered</small>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                       {{ old('is_active', $rule->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="effective_from">Effective From</label>
                            <input type="datetime-local" class="form-control" id="effective_from" name="effective_from" 
                                   value="{{ old('effective_from', $rule->effective_from ? $rule->effective_from->format('Y-m-d\TH:i') : '') }}">
                        </div>

                        <div class="mb-3">
                            <label for="effective_until">Effective Until</label>
                            <input type="datetime-local" class="form-control" id="effective_until" name="effective_until" 
                                   value="{{ old('effective_until', $rule->effective_until ? $rule->effective_until->format('Y-m-d\TH:i') : '') }}">
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
                            <i class="fas fa-save"></i> Update Rule
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="previewRule()">
                            <i class="fas fa-eye"></i> Preview Changes
                        </button>
                        <a href="{{ route('admin.cds.rules.show', $rule) }}" class="btn btn-outline-danger w-100">
                            <i class="fas fa-times"></i> Cancel Changes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let conditionIndex = {{ count(old('conditions', $rule->conditions->toArray())) }};
let parameterIndex = {{ count(old('parameters', $rule->parameters->toArray())) }};

$(document).ready(function() {
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
                    <div class="col-md-3">
                        <label>Operator</label>
                        <select class="form-control" name="conditions[${conditionIndex}][operator]">
                            <option value="equals">Equals</option>
                            <option value="contains">Contains</option>
                            <option value="greater_than">Greater Than</option>
                            <option value="less_than">Less Than</option>
                            <option value="between">Between</option>
                            <option value="in">In List</option>
                        </select>
                    </div>
                    <div class="col-md-4">
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

    // Add parameter
    $('#add-parameter').click(function() {
        const parameterHtml = `
            <div class="parameter-group" data-index="${parameterIndex}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Parameter Name</label>
                        <input type="text" class="form-control" name="parameters[${parameterIndex}][name]" placeholder="e.g., max_dose">
                    </div>
                    <div class="col-md-6">
                        <label>Value</label>
                        <input type="text" class="form-control" name="parameters[${parameterIndex}][value]" placeholder="Parameter value">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-parameter">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <hr class="my-3">
            </div>`;
        
        $('#parameters-container').append(parameterHtml);
        updateRemoveButtons();
        parameterIndex++;
    });

    // Remove condition
    $(document).on('click', '.remove-condition', function() {
        $(this).closest('.condition-group').remove();
        updateRemoveButtons();
    });

    // Remove parameter
    $(document).on('click', '.remove-parameter', function() {
        $(this).closest('.parameter-group').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        $('.remove-condition').prop('disabled', $('.condition-group').length <= 1);
        $('.remove-parameter').prop('disabled', $('.parameter-group').length <= 1);
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
        const field = condition.querySelector(`select[name*="[field]"]`)?.value;
        const operator = condition.querySelector(`select[name*="[operator]"]`)?.value;
        const value = condition.querySelector(`input[name*="[value]"]`)?.value;
        
        if (field && operator && value) {
            preview += `- ${field} ${operator} ${value}\n`;
        }
    });

    alert(preview);
}
</script>
@endpush
@endsection