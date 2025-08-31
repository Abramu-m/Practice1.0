@extends('layouts.app_main_layout')

@section('page_title', 'MTUHA Monthly Report')

@section('main_content')
<div class="container">
    <h3>MTUHA Monthly Report</h3>
    <p>Facility: <strong>{{ $hospital->description ?? '' }}</strong></p>
    <p>Region: {{ $hospital->mkoa ?? '' }} | District: {{ $hospital->wilaya ?? '' }}</p>
    <p>Month: {{ $month_name }} {{ $year }}</p>
    <div class="mb-3">
        <form method="get" action="{{ route('reports.mtuha.month') }}" class="form-inline">
            <input type="hidden" name="mwaka" value="{{ $year }}" />
            <input type="hidden" name="mwezi" value="{{ $month }}" />
            <label class="me-2"><input type="checkbox" name="nocache" value="1" /> Bypass cache</label>
            <button type="submit" name="pdf" value="1" class="btn btn-sm btn-primary">Download PDF</button>
            <a href="{{ route('reports.mtuha.month', ['mwaka' => $year, 'mwezi' => $month, 'nocache' => 1]) }}" class="btn btn-sm btn-secondary ms-2">Refresh (bypass cache)</a>
        </form>
    </div>
    <table class="table table-bordered table-sm">
    <thead>
    <tr>
        <td></td>
        <td>Jina la Kituo: {{ $hospital->description ?? '' }}</td>
        <td colspan="3">Wilaya: {{ $hospital->wilaya ?? '' }}</td>
        <td colspan="3">Mkoa: {{ $hospital->mkoa ?? '' }}</td>
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
    <tr>
        <td>&nbsp;1</td>
        <td>Wagonjwa waliohudhuria kwa mara ya kwanza mwaka huo (*) kituo chochote nchini</td>
        @php
            $r1 = $rows['row1'] ?? ['male'=>0,'female'=>0,'both'=>0];
        @endphp
    {{-- row-specific base counts removed to keep columns aligned with age-group buckets --}}

        {{-- For subsequent buckets we use the bucketVisits totals as approximation --}}
        @php $bv = $bucketVisits ?? []; @endphp
        @foreach(range(0,4) as $i)
            @php $b = $bv[$i] ?? ['male'=>0,'female'=>0,'both'=>0]; @endphp
            <td>{{ $b['male'] ?? 0 }}</td>
            <td>{{ $b['female'] ?? 0 }}</td>
            <td>{{ $b['both'] ?? 0 }}</td>
        @endforeach

    @php
        // sum only the first 5 buckets we displayed above
        $bvMale = 0; $bvFemale = 0;
        foreach(range(0,4) as $i) {
            $bvMale += $bv[$i]['male'] ?? 0;
            $bvFemale += $bv[$i]['female'] ?? 0;
        }
    $sumMale = $bvMale;
    $sumFemale = $bvFemale;
    @endphp
    <td>{{ $sumMale }}</td>
    <td>{{ $sumFemale }}</td>
    <td>{{ $sumMale + $sumFemale }}</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Mahudhurio ya kwanza/ wagonjwa wapya [kwenye kituo husika kwa tatizo fulani la kiafya]</td>
    @php $r2 = $rows['row2'] ?? ['male'=>0,'female'=>0,'both'=>0]; @endphp
    {{-- row-specific base counts removed to keep columns aligned with age-group buckets --}}

        @foreach(range(0,4) as $i)
            @php $b = ($bucketConsultations[$i] ?? ['male'=>0,'female'=>0,'both'=>0]); @endphp
            <td>{{ $b['male'] ?? 0 }}</td>
            <td>{{ $b['female'] ?? 0 }}</td>
            <td>{{ $b['both'] ?? 0 }}</td>
        @endforeach

    @php
        $cMale = 0; $cFemale = 0;
        foreach(range(0,4) as $i) {
            $cMale += ($bucketConsultations[$i]['male'] ?? 0);
            $cFemale += ($bucketConsultations[$i]['female'] ?? 0);
        }
    $dmSum = $cMale;
    $dfSum = $cFemale;
    @endphp
    <td>{{ $dmSum }}</td>
    <td>{{ $dfSum }}</td>
    <td>{{ $dmSum + $dfSum }}</td>
    </tr>
    <tr>
        <td>3</td>
        <td>Mahudhurio ya marudio</td>
    @php $r3 = $rows['row3'] ?? ['male'=>0,'female'=>0,'both'=>0]; @endphp
    {{-- row-specific base counts removed to keep columns aligned with age-group buckets --}}

        {{-- repeat: show bucket-level visits as approximation --}}
        @foreach(range(0,4) as $i)
            @php $b = $bv[$i] ?? ['male'=>0,'female'=>0,'both'=>0]; @endphp
            <td>{{ $b['male'] ?? 0 }}</td>
            <td>{{ $b['female'] ?? 0 }}</td>
            <td>{{ $b['both'] ?? 0 }}</td>
        @endforeach

    @php
        $bvMale2 = 0; $bvFemale2 = 0;
        foreach(range(0,4) as $i) {
            $bvMale2 += ($bv[$i]['male'] ?? 0);
            $bvFemale2 += ($bv[$i]['female'] ?? 0);
        }
    $cmSum = $bvMale2;
    $cfSum = $bvFemale2;
    @endphp
    <td>{{ $cmSum }}</td>
    <td>{{ $cfSum }}</td>
    <td>{{ $cmSum + $cfSum }}</td>
    </tr>
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
                <tr style="background-color: rgb(201, 15, 15);">
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
