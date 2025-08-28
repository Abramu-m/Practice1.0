@if(isset($investigation) && $investigation && $investigation->id)
@php
    // Get existing form data for prefilling (consistent with other forms)
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

    @if($editMode)
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle"></i>
            <strong>{{ $isReadOnly ? 'Viewing Final Results' : 'Editing Existing Results' }}</strong> - Status: 
            <span class="badge bg-{{ $existingData['_result_status'] === 'draft' ? 'secondary' : ($existingData['_result_status'] === 'preliminary' ? 'warning' : 'success') }}">
                {{ ucfirst($existingData['_result_status']) }}
            </span>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12 mb-3">
            <div id="save-indicator" class="float-end"></div>
            <h6 class="text-primary mb-3">
                <i class="fas fa-x-ray"></i>
                Imaging Study Report
                <small class="text-muted">{{ $investigation->medicalService->name ?? 'Diagnostic Imaging' }}</small>
            </h6>
        </div>
    </div>

    <!-- Study Information -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Study Details</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="imaging_modality" class="form-select" {{ $isReadOnly ? 'disabled' : '' }} required>
                            <option value="">Select Modality</option>
                            <option value="xray" {{ ($formData['imaging_modality'] ?? '') === 'xray' ? 'selected' : '' }}>X-Ray</option>
                            <option value="ct" {{ ($formData['imaging_modality'] ?? '') === 'ct' ? 'selected' : '' }}>CT Scan</option>
                            <option value="mri" {{ ($formData['imaging_modality'] ?? '') === 'mri' ? 'selected' : '' }}>MRI</option>
                            <option value="ultrasound" {{ ($formData['imaging_modality'] ?? '') === 'ultrasound' ? 'selected' : '' }}>Ultrasound</option>
                            <option value="mammography" {{ ($formData['imaging_modality'] ?? '') === 'mammography' ? 'selected' : '' }}>Mammography</option>
                            <option value="nuclear" {{ ($formData['imaging_modality'] ?? '') === 'nuclear' ? 'selected' : '' }}>Nuclear Medicine</option>
                            <option value="fluoroscopy" {{ ($formData['imaging_modality'] ?? '') === 'fluoroscopy' ? 'selected' : '' }}>Fluoroscopy</option>
                        </select>
                        <label>Imaging Modality <span class="text-danger">*</span></label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <input type="text" name="body_part" class="form-control" {{ $isReadOnly ? 'readonly' : '' }} required
                               placeholder="e.g., Chest, Abdomen, Brain"
                               value="{{ $formData['body_part'] ?? '' }}">
                        <label>Body Part/Region <span class="text-danger">*</span></label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="study_type" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                            <option value="routine" {{ ($formData['study_type'] ?? '') === 'routine' ? 'selected' : '' }}>Routine</option>
                            <option value="contrast" {{ ($formData['study_type'] ?? '') === 'contrast' ? 'selected' : '' }}>With Contrast</option>
                            <option value="without_contrast" {{ ($formData['study_type'] ?? '') === 'without_contrast' ? 'selected' : '' }}>Without Contrast</option>
                            <option value="pre_post_contrast" {{ ($formData['study_type'] ?? '') === 'pre_post_contrast' ? 'selected' : '' }}>Pre & Post Contrast</option>
                        </select>
                        <label>Study Type</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Quality -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Technical Assessment</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="image_quality" class="form-select" required>
                            <option value="">Select Quality</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="adequate">Adequate</option>
                            <option value="poor">Poor - Limited Diagnostic Value</option>
                        </select>
                        <label>Image Quality <span class="text-danger">*</span></label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="positioning" class="form-select">
                            <option value="optimal">Optimal</option>
                            <option value="adequate">Adequate</option>
                            <option value="suboptimal">Suboptimal</option>
                        </select>
                        <label>Patient Positioning</label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <input type="text" name="technique_notes" class="form-control"
                               placeholder="Special techniques or protocols used">
                        <label>Technique Notes</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinical History -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="form-floating">
                <textarea name="clinical_history" class="form-control" style="min-height: 80px;" 
                          placeholder="Relevant clinical history and indication for study" 
                          {{ $isReadOnly ? 'readonly' : '' }}>{{ $formData['clinical_history'] ?? '' }}</textarea>
                <label>Clinical History & Indication</label>
            </div>
        </div>
    </div>

    <!-- Findings -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Radiological Findings</h6>
            
            <!-- Primary Findings -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Primary Findings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <textarea name="primary_findings" class="form-control" style="min-height: 120px;" 
                                          placeholder="Describe main radiological findings" 
                                          {{ $isReadOnly ? 'readonly' : '' }} required>{{ $formData['primary_findings'] ?? '' }}</textarea>
                                <label>Primary Findings <span class="text-danger">*</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Specific Measurements -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><strong>Measurements (if applicable)</strong></label>
                            <div id="measurements">
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <input type="text" name="measurements[0][structure]" class="form-control" 
                                               placeholder="Structure/Lesion">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="measurements[0][dimension]" class="form-control" 
                                               placeholder="Size/Dimension">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="measurements[0][unit]" class="form-control" 
                                               placeholder="Unit (mm, cm)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="addDynamicField('measurements', 'measurements')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System-specific findings -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Systematic Review</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <textarea name="bones_joints" class="form-control" style="min-height: 80px;" 
                                          placeholder="Bone and joint findings"></textarea>
                                <label>Bones & Joints</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <textarea name="soft_tissues" class="form-control" style="min-height: 80px;" 
                                          placeholder="Soft tissue findings"></textarea>
                                <label>Soft Tissues</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <textarea name="organs" class="form-control" style="min-height: 80px;" 
                                          placeholder="Organ findings"></textarea>
                                <label>Organs</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <textarea name="vascular" class="form-control" style="min-height: 80px;" 
                                          placeholder="Vascular findings"></textarea>
                                <label>Vascular</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Impression & Recommendations -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Impression & Recommendations</h6>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-floating">
                        <textarea name="impression" class="form-control" style="min-height: 100px;" 
                                  placeholder="Radiological impression and diagnosis" 
                                  {{ $isReadOnly ? 'readonly' : '' }} required>{{ $formData['impression'] ?? '' }}</textarea>
                        <label>Impression <span class="text-danger">*</span></label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-floating">
                        <textarea name="recommendations" class="form-control" style="min-height: 80px;" 
                                  placeholder="Recommendations for further imaging or clinical follow-up"
                                  {{ $isReadOnly ? 'readonly' : '' }}>{{ $formData['recommendations'] ?? '' }}</textarea>
                        <label>Recommendations</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Studies -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Comparison & Follow-up</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <select name="comparison_available" class="form-select">
                            <option value="no">No Prior Studies</option>
                            <option value="yes">Compared to Prior Studies</option>
                        </select>
                        <label>Prior Studies Available</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <input type="date" name="comparison_date" class="form-control">
                        <label>Date of Comparison Study</label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-floating">
                        <textarea name="comparison_notes" class="form-control" style="min-height: 80px;" 
                                  placeholder="Comparison with prior studies"></textarea>
                        <label>Comparison Notes</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Radiologist Information -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="radiologist_name" class="form-control" 
                       value="{{ auth()->user()->name ?? '' }}" required>
                <label>Radiologist Name <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="datetime-local" name="report_date" class="form-control" 
                       value="{{ now()->format('Y-m-d\TH:i') }}" required>
                <label>Report Date <span class="text-danger">*</span></label>
            </div>
        </div>
    </div>

    <!-- Image Uploads -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <label class="form-label"><strong>Image Files</strong></label>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Primary Images <span class="text-danger">*</span></label>
                    <input type="file" name="primary_images[]" class="form-control" 
                           accept="image/*,.dcm" multiple required>
                    <small class="text-muted">DICOM, JPEG, PNG formats (Max: 50MB each)</small>
                    <div id="primary-preview" class="mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Additional Views</label>
                    <input type="file" name="additional_images[]" class="form-control" 
                           accept="image/*,.dcm" multiple>
                    <small class="text-muted">Additional views or comparison images</small>
                    <div id="additional-preview" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Findings Alert -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Critical Findings Alert
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="critical_finding" class="form-select">
                                    <option value="no">No Critical Findings</option>
                                    <option value="yes">Critical Finding Present</option>
                                </select>
                                <label>Critical Finding</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="datetime-local" name="notification_time" class="form-control">
                                <label>Physician Notification Time</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating">
                                <textarea name="critical_details" class="form-control" style="min-height: 80px;" 
                                          placeholder="Details of critical finding and notification"></textarea>
                                <label>Critical Finding Details</label>
                            </div>
                        </div>
                    </div>
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
function previewMultipleImages(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).slice(0, 5).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail me-2 mb-2';
                    img.style.maxHeight = '100px';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
        
        if (input.files.length > 5) {
            const span = document.createElement('span');
            span.className = 'text-muted';
            span.textContent = `... and ${input.files.length - 5} more files`;
            preview.appendChild(span);
        }
    }
}

// Attach preview handlers
document.addEventListener('DOMContentLoaded', function() {
    const primaryInput = document.querySelector('input[name="primary_images[]"]');
    const additionalInput = document.querySelector('input[name="additional_images[]"]');
    
    if (primaryInput) {
        primaryInput.addEventListener('change', function() {
            previewMultipleImages(this, 'primary-preview');
        });
    }
    
    if (additionalInput) {
        additionalInput.addEventListener('change', function() {
            previewMultipleImages(this, 'additional-preview');
        });
    }
});

// Auto-refresh mechanisms removed to prevent CSRF token conflicts
</script>
