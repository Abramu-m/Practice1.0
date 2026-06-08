@extends('layouts.app_main_layout')

@section('page_title', 'MTUHA Monthly Report')

@section('main_content')
<div class="container">
    <h3>MTUHA Monthly Report</h3>
    <p>Facility: <strong>{{ $facility->name ?? '' }}</strong></p>
    <p>Region: {{ $facility->region ?? '' }} | District: {{ $facility->district ?? '' }}</p>
    <p>Month: {{ $month_name }} {{ $year }}</p>
    <div class="mb-3">
        <form method="get" action="{{ route('reports.mtuha.month') }}" class="form-inline">
            <input type="hidden" name="mwaka" value="{{ $year }}" />
            <input type="hidden" name="mwezi" value="{{ $month }}" />
            <label class="me-2"><input type="checkbox" name="nocache" value="1" /> Bypass cache</label>
            <button type="submit" name="pdf" value="1" class="btn btn-sm btn-primary">Download PDF</button>
            <a href="{{ route('reports.mtuha.month', ['mwaka' => $year, 'mwezi' => $month, 'nocache' => 1]) }}" class="btn btn-sm btn-secondary ms-2">Refresh (bypass cache)</a>
            <a href="{{ route('reports.mtuha.select', ['mwaka' => $year, 'mwezi' => $month]) }}" class="btn btn-sm btn-outline-secondary ms-2">Change Month/Year</a>
        </form>
    </div>
    <table class="table table-bordered table-sm">
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
    <tr style="background-color: grey;">
        <td rowspan="2"><strong>NA</strong></td>
        <td rowspan="2">Maelezo</td>
        <td colspan="3">Umri chini ya mwezi 1</td>
        <td colspan="3">Umri mwezi 1 hadi umri chini ya mwaka 1</td>
        <td colspan="3">Umri mwaka 1 hadi umri chini ya miaka 5</td>
        <td colspan="3">Umri miaka 5 hadi umri chini ya miaka 60</td>
        <td colspan="3">Umri miaka 60 nakuendelea</td>
        <td colspan="3">Jumla Kuu</td>
    </tr>

    <tr style=" background-color: grey;">
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
            <td>{{ $b['male'] ?? 0 }}</td>
            <td>{{ $b['female'] ?? 0 }}</td>
            <td>{{ $b['both'] ?? 0 }}</td>
        @endforeach
        <td>{{ $rowMale }}</td>
        <td>{{ $rowFemale }}</td>
        <td>{{ $rowMale + $rowFemale }}</td>
    </tr>
    @endforeach
    <tr>
        <td></td>
        <td>Mahudhurio ya OPD (2+3)</td>
        @php
            // OPD should be sum of row2 (first-ever at facility) and row3 (repeat) per-bucket to avoid double-counting
            $r2_groups = $rows['row2_groups'] ?? [];
            $r3_groups = $rows['row3_groups'] ?? [];
        @endphp

        @foreach(range(0,4) as $i)
            @php
                $g2 = $r2_groups[$i] ?? ['male'=>0,'female'=>0,'both'=>0];
                $g3 = $r3_groups[$i] ?? ['male'=>0,'female'=>0,'both'=>0];
                $opdM = ($g2['male'] ?? 0) + ($g3['male'] ?? 0);
                $opdF = ($g2['female'] ?? 0) + ($g3['female'] ?? 0);
                $opdB = ($g2['both'] ?? 0) + ($g3['both'] ?? 0);
            @endphp
            <td>{{ $opdM }}</td>
            <td>{{ $opdF }}</td>
            <td>{{ $opdB }}</td>
        @endforeach

    @php
        $opdMaleBuckets = 0; $opdFemaleBuckets = 0;
        foreach(range(0,4) as $i) {
            $g2 = $r2_groups[$i] ?? ['male'=>0,'female'=>0,'both'=>0];
            $g3 = $r3_groups[$i] ?? ['male'=>0,'female'=>0,'both'=>0];
            $opdMaleBuckets += (($g2['male'] ?? 0) + ($g3['male'] ?? 0));
            $opdFemaleBuckets += (($g2['female'] ?? 0) + ($g3['female'] ?? 0));
        }
        $aa6 = $opdMaleBuckets;
        $bb6 = $opdFemaleBuckets;
    @endphp
    <td>{{ $aa6 }}</td>
    <td>{{ $bb6 }}</td>
    <td>{{ $aa6 + $bb6 }}</td>
    </tr>
        @if(!empty($sections) && is_array($sections))
            @foreach($sections as $s)
                <tr style="background-color: grey;">
                    <td style="width:40px; font-weight:600;">{{ $s['index'] ?? '' }}</td>
                    <td style="font-weight:600;" colspan="19">{{ $s['title'] }}</td>
                </tr>

                @if(!empty($s['diagnoses']))
                    @foreach($s['diagnoses'] as $g)
                        <tr>
                            <td>{{ $g['id'] }}</td>
                            <td>{{ $g['description'] }}</td>
                                        @foreach($g['buckets'] as $b)
                                            <td>{{ $b['male'] }}</td>
                                            <td>{{ $b['female'] }}</td>
                                            <td>{{ $b['both'] }}</td>
                                        @endforeach
                                        <td>{{ $g['totals']['male'] }}</td>
                                        <td>{{ $g['totals']['female'] }}</td>
                                        <td>{{ $g['totals']['both'] }}</td>
                        </tr>
                    @endforeach
                @else
                    {{-- Section has no diagnoses for this installation/data set --}}
                @endif
            @endforeach
        @else
            {{-- Fallback: render flat diagnosisGroups if sections missing --}}
            @foreach($diagnosisGroups as $g)
                <tr>
                    <td>{{ $g['id'] }}</td>
                    <td>{{ $g['description'] }}</td>
                    @foreach($g['buckets'] as $b)
                        <td>{{ $b['Male'] }}</td>
                        <td>{{ $b['Female'] }}</td>
                        <td>{{ $b['Both'] }}</td>
                    @endforeach
                    <td>{{ $g['totals']['Male'] }}</td>
                    <td>{{ $g['totals']['Female'] }}</td>
                    <td>{{ $g['totals']['Both'] }}</td>
                </tr>
            @endforeach
        @endif
    
        </tbody>
    </table>
</div>
@endsection
