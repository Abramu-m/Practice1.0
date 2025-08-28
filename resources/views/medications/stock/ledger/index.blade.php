@extends('layouts.app_main_layout')

@section('page_title', 'Medication Ledger')

@section('styles')
<style>
    .ledger-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .ledger-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .status-active { background-color: #28a745; }
    .status-expired { background-color: #dc3545; }
    .status-damaged { background-color: #fd7e14; }
    .status-disposed { background-color: #6c757d; }
    
    .expiry-warning { color: #fd7e14; font-weight: 600; }
    .expiry-danger { color: #dc3545; font-weight: 600; }
    .expiry-safe { color: #28a745; font-weight: 600; }
    
    .batch-info {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        display: inline-block;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #8b4513;
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        color: #721c24;
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #155724;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-book text-primary me-2"></i>
                        Medication Ledger
                    </h1>
                    <p class="text-muted mb-0">Track all medication inventory movements and batch details</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.stock.ledger.stock-summary') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>
                        Stock Summary
                    </a>
                    <a href="{{ route('medications.stock.ledger.expiry-report') }}" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Expiry Report
                    </a>
                    <a href="{{ route('medications.stock.ledger.export', request()->query()) }}" class="btn btn-outline-success">
                        <i class="fas fa-download me-2"></i>
                        Export CSV
                    </a>
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Summary --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Total Entries</h6>
                        <h3 class="mb-0">{{ number_format($statistics['total_entries']) }}</h3>
                    </div>
                    <i class="fas fa-list fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Active Stock</h6>
                        <h3 class="mb-0">{{ number_format($statistics['active_entries']) }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Expiring Soon</h6>
                        <h3 class="mb-0">{{ number_format($statistics['expiring_soon']) }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Expired</h6>
                        <h3 class="mb-0">{{ number_format($statistics['expired_entries']) }}</h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Stats Row --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Total Quantity</div>
                    <h4 class="mb-0 text-info">{{ number_format($statistics['total_quantity']) }}</h4>
                    <small class="text-muted">units</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Total Value</div>
                    <h4 class="mb-0 text-success">${{ number_format($statistics['total_value'], 2) }}</h4>
                    <small class="text-muted">inventory value</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Medications</div>
                    <h4 class="mb-0 text-primary">{{ number_format($statistics['unique_medications']) }}</h4>
                    <small class="text-muted">unique items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Batches</div>
                    <h4 class="mb-0 text-warning">{{ number_format($statistics['unique_batches']) }}</h4>
                    <small class="text-muted">total batches</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-section">
        <form method="GET" action="{{ route('medications.stock.ledger.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Medication, batch, GRN..." 
                       value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Medication</label>
                <select name="medication_id" class="form-select">
                    <option value="all" {{ ($medication_id ?? 'all') == 'all' ? 'selected' : '' }}>All Medications</option>
                    @foreach($medications as $medication)
                    <option value="{{ $medication->id }}" {{ ($medication_id ?? '') == $medication->id ? 'selected' : '' }}>
                        {{ $medication->generic_name }} {{ $medication->strength ? '(' . $medication->strength . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ ($status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ ($status ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="damaged" {{ ($status ?? '') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="disposed" {{ ($status ?? '') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Location</label>
                <select name="location_id" class="form-select">
                    <option value="all" {{ ($location_id ?? 'all') == 'all' ? 'selected' : '' }}>All Locations</option>
                    @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ ($location_id ?? '') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Expiry Status</label>
                <select name="expiry_status" class="form-select">
                    <option value="all" {{ ($expiry_status ?? 'all') == 'all' ? 'selected' : '' }}>All Items</option>
                    <option value="expired" {{ ($expiry_status ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="expiring_soon" {{ ($expiry_status ?? '') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                    <option value="valid" {{ ($expiry_status ?? '') == 'valid' ? 'selected' : '' }}>Valid</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('medications.stock.ledger.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
            
            {{-- Additional Filter Row --}}
            <div class="col-md-2">
                <label class="form-label">GRN</label>
                <select name="grn_id" class="form-select">
                    <option value="all" {{ ($grn_id ?? 'all') == 'all' ? 'selected' : '' }}>All GRNs</option>
                    @foreach($grns as $grn)
                    <option value="{{ $grn->id }}" {{ ($grn_id ?? '') == $grn->id ? 'selected' : '' }}>
                        {{ $grn->grn_number }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" class="form-control" name="date_from" value="{{ $date_from ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" class="form-control" name="date_to" value="{{ $date_to ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select name="sort_by" class="form-select">
                    <option value="created_at" {{ ($sort_by ?? 'created_at') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                    <option value="expiry_date" {{ ($sort_by ?? '') == 'expiry_date' ? 'selected' : '' }}>Expiry Date</option>
                    <option value="quantity_received" {{ ($sort_by ?? '') == 'quantity_received' ? 'selected' : '' }}>Quantity</option>
                    <option value="unit_cost" {{ ($sort_by ?? '') == 'unit_cost' ? 'selected' : '' }}>Unit Cost</option>
                    <option value="batch_number" {{ ($sort_by ?? '') == 'batch_number' ? 'selected' : '' }}>Batch Number</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Order</label>
                <select name="sort_order" class="form-select">
                    <option value="desc" {{ ($sort_order ?? 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ ($sort_order ?? '') == 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Per Page</label>
                <select name="per_page" class="form-select">
                    <option value="15" {{ (request('per_page') ?? 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ (request('per_page') ?? '') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ (request('per_page') ?? '') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (request('per_page') ?? '') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Ledger Entries --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i>
                        Ledger Entries
                    </h5>
                    <span class="badge bg-primary">{{ $ledgerEntries->total() }} entries</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Medication</th>
                                    <th>Batch Info</th>
                                    <th>GRN Details</th>
                                    <th>Quantity & Cost</th>
                                    <th>Expiry Status</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $entry->medication->generic_name ?? 'N/A' }}</div>
                                            @if($entry->medication && $entry->medication->brand_name)
                                                <small class="text-muted">{{ $entry->medication->brand_name }}</small>
                                            @endif
                                            @if($entry->medication && $entry->medication->strength)
                                                <br><span class="badge bg-light text-dark">{{ $entry->medication->strength }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="batch-info">{{ $entry->batch_number }}</div>
                                        @if($entry->manufacture_date)
                                            <small class="text-muted d-block">Mfg: {{ \Carbon\Carbon::parse($entry->manufacture_date)->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $entry->grn->grn_number ?? 'N/A' }}</div>
                                            @if($entry->grn && $entry->grn->grn_date)
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($entry->grn->grn_date)->format('M d, Y') }}</small>
                                            @endif
                                            @if($entry->grn && $entry->grn->supplier)
                                                <br><small class="badge bg-info">{{ $entry->grn->supplier->name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-bold">{{ number_format($entry->quantity_received) }} units</span>
                                            <br><span class="text-muted">${{ number_format($entry->unit_cost, 2) }}/unit</span>
                                            <br><small class="fw-medium text-success">Total: ${{ number_format($entry->quantity_received * $entry->unit_cost, 2) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $expiryDate = \Carbon\Carbon::parse($entry->expiry_date);
                                            $now = \Carbon\Carbon::now();
                                            $daysDiff = (int) $now->diffInDays($expiryDate, false);
                                            
                                            if ($daysDiff < 0) {
                                                $expiryClass = 'expiry-danger';
                                                $expiryText = 'Expired ' . abs($daysDiff) . ' days ago';
                                            } elseif ($daysDiff <= 30) {
                                                $expiryClass = 'expiry-danger';
                                                $expiryText = 'Expires in ' . $daysDiff . ' days';
                                            } elseif ($daysDiff <= 180) {
                                                $expiryClass = 'expiry-warning';
                                                $expiryText = 'Expires in ' . $daysDiff . ' days';
                                            } else {
                                                $expiryClass = 'expiry-safe';
                                                $expiryText = 'Valid (' . $daysDiff . ' days)';
                                            }
                                        @endphp
                                        <div class="{{ $expiryClass }}">
                                            {{ $expiryDate->format('M d, Y') }}
                                            <br><small>{{ $expiryText }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $entry->location->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge status-badge status-{{ $entry->status }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('medications.stock.ledger.show', $entry) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($entry->status === 'active')
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="updateStatus({{ $entry->id }}, 'expired')" 
                                                        title="Mark as Expired">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="updateStatus({{ $entry->id }}, 'damaged')" 
                                                        title="Mark as Damaged">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-book text-muted fa-3x mb-3"></i>
                                        <h5 class="text-muted">No Ledger Entries Found</h5>
                                        <p class="text-muted">No medication ledger entries match your current filters.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($ledgerEntries->hasPages())
                <div class="card-footer bg-white">
                    {{ $ledgerEntries->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Entry Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <input type="hidden" id="entryId">
                    <input type="hidden" id="newStatus">
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="statusNotes" rows="3" 
                                  placeholder="Add notes about this status change..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action will update the status of this ledger entry. Please confirm your decision.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateStatus(entryId, status) {
    document.getElementById('entryId').value = entryId;
    document.getElementById('newStatus').value = status;
    document.getElementById('statusNotes').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const entryId = document.getElementById('entryId').value;
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    
    fetch(`/medications/stock/ledger/${entryId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Status updated successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Failed to update status', 'error');
        }
    })
    .catch(error => {
        showToast('Error updating status', 'error');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
});

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}
</script>
@endsection
