<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Malaria Surveillance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }
        h1 { font-size: 13px; margin: 0; text-align: center; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .header-info td { border: none; padding: 2px 5px; }
        .meta-row td { padding: 3px 5px; }
        .grand-total td { font-weight: bold; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
    </style>
</head>
<body>

    {{-- Facility + title --}}
    <table class="header-info" style="margin-bottom:4px">
        <tr>
            <td class="text-center">
                <strong style="font-size:12px">{{ $facility['name'] ?? '' }}</strong>
            </td>
        </tr>
        <tr>
            <td class="text-center">
                <strong style="font-size:12px">WEEKLY MALARIA SURVEILLANCE REPORT</strong>
            </td>
        </tr>
    </table>

    {{-- Council / Region / Week info --}}
    <table class="meta-row" style="margin-bottom:8px">
        <tr>
            <td><strong>Council:</strong> {{ $facility['district'] ?? '' }}</td>
            <td><strong>Region:</strong> {{ $facility['region'] ?? '' }}</td>
            <td><strong>Week beginning (date):</strong> {{ $week_info['start_date'] }}</td>
            <td><strong>Week Ending (date):</strong> {{ $week_info['end_date'] }}</td>
            <td><strong>Week Number:</strong> {{ $week_info['week_number'] }}</td>
        </tr>
    </table>

    {{-- Main data table --}}
    <table>
        <thead>
            <tr>
                <th rowspan="2" class="text-left" style="width:14%">Days</th>
                <th colspan="2">Number Tested with mRDT/Microscope</th>
                <th colspan="2">Number Tested Positive</th>
                <th colspan="2">Number of Clinical Malaria Cases</th>
            </tr>
            <tr>
                <th class="text-center">Under 5 Years</th>
                <th class="text-center">5 Years and Above</th>
                <th class="text-center">Under 5 Years</th>
                <th class="text-center">5 Years and Above</th>
                <th class="text-center">Under 5 Years</th>
                <th class="text-center">5 Years and Above</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $day)
            <tr>
                <td class="text-left">{{ $day['day_name'] }}</td>
                <td class="text-center">{{ $day['tested_under5'] }}</td>
                <td class="text-center">{{ $day['tested_5plus'] }}</td>
                <td class="text-center">{{ $day['positive_under5'] }}</td>
                <td class="text-center">{{ $day['positive_5plus'] }}</td>
                <td class="text-center">{{ $day['clinical_under5'] }}</td>
                <td class="text-center">{{ $day['clinical_5plus'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td class="text-left">Total</td>
                <td class="text-center">{{ $totals['tested_under5'] }}</td>
                <td class="text-center">{{ $totals['tested_5plus'] }}</td>
                <td class="text-center">{{ $totals['positive_under5'] }}</td>
                <td class="text-center">{{ $totals['positive_5plus'] }}</td>
                <td class="text-center">{{ $totals['clinical_under5'] }}</td>
                <td class="text-center">{{ $totals['clinical_5plus'] }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
