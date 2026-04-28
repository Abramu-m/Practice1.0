{{-- Modal view for displaying investigation template results --}}
<!-- Investigation Information Header -->
<div class="alert alert-primary mb-4">
    <div class="row">
        <div class="col-md-8">
            <h6><i class="fas fa-vial"></i> Investigation Details</h6>
            <strong>{{ $result->investigation->medicalService->name }}</strong>
            @if($result->investigation->medicalService->code)
                <span class="badge bg-light text-dark ms-2">{{ $result->investigation->medicalService->code }}</span>
            @endif
            <br>
            <small class="text-muted">
                Patient: <strong>{{ $result->investigation->patient->first_name }} {{ $result->investigation->patient->last_name }}</strong> |
                Investigation ID: {{ $result->investigation->id }} |
                Priority: 
                <span class="badge bg-{{ $result->investigation->priority === 'stat' ? 'danger' : ($result->investigation->priority === 'urgent' ? 'warning' : 'secondary') }}">
                    {{ strtoupper($result->investigation->priority) }}
                </span>
            </small>
        </div>
        <div class="col-md-4 text-end">
            <div>
                <strong>Ordered:</strong> {{ $result->investigation->ordered_at ? $result->investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}<br>
                @if($result->investigation->doctor)
                    <strong>Doctor:</strong>
                    Dr. {{ $result->investigation->doctor->user->first_name }} {{ $result->investigation->doctor->user->last_name }}
                @else
                    <span class="text-muted">Not specified</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Result Status Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-{{ $result->form_status === 'final' ? 'success' : ($result->form_status === 'preliminary' ? 'info' : 'warning') }}">
            {{ ucfirst($result->form_status) }} Report
        </span>
        <span class="badge bg-light text-dark ms-2">{{ ucfirst($result->template_name) }} Template</span>
    </div>
    <div>
        <small class="text-muted">
            <i class="fas fa-calendar"></i> Reported: {{ $result->reported_at->format('M d, Y H:i') }} |
            <i class="fas fa-user"></i> {{ $result->reportedBy->name ?? 'Unknown' }}
        </small>
    </div>
</div>

<!-- Result Content -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-chart-line"></i> 
            {{ $result->investigation->medicalService->name }} Results
        </h6>
    </div>
    <div class="card-body">
        @php
            $tplCode = $result->metadata['template_code'] ?? $result->template_name ?? '';
        @endphp
        @if(in_array($tplCode, ['simple', 'simple_lab', 'single_numeric_lab', 'qualitative_lab', 'urinalysis', 'full_blood_picture', 'blood_count', 'genxpert_tb', 'zn_stain_tb', 'blood_grouping', 'pbs_microfilaria', 'pbs_malaria', 'pbs_rbc_morphology', 'psa_semiquantitative', 'gram_stain']) && isset($result->form_data['parameters']))
            {{-- Simple lab results display --}}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Value</th>
                            <th>Unit</th>
                            <th>Normal Range</th>
                            <th>Status</th>
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

                            // Helpers to compute status from a value and reference range (if status is missing)
                            $toFloat = function ($val) {
                                if ($val === null || $val === '') return null;
                                if (is_numeric($val)) return (float)$val;
                                // Extract first numeric (handles strings like "<5", ">= 3.2", "5 mg/dL")
                                if (preg_match('/-?\d+(?:[\.,]\d+)?/', (string)$val, $m)) {
                                    return (float) str_replace(',', '.', $m[0]);
                                }
                                return null;
                            };

                            $computeStatusFromRange = function ($valueRaw, $rangeRaw) use ($toFloat) {
                                // Arrays are not supported for computation
                                if (is_array($valueRaw) || is_array($rangeRaw)) return null;
                                $val = $toFloat($valueRaw);
                                if ($val === null || !$rangeRaw) return null;
                                $r = trim((string)$rangeRaw);
                                // Normalize unicode dashes to hyphen
                                $r = str_replace(["–", "—", "−"], "-", $r);
                                // Common patterns: "a-b" or "a to b"
                                if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*(?:-|to)\s*(-?\d+(?:\.\d+)?)\s*$/i', $r, $mm)) {
                                    $lo = (float)$mm[1];
                                    $hi = (float)$mm[2];
                                    if ($val < $lo) return 'low';
                                    if ($val > $hi) return 'high';
                                    return 'normal';
                                }
                                // Comparator patterns: "< x", "<= x", "> x", ">= x"
                                if (preg_match('/^\s*([<>]=?)\s*(-?\d+(?:\.\d+)?)\s*$/', $r, $mm)) {
                                    $op = $mm[1];
                                    $cut = (float)$mm[2];
                                    if ($op === '<')  return $val <  $cut ? 'normal' : 'high';
                                    if ($op === '<=') return $val <= $cut ? 'normal' : 'high';
                                    if ($op === '>')  return $val >  $cut ? 'normal' : 'low';
                                    if ($op === '>=') return $val >= $cut ? 'normal' : 'low';
                                }
                                // Fallback: cannot determine
                                return null;
                            };
                        @endphp
                        
                        @foreach($parameters as $param)
                            @php
                                // Handle both array and object parameter formats
                                if (is_string($param)) {
                                    $param = json_decode($param, true);
                                }
                                
                                // Ensure we have an array
                                if (!is_array($param)) {
                                    continue;
                                }
                                // Support both schema variants and compute status from range if missing
                                $pname = $param['parameter_name'] ?? ($param['parameter'] ?? 'N/A');
                                $pvalue = is_array($param['value'] ?? null) ? null : ($param['value'] ?? null);
                                $punit  = is_array($param['unit'] ?? null) ? '' : ($param['unit'] ?? '');
                                $prange = is_array($param['normal_range'] ?? null) ? '' : ($param['normal_range'] ?? '');
                                $status = $param['status'] ?? null;
                                if (!$status) {
                                    $status = $computeStatusFromRange($pvalue, $prange) ?? 'unknown';
                                }
                                $badgeClass = match($status) {
                                    'high' => 'bg-danger',
                                    'low' => 'bg-warning',
                                    'normal' => 'bg-success',
                                    'critical' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
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
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
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

            @if(isset($result->form_data['analyzed_by']) || isset($result->form_data['analysis_date']))
                <div class="mt-3">
                    <h6>Analysis Information:</h6>
                    <div class="row">
                        @if(isset($result->form_data['analyzed_by']))
                        <div class="col-md-6">
                            <strong>Analyzed By:</strong> 
                            @if(is_array($result->form_data['analyzed_by']))
                                {{ json_encode($result->form_data['analyzed_by']) }}
                            @else
                                {{ $result->form_data['analyzed_by'] }}
                            @endif
                        </div>
                        @endif
                        @if(isset($result->form_data['analysis_date']))
                        <div class="col-md-6">
                            <strong>Analysis Date:</strong> {{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('M d, Y H:i') }}
                        </div>
                        @endif
                    </div>
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
            <div class="mt-3 d-flex gap-4">
                @if(isset($result->form_data['analyzed_by']) && $result->form_data['analyzed_by'])
                    <div><span class="text-muted small">Analyzed By</span><br><strong>{{ $result->form_data['analyzed_by'] }}</strong></div>
                @endif
                @if(isset($result->form_data['analysis_date']) && $result->form_data['analysis_date'])
                    <div><span class="text-muted small">Analysis Date</span><br><strong>{{ \Carbon\Carbon::parse($result->form_data['analysis_date'])->format('M d, Y H:i') }}</strong></div>
                @endif
            </div>

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
