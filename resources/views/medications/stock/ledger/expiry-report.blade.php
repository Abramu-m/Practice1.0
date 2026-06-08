@extends('layouts.app_main_layout')

@section('page_title', 'Medication Expiry Report')

@section('styles')
<style>
    .expiry-card {
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .expiry-card.danger {
        border-left: 5px solid #dc3545;
    }
    
    .expiry-card.warning {
        border-left: 5px solid #ffc107;
    }
    
    .expiry-card.info {
        border-left: 5px solid #17a2b8;
    }
    
    .expiry-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .expiry-header.danger {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    }
    
    .expiry-header.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .expiry-header.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .expiry-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s;
    }
    
    .expiry-item:hover {
        background-color: #f8f9fa;
    }
    
    .expiry-item:last-child {
        border-bottom: none;
    }
    
    .medication-name {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }
    
    .batch-info {
        font-family: 'Courier New', monospace;
        background: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        display: inline-block;
    }
    
    .expiry-countdown {
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        display: inline-block;
    }
    
    .expired-countdown {
        background: #f8d7da;
        color: #721c24;
    }
    
    .warning-countdown {
        background: #fff3cd;
        color: #856404;
    }
    
    .info-countdown {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .value-display {
        color: #28a745;
        font-weight: 600;
    }
    
    .summary-stats {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .filters-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .priority-high { border-left: 4px solid #dc3545; }
    .priority-medium { border-left: 4px solid #ffc107; }
    .priority-low { border-left: 4px solid #28a745; }

    @media print {
        .app-header,
        .app-sidebar,
        .app-footer,
        .no-print { display: none !important; }

        .app-wrapper, .app-main, .app-content, .container-fluid {
            margin: 0 !important; padding: 0 !important;
            width: 100% !important; background: #fff !important;
        }

        @page { margin: 10mm 12mm; }
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
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Medication Expiry Report
                    </h1>
                    <p class="text-muted mb-0">Monitor medication expiration dates and take proactive action</p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <a href="{{ route('medications.stock.ledger.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Ledger
                    </a>
                    <a href="{{ route('medications.stock.ledger.export', array_merge(request()->query(), ['expiry_status' => 'expired,expiring_soon'])) }}" class="btn btn-outline-danger">
                        <i class="fas fa-download me-2"></i>
                        Export Expiring Items
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>
                        Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="summary-stats">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number text-danger">{{ $expired->count() }}</div>
                    <div class="text-muted">Already Expired</div>
                    <small class="text-danger">Immediate Action Required</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number text-warning">{{ $expiringSoon->count() }}</div>
                    <div class="text-muted">Expiring Soon</div>
                    <small class="text-warning">Within 3 Months</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number text-info">{{ $expiringLater->count() }}</div>
                    <div class="text-muted">Expiring Later</div>
                    <small class="text-info">3-{{ $months ?? 6 }} Months</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    @php
                        $totalValue = ($expired->sum(function($item) { return $item->quantity_received * $item->unit_cost; }) +
                                     $expiringSoon->sum(function($item) { return $item->quantity_received * $item->unit_cost; }));
                    @endphp
                    <div class="stat-number text-success">Tsh {{ number_format($totalValue, 0) }}</div>
                    <div class="text-muted">At Risk Value</div>
                    <small class="text-success">Expired + Expiring Soon</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filters-card">
        <form method="GET" action="{{ route('medications.stock.ledger.expiry-report') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Time Horizon</label>
                <select name="months" class="form-select">
                    <option value="3" {{ (request('months') ?? 6) == 3 ? 'selected' : '' }}>Next 3 Months</option>
                    <option value="6" {{ (request('months') ?? 6) == 6 ? 'selected' : '' }}>Next 6 Months</option>
                    <option value="12" {{ (request('months') ?? 6) == 12 ? 'selected' : '' }}>Next 12 Months</option>
                    <option value="24" {{ (request('months') ?? 6) == 24 ? 'selected' : '' }}>Next 24 Months</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Location Filter</label>
                <select name="location_id" class="form-select">
                    <option value="all" {{ (request('location_id') ?? 'all') == 'all' ? 'selected' : '' }}>All Locations</option>
                    {{-- Add locations here if available --}}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Minimum Value</label>
                <input type="number" class="form-control" name="min_value" 
                       placeholder="Filter by minimum value..." 
                       value="{{ request('min_value') ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Update Report
                    </button>
                    <a href="{{ route('medications.stock.ledger.expiry-report') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Expired Items (Critical) --}}
    @if($expired->count() > 0)
    <div class="expiry-card danger">
        <div class="expiry-header danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-times-circle me-2"></i>
                        Already Expired Items
                    </h4>
                    <p class="mb-0 opacity-75">These items have already passed their expiry date and require immediate attention</p>
                </div>
                <div class="text-end">
                    <div class="h3 mb-0">{{ $expired->count() }}</div>
                    <small>Items</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @foreach($expired as $item)
            <div class="expiry-item priority-high">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="medication-name">{{ $item->medication->generic_name }}</div>
                        @if($item->medication->brand_name)
                            <div class="text-muted small">{{ $item->medication->brand_name }}</div>
                        @endif
                        @if($item->medication->strength)
                            <span class="badge bg-light text-dark">{{ $item->medication->strength }}</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <div class="batch-info">{{ $item->batch_number }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fw-bold">{{ number_format($item->quantity_received) }} units</div>
                        <div class="value-display small">Tsh {{ number_format($item->quantity_received * $item->unit_cost, 2) }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fw-bold text-danger">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</div>
                        @php
                            $daysExpired = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($item->expiry_date));
                        @endphp
                        <div class="expired-countdown">
                            Expired {{ $daysExpired }} days ago
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('medications.stock.ledger.show', $item) }}" 
                               class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="updateStatus({{ $item->id }}, 'expired')" 
                                    title="Mark as Expired">
                                <i class="fas fa-exclamation-triangle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Expiring Soon (Within 3 Months) --}}
    @if($expiringSoon->count() > 0)
    <div class="expiry-card warning">
        <div class="expiry-header warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-clock me-2"></i>
                        Expiring Soon (Within 3 Months)
                    </h4>
                    <p class="mb-0 opacity-75">These items will expire within the next 3 months - plan usage or disposal</p>
                </div>
                <div class="text-end">
                    <div class="h3 mb-0">{{ $expiringSoon->count() }}</div>
                    <small>Items</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @foreach($expiringSoon as $item)
            <div class="expiry-item priority-medium">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="medication-name">{{ $item->medication->generic_name }}</div>
                        @if($item->medication->brand_name)
                            <div class="text-muted small">{{ $item->medication->brand_name }}</div>
                        @endif
                        @if($item->medication->strength)
                            <span class="badge bg-light text-dark">{{ $item->medication->strength }}</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <div class="batch-info">{{ $item->batch_number }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fw-bold">{{ number_format($item->quantity_received) }} units</div>
                        <div class="value-display small">Tsh {{ number_format($item->quantity_received * $item->unit_cost, 2) }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fw-bold text-warning">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</div>
                        @php
                            $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($item->expiry_date));
                        @endphp
                        <div class="warning-countdown">
                            {{ $daysLeft }} days left
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('medications.stock.ledger.show', $item) }}" 
                               class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('medications.stock.grn.show', $item->grn) }}" 
                               class="btn btn-sm btn-outline-info" title="View GRN">
                                <i class="fas fa-truck"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Expiring Later (3-6 Months) --}}
    @if($expiringLater->count() > 0)
    <div class="expiry-card info">
        <div class="expiry-header info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-info-circle me-2"></i>
                        Expiring Later (3-{{ $months ?? 6 }} Months)
                    </h4>
                    <p class="mb-0 opacity-75">Monitor these items for future planning</p>
                </div>
                <div class="text-end">
                    <div class="h3 mb-0">{{ $expiringLater->count() }}</div>
                    <small>Items</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @foreach($expiringLater->take(10) as $item)
            <div class="expiry-item priority-low">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="medication-name">{{ $item->medication->generic_name }}</div>
                        @if($item->medication->brand_name)
                            <div class="text-muted small">{{ $item->medication->brand_name }}</div>
                        @endif
                        @if($item->medication->strength)
                            <span class="badge bg-light text-dark">{{ $item->medication->strength }}</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <div class="batch-info">{{ $item->batch_number }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fw-bold">{{ number_format($item->quantity_received) }} units</div>
                        <div class="value-display small">Tsh {{ number_format($item->quantity_received * $item->unit_cost, 2) }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fw-bold text-info">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</div>
                        @php
                            $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($item->expiry_date));
                        @endphp
                        <div class="info-countdown">
                            {{ $daysLeft }} days left
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="{{ route('medications.stock.ledger.show', $item) }}" 
                           class="btn btn-sm btn-outline-primary" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
            @if($expiringLater->count() > 10)
            <div class="expiry-item text-center">
                <a href="{{ route('medications.stock.ledger.index', ['expiry_status' => 'valid']) }}" class="btn btn-outline-info">
                    View All {{ $expiringLater->count() }} Items Expiring Later
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- No Expiring Items --}}
    @if($expired->count() == 0 && $expiringSoon->count() == 0 && $expiringLater->count() == 0)
    <div class="text-center py-5">
        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
        <h4 class="text-success">All Good!</h4>
        <p class="text-muted">No medications are expiring within the selected time frame.</p>
        <a href="{{ route('medications.stock.ledger.index') }}" class="btn btn-primary">
            View All Ledger Entries
        </a>
    </div>
    @endif
</div>

{{-- Action Modal --}}
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Take Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>What action would you like to take for this expired medication?</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-warning" onclick="markAsExpired()">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Mark as Expired
                    </button>
                    <button class="btn btn-danger" onclick="markAsDisposed()">
                        <i class="fas fa-trash me-2"></i>
                        Mark as Disposed
                    </button>
                    <button class="btn btn-info" onclick="createWasteEntry()">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Create Waste Entry
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateStatus(itemId, status) {
    if (confirm(`Are you sure you want to mark this item as ${status}?`)) {
        fetch(`/medications/stock/ledger/${itemId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: status,
                notes: `Marked as ${status} from expiry report at ${new Date().toLocaleString()}`
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Item marked as ${status} successfully`, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast('Failed to update status', 'error');
            }
        })
        .catch(error => {
            showToast('Error updating status', 'error');
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
