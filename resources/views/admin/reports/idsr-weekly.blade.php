@extends('layouts.app_main_layout')

@section('page_title', 'IDSR Weekly Report')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-microscope"></i> IDSR Weekly Disease Report
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
                    <form method="GET" action="{{ route('admin.reports.idsr-weekly') }}" class="form-inline">
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

    <!-- Diseases Report -->
    <div class="row">
        <div class="col-md-12">
            @forelse ($diseases as $disease_name => $disease_data)
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">{{ $disease_name }}</h6>
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
                                    @foreach ($disease_data['by_age_gender'] as $row)
                                        <tr>
                                            <td>{{ $row['age_group'] }}</td>
                                            <td class="text-center">{{ $row['male'] }}</td>
                                            <td class="text-center">{{ $row['female'] }}</td>
                                            <td class="text-center"><strong>{{ $row['total'] }}</strong></td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-active">
                                        <td><strong>Total Cases</strong></td>
                                        <td class="text-center"><strong>{{ $disease_data['totals']['male'] }}</strong></td>
                                        <td class="text-center"><strong>{{ $disease_data['totals']['female'] }}</strong></td>
                                        <td class="text-center"><strong>{{ $disease_data['totals']['total'] }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> No disease cases reported for this week.
                </div>
            @endforelse

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
                            <p class="mb-0"><strong>Total Diseases:</strong> {{ count($diseases) }}</p>
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
