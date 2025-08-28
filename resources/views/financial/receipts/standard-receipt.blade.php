<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .clinic-info {
            font-size: 11px;
            color: #666;
        }
        .receipt-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .receipt-details {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .detail-row {
            display: table-row;
        }
        .detail-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 5px 0;
            vertical-align: top;
        }
        .detail-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }
        .service-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .service-table th,
        .service-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .service-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .service-table .amount {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        .total-label {
            font-weight: bold;
        }
        .total-amount {
            font-weight: bold;
        }
        .grand-total {
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        .payment-info {
            background-color: #e8f4f8;
            border: 1px solid #b8d4da;
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .payment-status {
            font-weight: bold;
            color: #2c5530;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .receipt-container { border: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">{{ $clinic_info['name'] }}</div>
            <div class="clinic-info">
                {{ $clinic_info['address'] }}<br>
                Phone: {{ $clinic_info['phone'] }} | Email: {{ $clinic_info['email'] }}<br>
                @if($clinic_info['license'])
                    License: {{ $clinic_info['license'] }} | 
                @endif
                @if($clinic_info['tax_id'])
                    Tax ID: {{ $clinic_info['tax_id'] }}
                @endif
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">PAYMENT RECEIPT</div>

        <!-- Receipt Details -->
        <div class="receipt-details">
            <div class="detail-row">
                <div class="detail-label">Receipt No:</div>
                <div class="detail-value">{{ $receipt_number }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date:</div>
                <div class="detail-value">{{ $receipt_date->format('F d, Y H:i A') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Patient:</div>
                <div class="detail-value">
                    @if($patient)
                        {{ $patient->first_name }} {{ $patient->last_name }}<br>
                        <small>ID: {{ $patient->patient_number ?? $patient->id }}</small>
                    @else
                        Walk-in Patient
                    @endif
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Service Date:</div>
                <div class="detail-value">{{ $service_details['service_date']->format('F d, Y') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Issued By:</div>
                <div class="detail-value">{{ $issued_by }}</div>
            </div>
        </div>

        <!-- Service Details Table -->
        <table class="service-table">
            <thead>
                <tr>
                    <th>Service/Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($service_details['items'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td class="amount">${{ number_format($item['unit_price'], 2) }}</td>
                    <td class="amount">${{ number_format($item['total_price'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Payment Information -->
        <div class="payment-info">
            <strong>Payment Information:</strong><br>
            Method: {{ $payment_details['payment_method'] }}<br>
            @if($payment_details['payment_reference'])
                Reference: {{ $payment_details['payment_reference'] }}<br>
            @endif
            Status: <span class="payment-status">{{ $payment_details['payment_status'] }}</span>
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-amount">${{ number_format($payment_details['total_amount'], 2) }}</span>
            </div>
            @if($payment_details['insurance_covered'] > 0)
            <div class="total-row">
                <span class="total-label">Insurance Coverage:</span>
                <span class="total-amount">-${{ number_format($payment_details['insurance_covered'], 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span class="total-label">Amount Paid:</span>
                <span class="total-amount">${{ number_format($payment_details['patient_paid'], 2) }}</span>
            </div>
            @if($payment_details['balance_due'] > 0)
            <div class="total-row">
                <span class="total-label">Balance Due:</span>
                <span class="total-amount">${{ number_format($payment_details['balance_due'], 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for choosing our medical services!</strong></p>
            <p>This is a computer-generated receipt. Please retain for your records.</p>
            <p>For inquiries, please contact us at {{ $clinic_info['phone'] }} or {{ $clinic_info['email'] }}</p>
            <small>Generated on {{ now()->format('F d, Y H:i A') }}</small>
        </div>
    </div>
</body>
</html>
