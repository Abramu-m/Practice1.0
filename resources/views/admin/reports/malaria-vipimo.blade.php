@extends('layouts.app_main_layout')

@section('page_title', 'Malaria Vipimo Monthly Report')

@section('main_content')
<div class="container-fluid">

    {{-- Filter bar --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label mb-1">Month</label>
                    <select name="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">View</button>
                    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                    @if(!$mrdt_id || !$bs_id)
                    <a href="{{ route('settings.index') }}#report-config" class="btn btn-warning btn-sm">
                        <i class="fas fa-cog"></i> Configure
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(!$mrdt_id || !$bs_id)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        Malaria investigations are not fully configured.
        <a href="{{ route('settings.index') }}#report-config">Go to Settings → Configure Reports</a> to select which investigation is mRDT and which is Blood Smear (BS).
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-flask"></i>
                Fomu ya Taarifa ya Vipimo vya Malaria &mdash; {{ $month_name }} {{ $year }}
            </h5>
        </div>
        <div class="card-body">

            {{-- Facility info --}}
            <p class="mb-1"><strong>JINA LA KITUO:</strong> {{ $facility['name'] }} &nbsp; <strong>WILAYA:</strong> {{ $facility['district'] }} &nbsp; <strong>MKOA:</strong> {{ $facility['region'] }}</p>
            <p class="mb-3"><strong>MWEZI:</strong> {{ $month }} &nbsp; <strong>MWAKA:</strong> {{ $year }}</p>

            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" style="font-size:0.85rem;">
                    <thead class="table-dark">
                        <tr>
                            <th colspan="2" rowspan="2" class="align-middle">KIPIMO</th>
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
                        @php
                            $agKeys = $age_group_keys;
                            $c      = $counts;
                            $rt     = $row_totals;
                            $gt     = $grand_total;
                            $gat    = $grand_age_total;
                            $rows   = [
                                ['key' => 'mrdt_negative', 'group' => 'mRDT', 'label' => 'Negative', 'groupspan' => true],
                                ['key' => 'mrdt_positive', 'group' => 'mRDT', 'label' => 'Positive',  'groupspan' => false],
                                ['key' => 'bs_no_mps',     'group' => 'BS',   'label' => 'No MPS',    'groupspan' => true],
                                ['key' => 'bs_mps_seen',   'group' => 'BS',   'label' => 'MPS seen',  'groupspan' => false],
                            ];
                        @endphp
                        @foreach($rows as $row)
                        <tr>
                            @if($row['groupspan'])
                            <td class="fw-bold text-start" rowspan="2">{{ $row['group'] }}</td>
                            @endif
                            <td class="text-start">{{ $row['label'] }}</td>
                            @foreach($agKeys as $ag)
                            <td>{{ $c[$row['key']][$ag]['male']   ?? 0 }}</td>
                            <td>{{ $c[$row['key']][$ag]['female'] ?? 0 }}</td>
                            @endforeach
                            <td>{{ $rt[$row['key']]['male']   ?? 0 }}</td>
                            <td>{{ $rt[$row['key']]['female'] ?? 0 }}</td>
                            <td class="fw-bold">{{ $rt[$row['key']]['total'] ?? 0 }}</td>
                        </tr>
                        @endforeach
                        <tr class="table-secondary fw-bold">
                            <td colspan="2" class="text-start">Jumla Ya Vipimo Vyote</td>
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
            </div>

            <p class="text-muted mt-2" style="font-size:0.78rem;">
                Generated: {{ $generated_at->format('d M Y H:i') }}
                @if($mrdt_service) &nbsp;|&nbsp; mRDT: {{ $mrdt_service }} @endif
                @if($bs_service)   &nbsp;|&nbsp; BS: {{ $bs_service }}     @endif
            </p>
        </div>
    </div>

</div>
@endsection
