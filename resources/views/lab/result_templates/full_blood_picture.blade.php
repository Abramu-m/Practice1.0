{{-- Full Blood Picture (Complete Blood Count) Result Template --}}
@php
// Each entry: [name, unit, normal_range, value_type]
// value_type: numeric | select_neg | select_none | text
// Normal ranges: approximate adult reference intervals (sex-neutral where possible)
$fbpParams = [
    // ── Red Blood Cells ──────────────────────────────────────
    ['Haemoglobin (Hb)',          'g/dL',       '11.5 – 17.5',  'numeric'],
    ['Haematocrit (HCT / PCV)',   '%',           '36 – 53',      'numeric'],
    ['RBC Count',                 '×10⁶/µL',    '3.8 – 5.9',    'numeric'],
    ['MCV',                       'fL',          '80 – 100',     'numeric'],
    ['MCH',                       'pg',          '27 – 33',      'numeric'],
    ['MCHC',                      'g/dL',        '31.5 – 35.7',  'numeric'],
    ['RDW',                       '%',           '11.5 – 14.5',  'numeric'],
    ['Reticulocytes',             '%',           '0.5 – 1.5',    'numeric'],
    // ── White Blood Cells ────────────────────────────────────
    ['Total WBC',                 '×10³/µL',    '4.0 – 11.0',   'numeric'],
    ['Neutrophils',               '×10³/µL',    '1.8 – 7.7',    'numeric'],
    ['Neutrophils %',             '%',           '40 – 75',      'numeric'],
    ['Lymphocytes',               '×10³/µL',    '1.0 – 4.8',    'numeric'],
    ['Lymphocytes %',             '%',           '20 – 45',      'numeric'],
    ['Monocytes',                 '×10³/µL',    '0.2 – 1.2',    'numeric'],
    ['Monocytes %',               '%',           '2 – 10',       'numeric'],
    ['Eosinophils',               '×10³/µL',    '0.0 – 0.7',    'numeric'],
    ['Eosinophils %',             '%',           '1 – 6',        'numeric'],
    ['Basophils',                 '×10³/µL',    '0.0 – 0.1',    'numeric'],
    ['Basophils %',               '%',           '0 – 1',        'numeric'],
    ['Band Neutrophils %',        '%',           '0 – 5',        'numeric'],
    // ── Platelets ────────────────────────────────────────────
    ['Platelet Count',            '×10³/µL',    '150 – 400',    'numeric'],
    ['MPV',                       'fL',          '7.5 – 12.5',   'numeric'],
    ['PDW',                       'fL',          '10 – 17',      'numeric'],
    // ── Peripheral Smear ─────────────────────────────────────
    ['Red Cell Morphology',       '',            'Normocytic normochromic', 'text'],
    ['Platelet Morphology',       '',            'Normal',       'text'],
    ['WBC Morphology / Comment',  '',            'Normal',       'text'],
];

// Section headers — key = index of first param in section
$sections = [
    0  => 'Red Blood Cells (Erythrocyte Indices)',
    8  => 'White Blood Cells (Leukocyte Count & Differential)',
    20 => 'Platelets (Thrombocyte Indices)',
    23 => 'Peripheral Blood Smear',
];

// Pre-populate from existing saved data (edit mode)
$existingParams = [];
if (isset($existingData['parameters'])) {
    $ep = $existingData['parameters'];
    if (is_string($ep)) $ep = json_decode($ep, true);
    if (is_array($ep)) {
        foreach ($ep as $p) {
            if (isset($p['parameter_name'])) {
                $existingParams[$p['parameter_name']] = $p;
            }
        }
    }
}
@endphp

