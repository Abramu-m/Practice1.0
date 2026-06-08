@php
    $patientName = trim($visit->patientInfo->first_name . ' ' . ($visit->patientInfo->middle_name ?? '') . ' ' . $visit->patientInfo->last_name);
    $patientAge  = $visit->patientInfo->age ? $visit->patientInfo->age . ' years' : 'N/A';
    $patientGender = ucfirst($visit->patientInfo->gender ?? 'N/A');
    $mrn         = $visit->patientInfo->mr_number ?? $visit->patientInfo->id;
    $visitDate   = optional($visit->visit_date ?? $visit->created_at)->format('d M Y');
    $summaryAllergies = $drugAllergySummary ?: ($otherAllergiesSummary ?: 'N/A');
    $facilityName    = $facility->name ?? 'Medical Facility';
    $facilityAddress = $facility->address ?? '';
    $facilityPhone   = $facility->phone ?? '';
    $facilityEmail   = $facility->email ?? '';
@endphp

{{-- ─── Facility header ─── --}}
<div style="text-align:center; margin-bottom:14px; border-bottom:2px solid #dee2e6; padding-bottom:10px;">
    <div style="font-size:1.05rem; font-weight:700; color:#212529;">{{ $facilityName }}</div>
    @if($facilityAddress)
        <div style="font-size:0.82rem; color:#6c757d;">{{ $facilityAddress }}</div>
    @endif
    @if($facilityPhone || $facilityEmail)
        <div style="font-size:0.82rem; color:#6c757d;">
            @if($facilityPhone) Phone: {{ $facilityPhone }} @endif
            @if($facilityPhone && $facilityEmail) &nbsp;|&nbsp; @endif
            @if($facilityEmail) {{ $facilityEmail }} @endif
        </div>
    @endif
</div>

{{-- ─── Patient info banner ─── --}}
<table style="width:100%; border-collapse:collapse; border:1px solid #dee2e6; margin-bottom:16px; font-size:0.875rem;">
    <tr>
        <td style="padding:8px 12px; width:30%; border-right:1px solid #dee2e6; background:#f8f9fa;">
            <div style="font-size:0.68rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Patient Name</div>
            <div style="font-weight:600; color:#212529;">{{ $patientName }}</div>
        </td>
        <td style="padding:8px 12px; width:16%; border-right:1px solid #dee2e6; background:#f8f9fa;">
            <div style="font-size:0.68rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">MRN</div>
            <div style="font-weight:600; color:#212529;">{{ $mrn }}</div>
        </td>
        <td style="padding:8px 12px; width:16%; border-right:1px solid #dee2e6; background:#f8f9fa;">
            <div style="font-size:0.68rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Gender</div>
            <div style="font-weight:600; color:#212529;">{{ $patientGender }}</div>
        </td>
        <td style="padding:8px 12px; width:16%; border-right:1px solid #dee2e6; background:#f8f9fa;">
            <div style="font-size:0.68rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Age</div>
            <div style="font-weight:600; color:#212529;">{{ $patientAge }}</div>
        </td>
        <td style="padding:8px 12px; width:22%; background:#f8f9fa;">
            <div style="font-size:0.68rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Visit Date</div>
            <div style="font-weight:600; color:#212529;">{{ $visitDate }}</div>
        </td>
    </tr>
</table>

