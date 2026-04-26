@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Stock Movements</h3>
                    <div class="card-tools">
                        <a href="{{ route('store-stock-movements.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Movement
                        </a>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportMovements()">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $stats['total_movements'] ?? 0 }}</h3>
                                    <p>Total Movements</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['movements_today'] ?? 0 }}</h3>
                                    <p>Today's Movements</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>${{ number_format($stats['total_value'] ?? 0, 2) }}</h3>
                                    <p>Total Value Moved</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['pending_transfers'] ?? 0 }}</h3>
                                    <p>Pending Transfers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('store-stock-movements.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="location_id" class="form-control">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
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
                                <select name="movement_type" class="form-control">
                                    <option value="">All Movement Types</option>
                                    <option value="in" {{ request('movement_type') == 'in' ? 'selected' : '' }}>Stock In</option>
                                    <option value="out" {{ request('movement_type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                                    <option value="transfer" {{ request('movement_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="adjustment" {{ request('movement_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                    <option value="waste" {{ request('movement_type') == 'waste' ? 'selected' : '' }}>Waste</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="transaction_type" class="form-control">
                                    <option value="">All Transaction Types</option>
                                    <option value="purchase" {{ request('transaction_type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                                    <option value="dispensing" {{ request('transaction_type') == 'dispensing' ? 'selected' : '' }}>Dispensing</option>
                                    <option value="requisition" {{ request('transaction_type') == 'requisition' ? 'selected' : '' }}>Requisition</option>
                                    <option value="transfer" {{ request('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                    <option value="waste" {{ request('transaction_type') == 'waste' ? 'selected' : '' }}>Waste</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <input type="text" name="reference_number" class="form-control" value="{{ request('reference_number') }}" placeholder="Reference Number">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="batch_number" class="form-control" value="{{ request('batch_number') }}" placeholder="Batch Number">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('store-stock-movements.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Movements Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Transaction</th>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th>From/To</th>
                                    <th>Batch</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                    <th>Balance</th>
                                    <th>Reference</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>
                                            <small>{{ $movement->movement_date->format('M d, Y') }}</small>
                                            <br><small class="text-muted">{{ $movement->movement_date->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'in' => 'success',
                                                    'out' => 'danger',
                                                    'transfer' => 'info',
                                                    'adjustment' => 'warning',
                                                    'waste' => 'dark'
                                                ];
                                                $typeIcons = [
                                                    'in' => 'arrow-down',
                                                    'out' => 'arrow-up',
                                                    'transfer' => 'exchange-alt',
                                                    'adjustment' => 'edit',
                                                    'waste' => 'trash'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$movement->movement_type] ?? 'secondary' }}">
                                                <i class="fas fa-{{ $typeIcons[$movement->movement_type] ?? 'question' }}"></i>
                                                {{ ucfirst($movement->movement_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ ucfirst(str_replace('_', ' ', $movement->transaction_type)) }}</small>
                                        </td>
                                        <td>
                                            @if($movement->item_type === 'medication' && $movement->medication)
                                                <strong>{{ $movement->medication->generic_name }}</strong>
                                                @if($movement->medication->brand_name)
                                                    <br><small class="text-muted">{{ $movement->medication->brand_name }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ ucfirst($movement->item_type) }} #{{ $movement->item_id }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $movement->storeLocation->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if($movement->movement_type === 'transfer')
                                                <small>
                                                    From: {{ $movement->fromLocation->name ?? 'N/A' }}<br>
                                                    To: {{ $movement->toLocation->name ?? 'N/A' }}
                                                </small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($movement->batch_number)
                                                <code>{{ $movement->batch_number }}</code>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $movement->movement_type === 'in' ? 'success' : 'danger' }}">
                                                {{ $movement->movement_type === 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($movement->unit_cost, 2) }}</td>
                                        <td><strong>${{ number_format($movement->total_cost, 2) }}</strong></td>
                                        <td>
                                            <small>
                                                Before: {{ number_format($movement->balance_before) }}<br>
                                                After: <strong>{{ number_format($movement->balance_after) }}</strong>
                                            </small>
                                        </td>
                                        <td>
                                            @if($movement->reference_number)
                                                <small>{{ $movement->reference_number }}</small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $movement->createdBy->first_name ?? '' }} {{ $movement->createdBy->last_name ?? '' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('store-stock-movements.show', $movement->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($movement->notes)
                                                    <button type="button" class="btn btn-sm btn-secondary" 
                                                            onclick="showNotesModal('{{ addslashes($movement->notes) }}')"
                                                            title="View Notes">
                                                        <i class="fas fa-sticky-note"></i>
                                                    </button>
                                                @endif
                                                @if($movement->movement_type === 'transfer' && $movement->transaction_type === 'transfer')
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="showReverseModal({{ $movement->id }})"
                                                            title="Reverse Movement">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No stock movements found.</p>
                                                <a href="{{ route('store-stock-movements.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Add Movement
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($movements) && method_exists($movements, 'links'))
                        {{ $movements->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Movement Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="notesContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reverse Movement Modal -->
<div class="modal fade" id="reverseModal" tabindex="-1" role="dialog" aria-labelledby="reverseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="reverseForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reverseModalLabel">Reverse Movement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This will create a reverse movement to undo this transaction. This action cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reverse_reason">Reason for Reversal:</label>
                        <textarea class="form-control" id="reverse_reason" name="reason" rows="3" required placeholder="Please provide a reason for reversing this movement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reverse Movement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showNotesModal(notes) {
    document.getElementById('notesContent').textContent = notes;
    $('#notesModal').modal('show');
}

function showReverseModal(movementId) {
    document.getElementById('reverseForm').action = `/store-stock-movements/${movementId}/reverse`;
    $('#reverseModal').modal('show');
}

function exportMovements() {
    // Get current filter parameters
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    
    // Create download link
    window.location.href = `{{ route('store-stock-movements.index') }}?${params.toString()}`;
}
</script>
@endsection
