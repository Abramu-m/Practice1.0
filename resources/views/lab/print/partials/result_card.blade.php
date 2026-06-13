{{-- PDF result card for a single investigation --}}
@php
    $tplCode = $result->metadata['template_code'] ?? $result->template_name ?? '';
    $tplNameMap = [
        'legacy' => 'legacy',
        'long text' => 'narrative_lab',
        'qualitative positive negative' => 'qualitative_lab',
        'single numeric lab values' => 'single_numeric_lab',
        'urinalysis' => 'urinalysis',
        'multistix' => 'multistix',
        'wet preparation microscopy' => 'wet_prep_microscopy',
        'stool analysis' => 'stool_analysis',
        'spermiogram' => 'spermiogram',
        'anamnesis for sterility patients' => 'sterility_anamnesis',
        'full blood picture' => 'full_blood_picture',
        'mrdt malaria' => 'mrdt_malaria',
        'genexpert mtb/rif' => 'genxpert_tb',
        'zn stain microscopy (afb)' => 'zn_stain_tb',
        'blood group & rh typing' => 'blood_grouping',
        'pbs – microfilaria' => 'pbs_microfilaria',
        'pbs – malaria parasites' => 'pbs_malaria',
        'pbs – rbc morphology' => 'pbs_rbc_morphology',
        'psa semi-quantitative' => 'psa_semiquantitative',
        'gram stain microscopy' => 'gram_stain',
    ];
    $normalizedName = is_string($tplCode) ? mb_strtolower(trim($tplCode)) : '';
    if ($normalizedName) {
        if (isset($tplNameMap[$normalizedName])) {
            $tplCode = $tplNameMap[$normalizedName];
        } elseif (str_contains($normalizedName, 'single numeric')) {
            $tplCode = 'single_numeric_lab';
        } elseif (str_contains($normalizedName, 'qualitative')) {
            $tplCode = 'qualitative_lab';
        } elseif (str_contains($normalizedName, 'long text') || str_contains($normalizedName, 'legacy') || str_contains($normalizedName, 'narrative')) {
            $tplCode = 'narrative_lab';
        } elseif (str_contains($normalizedName, 'urinalysis')) {
            $tplCode = 'urinalysis';
        } elseif (str_contains($normalizedName, 'full blood') || str_contains($normalizedName, 'complete blood count') || str_contains($normalizedName, 'cbc')) {
            $tplCode = 'full_blood_picture';
        } elseif (str_contains($normalizedName, 'blood count')) {
            $tplCode = 'blood_count';
        } elseif (str_contains($normalizedName, 'genexpert') || str_contains($normalizedName, 'gene xpert') || str_contains($normalizedName, 'mtb')) {
            $tplCode = 'genxpert_tb';
        } elseif (str_contains($normalizedName, 'zn stain') || str_contains($normalizedName, 'afb')) {
            $tplCode = 'zn_stain_tb';
        } elseif (str_contains($normalizedName, 'blood group')) {
            $tplCode = 'blood_grouping';
        } elseif (str_contains($normalizedName, 'pbs') || str_contains($normalizedName, 'microfilaria') || str_contains($normalizedName, 'malaria') || str_contains($normalizedName, 'rbc morphology')) {
            $tplCode = 'pbs_malaria';
        }
    }

    $analyzedByRaw = $result->form_data['analyzed_by'] ?? null;
    $reporterName = $result->reportedBy->name
        ?? (is_numeric($analyzedByRaw) ? optional(\App\Models\User::find($analyzedByRaw))->name : $analyzedByRaw)
        ?? null;
    $reporterDate = isset($result->form_data['analysis_date']) ? \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('d M Y H:i') : null;

    // Shape-based fallback: any template with a parameters[] array renders as a
    // generic table, regardless of $tplCode — covers new/unmapped template codes.
    $resultParams = $result->form_data['parameters'] ?? null;
    if (is_string($resultParams)) $resultParams = json_decode($resultParams, true);
    $hasGenericParameters = is_array($resultParams) && count($resultParams) > 0;
@endphp

{{-- Section heading --}}
<div style="border-left:3px solid #0d6efd; padding:3px 10px; background:#f0f4ff; margin-bottom:6px; margin-top:14px;">
    <span style="font-weight:700; font-size:0.85rem;">{{ $result->investigation->medicalService->name }} Results</span>
</div>

@if($tplCode === 'narrative_lab')
    @php
        $narrativeValue = null;
        if (isset($result->form_data['parameters'])) {
            $ps = $result->form_data['parameters'];
            if (is_string($ps)) $ps = json_decode($ps, true);
            $narrativeValue = $ps[0]['value'] ?? null;
        }
    @endphp
    <div style="border:1px solid #dee2e6; padding:8px 10px; font-size:0.875rem; white-space:pre-wrap; margin-bottom:6px;">{{ $narrativeValue ?? '—' }}</div>
    @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
        <div style="font-size:0.8rem; margin-bottom:4px;"><strong>Comments:</strong> {{ $result->form_data['additional_comments'] }}</div>
    @endif

