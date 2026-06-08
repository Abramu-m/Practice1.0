@php
    use App\Models\Patient;
    use App\Models\Prescription;
    $testPatients = Patient::with(['allergies'])
        ->whereIn('card_number', ['CDS-TEST-001', 'CDS-TEST-002', 'CDS-TEST-003'])
        ->orderBy('card_number')
        ->get();
    // Load active prescription medication names for each test patient
    $testPatientRx = [];
    foreach ($testPatients as $tp) {
        $testPatientRx[$tp->id] = Prescription::with('medication')
            ->where('patient_id', $tp->id)
            ->whereIn('status', ['prescribed', 'prepared'])
            ->get()
            ->pluck('medication.generic_name')
            ->filter()
            ->values();
    }
@endphp

<div class="alert alert-info mb-3">
    <strong><i class="fas fa-info-circle me-1"></i> Rule Testing Interface</strong><br>
    <strong>Rule:</strong> {{ $rule->name }} &nbsp;|&nbsp;
    <strong>Type:</strong> {{ $rule->ruleType->display_name }} &nbsp;|&nbsp;
    <strong>Priority:</strong> {{ $rule->priority }} &nbsp;|&nbsp;
    <strong>Severity:</strong> <span class="badge bg-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }}">{{ ucfirst($rule->severity) }}</span>
</div>

