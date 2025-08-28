{{-- TB Investigation Result Template --}}
<div class="result-template-container" style="background-color: #fff; padding: 15px; border-radius: 5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-microscope"></i>
            TB Investigation Results
        </h6>
        <small class="text-muted">Complete the sections below based on laboratory findings</small>
    </div>

    {{-- Specimen Information --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong>Laboratory Serial No:</strong></label>
            <input type="text" class="form-control form-control-sm" name="lab_serial_no" 
                   placeholder="e.g., 775/2023">
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Date of Reception:</strong></label>
            <input type="datetime-local" class="form-control form-control-sm" name="date_reception" 
                   value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong>Specimen Type:</strong></label>
            <select class="form-select form-select-sm" name="specimen_type">
                <option value="">Select specimen type</option>
                <option value="sputum">Sputum</option>
                <option value="csf">CSF</option>
                <option value="peritoneal">Peritoneal fluid</option>
                <option value="pleural">Pleural fluid</option>
                <option value="gastric">Gastric aspirate</option>
                <option value="urine">Urine</option>
                <option value="tissue">Tissue biopsy</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Specimen Appearance:</strong></label>
            <input type="text" class="form-control form-control-sm" name="specimen_appearance" 
                   placeholder="e.g., Salivary, Purulent, Blood-stained">
        </div>
    </div>

    {{-- Xpert MTB/RIF Results --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-dna"></i> Xpert MTB/RIF Results</h6>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label"><strong>Test Method:</strong></label>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="test_method[]" value="zn" id="zn">
                        <label class="form-check-label me-3" for="zn">ZN Staining</label>
                        <input class="form-check-input" type="checkbox" name="test_method[]" value="fm" id="fm">
                        <label class="form-check-label" for="fm">Fluorescent Microscopy</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>Date of Examination:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="date_examination" 
                           value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Xpert Result:</strong></label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="xpert_result" value="not_detected" id="not_detected">
                    <label class="form-check-label" for="not_detected">
                        MTB Not Detected (N)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="xpert_result" value="detected_susceptible" id="detected_susceptible">
                    <label class="form-check-label" for="detected_susceptible">
                        MTB Detected - Rifampicin Resistance Not Detected (T)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="xpert_result" value="detected_resistant" id="detected_resistant">
                    <label class="form-check-label" for="detected_resistant">
                        MTB Detected - Rifampicin Resistance Detected (RR)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="xpert_result" value="detected_indeterminate" id="detected_indeterminate">
                    <label class="form-check-label" for="detected_indeterminate">
                        MTB Detected - Rifampicin Resistance Indeterminate (TI)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="xpert_result" value="error" id="error_result">
                    <label class="form-check-label" for="error_result">
                        Error / No Result / Invalid (I)
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label"><strong>Examined by:</strong></label>
                    <input type="text" class="form-control form-control-sm" name="examined_by" 
                           value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>Microscopy Grade:</strong></label>
                    <select class="form-select form-select-sm" name="microscopy_grade">
                        <option value="">Select grade</option>
                        <option value="neg">Negative</option>
                        <option value="scanty">Scanty (1-9 AFB/100 fields)</option>
                        <option value="1+">1+ (10-99 AFB/100 fields)</option>
                        <option value="2+">2+ (1-10 AFB/10 fields)</option>
                        <option value="3+">3+ (>10 AFB/field)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Findings --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-notes-medical"></i> Additional Laboratory Findings</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label"><strong>Clinical Correlation:</strong></label>
                <textarea class="form-control" name="clinical_correlation" rows="2" 
                          placeholder="Clinical correlation and recommendations..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label"><strong>Additional Comments:</strong></label>
                <textarea class="form-control" name="additional_comments" rows="3" 
                          placeholder="Any additional laboratory observations or recommendations..."></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label"><strong>Reviewed by:</strong></label>
                    <input type="text" class="form-control form-control-sm" name="reviewed_by" 
                           placeholder="Supervisor/Pathologist name">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>Review Date:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="review_date" 
                           value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Quality Control --}}
    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-6">
                <strong>Quality Control:</strong>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="quality_control[]" value="positive_control" id="pos_control">
                    <label class="form-check-label" for="pos_control">Positive Control ✓</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="quality_control[]" value="negative_control" id="neg_control">
                    <label class="form-check-label" for="neg_control">Negative Control ✓</label>
                </div>
            </div>
            <div class="col-md-6">
                <strong>Certification:</strong>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="certification" value="verified" id="verified">
                    <label class="form-check-label" for="verified">
                        Results verified and approved for release
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
