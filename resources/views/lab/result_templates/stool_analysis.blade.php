{{-- Stool Analysis Result Template --}}
@php
$saParams = [
    ['Pus Cells',               '', 'None',     'select_none'],
    ['RBC',                     '', 'None',      'select_none'],
    ['Hookworms',               '', 'Negative',  'select_neg'],
    ['Ascaris lumbricoides',    '', 'Negative',  'select_neg'],
    ['Amoeba cysts',            '', 'Negative',  'select_neg'],
    ['Enterobius vermicularis', '', 'Negative',  'select_neg'],
    ['Trichomonas hominis',     '', 'Negative',  'select_neg'],
    ['Giardia lamblia',         '', 'Negative',  'select_neg'],
    ['Schistosomiasis mansoni', '', 'Negative',  'select_neg'],
    ['Taenia solium',           '', 'Negative',  'select_neg'],
    ['Trichuris trichiura',     '', 'Negative',  'select_neg'],
    ['Strongyloides',           '', 'Negative',  'select_neg'],
];

$sections = [0 => 'Stool Analysis'];

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
        <h6 class="text-primary"><i class="fas fa-microscope"></i> Stool Analysis</h6>
        <small class="text-muted">Enter values for each parameter; status is auto-determined where possible</small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="saParametersTable">
            <thead class="table-light">
                <tr>
                    <th style="width:26%">Parameter</th>
                    <th style="width:22%">Value</th>
                    <th style="width:14%">Normal</th>
                    <th style="width:12%">Status</th>
                    <th style="width:26%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saParams as $idx => $param)
                    @php
                        [$pname, $punit, $prange, $ptype] = $param;
                        $ex = $existingParams[$pname] ?? [];
                        $exValue   = $ex['value']   ?? '';
                        $exStatus  = $ex['status']  ?? '';
                        $exRemarks = $ex['remarks'] ?? '';
                    @endphp

                    @if(isset($sections[$idx]))
                        <tr class="table-secondary">
                            <td colspan="5" class="fw-semibold small text-uppercase py-1">
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
                            @if($ptype === 'select_neg')
                                <select class="form-select form-select-sm"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="saUpdateStatus(this, {{ $idx }})">
                                    <option value="">— Select —</option>
                                    @foreach(['Negative','Trace','1+','2+','3+','4+','Positive'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select form-select-sm"
                                        name="parameters[{{ $idx }}][value]"
                                        onchange="saUpdateStatusNone(this, {{ $idx }})">
                                    <option value="">— Select —</option>
                                    @foreach(['None','Rare','Few','Moderate','Many'] as $opt)
                                        <option value="{{ $opt }}" {{ $exValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;" tabindex="-1"
                                   name="parameters[{{ $idx }}][normal_range]" value="{{ $prange }}" readonly>
                        </td>
                        <td>
                            <select class="form-select form-select-sm"
                                    id="sa-status-{{ $idx }}"
                                    style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;appearance:none;" tabindex="-1"
                                    name="parameters[{{ $idx }}][status]">
                                <option value="normal"   {{ ($exStatus ?: 'normal') === 'normal'   ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ $exStatus === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
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
function saUpdateStatus(select, idx) {
    const sel = document.getElementById('sa-status-' + idx);
    if (!sel) return;
    const val = select.value;
    if (!val) { sel.value = 'normal'; return; }
    sel.value = (val === 'Negative') ? 'normal' : 'abnormal';
}
function saUpdateStatusNone(select, idx) {
    const sel = document.getElementById('sa-status-' + idx);
    if (!sel) return;
    const val = select.value;
    if (!val) { sel.value = 'normal'; return; }
    sel.value = (val === 'None') ? 'normal' : 'abnormal';
}
</script>
