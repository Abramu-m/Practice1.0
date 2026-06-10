@extends('layouts.app_main_layout')

@section('page_title', 'IDSR Weekly Report — Form 3B')

@section('styles')
<style>
    .idsr-table th, .idsr-table td {
        font-size: 0.72rem;
        padding: 3px 5px;
        vertical-align: middle;
        text-align: center;
    }
    .idsr-table td:nth-child(2) { text-align: left; }
    .idsr-table thead th { background: #1a3a5c; color: #fff; border-color: #0d2233; }
    .idsr-table thead tr:nth-child(2) th { background: #1f5c99; }
    .idsr-table thead tr:nth-child(3) th { background: #2878c8; }
    .group-header { background: #dee2e6 !important; color: #212529 !important; font-weight: 600; }

    @media print {
        .app-header, .app-sidebar, .app-footer, .no-print { display: none !important; }
        .app-wrapper, .app-main, .app-content, .container-fluid {
            margin: 0 !important; padding: 0 !important;
            width: 100% !important; background: #fff !important;
        }
        @page { margin: 8mm 10mm; size: A4 landscape; }
        .idsr-table th, .idsr-table td { font-size: 7pt; padding: 2px 3px; }
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">

    {{-- Toolbar --}}
    <div class="row mb-3 no-print">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('admin.reports.idsr-weekly') }}" class="d-flex align-items-center gap-2 flex-wrap">
                        <label class="mb-0 fw-semibold me-1">Week:</label>
                        <select name="week" class="form-select form-select-sm" style="width:90px" required>
                            @for ($w = 1; $w <= 53; $w++)
                                <option value="{{ $w }}" @selected($w == $week)>{{ $w }}</option>
                            @endfor
                        </select>

                        <label class="mb-0 fw-semibold ms-2 me-1">Year:</label>
                        <select name="year" class="form-select form-select-sm" style="width:90px">
                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                            @endfor
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary ms-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Form header --}}
    <div class="text-center mb-2">
        <h6 class="mb-0 fw-bold text-uppercase">FORM 3 B: WEEKLY REPORTED CASES / DEATHS AT FACILITY LEVELS</h6>
        <small class="text-muted">Integrated Disease Surveillance and Response (IDSR)</small>
    </div>
    <div class="row mb-2">
        <div class="col-md-8 mx-auto">
            <table class="table table-sm table-bordered mb-0" style="font-size:0.78rem">
                <tr>
                    <td class="fw-semibold" style="width:18%">Region:</td>
                    <td>{{ $facility['region'] ?? '—' }}</td>
                    <td class="fw-semibold" style="width:18%">District:</td>
                    <td>{{ $facility['district'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Facility:</td>
                    <td>{{ $facility['name'] ?? '—' }}</td>
                    <td class="fw-semibold">Week No. / Year:</td>
                    <td>{{ $week_info['week_number'] }} / {{ $year }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Week Dates:</td>
                    <td colspan="3">
                        {{ \Carbon\Carbon::parse($week_info['start_date'])->format('d M Y') }}
                        &nbsp;—&nbsp;
                        {{ \Carbon\Carbon::parse($week_info['end_date'])->format('d M Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Main report table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-sm idsr-table mb-1">
            <thead>
                {{-- Level 1 --}}
                <tr>
                    <th rowspan="3" style="width:26px">S/No</th>
                    <th rowspan="3" style="width:220px;text-align:left">Disease / Condition</th>
                    <th colspan="9">CASES THIS WEEK</th>
                    <th colspan="9">DEATHS THIS WEEK</th>
                    <th colspan="6">CUMULATIVE (Jan 1 – Week {{ $week_info['week_number'] }})</th>
                </tr>
                {{-- Level 2 --}}
                <tr>
                    <th colspan="3">&lt;5 yrs</th>
                    <th colspan="3">5+ yrs</th>
                    <th colspan="3">Total</th>
                    <th colspan="3">&lt;5 yrs</th>
                    <th colspan="3">5+ yrs</th>
                    <th colspan="3">Total</th>
                    <th colspan="3">Cases</th>
                    <th colspan="3">Deaths</th>
                </tr>
                {{-- Level 3 --}}
                <tr>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                    <th>M</th><th>F</th><th>T</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($diseases as $id => $d)
                @php
                    $wc = $d['weekly_cases'];
                    $cc = $d['cumulative_cases'];
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="text-align:left">{{ $d['name'] }}</td>
                    {{-- Cases this week: <5 --}}
                    <td>{{ $wc['u5_m'] ?: '' }}</td>
                    <td>{{ $wc['u5_f'] ?: '' }}</td>
                    <td>{{ $wc['u5_t'] ?: '' }}</td>
                    {{-- Cases this week: 5+ --}}
                    <td>{{ $wc['5p_m'] ?: '' }}</td>
                    <td>{{ $wc['5p_f'] ?: '' }}</td>
                    <td>{{ $wc['5p_t'] ?: '' }}</td>
                    {{-- Cases this week: Total --}}
                    <td>{{ $wc['tot_m'] ?: '' }}</td>
                    <td>{{ $wc['tot_f'] ?: '' }}</td>
                    <td>{{ $wc['tot_t'] ?: '' }}</td>
                    {{-- Deaths this week --}}
                    <td>—</td><td>—</td><td>—</td>
                    <td>—</td><td>—</td><td>—</td>
                    <td>—</td><td>—</td><td>—</td>
                    {{-- Cumulative cases --}}
                    <td>{{ $cc['m'] ?: '' }}</td>
                    <td>{{ $cc['f'] ?: '' }}</td>
                    <td>{{ $cc['t'] ?: '' }}</td>
                    {{-- Cumulative deaths --}}
                    <td>—</td><td>—</td><td>—</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-muted" style="font-size:0.72rem" id="death-note">
        <em>Note: Death columns show — because in-facility death outcome tracking is not yet enabled in this system.</em>
    </p>
    <p class="text-muted" style="font-size:0.72rem">
        Generated: {{ $generated_at->format('d M Y H:i') }} &nbsp;|&nbsp; {{ $facility['name'] ?? '' }}
    </p>

</div>
@endsection
