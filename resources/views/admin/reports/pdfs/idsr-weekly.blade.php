<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>IDSR Form 3B — Week {{ $week ?? '' }} {{ $year ?? '' }}</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 8pt; margin: 0; color: #111; }

    /* Header meta table */
    .header-block { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .header-block td { padding: 1px 4px; font-size: 8pt; }
    .form-title { text-align: center; font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
    .form-subtitle { text-align: center; font-size: 8pt; margin-bottom: 4px; }

    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 7.5pt; }
    .meta-table td { border: 1px solid #999; padding: 2px 5px; }
    .meta-table .lbl { font-weight: bold; background: #f0f0f0; width: 14%; }

    /* Main report table */
    table.report { width: 100%; border-collapse: collapse; font-size: 7pt; page-break-inside: avoid; }
    table.report th { background: #1a3a5c; color: #fff; border: 1px solid #ccc; padding: 2px 3px; text-align: center; vertical-align: middle; }
    table.report th.sub1 { background: #1f5c99; }
    table.report th.sub2 { background: #2878c8; }
    table.report td { border: 1px solid #ccc; padding: 2px 3px; text-align: center; vertical-align: middle; }
    table.report td.disease-name { text-align: left; font-size: 6.5pt; }
    table.report tr:nth-child(even) td { background: #f7f9fb; }

    .note { font-size: 6.5pt; color: #555; margin-top: 4px; }
    .footer { font-size: 6pt; color: #777; margin-top: 6px; text-align: center; }
</style>
</head>
<body>

<div class="form-title">FORM 3 B: WEEKLY REPORTED CASES / DEATHS AT FACILITY LEVELS</div>
<div class="form-subtitle">Integrated Disease Surveillance and Response (IDSR)</div>

<table class="meta-table">
    <tr>
        <td class="lbl">Region:</td>
        <td>{{ $facility['region'] ?? '—' }}</td>
        <td class="lbl">District:</td>
        <td>{{ $facility['district'] ?? '—' }}</td>
        <td class="lbl">Facility:</td>
        <td>{{ $facility['name'] ?? '—' }}</td>
        <td class="lbl">Week No.:</td>
        <td>{{ $week_info['week_number'] ?? $week ?? '—' }} / {{ $year ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Week Dates:</td>
        <td colspan="7">
            {{ isset($week_info['start_date']) ? \Carbon\Carbon::parse($week_info['start_date'])->format('d M Y') : '—' }}
            &nbsp;—&nbsp;
            {{ isset($week_info['end_date']) ? \Carbon\Carbon::parse($week_info['end_date'])->format('d M Y') : '—' }}
        </td>
    </tr>
</table>

<table class="report">
    <thead>
        <tr>
            <th rowspan="3" style="width:20px">#</th>
            <th rowspan="3" style="width:150px;text-align:left">Disease / Condition</th>
            <th colspan="9">CASES THIS WEEK</th>
            <th colspan="9">DEATHS THIS WEEK</th>
            <th colspan="6">CUMULATIVE (Jan 1–Wk {{ $week_info['week_number'] ?? $week ?? '' }})</th>
        </tr>
        <tr>
            <th class="sub1" colspan="3">&lt;5 yrs</th>
            <th class="sub1" colspan="3">5+ yrs</th>
            <th class="sub1" colspan="3">Total</th>
            <th class="sub1" colspan="3">&lt;5 yrs</th>
            <th class="sub1" colspan="3">5+ yrs</th>
            <th class="sub1" colspan="3">Total</th>
            <th class="sub1" colspan="3">Cases</th>
            <th class="sub1" colspan="3">Deaths</th>
        </tr>
        <tr>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
            <th class="sub2">M</th><th class="sub2">F</th><th class="sub2">T</th>
        </tr>
    </thead>
    <tbody>
        @php $sn = 1; @endphp
        @foreach ($diseases as $id => $d)
        @php
            $wc = $d['weekly_cases'];
            $cc = $d['cumulative_cases'];
        @endphp
        <tr>
            <td>{{ $sn++ }}</td>
            <td class="disease-name">{{ $d['name'] }}</td>
            <td>{{ $wc['u5_m'] ?: '' }}</td>
            <td>{{ $wc['u5_f'] ?: '' }}</td>
            <td>{{ $wc['u5_t'] ?: '' }}</td>
            <td>{{ $wc['5p_m'] ?: '' }}</td>
            <td>{{ $wc['5p_f'] ?: '' }}</td>
            <td>{{ $wc['5p_t'] ?: '' }}</td>
            <td>{{ $wc['tot_m'] ?: '' }}</td>
            <td>{{ $wc['tot_f'] ?: '' }}</td>
            <td>{{ $wc['tot_t'] ?: '' }}</td>
            <td>—</td><td>—</td><td>—</td>
            <td>—</td><td>—</td><td>—</td>
            <td>—</td><td>—</td><td>—</td>
            <td>{{ $cc['m'] ?: '' }}</td>
            <td>{{ $cc['f'] ?: '' }}</td>
            <td>{{ $cc['t'] ?: '' }}</td>
            <td>—</td><td>—</td><td>—</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="note">* Death columns show — because in-facility death outcome tracking is not enabled in this system.</div>
<div class="footer">Generated: {{ $generated_at->format('d M Y H:i') }} &nbsp;|&nbsp; {{ $facility['name'] ?? '' }}</div>

</body>
</html>
