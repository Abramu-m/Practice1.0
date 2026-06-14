<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — {{ $salaryPayment->payment_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #212529;
        }

        .print-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .print-toolbar button {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-print { background: #0d6efd; color: #fff; }
        .btn-print:hover { background: #0b5ed7; }
        .btn-close-tab { background: #6c757d; color: #fff; }
        .btn-close-tab:hover { background: #5c636a; }

        .payslip-wrapper {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 4px;
            padding: 30px;
            min-width: 600px;
            max-width: 800px;
            width: 100%;
        }

        table { width: 100%; border-collapse: collapse; }

        .header-table td { vertical-align: top; padding-bottom: 10px; }
        .facility-name { font-size: 18px; font-weight: bold; }
        .payslip-title { font-size: 16px; font-weight: bold; text-align: right; }

        .info-table td { padding: 3px 6px; border: 1px solid #ccc; }
        .info-table td.label { font-weight: bold; width: 25%; background: #f8f9fa; }

        .items-table { margin-top: 15px; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 6px 8px; }
        .items-table th { background: #f8f9fa; text-align: left; }
        .items-table td.amount, .items-table th.amount { text-align: right; }

        .totals-table td { padding: 4px 8px; }
        .totals-table td.label { text-align: right; font-weight: bold; }
        .totals-table td.amount { text-align: right; width: 150px; }
        .totals-table tr.net td { font-weight: bold; font-size: 14px; border-top: 2px solid #212529; }

        .signature-table { margin-top: 40px; }
        .signature-table td { padding-top: 30px; text-align: center; border-top: 1px solid #212529; }

        @media print {
            body { background: none; padding: 0; }
            .print-toolbar { display: none; }
            .payslip-wrapper { box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <button class="btn-print" onclick="window.print()">&#x1F5A8; Print</button>
        <button class="btn-close-tab" onclick="window.close()">Close</button>
    </div>

    <div class="payslip-wrapper">

        <table class="header-table">
            <tr>
                <td>
                    <div class="facility-name">{{ $facility->name ?? 'Facility' }}</div>
                    <div>{{ $facility->address ?? '' }}</div>
                    <div>{{ $facility->phone ?? '' }} {{ $facility->email ? '— ' . $facility->email : '' }}</div>
                </td>
                <td>
                    <div class="payslip-title">PAYSLIP</div>
                    <div style="text-align: right;">{{ $salaryPayment->payment_number }}</div>
                    <div style="text-align: right;">{{ $salaryPayment->period_label }}</div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td class="label">Employee</td>
                <td>{{ $salaryPayment->employee->name }}</td>
                <td class="label">Employee #</td>
                <td>{{ $salaryPayment->employee->employee_number }}</td>
            </tr>
            <tr>
                <td class="label">Job Title</td>
                <td>{{ $salaryPayment->employee->job_title ?: '—' }}</td>
                <td class="label">Department</td>
                <td>{{ $salaryPayment->employee->department ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Payment Method</td>
                <td>{{ ucwords(str_replace('_', ' ', $salaryPayment->payment_method)) }}</td>
                <td class="label">Status</td>
                <td>{{ ucfirst($salaryPayment->status) }}</td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Earnings</th>
                    <th class="amount">Amount (Tsh)</th>
                    <th>Deductions</th>
                    <th class="amount">Amount (Tsh)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $allowances = $salaryPayment->items->where('type', 'allowance')->values();
                    $deductions = $salaryPayment->items->where('type', 'deduction')->values();
                    $rows = max($allowances->count(), $deductions->count()) + 1; // +1 for basic salary row
                @endphp
                @for($i = 0; $i < $rows; $i++)
                    <tr>
                        @if($i === 0)
                            <td>Basic Salary</td>
                            <td class="amount">{{ number_format($salaryPayment->basic_salary, 2) }}</td>
                        @elseif($allowances->has($i - 1))
                            <td>{{ $allowances[$i - 1]->name }}</td>
                            <td class="amount">{{ number_format($allowances[$i - 1]->amount, 2) }}</td>
                        @else
                            <td></td>
                            <td></td>
                        @endif

                        @if($deductions->has($i))
                            <td>{{ $deductions[$i]->name }}</td>
                            <td class="amount">{{ number_format($deductions[$i]->amount, 2) }}</td>
                        @else
                            <td></td>
                            <td></td>
                        @endif
                    </tr>
                @endfor
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="label">Gross Pay (Basic + Allowances)</td>
                <td class="amount">{{ number_format($salaryPayment->basic_salary + $salaryPayment->total_allowances, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Deductions</td>
                <td class="amount">- {{ number_format($salaryPayment->total_deductions, 2) }}</td>
            </tr>
            <tr class="net">
                <td class="label">Net Pay</td>
                <td class="amount">Tsh {{ number_format($salaryPayment->net_salary, 2) }}</td>
            </tr>
        </table>

        <table class="signature-table">
            <tr>
                <td>Employee Signature</td>
                <td>Authorized Signature</td>
            </tr>
        </table>

    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>

</body>
</html>
