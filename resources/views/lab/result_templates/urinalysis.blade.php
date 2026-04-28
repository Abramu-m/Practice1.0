{{-- Urinalysis (Urine Analysis) Result Template --}}
@php
// Pre-populated urinalysis parameters grouped by section.
// Each: [name, unit, normal_range, value_type]  value_type: numeric | text | select
$uaParams = [
    // Physical
    ['Color',               '',      'Yellow (pale to amber)',   'text'],
    ['Clarity / Turbidity', '',      'Clear',                    'text'],
    ['Odor',                '',      'Aromatic',                 'text'],
    ['Specific Gravity',    '',      '1.005 – 1.030',            'numeric'],
    // Chemical
    ['pH',                  '',      '4.6 – 8.0',                'numeric'],
    ['Protein',             'mg/dL', '0 – 8',                    'numeric'],
    ['Glucose',             '',      'Negative',                 'select_neg'],
    ['Ketones',             '',      'Negative',                 'select_neg'],
    ['Bilirubin',           '',      'Negative',                 'select_neg'],
    ['Urobilinogen',        'mg/dL', '0.2 – 1.0',               'numeric'],
    ['Nitrites',            '',      'Negative',                 'select_neg'],
    ['Leukocyte Esterase',  '',      'Negative',                 'select_neg'],
    // Microscopic
    ['RBCs',                '/hpf',  '0 – 2',                    'numeric'],
    ['RBC Casts',           '',      'None',                     'select_none'],
    ['WBCs',                '/hpf',  '0 – 4',                    'numeric'],
    ['WBC Casts',           '',      'None',                     'select_none'],
    ['Epithelial Cells',    '/hpf',  '≤ 15 – 20',               'numeric'],
    ['Casts',               '',      'None',                     'select_none'],
    ['Crystals',            '',      'None',                     'select_none'],
    ['Bacteria',            '',      'None',                     'select_none'],
    ['Yeast',               '',      'None',                     'select_none'],
];

// Section labels — index of first param in each section
$sections = [
    0  => 'Physical Examination',
    4  => 'Chemical / Dipstick Examination',
    12 => 'Microscopic Examination',
];

// Load existing data for edit mode
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
            <i class="fas fa-flask"></i>
            Urinalysis (Urine Analysis)
        </h6>
        <small class="text-muted">Enter values for each parameter; status is auto-determined where possible</small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="uaParametersTable">
            <thead class="table-light">
                <tr>
                    <th style="width:22%">Parameter</th>
                    <th style="width:18%">Value</th>
                    <th style="width:8%">Unit</th>
                    <th style="width:18%">Normal Range</th>
                    <th style="width:12%">Status</th>
                    <th style="width:22%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($uaParams as $idx => $param)
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

                        {{-- Value input (type-dependent) --}}
                        <td>
                            @if($ptype === 'select_neg')
                                <select class="form-select form-select-sm ua-value-select"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="uaUpdateStatus(this, {{ $idx }}, 'neg')">
                                    <option value="">— Select —</option>
                                    @foreach(['Negative','Trace','1+','2+','3+','4+','Positive'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($ptype === 'select_none')
                                <select class="form-select form-select-sm ua-value-select"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="uaUpdateStatus(this, {{ $idx }}, 'none')">
                                    <option value="">— Select —</option>
                                    @foreach(['None','Rare','Few','Moderate','Many'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control form-control-sm ua-value-input"
                                       name="parameters[{{ $idx }}][value]"
                                       value="{{ $exValue }}"
                                       placeholder="{{ $ptype === 'numeric' ? '—' : 'e.g. Yellow' }}"
                                       data-idx="{{ $idx }}"
                                       data-range="{{ $prange }}"
                                       oninput="uaCheckNumeric(this)">
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
                            <select class="form-select form-select-sm ua-status-select"
                                    id="ua-status-{{ $idx }}"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $idx }}][status]">
                                <option value="normal"   {{ ($exStatus ?: 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ $exStatus === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="high"     {{ $exStatus === 'high'     ? 'selected' : '' }}>High</option>
                                <option value="low"      {{ $exStatus === 'low'      ? 'selected' : '' }}>Low</option>
                                <option value="unknown"  {{ $exStatus === 'unknown'  ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </td>

                        {{-- Remarks (free text) --}}
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
 * Auto-determine status for numeric urinalysis parameters.
 * Parses ranges like "1.005 – 1.030", "4.6 – 8.0", "0 – 8", "0 – 2", "≤ 15 – 20"
 */
function uaCheckNumeric(input) {
    const idx   = input.dataset.idx;
    const raw   = input.value.trim();
    const range = input.dataset.range || '';
    const sel   = document.getElementById('ua-status-' + idx);
    if (!sel) return;

    if (!raw || isNaN(parseFloat(raw))) {
        sel.value = 'normal';
        return;
    }
    const val = parseFloat(raw);

    // Normalize dashes/special chars, strip leading ≤/≥/< >
    let r = range.replace(/[–—−]/g, '-').replace(/[≤<]/g, '').replace(/[≥>]/g, '').trim();

    // Range: "lo - hi"
    const rangeMatch = r.match(/^(-?\d+(?:\.\d+)?)\s*-\s*(-?\d+(?:\.\d+)?)$/);
    if (rangeMatch) {
        const lo = parseFloat(rangeMatch[1]);
        const hi = parseFloat(rangeMatch[2]);
        if (val < lo)      sel.value = 'low';
        else if (val > hi) sel.value = 'high';
        else               sel.value = 'normal';
        return;
    }
    sel.value = 'normal';
}

/**
 * Auto-determine status for select-based parameters.
 * mode 'neg'  → Negative = normal, anything else = abnormal
 * mode 'none' → None = normal, anything else = abnormal
 */
function uaUpdateStatus(select, idx, mode) {
    const sel = document.getElementById('ua-status-' + idx);
    if (!sel) return;
    const val = select.value;
    if (!val) { sel.value = 'normal'; return; }
    if (mode === 'neg')  sel.value = (val === 'Negative') ? 'normal' : 'abnormal';
    if (mode === 'none') sel.value = (val === 'None')     ? 'normal' : 'abnormal';
}
</script>
