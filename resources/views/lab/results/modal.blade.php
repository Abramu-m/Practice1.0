{{-- Modal view for displaying investigation template results --}}
<!-- Result Content -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-chart-line"></i> 
            {{ $result->investigation->medicalService->name }} Results for 
            <strong>{{ $result->investigation->patient->mr_number }}: {{ $result->investigation->patient->first_name }} {{ $result->investigation->patient->last_name }}</strong> |
        </h6>
    </div>
    <div class="card-body">
        @php
            $tplCode = $result->metadata['template_code'] ?? $result->template_name ?? '';
            $analyzedByRaw = $result->form_data['analyzed_by'] ?? null;
            $analyzerName = is_numeric($analyzedByRaw)
                ? optional(\App\Models\User::find($analyzedByRaw))->name
                : ($analyzedByRaw ?: null);
        @endphp
        @if(in_array($tplCode, ['simple', 'simple_lab', 'single_numeric_lab', 'qualitative_lab', 'urinalysis', 'multistix', 'wet_prep_microscopy', 'stool_analysis', 'spermiogram', 'mrdt_malaria', 'full_blood_picture', 'blood_count', 'genxpert_tb', 'zn_stain_tb', 'blood_grouping', 'pbs_microfilaria', 'pbs_malaria', 'pbs_rbc_morphology', 'psa_semiquantitative', 'gram_stain']) && isset($result->form_data['parameters']))
            {{-- Simple lab results display --}}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Value</th>
                            <th>Unit</th>
                            <th>Normal Range</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Handle both array and object formats
                            $parameters = $result->form_data['parameters'];
                            if (is_string($parameters)) {
                                $parameters = json_decode($parameters, true);
                            }
                            // If it's still not an array, try to make it one
                            if (!is_array($parameters)) {
                                $parameters = [$parameters];
                            }

                        @endphp

                        @foreach($parameters as $param)
                            @php
                                if (is_string($param)) $param = json_decode($param, true);
                                if (!is_array($param)) continue;
                                $pname  = $param['parameter_name'] ?? ($param['parameter'] ?? 'N/A');
                                $pvalue = is_array($param['value'] ?? null) ? null : ($param['value'] ?? null);
                                $punit  = is_array($param['unit'] ?? null) ? '' : ($param['unit'] ?? '');
                                $prange = is_array($param['normal_range'] ?? null) ? '' : ($param['normal_range'] ?? '');
                            @endphp
                            <tr>
                                <td class="fw-medium">{{ $pname }}</td>
                                <td>
                                    @if(is_array($param['value'] ?? ''))
                                        {{ json_encode($param['value']) }}
                                    @else
                                        {{ $pvalue ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="text-muted">{{ $punit }}</td>
                                <td class="text-muted">{{ $prange }}</td>
                                <td class="text-muted">
                                    @if(is_array($param['remarks'] ?? ''))
                                        {{ json_encode($param['remarks']) }}
                                    @else
                                        {{ $param['remarks'] ?? '' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                <div class="mt-3">
                    <h6>Additional Comments:</h6>
                    <div class="alert alert-light">
                        @if(is_array($result->form_data['additional_comments']))
                            {{ json_encode($result->form_data['additional_comments']) }}
                        @else
                            {{ $result->form_data['additional_comments'] }}
                        @endif
                    </div>
                </div>
            @endif

            @if($analyzerName || isset($result->form_data['analysis_date']))
                <div class="mt-3 d-flex gap-4">
                    @if($analyzerName)
                        <div><span class="text-muted small">Analyzed By</span><br><strong>{{ $analyzerName }}</strong></div>
                    @endif
                    @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                        <div><span class="text-muted small">Analysis Date</span><br><strong>{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('d M Y H:i') }}</strong></div>
                    @endif
                </div>
            @endif

        @elseif($tplCode === 'narrative_lab')
            {{-- Narrative / free-text result --}}
            @php
                $narrativeValue = null;
                if (isset($result->form_data['parameters'])) {
                    $params = $result->form_data['parameters'];
                    if (is_string($params)) $params = json_decode($params, true);
                    $narrativeValue = $params[0]['value'] ?? null;
                }
            @endphp
            <div class="border rounded p-3 bg-light" style="white-space:pre-wrap;font-size:0.95rem;min-height:80px;">{{ $narrativeValue ?? '—' }}</div>
            @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                <div class="mt-3">
                    <h6>Additional Comments:</h6>
                    <div class="alert alert-light">{{ $result->form_data['additional_comments'] }}</div>
                </div>
            @endif
            @if($analyzerName || isset($result->form_data['analysis_date']))
                <div class="mt-3 d-flex gap-4">
                    @if($analyzerName)
                        <div><span class="text-muted small">Analyzed By</span><br><strong>{{ $analyzerName }}</strong></div>
                    @endif
                    @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                        <div><span class="text-muted small">Analysis Date</span><br><strong>{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('d M Y H:i') }}</strong></div>
                    @endif
                </div>
            @endif

        @elseif($tplCode === 'tb')
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
                        @if(is_array($result->form_data['clinical_notes'] ?? null))
                            {{ json_encode($result->form_data['clinical_notes']) }}
                        @else
                            {{ $result->form_data['clinical_notes'] }}
                        @endif
                    </div>
                </div>
            @endif
        @elseif($tplCode === 'legacy')
            {{-- LEGACY / single narrative-style result --}}
            @php
                $parameters = $result->form_data['parameters'] ?? [];

                if (is_string($parameters)) {
                    $parameters = json_decode($parameters, true);
                }

                if (!is_array($parameters)) {
                    $parameters = [];
                }

                $legacyValue = collect($parameters)
                ->map(function ($p) {
                    if (!is_array($p)) return null;

                    return trim($p['value'] ?? '');
                })
                ->filter()
                ->map(function ($line) {
                    return "• " . $line;
                })
                ->implode("\n");
            @endphp

            <div class="border rounded p-3 bg-light"
                style="white-space:pre-wrap;font-size:0.95rem;min-height:80px;">
                {{ $legacyValue }}
            </div>

            @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                <div class="mt-3">
                    <h6>Additional Comments:</h6>
                    <div class="alert alert-light">
                        {{ $result->form_data['additional_comments'] }}
                    </div>
                </div>
            @endif

            @if($analyzerName || isset($result->form_data['analysis_date']))
                <div class="mt-3 d-flex gap-4">
                    @if($analyzerName)
                        <div><span class="text-muted small">Analyzed By</span><br><strong>{{ $analyzerName }}</strong></div>
                    @endif
                    @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                        <div><span class="text-muted small">Analysis Date</span><br><strong>{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('d M Y H:i') }}</strong></div>
                    @endif
                </div>
            @endif
        @elseif($tplCode === 'sterility_anamnesis')
            {{-- Anamnesis for Sterility Patients — grouped sectioned display --}}
            @php
                $anaVals = [];
                if (isset($result->form_data['parameters'])) {
                    $ps = $result->form_data['parameters'];
                    if (is_string($ps)) $ps = json_decode($ps, true);
                    if (is_array($ps)) {
                        foreach ($ps as $p) {
                            if (is_array($p) && isset($p['parameter_name'])) {
                                $anaVals[$p['parameter_name']] = $p['value'] ?? '';
                            }
                        }
                    }
                }
                $av = fn(string $k) => $anaVals[$k] ?? '';
                $anaSections = [
                    'Obstetric History'  => ['Para', 'Years of Delivery', 'Abortions', 'Years of Abortion', 'Alive', 'D+C or EVA', 'CD4', 'HVL', 'Operations', 'Which Operations', 'Hysterosalpingography', 'HIV/AIDS/ART'],
                    'Husband Details'    => ['1st/2nd/3rd.. Husband', 'History of Orchitis', 'Husband is Father of Children', 'PITC', 'How Many Wives', 'Regular Drug Intake', 'Drug Name', 'She is the Nth Wife', 'Number of Children of Husband', 'Years with Partner', 'Age of Lastborn Child', 'Husband Operations', 'Type of Husband Operations'],
                    'Contraceptives'     => ['Contraceptive Method 1', 'Contraceptive Method 1 Duration', 'Contraceptive Method 2', 'Contraceptive Method 2 Duration', 'Contraceptive Method 3', 'Contraceptive Method 3 Duration'],
                    'Menstrual Cycle'    => ['Cycle Length', 'Menstrual Bleeding', 'Amenorrhea at the Moment', 'Duration of Cycle Changing', 'Intermediate Bleeding', 'Bleeding Intensity'],
                    'Prolactinemia'      => ['Milk Discharge'],
                    'PID'                => ['Previous PID', 'When (PID)', 'Dyspareunie', 'Dysmenstruation', 'Genital Itching'],
                    'STD — Wife'         => ['Wife STD', 'Wife STD Disease', 'Wife STD Year'],
                    'STD — Husband'      => ['Husband STD', 'Husband STD Disease', 'Husband STD Year'],
                    'Spermiogram Result' => ['Spermiogram'],
                ];
            @endphp
            <div class="row g-2">
                @foreach($anaSections as $secTitle => $secFields)
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-header py-1 px-3" style="background:#6c757d;color:#fff;">
                            <small class="fw-semibold text-uppercase tracking-wide">{{ $secTitle }}</small>
                        </div>
                        <div class="card-body py-2 px-3">
                            <div class="row row-cols-2 g-1">
                                @foreach($secFields as $field)
                                @php $val = $av($field); @endphp
                                <div class="col">
                                    <div class="d-flex align-items-start gap-1 py-1 border-bottom border-light">
                                        <span class="text-muted" style="font-size:0.78rem;min-width:155px;white-space:nowrap;">{{ $field }}</span>
                                        <strong class="small {{ $val === 'Yes' ? 'text-danger' : '' }}">{{ $val !== '' ? $val : '—' }}</strong>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
                <div class="mt-3">
                    <h6>Additional Comments:</h6>
                    <div class="alert alert-light">{{ $result->form_data['additional_comments'] }}</div>
                </div>
            @endif
            @if($analyzerName || isset($result->form_data['analysis_date']))
                <div class="mt-3 d-flex gap-4">
                    @if($analyzerName)
                        <div><span class="text-muted small">Analyzed By</span><br><strong>{{ $analyzerName }}</strong></div>
                    @endif
                    @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                        <div><span class="text-muted small">Analysis Date</span><br><strong>{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('d M Y H:i') }}</strong></div>
                    @endif
                </div>
            @endif

        @elseif(in_array($tplCode, ['genxpert_tb', 'zn_stain_tb']))
            {{-- GeneXpert TB / ZN Stain — structured summary of saved results --}}
            @php
                $fd = $result->form_data;
                $microRows = collect(['A', 'B', 'C'])->filter(fn($r) => !empty($fd['micro_result_' . $r]))->values();
                $hasXpert  = !empty($fd['xpert_result']);
                $hasLflam  = !empty($fd['lflam_result']);
                $skinSites = ['left_earlobe' => 'L. Earlobe', 'right_earlobe' => 'R. Earlobe', 'lesion_1' => 'Lesion 1', 'lesion_2' => 'Lesion 2'];
                $skinRows  = collect($skinSites)->filter(fn($label, $key) => !empty($fd['skin_result_' . $key]));
                $noResults = $microRows->isEmpty() && !$hasXpert && !$hasLflam && $skinRows->isEmpty();

                $rBadge = function (string $val): string {
                    $v = strtolower($val);
                    $class = match(true) {
                        in_array($v, ['neg', 'negative'])             => 'success',
                        in_array($v, ['positive', 'rr'])              => 'danger',
                        in_array($v, ['1+', '2+', '3+', 'scanty'])   => 'warning text-dark',
                        in_array($v, ['indeterminate'])               => 'warning text-dark',
                        default                                       => 'secondary',
                    };
                    return '<span class="badge bg-' . $class . '">' . e(strtoupper($val)) . '</span>';
                };
            @endphp

            @if($noResults)
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-flask fa-2x mb-2 d-block"></i>
                    No test results recorded yet.
                </div>
            @endif

            @if($microRows->isNotEmpty())
            <div class="border-bottom px-3 py-2">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="text-uppercase fw-bold small text-muted" style="letter-spacing:.05em;">Microscopy</span>
                    @if(!empty($fd['zn_fm']))
                        <span class="badge bg-light text-dark border">{{ strtoupper($fd['zn_fm']) }}</span>
                    @endif
                </div>
                <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:28px;">Spec.</th>
                            <th>Date</th>
                            <th>Appearance</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($microRows as $r)
                        <tr>
                            <td class="fw-bold">{{ $r }}</td>
                            <td>{{ $fd['micro_date_' . $r] ?? '—' }}</td>
                            <td class="text-muted small">{{ $fd['micro_appearance_' . $r] ?? '—' }}</td>
                            <td>{!! $rBadge($fd['micro_result_' . $r]) !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($hasXpert)
            <div class="border-bottom px-3 py-2">
                <div class="fw-bold small text-muted text-uppercase mb-2" style="letter-spacing:.05em;">Xpert MTB/RIF</div>
                <div class="d-flex flex-wrap gap-4">
                    <div>
                        <div class="text-muted" style="font-size:.75rem;">MTB Result</div>
                        {!! $rBadge($fd['xpert_result']) !!}
                    </div>
                    @if(!empty($fd['xpert_appearance']))
                    <div>
                        <div class="text-muted" style="font-size:.75rem;">Appearance</div>
                        <strong class="small">{{ $fd['xpert_appearance'] }}</strong>
                    </div>
                    @endif
                    @if(!empty($fd['xpert_date']))
                    <div>
                        <div class="text-muted" style="font-size:.75rem;">Date</div>
                        <strong class="small">{{ $fd['xpert_date'] }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($hasLflam)
            <div class="border-bottom px-3 py-2">
                <div class="fw-bold small text-muted text-uppercase mb-2" style="letter-spacing:.05em;">TB LF-LAM</div>
                <div class="d-flex flex-wrap gap-4">
                    <div>
                        <div class="text-muted" style="font-size:.75rem;">Result</div>
                        {!! $rBadge($fd['lflam_result']) !!}
                    </div>
                    @if(!empty($fd['lflam_date']))
                    <div>
                        <div class="text-muted" style="font-size:.75rem;">Date</div>
                        <strong class="small">{{ $fd['lflam_date'] }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($skinRows->isNotEmpty())
            <div class="border-bottom px-3 py-2">
                <div class="fw-bold small text-muted text-uppercase mb-2" style="letter-spacing:.05em;">Skin Smear — Leprosy</div>
                <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                    <thead class="table-light">
                        <tr><th>Site</th><th>Date</th><th>Result</th></tr>
                    </thead>
                    <tbody>
                        @foreach($skinRows as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $fd['skin_date_' . $key] ?? '—' }}</td>
                            <td>{!! $rBadge($fd['skin_result_' . $key]) !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!empty($fd['examined_by']) || !empty($fd['reviewed_by']) || !empty($fd['comments']))
            <div class="px-3 py-2 bg-light d-flex flex-wrap gap-3">
                @if(!empty($fd['examined_by']))
                <div>
                    <div class="text-muted" style="font-size:.75rem;">Examined By</div>
                    <strong class="small">{{ $fd['examined_by'] }}</strong>
                </div>
                @endif
                @if(!empty($fd['examined_date']))
                <div>
                    <div class="text-muted" style="font-size:.75rem;">Date</div>
                    <strong class="small">{{ $fd['examined_date'] }}</strong>
                </div>
                @endif
                @if(!empty($fd['reviewed_by']))
                <div>
                    <div class="text-muted" style="font-size:.75rem;">Reviewed By</div>
                    <strong class="small">{{ $fd['reviewed_by'] }}</strong>
                </div>
                @endif
                @if(!empty($fd['comments']))
                <div class="w-100">
                    <div class="text-muted" style="font-size:.75rem;">Comments</div>
                    <span class="small">{{ $fd['comments'] }}</span>
                </div>
                @endif
            </div>
            @endif

            <div class="px-3 pt-2 pb-1 text-end">
                <a href="{{ route('lab.template-results.view', $result->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-expand-alt me-1"></i> View Full Form
                </a>
            </div>

        @elseif($tplCode === 'cd4')
            @include('lab.result_templates.cd4', [
                'existingData'  => $result->form_data,
                'investigation' => $result->investigation,
                'visit'         => $result->investigation->visit,
                'isReadOnly'    => true,
            ])
            <div class="mt-2 text-end">
                <a href="{{ route('lab.template-results.view', $result->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-expand-alt me-1"></i> View Full Form
                </a>
            </div>

        @elseif($tplCode === 'general')
            @include('lab.result_templates.general', [
                'existingData'  => $result->form_data,
                'investigation' => $result->investigation,
                'isReadOnly'    => true,
            ])
            <div class="mt-2 text-end">
                <a href="{{ route('lab.template-results.view', $result->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-expand-alt me-1"></i> View Full Form
                </a>
            </div>

        @else
            {{-- Generic complex result display --}}
            <div class="row">
                @foreach($result->form_data as $key => $value)
                    @if(!in_array($key, ['_token', 'template_', 'action']) && !empty($value))
                    <div class="col-md-6 mb-3">
                        <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                        <div class="mt-1">
                            @if(is_array($value))
                                @foreach($value as $subKey => $subValue)
                                    <div><em>{{ ucwords(str_replace('_', ' ', $subKey)) }}:</em>
                                        @if(is_array($subValue))
                                            {{ json_encode($subValue) }}
                                        @else
                                            {{ $subValue }}
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                @if(is_array($value))
                                    {{ json_encode($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Metadata -->
<!--
@if($result->metadata)
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-info-circle"></i> Metadata
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    <strong>Template Version:</strong> {{ $result->template_version }}<br>
                    <strong>Form Fields:</strong> {{ $result->metadata['form_fields_count'] ?? 'N/A' }}<br>
                    <strong>Submitted:</strong> {{ isset($result->metadata['submitted_at']) ? \Carbon\Carbon::parse($result->metadata['submitted_at'])->format('M d, Y H:i:s') : 'N/A' }}
                </small>
            </div>
            @if($result->verifiedBy)
            <div class="col-md-6">
                <small class="text-muted">
                    <strong>Verified by:</strong> {{ $result->verifiedBy->name }}<br>
                    <strong>Verified at:</strong> {{ $result->verified_at ? $result->verified_at->format('M d, Y H:i') : 'N/A' }}
                </small>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
-->