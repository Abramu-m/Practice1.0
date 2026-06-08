@extends('layouts.app_main_layout')

@section('page_title', 'GRN Management')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-truck text-primary me-2"></i>
                        Goods Received Notes (GRN) Management
                    </h1>
                    <p class="text-muted mb-0">Process and verify incoming medication deliveries</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshGrnData()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-clock text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Verification</h6>
                            <h4 class="mb-0 text-warning" id="pending-verification">{{ $statusCounts['pending_verification'] ?? 0 }}</h4>
                            <small class="text-muted">Need attention</small>
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
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Verified</h6>
                            <h4 class="mb-0 text-info" id="verified-count">{{ $statusCounts['verified'] ?? 0 }}</h4>
                            <small class="text-muted">Ready for approval</small>
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
                            <div class="bg-success bg-gradient rounded-3 p-3">
                                <i class="fas fa-thumbs-up text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h4 class="mb-0 text-success" id="approved-count">{{ $statusCounts['approved'] ?? 0 }}</h4>
                            <small class="text-muted">Stock updated</small>
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
                            <h6 class="text-muted mb-1">Rejected</h6>
                            <h4 class="mb-0 text-danger" id="rejected-count">{{ $statusCounts['rejected'] ?? 0 }}</h4>
                            <small class="text-muted">Returned/discrepancy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="grn-filter-form" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search GRNs</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="GRN number, supplier..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending_verification" {{ request('status') == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="supplier" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier" name="supplier_id">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date-from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date-from" name="date_from" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="date-to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date-to" name="date_to" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex flex-column gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- GRN List --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i>
                        Goods Received Notes
                    </h5>
                    <span class="badge bg-light text-dark">
                        Total: {{ count($grns ?? []) }} GRNs
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>GRN Details</th>
                                    <th>Supplier</th>
                                    <th>Date Received</th>
                                    <th>Items</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grns ?? [] as $grn)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $grn['grn_number'] ?? 'N/A' }}</div>
                                            <small class="text-muted">ID: {{ $grn['id'] ?? 'N/A' }}</small>
                                            @if($grn['reference_number'] ?? false)
                                            <br><small class="badge bg-light text-dark">Ref: {{ $grn['reference_number'] }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $grn['supplier_name'] ?? 'Unknown' }}</div>
                                            @if($grn['supplier_contact'] ?? false)
                                            <small class="text-muted">{{ $grn['supplier_contact'] }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div>{{ $grn['received_date'] ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $grn['received_time'] ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $grn['items_count'] ?? 0 }}</span>
                                        <br><small class="text-muted">medications</small>
                                    </td>
                                    <td>
                                        @if($grn['total_value'] ?? false)
                                        <span class="fw-bold">Tsh {{ number_format($grn['total_value'], 2) }}</span>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = $grn['status'] ?? 'unknown';
                                            $badgeClass = match($status) {
                                                'pending_verification' => 'bg-warning text-dark',
                                                'verified' => 'bg-info',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            $statusText = match($status) {
                                                'pending_verification' => 'Pending Verification',
                                                'verified' => 'Verified',
                                                'approved' => 'Approved',
                                                'rejected' => 'Rejected',
                                                default => 'Unknown'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                        @if($grn['priority'] ?? false)
                                        <br><small class="badge bg-danger mt-1">{{ ucfirst($grn['priority']) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('medications.stock.grn.show', $grn['id']) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($status === 'pending_verification')
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="verifyGrn({{ $grn['id'] }})" 
                                                    title="Verify GRN">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                            
                                            @if($status === 'verified')
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="approveGrn({{ $grn['id'] }})" 
                                                    title="Approve GRN">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="rejectGrn({{ $grn['id'] }})" 
                                                    title="Reject GRN">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('medications.stock.grn.show', $grn['id']) }}">
                                                            <i class="fas fa-eye me-2"></i>
                                                            View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="printGrn({{ $grn['id'] }})">
                                                            <i class="fas fa-print me-2"></i>
                                                            Print GRN
                                                        </a>
                                                    </li>
                                                    @if($status === 'approved')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="viewStockUpdate({{ $grn['id'] }})">
                                                            <i class="fas fa-warehouse me-2"></i>
                                                            View Stock Update
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-truck text-muted fa-3x mb-3"></i>
                                        <p class="text-muted mb-0">No GRNs found matching your criteria</p>
                                        <small class="text-muted">Try adjusting your filters or search terms</small>
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
</div>

{{-- Verification Modal --}}
<div class="modal fade" id="verifyGrnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-warning me-2"></i>
                    Verify GRN
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="verify-grn-form">
                    <input type="hidden" id="verify-grn-id">
                    <div class="mb-3">
                        <label for="verify-notes" class="form-label">Verification Notes</label>
                        <textarea class="form-control" id="verify-notes" rows="3" 
                                  placeholder="Add any notes about the verification..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirm-verification" required>
                        <label class="form-check-label" for="confirm-verification">
                            I confirm that I have physically verified all items in this GRN
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitVerification()">
                    <i class="fas fa-check me-2"></i>
                    Verify GRN
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Approval Modal --}}
<div class="modal fade" id="approveGrnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-thumbs-up text-success me-2"></i>
                    Approve GRN
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approve-grn-form">
                    <input type="hidden" id="approve-grn-id">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Approving this GRN will automatically update stock levels for all included medications.
                    </div>
                    <div class="mb-3">
                        <label for="approve-notes" class="form-label">Approval Notes</label>
                        <textarea class="form-control" id="approve-notes" rows="3" 
                                  placeholder="Add any notes about the approval..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirm-approval" required>
                        <label class="form-check-label" for="confirm-approval">
                            I confirm that this GRN is ready for approval and stock update
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitApproval()">
                    <i class="fas fa-thumbs-up me-2"></i>
                    Approve GRN
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshGrnData() {
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function clearFilters() {
    document.getElementById('grn-filter-form').reset();
    window.location.href = '{{ route("medications.stock.grn.index") }}';
}

function verifyGrn(grnId) {
    document.getElementById('verify-grn-id').value = grnId;
    new bootstrap.Modal(document.getElementById('verifyGrnModal')).show();
}

function approveGrn(grnId) {
    document.getElementById('approve-grn-id').value = grnId;
    new bootstrap.Modal(document.getElementById('approveGrnModal')).show();
}

function rejectGrn(grnId) {
    if (confirm('Are you sure you want to reject this GRN? This action cannot be undone.')) {
        // Handle rejection logic
        alert('GRN rejection functionality to be implemented');
    }
}

function submitVerification() {
    const grnId = document.getElementById('verify-grn-id').value;
    const notes = document.getElementById('verify-notes').value;
    const confirmed = document.getElementById('confirm-verification').checked;
    
    if (!confirmed) {
        alert('Please confirm that you have verified the GRN');
        return;
    }
    
    // Submit verification
    fetch(`{{ route('medications.stock.grn.verify', ':id') }}`.replace(':id', grnId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('verifyGrnModal')).hide();
            showToast('GRN verified successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Verification failed', 'error');
        }
    })
    .catch(error => {
        showToast('Error verifying GRN', 'error');
    });
}

function submitApproval() {
    const grnId = document.getElementById('approve-grn-id').value;
    const notes = document.getElementById('approve-notes').value;
    const confirmed = document.getElementById('confirm-approval').checked;
    
    if (!confirmed) {
        alert('Please confirm the approval');
        return;
    }
    
    // Submit approval
    fetch(`{{ route('medications.stock.grn.approve', ':id') }}`.replace(':id', grnId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('approveGrnModal')).hide();
            showToast('GRN approved successfully. Stock levels updated.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Approval failed', 'error');
        }
    })
    .catch(error => {
        showToast('Error approving GRN', 'error');
    });
}

function printGrn(grnId) {
    window.open(`{{ route('medications.stock.grn.show', ':id') }}`.replace(':id', grnId) + '?print=1', '_blank');
}

function viewStockUpdate(grnId) {
    // Navigate to stock movements for this GRN
    window.location.href = `{{ route('medications.reports.movements') }}?grn_id=${grnId}`;
}

function showToast(message, type = 'info') {
    // Toast implementation (same as in dashboard)
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

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('#grn-filter-form select, #grn-filter-form input[type="date"]');
    selects.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('grn-filter-form').submit();
        });
    });
});
</script>
@endsection
