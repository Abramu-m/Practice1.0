<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Lab Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1f3a93;
        }
        .facility-info {
            margin-bottom: 20px;
            border-top: 2px solid #1f3a93;
            border-bottom: 2px solid #1f3a93;
            padding: 10px 0;
        }
        .facility-info p {
            margin: 5px 0;
        }
        .summary-section {
            margin: 20px 0;
        }
        .summary-section h2 {
            background-color: #f0f0f0;
            padding: 10px;
            margin: 0 0 10px 0;
            border-left: 4px solid #1f3a93;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #1f3a93;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary-card {
            display: inline-block;
            width: 22%;
            margin: 1%;
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: top;
        }
        .summary-card strong {
            display: block;
            margin-bottom: 10px;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #1f3a93;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Monthly Laboratory Reports</h1>
        <p>All Tests Conducted and Completion Status</p>
    </div>

    <div class="facility-info">
        <p><strong>Facility:</strong> {{ $facility['name'] ?? 'N/A' }}</p>
        <p><strong>Region:</strong> {{ $facility['region'] ?? 'N/A' }}</p>
        <p><strong>District:</strong> {{ $facility['district'] ?? 'N/A' }}</p>
        <p><strong>Report Period:</strong> {{ $month_name }} {{ $year }}</p>
        <p><strong>Generated:</strong> {{ $generated_at->format('d M Y H:i') }}</p>
    </div>

    <div class="summary-section">
        <h2>Summary Statistics</h2>
        <div class="summary-card">
            <strong>Total Tests</strong>
            <div class="value">{{ $total_tests ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <strong>Completed</strong>
            <div class="value">{{ $completed_tests ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <strong>Pending</strong>
            <div class="value">{{ $pending_tests ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <strong>Completion Rate</strong>
            <div class="value">{{ $completion_rate ?? 0 }}%</div>
        </div>
    </div>

    @if(isset($by_test_type))
    <div class="summary-section">
        <h2>Tests by Type</h2>
        <table>
            <thead>
                <tr>
                    <th>Test Name</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Completion Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($by_test_type as $test)
                <tr>
                    <td>{{ $test['test_name'] }}</td>
                    <td>{{ $test['total_tests'] ?? 0 }}</td>
                    <td>{{ $test['completed_tests'] ?? 0 }}</td>
                    <td>{{ $test['pending_tests'] ?? 0 }}</td>
                    <td>{{ $test['completion_rate'] ?? 0 }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No test data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($by_category))
    <div class="summary-section">
        <h2>Tests by Category</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($by_category as $cat)
                <tr>
                    <td>{{ $cat['category'] }}</td>
                    <td>{{ $cat['count'] ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">No category data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This report was automatically generated by Practice 1.0 Health Information System</p>
        <p>For official use only. Confidential information.</p>
    </div>
</body>
</html>
