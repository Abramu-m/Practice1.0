{{-- Anamnesis for Sterility Patients Result Template --}}
{{-- Custom questionnaire layout; stores via parameters[idx][parameter_name + value] --}}
@php
// Parameter definitions: [idx => [label, type, options[]]]
$aParams = [
    // Obstetric History
    0  => ['Para',                             'text',   []],
    1  => ['Years of Delivery',                'text',   []],
    2  => ['Operations',                       'select', ['Yes', 'No']],
    3  => ['Which Operations',                 'text',   []],
    4  => ['Hysterosalpingography',            'select', ['No', 'Yes – Both tubes patent', 'Yes – One tube patent', 'Yes – Both tubes blocked']],
    5  => ['Abortions',                        'text',   []],
    6  => ['Years of Abortion',                'text',   []],
    7  => ['Alive',                            'text',   []],
    8  => ['D+C or EVA',                       'select', ['No', 'Yes']],
    9  => ['CD4',                              'text',   []],
    10 => ['HVL',                              'text',   []],
    11 => ['HIV/AIDS/ART',                     'select', ['No', 'Yes']],
    // Husband Details
    12 => ['1st/2nd/3rd.. Husband',            'select', ['1st', '2nd', '3rd', '4th', '5th']],
    13 => ['History of Orchitis',              'select', ['No', 'Yes']],
    14 => ['Husband is Father of Children',    'select', ['Yes', 'No']],
    15 => ['PITC',                             'select', ['NR', 'R']],
    16 => ['How Many Wives',                   'text',   []],
    17 => ['Regular Drug Intake',              'select', ['No', 'Yes']],
    18 => ['Drug Name',                        'text',   []],
    19 => ['She is the Nth Wife',              'select', ['1st', '2nd', '3rd', '4th', '5th', '6th']],
    20 => ['Number of Children of Husband',    'text',   []],
    21 => ['Years with Partner',               'text',   []],
    22 => ['Age of Lastborn Child',            'text',   []],
    23 => ['Husband Operations',               'select', ['No', 'Yes']],
    24 => ['Type of Husband Operations',       'text',   []],
    // Contraceptives
    25 => ['Contraceptive Method 1',           'text',   []],
    26 => ['Contraceptive Method 1 Duration',  'text',   []],
    27 => ['Contraceptive Method 2',           'text',   []],
    28 => ['Contraceptive Method 2 Duration',  'text',   []],
    29 => ['Contraceptive Method 3',           'text',   []],
    30 => ['Contraceptive Method 3 Duration',  'text',   []],
    // Menstrual Cycle
    31 => ['Cycle Length',                     'text',   []],
    32 => ['Menstrual Bleeding',               'text',   []],
    33 => ['Amenorrhea at the Moment',         'select', ['No', 'Yes']],
    34 => ['Duration of Cycle Changing',       'select', ['No', 'Slightly', 'More than 5 days']],
    35 => ['Intermediate Bleeding',            'select', ['No', 'Yes']],
    36 => ['Bleeding Intensity',               'select', ['Scanty', 'Normal', 'Strong', 'Clots']],
    // Prolactinemia
    37 => ['Milk Discharge',                   'select', ['No', 'Yes']],
    // PID
    38 => ['Previous PID',                     'select', ['No', 'Yes']],
    39 => ['When (PID)',                       'text',   []],
    40 => ['Dyspareunie',                      'select', ['No', 'Yes']],
    41 => ['Dysmenstruation',                  'select', ['No', 'Yes']],
    42 => ['Genital Itching',                  'select', ['No', 'Yes']],
    // STD – Wife
    43 => ['Wife STD',                         'select', ['No', 'Yes']],
    44 => ['Wife STD Disease',                 'text',   []],
    45 => ['Wife STD Year',                    'text',   []],
    // STD – Husband
    46 => ['Husband STD',                      'select', ['No', 'Yes']],
    47 => ['Husband STD Disease',              'text',   []],
    48 => ['Husband STD Year',                 'text',   []],
    // Spermiogram
    49 => ['Spermiogram',                      'select', ['No', 'Yes – Normal findings', 'Yes – Abnormal findings']],
];

