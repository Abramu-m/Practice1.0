@extends('layouts.app_main_layout')

@section('page_title', 'Stock Movements Audit Trail')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-history text-info me-2"></i>
                        Stock Movements Audit Trail
                    </h1>
                    <p class="text-muted mb-0">Comprehensive history of all stock movements and transactions</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    <button class="btn btn-primary" onclick="exportAuditTrail()">
                        <i class="fas fa-download me-2"></i>
                        Export Trail
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filter Audit Trail
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.reconciliation.audit-trail') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="medication_id" class="form-label">Medication</label>
                                <select name="medication_id" id="medication_id" class="form-select">
                                    <option value="">All Medications</option>
                                    @foreach($medications as $medication)
                                        <option value="{{ $medication->id }}" 
                                            {{ $request->medication_id == $medication->id ? 'selected' : '' }}>
                                            {{ $medication->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="location_id" class="form-label">Location</label>
                                <select name="location_id" id="location_id" class="form-select">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ $request->location_id == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="movement_type" class="form-label">Movement Type</label>
                                <select name="movement_type" id="movement_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="inward" {{ $request->movement_type == 'inward' ? 'selected' : '' }}>Inward</option>
                                    <option value="outward" {{ $request->movement_type == 'outward' ? 'selected' : '' }}>Outward</option>
                                    <option value="transfer" {{ $request->movement_type == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="adjustment" {{ $request->movement_type == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" 
                                       value="{{ $request->start_date }}">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" 
                                       value="{{ $request->end_date }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    Apply Filters
                                </button>
                                <a href="{{ route('medications.reconciliation.audit-trail') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Trail Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Movement History
                        @if(isset($auditTrail) && count($auditTrail) > 0)
                            <span class="badge bg-info ms-2">{{ count($auditTrail) }} records</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($auditTrail) && count($auditTrail) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Medication</th>
                                        <th>Batch</th>
                                        <th>Location</th>
                                        <th>Movement Type</th>
                                        <th>Quantity</th>
                                        <th>Running Balance</th>
                                        <th>Reference</th>
                                        <th>User</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditTrail as $movement)
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($movement['movement_date'] ?? $movement['created_at'] ?? now())->format('M d, Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong>{{ $movement['medication_name'] ?? $movement['medication'] ?? 'Unknown' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $movement['batch_number'] ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $movement['location_name'] ?? $movement['location'] ?? 'Unknown' }}</td>
                                            <td>
                                                @php
                                                    $type = $movement['transaction_type'] ?? $movement['movement_type'] ?? 'unknown';
                                                    $badgeClass = match($type) {
                                                        'inward', 'received' => 'bg-success',
                                                        'outward', 'dispensed' => 'bg-danger',
                                                        'transfer' => 'bg-warning',
                                                        'adjustment' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($type) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $quantity = $movement['quantity'] ?? 0;
                                                    $quantityClass = $quantity > 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $quantityClass }}">
                                                    {{ $quantity > 0 ? '+' : '' }}{{ $quantity }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $movement['running_balance'] ?? $movement['balance_after'] ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if(isset($movement['reference_number']))
                                                    <small class="text-muted">{{ $movement['reference_number'] }}</small>
                                                @else
                                                    <small class="text-muted">—</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $movement['user_name'] ?? $movement['created_by'] ?? 'System' }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if(isset($movement['id']))
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="viewMovementDetails({{ $movement['id'] }})"
                                                                title="View details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                    @if(isset($movement['can_reverse']) && $movement['can_reverse'])
                                                        <button type="button" class="btn btn-outline-warning" 
                                                                onclick="reverseMovement({{ $movement['id'] }})"
                                                                title="Reverse movement">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination would go here if implemented --}}
                        
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h4>No Movement History Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['medication_id', 'location_id', 'movement_type', 'start_date', 'end_date']))
                                    No stock movements match your current filter criteria.
                                @else
                                    No stock movements have been recorded yet.
                                @endif
                            </p>
                            @if(request()->hasAny(['medication_id', 'location_id', 'movement_type', 'start_date', 'end_date']))
                                <a href="{{ route('medications.reconciliation.audit-trail') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-2"></i>
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Movement Details Modal --}}
<div class="modal fade" id="movementDetailsModal" tabindex="-1" aria-labelledby="movementDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="movementDetailsModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    Movement Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="movementDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function exportAuditTrail() {
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('export', 'pdf');
    window.open(`{{ route('medications.reconciliation.export-report') }}?report_type=audit&format=pdf&${currentParams.toString()}`, '_blank');
}

function viewMovementDetails(movementId) {
    $('#movementDetailsModal').modal('show');
    
    // Load movement details via AJAX
    fetch(`/medications/reconciliation/movement/${movementId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('movementDetailsContent').innerHTML = formatMovementDetails(data.movement);
            } else {
                document.getElementById('movementDetailsContent').innerHTML = 
                    '<div class="alert alert-danger">Error loading movement details.</div>';
            }
        })
        .catch(error => {
            document.getElementById('movementDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Error loading movement details.</div>';
        });
}

function formatMovementDetails(movement) {
    return `
        <div class="row g-3">
            <div class="col-md-6">
                <strong>Movement ID:</strong> ${movement.id || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Date:</strong> ${movement.movement_date || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Medication:</strong> ${movement.medication_name || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Batch Number:</strong> ${movement.batch_number || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Location:</strong> ${movement.location_name || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Movement Type:</strong> ${movement.transaction_type || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Quantity:</strong> ${movement.quantity || '0'}
            </div>
            <div class="col-md-6">
                <strong>Balance After:</strong> ${movement.balance_after || 'N/A'}
            </div>
            <div class="col-12">
                <strong>Reference:</strong> ${movement.reference_number || 'No reference'}
            </div>
            <div class="col-12">
                <strong>Notes:</strong> ${movement.notes || 'No notes available'}
            </div>
        </div>
    `;
}

function reverseMovement(movementId) {
    if (confirm('Are you sure you want to reverse this movement? This action cannot be undone.')) {
        // Implement movement reversal
        console.log('Reversing movement:', movementId);
        alert('Movement reversal feature to be implemented.');
    }
}

// Auto-set end date when start date is selected
document.getElementById('start_date').addEventListener('change', function() {
    const endDateField = document.getElementById('end_date');
    if (!endDateField.value && this.value) {
        endDateField.value = this.value;
    }
});
</script>
@endsection
