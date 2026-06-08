@extends('layouts.app_main_layout')

@section('page_title', 'Microbiology Lab Report')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-flask"></i> Microbiology Lab Report
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
                    <form method="GET" action="{{ route('admin.reports.lab-microbiology') }}" class="form-inline">
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

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Total Tests</h6>
                    <h2 class="text-primary mb-0">{{ $total_tests }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Completed</h6>
                    <h2 class="text-success mb-0">{{ $completed_tests }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Pending</h6>
                    <h2 class="text-warning mb-0">{{ $pending_tests }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Completion Rate</h6>
                    <h2 class="text-info mb-0">{{ $completion_rate }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Investigations List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Microbiology Investigations</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Test Name</th>
                                    <th class="text-center">Patient ID</th>
                                    <th class="text-center">Visit Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Result Value</th>
                                    <th class="text-center">Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($investigations as $inv)
                                    <tr>
                                        <td>{{ $inv['test_name'] }}</td>
                                        <td class="text-center">{{ $inv['patient_id'] }}</td>
                                        <td class="text-center">{{ $inv['visit_date'] }}</td>
                                        <td class="text-center">
                                            @if ($inv['status'] === 'Completed' || !empty($inv['result_value']))
                                                <span class="badge badge-success">Completed</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $inv['result_value'] ?? '-' }}</td>
                                        <td class="text-center">{{ $inv['result_unit'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No microbiology investigations found this month</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Information -->
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
                            <h6 class="card-title">Laboratory Summary</h6>
                            <p class="mb-1"><strong>Total Tests:</strong> {{ $total_tests }}</p>
                            <p class="mb-1"><strong>Completed:</strong> {{ $completed_tests }}</p>
                            <p class="mb-1"><strong>Pending:</strong> {{ $pending_tests }}</p>
                            <p class="mb-0"><strong>Completion Rate:</strong> {{ $completion_rate }}%</p>
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
</style>
@endsection
