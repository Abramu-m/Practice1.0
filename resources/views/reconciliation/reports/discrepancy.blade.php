<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discrepancy Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #2d3748; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #f7fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .critical { background: #fed7d7; color: #c53030; }
        .minor    { background: #bee3f8; color: #2b6cb0; }
        .summary  { margin-bottom: 16px; padding: 10px; background: #edf2f7; border-radius: 4px; }
        .summary span { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Stock Discrepancy Report</h1>
    <div class="meta">Generated: {{ now()->format('d M Y, H:i') }}</div>

    <div class="summary">
        Total items flagged: <span>{{ count($report) }}</span>
        &nbsp;|&nbsp;
        Critical: <span>{{ collect($report)->where('severity', 'critical')->count() }}</span>
        &nbsp;|&nbsp;
        Minor: <span>{{ collect($report)->where('severity', 'minor')->count() }}</span>
    </div>

    @if(count($report) > 0)
    <table>
        <thead>
            <tr>
                <th>Medication</th>
                <th>Batch</th>
                <th>Issues</th>
                <th>Qty Received</th>
                <th>Status</th>
                <th>Expiry</th>
                <th>Severity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $item)
            <tr>
                <td>{{ $item['medication'] ?? '—' }}</td>
                <td>{{ $item['batch_number'] ?? '—' }}</td>
                <td>{{ implode('; ', (array)($item['issues'] ?? [])) }}</td>
                <td>{{ $item['received_quantity'] ?? 0 }}</td>
                <td>{{ ucfirst($item['status'] ?? '—') }}</td>
                <td>{{ $item['expiry_date'] ? \Carbon\Carbon::parse($item['expiry_date'])->format('d/m/Y') : '—' }}</td>
                <td><span class="badge {{ $item['severity'] ?? 'minor' }}">{{ ucfirst($item['severity'] ?? 'minor') }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>No discrepancies found.</p>
    @endif
</body>
</html>
