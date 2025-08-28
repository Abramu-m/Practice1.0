{{-- Medication Management Navigation --}}
<div class="medication-nav bg-white shadow-sm border-bottom">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light px-0">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('medications.dashboard') }}">
                <i class="fas fa-pills text-primary me-2"></i>
                <span class="fw-bold">Medication Management</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#medicationNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="medicationNav">
                <ul class="navbar-nav me-auto">
                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('medications.dashboard') ? 'active' : '' }}" 
                           href="{{ route('medications.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    {{-- Stock Management Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('medications.stock.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-warehouse me-1"></i>
                            Stock Management
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.stock.levels') ? 'active' : '' }}" 
                                   href="{{ route('medications.stock.levels') }}">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Stock Levels
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.stock.alerts') ? 'active' : '' }}" 
                                   href="{{ route('medications.stock.alerts') }}">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Stock Alerts
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.stock.grn.*') ? 'active' : '' }}" 
                                   href="{{ route('medications.stock.grn.index') }}">
                                    <i class="fas fa-truck me-2"></i>
                                    GRN Management
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('store.requisitions.*') ? 'active' : '' }}" 
                                   href="{{ route('store.requisitions.index') }}">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Requisitions
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.stock.transfers.*') ? 'active' : '' }}" 
                                   href="{{ route('medications.stock.transfers.index') }}">
                                    <i class="fas fa-exchange-alt me-2"></i>
                                    Stock Transfers
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.stock.adjustments.*') ? 'active' : '' }}" 
                                   href="{{ route('medications.stock.adjustments.index') }}">
                                    <i class="fas fa-edit me-2"></i>
                                    Adjustments
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.stock.disposal.*') ? 'active' : '' }}" 
                                   href="{{ route('medications.stock.disposal.index') }}">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    Disposal
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Consumption Tracking Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('medications.consumption.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-prescription-bottle-alt me-1"></i>
                            Consumption
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.consumption.index') ? 'active' : '' }}" 
                                   href="{{ route('medications.consumption.index') }}">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Overview
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.consumption.prescriptions.*') ? 'active' : '' }}" 
                                   href="{{ route('medications.consumption.prescriptions.index') }}">
                                    <i class="fas fa-prescription me-2"></i>
                                    Prescriptions
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.consumption.analytics') ? 'active' : '' }}" 
                                   href="{{ route('medications.consumption.analytics') }}">
                                    <i class="fas fa-analytics me-2"></i>
                                    Analytics
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Medical Services Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('medical_services.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-stethoscope me-1"></i>
                            Medical Services
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medical_services.index') && !request('category') ? 'active' : '' }}" 
                                   href="{{ route('medical_services.index') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    All Services
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medical_services.index') && request('category') == 'investigations' ? 'active' : '' }}" 
                                   href="{{ route('medical_services.index', ['category' => 'investigations']) }}">
                                    <i class="fas fa-microscope me-2"></i>
                                    Investigations
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medical_services.index') && request('category') == 'procedures' ? 'active' : '' }}" 
                                   href="{{ route('medical_services.index', ['category' => 'procedures']) }}">
                                    <i class="fas fa-procedures me-2"></i>
                                    Procedures
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('medical_services.create') }}">
                                    <i class="fas fa-plus me-2"></i>
                                    Add New Service
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Reconciliation Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('medications.reconciliation.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-balance-scale me-1"></i>
                            Reconciliation
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reconciliation.index') ? 'active' : '' }}" 
                                   href="{{ route('medications.reconciliation.index') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reconciliation.discrepancies') ? 'active' : '' }}" 
                                   href="{{ route('medications.reconciliation.discrepancies') }}">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Discrepancies
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reconciliation.audit') ? 'active' : '' }}" 
                                   href="{{ route('medications.reconciliation.audit') }}">
                                    <i class="fas fa-history me-2"></i>
                                    Audit Trail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reconciliation.comparison') ? 'active' : '' }}" 
                                   href="{{ route('medications.reconciliation.comparison') }}">
                                    <i class="fas fa-not-equal me-2"></i>
                                    Stock Comparison
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reconciliation.corrections.*') ? 'active' : '' }}" 
                                   href="{{ route('medications.reconciliation.corrections.form') }}">
                                    <i class="fas fa-wrench me-2"></i>
                                    Manual Corrections
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Reports Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('medications.reports.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-pie me-1"></i>
                            Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.index') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.index') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Reports Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.stock.levels') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.stock.levels') }}">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Stock Level Reports
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.consumption') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.consumption') }}">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Consumption Reports
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.movements') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.movements') }}">
                                    <i class="fas fa-arrows-alt me-2"></i>
                                    Movement Reports
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.expiry') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.expiry') }}">
                                    <i class="fas fa-calendar-times me-2"></i>
                                    Expiry Reports
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.abc.analysis') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.abc.analysis') }}">
                                    <i class="fas fa-sort-alpha-down me-2"></i>
                                    ABC Analysis
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('medications.reports.custom') ? 'active' : '' }}" 
                                   href="{{ route('medications.reports.custom') }}">
                                    <i class="fas fa-cogs me-2"></i>
                                    Custom Reports
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                {{-- Right side navigation items --}}
                <ul class="navbar-nav ms-auto">
                    {{-- Quick Actions Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus-circle me-1"></i>
                            Quick Actions
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('medications.stock.transfers.create') }}">
                                    <i class="fas fa-exchange-alt me-2"></i>
                                    New Stock Transfer
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('medications.stock.adjustments.create') }}">
                                    <i class="fas fa-edit me-2"></i>
                                    Stock Adjustment
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('medical_services.create') }}">
                                    <i class="fas fa-microscope me-2"></i>
                                    New Medical Service
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('medications.reconciliation.corrections.form') }}">
                                    <i class="fas fa-wrench me-2"></i>
                                    Manual Correction
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Notifications --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <span id="notification-count">0</span>
                                <span class="visually-hidden">unread alerts</span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="fas fa-bell me-2"></i>
                                    Medication Alerts
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <div id="medication-alerts" class="px-3">
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        No active alerts
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center" href="{{ route('medications.stock.alerts') }}">
                                    <small>View All Alerts</small>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

