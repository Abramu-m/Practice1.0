@extends('layouts.app_main_layout')

@section('page_title', 'ALu Report')

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
                    <form method="GET" action="{{ route('admin.reports.alu-monthly') }}" class="d-flex align-items-center gap-2 flex-wrap">
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
                        <a href="{{ route('settings.reports.alu-monthly') }}" class="btn btn-sm btn-secondary ms-auto">
                            <i class="fas fa-sliders-h"></i> Configure Mapping
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Report --}}
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="card">
                <div class="card-body">

                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1">{{ $facility['name'] ?? '' }}</h5>
                        <h5 class="fw-bold mb-0">FOMU YA TAARIFA YA MWEZI YA DAWA ZA MATIBABU YA MALARIA</h5>
                    </div>

                    <table class="table table-bordered table-sm mb-3">
                        <tr>
                            <td><strong>Wilaya:</strong> {{ $facility['district'] ?? '' }}</td>
                            <td><strong>Mkoa:</strong> {{ $facility['region'] ?? '' }}</td>
                            <td><strong>Mwezi:</strong> {{ $month_name }}</td>
                            <td><strong>Mwaka:</strong> {{ $year }}</td>
                        </tr>
                    </table>

                    {{-- Table 1: ALu doses --}}
                    <table class="table table-bordered table-sm text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th colspan="8">JUMLA YA VIDONGE VYA DAWA MSETO YA MALARIA (ALu) VILIVYOTOLEWA</th>
                            </tr>
                            <tr>
                                <th>Na</th>
                                <th>Dawa</th>
                                <th>Kipimo cha kugawa</th>
                                <th>Miaka 0-3<br>(0-15kg)</th>
                                <th>Miaka 3.1-8<br>(15.1-25kg)</th>
                                <th>Miaka 8.1-12<br>(25.1-35kg)</th>
                                <th>Miaka 12 na zaidi<br>(35kg+)</th>
                                <th>Jumla<br>(a+b+c+d)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alu_rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="text-start">{{ $row['label'] }}</td>
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

                    {{-- Table 2: Artesunate (static placeholder structure) --}}
                    <table class="table table-bordered table-sm text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th colspan="8">KIASI KILICHOTOLEWA CHA DAWA YA SINDANO YA ARTESUNATE</th>
                            </tr>
                            <tr>
                                <th>Na</th>
                                <th>Dawa</th>
                                <th>Kipimo cha kugawa</th>
                                <th>Chini au sawa ya kg 25</th>
                                <th>Uzito wa 26-50kg</th>
                                <th>Uzito wa 51-70kg</th>
                                <th>Uzito wa 76-100kg</th>
                                <th>Jumla</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>5a</td>
                                <td class="text-start">Idadi ya vichupa vilivyotumika (Initiated treatment and admitted)</td>
                                <td>Sindano</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>5b</td>
                                <td class="text-start">Idadi ya vichupa vilivyotumika (Initiated treatment and referred out)</td>
                                <td>Sindano</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>5c</td>
                                <td class="text-start">Idadi ya vichupa vilivyotumika (Referred in and treated)</td>
                                <td>Sindano</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Signature footer --}}
                    <table class="table table-borderless table-sm mt-4" style="font-size: 0.95rem;">
                        <tr>
                            <td style="width: 60%;">Jina la Mtayarishaji wa Taarifa: {{ auth()->user()?->name }}..................................................................</td>
                            <td>Kada......................................</td>
                        </tr>
                        <tr>
                            <td colspan="2">Saini............................................................................</td>
                        </tr>
                        <tr>
                            <td colspan="2">Imepitiwa na: {{ $facility?->inCharge?->name }}........................................................................, ...................................................................................................</td>
                        </tr>
                        <tr>
                            <td colspan="2">Namba ya simu ya kituo........................</td>
                        </tr>
                        <tr>
                            <td colspan="2">Tarehe ya kupokelewa Wilayani/Kutumwa kwenye mfumo:....../......./...........</td>
                        </tr>
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
