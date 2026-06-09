{{-- mRDT Malaria Result Template --}}
<div class="result-template-container" style="background-color: #fff; padding: 15px; border-radius: 5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-vial"></i>
            mRDT Malaria Result
        </h6>
        <small class="text-muted">Select the mRDT result for each parameter</small>
    </div>

    @php
        $formData       = $existingData ?? [];
        $mrdtParameters = $formData['parameters'] ?? [];
        $serviceName    = isset($investigation) && $investigation->medicalService ? $investigation->medicalService->name : '';
    @endphp

    @if(isset($investigation) && $investigation->medicalService)
    <script>
        window.mrdtServiceData = {
            name: '{{ $investigation->medicalService->name }}'
        };
    </script>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered" id="mrdtParametersTable">
            <thead class="table-light">
                <tr>
                    <th width="30%">Parameter</th>
                    <th width="20%">Result</th>
                    <th width="15%">Status</th>
                    <th width="30%">Remarks</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody id="mrdtParametersBody">
                @if(count($mrdtParameters) > 0)
                    @foreach($mrdtParameters as $index => $param)
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $index }}][parameter_name]"
                                   value="{{ $param['parameter_name'] ?? $serviceName }}"
                                   required readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm mrdt-value-select"
                                    name="parameters[{{ $index }}][value]"
                                    data-row-index="{{ $index }}"
                                    onchange="updateMrdtStatus(this)">
                                <option value="">-- Select --</option>
                                <option value="Indifferent"       {{ ($param['value'] ?? '') === 'Indifferent'       ? 'selected' : '' }}>Indifferent</option>
                                <option value="Negative"          {{ ($param['value'] ?? '') === 'Negative'          ? 'selected' : '' }}>Negative</option>
                                <option value="Positive"          {{ ($param['value'] ?? '') === 'Positive'          ? 'selected' : '' }}>Positive</option>
                                <option value="Positive. pf"      {{ ($param['value'] ?? '') === 'Positive. pf'      ? 'selected' : '' }}>Positive. pf</option>
                                <option value="Positive. pf, pan" {{ ($param['value'] ?? '') === 'Positive. pf, pan' ? 'selected' : '' }}>Positive. pf, pan</option>
                                <option value="Positive. pan"     {{ ($param['value'] ?? '') === 'Positive. pan'     ? 'selected' : '' }}>Positive. pan</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm mrdt-status-select"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $index }}][status]">
                                <option value="normal"   {{ ($param['status'] ?? 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ ($param['status'] ?? '') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="unknown"  {{ ($param['status'] ?? '') === 'unknown'  ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="parameters[{{ $index }}][remarks]"
                                   value="{{ $param['remarks'] ?? '' }}"
                                   placeholder="Optional remarks">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="removeMrdtParameter(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[0][parameter_name]"
                                   value="{{ $serviceName }}"
                                   required readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm mrdt-value-select"
                                    name="parameters[0][value]"
                                    data-row-index="0"
                                    onchange="updateMrdtStatus(this)">
                                <option value="">-- Select --</option>
                                <option value="Indifferent">Indifferent</option>
                                <option value="Negative">Negative</option>
                                <option value="Positive">Positive</option>
                                <option value="Positive. pf">Positive. pf</option>
                                <option value="Positive. pf, pan">Positive. pf, pan</option>
                                <option value="Positive. pan">Positive. pan</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm mrdt-status-select"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[0][status]">
                                <option value="normal">Normal</option>
                                <option value="abnormal">Abnormal</option>
                                <option value="unknown">Unknown</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="parameters[0][remarks]"
                                   placeholder="Optional remarks">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMrdtParameter(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($investigation->medicalService->multiple_parameters)
    <div class="mt-3">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMrdtParameter()">
            <i class="fas fa-plus"></i> Add Parameter
        </button>
    </div>
    @endif

    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Quality Control</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analyzed By:</strong></label>
                    <input type="text" class="form-control form-control-sm" name="analyzed_by"
                           value="{{ isset($currentUser) ? $currentUser->name : (auth()->user()->name ?? '') }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analysis Date:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="analysis_date"
                           value="{{ $formData['analysis_date'] ?? now()->format('Y-m-d\TH:i') }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
            </div>
            <div class="row g-2 align-items-start mt-2">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Additional Comments:</strong></label>
                    <textarea class="form-control form-control-sm" name="additional_comments" rows="2"
                              placeholder="Any additional observations or comments...">{{ $formData['additional_comments'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let mrdtParameterCount = {{ count($mrdtParameters) > 0 ? count($mrdtParameters) : 1 }};

const mrdtStatusMap = {
    'Indifferent':       { status: 'unknown',  cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#6c757d;border-color:#6c757d;' },
    'Negative':          { status: 'normal',   cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#155724;border-color:#28a745;' },
    'Positive':          { status: 'abnormal', cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#dc3545;border-color:#dc3545;' },
    'Positive. pf':      { status: 'abnormal', cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#dc3545;border-color:#dc3545;' },
    'Positive. pf, pan': { status: 'abnormal', cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#dc3545;border-color:#dc3545;' },
    'Positive. pan':     { status: 'abnormal', cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#dc3545;border-color:#dc3545;' },
    '':                  { status: 'normal',   cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;' },
};

function updateMrdtStatus(selectEl) {
    const row = selectEl.closest('tr');
    if (!row) return;
    const statusSelect = row.querySelector('.mrdt-status-select');
    const remarksInput = row.querySelector('input[name*="[remarks]"]');
    if (!statusSelect) return;

    const chosen = mrdtStatusMap[selectEl.value] || mrdtStatusMap[''];
    statusSelect.value = chosen.status;
    statusSelect.style.cssText = chosen.cssText;

    if (remarksInput && selectEl.value) {
        remarksInput.value = selectEl.value;
    }
}

function addMrdtParameter() {
    const tbody = document.getElementById('mrdtParametersBody');
    const defaultParameter = window.mrdtServiceData ? window.mrdtServiceData.name : '';
    const idx = mrdtParameterCount;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm"
                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                   name="parameters[${idx}][parameter_name]"
                   value="${defaultParameter}" required readonly>
        </td>
        <td>
            <select class="form-select form-select-sm mrdt-value-select"
                    name="parameters[${idx}][value]"
                    data-row-index="${idx}"
                    onchange="updateMrdtStatus(this)">
                <option value="">-- Select --</option>
                <option value="Indifferent">Indifferent</option>
                <option value="Negative">Negative</option>
                <option value="Positive">Positive</option>
                <option value="Positive. pf">Positive. pf</option>
                <option value="Positive. pf, pan">Positive. pf, pan</option>
                <option value="Positive. pan">Positive. pan</option>
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm mrdt-status-select"
                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                    name="parameters[${idx}][status]">
                <option value="normal">Normal</option>
                <option value="abnormal">Abnormal</option>
                <option value="unknown">Unknown</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm"
                   name="parameters[${idx}][remarks]"
                   placeholder="Optional remarks">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMrdtParameter(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    mrdtParameterCount++;
}

function removeMrdtParameter(button) {
    const tbody = document.getElementById('mrdtParametersBody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
    } else {
        alert('At least one parameter is required.');
    }
}

document.querySelectorAll('.mrdt-value-select').forEach(function(sel) {
    if (sel.value) updateMrdtStatus(sel);
});
</script>

<style>
.mrdt-value-select option[value="Indifferent"]       { color: #6c757d; }
.mrdt-value-select option[value="Negative"]          { color: #155724; }
.mrdt-value-select option[value="Positive"]          { color: #dc3545; font-weight: 600; }
.mrdt-value-select option[value="Positive. pf"]      { color: #dc3545; font-weight: 600; }
.mrdt-value-select option[value="Positive. pf, pan"] { color: #dc3545; font-weight: 600; }
.mrdt-value-select option[value="Positive. pan"]     { color: #dc3545; font-weight: 600; }
</style>
