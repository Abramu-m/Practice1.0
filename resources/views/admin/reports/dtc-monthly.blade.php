@extends('layouts.app_main_layout')

@section('page_title', 'DTC Monthly Report')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-heartbeat"></i> DTC (Diarrhea Treatment Center) Monthly Report
            </h1>
            <p class="text-muted">
                {{ $facility['name'] ?? 'Facility' }} |
                {{ $month_name }} {{ $year }}
            </p>
        </div>
    </div>

    <!-- Report Filters & Controls -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.dtc-monthly') }}" class="form-inline">
                        <label class="mr-2">Select Month:</label>
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary ml-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Diarrhea Cases Report -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Diarrhea Cases by Age & Gender</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Age Group</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($by_age_gender as $row)
                                    <tr>
                                        <td>{{ $row['age_group'] }}</td>
                                        <td class="text-center">{{ $row['male'] }}</td>
                                        <td class="text-center">{{ $row['female'] }}</td>
                                        <td class="text-center"><strong>{{ $row['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                                <tr class="table-active">
                                    <td><strong>Total</strong></td>
                                    <td class="text-center"><strong>{{ $totals['male'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $totals['female'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $totals['total'] }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Report Information</h6>
                            <p class="mb-1"><strong>Facility:</strong> {{ $facility['name'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Region:</strong> {{ $facility['region'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>District:</strong> {{ $facility['district'] ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Generated:</strong> {{ $generated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Summary Statistics</h6>
                            <p class="mb-1"><strong>Total Cases:</strong> <span class="badge badge-danger">{{ $total_cases }}</span></p>
                            <p class="mb-1"><strong>Males:</strong> {{ $totals['male'] ?? 0 }}</p>
                            <p class="mb-0"><strong>Females:</strong> {{ $totals['female'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.table-responsive {
    border-radius: 4px;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.table-active {
    background-color: #f8f9fa !important;
}
</style>
@endsection