@elseif($tplCode === 'legacy')
    @php
        $ps = $result->form_data['parameters'] ?? [];
        if (is_string($ps)) $ps = json_decode($ps, true);
        if (!is_array($ps)) $ps = [];
        $legacyText = collect($ps)->map(fn($p) => is_array($p) ? trim($p['value'] ?? '') : null)->filter()->map(fn($l) => '• '.$l)->implode("\n");
    @endphp
    <div style="border:1px solid #dee2e6; padding:8px 10px; font-size:0.875rem; white-space:pre-wrap; margin-bottom:6px;">{{ $legacyText }}</div>
    @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
        <div style="font-size:0.8rem; margin-bottom:4px;"><strong>Comments:</strong> {{ $result->form_data['additional_comments'] }}</div>
    @endif

@elseif($tplCode === 'sterility_anamnesis')
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
    @foreach($anaSections as $secTitle => $secFields)
    <div style="margin-bottom:4px;">
        <div style="background:#6c757d; color:#fff; padding:2px 8px; font-size:0.72rem; font-weight:600; text-transform:uppercase;">{{ $secTitle }}</div>
        <table style="width:100%; border-collapse:collapse; font-size:0.78rem;">
            @foreach(array_chunk($secFields, 2) as $pair)
            <tr>
                @foreach($pair as $field)
                @php $val = $av($field); @endphp
                <td style="border:1px solid #dee2e6; padding:2px 6px; width:50%; vertical-align:top;">
                    <span style="color:#6c757d;">{{ $field }}:</span>
                    <strong style="{{ $val === 'Yes' ? 'color:#dc3545;' : '' }}">{{ $val !== '' ? $val : '—' }}</strong>
                </td>
                @endforeach
                @if(count($pair) === 1)<td style="border:1px solid #dee2e6; padding:2px 6px; width:50%;"></td>@endif
            </tr>
            @endforeach
        </table>
    </div>
    @endforeach
    @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
        <div style="font-size:0.8rem; margin-top:4px;"><strong>Comments:</strong> {{ $result->form_data['additional_comments'] }}</div>
    @endif

@elseif($hasGenericParameters)
    @php $parameters = $resultParams; @endphp
    <table style="width:100%; border-collapse:collapse; font-size:0.875rem; margin-bottom:6px;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="border:1px solid #dee2e6; padding:5px 8px; text-align:left; width:35%;">Parameter</th>
                <th style="border:1px solid #dee2e6; padding:5px 8px; text-align:left; width:15%;">Value</th>
                <th style="border:1px solid #dee2e6; padding:5px 8px; text-align:left; width:12%;">Unit</th>
                <th style="border:1px solid #dee2e6; padding:5px 8px; text-align:left; width:20%;">Normal Range</th>
                <th style="border:1px solid #dee2e6; padding:5px 8px; text-align:left; width:18%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parameters as $i => $param)
                @php
                    if (is_string($param)) $param = json_decode($param, true);
                    if (!is_array($param)) continue;
                    $pname  = $param['parameter_name'] ?? ($param['parameter'] ?? 'N/A');
                    $pvalue = is_array($param['value'] ?? null) ? '' : ($param['value'] ?? '');
                    $punit  = is_array($param['unit'] ?? null) ? '' : ($param['unit'] ?? '');
                    $prange = is_array($param['normal_range'] ?? null) ? '' : ($param['normal_range'] ?? '');
                    $premarks = is_array($param['remarks'] ?? null) ? '' : ($param['remarks'] ?? '');
                    $rowBg = $i % 2 === 0 ? '#fff' : '#f8f9fa';
                @endphp
                <tr style="background:{{ $rowBg }};">
                    <td style="border:1px solid #dee2e6; padding:4px 8px; font-weight:500;">{{ $pname }}</td>
                    <td style="border:1px solid #dee2e6; padding:4px 8px;">{{ $pvalue !== '' ? $pvalue : '—' }}</td>
                    <td style="border:1px solid #dee2e6; padding:4px 8px; color:#6c757d;">{{ $punit }}</td>
                    <td style="border:1px solid #dee2e6; padding:4px 8px; color:#6c757d;">{{ $prange }}</td>
                    <td style="border:1px solid #dee2e6; padding:4px 8px; color:#6c757d;">{{ $premarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($result->form_data['additional_comments']) && $result->form_data['additional_comments'])
        <div style="font-size:0.8rem; color:#495057; margin-bottom:4px;">
            <strong>Comments:</strong>
            {{ is_array($result->form_data['additional_comments']) ? implode(', ', $result->form_data['additional_comments']) : $result->form_data['additional_comments'] }}
        </div>
    @endif

@else
    {{-- Generic fallback --}}
    <table style="width:100%; border-collapse:collapse; font-size:0.875rem; margin-bottom:6px;">
        @foreach($result->form_data as $key => $value)
            @if(!in_array($key, ['_token', 'template_', 'action', 'analyzed_by', 'analysis_date']) && !empty($value) && !is_array($value))
            <tr>
                <td style="border:1px solid #dee2e6; padding:4px 8px; width:35%; font-weight:600; background:#f8f9fa;">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                <td style="border:1px solid #dee2e6; padding:4px 8px;">{{ $value }}</td>
            </tr>
            @endif
        @endforeach
    </table>
@endif

{{-- Reporter info --}}
@if($reporterName || $reporterDate)
<div style="font-size:0.78rem; color:#6c757d; text-align:right; margin-bottom:12px;">
    @if($reporterName) Reported by: <strong style="color:#495057;">{{ $reporterName }}</strong> @endif
    @if($reporterDate) &nbsp; on {{ $reporterDate }} @endif
</div>
@endif
