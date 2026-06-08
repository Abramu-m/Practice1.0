<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Financial Summary - {{ $summary_date->format('F d, Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .summary-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .clinic-name {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .clinic-info {
            font-size: 11px;
            color: #666;
        }
        .summary-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0;
            background-color: #f5f5f5;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .date-info {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #e8f4f8;
            border: 1px solid #b8d4da;
            border-radius: 4px;
            font-weight: bold;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        .metric-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .metric-title {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .metric-value.money {
            color: #2e7d2e;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .transactions-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .transactions-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .transactions-table .time {
            white-space: nowrap;
            font-size: 11px;
        }
        .category-section {
            margin: 30px 0;
        }
        .category-header {
            background-color: #e9ecef;
            padding: 10px 15px;
            font-weight: bold;
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .category-total {
            font-size: 14px;
            color: #2e7d2e;
        }
        .summary-box {
            background-color: #f0f8ff;
            border: 2px solid #4a90e2;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-amount {
            font-weight: bold;
        }
        .grand-total {
            border-top: 2px solid #333;
            margin-top: 12px;
            padding-top: 12px;
            font-size: 16px;
            color: #2c5530;
        }
        .payment-methods {
            background-color: #fff9e6;
            border: 1px solid #f0d000;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .payment-method-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .hourly-breakdown {
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .summary-container { max-width: none; }
            .metrics-grid { grid-template-columns: repeat(4, 1fr); }
        }
    </style>
</head>
<body>
    <div class="summary-container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">{{ config('app.clinic_name', 'Medical Facility') }}</div>
            <div class="clinic-info">
                {{ config('app.clinic_address', 'Clinic Address') }}<br>
                Phone: {{ config('app.clinic_phone', 'Phone Number') }} | Email: {{ config('app.clinic_email', 'clinic@example.com') }}
            </div>
        </div>

        <!-- Summary Title -->
        <div class="summary-title">DAILY FINANCIAL SUMMARY</div>

        <!-- Date Information -->
        <div class="date-info">
            {{ $summary_date->format('l, F d, Y') }}
        </div>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-title">Total Revenue</div>
                <div class="metric-value money">Tsh {{ number_format($total_revenue, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Total Transactions</div>
                <div class="metric-value">{{ $total_transactions }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Patient Payments</div>
                <div class="metric-value money">Tsh {{ number_format($patient_payments, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Insurance Coverage</div>
                <div class="metric-value money">Tsh {{ number_format($insurance_payments, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Average Transaction</div>
                <div class="metric-value money">
                    Tsh {{ $total_transactions > 0 ? number_format($total_revenue / $total_transactions, 2) : '0.00' }}
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Consultation Revenue</div>
                <div class="metric-value money">Tsh {{ number_format($consultation_revenue, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Investigation Revenue</div>
                <div class="metric-value money">Tsh {{ number_format($investigation_revenue, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Medication Revenue</div>
                <div class="metric-value money">Tsh {{ number_format($medication_revenue, 2) }}</div>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="payment-methods">
            <h3 style="margin-top: 0; color: #996f00;">Payment Methods</h3>
            @foreach($payment_methods as $method => $data)
            <div class="payment-method-row">
                <span>{{ ucfirst(str_replace('_', ' ', $method)) }} ({{ $data['count'] }} transactions)</span>
                <span><strong>Tsh {{ number_format($data['amount'], 2) }}</strong></span>
            </div>
            @endforeach
        </div>

        <!-- Transactions by Category -->
        @foreach($transactions->groupBy('category') as $category => $categoryTransactions)
        <div class="category-section">
            <div class="category-header">
                <span>{{ ucfirst(str_replace('_', ' ', $category)) }}</span>
                <span class="category-total">
                    {{ $categoryTransactions->count() }} transactions | 
                    Tsh {{ number_format($categoryTransactions->sum('amount'), 2) }}
                </span>
            </div>
            
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Description</th>
                        <th>Payment Method</th>
                        <th>Insurance</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryTransactions->sortBy('transaction_date') as $transaction)
                    <tr>
                        <td class="time">{{ $transaction->transaction_date->format('H:i A') }}</td>
                        <td>
                            @if($transaction->patient)
                                {{ $transaction->patient->first_name }} {{ $transaction->patient->last_name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</td>
                        <td class="amount">
                            @if($transaction->insurance_covered_amount > 0)
                                Tsh {{ number_format($transaction->insurance_covered_amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="amount">Tsh {{ number_format($transaction->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach

        <!-- Hourly Breakdown -->
        @if(!empty($hourly_breakdown))
        <div class="hourly-breakdown">
            <div class="category-header">
                <span>Hourly Revenue Distribution</span>
            </div>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th>Transactions</th>
                        <th>Revenue</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hourly_breakdown as $hour => $data)
                    <tr>
                        <td>{{ sprintf('%02d:00 - %02d:59', $hour, $hour) }}</td>
                        <td>{{ $data['count'] }}</td>
                        <td class="amount">Tsh {{ number_format($data['amount'], 2) }}</td>
                        <td class="amount">
                            {{ $total_revenue > 0 ? number_format(($data['amount'] / $total_revenue) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Summary Totals -->
        <div class="summary-box">
            <div class="summary-row">
                <span class="summary-label">Total Transactions Processed:</span>
                <span class="summary-amount">{{ $total_transactions }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Gross Revenue:</span>
                <span class="summary-amount">Tsh {{ number_format($total_revenue, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Patient Direct Payments:</span>
                <span class="summary-amount">Tsh {{ number_format($patient_payments, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Insurance Covered Amount:</span>
                <span class="summary-amount">Tsh {{ number_format($insurance_payments, 2) }}</span>
            </div>
            <div class="summary-row grand-total">
                <span class="summary-label">Net Daily Revenue:</span>
                <span class="summary-amount">Tsh {{ number_format($total_revenue, 2) }}</span>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="category-section">
            <div class="category-header">
                <span>Additional Statistics</span>
            </div>
            <div style="padding: 15px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                <div class="summary-row">
                    <span>Busiest Hour:</span>
                    <span>
                        @if(!empty($hourly_breakdown))
                            {{ array_keys($hourly_breakdown, max($hourly_breakdown))[0] ?? 'N/A' }}:00
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div class="summary-row">
                    <span>Most Used Payment Method:</span>
                    <span>
                        @if(!empty($payment_methods))
                            {{ ucfirst(str_replace('_', ' ', array_key_first($payment_methods))) }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div class="summary-row">
                    <span>Transactions vs Previous Day:</span>
                    <span>
                        @if(isset($previous_day_comparison))
                            {{ $previous_day_comparison > 0 ? '+' : '' }}{{ $previous_day_comparison }}%
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Daily Financial Summary Report</strong></p>
            <p>This report includes all financial transactions processed on {{ $summary_date->format('F d, Y') }}</p>
            <p>Report generated on {{ now()->format('F d, Y H:i A') }} by {{ auth()->user()->name ?? 'System' }}</p>
            <small>For detailed transaction records, please refer to individual receipts and patient statements.</small>
        </div>
    </div>
</body>
</html>
