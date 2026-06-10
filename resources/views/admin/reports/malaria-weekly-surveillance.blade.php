@extends('layouts.app_main_layout')

@section('page_title', 'Weekly Malaria Surveillance')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="bi bi-virus text-danger"></i> Weekly Malaria Surveillance
            </h1>
            <p class="text-muted">
                {{ $facility['name'] ?? 'Facility' }} |
                {{ $week_info['formatted'] ?? '' }}
            </p>
        </div>
    </div>

    <!-- Report Filters & Controls -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.malaria-weekly-surveillance') }}" class="form-inline">
                        <label class="mr-2">Select Week:</label>
                        <select name="week" class="form-control form-control-sm mr-2" required>
                            @for ($w = 1; $w <= 53; $w++)
                                <option value="{{ $w }}" {{ $w == $week ? 'selected' : '' }}>
                                    Week {{ $w }}
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

    <!-- Malaria Surveillance Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        Week beginning (date): {{ $week_info['start_date'] }}
                        &nbsp;|&nbsp;
                        Week Ending (date): {{ $week_info['end_date'] }}
                        &nbsp;|&nbsp;
                        Week Number: {{ $week_info['week_number'] }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th rowspan="2" class="align-middle">Days</th>
                                    <th colspan="2">Number Tested with mRDT/Microscope</th>
                                    <th colspan="2">Number Tested Positive</th>
                                    <th colspan="2">Number of Clinical Malaria Cases</th>
                                </tr>
                                <tr>
                                    <th>Under 5 Years</th>
                                    <th>5 Years and Above</th>
                                    <th>Under 5 Years</th>
                                    <th>5 Years and Above</th>
                                    <th>Under 5 Years</th>
                                    <th>5 Years and Above</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($days as $day)
                                    <tr>
                                        <td class="text-left">{{ $day['day_name'] }}</td>
                                        <td>{{ $day['tested_under5'] }}</td>
                                        <td>{{ $day['tested_5plus'] }}</td>
                                        <td>{{ $day['positive_under5'] }}</td>
                                        <td>{{ $day['positive_5plus'] }}</td>
                                        <td>{{ $day['clinical_under5'] }}</td>
                                        <td>{{ $day['clinical_5plus'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td class="text-left"><strong>Total</strong></td>
                                    <td><strong>{{ $totals['tested_under5'] }}</strong></td>
                                    <td><strong>{{ $totals['tested_5plus'] }}</strong></td>
                                    <td><strong>{{ $totals['positive_under5'] }}</strong></td>
                                    <td><strong>{{ $totals['positive_5plus'] }}</strong></td>
                                    <td><strong>{{ $totals['clinical_under5'] }}</strong></td>
                                    <td><strong>{{ $totals['clinical_5plus'] }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
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
                            <h6 class="card-title">Week Details</h6>
                            <p class="mb-1"><strong>Week Number:</strong> {{ $week_info['week_number'] }}</p>
                            <p class="mb-1"><strong>Start Date:</strong> {{ $week_info['start_date'] }}</p>
                            <p class="mb-1"><strong>End Date:</strong> {{ $week_info['end_date'] }}</p>
                            <p class="mb-1"><strong>mRDT Service:</strong> {{ $mrdt_service ?? 'Not configured' }}</p>
                            <p class="mb-0"><strong>BS Service:</strong> {{ $bs_service ?? 'Not configured' }}</p>
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
