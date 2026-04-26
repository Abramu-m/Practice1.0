@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Locations Stock</h3>
                    <div class="card-tools">
                        <a href="{{ route('store.requisitions.create') }}" class="btn btn-warning w-100">
                            <i class="fas fa-clipboard-list"></i> New Requisition
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total_locations'] ?? 0 }}</h3>
                                    <p>Total Locations</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['total_medications'] ?? 0 }}</h3>
                                    <p>Unique Medications</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-pills"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['expiring_soon'] ?? 0 }}</h3>
                                    <p>Expiring Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['out_of_stock'] ?? 0 }}</h3>
                                    <p>Out of Stock</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('store-locations-stock.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="location_id" class="form-control">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="medication_id" class="form-control">
                                    <option value="">All Medications</option>
                                    @foreach($medications as $medication)
                                        <option value="{{ $medication->id }}" {{ request('medication_id') == $medication->id ? 'selected' : '' }}>
                                            {{ $medication->generic_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="depleted" {{ request('status') == 'depleted' ? 'selected' : '' }}>Depleted</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="expiry_status" class="form-control">
                                    <option value="">All Expiry Status</option>
                                    <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="expiring_soon" {{ request('expiry_status') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                                    <option value="valid" {{ request('expiry_status') == 'valid' ? 'selected' : '' }}>Valid</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('store-locations-stock.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Stock Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Medication</th>
                                    <th>Batch Number</th>
                                    <th>Manufacture Date</th>
                                    <th>Expiry Date</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                    <th>Requisition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockEntries as $entry)
                                    <tr>
                                        <td>
                                            <strong>{{ $entry->location->name ?? 'N/A' }}</strong>
                                            @if($entry->location->type)
                                                <br><small class="text-muted">{{ ucfirst($entry->location->type) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $entry->medication->generic_name ?? 'N/A' }}</strong>
                                            @if($entry->medication->brand_name)
                                                <br><small class="text-muted">{{ $entry->medication->brand_name }}</small>
                                            @endif
                                            @if($entry->medication->strength)
                                                <br><small class="text-muted">{{ $entry->medication->strength }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $entry->batch_number }}</code>
                                        </td>
                                        <td>
                                            @if($entry->manufacture_date)
                                                <small>{{ $entry->manufacture_date->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $isExpired = $entry->expiry_date->isPast();
                                                $isExpiringSoon = $entry->expiry_date->diffInDays() <= 30;
                                                $badgeClass = $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'success');
                                            @endphp
                                            <span class="badge bg-{{ $badgeClass }}">
                                                {{ $entry->expiry_date->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $entry->quantity > 0 ? 'success' : 'danger' }}">
                                                {{ number_format($entry->quantity) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                ${{ number_format($entry->unit_cost, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                ${{ number_format($entry->quantity * $entry->unit_cost, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'expired' => 'danger',
                                                    'depleted' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$entry->status] ?? 'secondary' }}">
                                                {{ ucfirst($entry->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($entry->requisition)
                                                <small>
                                                    <a href="#" class="text-info">
                                                        REQ-{{ str_pad($entry->requisition->id, 6, '0', STR_PAD_LEFT) }}
                                                    </a>
                                                </small>
                                            @else
                                                <small class="text-muted">Direct Entry</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('store-locations-stock.show', $entry->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($entry->quantity > 0)
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="showMovementModal({{ $entry->id }}, '{{ $entry->medication->generic_name }}', {{ $entry->quantity }})"
                                                            title="Create Movement">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="showHistoryModal({{ $entry->id }})"
                                                        title="View History">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No stock entries found.</p>
                                                <a href="{{ route('store.requisitions.create') }}" class="btn btn-warning w-100">
                                                    <i class="fas fa-clipboard-list"></i> New Requisition
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($stockEntries) && method_exists($stockEntries, 'links'))
                        {{ $stockEntries->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movement Modal -->
<div class="modal fade" id="movementModal" tabindex="-1" role="dialog" aria-labelledby="movementModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="movementForm" method="POST" action="{{ route('store-stock-movements.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="movementModalLabel">Create Stock Movement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="stock_id" id="movement_stock_id">
                    
                    <div class="mb-3">
                        <label>Medication:</label>
                        <p id="movement_medication_name" class="form-control-plaintext"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label>Available Quantity:</label>
                        <p id="movement_available_quantity" class="form-control-plaintext"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="movement_type">Movement Type:</label>
                        <select class="form-control" id="movement_type" name="movement_type" required>
                            <option value="">Select Movement Type</option>
                            <option value="out">Out (Dispensing/Usage)</option>
                            <option value="transfer">Transfer to Another Location</option>
                            <option value="adjustment">Stock Adjustment</option>
                            <option value="waste">Waste/Disposal</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="movement_quantity">Quantity:</label>
                        <input type="number" class="form-control" id="movement_quantity" name="quantity" min="1" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="movement_notes">Notes/Reason:</label>
                        <textarea class="form-control" id="movement_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Movement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Stock Movement History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading history...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showMovementModal(stockId, medicationName, availableQuantity) {
    document.getElementById('movement_stock_id').value = stockId;
    document.getElementById('movement_medication_name').textContent = medicationName;
    document.getElementById('movement_available_quantity').textContent = availableQuantity;
    document.getElementById('movement_quantity').max = availableQuantity;
    
    $('#movementModal').modal('show');
}

function showHistoryModal(stockId) {
    // Load movement history via AJAX
    document.getElementById('historyContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading history...</div>';
    $('#historyModal').modal('show');
    
    // You can implement AJAX call here to load movement history
    // For now, showing placeholder
    setTimeout(() => {
        document.getElementById('historyContent').innerHTML = '<p class="text-muted">Movement history will be loaded here.</p>';
    }, 1000);
}
</script>
@endsection
