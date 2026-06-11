<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MTUHA Monthly Report - {{ $month_name }} {{ $year }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 6mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
        }
        h3 {
            text-align: center;
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #000;
        }
        td {
            padding: 2px 4px;
            vertical-align: middle;
        }
        td.num {
            text-align: center;
        }
        .grey-row td {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }
        .section-row td {
            background-color: #d9d9d9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h3>MTUHA Monthly Report — {{ $month_name }} {{ $year }}</h3>

    <table>
    <thead>
    <tr>
        <td></td>
        <td>Jina la Kituo: {{ $facility->name ?? '' }}</td>
        <td colspan="3">Wilaya: {{ $facility->district ?? '' }}</td>
        <td colspan="3">Mkoa: {{ $facility->region ?? '' }}</td>
        <td colspan="3">Mwezi: {{ $month_name }}</td>
        <td colspan="3">Mwaka: {{ $year }}</td>
        <td colspan="6"></td>
    </tr>
    <tr class="grey-row">
        <td rowspan="2"><strong>NA</strong></td>
        <td rowspan="2">Maelezo</td>
        <td colspan="3">Umri chini ya mwezi 1</td>
        <td colspan="3">Umri mwezi 1 hadi umri chini ya mwaka 1</td>
        <td colspan="3">Umri mwaka 1 hadi umri chini ya miaka 5</td>
        <td colspan="3">Umri miaka 5 hadi umri chini ya miaka 60</td>
        <td colspan="3">Umri miaka 60 nakuendelea</td>
        <td colspan="3">Jumla Kuu</td>
    </tr>
    <tr class="grey-row">
        <td>ME</td>
        <td>MKE</td>
        <td>JUMLA</td>
        <td>ME</td>
        <td>MKE</td>
        <td>JUMLA</td>
        <td>ME</td>
        <td>MKE</td>
        <td>JUMLA</td>
        <td>ME</td>
        <td>MKE</td>
        <td>JUMLA</td>
        <td>ME</td>
        <td>MKE</td>
        <td>JUMLA</td>
        <td>ME</td>
        <td>MKE</td>
        <td>JUMLA</td>
    </tr>
    </thead>
    <tbody>
    @php
        $rowDefs = [
            ['no' => '1', 'label' => 'Wagonjwa waliohudhuria kwa mara ya kwanza mwaka huo (*) kituo chochote nchini', 'groups' => $rows['row1_groups'] ?? []],
            ['no' => '2', 'label' => 'Mahudhurio ya kwanza/ wagonjwa wapya [kwenye kituo husika kwa tatizo fulani la kiafya]', 'groups' => $rows['row2_groups'] ?? []],
            ['no' => '3', 'label' => 'Mahudhurio ya marudio', 'groups' => $rows['row3_groups'] ?? []],
        ];
    @endphp
    @foreach($rowDefs as $rd)
    <tr>
        <td>{{ $rd['no'] }}</td>
        <td>{{ $rd['label'] }}</td>
        @php $rowMale = 0; $rowFemale = 0; @endphp
        @foreach(range(0,4) as $i)
            @php
                $b = $rd['groups'][$i] ?? ['male'=>0,'female'=>0,'both'=>0];
                $rowMale += $b['male'] ?? 0;
                $rowFemale += $b['female'] ?? 0;
            @endphp
            <td class="num">{{ $b['male'] ?? 0 }}</td>
            <td class="num">{{ $b['female'] ?? 0 }}</td>
            <td class="num">{{ $b['both'] ?? 0 }}</td>
        @endforeach
        <td class="num">{{ $rowMale }}</td>
        <td class="num">{{ $rowFemale }}</td>
        <td class="num">{{ $rowMale + $rowFemale }}</td>
    </tr>
    @endforeach
    <tr>
        <td></td>
        <td>Mahudhurio ya OPD (2+3)</td>
        @php
            $r2_groups = $rows['row2_groups'] ?? [];
            $r3_groups = $rows['row3_groups'] ?? [];
            $opdMaleBuckets = 0; $opdFemaleBuckets = 0;
        @endphp
        @foreach(range(0,4) as $i)
            @php
                $g2 = $r2_groups[$i] ?? ['male'=>0,'female'=>0,'both'=>0];
                $g3 = $r3_groups[$i] ?? ['male'=>0,'female'=>0,'both'=>0];
                $opdM = ($g2['male'] ?? 0) + ($g3['male'] ?? 0);
                $opdF = ($g2['female'] ?? 0) + ($g3['female'] ?? 0);
                $opdB = ($g2['both'] ?? 0) + ($g3['both'] ?? 0);
                $opdMaleBuckets += $opdM;
                $opdFemaleBuckets += $opdF;
            @endphp
            <td class="num">{{ $opdM }}</td>
            <td class="num">{{ $opdF }}</td>
            <td class="num">{{ $opdB }}</td>
        @endforeach
        <td class="num">{{ $opdMaleBuckets }}</td>
        <td class="num">{{ $opdFemaleBuckets }}</td>
        <td class="num">{{ $opdMaleBuckets + $opdFemaleBuckets }}</td>
    </tr>
        @if(!empty($sections) && is_array($sections))
            @foreach($sections as $s)
                <tr class="section-row">
                    <td>{{ $s['index'] ?? '' }}</td>
                    <td colspan="19">{{ $s['title'] }}</td>
                </tr>

                @if(!empty($s['diagnoses']))
                    @foreach($s['diagnoses'] as $g)
                        <tr>
                            <td>{{ $g['id'] }}</td>
                            <td>{{ $g['description'] }}</td>
                            @foreach($g['buckets'] as $b)
                                <td class="num">{{ $b['male'] }}</td>
                                <td class="num">{{ $b['female'] }}</td>
                                <td class="num">{{ $b['both'] }}</td>
                            @endforeach
                            <td class="num">{{ $g['totals']['male'] }}</td>
                            <td class="num">{{ $g['totals']['female'] }}</td>
                            <td class="num">{{ $g['totals']['both'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        @else
            @foreach($diagnosisGroups as $g)
                <tr>
                    <td>{{ $g['id'] }}</td>
                    <td>{{ $g['description'] }}</td>
                    @foreach($g['buckets'] as $b)
                        <td class="num">{{ $b['Male'] }}</td>
                        <td class="num">{{ $b['Female'] }}</td>
                        <td class="num">{{ $b['Both'] }}</td>
                    @endforeach
                    <td class="num">{{ $g['totals']['Male'] }}</td>
                    <td class="num">{{ $g['totals']['Female'] }}</td>
                    <td class="num">{{ $g['totals']['Both'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
    </table>

    <div style="margin-top: 18px; font-size: 9px; line-height: 2.2;">
        <p>Jina la Mtayarishaji wa Ripoti: {{ auth()->user()?->name }}............................................................. Cheo:......................................... Wadhifa:.......................................................</p>
        <p>Tarehe ya kuandaa:............................................................... Imepitiwa na: {{ $facility?->inCharge?->name }}......................................................................</p>
        <p>Namba ya Simu ya Kituo:................................................................. Taarifa imepokelewa wilayani tarehe:.....................................................</p>
    </div>
</body>
</html>
