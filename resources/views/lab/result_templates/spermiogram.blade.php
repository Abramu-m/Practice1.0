{{-- Spermiogram Result Template --}}
@php
$sgParams = [
    // Specimen Information (idx 0–5)
    ['Specimen Quality',  '',             '',          'select_specimen'],
    ['Reason',            '',             '',          'text'],
    ['Color',             '',             '',          'text'],
    ['Volume',            'mL',           '1.5 – 4',   'numeric'],
    ['Viscosity',         '',             '',          'select_viscosity'],
    ['pH',                '',             '7.2 – 7.8', 'numeric'],
    // Sperm Parameters (idx 6–10)
    ['Sperm Count',       'million/mL',   '20 – 60',   'numeric'],
    ['Morphology',        '%',            '> 80',      'numeric_lo'],
    ['Motility',          '%',            '> 70',      'numeric_lo'],
    ['Progressive',       '%',            '> 60',      'numeric_lo'],
    ['Pus Cells',         '',             'None',      'select_none'],
    // Conclusion (idx 11)
    ['Conclusion',        '',             '',          'select_conclusion'],
];

$sections = [
    0  => 'Specimen Information',
    6  => 'Sperm Parameters',
    11 => 'Conclusion',
];

$existingParams = [];
if (isset($existingData['parameters'])) {
    $ep = $existingData['parameters'];
    if (is_string($ep)) $ep = json_decode($ep, true);
    if (is_array($ep)) {
        foreach ($ep as $p) {
            if (isset($p['parameter_name'])) $existingParams[$p['parameter_name']] = $p;
        }
    }
}
@endphp

<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary"><i class="fas fa-vial"></i> Spermiogram</h6>
        <small class="text-muted">Enter values for each parameter; status is auto-determined where possible</small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="sgParametersTable">
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
                @foreach($sgParams as $idx => $param)
                    @php
                        [$pname, $punit, $prange, $ptype] = $param;
                        $ex = $existingParams[$pname] ?? [];
                        $exValue   = $ex['value']   ?? '';
                        $exStatus  = $ex['status']  ?? '';
                        $exRemarks = $ex['remarks'] ?? '';
                    @endphp

                    @if(isset($sections[$idx]))
                        <tr class="table-secondary">
                            <td colspan="6" class="fw-semibold small text-uppercase py-1">
                                <i class="fas fa-angle-right me-1"></i>{{ $sections[$idx] }}
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][parameter_name]" value="{{ $pname }}" readonly>
                        </td>
                        <td>
                            @if($ptype === 'select_specimen')
                                <select class="form-select form-select-sm"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="sgStatusFromSelect(this, {{ $idx }}, 'Adequate')">
                                    <option value="">— Select —</option>
                                    @foreach(['Adequate','Inadequate'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($ptype === 'select_viscosity')
                                <select class="form-select form-select-sm"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="sgStatusFromSelect(this, {{ $idx }}, 'Liquefied')">
                                    <option value="">— Select —</option>
                                    @foreach(['Liquefied','Hyperviscous','Not liquefied'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($ptype === 'select_none')
                                <select class="form-select form-select-sm"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="sgStatusFromSelect(this, {{ $idx }}, 'None')">
                                    <option value="">— Select —</option>
                                    @foreach(['None','Rare','Few','Moderate','Many'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($ptype === 'select_conclusion')
                                <select class="form-select form-select-sm"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="sgConclusionStatus(this, {{ $idx }})">
                                    <option value="">— Select —</option>
                                    @foreach(['Normozoospermia','Oligozoospermia','Asthenozoospermia','Teratozoospermia','Azoospermia','Oligoasthenoteratozoospermia (OAT)','Normozoospermia with increased Pus Cells'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control form-control-sm sg-value-input"
                                       name="parameters[{{ $idx }}][value]"
                                       value="{{ $exValue }}"
                                       placeholder="{{ $ptype === 'numeric' || $ptype === 'numeric_lo' ? '—' : 'e.g. White' }}"
                                       data-idx="{{ $idx }}"
                                       data-range="{{ $prange }}"
                                       data-type="{{ $ptype }}"
                                       oninput="sgCheckValue(this)">
                            @endif
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][unit]" value="{{ $punit }}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][normal_range]" value="{{ $prange }}" readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm"
                                    id="sg-status-{{ $idx }}"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $idx }}][status]">
                                <option value="normal"   {{ ($exStatus ?: 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ $exStatus === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="high"     {{ $exStatus === 'high'     ? 'selected' : '' }}>High</option>
                                <option value="low"      {{ $exStatus === 'low'      ? 'selected' : '' }}>Low</option>
                                <option value="unknown"  {{ $exStatus === 'unknown'  ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="parameters[{{ $idx }}][remarks]" value="{{ $exRemarks }}"
                                   placeholder="Optional remarks">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

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
function sgCheckValue(input) {
    const idx   = input.dataset.idx;
    const raw   = input.value.trim();
    const range = input.dataset.range || '';
    const type  = input.dataset.type || '';
    const sel   = document.getElementById('sg-status-' + idx);
    if (!sel || !raw || isNaN(parseFloat(raw))) { if (sel) sel.value = 'normal'; return; }
    const val = parseFloat(raw);

    // Handle "> X" one-sided lower bound (numeric_lo)
    if (type === 'numeric_lo') {
        const loMatch = range.replace(/[≥>]/g, '').trim().match(/^(\d+(?:\.\d+)?)$/);
        if (loMatch) {
            sel.value = (val >= parseFloat(loMatch[1])) ? 'normal' : 'low';
            return;
        }
    }

    // Handle standard "lo – hi" range
    let r = range.replace(/[–—−]/g, '-').replace(/[≤<]/g, '').replace(/[≥>]/g, '').trim();
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

function sgStatusFromSelect(select, idx, normalVal) {
    const sel = document.getElementById('sg-status-' + idx);
    if (!sel) return;
    const val = select.value;
    if (!val) { sel.value = 'normal'; return; }
    sel.value = (val === normalVal) ? 'normal' : 'abnormal';
}

function sgConclusionStatus(select, idx) {
    const sel = document.getElementById('sg-status-' + idx);
    if (!sel) return;
    const val = select.value;
    if (!val) { sel.value = 'normal'; return; }
    sel.value = val.startsWith('Normo') ? 'normal' : 'abnormal';
}
</script>
