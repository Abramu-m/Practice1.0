@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3">🧪 CDS Test Patients</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.cds.dashboard') }}">CDS Dashboard</a></li>
                            <li class="breadcrumb-item active">Test Patients</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button class="btn btn-outline-warning btn-sm" onclick="reseedPatients()">
                        <i class="fas fa-redo"></i> Reset All to Defaults
                    </button>
                    <a href="{{ route('admin.cds.rules.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Rules
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert area --}}
    <div id="pageAlerts"></div>

    {{-- Info banner --}}
    <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
        <i class="fas fa-info-circle fa-lg mt-1"></i>
        <div>
            <strong>How to use:</strong> Select one of these patients when testing a CDS rule.
            Each patient is pre-configured with known allergies, lab results and co-morbidities so you can
            intentionally trigger alerts (e.g. prescribe Penicillin to Amina, or an NSAID to Margaret).
            Use the <strong>Edit</strong> button to tweak any patient before running a test.
        </div>
    </div>

    {{-- Patient Cards --}}
    <div class="row g-4">
        @foreach($patients as $patient)
        @php
            $age = \Carbon\Carbon::parse($patient->date_of_birth)->age;
            $pmh = $patient->pastMedicalHistory;
            $vitals = $patient->vitals;
            $colors = ['CDS-TEST-001' => 'primary', 'CDS-TEST-002' => 'success', 'CDS-TEST-003' => 'danger'];
            $color  = $colors[$patient->card_number] ?? 'secondary';
            $icons  = ['CDS-TEST-001' => 'fas fa-child', 'CDS-TEST-002' => 'fas fa-user', 'CDS-TEST-003' => 'fas fa-user-clock'];
            $icon   = $icons[$patient->card_number] ?? 'fas fa-user';
        @endphp
        <div class="col-md-4">
            <div class="card h-100 border-{{ $color }}">
                {{-- Card Header --}}
                <div class="card-header bg-{{ $color }} text-white d-flex justify-content-between align-items-center">
                    <div>
                        <i class="{{ $icon }} me-2"></i>
                        <strong>{{ $patient->full_name }}</strong>
                        <span class="badge bg-white text-{{ $color }} ms-2">{{ $age }} yrs</span>
                    </div>
                    <button class="btn btn-sm btn-light"
                            onclick="openEditModal({{ $patient->id }})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>

                {{-- Demographics --}}
                <div class="card-body pb-1">
                    <div class="row text-sm mb-2">
                        <div class="col-6">
                            <small class="text-muted">Gender</small><br>
                            <strong>{{ $patient->gender }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">DOB</small><br>
                            <strong>{{ \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') }}</strong>
                        </div>
                        <div class="col-6 mt-2">
                            <small class="text-muted">Card No.</small><br>
                            <code>{{ $patient->card_number }}</code>
                        </div>
                        <div class="col-6 mt-2">
                            <small class="text-muted">Occupation</small><br>
                            {{ $patient->occupation ?? '—' }}
                        </div>
                    </div>

                    {{-- Allergies --}}
                    <div class="mt-3">
                        <h6 class="fw-bold text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Allergies</h6>
                        @forelse($patient->allergies as $allergy)
                            <span class="badge bg-danger me-1 mb-1"
                                  title="{{ $allergy->reaction }}">
                                {{ $allergy->substance_name }}
                                <span class="badge bg-white text-danger ms-1" style="font-size:0.65em">{{ ucfirst($allergy->severity) }}</span>
                            </span>
                        @empty
                            <span class="text-muted small">None recorded</span>
                        @endforelse
                    </div>

                    {{-- Chronic Conditions --}}
                    @if($pmh && $pmh->chronic_conditions)
                    <div class="mt-3">
                        <h6 class="fw-bold text-warning"><i class="fas fa-heartbeat me-1"></i>Conditions</h6>
                        <p class="small mb-1">{{ $pmh->chronic_conditions }}</p>
                    </div>
                    @endif

                    {{-- Current Medications --}}
                    @if($pmh && $pmh->current_medications)
                    <div class="mt-3">
                        <h6 class="fw-bold text-info"><i class="fas fa-pills me-1"></i>Current Medications</h6>
                        <p class="small mb-1">{{ $pmh->current_medications }}</p>
                    </div>
                    @endif

                    {{-- Vitals --}}
                    @if($vitals)
                    <div class="mt-3">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-stethoscope me-1"></i>Vitals</h6>
                        <div class="row g-1 text-center">
                            <div class="col-4">
                                <div class="border rounded p-1">
                                    <div class="small text-muted">BP</div>
                                    <div class="fw-bold" style="font-size:0.85em">
                                        {{ $vitals->systolic_bp }}/{{ $vitals->diastolic_bp }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-1">
                                    <div class="small text-muted">Wt/Ht</div>
                                    <div class="fw-bold" style="font-size:0.85em">
                                        {{ $vitals->weight }}kg / {{ $vitals->height }}cm
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-1">
                                    <div class="small text-muted">SpO₂</div>
                                    <div class="fw-bold" style="font-size:0.85em">{{ $vitals->oxygen_saturation }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Lab Results --}}
                    @if($patient->investigations->count() > 0)
                    <div class="mt-3">
                        <h6 class="fw-bold text-purple"><i class="fas fa-flask me-1"></i>Lab / Investigations</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    @foreach($patient->investigations as $inv)
                                    @php $cd = $inv->clinical_data; @endphp
                                    @if(is_array($cd) && !empty($cd['test_name']))
                                    <tr>
                                        <td class="text-muted small pe-2" style="white-space:nowrap">{{ $cd['test_name'] }}</td>
                                        <td class="small fw-medium">{{ $cd['result'] }}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="card-footer bg-transparent text-end">
                    <button class="btn btn-outline-{{ $color }} btn-sm"
                            onclick="openEditModal({{ $patient->id }})">
                        <i class="fas fa-edit"></i> Edit Patient
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ========================================================== --}}
{{-- Edit Patient Modal (one modal, populated dynamically)       --}}
{{-- ========================================================== --}}
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPatientModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Test Patient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPatientForm">
                @csrf
                <input type="hidden" id="edit_patient_id">

                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="editTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-demo">Demographics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-vitals">Vitals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-allergies">
                                Allergies <span id="allergy_count_badge" class="badge bg-danger ms-1">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-history">Medical History</a>
                        </li>
                    </ul>

                    <div class="tab-content px-1">

                        {{-- DEMOGRAPHICS TAB --}}
                        <div class="tab-pane fade show active" id="tab-demo">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" id="edit_dob" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" id="edit_gender">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control" name="contact" id="edit_contact">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Residence</label>
                                    <input type="text" class="form-control" name="residence" id="edit_residence">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" class="form-control" name="occupation" id="edit_occupation">
                                </div>
                            </div>
                        </div>

                        {{-- VITALS TAB --}}
                        <div class="tab-pane fade" id="tab-vitals">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="weight" id="edit_weight">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control" name="height" id="edit_height">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Systolic BP (mmHg)</label>
                                    <input type="number" class="form-control" name="systolic_bp" id="edit_systolic_bp">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Diastolic BP (mmHg)</label>
                                    <input type="number" class="form-control" name="diastolic_bp" id="edit_diastolic_bp">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pulse Rate (bpm)</label>
                                    <input type="number" class="form-control" name="pulse_rate" id="edit_pulse_rate">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Temperature (°C)</label>
                                    <input type="number" step="0.1" class="form-control" name="temperature" id="edit_temperature">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Resp. Rate (/min)</label>
                                    <input type="number" class="form-control" name="respiratory_rate" id="edit_respiratory_rate">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">SpO₂ (%)</label>
                                    <input type="number" class="form-control" name="oxygen_saturation" id="edit_oxygen_saturation">
                                </div>
                            </div>
                        </div>

                        {{-- ALLERGIES TAB --}}
                        <div class="tab-pane fade" id="tab-allergies">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Known Allergies</h6>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="addAllergyRow()">
                                    <i class="fas fa-plus"></i> Add Allergy
                                </button>
                            </div>
                            <div id="allergyRows"></div>
                            <input type="hidden" name="allergies_json" id="allergies_json_input">
                        </div>

                        {{-- MEDICAL HISTORY TAB --}}
                        <div class="tab-pane fade" id="tab-history">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Allergies (text summary)</label>
                                    <textarea class="form-control" rows="2" name="allergies_text" id="edit_allergies_text"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Chronic Conditions</label>
                                    <textarea class="form-control" rows="3" name="chronic_conditions" id="edit_chronic_conditions"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Current Medications</label>
                                    <textarea class="form-control" rows="3" name="current_medications" id="edit_current_medications"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Previous Surgeries</label>
                                    <textarea class="form-control" rows="2" name="previous_surgeries" id="edit_previous_surgeries"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Family History</label>
                                    <textarea class="form-control" rows="2" name="family_history" id="edit_family_history"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Social History</label>
                                    <textarea class="form-control" rows="2" name="social_history" id="edit_social_history"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Smoking Status</label>
                                    <select class="form-select" name="smoking_status" id="edit_smoking_status">
                                        <option value="non_smoker">Non-smoker</option>
                                        <option value="former_smoker">Former smoker</option>
                                        <option value="current_smoker">Current smoker</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alcohol Use</label>
                                    <input type="text" class="form-control" name="alcohol_use" id="edit_alcohol_use"
                                           placeholder="e.g. none / occasional / heavy">
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// -----------------------------------------------------------------------
// Patient data passed from PHP (prepared in controller - Blade directive
// limitations prevent complex closures in json output directives)
// -----------------------------------------------------------------------
const TEST_PATIENTS = {!! json_encode($testPatientsJs) !!};
const MEDICATIONS   = {!! json_encode($medications->map(fn($m) => ['id' => $m->id, 'label' => $m->generic_name . ($m->brand_name ? ' / '.$m->brand_name : ''), 'generic_name' => $m->generic_name])->values()) !!};

// -----------------------------------------------------------------------
// Open the edit modal and populate with current data
// -----------------------------------------------------------------------
function openEditModal(patientId) {
    const p = TEST_PATIENTS[patientId];
    if (!p) { alert('Patient data not found.'); return; }

    document.getElementById('edit_patient_id').value = patientId;

    // Demographics
    setVal('edit_first_name',  p.first_name);
    setVal('edit_last_name',   p.last_name);
    setVal('edit_dob',         p.date_of_birth);
    setVal('edit_gender',      p.gender);
    setVal('edit_contact',     p.contact  ?? '');
    setVal('edit_residence',   p.residence ?? '');
    setVal('edit_occupation',  p.occupation ?? '');

    // Vitals
    setVal('edit_weight',            p.weight ?? '');
    setVal('edit_height',            p.height ?? '');
    setVal('edit_systolic_bp',       p.systolic_bp ?? '');
    setVal('edit_diastolic_bp',      p.diastolic_bp ?? '');
    setVal('edit_pulse_rate',        p.pulse_rate ?? '');
    setVal('edit_temperature',       p.temperature ?? '');
    setVal('edit_respiratory_rate',  p.respiratory_rate ?? '');
    setVal('edit_oxygen_saturation', p.oxygen_saturation ?? '');

    // PMH
    setVal('edit_allergies_text',    p.allergies_text ?? '');
    setVal('edit_chronic_conditions',p.chronic_conditions ?? '');
    setVal('edit_current_medications',p.current_medications ?? '');
    setVal('edit_previous_surgeries',p.previous_surgeries ?? '');
    setVal('edit_family_history',    p.family_history ?? '');
    setVal('edit_social_history',    p.social_history ?? '');
    setVal('edit_smoking_status',    p.smoking_status ?? 'non_smoker');
    setVal('edit_alcohol_use',       p.alcohol_use ?? '');

    // Allergies rows
    renderAllergyRows(p.allergies || []);

    // Activate first tab
    const firstTab = document.querySelector('#editTabs .nav-link.active');
    if (!firstTab) document.querySelector('#editTabs .nav-link')?.click();

    $('#editPatientModal').modal('show');
}

// -----------------------------------------------------------------------
// Allergy rows management
// -----------------------------------------------------------------------
let allergyData = [];

function renderAllergyRows(list) {
    allergyData = list.map(a => ({ ...a }));
    refreshAllergyUI();
}

function refreshAllergyUI() {
    const container = document.getElementById('allergyRows');
    const badge     = document.getElementById('allergy_count_badge');
    badge.textContent = allergyData.length;

    if (allergyData.length === 0) {
        container.innerHTML = '<p class="text-muted small">No allergies recorded. Click "+ Add Allergy" to add one.</p>';
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
    html += '<thead><tr><th style="min-width:220px">Drug</th><th>Reaction</th><th>Severity</th><th></th></tr></thead><tbody>';

    // Build reusable medication <option> list
    const medOptions = MEDICATIONS.map(m =>
        `<option value="${m.id}">${escHtml(m.label)}</option>`
    ).join('');

    allergyData.forEach((a, idx) => {
        const selectedMedId = a.medication_id || '';
        html += `<tr>
            <td>
                <select id="allergy-med-${idx}" class="form-select form-select-sm allergy-med-select" data-idx="${idx}">
                    <option value="">-- select drug --</option>
                    ${MEDICATIONS.map(m =>
                        `<option value="${m.id}" ${m.id == selectedMedId ? 'selected' : ''}>${escHtml(m.label)}</option>`
                    ).join('')}
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" value="${escHtml(a.reaction)}"
                       onchange="updateAllergy(${idx}, 'reaction', this.value)"></td>
            <td>
                <select class="form-select form-select-sm" onchange="updateAllergy(${idx}, 'severity', this.value)">
                    ${['mild','moderate','severe'].map(s =>
                        `<option value="${s}" ${a.severity===s?'selected':''}>${cap(s)}</option>`
                    ).join('')}
                </select>
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAllergy(${idx})">
                <i class="fas fa-trash"></i></button></td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html;

    // Initialise Select2 on each drug dropdown (searchable)
    allergyData.forEach((a, idx) => {
        $(`#allergy-med-${idx}`).select2({
            placeholder: '-- select drug --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editPatientModal')
        }).on('change', function () {
            updateAllergyMed(idx, $(this).val());
        });
    });
}

