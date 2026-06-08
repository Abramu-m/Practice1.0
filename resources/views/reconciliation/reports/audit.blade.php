<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Trail Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2d3748; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f7fafc; }
        .in  { color: #276749; font-weight: bold; }
        .out { color: #c53030; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Stock Movement Audit Trail</h1>
    <div class="meta">Generated: {{ now()->format('d M Y, H:i') }} &nbsp;|&nbsp; Records: {{ $report->count() }}</div>

    @if($report->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Medication</th>
                <th>Type</th>
                <th>Qty</th>
                <th>From</th>
                <th>To</th>
                <th>Reference</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['movement_date'])->format('d/m/Y H:i') }}</td>
                <td>{{ $row['medication_name'] }}</td>
                <td>
                    <span class="{{ in_array($row['movement_type'], ['in','inward']) ? 'in' : 'out' }}">
                        {{ strtoupper($row['movement_type']) }}
                    </span>
                </td>
                <td>{{ $row['quantity'] }}</td>
                <td>{{ $row['from_location'] }}</td>
                <td>{{ $row['to_location'] }}</td>
                <td>{{ $row['reference_number'] ?? '—' }}</td>
                <td>{{ $row['created_by'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>No movements found for the selected filters.</p>
    @endif
</body>
</html>
