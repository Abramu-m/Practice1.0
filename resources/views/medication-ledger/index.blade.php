@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Ledger</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-ledger.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Entry
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('medication-ledger.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="medication_id" class="form-control">
                                    <option value="">All Medications</option>
                                    @foreach($medications as $medication)
                                        <option value="{{ $medication->id }}" {{ request('medication_id') == $medication->id ? 'selected' : '' }}>
                                            {{ $medication->name }}
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
                                    <option value="quarantined" {{ request('status') == 'quarantined' ? 'selected' : '' }}>Quarantined</option>
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
                                <select name="quantity_status" class="form-control">
                                    <option value="">All Quantity Status</option>
                                    <option value="available" {{ request('quantity_status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="depleted" {{ request('quantity_status') == 'depleted' ? 'selected' : '' }}>Depleted</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('medication-ledger.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Ledger Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Batch Number</th>
                                    <th>GRN</th>
                                    <th>Received</th>
                                    <th>Dispensed</th>
                                    <th>Remaining</th>
                                    <th>Unit Cost</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerEntries as $entry)
                                    <tr>
                                        <td>
                                            <strong>{{ $entry->medication->name }}</strong>
                                            @if($entry->medication->generic_name)
                                                <br><small class="text-muted">{{ $entry->medication->generic_name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $entry->batch_number }}</code>
                                        </td>
                                        <td>
                                            <small>{{ $entry->goodsReceivedNote->grn_number ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-black">{{ number_format($entry->quantity_received) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ number_format($entry->quantity_dispensed) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-black badge bg-{{ $entry->quantity_remaining > 0 ? 'success' : 'danger' }}">
                                                {{ number_format($entry->quantity_remaining) }}
                                            </span>
                                        </td>
                                        <td>Tsh {{ number_format($entry->unit_cost, 2) }}</td>
                                        <td>
                                            <span class="text-black badge bg-{{ $entry->expiry_date->isPast() ? 'danger' : ($entry->expiry_date->diffInDays() <= 30 ? 'warning' : 'success') }}">
                                                {{ $entry->expiry_date->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-black badge bg-{{ $entry->status_badge_class }}">
                                                {{ ucfirst($entry->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('medication-ledger.show', $entry->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medication-ledger.edit', $entry->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($entry->quantity_remaining > 0)
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="showDispenseModal({{ $entry->id }}, '{{ $entry->medication->name }}', {{ $entry->quantity_remaining }})">
                                                        <i class="fas fa-prescription-bottle"></i>
                                                    </button>
                                                @endif
                                                <form action="{{ route('medication-ledger.destroy', $entry->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No ledger entries found.</p>
                                                <a href="{{ route('medication-ledger.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Add New Entry
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $ledgerEntries->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dispense Modal -->
<div class="modal fade" id="dispenseModal" tabindex="-1" role="dialog" aria-labelledby="dispenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="dispenseForm" method="POST" action="{{ route('medication-ledger.dispense') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="dispenseModalLabel">Dispense Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="medication_id" id="dispense_medication_id">
                    
                    <div class="mb-3">
                        <label>Medication:</label>
                        <p id="dispense_medication_name" class="form-control-plaintext"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label>Available Quantity:</label>
                        <p id="dispense_available_quantity" class="form-control-plaintext"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispense_quantity">Quantity to Dispense:</label>
                        <input type="text" class="form-control" id="dispense_quantity" name="quantity" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispense_reason">Reason:</label>
                        <input type="text" class="form-control" id="dispense_reason" name="reason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Dispense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDispenseModal(medicationId, medicationName, availableQuantity) {
    document.getElementById('dispense_medication_id').value = medicationId;
    document.getElementById('dispense_medication_name').textContent = medicationName;
    document.getElementById('dispense_available_quantity').textContent = availableQuantity;
    document.getElementById('dispense_quantity').max = availableQuantity;
    
    $('#dispenseModal').modal('show');
}
</script>
@endsection