{{-- ─── Chief Complaints ─── --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#0d47a1;">Chief Complaints / History of Present Illness</span>
</div>
<div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px;">{!! nl2br(e($consultation->history_of_present_illness ?? 'N/A')) !!}</div>

{{-- ─── Past Medical History ─── --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#0d47a1;">Past Medical History</span>
</div>
<div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px;">{!! nl2br(e(optional($pastMedicalHistory)->history ?? optional($pastMedicalHistory)->summary ?? 'N/A')) !!}</div>

{{-- ─── Allergies ─── --}}
<div style="border-left:3px solid #dc3545; padding:3px 10px; background:#fff5f5; margin-bottom:6px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#a61c00;">Allergies</span>
</div>
<div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px;">{{ $summaryAllergies }}</div>

{{-- ─── Diagnosis ─── --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#0d47a1;">Diagnosis &amp; Plan</span>
</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:0.875rem;">
    <thead>
        <tr>
            <th style="background:#e8f0fe; border:1px solid #c5cae9; padding:6px 10px; width:33%; font-weight:600; font-size:0.8rem; color:#1a237e;">Provisional Diagnosis</th>
            <th style="background:#e8f5e9; border:1px solid #c8e6c9; padding:6px 10px; width:33%; font-weight:600; font-size:0.8rem; color:#1b5e20;">Final Diagnosis</th>
            <th style="background:#fff8e1; border:1px solid #ffe082; padding:6px 10px; width:34%; font-weight:600; font-size:0.8rem; color:#5d4037;">Plan / Remarks</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border:1px solid #dee2e6; padding:8px 10px; vertical-align:top;">{!! nl2br(e($consultation->provisional_diagnosis ?? 'N/A')) !!}</td>
            <td style="border:1px solid #dee2e6; padding:8px 10px; vertical-align:top;">{!! nl2br(e($consultation->final_diagnosis ?? 'N/A')) !!}</td>
            <td style="border:1px solid #dee2e6; padding:8px 10px; vertical-align:top;">{!! nl2br(e($consultation->remarks ?? ($consultation->followup_instructions ?? 'N/A'))) !!}</td>
        </tr>
    </tbody>
</table>

{{-- ─── Vital Signs ─── --}}
<div style="border-left:3px solid #198754; padding:3px 10px; background:#f0fff4; margin-bottom:6px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#14532d;">Vital Signs</span>
</div>
@if($vitals)
<table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:0.875rem;">
    <tr>
        <td style="padding:7px 10px; border:1px solid #dee2e6; width:33%; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">BP</span>
            <div style="font-weight:600;">{{ $vitals->systolic_bp ?? 'N/A' }}/{{ $vitals->diastolic_bp ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">mmHg</span></div>
        </td>
        <td style="padding:7px 10px; border:1px solid #dee2e6; width:33%; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Pulse</span>
            <div style="font-weight:600;">{{ $vitals->pulse_rate ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">bpm</span></div>
        </td>
        <td style="padding:7px 10px; border:1px solid #dee2e6; width:34%; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Temperature</span>
            <div style="font-weight:600;">{{ $vitals->temperature ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">°C</span></div>
        </td>
    </tr>
    <tr>
        <td style="padding:7px 10px; border:1px solid #dee2e6; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">RR</span>
            <div style="font-weight:600;">{{ $vitals->respiratory_rate ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">/min</span></div>
        </td>
        <td style="padding:7px 10px; border:1px solid #dee2e6; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">SpO₂</span>
            <div style="font-weight:600;">{{ $vitals->oxygen_saturation ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">%</span></div>
        </td>
        <td style="padding:7px 10px; border:1px solid #dee2e6; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Weight</span>
            <div style="font-weight:600;">{{ $vitals->weight ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">kg</span></div>
        </td>
    </tr>
    <tr>
        <td style="padding:7px 10px; border:1px solid #dee2e6; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Height</span>
            <div style="font-weight:600;">{{ $vitals->height ?? 'N/A' }} <span style="font-weight:400; color:#6c757d; font-size:0.8rem;">cm</span></div>
        </td>
        <td style="padding:7px 10px; border:1px solid #dee2e6; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">BMI</span>
            <div style="font-weight:600;">{{ $vitals->bmi ?? 'N/A' }}</div>
        </td>
        <td style="padding:7px 10px; border:1px solid #dee2e6; background:#fafafa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">MUAC / OFC</span>
            <div style="font-weight:600;">{{ trim(($vitals->muac ?? '') . ' / ' . ($vitals->ofc ?? '')) ?: 'N/A' }}</div>
        </td>
    </tr>
</table>
@else
<div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px; color:#6c757d;">N/A</div>
@endif

{{-- ─── Systemic Examination ─── --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#0d47a1;">Systemic Examination</span>
</div>
@if(isset($examinations) && $examinations->count() > 0)
    @foreach($examinations as $examination)
        @php
            $systems = collect([
                'General Findings'       => $examination->general_findings,
                'Cardiovascular System'  => $examination->cardiovascular_system,
                'Respiratory System'     => $examination->respiratory_system,
                'Gastrointestinal System'=> $examination->gastrointestinal_system,
                'Nervous System'         => $examination->nervous_system,
                'Musculoskeletal System' => $examination->musculoskeletal_system,
                'Genitourinary System'   => $examination->genitourinary_system,
                'Endocrine System'       => $examination->endocrine_system,
                'Skin Examination'       => $examination->skin_examination,
                'Psychiatric Assessment' => $examination->psychiatric_assessment,
            ])->filter(fn($value) => trim((string)$value) !== '');
        @endphp
        @if($systems->isNotEmpty())
        <table style="width:100%; border-collapse:collapse; margin-bottom:10px; font-size:0.875rem;">
            @foreach($systems as $label => $value)
            <tr>
                <td style="padding:5px 10px; border:1px solid #dee2e6; width:28%; background:#fafafa; font-weight:600; color:#495057; vertical-align:top;">{{ $label }}</td>
                <td style="padding:5px 10px; border:1px solid #dee2e6; vertical-align:top;">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    @endforeach
@else
    <div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px; color:#6c757d;">N/A</div>
@endif

{{-- ─── Investigations ─── --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px; margin-top:10px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#0d47a1;">Investigation Results</span>
</div>
@if(isset($testResults) && $testResults->count() > 0)
    @php
        $resultToFloat = function ($val) {
            if ($val === null || $val === '') return null;
            if (is_numeric($val)) return (float)$val;
            if (preg_match('/-?\d+(?:[\.,]\d+)?/', (string)$val, $m)) return (float) str_replace(',', '.', $m[0]);
            return null;
        };
        $resultComputeStatus = function ($valueRaw, $rangeRaw) use ($resultToFloat) {
            $val = $resultToFloat($valueRaw);
            if ($val === null || !$rangeRaw) return null;
            $r = str_replace(["–","—","−"], "-", trim((string)$rangeRaw));
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
        $tplNameMap = ['LEGACY' => 'legacy', 'Long Text' => 'narrative_lab', 'Qualitative Positive Negative' => 'qualitative_lab', 'Single Numeric Lab Values' => 'single_numeric_lab', 'Urinalysis' => 'urinalysis', 'Full Blood Picture' => 'full_blood_picture', 'GeneXpert MTB/RIF' => 'genxpert_tb', 'ZN Stain Microscopy (AFB)' => 'zn_stain_tb', 'Blood Group & Rh Typing' => 'blood_grouping', 'PBS – Microfilaria' => 'pbs_microfilaria', 'PBS – Malaria Parasites' => 'pbs_malaria', 'PBS – RBC Morphology' => 'pbs_rbc_morphology', 'PSA Semi-quantitative' => 'psa_semiquantitative', 'Gram Stain Microscopy' => 'gram_stain'];

        $simpleResults  = collect();
        $complexResults = collect();
        foreach ($testResults as $result) {
            $tc = $result->template_result->metadata['template_code'] ?? null;
            if (!$tc) $tc = $tplNameMap[$result->template_result->template_name ?? ''] ?? ($result->template_result->template_name ?? '');
            $result->_tplCode = $tc;
            if (in_array($tc, ['legacy', 'narrative_lab', 'urinalysis', 'full_blood_picture', 'blood_count', 'genxpert_tb', 'zn_stain_tb', 'blood_grouping', 'pbs_microfilaria', 'pbs_malaria', 'pbs_rbc_morphology', 'psa_semiquantitative', 'gram_stain'])) $complexResults->push($result);
            else $simpleResults->push($result);
        }
    @endphp

    {{-- Simple results: unified table --}}
    @if($simpleResults->count())
    <div class="table-responsive mb-3">
        <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Investigation</th>
                    <th>Value</th>
                    <th>Unit</th>
                    <th>Normal Range</th>
                    <th>Status</th>
                    <th style="white-space:nowrap;">Reported</th>
                </tr>
            </thead>
            <tbody>
                @foreach($simpleResults as $result)
                    @php
                        $params = $result->form_data['parameters'] ?? [];
                        if (is_string($params)) $params = json_decode($params, true);
                        if (!is_array($params)) $params = [];
                        $firstRow = true;
                    @endphp
                    @if(empty($params))
                        <tr>
                            <td class="fw-medium">{{ $result->test_name }}</td>
                            <td colspan="3" class="text-muted">—</td>
                            <td>—</td>
                            <td class="text-muted small" style="white-space:nowrap;">{{ $result->reported_at->format('d/m/Y H:i') }}<br>{{ $result->reported_by }}</td>
                        </tr>
                    @else
                        @foreach($params as $param)
                            @php
                                if (is_string($param)) $param = json_decode($param, true);
                                if (!is_array($param)) continue;
                                $pvalue = $param['value'] ?? null;
                                $punit  = $param['unit'] ?? '';
                                $prange = $param['normal_range'] ?? '';
                                $status = $param['status'] ?? $resultComputeStatus($pvalue, $prange) ?? 'unknown';
                                $badgeClass = match($status) {
                                    'high'     => 'bg-danger',
                                    'low'      => 'bg-warning text-dark',
                                    'normal'   => 'bg-success',
                                    'critical' => 'bg-danger',
                                    default    => 'bg-secondary'
                                };
                            @endphp
                            <tr>
                                <td class="fw-medium">
                                    @if($firstRow)
                                        {{ $result->test_name }}
                                        @php $firstRow = false; @endphp
                                    @endif
                                </td>
                                <td>{{ $pvalue ?? '—' }}</td>
                                <td class="text-muted">{{ $punit }}</td>
                                <td class="text-muted">{{ $prange }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
                                <td class="text-muted small" style="white-space:nowrap;">
                                    @if($loop->first)
                                        {{ $result->reported_at->format('d/m/Y H:i') }}<br>{{ $result->reported_by }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Complex / narrative results --}}
    @foreach($complexResults as $result)
        <div style="border:1px solid #dee2e6; border-radius:4px; padding:10px 12px; margin-bottom:8px; background:#fafafa;">
            <table style="width:100%; border-collapse:collapse; margin-bottom:6px;">
                <tr>
                    <td style="font-weight:600; font-size:0.875rem;">{{ $result->test_name }}</td>
                    @if(isset($result->reported_at))
                    <td style="text-align:right; font-size:0.78rem; color:#6c757d;">{{ $result->reported_at->format('d/m/Y H:i') }}</td>
                    @endif
                </tr>
            </table>

            @if($result->template_result)
                @if(!empty($isPdf))
                    @includeIf('lab.results.modal', ['result' => $result->template_result])
                @else
                    <div id="complexResultInline-{{ $result->template_result->id }}">
                        <div class="d-flex justify-content-center align-items-center" style="min-height:60px;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <script>
                        (function(){
                            const target = document.getElementById('complexResultInline-{{ $result->template_result->id }}');
                            if (!target) return;
                            fetch('/lab/template-results/{{ $result->template_result->id }}/modal')
                                .then(r => { if (!r.ok) throw new Error('Failed to load'); return r.text(); })
                                .then(html => { target.innerHTML = html; })
                                .catch(err => {
                                    target.innerHTML = `<div class="alert alert-danger py-2"><i class="fas fa-exclamation-triangle"></i> Failed to load result. <small class="text-muted">${err.message}</small></div>`;
                                });
                        })();
                    </script>
                @endif
            @endif
        </div>
    @endforeach
@else
    <div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px; color:#6c757d;">No test results available yet.</div>
@endif

{{-- ─── Prescriptions ─── --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px; margin-top:10px;">
    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em; color:#0d47a1;">Prescriptions</span>
</div>
@if(isset($prescriptions) && $prescriptions->count() > 0)
    <table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:0.875rem;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">#</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Medicine</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Dosage</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Frequency</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Duration</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Qty</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Route</th>
                <th style="border:1px solid #dee2e6; padding:6px 8px; text-align:left;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescriptions as $index => $prescription)
            <tr style="{{ $index % 2 === 0 ? 'background:#fff;' : 'background:#f8f9fa;' }}">
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $index + 1 }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $prescription->medication->generic_name ?? $prescription->medication->name ?? 'N/A' }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $prescription->dosage ?? 'N/A' }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $prescription->frequency->frequency_name ?? 'N/A' }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $prescription->duration_days ?? $prescription->duration ?? 'N/A' }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $prescription->quantity ?? 'N/A' }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ $prescription->administrationRoute->route_name ?? 'N/A' }}</td>
                <td style="border:1px solid #dee2e6; padding:5px 8px;">{{ ucfirst($prescription->status ?? 'N/A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div style="font-size:0.875rem; margin-bottom:14px; padding:0 4px; color:#6c757d;">N/A</div>
@endif
