@extends('layouts.app_main_layout')

@section('page_title', 'GRN Management')

@section('main_content')
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
                    <a href="{{ route('medications.stock.grn.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        New GRN
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Total GRNs</div>
                    <h4 class="mb-0 text-primary">{{ $statistics['total_grns'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Drafted</div>
                    <h4 class="mb-0 text-warning">{{ $statistics['draft_grns'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Received</div>
                    <h4 class="mb-0 text-info">{{ $statistics['received_grns'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Verified</div>
                    <h4 class="mb-0 text-success">{{ $statistics['verified_grns'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Rejected</div>
                    <h4 class="mb-0 text-danger">{{ $statistics['rejected_grns'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Posted</div>
                    <h4 class="mb-0 text-primary">{{ $statistics['posted_grns'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Total Value</div>
                    <h4 class="mb-0 text-success">${{ number_format($statistics['total_value'] ?? 0, 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.stock.grn.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="GRN number, invoice, supplier..." 
                                   value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ ($status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="verified" {{ ($status ?? '') == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="approved" {{ ($status ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ ($status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Supplier</label>
                            <select name="supplier" class="form-select">
                                <option value="all">All Suppliers</option>
                                @foreach($suppliers ?? [] as $supplierOption)
                                <option value="{{ $supplierOption->id }}" {{ ($supplier ?? '') == $supplierOption->id ? 'selected' : '' }}>
                                    {{ $supplierOption->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                                <a href="{{ route('medications.stock.grn.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
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
                    <span class="badge bg-primary">{{ $grns->total() ?? 0 }} GRNs</span>
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
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grns ?? [] as $grn)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $grn->grn_number ?? 'N/A' }}</div>
                                            <small class="text-muted">ID: {{ $grn->id }}</small>
                                            @if($grn->invoice_number)
                                            <br><small class="badge bg-light text-dark">Invoice: {{ $grn->invoice_number }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $grn->supplier->name ?? 'Unknown' }}</div>
                                            @if($grn->supplier->contact_person ?? false)
                                            <small class="text-muted">{{ $grn->supplier->contact_person }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div>{{ $grn->received_at ? \Carbon\Carbon::parse($grn->received_at)->format('M d, Y') : 'N/A' }}</div>
                                            <small class="text-muted">{{ $grn->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $grn->items->count() ?? 0 }}</span>
                                        <br><small class="text-muted">medications</small>
                                    </td>
                                    <td>
                                        @if($grn->total_amount)
                                        <span class="fw-bold">${{ number_format($grn->total_amount, 2) }}</span>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($grn->status) {
                                                'pending' => 'bg-warning text-dark',
                                                'verified' => 'bg-info',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($grn->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('medications.stock.grn.show', $grn->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($grn->status === 'pending')
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="verifyGrn({{ $grn->id }})" 
                                                    title="Verify GRN">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                            
                                            @if($grn->status === 'verified')
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="approveGrn({{ $grn->id }})" 
                                                    title="Approve GRN">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-truck text-muted fa-3x mb-3"></i>
                                        <h5 class="text-muted">No GRNs Found</h5>
                                        <p class="text-muted">No goods received notes match your current filters.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if(isset($grns) && $grns->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $grns->links() }}
                </div>
                @endif
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

function verifyGrn(grnId) {
    if (confirm('Verify this GRN? This will mark it as verified and ready for approval.')) {
        // Redirect to verification page
        window.location.href = `/medications/stock/grn/${grnId}/verify`;
    }
}

function approveGrn(grnId) {
    if (confirm('Approve this GRN? This will update stock levels for all included medications.')) {
        // Submit approval request
        fetch(`/medications/stock/grn/${grnId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('GRN approved successfully', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast('Approval failed', 'error');
            }
        })
        .catch(error => {
            showToast('Error approving GRN', 'error');
        });
    }
}

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
