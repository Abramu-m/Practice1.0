<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - {{ $receipt_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 10px;
            font-size: 11px;
            line-height: 1.2;
            width: 58mm; /* For 58mm thermal printer */
        }
        .receipt-container {
            width: 100%;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .clinic-name {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        .item-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-price {
            text-align: right;
            margin-left: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin: 3px 0;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="clinic-name">{{ $clinic_info['name'] }}</div>
        <div class="center">{{ $clinic_info['address'] }}</div>
        <div class="center">{{ $clinic_info['phone'] }}</div>
        
        <div class="separator"></div>
        
        <!-- Receipt Info -->
        <div class="center bold">PAYMENT RECEIPT</div>
        <div class="separator"></div>
        
        <div>Receipt: {{ $receipt_number }}</div>
        <div>Date: {{ $receipt_date->format('M d, Y H:i') }}</div>
        @if($patient)
        <div>Patient: {{ $patient->first_name }} {{ $patient->last_name }}</div>
        <div>ID: {{ $patient->patient_number ?? $patient->id }}</div>
        @endif
        <div>Cashier: {{ $issued_by }}</div>
        
        <div class="separator"></div>
        
        <!-- Items -->
        @foreach($service_details['items'] as $item)
        <div class="item-row">
            <span class="item-name">{{ $item['name'] }}</span>
        </div>
        <div class="item-row">
            <span>{{ $item['quantity'] }} x ${{ number_format($item['unit_price'], 2) }}</span>
            <span class="item-price">${{ number_format($item['total_price'], 2) }}</span>
        </div>
        @endforeach
        
        <div class="separator"></div>
        
        <!-- Totals -->
        <div class="total-row">
            <span>SUBTOTAL:</span>
            <span>${{ number_format($payment_details['total_amount'], 2) }}</span>
        </div>
        
        @if($payment_details['insurance_covered'] > 0)
        <div class="total-row">
            <span>INSURANCE:</span>
            <span>-${{ number_format($payment_details['insurance_covered'], 2) }}</span>
        </div>
        @endif
        
        <div class="total-row">
            <span>TOTAL PAID:</span>
            <span>${{ number_format($payment_details['patient_paid'], 2) }}</span>
        </div>
        
        <div class="separator"></div>
        
        <!-- Payment Info -->
        <div>Payment: {{ $payment_details['payment_method'] }}</div>
        @if($payment_details['payment_reference'])
        <div>Ref: {{ $payment_details['payment_reference'] }}</div>
        @endif
        <div>Status: {{ $payment_details['payment_status'] }}</div>
        
        <div class="separator"></div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="bold">Thank You!</div>
            <div>Please retain for your records</div>
            <div style="margin-top: 10px; font-size: 9px;">
                {{ now()->format('M d, Y H:i A') }}
            </div>
        </div>
    </div>
</body>
</html>
