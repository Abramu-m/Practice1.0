@extends('layouts.app_main_layout')

@section('page_title', 'Stock Levels Management')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-warehouse text-primary me-2"></i>
                        Stock Levels Management
                    </h1>
                    <p class="text-muted mb-0">Monitor and manage medication inventory across all locations</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" onclick="exportStockReport()">
                        <i class="fas fa-download me-2"></i>
                        Export
                    </button>
                    <button class="btn btn-outline-primary" onclick="refreshStockData()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-3 p-3">
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">In Stock</h6>
                            <h4 class="mb-0 text-success" id="in-stock-count">{{ $stockSummary['in_stock'] ?? 0 }}</h4>
                            <small class="text-muted">Available items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-exclamation-triangle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Low Stock</h6>
                            <h4 class="mb-0 text-warning" id="low-stock-count">{{ $stockSummary['low_stock'] ?? 0 }}</h4>
                            <small class="text-muted">Below reorder level</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-gradient rounded-3 p-3">
                                <i class="fas fa-times-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Out of Stock</h6>
                            <h4 class="mb-0 text-danger" id="out-stock-count">{{ $stockSummary['out_of_stock'] ?? 0 }}</h4>
                            <small class="text-muted">Zero quantity</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-3 p-3">
                                <i class="fas fa-calendar-times text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Expiring Soon</h6>
                            <h4 class="mb-0 text-info" id="expiring-count">{{ $stockSummary['expiring'] ?? 0 }}</h4>
                            <small class="text-muted">Next 30 days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters and Search --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="stock-filter-form" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Medications</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Name, generic name, or code..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="location" class="form-label">Location</label>
                            <select class="form-select" id="location" name="location_id">
                                <option value="">All Locations</option>
                                @foreach($locations ?? [] as $location)
                                <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories ?? [] as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="stock-status" class="form-label">Stock Status</label>
                            <select class="form-select" id="stock-status" name="stock_status">
                                <option value="">All Status</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>
                                    Filter
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times me-2"></i>
                                    Clear
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Levels Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i>
                        Medication Stock Levels
                    </h5>
                    <span class="badge bg-light text-dark">
                        Total: {{ count($stockLevels ?? []) }} items
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="stock-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Medication</th>
                                    <th>Category</th>
                                    <th>Total Stock</th>
                                    <th>Reorder Level</th>
                                    <th>Status</th>
                                    <th>Location Breakdown</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockLevels ?? [] as $stock)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $stock['name'] }}</div>
                                            <small class="text-muted">{{ $stock['generic_name'] }}</small>
                                            @if($stock['id'])
                                            <br><small class="badge bg-light text-dark">ID: {{ $stock['id'] }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($stock['category'] ?? 'N/A') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold fs-5">{{ number_format($stock['total_stock'] ?? 0) }}</span>
                                        <br><small class="text-muted">units</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($stock['reorder_level'] ?? 0) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $status = $stock['stock_status'] ?? 'unknown';
                                            $badgeClass = match($status) {
                                                'in_stock' => 'bg-success',
                                                'low_stock' => 'bg-warning text-dark',
                                                'out_of_stock' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            $statusText = match($status) {
                                                'in_stock' => 'In Stock',
                                                'low_stock' => 'Low Stock',
                                                'out_of_stock' => 'Out of Stock',
                                                default => 'Unknown'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        @if(!empty($stock['location_stocks']))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($stock['location_stocks'] as $locationStock)
                                            <span class="badge bg-light text-dark small" title="{{ $locationStock['location_name'] }}">
                                                {{ Str::limit($locationStock['location_name'], 8) }}: {{ number_format($locationStock['available_quantity']) }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @else
                                        <span class="text-muted">No locations</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('medications.stock.availability', $stock['id']) }}">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('medications.stock.transfers.create') }}?medication_id={{ $stock['id'] }}">
                                                        <i class="fas fa-exchange-alt me-2"></i>
                                                        Transfer Stock
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('medications.stock.adjustments.create') }}?medication_id={{ $stock['id'] }}">
                                                        <i class="fas fa-edit me-2"></i>
                                                        Adjust Stock
                                                    </a>
                                                </li>
                                                @if($status === 'low_stock' || $status === 'out_of_stock')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-warning" href="{{ route('store.requisitions.index') }}?medication_id={{ $stock['id'] }}">
                                                        <i class="fas fa-shopping-cart me-2"></i>
                                                        Create Requisition
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-box-open text-muted fa-3x mb-3"></i>
                                        <p class="text-muted mb-0">No medications found matching your criteria</p>
                                        <small class="text-muted">Try adjusting your filters or search terms</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if(isset($stockLevels) && count($stockLevels) > 0)
                <div class="card-footer bg-white border-0">
                    {{-- Pagination would go here if using paginated results --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ count($stockLevels) }} medications
                        </small>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="exportStockReport()">
                                <i class="fas fa-download me-2"></i>
                                Export to Excel
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="printStockReport()">
                                <i class="fas fa-print me-2"></i>
                                Print Report
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function refreshStockData() {
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    // Simulate refresh
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function clearFilters() {
    document.getElementById('stock-filter-form').reset();
    window.location.href = '{{ route("medications.stock.levels") }}';
}

function exportStockReport() {
    // Get current filters
    const form = document.getElementById('stock-filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Add export parameter
    params.append('export', 'excel');
    
    // Create download link
    const url = '{{ route("medications.reports.export") }}?' + params.toString();
    window.open(url, '_blank');
}

function printStockReport() {
    window.print();
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('#stock-filter-form select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('stock-filter-form').submit();
        });
    });
    
    // Search input with debounce
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('stock-filter-form').submit();
        }, 500);
    });
});

// Add print styles
const printStyles = `
<style media="print">
    .medication-nav, .btn, .dropdown { display: none !important; }
    .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    .table { font-size: 12px; }
    @page { margin: 1cm; }
</style>
`;
document.head.insertAdjacentHTML('beforeend', printStyles);
</script>
@endsection