<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary">
            <i class="fas fa-tint"></i>
            Full Blood Picture (Complete Blood Count)
        </h6>
        <small class="text-muted">Enter values for each parameter; status is auto-determined from reference ranges</small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="fbpParametersTable">
            <thead class="table-light">
                <tr>
                    <th style="width:24%">Parameter</th>
                    <th style="width:16%">Value</th>
                    <th style="width:10%">Unit</th>
                    <th style="width:18%">Normal Range</th>
                    <th style="width:12%">Status</th>
                    <th style="width:20%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fbpParams as $idx => $param)
                    @php
                        [$pname, $punit, $prange, $ptype] = $param;
                        $ex = $existingParams[$pname] ?? [];
                        $exValue   = $ex['value']   ?? '';
                        $exStatus  = $ex['status']  ?? '';
                        $exRemarks = $ex['remarks'] ?? '';
                    @endphp

                    {{-- Section header row --}}
                    @if(isset($sections[$idx]))
                        <tr class="table-secondary">
                            <td colspan="6" class="fw-semibold small text-uppercase py-1">
                                <i class="fas fa-angle-right me-1"></i>{{ $sections[$idx] }}
                            </td>
                        </tr>
                    @endif

                    <tr>
                        {{-- Parameter name (readonly) --}}
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][parameter_name]"
                                   value="{{ $pname }}" readonly>
                        </td>

                        {{-- Value --}}
                        <td>
                            @if($ptype === 'text')
                                <input type="text" class="form-control form-control-sm fbp-text-input"
                                       name="parameters[{{ $idx }}][value]"
                                       value="{{ $exValue }}"
                                       placeholder="Enter finding"
                                       data-idx="{{ $idx }}"
                                       data-expected="{{ $prange }}"
                                       oninput="fbpCheckText(this)">
                            @else
                                <input type="text" class="form-control form-control-sm fbp-value-input"
                                       name="parameters[{{ $idx }}][value]"
                                       value="{{ $exValue }}"
                                       placeholder="—"
                                       data-idx="{{ $idx }}"
                                       data-range="{{ $prange }}"
                                       oninput="fbpCheckNumeric(this)">
                            @endif
                        </td>

                        {{-- Unit (readonly) --}}
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][unit]"
                                   value="{{ $punit }}" readonly>
                        </td>

                        {{-- Normal range (readonly) --}}
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][normal_range]"
                                   value="{{ $prange }}" readonly>
                        </td>

                        {{-- Status (auto-set, readonly) --}}
                        <td>
                            <select class="form-select form-select-sm fbp-status-select"
                                    id="fbp-status-{{ $idx }}"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $idx }}][status]">
                                <option value="normal"   {{ ($exStatus ?: 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                                <option value="high"     {{ $exStatus === 'high'     ? 'selected' : '' }}>High</option>
                                <option value="low"      {{ $exStatus === 'low'      ? 'selected' : '' }}>Low</option>
                                <option value="critical" {{ $exStatus === 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="abnormal" {{ $exStatus === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="unknown"  {{ $exStatus === 'unknown'  ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </td>

                        {{-- Remarks --}}
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="parameters[{{ $idx }}][remarks]"
                                   value="{{ $exRemarks }}"
                                   placeholder="Optional remarks">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Quality Control --}}
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Quality Control</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analyzed By:</strong></label>
                    <input type="text" class="form-control form-control-sm lab-readonly" name="analyzed_by"
                           value="{{ isset($currentUser) ? $currentUser->name : (auth()->user()->name ?? '') }}" readonly>
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
                              placeholder="Any additional observations or comments...">{{ $existingData['additional_comments'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Auto-determine status for numeric FBP parameters.
 * Parses "lo – hi" style ranges (supports en-dash, em-dash, hyphen, spaces).
 * Also flags critical values (>30% outside normal range).
 */
function fbpCheckNumeric(input) {
    const idx   = input.dataset.idx;
    const raw   = input.value.trim();
    const range = input.dataset.range || '';
    const sel   = document.getElementById('fbp-status-' + idx);
    if (!sel) return;

    if (!raw || isNaN(parseFloat(raw))) {
        sel.value = 'normal';
        return;
    }
    const val = parseFloat(raw);

    // Normalize dashes and strip any leading ≤ ≥ < >
    let r = range.replace(/[–—−]/g, '-').replace(/[≤<≥>]/g, '').trim();
    const m = r.match(/^(-?\d+(?:\.\d+)?)\s*-\s*(-?\d+(?:\.\d+)?)$/);
    if (!m) { sel.value = 'normal'; return; }

    const lo = parseFloat(m[1]);
    const hi = parseFloat(m[2]);
    const span = hi - lo;

    if (val < lo) {
        sel.value = (span > 0 && val < lo - span * 0.3) ? 'critical' : 'low';
    } else if (val > hi) {
        sel.value = (span > 0 && val > hi + span * 0.3) ? 'critical' : 'high';
    } else {
        sel.value = 'normal';
    }
}

/**
 * For free-text morphology fields: mark abnormal if value differs from expected.
 */
function fbpCheckText(input) {
    const idx      = input.dataset.idx;
    const expected = (input.dataset.expected || '').toLowerCase().trim();
    const val      = input.value.toLowerCase().trim();
    const sel      = document.getElementById('fbp-status-' + idx);
    if (!sel || !val) return;
    sel.value = (expected && val === expected) ? 'normal' : 'abnormal';
}
</script>
