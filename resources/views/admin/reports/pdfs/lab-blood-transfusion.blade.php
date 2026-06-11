<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blood Transfusion Monthly Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }
        h1 { font-size: 13px; margin: 0; text-align: center; }
        h2 { font-size: 12px; margin: 0; text-align: center; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .header-info td { border: none; padding: 2px 6px; }

        .no-border td { border: none; }

        .meta-row td { padding: 3px 6px; }

        .grand-total td { font-weight: bold; }

        .footer-table td {
            border: none;
            padding: 8px 4px 2px 4px;
            vertical-align: top;
        }
        .dot-line {
            display: inline-block;
            border-bottom: 1px dotted #000;
            min-width: 140px;
        }
    </style>
</head>
<body>

    {{-- Facility + title --}}
    <table class="header-info" style="margin-bottom:4px">
        <tr>
            <td style="text-align:center; padding-bottom:2px">
                <strong style="font-size:12px">{{ $facility['name'] ?? '' }}</strong>
            </td>
        </tr>
        <tr>
            <td style="text-align:center">
                <strong style="font-size:12px">BLOOD TRANSFUSION MONTHLY REPORT</strong>
            </td>
        </tr>
    </table>

    {{-- Council / Region / Month / Year --}}
    <table class="meta-row" style="margin-bottom:8px">
        <tr>
            <td><strong>Council:</strong> {{ $facility['district'] ?? '' }}</td>
            <td><strong>Region:</strong> {{ $facility['region'] ?? '' }}</td>
            <td><strong>Month:</strong> {{ $month_name }}</td>
            <td><strong>Year:</strong> {{ $year }}</td>
        </tr>
    </table>

    {{-- Main data table --}}
    <table>
        <thead>
            <tr>
                <th style="text-align:left">Test</th>
                <th style="text-align:center; width:60px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
            <tr>
                <td>{{ $row->row_label }}</td>
                <td style="text-align:center">{{ $counts[$row->row_key] ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td>GRAND TOTAL</td>
                <td style="text-align:center">{{ $grand_total }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Swahili footer --}}
    <table class="footer-table" style="margin-top:16px">
        <tr>
            <td style="width:50%">
                Jina la Mtayarishaji wa Ripoti: <span class="dot-line">{{ auth()->user()?->name }}</span>
            </td>
            <td>
                Cheo: <span class="dot-line">&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Wadhifa: <span class="dot-line" style="min-width:280px">&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td>
                Tarehe ya kuandaa: <span class="dot-line">&nbsp;</span>
            </td>
            <td>
                Imepitwa na: <span class="dot-line">{{ $facility?->inCharge?->name }}</span>
            </td>
        </tr>
        <tr>
            <td>
                Namba ya Simu ya Kituo: <span class="dot-line">&nbsp;</span>
            </td>
            <td>
                Taarifa imepokelewa wilayani tarehe: <span class="dot-line" style="min-width:80px">&nbsp;</span>
            </td>
        </tr>
    </table>

</body>
</html>
