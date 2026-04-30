@extends('layouts.app_main_layout')

@section('page_title', 'Stock Transfers')

@section('main_content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-exchange-alt text-primary me-2"></i>
                        Stock Transfers
                    </h1>
                    <p class="text-muted mb-0">Manage stock transfers between locations</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.stock.transfers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        New Transfer
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-exchange-alt fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $statistics['total_transfers'] }}</h3>
                    <p class="text-muted mb-0 small">Total Transfers</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $statistics['pending_transfers'] }}</h3>
                    <p class="text-muted mb-0 small">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $statistics['completed_transfers'] }}</h3>
                    <p class="text-muted mb-0 small">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $statistics['cancelled_transfers'] }}</h3>
                    <p class="text-muted mb-0 small">Cancelled</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $statistics['monthly_transfers'] }}</h3>
                    <p class="text-muted mb-0 small">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Tsh {{ number_format($statistics['total_value_transferred'], 2) }}</h3>
                    <p class="text-muted mb-0 small">Value Transferred</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.stock.transfers.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Transfer number, medication, notes..." 
                                       value="{{ $search }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">From Location</label>
                                <select name="from_location" class="form-select">
                                    <option value="all">All Locations</option>
                                    @foreach($storeLocations as $location)
                                    <option value="{{ $location->id }}" {{ $fromLocation == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">To Location</label>
                                <select name="to_location" class="form-select">
                                    <option value="all">All Locations</option>
                                    @foreach($storeLocations as $location)
                                    <option value="{{ $location->id }}" {{ $toLocation == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="form-label">From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="form-label">To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                            <div class="col-md-1 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('medications.stock.transfers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Transfers Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i>
                        Transfer Records
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($transfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Transfer #</th>
                                    <th>Medication</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Quantity</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $transfer)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $transfer->reference_number }}</div>
                                        <small class="text-muted">{{ $transfer->user->name ?? 'Unknown' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $transfer->medication->name ?? 'N/A' }}</div>
                                        @if($transfer->batch_number)
                                        <small class="text-muted">Batch: {{ $transfer->batch_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $transfer->fromLocation->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $transfer->toLocation->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($transfer->quantity, 2) }}</span>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($transfer->movement_date)->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($transfer->created_at)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($transfer->status ?? 'pending') {
                                                'pending' => 'bg-warning text-dark',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($transfer->status ?? 'Pending') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">Tsh {{ number_format($transfer->quantity * $transfer->unit_cost, 2) }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($transfer->status === 'pending')
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="processTransfer({{ $transfer->id }}, 'approve')"
                                                    title="Approve Transfer">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="processTransfer({{ $transfer->id }}, 'reject')"
                                                    title="Reject Transfer">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-info" 
                                                    onclick="viewTransferDetails({{ $transfer->id }})"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($transfers->hasPages())
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $transfers->firstItem() }} to {{ $transfers->lastItem() }} 
                                of {{ $transfers->total() }} transfers
                            </div>
                            {{ $transfers->links() }}
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No transfers found</h4>
                        <p class="text-muted">
                            @if(request()->hasAny(['search', 'status', 'from_location', 'to_location', 'date_from', 'date_to']))
                                No transfers match your current filters.
                                <a href="{{ route('medications.stock.transfers.index') }}" class="btn btn-link p-0">Clear filters</a>
                            @else
                                Start by creating your first stock transfer.
                            @endif
                        </p>
                        <a href="{{ route('medications.stock.transfers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Create Transfer
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Transfer Processing Modal --}}
<div class="modal fade" id="processTransferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processTransferModalLabel">Process Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="processTransferForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="transferId">
                    <input type="hidden" id="transferAction">
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Add processing notes (optional)"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="actionMessage"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="processBtn">Process</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function processTransfer(transferId, action) {
    document.getElementById('transferId').value = transferId;
    document.getElementById('transferAction').value = action;
    
    const modal = new bootstrap.Modal(document.getElementById('processTransferModal'));
    const processBtn = document.getElementById('processBtn');
    const actionMessage = document.getElementById('actionMessage');
    
    if (action === 'approve') {
        processBtn.className = 'btn btn-success';
        processBtn.textContent = 'Approve Transfer';
        actionMessage.textContent = 'This will approve the transfer and update stock levels at both locations.';
    } else {
        processBtn.className = 'btn btn-danger';
        processBtn.textContent = 'Reject Transfer';
        actionMessage.textContent = 'This will reject the transfer and no stock changes will be made.';
    }
    
    modal.show();
}

function viewTransferDetails(transferId) {
    // Implement view details functionality
    alert('Transfer details view not implemented yet');
}

document.getElementById('processTransferForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const transferId = document.getElementById('transferId').value;
    const action = document.getElementById('transferAction').value;
    const notes = e.target.notes.value;
    
    fetch(`/medications/stock/transfers/${transferId}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            action: action,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the transfer');
    });
});
</script>
@endpush