function addAllergyRow() {
    allergyData.push({ medication_id: null, substance_name: '', reaction: '', severity: 'moderate' });
    refreshAllergyUI();
}

function removeAllergy(idx) {
    allergyData.splice(idx, 1);
    refreshAllergyUI();
}

function updateAllergy(idx, field, value) {
    allergyData[idx][field] = value;
}

// Called when the drug dropdown changes: set medication_id and auto-resolve substance_name
function updateAllergyMed(idx, medId) {
    allergyData[idx].medication_id = medId ? parseInt(medId) : null;
    const med = MEDICATIONS.find(m => m.id == medId);
    allergyData[idx].substance_name = med ? med.generic_name : '';
}

// -----------------------------------------------------------------------
// Form submit
// -----------------------------------------------------------------------
document.getElementById('editPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const patientId = document.getElementById('edit_patient_id').value;

    // Sync allergy JSON
    document.getElementById('allergies_json_input').value = JSON.stringify(allergyData);

    const formData = new FormData(this);

    fetch(`/admin/cds/test-patients/${patientId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            showPageAlert('success', data.message || 'Patient updated.');
            $('#editPatientModal').modal('hide');
            setTimeout(() => location.reload(), 1200);
        } else {
            const errMsg = data.errors
                ? Object.values(data.errors).flat().join(' | ')
                : (data.message || 'Update failed.');
            showPageAlert('danger', errMsg);
        }
    })
    .catch(() => showPageAlert('danger', 'Network error – please try again.'));
});

// -----------------------------------------------------------------------
// Reseed
// -----------------------------------------------------------------------
function reseedPatients() {
    if (!confirm('Reset all three test patients back to their default values? Any manual edits will be lost.')) return;
    fetch('/admin/cds/test-patients/reseed', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        showPageAlert(data.success ? 'success' : 'danger', data.message || 'Done.');
        if (data.success) setTimeout(() => location.reload(), 1200);
    })
    .catch(() => showPageAlert('danger', 'Network error.'));
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function setVal(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    const strVal = String(val ?? '');
    el.value = strVal;
    // If direct assignment didn't stick (value not in options), try case-insensitive match
    if (el.tagName === 'SELECT' && el.value !== strVal) {
        const lower = strVal.toLowerCase();
        const opt = Array.from(el.options).find(o => o.value.toLowerCase() === lower);
        if (opt) el.value = opt.value;
    }
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function cap(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

function showPageAlert(type, message) {
    const div = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.innerHTML = `${escHtml(message)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.getElementById('pageAlerts').prepend(div);
    setTimeout(() => div.classList.remove('show'), 4000);
}
</script>
@endpush
@endsection
