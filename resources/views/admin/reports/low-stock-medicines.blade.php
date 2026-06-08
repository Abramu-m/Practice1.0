@extends('layouts.app_main_layout')

@section('page_title', 'Low Stock Alert')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-exclamation-circle"></i> Low Stock Medicines Alert
            </h1>
            <p class="text-muted">
                {{ $facility['name'] ?? 'Facility' }} |
                As of {{ $generated_at->format('d M Y H:i') }}
            </p>
        </div>
    </div>

    <!-- Report Controls -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('admin.reports.low-stock-medicines') }}?pdf=1" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                        <button class="btn btn-sm btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Total Low Stock Items</h6>
                    <h2 class="text-warning mb-0">{{ $total_low_stock_items }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Out of Stock</h6>
                    <h2 class="text-danger mb-0">
                        {{ count(array_filter($medicines, function($m) { return $m['status'] === 'out_of_stock'; })) }}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title">Low Stock</h6>
                    <h2 class="text-warning mb-0">
                        {{ count(array_filter($medicines, function($m) { return $m['status'] === 'low_stock'; })) }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Medicines Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">Medicines Below Reorder Level</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Reorder Level</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($medicines as $med)
                                    <tr class="{{ $med['status'] === 'out_of_stock' ? 'table-danger' : 'table-warning' }}">
                                        <td><strong>{{ $med['name'] }}</strong></td>
                                        <td>{{ $med['category'] ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $med['current_stock'] == 0 ? 'badge-danger' : 'badge-warning' }}">
                                                {{ $med['current_stock'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $med['reorder_level'] }}</td>
                                        <td class="text-center">
                                            @if ($med['status'] === 'out_of_stock')
                                                <span class="badge badge-danger"><i class="fas fa-times"></i> Out of Stock</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-exclamation"></i> Low Stock</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('medications.index') }}" class="btn btn-xs btn-primary" title="Manage Stock">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <strong><i class="fas fa-check-circle"></i> All medicines are well stocked!</strong>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Facility Info -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Facility Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Facility:</strong> {{ $facility['name'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Region:</strong> {{ $facility['region'] ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>District:</strong> {{ $facility['district'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Generated:</strong> {{ $generated_at->format('d M Y H:i:s') }}</p>
                            <p class="mb-1"><strong>Generated By:</strong> {{ $generated_by }}</p>
                            <p class="mb-0"><strong>Report Type:</strong> Stock Alert</p>
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

.table-danger {
    background-color: #f8d7da !important;
}

.table-warning {
    background-color: #fff3cd !important;
}

.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection
