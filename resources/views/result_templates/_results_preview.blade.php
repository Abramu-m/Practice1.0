{{-- Results Preview Partial — mirrors the real lab/results/view.blade.php format for preview purposes --}}
@php
    // Build sample rows from template_fields if available, otherwise fall back to code-based defaults
    $rows = [];

    if (!empty($template->template_fields) && is_array($template->template_fields)) {
        foreach ($template->template_fields as $field) {
            $label = $field['label'] ?? $field['name'] ?? 'Parameter';
            $unit  = $field['unit'] ?? '';
            $range = $field['normal_range'] ?? $field['reference_range'] ?? '';
            $rows[] = ['name' => $label, 'unit' => $unit, 'range' => $range];
        }
    }

    // Well-known templates: provide representative sample rows
    if (empty($rows)) {
        $codeSamples = [
            'single_numeric_lab' => [
                ['name' => $template->name ?? 'Parameter', 'unit' => $template->unit ?? '', 'range' => ''],
            ],
            'qualitative_lab' => [
                ['name' => $template->name ?? 'Parameter', 'unit' => '', 'range' => 'Negative / Positive'],
            ],
            'narrative_lab' => null, // special: narrative
            'full_blood_picture' => [
                ['name' => 'Haemoglobin (Hb)',   'unit' => 'g/dL',      'range' => '11.5 – 17.5'],
                ['name' => 'Haematocrit (PCV)',   'unit' => '%',         'range' => '36 – 53'],
                ['name' => 'RBC Count',           'unit' => '×10⁶/µL',  'range' => '3.8 – 5.9'],
                ['name' => 'Total WBC',           'unit' => '×10³/µL',  'range' => '4.0 – 11.0'],
                ['name' => 'Neutrophils %',       'unit' => '%',         'range' => '40 – 75'],
                ['name' => 'Lymphocytes %',       'unit' => '%',         'range' => '20 – 45'],
                ['name' => 'Platelet Count',      'unit' => '×10³/µL',  'range' => '150 – 400'],
                ['name' => 'Red Cell Morphology', 'unit' => '',          'range' => 'Normocytic normochromic'],
            ],
            'urinalysis' => [
                ['name' => 'Colour',       'unit' => '', 'range' => 'Pale Yellow'],
                ['name' => 'Appearance',   'unit' => '', 'range' => 'Clear'],
                ['name' => 'pH',           'unit' => '', 'range' => '4.5 – 8.0'],
                ['name' => 'Specific Gravity', 'unit' => '', 'range' => '1.003 – 1.030'],
                ['name' => 'Protein',      'unit' => '', 'range' => 'Negative'],
                ['name' => 'Glucose',      'unit' => '', 'range' => 'Negative'],
                ['name' => 'Ketones',      'unit' => '', 'range' => 'Negative'],
                ['name' => 'Blood',        'unit' => '', 'range' => 'Negative'],
            ],
            'blood_grouping' => [
                ['name' => 'ABO Group',   'unit' => '', 'range' => 'A / B / AB / O'],
                ['name' => 'Rh Factor',   'unit' => '', 'range' => 'Positive / Negative'],
                ['name' => 'Direct Coombs', 'unit' => '', 'range' => 'Negative'],
            ],
            'cd4' => [
                ['name' => 'CD4 Count',       'unit' => 'cells/µL', 'range' => '500 – 1500'],
                ['name' => 'CD4 Percentage',  'unit' => '%',        'range' => '28 – 57'],
            ],
            'tb' => [
                ['name' => 'Microscopy Result',  'unit' => '', 'range' => 'Negative'],
                ['name' => 'Xpert MTB/RIF',      'unit' => '', 'range' => 'Not Detected'],
            ],
            'gram_stain' => [
                ['name' => 'Gram Stain Result',  'unit' => '', 'range' => ''],
                ['name' => 'Organism',           'unit' => '', 'range' => ''],
            ],
            'pbs_malaria' => [
                ['name' => 'Malaria Parasites',  'unit' => '', 'range' => 'Not Seen'],
                ['name' => 'Species',            'unit' => '', 'range' => ''],
            ],
        ];

        $code = $template->code ?? '';
        $rows = $codeSamples[$code] ?? [
            ['name' => $template->name ?? 'Result', 'unit' => '', 'range' => ''],
        ];
    }

    $isNarrative = ($template->code ?? '') === 'narrative_lab';
    $isGenericProcedure = str_contains($template->code ?? '', 'procedure') || str_contains($template->code ?? '', 'imaging') || str_contains($template->code ?? '', 'general');
@endphp

<div style="font-family: inherit;">

    {{-- Mock investigation header --}}
    <div class="mb-3 p-3 rounded" style="background: linear-gradient(135deg, #e8f4fd, #f0f8ff); border-left: 4px solid #0d6efd;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-1">
                    <i class="fas fa-vial text-primary me-2"></i>
                    {{ $template->name }}
                    @if($template->code)
                        <span class="badge bg-secondary ms-2 fw-normal" style="font-size:0.7rem;">{{ $template->code }}</span>
                    @endif
                </h6>
                <div class="text-muted small">
                    Patient: <strong class="text-dark">Sample Patient</strong>
                    &nbsp;&bull;&nbsp; Investigation #—
                    &nbsp;&bull;&nbsp;
                    <span class="badge bg-secondary">ROUTINE</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end text-muted small">
                <div><i class="fas fa-calendar-alt me-1"></i> Ordered: —</div>
                <div><i class="fas fa-user-md me-1"></i> Dr. —</div>
            </div>
        </div>
    </div>

    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:0.82em;">
        <i class="fas fa-info-circle me-1"></i>
        This is a sample preview of how finalized results will appear. Actual values will be filled in by lab staff.
    </div>

    {{-- Results card --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 text-dark">
                <i class="fas fa-chart-line text-success me-2"></i>
                {{ $template->name }} Results
            </h6>
        </div>
        <div class="card-body p-0">
            @if($isNarrative)
                <div class="p-4">
                    <p class="text-muted small mb-1">{{ $template->name }}</p>
                    <div class="border rounded p-3 bg-light" style="white-space:pre-wrap;font-size:0.95rem;">
                        [Narrative findings will appear here once entered by the clinician.]
                    </div>
                </div>
            @elseif($isGenericProcedure)
                <div class="p-4">
                    <div class="row">
                        @foreach($rows as $row)
                        <div class="col-md-6 mb-3">
                            <p class="text-muted small mb-1">{{ $row['name'] }}</p>
                            <span class="fw-semibold text-muted fst-italic">—</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            @else
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
                            @foreach($rows as $row)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-muted fst-italic">—</td>
                                <td class="text-muted small">{{ $row['unit'] }}</td>
                                <td class="text-muted small">{{ $row['range'] }}</td>
                                <td><span class="badge bg-secondary">—</span></td>
                                <td class="pe-4 text-muted small">—</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Analysis footer --}}
            <div class="px-4 py-3 border-top bg-light d-flex flex-wrap gap-4">
                <div>
                    <span class="text-muted small">Analyzed By</span><br>
                    <span class="fw-semibold text-muted fst-italic">—</span>
                </div>
                <div>
                    <span class="text-muted small">Analysis Date</span><br>
                    <span class="fw-semibold text-muted fst-italic">—</span>
                </div>
                <div>
                    <span class="text-muted small">Reported By</span><br>
                    <span class="fw-semibold text-muted fst-italic">—</span>
                </div>
                <div>
                    <span class="text-muted small">Reported At</span><br>
                    <span class="fw-semibold text-muted fst-italic">—</span>
                </div>
            </div>
        </div>
    </div>

</div>
