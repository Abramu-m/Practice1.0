<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Taarifa ya Mwezi ya Dawa za Matibabu ya Malaria</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; padding: 18px 22px; }

        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table thead th { font-weight: bold; font-size: 9px; }
        .main-table .section-header { font-size: 10px; text-align: left; }
        .main-table .drug-label { text-align: left; }
    </style>
</head>
<body>

{{-- HEADER --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
    <tr>
        <td style="width:70px; text-align:center; vertical-align:middle;">
            @if(function_exists('imagecreatefrompng') || function_exists('imagecreatefromjpeg'))
            <img src="{{ public_path('images/tzlogo.jpg') }}" style="height:60px;" alt="">
            @else
            <div style="width:60px;height:60px;border:1px solid #999;"></div>
            @endif
        </td>
        <td style="text-align:center; vertical-align:middle;">
            <div style="font-size:13px; font-weight:bold; letter-spacing:0.5px;">WIZARA YA AFYA</div>
            <div style="font-size:12px; font-weight:bold; margin-top:5px;">FOMU YA TAARIFA YA MWEZI YA DAWA ZA MATIBABU YA MALARIA</div>
        </td>
        <td style="width:70px;">&nbsp;</td>
    </tr>
</table>

{{-- FACILITY INFO --}}
<table width="100%" cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:3px;">
    <tr>
        <td style="white-space:nowrap; width:1%; border:none;">Jina la Kituo:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">
            <span style="text-decoration:underline;">{{ $facility['name'] ?? '' }}</span>
        </td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">Wilaya:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">
            <span style="text-decoration:underline;">{{ $facility['district'] ?? '' }}</span>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0" style="font-size:11px; margin-bottom:12px;">
    <tr>
        <td style="white-space:nowrap; width:1%; border:none;">Mkoa:</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">
            <span style="text-decoration:underline;">{{ $facility['region'] ?? '' }}</span>
        </td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">Mwezi:</td>
        <td style="border-bottom:1px solid #000; min-width:80px; border-top:none; border-left:none; border-right:none; padding-left:4px;">{{ $month_name }}</td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">Mwaka:</td>
        <td style="border-bottom:1px solid #000; min-width:60px; border-top:none; border-left:none; border-right:none; padding-left:4px;">{{ $year }}</td>
    </tr>
</table>

{{-- TABLE 1: ALu DOSES --}}
<table class="main-table">
    <thead>
        <tr>
            <th colspan="8" class="section-header">JUMLA YA VIDONGE VYA DAWA MSETO YA MALARIA (ALu) VILIVYOTOLEWA</th>
        </tr>
        <tr>
            <th style="width:25px">Na</th>
            <th style="width:140px">Dawa</th>
            <th style="width:80px">Kipimo cha kugawa</th>
            <th>Miaka 0-3<br>(0-15kg)</th>
            <th>Miaka 3.1-8<br>(15.1-25kg)</th>
            <th>Miaka 8.1-12<br>(25.1-35kg)</th>
            <th>Miaka 12 na zaidi<br>(35kg+)</th>
            <th style="width:45px">Jumla<br>(a+b+c+d)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($alu_rows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="drug-label">{{ $row['label'] }}</td>
            <td>{{ $row['unit'] }}</td>
            <td>{{ $row['a'] }}</td>
            <td>{{ $row['b'] }}</td>
            <td>{{ $row['c'] }}</td>
            <td>{{ $row['d'] }}</td>
            <td>{{ $row['total'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- TABLE 2: ARTESUNATE (static placeholder structure) --}}
<table class="main-table" style="margin-top:14px;">
    <thead>
        <tr>
            <th colspan="8" class="section-header">KIASI KILICHOTOLEWA CHA DAWA YA SINDANO YA ARTESUNATE</th>
        </tr>
        <tr>
            <th style="width:25px">Na</th>
            <th style="width:140px">Dawa</th>
            <th style="width:80px">Kipimo cha kugawa</th>
            <th>Chini au sawa ya kg 25</th>
            <th>Uzito wa 26-50kg</th>
            <th>Uzito wa 51-70kg</th>
            <th>Uzito wa 76-100kg</th>
            <th style="width:45px">Jumla</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>5a</td>
            <td class="drug-label">Idadi ya vichupa vilivyotumika (Initiated treatment and admitted)</td>
            <td>Sindano</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        <tr>
            <td>5b</td>
            <td class="drug-label">Idadi ya vichupa vilivyotumika (Initiated treatment and referred out)</td>
            <td>Sindano</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        <tr>
            <td>5c</td>
            <td class="drug-label">Idadi ya vichupa vilivyotumika (Referred in and treated)</td>
            <td>Sindano</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
    </tbody>
</table>

{{-- SIGNATURE FOOTER --}}
<table width="100%" cellpadding="3" cellspacing="0" style="font-size:11px; margin-top:22px;">
    <tr>
        <td style="white-space:nowrap; width:1%; border:none;">Jina la Mtayarishaji wa Taarifa</td>
        <td style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">{{ auth()->user()?->name }}</td>
        <td style="white-space:nowrap; width:1%; border:none; padding-left:12px;">Kada</td>
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
        <td style="white-space:nowrap; border:none;">Namba ya simu ya kituo</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Tarehe ya kupokelewa Wilayani/Kutumwa kwenye mfumo</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
</table>

</body>
</html>
