@extends('layouts.app_main_layout')

@section('page_title', 'Store Management Dashboard')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Store Management Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Store Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Key Metrics Row -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="totalItems">{{ $metrics['totalItems'] ?? 0 }}</h3>
                            <p>Total Items</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <a href="{{ route('medications.index') }}" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="totalStockValue">{{ $metrics['totalStockValue'] ?? '0.00' }}</h3>
                            <p>Total Stock Value</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="{{ route('store.reports.stock-valuation') }}" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="lowStockItems">{{ $metrics['lowStockItems'] ?? 0 }}</h3>
                            <p>Low Stock Items</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="{{ route('store.low-stock') }}" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="expiredItems">{{ $metrics['expiredItems'] ?? 0 }}</h3>
                            <p>Expired Items</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <a href="{{ route('store.expired-items') }}" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <a href="{{ route('medications.create') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-plus"></i> Add New Item
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('store.grn.create') }}" class="btn btn-success w-100">
                                        <i class="fas fa-truck"></i> New GRN
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('store.requisitions.create') }}" class="btn btn-warning w-100">
                                        <i class="fas fa-clipboard-list"></i> New Requisition
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('store.stock.transfers') }}" class="btn btn-info w-100">
                                        <i class="fas fa-exchange-alt"></i> Stock Transfer
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('store.stock.adjustments') }}" class="btn btn-secondary w-100">
                                        <i class="fas fa-adjust"></i> Stock Adjustment
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('store.reports.index') }}" class="btn btn-dark w-100">
                                        <i class="fas fa-chart-bar"></i> Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Alerts and Recent Activity Row -->
            <div class="row">
                <!-- Alerts -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Stock Alerts</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column" id="alertsList">
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <i class="fas fa-spinner fa-spin"></i> Loading alerts...
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Movements -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Stock Movements</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column" id="recentMovements">
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <i class="fas fa-spinner fa-spin"></i> Loading recent movements...
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row">
                <!-- Stock Level Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Stock Levels by Category</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-responsive">
                                <canvas id="stockLevelChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Expiry Timeline -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Items Expiring Soon</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-responsive">
                                <canvas id="expiryChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Performing Items -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Performing Items</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped" id="topItemsTable">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Consumption Rate</th>
                                        <th>Days Until Reorder</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Loading top items...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Load dashboard data
    loadDashboardData();
    
    // Refresh data every 30 seconds
    setInterval(loadDashboardData, 30000);
    
    function loadDashboardData() {
        // Load alerts
        $.get('{{ route("store.alerts") }}', function(data) {
            updateAlerts(data);
        });
        
        // Load recent movements
        $.get('{{ route("store.recent-movements") }}', function(data) {
            updateRecentMovements(data);
        });
        
        // Load top items
        $.get('{{ route("store.metrics") }}', function(data) {
            updateTopItems(data.topItems);
            updateCharts(data);
        });
    }
    
    function updateAlerts(alerts) {
        var alertsList = $('#alertsList');
        alertsList.empty();
        
        if (alerts.length === 0) {
            alertsList.append('<li class="nav-item"><div class="nav-link text-success"><i class="fas fa-check"></i> No alerts</div></li>');
        } else {
            alerts.forEach(function(alert) {
                var iconClass = alert.type === 'low_stock' ? 'fas fa-exclamation-triangle text-warning' : 'fas fa-calendar-times text-danger';
                alertsList.append(
                    '<li class="nav-item">' +
                    '<div class="nav-link">' +
                    '<i class="' + iconClass + '"></i> ' +
                    alert.message +
                    '</div>' +
                    '</li>'
                );
            });
        }
    }
    
    function updateRecentMovements(movements) {
        var movementsList = $('#recentMovements');
        movementsList.empty();
        
        if (movements.length === 0) {
            movementsList.append('<li class="nav-item"><div class="nav-link text-info"><i class="fas fa-info"></i> No recent movements</div></li>');
        } else {
            movements.forEach(function(movement) {
                var iconClass = movement.type === 'in' ? 'fas fa-arrow-up text-success' : 'fas fa-arrow-down text-danger';
                movementsList.append(
                    '<li class="nav-item">' +
                    '<div class="nav-link">' +
                    '<i class="' + iconClass + '"></i> ' +
                    movement.item_name + ' (' + movement.quantity + ' ' + movement.unit + ')' +
                    '<small class="float-end text-muted">' + movement.time_ago + '</small>' +
                    '</div>' +
                    '</li>'
                );
            });
        }
    }
    
    function updateTopItems(items) {
        var tbody = $('#topItemsTable tbody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">No items found</td></tr>');
        } else {
            items.forEach(function(item) {
                var statusClass = item.status === 'Good' ? 'success' : (item.status === 'Low' ? 'warning' : 'danger');
                tbody.append(
                    '<tr>' +
                    '<td>' + item.name + '</td>' +
                    '<td>' + item.category + '</td>' +
                    '<td>' + item.current_stock + ' ' + item.unit + '</td>' +
                    '<td>' + item.consumption_rate + '/day</td>' +
                    '<td>' + item.days_until_reorder + '</td>' +
                    '<td><span class="badge badge-' + statusClass + '">' + item.status + '</span></td>' +
                    '</tr>'
                );
            });
        }
    }
    
    function updateCharts(data) {
        // Stock Level Chart
        var ctx1 = document.getElementById('stockLevelChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: data.stockByCategory.labels,
                datasets: [{
                    data: data.stockByCategory.values,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Expiry Chart
        var ctx2 = document.getElementById('expiryChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: data.expiryTimeline.labels,
                datasets: [{
                    label: 'Items Expiring',
                    data: data.expiryTimeline.values,
                    backgroundColor: '#FF6384'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endsection

@section('styles')
<style>
.small-box {
    border-radius: 10px;
    transition: transform 0.2s;
}

.small-box:hover {
    transform: translateY(-5px);
}

.content-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-attachment: fixed;
    min-height: 100vh;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}

.btn {
    border-radius: 25px;
    transition: all 0.3s;
}

.btn:hover {
    transform: translateY(-2px);
}

.table {
    background: white;
    border-radius: 10px;
}

.nav-link {
    border-radius: 10px;
}

.chart-responsive {
    position: relative;
    height: 300px;
}
</style>
@endsection
