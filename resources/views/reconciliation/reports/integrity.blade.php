<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Integrity Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        h2 { font-size: 12px; margin: 14px 0 6px; border-bottom: 1px solid #cbd5e0; padding-bottom: 4px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 16px; }
        th { background: #2d3748; color: #fff; padding: 5px 8px; text-align: left; font-size: 10px; }
        td { padding: 4px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f7fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .critical { background: #fed7d7; color: #c53030; }
        .major    { background: #feebc8; color: #c05621; }
        .minor    { background: #bee3f8; color: #2b6cb0; }
        .status-good     { color: #276749; font-weight: bold; }
        .status-critical { color: #c53030; font-weight: bold; }
        .summary { padding: 10px; background: #edf2f7; border-radius: 4px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>Stock Integrity Report</h1>
    <div class="meta">Generated: {{ now()->format('d M Y, H:i') }}</div>

    <div class="summary">
        <strong>Overall Status:</strong>
        <span class="status-{{ in_array($report['status'] ?? '', ['critical','warning']) ? 'critical' : 'good' }}">
            {{ strtoupper($report['status'] ?? 'unknown') }}
        </span>
        &nbsp;|&nbsp;
        <strong>Medications Checked:</strong> {{ $report['total_medications'] ?? 0 }}
        &nbsp;|&nbsp;
        <strong>Critical Issues:</strong> {{ $report['summary']['critical_discrepancies'] ?? 0 }}
    </div>

    @foreach($report['checks_performed'] ?? [] as $checkName => $discrepancies)
        @if(count($discrepancies) > 0)
        <h2>{{ ucwords(str_replace('_', ' ', $checkName)) }} ({{ count($discrepancies) }} issue(s))</h2>
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($discrepancies[0]) as $col)
                        @if(!in_array($col, ['medication_id']))
                            <th>{{ ucwords(str_replace('_', ' ', $col)) }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($discrepancies as $row)
                <tr>
                    @foreach($row as $key => $val)
                        @if(!in_array($key, ['medication_id']))
                            <td>
                                @if($key === 'severity')
                                    <span class="badge {{ $val }}">{{ ucfirst($val) }}</span>
                                @elseif(is_array($val))
                                    {{ implode('; ', $val) }}
                                @else
                                    {{ $val }}
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    @endforeach

    @if(collect($report['checks_performed'] ?? [])->flatten(1)->count() === 0)
        <p>No integrity issues found. All checks passed.</p>
    @endif
</body>
</html>
