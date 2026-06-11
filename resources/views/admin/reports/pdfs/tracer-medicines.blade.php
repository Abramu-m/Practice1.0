<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fomu ya Taarifa ya Viashiria vya Upatikanaji wa Dawa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            padding: 20px 25px;
        }
        .center { text-align: center; }
        .bold   { font-weight: bold; }

        /* Main medicine table */
        .medicine-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 6px;
        }
        .medicine-table th,
        .medicine-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: middle;
        }
        .medicine-table thead th {
            font-weight: bold;
            text-align: center;
            font-size: 9.5px;
            line-height: 1.3;
        }
        .medicine-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>

    {{-- ── HEADER ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
        <tr>
            <td style="text-align:center; vertical-align:middle; width:70px;">
                @if(function_exists('imagecreatefrompng') || function_exists('imagecreatefromjpeg'))
                <img src="{{ public_path('images/tzlogo.jpg') }}" style="height:65px;" alt="">
                @endif
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <div style="font-size:13px; font-weight:bold; letter-spacing:1px;">WIZARA YA AFYA</div>
                <div style="font-size:12px; font-weight:bold; margin-top:3px;">FOMU YA TAARIFA YA VIASHIRIA VYA UPATIKANAJI WA DAWA NCHINI</div>
                <div style="font-size:12px; font-weight:bold;">(TRACER MEDICINES)</div>
                <div style="font-size:11px; font-weight:bold; margin-top:2px;">(CHANJO, DAWA MUHIMU, DAWA ZA UZAZI WA MPANGO, VIFAA TIBA NA VIFAA VYA MAABARA)</div>
            </td>
            <td style="width:70px;">&nbsp;</td>
        </tr>
    </table>

    {{-- ── FACILITY FIELDS ── --}}
    <table width="100%" cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:4px;">
        <tr>
            <td style="white-space:nowrap; width:1%;">Jina la Kituo</td>
            <td style="border-bottom:1px solid #000; padding-bottom:1px;">{{ $facility['name'] ?? '' }}</td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Aina ya Kituo</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Mmiliki wa Kituo</td>
            <td style="border-bottom:1px solid #000;"></td>
        </tr>
    </table>
    <table width="100%" cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:4px;">
        <tr>
            <td style="white-space:nowrap; width:1%;">Wilaya</td>
            <td style="border-bottom:1px solid #000; padding-bottom:1px;">{{ $facility['district'] ?? '' }}</td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Mkoa</td>
            <td style="border-bottom:1px solid #000; padding-bottom:1px;">{{ $facility['region'] ?? '' }}</td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Mwezi</td>
            <td style="border-bottom:1px solid #000; padding-bottom:1px;">{{ $month_name }}</td>
        </tr>
    </table>
    <table cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:8px;">
        <tr>
            <td style="white-space:nowrap; width:1%;">Mwaka</td>
            <td style="border-bottom:1px solid #000; min-width:80px; padding-bottom:1px;">{{ $year }}</td>
        </tr>
    </table>

    {{-- ── LEGEND ── --}}
    <table width="100%" cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:8px;">
        <tr valign="top">
            <td width="40%">
                <strong>Chagua 1</strong> = Kama kiashiria/dawa ipo.<br>
                <strong>Chagua 0</strong> = Kama kiashiria/dawa haipo.
            </td>
            <td width="60%">
                <strong>Muda wa kiashiria/dawa kutokuwepo</strong><br>
                <strong>Chagua:</strong><br>
                <strong>A</strong> = Kama haipo kwa siku zisizozidi wiki moja<br>
                <strong>B</strong> = Kama haipo kwa muda wa wiki 1-4<br>
                <strong>C</strong> = Kama haipo kwa zaidi ya mwezi mzima.
            </td>
        </tr>
    </table>

    {{-- ── MEDICINE TABLE ── --}}
    @php $items = $tracer_medicines['items'] ?? []; @endphp

    <table class="medicine-table">
        <thead>
            <tr>
                <th style="width:25px;">Na</th>
                <th style="text-align:left;">Maelezo</th>
                <th style="width:90px;">Je Kituo kinatoa<br>huduma hii?<br>1=ndio<br>0=hapana</th>
                <th style="width:90px;">Hali ya<br>kiashiria/dawa<br>ipo/haipo?<br>1=Ndio<br>0=hapana</th>
                <th style="width:90px;">Kama haipo<br>A: chini ya wiki<br>moja<br>B: wiki 1-4<br>C: zaidi ya<br>mwezi mzima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $med)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ $med['name'] }}{{ ($med['strength'] && $med['strength'] !== '—') ? ' — ' . $med['strength'] : '' }}</td>
                <td style="text-align:center;">1</td>
                <td style="text-align:center;">{{ $med['available'] ? 1 : 0 }}</td>
                <td style="text-align:center;">&nbsp;</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; color:#666; font-style:italic;">
                    Hakuna dawa za kiashiria zilizowekwa.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── SIGNATURE ── --}}
    <table width="100%" cellpadding="3" cellspacing="0" style="font-size:11px; margin-top:18px;">
        <tr>
            <td style="white-space:nowrap; width:1%;">Jina la Mtayarishaji wa Taarifa</td>
            <td style="border-bottom:1px solid #000;">{{ auth()->user()?->name }}</td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Chao.</td>
            <td style="border-bottom:1px solid #000;"></td>
        </tr>
        <tr><td colspan="4" style="height:5px;"></td></tr>
        <tr>
            <td style="white-space:nowrap; width:1%;">Wadhifa</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Imepitiwa na</td>
            <td style="border-bottom:1px solid #000;">{{ $facility?->inCharge?->name }}</td>
        </tr>
        <tr><td colspan="4" style="height:5px;"></td></tr>
        <tr>
            <td style="white-space:nowrap; width:1%;">Namba ya simu ya kituo</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="white-space:nowrap; width:1%; padding-left:10px;">Inapokelewa Wilayani tarehe</td>
            <td style="border-bottom:1px solid #000;"></td>
        </tr>
    </table>

    <div style="margin-top:15px; font-size:9px; color:#555; text-align:center;">
        Imetolewa na Mfumo wa Taarifa za Afya &mdash; {{ $facility['name'] ?? '' }} &mdash; {{ $generated_at->format('d/m/Y H:i') }}
    </div>

</body>
</html>
