{{-- General Laboratory Result Template --}}
<div class="result-template-container" style="background-color: #fff; padding: 15px; border-radius: 5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-flask"></i>
            General Laboratory Results
        </h6>
        <small class="text-muted">Complete the laboratory findings below</small>
    </div>

    {{-- Laboratory Information --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label"><strong>Lab Serial No:</strong></label>
            <input type="text" class="form-control form-control-sm" name="lab_serial_no" 
                   placeholder="e.g., LAB-{{ date('Y') }}-001">
        </div>
        <div class="col-md-4">
            <label class="form-label"><strong>Date Received:</strong></label>
            <input type="datetime-local" class="form-control form-control-sm" name="date_received" 
                   value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label"><strong>Date Reported:</strong></label>
            <input type="datetime-local" class="form-control form-control-sm" name="date_reported" 
                   value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
    </div>

    {{-- Specimen Information --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong>Specimen Type:</strong></label>
            <select class="form-select form-select-sm" name="specimen_type">
                <option value="">Select specimen type</option>
                <option value="blood">Blood</option>
                <option value="serum">Serum</option>
                <option value="plasma">Plasma</option>
                <option value="urine">Urine</option>
                <option value="stool">Stool</option>
                <option value="csf">Cerebrospinal Fluid</option>
                <option value="sputum">Sputum</option>
                <option value="wound_swab">Wound Swab</option>
                <option value="throat_swab">Throat Swab</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Specimen Condition:</strong></label>
            <select class="form-select form-select-sm" name="specimen_condition">
                <option value="satisfactory">Satisfactory</option>
                <option value="hemolyzed">Hemolyzed</option>
                <option value="clotted">Clotted</option>
                <option value="insufficient">Insufficient quantity</option>
                <option value="contaminated">Contaminated</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>

    {{-- Laboratory Results --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Laboratory Findings</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label"><strong>Results:</strong></label>
                <textarea class="form-control" name="lab_results" rows="6" 
                          placeholder="Enter detailed laboratory findings...

Example formats:
- Hemoglobin: 12.5 g/dL (Normal: 12-16)
- White Blood Cells: 8,500/μL (Normal: 4,000-11,000)
- Glucose (Fasting): 95 mg/dL (Normal: 70-100)
- Creatinine: 1.0 mg/dL (Normal: 0.6-1.2)

Or narrative format for complex findings..."></textarea>
            </div>

            {{-- Quick Parameter Entry --}}
            <div class="border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                <h6 class="text-secondary mb-3">Quick Parameter Entry</h6>
                <div class="row" id="quick_parameters">
                    <div class="col-md-12">
                        <div class="row mb-2 parameter-row">
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" 
                                       name="quick_param_name[]" placeholder="Parameter name">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control form-control-sm" 
                                       name="quick_param_value[]" placeholder="Value">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control form-control-sm" 
                                       name="quick_param_unit[]" placeholder="Unit">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" 
                                       name="quick_param_range[]" placeholder="Normal range">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" name="quick_param_status[]">
                                    <option value="normal">Normal</option>
                                    <option value="abnormal">Abnormal</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuickParameter()">
                    <i class="fas fa-plus"></i> Add Parameter
                </button>
            </div>

            {{-- Interpretation --}}
            <div class="mb-3">
                <label class="form-label"><strong>Clinical Interpretation:</strong></label>
                <textarea class="form-control" name="clinical_interpretation" rows="3" 
                          placeholder="Clinical significance and interpretation of results..."></textarea>
            </div>

            {{-- Recommendations --}}
            <div class="mb-3">
                <label class="form-label"><strong>Recommendations:</strong></label>
                <textarea class="form-control" name="recommendations" rows="2" 
                          placeholder="Follow-up recommendations or additional tests suggested..."></textarea>
            </div>
        </div>
    </div>

    {{-- Laboratory Personnel --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label"><strong>Technologist:</strong></label>
            <input type="text" class="form-control form-control-sm" name="technologist" 
                   value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label"><strong>Pathologist/Supervisor:</strong></label>
            <input type="text" class="form-control form-control-sm" name="supervisor" 
                   placeholder="Supervisor name and signature">
        </div>
    </div>

    {{-- Quality Assurance --}}
    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-8">
                <strong>Quality Assurance:</strong>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="qa_checks[]" value="calibration" id="calibration">
                    <label class="form-check-label" for="calibration">Equipment Calibrated</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="qa_checks[]" value="controls" id="controls">
                    <label class="form-check-label" for="controls">Controls Passed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="qa_checks[]" value="reviewed" id="reviewed">
                    <label class="form-check-label" for="reviewed">Results Reviewed</label>
                </div>
            </div>
            <div class="col-md-4">
                <strong>Critical Values:</strong>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="critical_notified" value="yes" id="critical_notified">
                    <label class="form-check-label" for="critical_notified">
                        Critical values called to physician
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addQuickParameter() {
    const container = document.getElementById('quick_parameters');
    const newRow = document.createElement('div');
    newRow.className = 'col-md-12';
    newRow.innerHTML = `
        <div class="row mb-2 parameter-row">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" 
                       name="quick_param_name[]" placeholder="Parameter name">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" 
                       name="quick_param_value[]" placeholder="Value">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" 
                       name="quick_param_unit[]" placeholder="Unit">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" 
                       name="quick_param_range[]" placeholder="Normal range">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="quick_param_status[]">
                    <option value="normal">Normal</option>
                    <option value="abnormal">Abnormal</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
        </div>
    `;
    container.appendChild(newRow);
}
</script>
