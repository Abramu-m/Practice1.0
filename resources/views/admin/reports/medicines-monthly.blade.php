@extends('layouts.app_main_layout')

@section('page_title', 'Medicines Monthly Dispensing Report')

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
</style>
@endsection

@section('main_content')
<div class="container-fluid">

    {{-- Controls --}}
    <div class="row mb-3 no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('admin.reports.medicines-monthly') }}" class="d-flex align-items-center gap-2 flex-wrap">
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
                        <a href="{{ route('admin.pharmacy-settings.medicine-dispensing.index') }}" class="btn btn-sm btn-secondary ms-auto">
                            <i class="fas fa-sliders-h"></i> Configure Mapping
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Report --}}
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-body">

                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1">{{ $facility['name'] ?? '' }}</h5>
                        <h5 class="fw-bold mb-0">TAARIFA YA MWEZI YA KUTOLEA DAWA</h5>
                    </div>

                    <table class="table table-bordered table-sm mb-3">
                        <tr>
                            <td><strong>Wilaya:</strong> {{ $facility['district'] ?? '' }}</td>
                            <td><strong>Mkoa:</strong> {{ $facility['region'] ?? '' }}</td>
                            <td><strong>Mwezi:</strong> {{ $month_name }}</td>
                            <td><strong>Mwaka:</strong> {{ $year }}</td>
                        </tr>
                    </table>

                    <table class="table table-bordered table-sm text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2">Na</th>
                                <th rowspan="2">Dawa</th>
                                <th rowspan="2">Kipimo cha kugawa</th>
                                <th colspan="3">Kiasi cha dawa kilichotolewa kwa wagonjwa</th>
                                <th rowspan="2">Jumla</th>
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
                                    <td rowspan="{{ $row['drug_rowspan'] }}" class="text-start">{{ $row['drug_label'] }}</td>
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

                    <p class="text-muted small mt-2 mb-0 no-print">
                        Generated: {{ $generated_at->format('d M Y H:i') }}
                    </p>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
