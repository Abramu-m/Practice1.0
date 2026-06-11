<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Taarifa ya Mwezi ya Kutolea Dawa</title>
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
            <div style="font-size:12px; font-weight:bold; margin-top:5px;">FOMU YA TAARIFA YA MWEZI YA KUTOLEA DAWA</div>
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

{{-- MAIN DATA TABLE --}}
<table class="main-table">
    <thead>
        <tr>
            <th rowspan="2" style="width:30px">Na</th>
            <th rowspan="2" style="width:170px">Dawa</th>
            <th rowspan="2" style="width:80px">Kipimo cha kugawa</th>
            <th colspan="3">Kiasi cha dawa kilichotolewa kwa wagonjwa</th>
            <th rowspan="2" style="width:45px">Jumla</th>
        </tr>
        <tr>
            <th>Umri chini ya miaka 5</th>
            <th>Umri miaka 5 hadi 59</th>
            <th>Umri miaka 60 na zaidi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dispensing_rows as $row)
        <tr>
            @if($row['row_no_rowspan'] > 0)
                <td rowspan="{{ $row['row_no_rowspan'] }}">{{ $row['row_no'] }}</td>
            @endif
            @if($row['drug_rowspan'] > 0)
                <td rowspan="{{ $row['drug_rowspan'] }}" class="drug-label">{{ $row['drug_label'] }}</td>
            @endif
            <td>{{ $row['unit_label'] }}</td>
            <td>{{ $row['under_5'] ?? '' }}</td>
            <td>{{ $row['5_to_59'] ?? '' }}</td>
            <td>{{ $row['60_plus'] ?? '' }}</td>
            <td>{{ $row['total'] ?? '' }}</td>
        </tr>
        @endforeach
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
        <td style="white-space:nowrap; border:none;">Inapitiwa na</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;">{{ $facility?->inCharge?->name }}</td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Namba ya simu ya kituo</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
    <tr><td colspan="4" style="height:5px; border:none;"></td></tr>
    <tr>
        <td style="white-space:nowrap; border:none;">Tarehe ya kupokelewa Wilayani</td>
        <td colspan="3" style="border-bottom:1px solid #000; border-top:none; border-left:none; border-right:none;"></td>
    </tr>
</table>

</body>
</html>
