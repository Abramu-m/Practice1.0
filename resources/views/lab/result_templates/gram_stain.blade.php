{{-- Gram Stain Microscopy Result Template --}}
@php
$params = [
    [
        'name'    => 'Gram-positive Cocci',
        'options' => ['Not Seen', 'Rare', 'Few', 'Moderate', 'Many'],
        'normal'  => ['Not Seen'],
        'critical'=> ['Many'],
    ],
    [
        'name'    => 'Gram-negative Cocci',
        'options' => ['Not Seen', 'Rare', 'Few', 'Moderate', 'Many'],
        'normal'  => ['Not Seen'],
        'critical'=> ['Many'],
    ],
    [
        'name'    => 'Gram-positive Bacilli',
        'options' => ['Not Seen', 'Rare', 'Few', 'Moderate', 'Many'],
        'normal'  => ['Not Seen'],
        'critical'=> ['Many'],
    ],
    [
        'name'    => 'Gram-negative Bacilli',
        'options' => ['Not Seen', 'Rare', 'Few', 'Moderate', 'Many'],
        'normal'  => ['Not Seen'],
        'critical'=> ['Many'],
    ],
    [
        'name'    => 'WBCs (pus cells)',
        'options' => ['None', 'Rare (<5/hpf)', 'Few (5–25/hpf)', 'Moderate (26–100/hpf)', 'Many (>100/hpf)'],
        'normal'  => ['None', 'Rare (<5/hpf)'],
        'critical'=> ['Many (>100/hpf)'],
    ],
    [
        'name'    => 'Epithelial Cells',
        'options' => ['None', 'Rare', 'Few', 'Moderate', 'Many'],
        'normal'  => ['None'],
        'critical'=> [],
    ],
    [
        'name'    => 'Interpretation',
        'options' => ['No organisms seen', 'Gram-positive cocci (clusters)', 'Gram-positive cocci (chains)', 'Gram-negative diplococci', 'Gram-negative bacilli', 'Mixed flora', 'Further culture recommended'],
        'normal'  => ['No organisms seen'],
        'critical'=> ['Gram-negative diplococci'],
    ],
];
$existingParams = [];
if (isset($existingData['parameters'])) {
    $ep = $existingData['parameters'];
    if (is_string($ep)) $ep = json_decode($ep, true);
    if (is_array($ep)) foreach ($ep as $p) if (isset($p['parameter_name'])) $existingParams[$p['parameter_name']] = $p;
}
@endphp

<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary"><i class="fas fa-microscope"></i> Gram Stain Microscopy</h6>
        <small class="text-muted">Gram stain examination of specimen for bacterial morphology</small>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr><th style="width:28%">Parameter</th><th style="width:32%">Result</th><th style="width:15%">Status</th><th style="width:25%">Remarks</th></tr>
            </thead>
            <tbody>
                @foreach($params as $idx => $param)
                @php $ex = $existingParams[$param['name']] ?? []; @endphp
                <tr>
                    <td><input type="text" class="form-control form-control-sm" style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1" name="parameters[{{ $idx }}][parameter_name]" value="{{ $param['name'] }}" readonly></td>
                    <td>
                        <select class="form-select form-select-sm" name="parameters[{{ $idx }}][value]"
                                data-idx="{{ $idx }}"
                                data-normal="{{ implode('|', $param['normal']) }}"
                                data-critical="{{ implode('|', $param['critical']) }}"
                                onchange="multiOptStatus(this, 'gs')">
                            <option value="">— Select —</option>
                            @foreach($param['options'] as $opt)
                                <option value="{{ $opt }}" {{ ($ex['value'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" id="gs-status-{{ $idx }}" style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1" name="parameters[{{ $idx }}][status]">
                            <option value="normal"   {{ ($ex['status'] ?? 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                            <option value="abnormal" {{ ($ex['status'] ?? '') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                            <option value="critical" {{ ($ex['status'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="unknown"  {{ ($ex['status'] ?? '') === 'unknown'  ? 'selected' : '' }}>Unknown</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="parameters[{{ $idx }}][remarks]" value="{{ $ex['remarks'] ?? '' }}" placeholder="Optional remarks"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card mt-4">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="fas fa-check-circle"></i> Quality Control</h6></div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analyzed By:</strong></label>
                    <input type="text" class="form-control form-control-sm lab-readonly" name="analyzed_by" value="{{ isset($currentUser) ? $currentUser->name : (auth()->user()->name ?? '') }}" readonly>
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analysis Date:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="analysis_date" value="{{ now()->format('Y-m-d\TH:i') }}" readonly style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Additional Comments:</strong></label>
                    <textarea class="form-control form-control-sm" name="additional_comments" rows="2" placeholder="Any additional observations...">{{ $existingData['additional_comments'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
if (typeof multiOptStatus === 'undefined') {
    function multiOptStatus(select, prefix) {
        const idx      = select.dataset.idx;
        const normals  = (select.dataset.normal  || '').split('|');
        const criticals= (select.dataset.critical || '').split('|').filter(Boolean);
        const val      = select.value;
        const sel      = document.getElementById(prefix + '-status-' + idx);
        if (!sel || !val) return;
        if (criticals.includes(val))    sel.value = 'critical';
        else if (normals.includes(val)) sel.value = 'normal';
        else                            sel.value = 'abnormal';
    }
}
</script>
