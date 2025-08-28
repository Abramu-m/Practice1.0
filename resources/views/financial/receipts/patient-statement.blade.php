<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Statement - {{ $statement_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .statement-container {
            max-width: 800px;
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
        .statement-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0;
            background-color: #f5f5f5;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .patient-info {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .info-value {
            flex: 1;
        }
        .period-info {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #e8f4f8;
            border: 1px solid #b8d4da;
            border-radius: 4px;
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
        .transactions-table .date {
            white-space: nowrap;
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
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        .category-section {
            margin: 25px 0;
        }
        .category-header {
            background-color: #e9ecef;
            padding: 8px 12px;
            font-weight: bold;
            border-left: 4px solid #007bff;
            margin-bottom: 5px;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .statement-container { max-width: none; }
        }
    </style>
</head>
<body>
    <div class="statement-container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">{{ config('app.clinic_name', 'Medical Facility') }}</div>
            <div class="clinic-info">
                {{ config('app.clinic_address', 'Clinic Address') }}<br>
                Phone: {{ config('app.clinic_phone', 'Phone Number') }} | Email: {{ config('app.clinic_email', 'clinic@example.com') }}
            </div>
        </div>

        <!-- Statement Title -->
        <div class="statement-title">PATIENT ACCOUNT STATEMENT</div>

        <!-- Patient Information -->
        <div class="patient-info">
            <div class="info-row">
                <div class="info-label">Patient Name:</div>
                <div class="info-value">{{ $patient->first_name }} {{ $patient->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">MR Number:</div>
                <div class="info-value">{{ $patient->mr_number }}</div>
            </div>
            @if($patient->phone)
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $patient->phone }}</div>
            </div>
            @endif
            @if($patient->email)
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $patient->email }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Statement #:</div>
                <div class="info-value">{{ $statement_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statement Date:</div>
                <div class="info-value">{{ $statement_date->format('F d, Y') }}</div>
            </div>
        </div>

        <!-- Period Information -->
        <div class="period-info">
            <strong>Statement Period:</strong>
            @if($date_from && $date_to)
                {{ \Carbon\Carbon::parse($date_from)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($date_to)->format('F d, Y') }}
            @else
                All transactions to date
            @endif
        </div>

        <!-- Transactions by Category -->
        @foreach($transactions->groupBy('category') as $category => $categoryTransactions)
        <div class="category-section">
            <div class="category-header">
                {{ ucfirst(str_replace('_', ' ', $category)) }} 
                ({{ $categoryTransactions->count() }} transactions)
            </div>
            
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th>Insurance</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryTransactions as $transaction)
                    <tr>
                        <td class="date">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->payment_reference }}</td>
                        <td class="amount">
                            @if($transaction->insurance_covered_amount > 0)
                                ${{ number_format($transaction->insurance_covered_amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="amount">${{ number_format($transaction->amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="4" style="text-align: right;">Category Total:</td>
                        <td class="amount">${{ number_format($categoryTransactions->sum('amount'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach

        <!-- Summary -->
        <div class="summary-box">
            <div class="summary-row">
                <span class="summary-label">Total Transactions:</span>
                <span class="summary-amount">{{ $transactions->count() }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Amount:</span>
                <span class="summary-amount">${{ number_format($total_amount, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Insurance Coverage:</span>
                <span class="summary-amount">${{ number_format($total_insurance, 2) }}</span>
            </div>
            <div class="summary-row grand-total">
                <span class="summary-label">Patient Payments:</span>
                <span class="summary-amount">${{ number_format($total_patient_paid, 2) }}</span>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        @if($transactions->count() > 0)
        <div class="category-section">
            <div class="category-header">Payment Method Breakdown</div>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Transactions</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions->groupBy('payment_method') as $method => $methodTransactions)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $method)) }}</td>
                        <td>{{ $methodTransactions->count() }}</td>
                        <td class="amount">${{ number_format($methodTransactions->sum('amount'), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for choosing our medical services!</strong></p>
            <p>This statement is computer-generated and shows all transactions for the specified period.</p>
            <p>For any questions regarding this statement, please contact us at {{ config('app.clinic_phone', 'Phone') }}</p>
            <small>Statement generated on {{ now()->format('F d, Y H:i A') }}</small>
        </div>
    </div>
</body>
</html>
