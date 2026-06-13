{{-- Test results table (simple results + complex/narrative results) --}}
{{-- Expects: $testResults --}}
@if(isset($testResults) && $testResults->count() > 0)
    @php
        // Helpers for status computation
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
        $simpleResults  = collect();
        $complexResults = collect();
        foreach ($testResults as $result) {
            $params = $result->form_data['parameters'] ?? null;
            if (is_string($params)) $params = json_decode($params, true);

            // A single short value renders inline; anything with multiple
            // parameters, no parameters[] at all (flat-field templates), or a
            // long single value collapses to a "View Full Results" button.
            $firstValue = is_array($params) ? ($params[0]['value'] ?? '') : '';
            $isSingleShortValue = is_array($params) && count($params) === 1
                && !is_array($firstValue) && strlen((string) $firstValue) <= 80;

            // narrative_lab (procedures, imaging, etc.) always collapses, even
            // for a short placeholder value — it supports long free text and
            // image attachments that the inline table can't represent.
            $templateCode = $result->metadata['template_code'] ?? null;
            if ($templateCode === 'narrative_lab') $isSingleShortValue = false;

            if ($isSingleShortValue) $simpleResults->push($result);
            else $complexResults->push($result);
        }
    @endphp

    {{-- ── Simple results: one unified table ── --}}
    @if($simpleResults->count())
    <div class="table-responsive mb-4">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Investigation</th>
                    <th>Value</th>
                    <th>Unit</th>
                    <th>Normal Range</th>
                    <th>Status</th>
                    <th>Reported</th>
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
                                    'low'      => 'bg-warning',
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

    {{-- ── Complex / narrative results ── --}}
    @foreach($complexResults as $result)
        <div class="d-flex justify-content-between align-items-center border p-3 mb-2 rounded bg-light">
            <span class="fw-semibold">{{ $result->test_name }}</span>
            @if($result->template_result)
                <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="viewComplexResult({{ $result->investigation_id ?? 'null' }}, {{ $result->template_result->id }})">
                    <i class="fas fa-expand-alt me-1"></i> View Full Results
                </button>
            @endif
        </div>
    @endforeach
@else
    <p class="text-muted">No test results available yet.</p>
@endif