{{-- Breadcrumb Section --}}
<div class="bg-light border-bottom">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="py-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('medications.dashboard') }}">
                        <i class="fas fa-home me-1"></i>
                        Medication Management
                    </a>
                </li>
                @if(request()->routeIs('medications.stock.*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('medications.stock.levels') }}">Stock Management</a>
                    </li>
                    @if(request()->routeIs('medications.stock.grn.*'))
                        <li class="breadcrumb-item active">GRN Management</li>
                    @elseif(request()->routeIs('store.requisitions.*'))
                        <li class="breadcrumb-item active">Requisitions</li>
                    @elseif(request()->routeIs('medications.stock.transfers.*'))
                        <li class="breadcrumb-item active">Stock Transfers</li>
                    @elseif(request()->routeIs('medications.stock.adjustments.*'))
                        <li class="breadcrumb-item active">Adjustments</li>
                    @elseif(request()->routeIs('medications.stock.disposal.*'))
                        <li class="breadcrumb-item active">Disposal</li>
                    @elseif(request()->routeIs('medications.stock.levels'))
                        <li class="breadcrumb-item active">Stock Levels</li>
                    @elseif(request()->routeIs('medications.stock.alerts'))
                        <li class="breadcrumb-item active">Stock Alerts</li>
                    @endif
                @elseif(request()->routeIs('medications.consumption.*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('medications.consumption.index') }}">Consumption Tracking</a>
                    </li>
                    @if(request()->routeIs('medications.consumption.prescriptions.*'))
                        <li class="breadcrumb-item active">Prescriptions</li>
                    @elseif(request()->routeIs('medications.consumption.analytics'))
                        <li class="breadcrumb-item active">Analytics</li>
                    @endif
                @elseif(request()->routeIs('medical_services.*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('medical_services.index') }}">Medical Services</a>
                    </li>
                    @if(request('category') == 'investigations')
                        <li class="breadcrumb-item active">Investigations</li>
                    @elseif(request('category') == 'procedures')
                        <li class="breadcrumb-item active">Procedures</li>
                    @elseif(request()->routeIs('medical_services.create'))
                        <li class="breadcrumb-item active">Add New Service</li>
                    @elseif(request()->routeIs('medical_services.edit'))
                        <li class="breadcrumb-item active">Edit Service</li>
                    @elseif(request()->routeIs('medical_services.show'))
                        <li class="breadcrumb-item active">Service Details</li>
                    @endif
                @elseif(request()->routeIs('medications.reconciliation.*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('medications.reconciliation.index') }}">Reconciliation</a>
                    </li>
                    @if(request()->routeIs('medications.reconciliation.discrepancies'))
                        <li class="breadcrumb-item active">Discrepancies</li>
                    @elseif(request()->routeIs('medications.reconciliation.audit'))
                        <li class="breadcrumb-item active">Audit Trail</li>
                    @elseif(request()->routeIs('medications.reconciliation.comparison'))
                        <li class="breadcrumb-item active">Stock Comparison</li>
                    @elseif(request()->routeIs('medications.reconciliation.corrections.*'))
                        <li class="breadcrumb-item active">Manual Corrections</li>
                    @endif
                @elseif(request()->routeIs('medications.reports.*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('medications.reports.index') }}">Reports</a>
                    </li>
                    @if(request()->routeIs('medications.reports.stock.levels'))
                        <li class="breadcrumb-item active">Stock Level Reports</li>
                    @elseif(request()->routeIs('medications.reports.consumption'))
                        <li class="breadcrumb-item active">Consumption Reports</li>
                    @elseif(request()->routeIs('medications.reports.movements'))
                        <li class="breadcrumb-item active">Movement Reports</li>
                    @elseif(request()->routeIs('medications.reports.expiry'))
                        <li class="breadcrumb-item active">Expiry Reports</li>
                    @elseif(request()->routeIs('medications.reports.abc.analysis'))
                        <li class="breadcrumb-item active">ABC Analysis</li>
                    @elseif(request()->routeIs('medications.reports.custom'))
                        <li class="breadcrumb-item active">Custom Reports</li>
                    @endif
                @elseif(request()->routeIs('medications.dashboard'))
                    <li class="breadcrumb-item active">Dashboard</li>
                @endif
            </ol>
        </nav>
    </div>
</div>

{{-- Custom CSS for medication navigation --}}
<style>
.medication-nav .navbar-nav .nav-link {
    color: #495057;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    margin: 0 0.25rem;
    transition: all 0.2s ease-in-out;
}

.medication-nav .navbar-nav .nav-link:hover {
    color: #0056b3;
    background-color: #f8f9fa;
}

.medication-nav .navbar-nav .nav-link.active {
    color: #0056b3;
    background-color: #e3f2fd;
    font-weight: 600;
}

.medication-nav .dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.medication-nav .dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease-in-out;
}

.medication-nav .dropdown-item:hover {
    background-color: #f8f9fa;
    color: #0056b3;
}

.medication-nav .dropdown-item.active {
    background-color: #e3f2fd;
    color: #0056b3;
    font-weight: 600;
}

.breadcrumb {
    font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    font-weight: bold;
}

#notification-count {
    font-size: 0.75rem;
}

.badge {
    font-size: 0.6em;
}
</style>

{{-- JavaScript for real-time notifications --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load medication alerts on page load
    loadMedicationAlerts();
    
    // Refresh alerts every 5 minutes
    setInterval(loadMedicationAlerts, 300000);
});

function loadMedicationAlerts() {
    fetch('{{ route("medications.stock.alerts") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateNotificationBadge(data);
        updateNotificationDropdown(data);
    })
    .catch(error => {
        console.log('Error loading alerts:', error);
    });
}

function updateNotificationBadge(alerts) {
    const badge = document.getElementById('notification-count');
    const totalAlerts = (alerts.low_stock_count || 0) + (alerts.expiring_count || 0);
    
    badge.textContent = totalAlerts;
    badge.parentElement.style.display = totalAlerts > 0 ? 'block' : 'none';
}

function updateNotificationDropdown(alerts) {
    const container = document.getElementById('medication-alerts');
    
    if (!alerts.low_stock_count && !alerts.expiring_count) {
        container.innerHTML = `
            <div class="text-muted text-center py-3">
                <i class="fas fa-check-circle me-2"></i>
                No active alerts
            </div>
        `;
        return;
    }
    
    let html = '';
    
    if (alerts.low_stock_count > 0) {
        html += `
            <div class="alert alert-warning alert-sm mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>${alerts.low_stock_count}</strong> medications are low in stock
            </div>
        `;
    }
    
    if (alerts.expiring_count > 0) {
        html += `
            <div class="alert alert-danger alert-sm mb-2">
                <i class="fas fa-calendar-times me-2"></i>
                <strong>${alerts.expiring_count}</strong> medications expiring soon
            </div>
        `;
    }
    
    container.innerHTML = html;
}
</script>
