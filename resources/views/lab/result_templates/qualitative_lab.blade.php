{{-- Qualitative Lab Result Template (Positive/Negative/Invalid) --}}
<div class="result-template-container" style="background-color: #fff; padding: 15px; border-radius: 5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-vial"></i>
            Qualitative Lab Result
        </h6>
        <small class="text-muted">Select the result for each parameter</small>
    </div>

    @if(isset($investigation) && $investigation->medicalService)
    <script>
        window.qualitativeServiceData = {
            name: '{{ $investigation->medicalService->name }}'
        };
    </script>
    @endif

    {{-- Lab Parameters Table --}}
    <div class="table-responsive">
        <table class="table table-bordered" id="qualitativeParametersTable">
            <thead class="table-light">
                <tr>
                    <th width="30%">Parameter</th>
                    <th width="20%">Result</th>
                    <th width="15%">Status</th>
                    <th width="30%">Remarks</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody id="qualitativeParametersBody">
                @if(isset($investigation) && $investigation->results->count() > 0)
                    @foreach($investigation->results as $result)
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $loop->index }}][parameter_name]"
                                   value="{{ $result->parameter_name }}" required readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm qualitative-value-select"
                                    name="parameters[{{ $loop->index }}][value]"
                                    data-row-index="{{ $loop->index }}"
                                    onchange="updateQualitativeStatus(this)">
                                <option value="">-- Select --</option>
                                <option value="Negative" {{ $result->value === 'Negative' ? 'selected' : '' }}>Negative</option>
                                <option value="Positive" {{ $result->value === 'Positive' ? 'selected' : '' }}>Positive</option>
                                <option value="Invalid" {{ $result->value === 'Invalid' ? 'selected' : '' }}>Invalid</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm qual-status-select"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $loop->index }}][status]">
                                <option value="normal"   {{ ($result->status ?? 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ ($result->status ?? '') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="unknown"  {{ ($result->status ?? '') === 'unknown'  ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="parameters[{{ $loop->index }}][remarks]"
                                   value="{{ $result->remarks }}"
                                   placeholder="Optional remarks">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQualitativeParameter(this)">
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
                                   value="{{ isset($investigation) && $investigation->medicalService ? $investigation->medicalService->name : '' }}"
                                   required readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm qualitative-value-select"
                                    name="parameters[0][value]"
                                    data-row-index="0"
                                    onchange="updateQualitativeStatus(this)">
                                <option value="">-- Select --</option>
                                <option value="Negative">Negative</option>
                                <option value="Positive">Positive</option>
                                <option value="Invalid">Invalid</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm qual-status-select"
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
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQualitativeParameter(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Add Parameter Button --}}
    @if($investigation->medicalService->multiple_parameters)
    <div class="mt-3">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addQualitativeParameter()">
            <i class="fas fa-plus"></i> Add Parameter
        </button>
    </div>
    @endif

    {{-- Quality Control Section --}}
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
                           value="{{ now()->format('Y-m-d\TH:i') }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
            </div>
            <div class="row g-2 align-items-start mt-2">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Additional Comments:</strong></label>
                    <textarea class="form-control form-control-sm" name="additional_comments" rows="2"
                              placeholder="Any additional observations or comments..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let qualitativeParameterCount = {{ isset($investigation) && $investigation->results->count() > 0 ? $investigation->results->count() : 1 }};

// Map dropdown value → status code + badge style
const qualitativeStatusMap = {
    'Negative': { status: 'normal',   cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#155724;border-color:#28a745;' },
    'Positive': { status: 'abnormal', cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#dc3545;border-color:#dc3545;' },
    'Invalid':  { status: 'unknown',  cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;color:#6c757d;border-color:#6c757d;' },
    '':         { status: 'normal',   cssText: 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;-webkit-appearance:none;' },
};

function updateQualitativeStatus(selectEl) {
    const row = selectEl.closest('tr');
    if (!row) return;
    const statusSelect = row.querySelector('.qual-status-select');
    const remarksInput = row.querySelector('input[name*="[remarks]"]');
    if (!statusSelect) return;

    const chosen = qualitativeStatusMap[selectEl.value] || qualitativeStatusMap[''];
    statusSelect.value = chosen.status;
    statusSelect.style.cssText = chosen.cssText;

    // Auto-set remarks to the selected value (Positive/Negative/Invalid)
    if (remarksInput && selectEl.value) {
        remarksInput.value = selectEl.value;
    }
}

function addQualitativeParameter() {
    const tbody = document.getElementById('qualitativeParametersBody');
    const defaultParameter = window.qualitativeServiceData ? window.qualitativeServiceData.name : '';
    const idx = qualitativeParameterCount;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm"
                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                   name="parameters[${idx}][parameter_name]"
                   value="${defaultParameter}" required readonly>
        </td>
        <td>
            <select class="form-select form-select-sm qualitative-value-select"
                    name="parameters[${idx}][value]"
                    data-row-index="${idx}"
                    onchange="updateQualitativeStatus(this)">
                <option value="">-- Select --</option>
                <option value="Negative">Negative</option>
                <option value="Positive">Positive</option>
                <option value="Invalid">Invalid</option>
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm qual-status-select"
                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                    name="parameters[${idx}][status]">
                <option value="normal">Normal</option>
                <option value="abnormal">Abnormal</option>
                <option value="unknown">Unknown</option>
                   placeholder="Optional remarks">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQualitativeParameter(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    qualitativeParameterCount++;
}

function removeQualitativeParameter(button) {
    const tbody = document.getElementById('qualitativeParametersBody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
    } else {
        alert('At least one parameter is required.');
    }
}

// Initialize status styling on existing rows (e.g., when editing a saved result)
document.querySelectorAll('.qualitative-value-select').forEach(function(sel) {
    if (sel.value) updateQualitativeStatus(sel);
});
</script>

<style>
.qualitative-value-select option[value="Negative"] { color: #155724; }
.qualitative-value-select option[value="Positive"] { color: #dc3545; font-weight: 600; }
.qualitative-value-select option[value="Invalid"]  { color: #6c757d; }
</style>
