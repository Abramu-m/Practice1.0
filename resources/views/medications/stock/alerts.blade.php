@extends('layouts.app_main_layout')

@section('page_title', 'Stock Alerts & Notifications')

@section('main_content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-bell text-danger me-2"></i>
                        Stock Alerts & Notifications
                    </h1>
                    <p class="text-muted mb-0">Monitor and manage stock-related alerts and notifications</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" onclick="markAllAsRead()">
                        <i class="fas fa-check me-2"></i>
                        Mark All Read
                    </button>
                    <button class="btn btn-outline-primary" onclick="refreshAlerts()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Total Alerts</div>
                    <h4 class="mb-0 text-primary">{{ $alertsSummary['total_alerts'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Critical</div>
                    <h4 class="mb-0 text-danger">{{ $alertsSummary['critical_alerts'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">High Priority</div>
                    <h4 class="mb-0 text-warning">{{ $alertsSummary['high_alerts'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Low Stock</div>
                    <h4 class="mb-0 text-info">{{ $alertsSummary['low_stock_count'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Expired</div>
                    <h4 class="mb-0 text-danger">{{ $alertsSummary['expired_count'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Expiring Soon</div>
                    <h4 class="mb-0 text-warning">{{ $alertsSummary['expiring_count'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.stock.alerts') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Alert Type</label>
                            <select name="type" class="form-select">
                                <option value="all" {{ ($alertType ?? 'all') == 'all' ? 'selected' : '' }}>All Types</option>
                                <option value="low_stock" {{ ($alertType ?? '') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="expired" {{ ($alertType ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="expiring" {{ ($alertType ?? '') == 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                                <option value="out_of_stock" {{ ($alertType ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="all" {{ ($priority ?? 'all') == 'all' ? 'selected' : '' }}>All Priorities</option>
                                <option value="critical" {{ ($priority ?? '') == 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="high" {{ ($priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ ($priority ?? '') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ ($priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-select">
                                <option value="all">All Locations</option>
                                @foreach($storeLocations ?? [] as $storeLocation)
                                <option value="{{ $storeLocation->id }}" {{ ($location ?? '') == $storeLocation->id ? 'selected' : '' }}>
                                    {{ $storeLocation->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                                <a href="{{ route('medications.stock.alerts') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts List --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list text-primary me-2"></i>
                            Active Alerts
                        </h5>
                        <span class="badge bg-primary">{{ $paginatedAlerts->total() ?? 0 }} alerts</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($paginatedAlerts ?? [] as $alert)
                    <div class="alert-item border-bottom p-3 {{ $alert['priority'] == 'critical' ? 'border-start border-danger border-4' : ($alert['priority'] == 'high' ? 'border-start border-warning border-4' : '') }}">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                @switch($alert['type'])
                                    @case('low_stock')
                                        <div class="bg-warning bg-gradient rounded-circle p-2">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                        @break
                                    @case('expired')
                                        <div class="bg-danger bg-gradient rounded-circle p-2">
                                            <i class="fas fa-times-circle text-white"></i>
                                        </div>
                                        @break
                                    @case('expiring')
                                        <div class="bg-warning bg-gradient rounded-circle p-2">
                                            <i class="fas fa-clock text-white"></i>
                                        </div>
                                        @break
                                    @case('out_of_stock')
                                        <div class="bg-danger bg-gradient rounded-circle p-2">
                                            <i class="fas fa-ban text-white"></i>
                                        </div>
                                        @break
                                @endswitch
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $alert['title'] }}</h6>
                                        <p class="mb-1 text-muted">{{ $alert['message'] }}</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $alert['priority'] == 'critical' ? 'danger' : ($alert['priority'] == 'high' ? 'warning' : ($alert['priority'] == 'medium' ? 'info' : 'secondary')) }}">
                                            {{ ucfirst($alert['priority']) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $alert['created_at']->diffForHumans() }}</small>
                                    </div>
                                </div>
                                
                                <div class="row text-sm">
                                    <div class="col-md-3">
                                        <strong>Medication:</strong><br>
                                        <span class="text-muted">{{ $alert['medication']['name'] ?? 'N/A' }}</span>
                                    </div>
                                    @if(isset($alert['current_stock']))
                                    <div class="col-md-2">
                                        <strong>Current Stock:</strong><br>
                                        <span class="text-muted">{{ $alert['current_stock'] }}</span>
                                    </div>
                                    @endif
                                    @if(isset($alert['reorder_level']))
                                    <div class="col-md-2">
                                        <strong>Reorder Level:</strong><br>
                                        <span class="text-muted">{{ $alert['reorder_level'] }}</span>
                                    </div>
                                    @endif
                                    @if(isset($alert['expiry_date']))
                                    <div class="col-md-3">
                                        <strong>Expiry Date:</strong><br>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($alert['expiry_date'])->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                    <div class="col-md-2">
                                        <strong>Action Required:</strong><br>
                                        <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $alert['action_required'])) }}</span>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @switch($alert['action_required'])
                                            @case('reorder')
                                            @case('urgent_reorder')
                                                <button class="btn btn-outline-primary" onclick="createRequisition({{ $alert['medication']['id'] }})">
                                                    <i class="fas fa-shopping-cart me-1"></i>Create Requisition
                                                </button>
                                                @break
                                            @case('dispose')
                                                <button class="btn btn-outline-danger" onclick="disposeItem({{ $alert['medication']['id'] }})">
                                                    <i class="fas fa-trash me-1"></i>Dispose
                                                </button>
                                                @break
                                            @case('monitor')
                                                <button class="btn btn-outline-info" onclick="monitorItem({{ $alert['medication']['id'] }})">
                                                    <i class="fas fa-eye me-1"></i>Monitor
                                                </button>
                                                @break
                                        @endswitch
                                        <button class="btn btn-outline-secondary" onclick="viewMedication({{ $alert['medication']['id'] }})">
                                            <i class="fas fa-info-circle me-1"></i>View Details
                                        </button>
                                        <button class="btn btn-outline-success" onclick="dismissAlert('{{ $alert['id'] }}')">
                                            <i class="fas fa-check me-1"></i>Dismiss
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash text-muted fa-3x mb-3"></i>
                        <h5 class="text-muted">No Active Alerts</h5>
                        <p class="text-muted">All stock levels are within normal parameters.</p>
                    </div>
                    @endforelse
                </div>
                
                @if(isset($paginatedAlerts) && $paginatedAlerts->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $paginatedAlerts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.alert-item:hover {
    background-color: rgba(0, 123, 255, 0.02);
}

.alert-item:last-child {
    border-bottom: none !important;
}

.badge {
    font-size: 0.75rem;
}

.text-sm {
    font-size: 0.875rem;
}
</style>

<script>
function refreshAlerts() {
    window.location.reload();
}

function markAllAsRead() {
    if (confirm('Mark all alerts as read?')) {
        // Implementation would send AJAX request to mark alerts as read
        showToast('All alerts marked as read', 'success');
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function createRequisition(medicationId) {
    // Redirect to requisition creation with pre-filled medication
    window.location.href = `/store/requisitions/create?medication_id=${medicationId}`;
}

function disposeItem(medicationId) {
    if (confirm('Initiate disposal process for this medication?')) {
        // Redirect to disposal form
        window.location.href = `/medications/stock/disposal?medication_id=${medicationId}`;
    }
}

function monitorItem(medicationId) {
    // Add medication to monitoring list
    showToast('Medication added to monitoring list', 'info');
}

function viewMedication(medicationId) {
    // Redirect to medication details
    window.location.href = `/medications/${medicationId}`;
}

function dismissAlert(alertId) {
    if (confirm('Dismiss this alert?')) {
        // Implementation would send AJAX request to dismiss alert
        showToast('Alert dismissed', 'success');
        
        // Remove alert from DOM
        const alertElement = event.target.closest('.alert-item');
        if (alertElement) {
            alertElement.style.transition = 'opacity 0.3s ease';
            alertElement.style.opacity = '0';
            setTimeout(() => {
                alertElement.remove();
            }, 300);
        }
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to page
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove after hiding
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
