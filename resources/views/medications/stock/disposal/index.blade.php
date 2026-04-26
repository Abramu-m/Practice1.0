@extends('layouts.app_main_layout')

@section('page_title', 'Medication Disposal')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Medication Disposal</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('medications.index') }}">Medications</a></li>
                        <li class="breadcrumb-item active">Disposal</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $statistics['total_disposals'] ?? 0 }}</h3>
                            <p>Total Disposals</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $statistics['pending_verification'] ?? 0 }}</h3>
                            <p>Pending Verification</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $statistics['verified_disposals'] ?? 0 }}</h3>
                            <p>Verified/Completed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($statistics['total_quantity_disposed'] ?? 0) }}</h3>
                            <p>Total Quantity Disposed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pills"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Disposal Records Card -->
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="card-title">Unfit Medication Disposal Records</h3>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('medications.stock.ledger.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Dispose More Items
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="search">Search</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           value="{{ $search }}" placeholder="Reference, medication, reason...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="status">Verification Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                                        <option value="pending_verification" {{ $status == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                                        <option value="verified" {{ $status == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="no_verification" {{ $status == 'no_verification' ? 'selected' : '' }}>No Verification Required</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="reason">Reason</label>
                                    <select name="reason" id="reason" class="form-control">
                                        <option value="all" {{ $reason == 'all' ? 'selected' : '' }}>All Reasons</option>
                                        @foreach($disposalReasons ?? [] as $key => $value)
                                            <option value="{{ $key }}" {{ $reason == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="date_from">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="date_to">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="mb-3">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="mb-3">
                                    <label>&nbsp;</label>
                                    <a href="{{ route('medications.stock.disposal.index') }}" class="btn btn-secondary w-100">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Disposal Records Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Date Disposed</th>
                                    <th>Medication</th>
                                    <th>Batch/Expiry</th>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>Disposed By</th>
                                    <th>Verification</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($disposals as $disposal)
                                    <tr>
                                        <td>
                                            <strong>{{ $disposal->reference_number ?? 'UNF-' . str_pad($disposal->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                        </td>
                                        <td>
                                            {{ $disposal->disposed_at ? \Carbon\Carbon::parse($disposal->disposed_at)->format('M d, Y H:i') : 'N/A' }}
                                        </td>
                                        <td>
                                            <strong>{{ $disposal->medication->generic_name ?? 'N/A' }}</strong>
                                            @if($disposal->medication && $disposal->medication->brand_name)
                                                <br><small class="text-muted">{{ $disposal->medication->brand_name }}</small>
                                            @endif
                                            @if($disposal->medication && $disposal->medication->strength)
                                                <br><small class="text-muted">{{ $disposal->medication->strength }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($disposal->batch_number)
                                                <strong>{{ $disposal->batch_number }}</strong>
                                            @endif
                                            @if($disposal->expiry_date)
                                                <br><small class="text-muted">Exp: {{ \Carbon\Carbon::parse($disposal->expiry_date)->format('M Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ number_format($disposal->quantity_discarded) }}</strong>
                                            @if($disposal->unit_cost)
                                                <br><small class="text-muted">${{ number_format($disposal->unit_cost, 2) }} each</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $disposal->reason ?? 'unknown')) }}</span>
                                        </td>
                                        <td>
                                            {{ $disposal->disposedBy->name ?? 'System' }}
                                            <br><small class="text-muted">{{ $disposal->disposed_at ? \Carbon\Carbon::parse($disposal->disposed_at)->format('H:i') : '' }}</small>
                                        </td>
                                        <td>
                                            @if($disposal->verification_required)
                                                @if($disposal->verified_by)
                                                    <span class="badge bg-success">Verified</span>
                                                    <br><small class="text-muted">by {{ $disposal->verifiedBy->name ?? 'Unknown' }}</small>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info" onclick="viewDisposal({{ $disposal->id }})" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($disposal->verification_required && !$disposal->verified_by)
                                                <button class="btn btn-success" onclick="verifyDisposal({{ $disposal->id }})" title="Verify">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <h5>No disposal records found</h5>
                                            <p>Try adjusting your search criteria or process new disposals.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($disposals) && $disposals->hasPages())
                        <div class="row mt-3">
                            <div class="col-sm-5">
                                <div class="dataTables_info">
                                    Showing {{ $disposals->firstItem() }} to {{ $disposals->lastItem() }} of {{ $disposals->total() }} results
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="float-end">
                                    {{ $disposals->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Disposal Reasons Breakdown Card -->
            @if(!empty($statistics['reasons_breakdown']))
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Disposal Reasons Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($statistics['reasons_breakdown'] as $reason => $count)
                                <div class="col-md-3 col-6">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ ucfirst(str_replace('_', ' ', $reason)) }}</span>
                                            <span class="info-box-number">{{ $count }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<!-- Quick Disposal Modal -->
<div class="modal fade" id="quickDisposalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Quick Disposal</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Quick disposal functionality will be available in the next update.</p>
                <p>For now, please use the bulk disposal option or contact your administrator.</p>
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
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#status, #reason').change(function() {
        $(this).closest('form').submit();
    });
    
    // Clear filters
    $('.btn-secondary').click(function(e) {
        e.preventDefault();
        $('#search, #date_from, #date_to').val('');
        $('#status, #reason').val('all');
        window.location.href = $(this).attr('href');
    });
});

function openQuickDisposalModal() {
    $('#quickDisposalModal').modal('show');
}

function openBulkDisposalModal() {
    alert('Bulk disposal functionality will be available in the next update.');
}

function markForDisposal(stockId, reason) {
    if (confirm('Mark this medication for disposal?')) {
        // This would trigger disposal process
        alert('Disposal functionality will be implemented in the next update.');
    }
}

function viewDisposal(disposalId) {
    // Fetch disposal details via AJAX
    fetch(`/medications/stock/disposal/${disposalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDisposalDetailsModal(data.disposal);
            } else {
                alert('Error loading disposal details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading disposal details');
        });
}

function verifyDisposal(disposalId) {
    if (confirm('Verify this disposal? This action cannot be undone.')) {
        fetch(`/medications/stock/disposal/${disposalId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                verified: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Disposal verified successfully');
                // Reload the page to refresh data
                location.reload();
            } else {
                showToast('error', 'Error verifying disposal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error verifying disposal');
        });
    }
}

function completeDisposal(disposalId) {
    if (confirm('Mark this disposal as completed?')) {
        fetch(`/medications/stock/disposal/${disposalId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Disposal marked as completed');
                location.reload();
            } else {
                showToast('error', 'Error completing disposal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error completing disposal');
        });
    }
}

function cancelDisposal(disposalId) {
    if (confirm('Cancel this disposal? This action cannot be undone.')) {
        fetch(`/medications/stock/disposal/${disposalId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Disposal cancelled successfully');
                location.reload();
            } else {
                showToast('error', 'Error cancelling disposal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error cancelling disposal');
        });
    }
}

function showDisposalDetailsModal(disposal) {
    const modalContent = `
        <div class="modal fade" id="disposalDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Disposal Details - ${disposal.reference_number || 'UNF-' + String(disposal.id).padStart(6, '0')}</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Basic Information</h5>
                                <table class="table table-sm">
                                    <tr><td><strong>Reference:</strong></td><td>${disposal.reference_number || 'UNF-' + String(disposal.id).padStart(6, '0')}</td></tr>
                                    <tr><td><strong>Medication:</strong></td><td>${disposal.medication?.generic_name || 'N/A'}</td></tr>
                                    <tr><td><strong>Brand:</strong></td><td>${disposal.medication?.brand_name || 'N/A'}</td></tr>
                                    <tr><td><strong>Batch Number:</strong></td><td>${disposal.batch_number || 'N/A'}</td></tr>
                                    <tr><td><strong>Expiry Date:</strong></td><td>${disposal.expiry_date ? new Date(disposal.expiry_date).toLocaleDateString() : 'N/A'}</td></tr>
                                    <tr><td><strong>Quantity:</strong></td><td>${disposal.quantity_discarded}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Disposal Details</h5>
                                <table class="table table-sm">
                                    <tr><td><strong>Reason:</strong></td><td><span class="badge bg-info">${disposal.reason?.replace('_', ' ')}</span></td></tr>
                                    <tr><td><strong>Method:</strong></td><td>${disposal.disposal_method || 'N/A'}</td></tr>
                                    <tr><td><strong>Disposed By:</strong></td><td>${disposal.disposed_by?.name || 'System'}</td></tr>
                                    <tr><td><strong>Disposed At:</strong></td><td>${disposal.disposed_at ? new Date(disposal.disposed_at).toLocaleString() : 'N/A'}</td></tr>
                                    <tr><td><strong>Verification:</strong></td><td>${disposal.verification_required ? (disposal.verified_by ? 'Verified by ' + disposal.verified_by?.name : 'Pending') : 'Not Required'}</td></tr>
                                </table>
                            </div>
                        </div>
                        ${disposal.notes ? `<div class="row mt-3"><div class="col-12"><h5>Notes</h5><p class="border p-3">${disposal.notes}</p></div></div>` : ''}
                    </div>
                    <div class="modal-footer">
                        ${disposal.verification_required && !disposal.verified_by ? 
                            `<button type="button" class="btn btn-success" onclick="verifyDisposal(${disposal.id}); $('#disposalDetailsModal').modal('hide');">
                                <i class="fas fa-check"></i> Verify Disposal
                            </button>` : ''}
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#disposalDetailsModal').remove();
    
    // Add modal to body and show it
    $('body').append(modalContent);
    $('#disposalDetailsModal').modal('show');
}

function showToast(type, message) {
    const toastId = 'toast-' + Date.now();
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    
    const toast = `
        <div id="${toastId}" class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="${iconClass}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(toast);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $(`#${toastId}`).alert('close');
    }, 5000);
}
</script>
@endsection
