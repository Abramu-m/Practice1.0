<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fomu ya Taarifa ya Vipimo vya Malaria</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; padding: 18px 22px; }

        .main-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table thead th { font-weight: bold; font-size: 10px; }
        .main-table .kipimo-label { text-align: left; font-weight: bold; }
        .main-table .result-label { text-align: left; }
        .main-table .total-row td { font-weight: bold; }

        .field-line { border-bottom: 1px solid #000; display: inline-block; min-width: 60px; }
    </style>
</head>
<body>

{{-- ── HEADER ── --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
    <tr>
        <td style="width:70px; text-align:center; vertical-align:middle;">
            @if(function_exists('imagecreatefrompng') || function_exists('imagecreatefromjpeg'))
            <img src="{{ public_path('images/tzlogo.jpg') }}" style="height:65px;" alt="">
            @else
            <div style="width:65px;height:65px;border:1px solid #999;"></div>
            @endif
        </td>
        <td style="text-align:center; vertical-align:middle;">
            <div style="font-size:13px; font-weight:bold; letter-spacing:0.5px;">WIZARA YA AFYA, MAENDELEO YA JAMII, WAZEE NA WATOTO</div>
            <div style="font-size:12px; font-weight:bold; margin-top:5px;">FOMU YA TAARIFA YA VIPIMO VYA MALARIA</div>
        </td>
        <td style="width:70px;">&nbsp;</td>
    </tr>
</table>

{{-- ── FACILITY INFO ── --}}
<table width="100%" cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:3px;">
    <tr>
        <td style="white-space:nowrap; width:1%; border:none;">JINA LA KITUO:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">
            <span style="text-decoration:underline;">{{ $facility['name'] ?? '' }}</span>
        </td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">WILAYA:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">
            <span style="text-decoration:underline;">{{ $facility['district'] ?? '' }}</span>
        </td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">MKOA:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">
            <span style="text-decoration:underline;">{{ $facility['region'] ?? '' }}</span>
        </td>
    </tr>
</table>
<table cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:12px;">
    <tr>
        <td style="white-space:nowrap; border:none;">MWEZI</td>
        <td style="border-bottom:1px solid #000; min-width:40px; border-top:none; border-left:none; border-right:none; padding-left:4px;">{{ $month }}</td>
        <td style="white-space:nowrap; border:none; padding-left:16px;">MWAKA:</td>
        <td style="border-bottom:1px solid #000; min-width:60px; border-top:none; border-left:none; border-right:none; padding-left:4px;">{{ $year }}</td>
    </tr>
</table>

{{-- ── MAIN DATA TABLE ── --}}
@php
    $agKeys    = $age_group_keys;   // ['under_1m','1_to_11m','1_to_4y','5y_plus']
    $agLabels  = $age_group_labels; // key => Swahili label
    $c         = $counts;           // all row counts
    $rt        = $row_totals;       // row-level male/female/total
    $gt        = $grand_total;      // grand total
    $gat       = $grand_age_total;  // per age-group grand

    $rows = [
        ['key' => 'mrdt_negative', 'group' => 'mRDT', 'label' => 'Negative', 'groupspan' => true],
        ['key' => 'mrdt_positive', 'group' => 'mRDT', 'label' => 'Positive',  'groupspan' => false],
        ['key' => 'bs_no_mps',     'group' => 'BS',   'label' => 'No MPS',    'groupspan' => true],
        ['key' => 'bs_mps_seen',   'group' => 'BS',   'label' => 'MPS seen',  'groupspan' => false],
    ];
@endphp

<table class="main-table">
    <thead>
        <tr>
            <th colspan="2" rowspan="2" style="text-align:center;">KIPIMO</th>
            <th colspan="2">Umri chini ya mwezi 1</th>
            <th colspan="2">Umri mwezi 1 hadi 11</th>
            <th colspan="2">Umri mwaka 1 hadi miaka 4</th>
            <th colspan="2">Umri miaka 5 na zaidi</th>
            <th colspan="3">Jumla</th>
        </tr>
        <tr>
            <th>Me</th><th>Ke</th>
            <th>Me</th><th>Ke</th>
            <th>Me</th><th>Ke</th>
            <th>Me</th><th>Ke</th>
            <th>Me</th><th>Ke</th><th>Jumla</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            @if($row['groupspan'])
            <td class="kipimo-label" rowspan="2">{{ $row['group'] }}</td>
            @endif
            <td class="result-label">{{ $row['label'] }}</td>
            @foreach($agKeys as $ag)
            <td>{{ $c[$row['key']][$ag]['male']   ?? 0 }}</td>
            <td>{{ $c[$row['key']][$ag]['female'] ?? 0 }}</td>
            @endforeach
            <td>{{ $rt[$row['key']]['male']   ?? 0 }}</td>
            <td>{{ $rt[$row['key']]['female'] ?? 0 }}</td>
            <td>{{ $rt[$row['key']]['total']  ?? 0 }}</td>
        </tr>
        @endforeach
        {{-- Grand total row --}}
        <tr class="total-row">
            <td colspan="2">Jumla Ya Vipimo Vyote</td>
            @foreach($agKeys as $ag)
            <td>{{ $gat[$ag]['male']   ?? 0 }}</td>
            <td>{{ $gat[$ag]['female'] ?? 0 }}</td>
            @endforeach
            <td>{{ $gt['male']   }}</td>
            <td>{{ $gt['female'] }}</td>
            <td>{{ $gt['total']  }}</td>
        </tr>
    </tbody>
</table>

{{-- ── SIGNATURE FOOTER ── --}}
<table width="100%" cellpadding="3" cellspacing="0" style="font-size:11px; margin-top:22px;">
    <tr>
        <td style="white-space:nowrap; width:1%; border:none;">Jina La Mtayarishaji wa Taarifa:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">{{ auth()->user()?->name }}</td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">Kada.</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Saini</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Imepitiwa na</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">{{ $facility?->inCharge?->name }}</td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Namba ya Simu ya Kituo</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Tarehe ya kupokelewa Wilayani</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
</table>

<div style="margin-top:14px; font-size:9px; color:#555; text-align:center;">
    Imetolewa na Mfumo wa Taarifa za Afya &mdash; {{ $facility['name'] ?? '' }} &mdash; {{ $generated_at->format('d/m/Y H:i') }}
</div>

</body>
</html>
