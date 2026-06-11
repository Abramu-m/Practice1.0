@extends('layouts.app_main_layout')

@section('page_title', 'Lab Result - ' . $result->investigation->medicalService->name)

@section('styles')
<style>
    @media print {
        .app-header,
        .app-sidebar,
        .app-footer,
        .no-print { display: none !important; }

        .app-wrapper, .app-main, .app-content, .container-fluid {
            margin: 0 !important; padding: 0 !important;
            width: 100% !important; background: #fff !important;
        }

        @page { margin: 10mm 12mm; }
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">

    <!-- Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('lab.results.form', $result->investigation->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Results Form
        </a>
        <span class="badge fs-6 bg-{{ $result->form_status === 'final' ? 'success' : ($result->form_status === 'preliminary' ? 'info' : 'warning') }}">
            {{ ucfirst($result->form_status) }} Report
        </span>
    </div>

    <!-- Investigation Header -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3" style="background: linear-gradient(135deg, #e8f4fd, #f0f8ff); border-left: 4px solid #0d6efd; border-radius: 4px;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="fas fa-vial text-primary me-2"></i>
                        {{ $result->investigation->medicalService->name }}
                        @if($result->investigation->medicalService->code)
                            <span class="badge bg-secondary ms-2 fw-normal" style="font-size:0.7rem;">{{ $result->investigation->medicalService->code }}</span>
                        @endif
                    </h5>
                    <div class="text-muted small">
                        Patient: <strong class="text-dark">{{ $result->investigation->patient->first_name }} {{ $result->investigation->patient->last_name }}</strong>
                        &nbsp;&bull;&nbsp; Investigation #{{ $result->investigation->id }}
                        &nbsp;&bull;&nbsp;
                        <span class="badge bg-{{ $result->investigation->priority === 'stat' ? 'danger' : ($result->investigation->priority === 'urgent' ? 'warning text-dark' : 'secondary') }}">
                            {{ strtoupper($result->investigation->priority) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end text-muted small mt-2 mt-md-0">
                    <div><i class="fas fa-calendar-alt me-1"></i> Ordered: {{ $result->investigation->ordered_at ? $result->investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}</div>
                    <div><i class="fas fa-user-md me-1"></i>
                        @if($result->investigation->doctor && $result->investigation->doctor->user)
                            Dr. {{ $result->investigation->doctor->user->first_name }} {{ $result->investigation->doctor->user->last_name }}
                        @else
                            <span class="text-muted">Not specified</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Data -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 text-dark">
                <i class="fas fa-chart-line text-success me-2"></i>
                {{ $result->investigation->medicalService->name }} Results
            </h6>
        </div>
        <div class="card-body p-0">
            @php
                $templateCode = $result->metadata['template_code'] ?? $result->template_name ?? '';
                $isSimpleTemplate = in_array($templateCode, ['simple', 'simple_lab', 'single_numeric_lab', 'qualitative_lab', 'mrdt_malaria', 'urinalysis', 'full_blood_picture', 'blood_count', 'genxpert_tb', 'zn_stain_tb', 'blood_grouping', 'pbs_microfilaria', 'pbs_malaria', 'pbs_rbc_morphology', 'psa_semiquantitative', 'gram_stain']) && isset($result->form_data['parameters']);
                $isQualitative = in_array($templateCode, ['qualitative_lab', 'mrdt_malaria']) && isset($result->form_data['parameters']);
                $isNarrative = $templateCode === 'narrative_lab';
                $isCd4 = $templateCode === 'cd4';
                $isGeneral = $templateCode === 'general';
                $isVitalObservations = $templateCode === 'vital_observations';
            @endphp
            @if($isNarrative)
                {{-- Narrative / free-text result --}}
                @php
                    $narrativeValue = null;
                    if (isset($result->form_data['parameters'])) {
                        $params = $result->form_data['parameters'];
                        if (is_string($params)) $params = json_decode($params, true);
                        $narrativeValue = $params[0]['value'] ?? null;
                    }
                @endphp
                <div class="p-4">
                    <p class="text-muted small mb-1">{{ $result->investigation->medicalService->name }}</p>
                    <div class="border rounded p-3 bg-light" style="white-space:pre-wrap;font-size:0.95rem;">{{ $narrativeValue ?? '—' }}</div>
                </div>
                <div class="px-4 py-3 border-top bg-light d-flex flex-wrap gap-4">
                    @if(isset($result->form_data['analyzed_by']) && $result->form_data['analyzed_by'])
                        <div><span class="text-muted small">Analyzed By</span><br><span class="fw-semibold">{{ $result->form_data['analyzed_by'] }}</span></div>
                    @endif
                    @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                        <div><span class="text-muted small">Analysis Date</span><br><span class="fw-semibold">{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('M d, Y H:i') }}</span></div>
                    @endif
                    @if($result->reportedBy)
                        <div><span class="text-muted small">Reported By</span><br><span class="fw-semibold">{{ $result->reportedBy->name }}</span></div>
                    @endif
                </div>
                @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                    <div class="px-4 py-3 border-top">
                        <p class="text-muted small mb-1">Additional Comments</p>
                        <p class="mb-0">{{ $result->form_data['additional_comments'] }}</p>
                    </div>
                @endif
            @elseif($isSimpleTemplate)
                {{-- Simple / numeric lab results --}}
                @php
                    $parameters = $result->form_data['parameters'];
                    if (is_string($parameters)) $parameters = json_decode($parameters, true);
                    if (!is_array($parameters)) $parameters = [$parameters];

                    $toFloat = function ($val) {
                        if ($val === null || $val === '') return null;
                        if (is_numeric($val)) return (float)$val;
                        if (preg_match('/-?\d+(?:[\.,]\d+)?/', (string)$val, $m)) return (float) str_replace(',', '.', $m[0]);
                        return null;
                    };
                    $computeStatus = function ($valueRaw, $rangeRaw) use ($toFloat) {
                        if (is_array($valueRaw) || is_array($rangeRaw)) return null;
                        $val = $toFloat($valueRaw);
                        if ($val === null || !$rangeRaw) return null;
                        $r = trim(str_replace(["–","—","−"], "-", (string)$rangeRaw));
                        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*(?:-|to)\s*(-?\d+(?:\.\d+)?)\s*$/i', $r, $mm)) {
                            if ($val < (float)$mm[1]) return 'low';
                            if ($val > (float)$mm[2]) return 'high';
                            return 'normal';
                        }
                        if (preg_match('/^\s*([<>]=?)\s*(-?\d+(?:\.\d+)?)\s*$/', $r, $mm)) {
                            $op = $mm[1]; $cut = (float)$mm[2];
                            if ($op === '<')  return $val <  $cut ? 'normal' : 'high';
                            if ($op === '<=') return $val <= $cut ? 'normal' : 'high';
                            if ($op === '>')  return $val >  $cut ? 'normal' : 'low';
                            if ($op === '>=') return $val >= $cut ? 'normal' : 'low';
                        }
                        return null;
                    };
                @endphp
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Parameter</th>
                                <th>Value</th>
                                <th>Unit</th>
                                <th>Normal Range</th>
                                <th>Status</th>
                                <th class="pe-4">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parameters as $param)
                                @php
                                    if (is_string($param)) $param = json_decode($param, true);
                                    if (!is_array($param)) continue;
                                    $pname  = $param['parameter_name'] ?? ($param['parameter'] ?? 'N/A');
                                    $pvalue = is_array($param['value'] ?? null) ? null : ($param['value'] ?? null);
                                    $punit  = is_array($param['unit'] ?? null) ? '' : ($param['unit'] ?? '');
                                    $prange = is_array($param['normal_range'] ?? null) ? '' : ($param['normal_range'] ?? '');
                                    $status = $param['status'] ?? ($computeStatus($pvalue, $prange) ?? 'unknown');
                                    $badgeClass = match($status) {
                                        'high'     => 'bg-danger',
                                        'low'      => 'bg-warning text-dark',
                                        'normal'   => 'bg-success',
                                        'critical' => 'bg-danger',
                                        default    => 'bg-secondary'
                                    };
                                    $rowClass = match($status) {
                                        'high', 'critical' => 'table-danger',
                                        'low'              => 'table-warning',
                                        default            => '',
                                    };
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="ps-4 fw-semibold">{{ $pname }}</td>
                                    <td class="fw-bold">{{ $pvalue ?? '—' }}</td>
                                    <td class="text-muted small">{{ $punit }}</td>
                                    <td class="text-muted small">{{ $prange }}</td>
                                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
                                    <td class="pe-4 text-muted small">{{ is_array($param['remarks'] ?? '') ? '' : ($param['remarks'] ?? '') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Analysis footer -->
                <div class="px-4 py-3 border-top bg-light d-flex flex-wrap gap-4">
                    @if(isset($result->form_data['analyzed_by']) && $result->form_data['analyzed_by'])
                        <div>
                            <span class="text-muted small">Analyzed By</span><br>
                            <span class="fw-semibold">{{ $result->form_data['analyzed_by'] }}</span>
                        </div>
                    @endif
                    @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                        <div>
                            <span class="text-muted small">Analysis Date</span><br>
                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('M d, Y H:i') }}</span>
                        </div>
                    @endif
                    @if($result->reportedBy)
                        <div>
                            <span class="text-muted small">Reported By</span><br>
                            <span class="fw-semibold">{{ $result->reportedBy->name }}</span>
                        </div>
                    @endif
                    @if($result->reported_at)
                        <div>
                            <span class="text-muted small">Reported At</span><br>
                            <span class="fw-semibold">{{ $result->reported_at->format('M d, Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                    <div class="px-4 py-3 border-top">
                        <p class="text-muted small mb-1">Additional Comments</p>
                        <p class="mb-0">{{ $result->form_data['additional_comments'] }}</p>
                    </div>
                @endif

            @elseif(in_array($templateCode, ['genxpert_tb', 'zn_stain_tb']))
                {{-- GeneXpert TB / ZN Stain — load official MoH form read-only with saved data --}}
                <div id="gx-tb-form-view">
                    <div id="gx-tb-loading" class="p-4 text-center text-muted">
                        <i class="fas fa-spinner fa-spin me-2"></i> Loading form&hellip;
                    </div>
                    <div id="gx-tb-tpl-container"></div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var investigationId = {{ $result->investigation->id }};
                    var savedData       = @json($result->form_data);
                    var container       = document.getElementById('gx-tb-tpl-container');
                    var loading         = document.getElementById('gx-tb-loading');

                    fetch('/api/result-template/{{ $templateCode }}?investigation_id=' + investigationId, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        loading.style.display = 'none';
                        container.innerHTML = html;

                        // Execute template inline scripts (registers event listeners + fill logic)
                        container.querySelectorAll('script').forEach(function (oldScript) {
                            var s = document.createElement('script');
                            s.textContent = oldScript.textContent;
                            document.head.appendChild(s);
                            oldScript.remove();
                        });

                        // Fill saved form_data into template fields
                        Object.entries(savedData).forEach(function ([key, val]) {
                            if (val === null || val === undefined || val === '') return;
                            var strVal = String(val);

                            // Text / date / time inputs and selects
                            var el = container.querySelector('[name="' + key + '"]:not([type="radio"]):not([type="checkbox"])');
                            if (el) el.value = strVal;

                            // Radios — iterate to match value without CSS escaping issues
                            container.querySelectorAll('input[type="radio"][name="' + key + '"]').forEach(function (radio) {
                                if (radio.value === strVal) {
                                    radio.checked = true;
                                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            });

                            // Checkboxes
                            var vals = Array.isArray(val) ? val.map(String) : [strVal];
                            container.querySelectorAll('input[type="checkbox"][name="' + key + '"]').forEach(function (cb) {
                                if (vals.includes(cb.value)) cb.checked = true;
                            });

                            // auto-val / pre-filled spans
                            container.querySelectorAll('[data-field="' + key + '"]').forEach(function (span) {
                                span.textContent = strVal;
                            });
                        });

                        // Show all result sections regardless of ordering data
                        ['section-microscopy', 'section-xpert', 'section-lflam', 'section-skin'].forEach(function (id) {
                            var el = document.getElementById(id);
                            if (el) el.style.display = '';
                        });

                        // Show request section at full opacity in view mode
                        var reqSection = container.querySelector('.tb-request-section');
                        if (reqSection) {
                            reqSection.style.opacity   = '1';
                            reqSection.style.pointerEvents = 'none';
                        }

                        // Make all form controls read-only (disabled preserves value in print)
                        container.querySelectorAll('input, select, textarea').forEach(function (el) {
                            el.disabled = true;
                        });
                    })
                    .catch(function () {
                        loading.innerHTML = '<div class="alert alert-warning m-3">Could not load form template.</div>';
                    });
                });
                </script>

            @elseif($templateCode === 'tb')
                {{-- TB results display --}}
                <div class="row">
                    @if(isset($result->form_data['microscopy_result']))
                    <div class="col-md-6">
                        <h6>Microscopy Results</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Result:</strong></td>
                                <td>{{ $result->form_data['microscopy_result'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($result->form_data['microscopy_grade']))
                            <tr>
                                <td><strong>Grade:</strong></td>
                                <td>{{ $result->form_data['microscopy_grade'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                            @if(isset($result->form_data['examined_by']))
                            <tr>
                                <td><strong>Examined by:</strong></td>
                                <td>{{ $result->form_data['examined_by'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    @endif

                    @if(isset($result->form_data['xpert_result']))
                    <div class="col-md-6">
                        <h6>Xpert MTB/RIF Results</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>MTB Result:</strong></td>
                                <td>{{ $result->form_data['xpert_result'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($result->form_data['rif_resistance']))
                            <tr>
                                <td><strong>RIF Resistance:</strong></td>
                                <td>{{ $result->form_data['rif_resistance'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    @endif
                </div>

                @if(isset($result->form_data['clinical_notes']) && $result->form_data['clinical_notes'])
                    <div class="mt-3">
                        <h6>Clinical Notes:</h6>
                        <div class="alert alert-light">
                            {{ $result->form_data['clinical_notes'] }}
                        </div>
                    </div>
                @endif

            @elseif($isCd4)
                <div class="p-3">
                    @include('lab.result_templates.cd4', [
                        'existingData'  => $result->form_data,
                        'investigation' => $result->investigation,
                        'visit'         => $result->investigation->visit,
                        'isReadOnly'    => true,
                    ])
                </div>
            @elseif($isGeneral)
                <div class="p-3">
                    @include('lab.result_templates.general', [
                        'existingData'  => $result->form_data,
                        'investigation' => $result->investigation,
                        'isReadOnly'    => true,
                    ])
                </div>
            @elseif($isVitalObservations)
                <div class="p-3">
                    @include('lab.result_templates.vital_observations', [
                        'existingData'  => $result->form_data,
                        'investigation' => $result->investigation,
                        'isReadOnly'    => true,
                    ])
                </div>
            @else
                {{-- Generic result display --}}
                <div class="p-4">
                    <div class="row">
                        @foreach($result->form_data as $key => $value)
                            @if(!in_array($key, ['_token', 'template_', 'action']) && !empty($value))
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                @if(is_array($value))
                                    @foreach($value as $subKey => $subValue)
                                        <div class="small">
                                            <em>{{ ucwords(str_replace('_', ' ', $subKey)) }}:</em>
                                            {{ is_array($subValue) ? json_encode($subValue) : $subValue }}
                                        </div>
                                    @endforeach
                                @else
                                    <span class="fw-semibold">{{ $value }}</span>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mt-3 no-print">
        <div class="d-flex gap-2">
            <a href="{{ route('lab.results.form', $result->investigation->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit"></i> Edit Results
            </a>
            @if($result->investigation->consultation && $result->investigation->consultation->visit)
                <a href="{{ route('lab.visits.investigations', $result->investigation->consultation->visit->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list"></i> All Investigations
                </a>
            @else
                <a href="{{ route('lab.visits.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list"></i> Lab Dashboard
                </a>
            @endif
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-dark" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            @if($result->form_status !== 'final')
                <button class="btn btn-sm btn-warning" onclick="promoteToFinal()">
                    <i class="fas fa-check"></i> Mark as Final
                </button>
            @endif
        </div>
    </div>

</div>

<script>
function promoteToFinal() {
    if (confirm('Mark this result as final? This cannot be undone.')) {
        alert('Promote to final — Result ID: {{ $result->id }}');
    }
}
</script>

@endsection