// Build existing values keyed by parameter_name
$av = [];
if (isset($existingData['parameters'])) {
    $ep = $existingData['parameters'];
    if (is_string($ep)) $ep = json_decode($ep, true);
    if (is_array($ep)) {
        foreach ($ep as $p) {
            if (!isset($p['parameter_name'], $p['value'])) continue;
            foreach ($aParams as $idx => $def) {
                if ($def[0] === $p['parameter_name']) { $av[$idx] = $p['value']; break; }
            }
        }
    }
}

// Helper: render hidden parameter_name input
$pn = fn(int $i) => '<input type="hidden" name="parameters[' . $i . '][parameter_name]" value="' . e($aParams[$i][0]) . '">';
@endphp

{{-- Inline macro: render a single field (label + hidden name + visible input) --}}
@php
function anaField(int $idx, array $aParams, array $av, string $extraClass = ''): string {
    [$label, $type, $opts] = $aParams[$idx];
    $val = $av[$idx] ?? '';
    $hidden = '<input type="hidden" name="parameters[' . $idx . '][parameter_name]" value="' . e($label) . '">';
    $inputName = 'parameters[' . $idx . '][value]';

    if ($type === 'select') {
        $opts_html = '<option value="">— Select —</option>';
        foreach ($opts as $o) {
            $sel = ($val === $o) ? ' selected' : '';
            $opts_html .= '<option value="' . e($o) . '"' . $sel . '>' . e($o) . '</option>';
        }
        $input = '<select class="form-select form-select-sm ' . $extraClass . '" name="' . $inputName . '">' . $opts_html . '</select>';
    } else {
        $input = '<input type="text" class="form-control form-control-sm ' . $extraClass . '" name="' . $inputName . '" value="' . e($val) . '" placeholder="...">';
    }
    return $hidden . $input;
}
@endphp

