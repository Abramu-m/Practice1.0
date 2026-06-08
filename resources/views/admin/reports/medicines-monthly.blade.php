@extends('layouts.app_main_layout')

@section('page_title', 'Medicines Monthly Report')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-pills"></i> Medicines Monthly Consumption Report
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
                    <form method="GET" action="{{ route('admin.reports.medicines-monthly') }}" class="form-inline">
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

    <!-- Medications by Category -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">Medications Dispensed by Category</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Unique Items</th>
                                    <th class="text-center">Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($medications['by_category'] as $cat)
                                    <tr>
                                        <td>{{ $cat['category'] ?? 'Uncategorized' }}</td>
                                        <td class="text-center">{{ $cat['unique_items'] }}</td>
                                        <td class="text-center">{{ $cat['total_quantity'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No medications dispensed</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Medications -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Top 20 Medicines Dispensed</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Quantity Dispensed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($medications['by_medication'] as $med)
                                    <tr>
                                        <td>{{ $med['name'] }}</td>
                                        <td>{{ $med['category'] ?? '-' }}</td>
                                        <td class="text-center">{{ $med['quantity_dispensed'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No medications dispensed</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Investigations/Lab Services -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">Laboratory/Investigation Services Conducted</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Service Name</th>
                                    <th class="text-center">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($investigations['by_type'] as $inv)
                                    <tr>
                                        <td>{{ $inv['name'] }}</td>
                                        <td class="text-center">{{ $inv['count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No investigations conducted</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row">
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
                    <h6 class="card-title">Consumption Summary</h6>
                    <p class="mb-1"><strong>Total Medications Dispensed:</strong> {{ $medications['total_dispensed'] }}</p>
                    <p class="mb-1"><strong>Unique Medications:</strong> {{ $medications['unique_medications'] }}</p>
                    <p class="mb-1"><strong>Total Lab Tests:</strong> {{ $investigations['total_conducted'] }}</p>
                    <p class="mb-0"><strong>Medicine Categories:</strong> {{ count($medications['by_category']) }}</p>
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
