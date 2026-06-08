@if(isset($investigation) && $investigation && $investigation->id)
@php
    // Get existing form data for prefilling
    $formData = $existingData ?? [];
    $parameters = $formData['parameters'] ?? [];
    $isReadOnly = isset($existingData['_result_status']) && $existingData['_result_status'] === 'final';
@endphp

@if($isReadOnly)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Final Results - Read Only:</strong> This report has been finalized and cannot be modified.
    </div>
@endif

<form action="{{ route('procedures.store-result', $investigation->id) }}" method="POST" enctype="multipart/form-data" class="procedure-form">
    @csrf
    <input type="hidden" name="result_type" value="{{ $investigation->medicalService->resultTemplate->code }}">
    <input type="hidden" name="investigation_id" value="{{ $investigation->id }}">

    <div class="row">
        <div class="col-md-12 mb-3">
            <div id="save-indicator" class="float-end"></div>
            <h6 class="text-primary mb-3">
                <i class="fas fa-file-medical"></i>
                Simple Laboratory Report
                @if($editMode)
                    <small class="text-muted">
                        - {{ $existingData['_result_status'] === 'draft' ? 'Editing Draft' : 'Editing Preliminary Results' }}
                    </small>
                @endif
            </h6>
        </div>
    </div>

    <!-- Test Parameters -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <label class="form-label"><strong>Test Parameters</strong></label>
            <div id="test-parameters">
                @if(count($parameters) > 0)
                    @foreach($parameters as $index => $parameter)
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Parameter Name <span class="text-danger">*</span></label>
                                <input type="text" name="parameters[{{ $index }}][parameter]" 
                                       class="form-control required-field" 
                                       placeholder="e.g., Hemoglobin, Glucose" 
                                       value="{{ $parameter['parameter'] ?? '' }}"
                                       {{ $isReadOnly ? 'readonly' : '' }} required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Result Value <span class="text-danger">*</span></label>
                                <input type="text" name="parameters[{{ $index }}][value]" 
                                       class="form-control required-field" 
                                       placeholder="e.g., 12.5, Normal" 
                                       value="{{ $parameter['value'] ?? '' }}"
                                       {{ $isReadOnly ? 'readonly' : '' }} required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unit</label>
                                <input type="text" name="parameters[{{ $index }}][unit]" 
                                       class="form-control" 
                                       placeholder="e.g., g/dL, mg/dL"
                                       value="{{ $parameter['unit'] ?? '' }}"
                                       {{ $isReadOnly ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Normal Range</label>
                                <input type="text" name="parameters[{{ $index }}][normal_range]" 
                                       class="form-control" 
                                       placeholder="e.g., 12-16"
                                       value="{{ $parameter['normal_range'] ?? '' }}"
                                       {{ $isReadOnly ? 'readonly' : '' }}>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Parameter Name <span class="text-danger">*</span></label>
                            <input type="text" name="parameters[0][parameter]" class="form-control required-field" 
                                   placeholder="e.g., Hemoglobin, Glucose" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Result Value <span class="text-danger">*</span></label>
                            <input type="text" name="parameters[0][value]" class="form-control required-field" 
                                   placeholder="e.g., 12.5, Normal" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit</label>
                            <input type="text" name="parameters[0][unit]" class="form-control" 
                                   placeholder="e.g., g/dL, mg/dL">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Normal Range</label>
                            <input type="text" name="parameters[0][normal_range]" class="form-control" 
                                   placeholder="e.g., 12-16">
                        </div>
                    </div>
                @endif
            </div>
            @if(!$isReadOnly)
                <button type="button" class="btn btn-outline-primary btn-sm" 
                        onclick="addDynamicField('test-parameters', 'parameters')">
                    <i class="fas fa-plus"></i> Add Parameter
                </button>
            @endif
        </div>
    </div>

    <!-- Overall Assessment -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="overall_assessment" class="form-select" {{ $isReadOnly ? 'disabled' : '' }} required>
                    <option value="">Select Assessment</option>
                    <option value="normal" {{ ($formData['overall_assessment'] ?? '') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="abnormal" {{ ($formData['overall_assessment'] ?? '') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                    <option value="borderline" {{ ($formData['overall_assessment'] ?? '') === 'borderline' ? 'selected' : '' }}>Borderline</option>
                    <option value="critical" {{ ($formData['overall_assessment'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <label>Overall Assessment <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="priority_flag" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="">Normal Priority</option>
                    <option value="urgent" {{ ($formData['priority_flag'] ?? '') === 'urgent' ? 'selected' : '' }}>Urgent - Notify Doctor</option>
                    <option value="critical" {{ ($formData['priority_flag'] ?? '') === 'critical' ? 'selected' : '' }}>Critical - Immediate Action</option>
                </select>
                <label>Priority Flag</label>
            </div>
        </div>
    </div>

    <!-- Technician Details -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="technician_name" class="form-control" 
                       value="{{ $formData['technician_name'] ?? auth()->user()->name ?? '' }}" 
                       {{ $isReadOnly ? 'readonly' : '' }} required>
                <label>Technician Name <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="datetime-local" name="verified_at" class="form-control" 
                       value="{{ isset($formData['verified_at']) ? \Carbon\Carbon::parse($formData['verified_at'])->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" 
                       {{ $isReadOnly ? 'readonly' : '' }} required>
                <label>Verification Time <span class="text-danger">*</span></label>
            </div>
        </div>
    </div>

    <!-- Clinical Notes -->
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="form-floating">
                <textarea name="clinical_notes" class="form-control" style="min-height: 100px;" 
                          placeholder="Any clinical observations or notes" {{ $isReadOnly ? 'readonly' : '' }}>{{ $formData['clinical_notes'] ?? '' }}</textarea>
                <label>Clinical Notes</label>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="form-floating">
                <textarea name="recommendations" class="form-control" style="min-height: 80px;" 
                          placeholder="Follow-up recommendations or additional tests needed" {{ $isReadOnly ? 'readonly' : '' }}>{{ $formData['recommendations'] ?? '' }}</textarea>
                <label>Recommendations</label>
            </div>
        </div>
    </div>

    <!-- File Attachments -->
    @if(!$isReadOnly)
        <div class="row">
            <div class="col-md-12 mb-4">
                <label class="form-label"><strong>Attachments</strong></label>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Report Image/Document</label>
                        <input type="file" name="report_image" class="form-control" 
                               accept="image/*,.pdf,.doc,.docx" onchange="previewImage(this, 'report-preview')">
                        <small class="text-muted">Formats: JPG, PNG, PDF, DOC (Max: 5MB)</small>
                        <div id="report-preview" class="mt-2"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Additional Files</label>
                        <input type="file" name="additional_files[]" class="form-control" 
                               accept="image/*,.pdf,.doc,.docx" multiple>
                        <small class="text-muted">Multiple files allowed</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Existing Attachments (if any) -->
    @if($editMode && isset($formData['attachments']) && count($formData['attachments']) > 0)
        <div class="row">
            <div class="col-md-12 mb-4">
                <label class="form-label"><strong>Current Attachments</strong></label>
                <div class="row">
                    @foreach($formData['attachments'] as $attachment)
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body p-2 text-center">
                                    @if(str_contains($attachment['mime_type'] ?? '', 'image'))
                                        <img src="{{ asset('storage/' . $attachment['path']) }}" 
                                             class="img-fluid" style="max-height: 100px; cursor: pointer;"
                                             onclick="showImageModal('{{ asset('storage/' . $attachment['path']) }}', '{{ $attachment['original_name'] ?? 'Image' }}')">
                                    @else
                                        <i class="fas fa-file fa-3x text-primary"></i>
                                        <br>
                                        <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            View {{ $attachment['original_name'] ?? 'File' }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('procedures.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if($editMode && isset($existingData['_result_id']))
                        <a href="{{ route('procedures.view-results', $investigation) }}" class="btn btn-outline-info ms-2" target="_blank">
                            <i class="fas fa-file-pdf"></i> View Current Report
                        </a>
                    @endif
                </div>
                @if(!$isReadOnly)
                    <div>
                        @if($editMode)
                            <!-- Edit mode buttons -->
                            @if($existingData['_result_status'] === 'draft')
                                <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                    <i class="fas fa-save"></i> Update Draft
                                </button>
                                <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                    <i class="fas fa-clock"></i> Save as Preliminary
                                </button>
                                <button type="submit" name="action" value="final" class="btn btn-success">
                                    <i class="fas fa-lock"></i> Finalize Results
                                </button>
                            @elseif($existingData['_result_status'] === 'preliminary')
                                <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Update Preliminary
                                </button>
                                <button type="submit" name="action" value="final" class="btn btn-success">
                                    <i class="fas fa-lock"></i> Finalize Results
                                </button>
                            @endif
                        @else
                            <!-- New result mode buttons -->
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                <i class="fas fa-clock"></i> Submit as Preliminary
                            </button>
                            <button type="submit" name="action" value="final" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Submit Final Results
                            </button>
                        @endif
                    </div>
                @else
                    <div>
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-lock"></i> Results Finalized
                        </span>
                        @if(isset($existingData['_updated_at']))
                            <small class="text-muted ms-2">
                                Finalized: {{ \Carbon\Carbon::parse($existingData['_updated_at'])->format('M d, Y H:i') }}
                            </small>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>
@else
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Error: Investigation not found. Please return to the procedures list and try again.
    </div>
@endif

<script>
function addDynamicField(containerId, fieldName) {
    const container = document.getElementById(containerId);
    const fieldCount = container.children.length;
    
    const newField = document.createElement('div');
    newField.className = 'row mb-3';
    newField.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="${fieldName}[${fieldCount}][parameter]" 
                   class="form-control" placeholder="Parameter Name" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="${fieldName}[${fieldCount}][value]" 
                   class="form-control" placeholder="Result Value" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="${fieldName}[${fieldCount}][unit]" 
                   class="form-control" placeholder="Unit">
        </div>
        <div class="col-md-2">
            <input type="text" name="${fieldName}[${fieldCount}][normal_range]" 
                   class="form-control" placeholder="Normal Range">
            <button type="button" class="btn btn-outline-danger btn-sm mt-1" 
                    onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newField);
}
</script>