<form id="testRuleForm">
    @csrf

    {{-- ── Test Patient Picker ── --}}
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white py-2">
            <i class="fas fa-user-check me-1"></i> Use Test Patient <small class="ms-2 opacity-75">(optional — fills patient context automatically)</small>
        </div>
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                @foreach($testPatients as $tp)
                @php
                    $tpAge = \Carbon\Carbon::parse($tp->date_of_birth)->age;
                    $tpAllergyNames = $tp->allergies->pluck('substance_name')->join(', ');
                    $tpRxNames = $testPatientRx[$tp->id] ?? collect();
                @endphp
                <div class="col-md-4">
                    <button type="button"
                            class="btn btn-outline-primary btn-sm w-100 test-patient-btn"
                            data-patient-id="{{ $tp->id }}"
                            data-patient-name="{{ $tp->full_name }}"
                            data-patient-age="{{ $tpAge }}"
                            data-patient-gender="{{ $tp->gender }}"
                            data-card="{{ $tp->card_number }}">
                        <div class="fw-bold">{{ $tp->full_name }}</div>
                        <small class="d-block text-muted">{{ $tpAge }}y · {{ ucfirst($tp->gender) }} · {{ $tp->card_number }}</small>
                        @if($tpAllergyNames)
                        <small class="d-block text-danger"><i class="fas fa-allergies"></i> {{ Str::limit($tpAllergyNames, 40) }}</small>
                        @endif
                        @if($tpRxNames->isNotEmpty())
                        <small class="d-block text-success"><i class="fas fa-prescription-bottle-alt"></i> Rx: {{ Str::limit($tpRxNames->join(', '), 45) }}</small>
                        @endif
                    </button>
                </div>
                @endforeach
                @if($testPatients->isEmpty())
                <div class="col-12 text-muted small">No CDS test patients found. <a href="{{ route('admin.cds.test-patients.index') }}">Create them here</a>.</div>
                @endif
            </div>
            <input type="hidden" name="patient_id" id="testPatientId" value="">
            <div id="selectedPatientBadge" class="mt-2" style="display:none">
                <span class="badge bg-primary"><i class="fas fa-user me-1"></i><span id="selectedPatientName"></span></span>
                <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" onclick="clearTestPatient()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ── Medication ── --}}
        <div class="col-md-6">
            <h6 class="text-secondary"><i class="fas fa-pills me-1"></i> Medication to Prescribe</h6>
            <div class="mb-2">
                <label class="form-label form-label-sm">Medication <span class="text-danger">*</span></label>
                <select id="testMedSelect" name="medication_id" class="form-select form-select-sm" style="width:100%">
                    <option value="">Search medication…</option>
                </select>
                <input type="hidden" name="medication_name" id="testMedName">
            </div>
            <div class="row g-2">
                <div class="col-4">
                    <label class="form-label form-label-sm">Dose (mg)</label>
                    <input type="number" class="form-control form-control-sm" name="dose_amount" id="testDoseAmount" value="500" min="0" step="0.01">
                </div>
                <div class="col-4">
                    <label class="form-label form-label-sm">Frequency <small class="text-muted">/day</small></label>
                    <input type="number" class="form-control form-control-sm" name="dose_frequency" id="testDoseFrequency" value="3" min="1" max="24">
                </div>
                <div class="col-4">
                    <label class="form-label form-label-sm">Duration <small class="text-muted">days</small></label>
                    <input type="number" class="form-control form-control-sm" name="dose_duration" id="testDoseDuration" value="7" min="1">
                </div>
            </div>
        </div>

        {{-- ── Patient Context Overrides ── --}}
        <div class="col-md-6">
            <h6 class="text-secondary"><i class="fas fa-user me-1"></i> Patient Context</h6>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label form-label-sm">Age (override)</label>
                    <input type="number" class="form-control form-control-sm" name="patient_age_override" id="testPatientAge" value="35" min="0" max="150">
                </div>
                <div class="col-6">
                    <label class="form-label form-label-sm">Weight kg (override)</label>
                    <input type="number" class="form-control form-control-sm" name="patient_weight_override" id="testPatientWeight" value="70" min="0" max="500" step="0.1">
                </div>
            </div>
            <div id="patientAllergyPanel" class="mt-2" style="display:none">
                <small class="text-muted d-block mb-1">Recorded allergies for this patient:</small>
                <div id="patientAllergyList" class="d-flex flex-wrap gap-1"></div>
            </div>
        </div>
    </div>

    {{-- ── Rule Conditions ── --}}
    @if($rule->conditions->count() > 0)
    <div class="mt-3">
        <h6 class="text-secondary"><i class="fas fa-filter me-1"></i> Rule Conditions</h6>
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light"><tr><th>Field</th><th>Operator</th><th>Value</th></tr></thead>
            <tbody>
                @foreach($rule->conditions as $condition)
                <tr>
                    <td><code>{{ $condition->field_name ?? $condition->field ?? '—' }}</code></td>
                    <td>{{ ucwords(str_replace('_', ' ', $condition->operator)) }}</td>
                    <td><strong>{{ $condition->value }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="mt-3 d-flex gap-2">
        <button type="button" class="btn btn-primary" onclick="runTest()">
            <i class="fas fa-play me-1"></i> Run Test
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="resetTestForm()">
            <i class="fas fa-redo me-1"></i> Reset
        </button>
    </div>
</form>

<div id="testResults" class="mt-3" style="display:none"></div>

<script>
(function () {
    // ── Select2 medication search ──────────────────────────────────────────
    $('#testMedSelect').select2({
        dropdownParent: $('#testRuleModal'),
        placeholder: 'Search medication…',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '/medications/search',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term, page: params.page || 1 }),
            processResults: data => ({
                results: data.results ?? [],
                pagination: { more: data.more ?? false },
            }),
        },
    }).on('select2:select', function (e) {
        // Extract the generic name (text before the first ' (' or ' - ')
        const raw = e.params.data.text || '';
        const generic = raw.split(/\s*[\(\-]/)[0].trim();
        document.getElementById('testMedName').value = generic;
    }).on('select2:clear', function () {
        document.getElementById('testMedName').value = '';
    });

    // ── Test patient buttons ───────────────────────────────────────────────
    document.querySelectorAll('.test-patient-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.test-patient-btn').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');

            document.getElementById('testPatientId').value = this.dataset.patientId;
            document.getElementById('selectedPatientName').textContent = this.dataset.patientName;
            document.getElementById('selectedPatientBadge').style.display = '';

            if (this.dataset.patientAge) {
                document.getElementById('testPatientAge').value = this.dataset.patientAge;
            }

            document.getElementById('patientAllergyPanel').style.display = '';
            document.getElementById('patientAllergyList').innerHTML =
                '<span class="text-muted small fst-italic">Run the test to see allergy details.</span>';
        });
    });

    window.clearTestPatient = function () {
        document.getElementById('testPatientId').value = '';
        document.getElementById('selectedPatientBadge').style.display = 'none';
        document.getElementById('patientAllergyPanel').style.display = 'none';
        document.querySelectorAll('.test-patient-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-primary');
        });
    };

    // ── runTest ────────────────────────────────────────────────────────────
    window.runTest = function () {
        const form = document.getElementById('testRuleForm');
        const data = new FormData(form);
        const obj  = Object.fromEntries(data.entries());

        if (!obj.medication_id) {
            showTestMsg('danger', 'Please select a medication before running the test.');
            return;
        }

        const btn = form.querySelector('button[onclick="runTest()"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Running…';

        fetch('/admin/cds/rules/{{ $rule->id }}/run-test', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(obj),
        })
        .then(r => r.json().then(d => ({ ok: r.ok, d })))
        .then(({ ok, d }) => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play me-1"></i> Run Test';
            if (!ok || !d.success) {
                showTestMsg('danger', 'Error: ' + (d.error || d.message || 'Unknown error'));
                return;
            }
            renderResults(d);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play me-1"></i> Run Test';
            showTestMsg('danger', 'Network error: ' + err.message);
        });
    };

    function renderResults(res) {
        const ctx   = res.context_summary || {};
        const rule  = res.rule_result;
        const fired = res.alert_fired;
        const conds = res.matches_conditions;

        // Update allergy panel
        if (ctx.allergy_count > 0) {
            document.getElementById('patientAllergyList').innerHTML =
                `<span class="badge bg-danger">${ctx.allergy_count} allerg${ctx.allergy_count === 1 ? 'y' : 'ies'} on record</span>`;
            document.getElementById('patientAllergyPanel').style.display = '';
        }

        const severityClass = fired
            ? (rule?.severity === 'critical' ? 'danger' : rule?.severity === 'high' ? 'warning' : 'warning')
            : 'success';

        const matchLabels = {
            exact_medication_match:      '<i class="fas fa-crosshairs me-1"></i>Exact FK match',
            drug_class_cross_reactivity: '<i class="fas fa-dna me-1"></i>Drug-class cross-reactivity',
            name_match:                  '<i class="fas fa-font me-1"></i>Name substring match',
        };

        let html = `
        <div class="card border-${severityClass}">
            <div class="card-header bg-${severityClass} text-white py-2 d-flex justify-content-between align-items-center">
                <strong>${fired
                    ? `<i class="fas fa-exclamation-triangle me-1"></i> Alert FIRED — ${esc(rule?.severity?.toUpperCase() ?? '')}`
                    : '<i class="fas fa-check-circle me-1"></i> No Alert — Rule did not trigger'}</strong>
                <span class="small">Conditions matched: <span class="badge bg-white text-dark">${conds ? 'Yes' : 'No'}</span></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <h6 class="text-muted">Context Used</h6>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted pe-3">Patient ID</td><td>${ctx.patient_id || '<em>anonymous</em>'}</td></tr>
                            <tr><td class="text-muted">Age</td><td>${ctx.patient_age ?? '—'} yrs</td></tr>
                            <tr><td class="text-muted">Weight</td><td>${ctx.patient_weight ?? '—'} kg</td></tr>
                            ${ctx.patient_creatinine ? `<tr><td class="text-muted">Creatinine</td><td>${esc(ctx.patient_creatinine)}</td></tr>` : ''}
                            ${ctx.patient_egfr != null ? `<tr><td class="text-muted">eGFR (C-G)</td><td><strong class="${ctx.patient_egfr < 30 ? 'text-danger' : ctx.patient_egfr < 60 ? 'text-warning' : 'text-success'}">${ctx.patient_egfr} mL/min</strong></td></tr>` : ''}
                            <tr><td class="text-muted">Medication</td><td>${esc(ctx.medication_name || '—')} <span class="text-muted">#${ctx.medication_id || '—'}</span></td></tr>
                            <tr><td class="text-muted">Dosage</td><td>${esc(ctx.dosage || '—')}</td></tr>
                            ${ctx.dose_frequency ? `<tr><td class="text-muted">Frequency</td><td>${ctx.dose_frequency}× / day</td></tr>` : ''}
                            ${ctx.dose_duration  ? `<tr><td class="text-muted">Duration</td><td>${ctx.dose_duration} days</td></tr>` : ''}
                            <tr><td class="text-muted">Allergies</td><td>${ctx.allergy_count ?? 0} on record</td></tr>
                        </table>
                    </div>`;

        if (fired && rule) {
            html += `
                    <div class="col-md-7">
                        <h6 class="text-muted">Alert Details</h6>
                        <p class="mb-2 fw-semibold text-${severityClass}">${esc(rule.message || '')}</p>
                        <p class="mb-2 small text-muted">${esc(rule.rationale || '')}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-${severityClass}">${esc(rule.severity || '')}</span>
                            <span class="badge bg-secondary">${esc(rule.rule_key || '')}</span>
                            <span class="badge bg-light text-dark">v${esc(rule.rule_version || '?')}</span>
                            ${rule.payload?.match_type
                                ? `<span class="badge bg-warning text-dark">${matchLabels[rule.payload.match_type] ?? esc(rule.payload.match_type)}</span>`
                                : ''}
                        </div>
                    </div>`;
        } else {
            html += `
                    <div class="col-md-7 d-flex align-items-center">
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x d-block mb-1"></i>
                            No conflict detected for <strong>${esc(ctx.medication_name || 'this medication')}</strong>
                            ${ctx.patient_id ? 'with the selected patient.' : '(anonymous context).'}
                        </div>
                    </div>`;
        }

        html += `</div></div></div>`;

        const el = document.getElementById('testResults');
        el.innerHTML = html;
        el.style.display = '';
    }

    function showTestMsg(type, msg) {
        const el = document.getElementById('testResults');
        el.innerHTML = `<div class="alert alert-${type} mb-0">${esc(msg)}</div>`;
        el.style.display = '';
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    window.resetTestForm = function () {
        document.getElementById('testRuleForm').reset();
        const el = document.getElementById('testResults');
        el.innerHTML = '';
        el.style.display = 'none';
        clearTestPatient();
        $('#testMedSelect').val(null).trigger('change');
        document.getElementById('testMedName').value = '';
    };
})();
</script>
