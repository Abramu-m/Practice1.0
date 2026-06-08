@if(isset($investigation) && $investigation && $investigation->id)
@php
    // Get existing form data for prefilling
    $formData = $existingData ?? [];
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
                <i class="fas fa-clipboard"></i>
                General Procedure Results
                @if($editMode)
                    <small class="text-muted">
                        - {{ $existingData['_result_status'] === 'draft' ? 'Editing Draft' : 'Editing Preliminary Results' }}
                    </small>
                @endif
            </h6>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>General Form:</strong> Use this form for procedures that don't have a specialized template.
            </div>
        </div>
    </div>

    <!-- Procedure Status -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="procedure_status" class="form-select" {{ $isReadOnly ? 'disabled' : '' }} required>
                    <option value="">Select Status</option>
                    <option value="completed" {{ ($formData['procedure_status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed Successfully</option>
                    <option value="partially_completed" {{ ($formData['procedure_status'] ?? '') === 'partially_completed' ? 'selected' : '' }}>Partially Completed</option>
                    <option value="cancelled" {{ ($formData['procedure_status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="rescheduled" {{ ($formData['procedure_status'] ?? '') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                </select>
                <label>Procedure Status <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="datetime-local" name="completion_time" class="form-control" 
                       value="{{ isset($formData['completion_time']) ? \Carbon\Carbon::parse($formData['completion_time'])->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" 
                       {{ $isReadOnly ? 'readonly' : '' }} required>
                <label>Completion Time <span class="text-danger">*</span></label>
            </div>
        </div>
    </div>

    <!-- Primary Results -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Procedure Results</h6>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-floating">
                        <textarea name="primary_results" class="form-control" style="min-height: 120px;" 
                                  placeholder="Main procedure findings and results" 
                                  {{ $isReadOnly ? 'readonly' : '' }} required>{{ $formData['primary_results'] ?? '' }}</textarea>
                        <label>Primary Results <span class="text-danger">*</span></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Observations -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Additional Findings</h6>
            <div id="additional-findings">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Finding Category</label>
                        <input type="text" name="findings[0][category]" class="form-control" 
                               placeholder="e.g., Appearance, Function, Measurement">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Observation</label>
                        <input type="text" name="findings[0][observation]" class="form-control" 
                               placeholder="Describe the finding or measurement">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Action</label>
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                onclick="addDynamicField('additional-findings', 'findings')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complications or Issues -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="complications" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="none" {{ (old('complications', $formData['complications'] ?? 'none') == 'none') ? 'selected' : '' }}>No Complications</option>
                    <option value="minor" {{ (old('complications', $formData['complications'] ?? '') == 'minor') ? 'selected' : '' }}>Minor Complications</option>
                    <option value="major" {{ (old('complications', $formData['complications'] ?? '') == 'major') ? 'selected' : '' }}>Major Complications</option>
                </select>
                <label>Complications</label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="complications_detail" class="form-control" 
                       value="{{ old('complications_detail', $formData['complications_detail'] ?? '') }}"
                       placeholder="Describe any complications" {{ $isReadOnly ? 'readonly' : '' }}>
                <label>Complication Details</label>
            </div>
        </div>
    </div>

    <!-- Overall Assessment -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="overall_assessment" class="form-select" required {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="">Select Assessment</option>
                    <option value="normal" {{ (old('overall_assessment', $formData['overall_assessment'] ?? '') == 'normal') ? 'selected' : '' }}>Normal/Within Normal Limits</option>
                    <option value="abnormal" {{ (old('overall_assessment', $formData['overall_assessment'] ?? '') == 'abnormal') ? 'selected' : '' }}>Abnormal Findings</option>
                    <option value="inconclusive" {{ (old('overall_assessment', $formData['overall_assessment'] ?? '') == 'inconclusive') ? 'selected' : '' }}>Inconclusive</option>
                    <option value="technical_failure" {{ (old('overall_assessment', $formData['overall_assessment'] ?? '') == 'technical_failure') ? 'selected' : '' }}>Technical Failure</option>
                </select>
                <label>Overall Assessment <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="clinical_significance" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="not_significant" {{ (old('clinical_significance', $formData['clinical_significance'] ?? 'not_significant') == 'not_significant') ? 'selected' : '' }}>Not Clinically Significant</option>
                    <option value="minor" {{ (old('clinical_significance', $formData['clinical_significance'] ?? '') == 'minor') ? 'selected' : '' }}>Minor Significance</option>
                    <option value="moderate" {{ (old('clinical_significance', $formData['clinical_significance'] ?? '') == 'moderate') ? 'selected' : '' }}>Moderate Significance</option>
                    <option value="major" {{ (old('clinical_significance', $formData['clinical_significance'] ?? '') == 'major') ? 'selected' : '' }}>Major Clinical Significance</option>
                </select>
                <label>Clinical Significance</label>
            </div>
        </div>
    </div>

    <!-- Professional Notes -->
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="form-floating">
                <textarea name="professional_notes" class="form-control" style="min-height: 100px;" 
                          placeholder="Professional interpretation and clinical correlation" {{ $isReadOnly ? 'readonly' : '' }}>{{ old('professional_notes', $formData['professional_notes'] ?? '') }}</textarea>
                <label>Professional Notes</label>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <textarea name="recommendations" class="form-control" style="min-height: 80px;" 
                          placeholder="Treatment recommendations" {{ $isReadOnly ? 'readonly' : '' }}>{{ old('recommendations', $formData['recommendations'] ?? '') }}</textarea>
                <label>Recommendations</label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <textarea name="follow_up" class="form-control" style="min-height: 80px;" 
                          placeholder="Follow-up requirements" {{ $isReadOnly ? 'readonly' : '' }}>{{ old('follow_up', $formData['follow_up'] ?? '') }}</textarea>
                <label>Follow-up Instructions</label>
            </div>
        </div>
    </div>

    <!-- Quality Assurance -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="quality_assurance" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="satisfactory" {{ (old('quality_assurance', $formData['quality_assurance'] ?? 'satisfactory') == 'satisfactory') ? 'selected' : '' }}>Satisfactory Quality</option>
                    <option value="suboptimal" {{ (old('quality_assurance', $formData['quality_assurance'] ?? '') == 'suboptimal') ? 'selected' : '' }}>Suboptimal Quality</option>
                    <option value="repeat_required" {{ (old('quality_assurance', $formData['quality_assurance'] ?? '') == 'repeat_required') ? 'selected' : '' }}>Repeat Required</option>
                </select>
                <label>Quality Assessment</label>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="urgency_flag" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="routine" {{ (old('urgency_flag', $formData['urgency_flag'] ?? 'routine') == 'routine') ? 'selected' : '' }}>Routine</option>
                    <option value="urgent" {{ (old('urgency_flag', $formData['urgency_flag'] ?? '') == 'urgent') ? 'selected' : '' }}>Urgent Review</option>
                    <option value="critical" {{ (old('urgency_flag', $formData['urgency_flag'] ?? '') == 'critical') ? 'selected' : '' }}>Critical - Immediate Action</option>
                </select>
                <label>Urgency Flag</label>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="reviewed_by_senior" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                    <option value="no" {{ (old('reviewed_by_senior', $formData['reviewed_by_senior'] ?? 'no') == 'no') ? 'selected' : '' }}>Not Required</option>
                    <option value="pending" {{ (old('reviewed_by_senior', $formData['reviewed_by_senior'] ?? '') == 'pending') ? 'selected' : '' }}>Pending Senior Review</option>
                    <option value="yes" {{ (old('reviewed_by_senior', $formData['reviewed_by_senior'] ?? '') == 'yes') ? 'selected' : '' }}>Reviewed by Senior</option>
                </select>
                <label>Senior Review</label>
            </div>
        </div>
    </div>

    <!-- Performer Information -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="performer_name" class="form-control" 
                       value="{{ old('performer_name', $formData['performer_name'] ?? auth()->user()->name ?? '') }}" required {{ $isReadOnly ? 'readonly' : '' }}>
                <label>Performer Name <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="performer_designation" class="form-control" 
                       value="{{ old('performer_designation', $formData['performer_designation'] ?? '') }}"
                       placeholder="e.g., Consultant, Specialist, Technician" {{ $isReadOnly ? 'readonly' : '' }}>
                <label>Designation</label>
            </div>
        </div>
    </div>

    <!-- File Attachments -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <label class="form-label"><strong>Supporting Files</strong></label>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Images/Photos</label>
                    <input type="file" name="procedure_images[]" class="form-control" 
                           accept="image/*" multiple onchange="previewImage(this, 'image-preview')">
                    <small class="text-muted">Photos of results, equipment readings, etc.</small>
                    <div id="image-preview" class="mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Documents</label>
                    <input type="file" name="procedure_documents[]" class="form-control" 
                           accept=".pdf,.doc,.docx,.txt" multiple>
                    <small class="text-muted">Reports, printouts, documentation</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('procedures.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div>
                    @if(!$isReadOnly)
                        <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                            <i class="fas fa-save"></i> Save as Draft
                        </button>
                        <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                            <i class="fas fa-clock"></i> Submit as Preliminary
                        </button>
                        <button type="submit" name="action" value="final" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Submit as Final
                        </button>
                    @else
                        @if($investigation->results && $investigation->results->first() && $investigation->results->first()->form_status !== 'final')
                            <a href="{{ route('procedures.show', $investigation->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Results
                            </a>
                        @endif
                    @endif
                </div>
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
