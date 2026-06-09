@extends('layouts.app_main_layout')

@section('page_title', 'Haematology Monthly Report')

@section('styles')
<style>
@media print {
    .app-header,
    .app-sidebar,
    .app-footer,
    .no-print { display: none !important; }

    .app-wrapper, .app-main, .app-content, .container-fluid {
        margin: 0 !important; padding: 0 !important;
        width: 100% !important; background: #fff !important;
    }

    @page { margin: 10mm 12mm; }
}

.section-header-row td {
    background-color: #f0f0f0;
    font-weight: bold;
    font-style: italic;
}
</style>
@endsection

@section('main_content')
<div class="container-fluid">

    {{-- Controls --}}
    <div class="row mb-3 no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('admin.reports.lab-hematology') }}" class="d-flex align-items-center gap-2 flex-wrap">
                        <label class="mb-0 me-1">Month:</label>
                        <select name="month" class="form-select form-select-sm" style="width:auto" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <a href="{{ route('admin.lab-settings.hematology.index') }}" class="btn btn-sm btn-secondary ms-auto">
                            <i class="fas fa-sliders-h"></i> Configure Mapping
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Report --}}
    <div class="row">
        <div class="col-md-9 mx-auto">
            <div class="card">
                <div class="card-body">

                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1">{{ $facility['name'] ?? '' }}</h5>
                        <h5 class="fw-bold mb-0">HAEMATOLOGY MONTHLY REPORT</h5>
                    </div>

                    <table class="table table-bordered table-sm mb-3">
                        <tr>
                            <td><strong>Council:</strong> {{ $facility['district'] ?? '' }}</td>
                            <td><strong>Region:</strong> {{ $facility['region'] ?? '' }}</td>
                            <td><strong>Month:</strong> {{ $month_name }}</td>
                            <td><strong>Year:</strong> {{ $year }}</td>
                        </tr>
                    </table>

                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>TEST</th>
                                <th class="text-center" style="width:70px">TOTAL</th>
                                <th class="text-center" style="width:60px">LOW</th>
                                <th class="text-center" style="width:60px">HIGH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @if($row->is_section_header)
                                <tr class="section-header-row">
                                    <td colspan="4">{{ $row->row_label }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td>{{ $row->row_label }}</td>
                                    <td class="text-center">{{ $totals[$row->row_key] ?? 0 }}</td>
                                    <td class="text-center">{{ $lows[$row->row_key] !== null ? $lows[$row->row_key] : '' }}</td>
                                    <td class="text-center">{{ $highs[$row->row_key] !== null ? $highs[$row->row_key] : '' }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td>GRAND TOTAL</td>
                                <td class="text-center">{{ $grand_total }}</td>
                                <td class="text-center">{{ $grand_total_low }}</td>
                                <td class="text-center">{{ $grand_total_high }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <p class="text-muted small mt-2 mb-0 no-print">
                        Generated: {{ $generated_at->format('d M Y H:i') }}
                    </p>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