<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">
    <div class="text-center mb-3">
        <h6 class="text-primary"><i class="fas fa-clipboard-list"></i> Anamnesis for Sterility Patients</h6>
        <small class="text-muted">Complete all relevant fields</small>
    </div>

    {{-- ── OBSTETRIC HISTORY ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">Obstetric History</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Para</label>
                    {!! anaField(0, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Years of Delivery</label>
                    {!! anaField(1, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Operations</label>
                    {!! anaField(2, $aParams, $av) !!}
                </div>
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Hysterosalpingography</label>
                    {!! anaField(4, $aParams, $av) !!}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Abortions</label>
                    {!! anaField(5, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Years of Abortion</label>
                    {!! anaField(6, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Which Operations</label>
                    {!! anaField(3, $aParams, $av) !!}
                </div>
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">HIV/AIDS/ART</label>
                    {!! anaField(11, $aParams, $av) !!}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Alive</label>
                    {!! anaField(7, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">D+C or EVA</label>
                    {!! anaField(8, $aParams, $av) !!}
                </div>
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">CD4</label>
                    {!! anaField(9, $aParams, $av) !!}
                </div>
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">HVL</label>
                    {!! anaField(10, $aParams, $av) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── HUSBAND DETAILS ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">Husband Details</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">1st/2nd/3rd.. Husband</label>
                    {!! anaField(12, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">History of Orchitis</label>
                    {!! anaField(13, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Husband is Father of Children</label>
                    {!! anaField(14, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">PITC</label>
                    {!! anaField(15, $aParams, $av) !!}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">How Many Wives</label>
                    {!! anaField(16, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Regular Drug Intake</label>
                    {!! anaField(17, $aParams, $av) !!}
                </div>
                <div class="col-sm-6">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Drug Name</label>
                    {!! anaField(18, $aParams, $av) !!}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">She is the Nth Wife</label>
                    {!! anaField(19, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">No. of Children of Husband</label>
                    {!! anaField(20, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Years with Partner</label>
                    {!! anaField(21, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Age of Lastborn Child</label>
                    {!! anaField(22, $aParams, $av) !!}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Husband Operations</label>
                    {!! anaField(23, $aParams, $av) !!}
                </div>
                <div class="col-sm-9">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Type of Husband Operations</label>
                    {!! anaField(24, $aParams, $av) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── CONTRACEPTIVES ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">Contraceptives</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2 mb-1">
                <div class="col-sm-6"><span class="small text-muted fst-italic">Type / Method</span></div>
                <div class="col-sm-6"><span class="small text-muted fst-italic">Years of Use</span></div>
            </div>
            @foreach([[25,26,'1:'],[27,28,'2:'],[29,30,'3:']] as [$mi, $di, $lbl])
            <div class="row g-2 mb-1 align-items-center">
                <div class="col-sm-1"><span class="fw-semibold">{{ $lbl }}</span></div>
                <div class="col-sm-5">{!! anaField($mi, $aParams, $av) !!}</div>
                <div class="col-sm-6">{!! anaField($di, $aParams, $av) !!}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── MENSTRUAL CYCLE ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">Menstrual Cycle</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Cycle Length (~days)</label>
                    {!! anaField(31, $aParams, $av) !!}
                </div>
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Menstrual Bleeding (days)</label>
                    {!! anaField(32, $aParams, $av) !!}
                </div>
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Amenorrhea at the Moment</label>
                    {!! anaField(33, $aParams, $av) !!}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Duration of Cycle Changing</label>
                    {!! anaField(34, $aParams, $av) !!}
                </div>
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Intermediate Bleeding</label>
                    {!! anaField(35, $aParams, $av) !!}
                </div>
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Bleeding Intensity</label>
                    {!! anaField(36, $aParams, $av) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── SIGNS OF PROLACTINEMIA ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">Signs of Prolactinemia</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-sm-4">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Milk Discharge</label>
                    {!! anaField(37, $aParams, $av) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── PID ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">PID</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Previous PID?</label>
                    {!! anaField(38, $aParams, $av) !!}
                </div>
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">When (year)</label>
                    {!! anaField(39, $aParams, $av) !!}
                </div>
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Dyspareunie</label>
                    {!! anaField(40, $aParams, $av) !!}
                </div>
                <div class="col-sm-2">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Dysmenstruation</label>
                    {!! anaField(41, $aParams, $av) !!}
                </div>
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Genital Itching</label>
                    {!! anaField(42, $aParams, $av) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── STD ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">STD</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-0">
                <div class="col-md-6 pe-md-2">
                    <p class="fw-semibold small mb-1">Wife</p>
                    <div class="row g-2">
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm mb-1">STD?</label>
                            {!! anaField(43, $aParams, $av) !!}
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label form-label-sm mb-1">Which disease</label>
                            {!! anaField(44, $aParams, $av) !!}
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm mb-1">When (year)</label>
                            {!! anaField(45, $aParams, $av) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-md-2 mt-2 mt-md-0" style="border-left:1px solid #dee2e6;">
                    <p class="fw-semibold small mb-1 ps-md-2">Husband</p>
                    <div class="row g-2">
                        <div class="col-sm-4">
                            <label class="form-label form-label-sm mb-1">STD?</label>
                            {!! anaField(46, $aParams, $av) !!}
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label form-label-sm mb-1">Which disease</label>
                            {!! anaField(47, $aParams, $av) !!}
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label form-label-sm mb-1">When (year)</label>
                            {!! anaField(48, $aParams, $av) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SPERMIOGRAM ── --}}
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-10 py-1 px-3">
            <span class="fw-semibold small text-uppercase">Spermiogram</span>
        </div>
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-sm-5">
                    <label class="form-label form-label-sm mb-1 fw-semibold">Spermiogram Result</label>
                    {!! anaField(49, $aParams, $av) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── QUALITY CONTROL ── --}}
    <div class="card mt-2">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Quality Control</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Recorded By:</strong></label>
                    <input type="text" class="form-control form-control-sm lab-readonly" name="analyzed_by"
                           value="{{ isset($currentUser) ? $currentUser->name : (auth()->user()->name ?? '') }}" readonly>
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Date:</strong></label>
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
