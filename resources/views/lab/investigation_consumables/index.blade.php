@extends('layouts.app_main_layout')

@section('page_title', 'Medical Service Consumable Templates')

@section('main_content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-flask text-primary me-2"></i>
                        Medical Service Consumable Templates
                    </h1>
                    <p class="text-muted mb-0">Define consumable requirements for each medical service and laboratory test</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-info" onclick="showStockSummary()">
                        <i class="fas fa-chart-bar me-2"></i>
                        Stock Summary
                    </button>
                    <button class="btn btn-outline-primary" onclick="bulkStockCheck()">
                        <i class="fas fa-check-double me-2"></i>
                        Bulk Stock Check
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search medical services...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('lab.service-consumables.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Medical Services Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Medical Services ({{ $medicalServices->total() }})
                </h5>
                <div class="d-flex gap-2">
                    <div class="btn-group" role="group">
                        <input type="checkbox" class="btn-check" id="select-all">
                        <label class="btn btn-outline-secondary btn-sm" for="select-all">
                            <i class="fas fa-check-square me-1"></i> Select All
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                            </th>
                            <th>Service Name</th>
                            <th>Category</th>
                            <th>Code</th>
                            <th>Required Consumables</th>
                            <th>Sample Required</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicalServices as $service)
                            <tr data-service-id="{{ $service->id }}">
                                <td>
                                    <input type="checkbox" class="form-check-input service-checkbox" 
                                           value="{{ $service->id }}">
                                </td>
                                <td>
                                    <div class="fw-medium text-primary">{{ $service->name }}</div>
                                    @if($service->description)
                                        <small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $service->serviceCategory->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <code class="small">{{ $service->code ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    @php
                                        $activeConsumables = $service->activeConsumableRequirements;
                                        $consumablesCount = $activeConsumables->count();
                                        $requiredCount = $activeConsumables->where('is_optional', false)->count();
                                        $optionalCount = $activeConsumables->where('is_optional', true)->count();
                                    @endphp
                                    
                                    @if($consumablesCount > 0)
                                        <div class="small">
                                            <span class="badge bg-primary">{{ $consumablesCount }} Total</span>
                                            @if($requiredCount > 0)
                                                <span class="badge bg-danger">{{ $requiredCount }} Required</span>
                                            @endif
                                            @if($optionalCount > 0)
                                                <span class="badge bg-info">{{ $optionalCount }} Optional</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">No consumables defined</span>
                                    @endif
                                </td>
                                <td>
                                    @if($service->requires_sample)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-vial me-1"></i>{{ $service->sample_type ?? 'Required' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Not required</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('lab.service-consumables.individual.show', $service) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Manage Consumables">
                                            <i class="fas fa-cogs"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewServiceDetails({{ $service->id }})"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="checkStockForService({{ $service->id }})"
                                                title="Check Stock">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-flask text-muted fa-3x mb-3"></i>
                                    <h5 class="text-muted">No medical services found</h5>
                                    <p class="text-muted">
                                        @if(request()->hasAny(['search', 'category_id']))
                                            Try adjusting your filters.
                                        @else
                                            No medical services available for consumable management.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($medicalServices->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $medicalServices->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Stock Summary Modal --}}
<div class="modal fade" id="stockSummaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Stock Summary
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="stockSummaryContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stock Check Modal --}}
<div class="modal fade" id="stockCheckModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-double me-2"></i>
                    Stock Availability Check
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="stockCheckContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.stock-status-available {
    color: #28a745 !important;
}
.stock-status-partial {
    color: #ffc107 !important;
}
.stock-status-insufficient {
    color: #dc3545 !important;
}
.service-checkbox:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle select all checkbox
    $('#select-all-checkbox').change(function() {
        $('.service-checkbox').prop('checked', this.checked);
    });
    
    // Handle individual checkboxes
    $('.service-checkbox').change(function() {
        const totalCheckboxes = $('.service-checkbox').length;
        const checkedCheckboxes = $('.service-checkbox:checked').length;
        
        $('#select-all-checkbox').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
});

// Check stock status for all investigations with consumables
function checkAllStockStatuses() {
    $('.stock-status-cell').each(function() {
        const investigationId = $(this).data('investigation-id');
        checkSingleStockStatus(investigationId);
    });
}

// Check stock status for a single investigation
function checkSingleStockStatus(investigationId) {
    const statusCell = $(`.stock-status-cell[data-investigation-id="${investigationId}"]`);
    
    $.ajax({
        url: `/investigations/${investigationId}/consumables/check-stock`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                let html = '';
                let statusClass = '';
// Manage consumables for a medical service
function manageConsumables(serviceId) {
    // For now, we'll show an alert. Later this could open a modal or navigate to a dedicated page
    alert(`Managing consumables for medical service ID: ${serviceId}\n\nThis feature will be implemented to allow adding/editing consumable requirements for this medical service.`);
}

// View service details
function viewServiceDetails(serviceId) {
    alert(`Viewing details for medical service ID: ${serviceId}\n\nThis feature will show comprehensive service information including description, category, pricing, and consumable requirements.`);
}

// Check stock for a service's consumables
function checkStockForService(serviceId) {
    alert(`Checking stock availability for all consumables required by medical service ID: ${serviceId}\n\nThis will show current stock levels vs requirements.`);
}

// Show stock summary
function showStockSummary() {
    $('#stockSummaryContent').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
    
    $('#stockSummaryModal').modal('show');
    
    // Load stock summary data
    $.ajax({
        url: '{{ route("lab.service-consumables.index") }}?action=stock_summary',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = generateStockSummaryReport(response.data);
                $('#stockSummaryContent').html(html);
            } else {
                $('#stockSummaryContent').html('<div class="alert alert-danger">Failed to load stock summary.</div>');
            }
        },
        error: function() {
            $('#stockSummaryContent').html('<div class="alert alert-danger">Error loading stock summary.</div>');
        }
    });
}

// Generate stock summary report HTML
function generateStockSummaryReport(data) {
    return `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h3 class="text-primary">${data.total_services || 0}</h3>
                        <p class="mb-0">Total Services</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h3 class="text-success">${data.services_with_stock || 0}</h3>
                        <p class="mb-0">Services w/ Stock</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <h3 class="text-warning">${data.low_stock_items || 0}</h3>
                        <p class="mb-0">Low Stock Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-danger">
                    <div class="card-body">
                        <h3 class="text-danger">${data.out_of_stock_items || 0}</h3>
                        <p class="mb-0">Out of Stock</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h6>Stock Status Summary:</h6>
                <div class="alert alert-info">
                    This summary shows the overall consumable stock status across all medical services.
                    Use the individual service actions to manage specific consumable requirements.
                </div>
            </div>
        </div>
    `;
}

// Bulk stock check for selected services
function bulkStockCheck() {
    const selectedServices = $('.service-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedServices.length === 0) {
        alert('Please select at least one medical service to check stock for.');
        return;
    }
    
    alert(`Bulk stock check for ${selectedServices.length} selected medical services.\n\nThis feature will check stock availability for all consumables required by the selected services.`);
}
</script>
@endpush
