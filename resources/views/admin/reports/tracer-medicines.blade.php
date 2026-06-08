@extends('layouts.app_main_layout')

@section('page_title', 'Tracer Medicines Report')

@section('main_content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('admin.reports.tracer-medicines') }}" class="d-flex align-items-center gap-2 flex-wrap">
                        <label class="mb-0 fw-semibold">Month:</label>
                        <select name="month" class="form-select form-select-sm" style="width:auto" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <a href="{{ route('medications.tracer') }}" class="btn btn-sm btn-outline-warning ms-auto">
                            <i class="bi bi-star-fill me-1"></i> Manage Tracer Medicines
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row mb-4 g-3">
        <div class="col-sm-4">
            <div class="card text-center h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-muted small mb-1">Total Tracer Medicines</div>
                    <div class="fs-2 fw-bold">{{ $tracer_medicines['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center h-100 border-success">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-success small mb-1">In Stock</div>
                    <div class="fs-2 fw-bold text-success">{{ $tracer_medicines['available_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center h-100 border-danger">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-danger small mb-1">Out of Stock</div>
                    <div class="fs-2 fw-bold text-danger">{{ $tracer_medicines['unavailable_count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(empty($tracer_medicines['items']))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            No tracer medicines have been mapped yet.
            <a href="{{ route('medications.tracer') }}" class="alert-link">Map tracer medicines here.</a>
        </div>
    @else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Tracer Medicines Availability — {{ $month_name }} {{ $year }}</h6>
            @php
                $rate = $tracer_medicines['total'] > 0
                    ? round(($tracer_medicines['available_count'] / $tracer_medicines['total']) * 100)
                    : 0;
            @endphp
            <span class="badge {{ $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} fs-6">
                Availability Rate: {{ $rate }}%
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tracerReportTable">
                    <thead class="table-light text-uppercase text-muted" style="font-size:.75rem">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Medicine Name</th>
                            <th>Strength</th>
                            <th class="text-center" style="width:130px">Stock Qty</th>
                            <th class="text-center" style="width:130px">Availability</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tracer_medicines['items'] as $i => $med)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $med['name'] }}</td>
                            <td class="text-muted">{{ $med['strength'] }}</td>
                            <td class="text-center">
                                @if($med['available'])
                                    <span class="badge bg-success-subtle text-success">{{ number_format($med['stock_quantity']) }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($med['available'])
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Available</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            Facility: {{ $facility['name'] ?? 'N/A' }} &nbsp;|&nbsp;
            Region: {{ $facility['region'] ?? 'N/A' }} &nbsp;|&nbsp;
            Generated: {{ $generated_at->format('d M Y H:i') }}
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#tracerReportTable').DataTable({
        responsive: true,
        pageLength: 50,
        lengthMenu: [25, 50, 100, -1],
        columnDefs: [
            { orderable: false, targets: [0] }
        ]
    });
});
</script>
@endsection
