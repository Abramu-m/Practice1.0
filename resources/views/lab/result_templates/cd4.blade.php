{{-- CD4 Count Result Template --}}
<div class="result-template-container" style="background-color: #fff; padding: 15px; border-radius: 5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-chart-line"></i>
            CD4 Count Results
        </h6>
        <small class="text-muted">Complete the CD4 laboratory findings below</small>
    </div>

    {{-- Laboratory Information --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label"><strong>Lab Serial No:</strong></label>
            <input type="text" class="form-control form-control-sm" name="lab_serial_no" 
                   placeholder="e.g., CD4-{{ date('Y') }}-001">
        </div>
        <div class="col-md-4">
            <label class="form-label"><strong>Date Received:</strong></label>
            <input type="datetime-local" class="form-control form-control-sm" name="date_received" 
                   value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label"><strong>Date Analyzed:</strong></label>
            <input type="datetime-local" class="form-control form-control-sm" name="date_analyzed" 
                   value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
    </div>

    {{-- CD4 Results --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-microscope"></i> CD4 Count Results</h6>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label"><strong>CD4+ T-Cell Count:</strong></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="cd4_count" 
                               placeholder="Enter CD4 count" min="0" max="3000">
                        <span class="input-group-text">cells/μL</span>
                    </div>
                    <small class="text-muted">Normal range: 500-1200 cells/μL</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>CD4 Percentage:</strong></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="cd4_percentage" 
                               placeholder="Enter CD4 %" min="0" max="100" step="0.1">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Normal range: 30-60%</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label"><strong>Total Lymphocyte Count:</strong></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="total_lymphocytes" 
                               placeholder="Total lymphocytes" min="0">
                        <span class="input-group-text">cells/μL</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>CD8+ T-Cell Count:</strong></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="cd8_count" 
                               placeholder="CD8 count (optional)" min="0">
                        <span class="input-group-text">cells/μL</span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label"><strong>CD4/CD8 Ratio:</strong></label>
                    <input type="number" class="form-control" name="cd4_cd8_ratio" 
                           placeholder="CD4/CD8 ratio" min="0" step="0.01">
                    <small class="text-muted">Normal range: 1.0-2.5</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>Test Method:</strong></label>
                    <select class="form-select" name="test_method">
                        <option value="">Select method</option>
                        <option value="flow_cytometry">Flow Cytometry</option>
                        <option value="facs_count">FACS Count</option>
                        <option value="cyflow">CyFlow</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Clinical Interpretation --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-stethoscope"></i> Clinical Interpretation</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label"><strong>HIV Status Assessment:</strong></label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hiv_category" value="normal" id="normal_immunity">
                            <label class="form-check-label" for="normal_immunity">
                                Normal immunity (CD4 > 500)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hiv_category" value="mild" id="mild_suppression">
                            <label class="form-check-label" for="mild_suppression">
                                Mild immunosuppression (CD4 350-500)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hiv_category" value="moderate" id="moderate_suppression">
                            <label class="form-check-label" for="moderate_suppression">
                                Moderate immunosuppression (CD4 200-349)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hiv_category" value="severe" id="severe_suppression">
                            <label class="form-check-label" for="severe_suppression">
                                Severe immunosuppression (CD4 < 200)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hiv_category" value="aids" id="aids_defining">
                            <label class="form-check-label" for="aids_defining">
                                AIDS-defining immunosuppression (CD4 < 100)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="hiv_category" value="unknown" id="unknown_status">
                            <label class="form-check-label" for="unknown_status">
                                Unknown HIV status
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Clinical Significance:</strong></label>
                <textarea class="form-control" name="clinical_significance" rows="3" 
                          placeholder="Clinical interpretation of CD4 count results...

Examples:
- Results indicate good immune function
- Immunosuppression consistent with HIV infection
- Opportunistic infection risk assessment
- Response to antiretroviral therapy"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Recommendations:</strong></label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recommendations[]" value="art_initiation" id="art_initiation">
                    <label class="form-check-label" for="art_initiation">Consider ART initiation</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recommendations[]" value="oi_prophylaxis" id="oi_prophylaxis">
                    <label class="form-check-label" for="oi_prophylaxis">Opportunistic infection prophylaxis</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recommendations[]" value="repeat_testing" id="repeat_testing">
                    <label class="form-check-label" for="repeat_testing">Repeat testing in 3-6 months</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recommendations[]" value="specialist_referral" id="specialist_referral">
                    <label class="form-check-label" for="specialist_referral">HIV specialist referral</label>
                </div>
                <div class="mt-2">
                    <textarea class="form-control" name="additional_recommendations" rows="2" 
                              placeholder="Additional specific recommendations..."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Quality Control --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong>Laboratory Technician:</strong></label>
            <input type="text" class="form-control form-control-sm" name="technician" 
                   value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Reviewed by:</strong></label>
            <input type="text" class="form-control form-control-sm" name="reviewed_by" 
                   placeholder="Supervisor/Laboratory Director">
        </div>
    </div>

    {{-- Reference Values Alert --}}
    <div class="alert alert-warning">
        <h6><i class="fas fa-info-circle"></i> Reference Values</h6>
        <div class="row">
            <div class="col-md-6">
                <strong>Normal CD4 Count:</strong>
                <ul class="mb-0">
                    <li>Adults: 500-1200 cells/μL</li>
                    <li>Children: Age-dependent (higher)</li>
                </ul>
            </div>
            <div class="col-md-6">
                <strong>HIV Treatment Guidelines:</strong>
                <ul class="mb-0">
                    <li>CD4 < 350: Consider ART</li>
                    <li>CD4 < 200: Urgent ART + OI prophylaxis</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Certification --}}
    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-8">
                <strong>Quality Assurance:</strong>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="qa_controls[]" value="positive_control" id="pos_control">
                    <label class="form-check-label" for="pos_control">Positive Control ✓</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="qa_controls[]" value="negative_control" id="neg_control">
                    <label class="form-check-label" for="neg_control">Negative Control ✓</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="qa_controls[]" value="calibration" id="calibration_check">
                    <label class="form-check-label" for="calibration_check">Calibration Verified ✓</label>
                </div>
            </div>
            <div class="col-md-4">
                <strong>Result Status:</strong>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="result_verified" value="yes" id="verified">
                    <label class="form-check-label" for="verified">
                        Results verified and approved
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
