@extends('layouts.app_main_layout')

@section('page_title', 'Stock Summary Report')

@section('styles')
<style>
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .summary-card h3 {
        font-weight: 300;
        margin-bottom: 0.5rem;
    }
    
    .summary-card .display-4 {
        font-weight: 700;
    }
    
    .stock-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .table thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
    }
    
    .table td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    .medication-info {
        line-height: 1.6;
    }
    
    .quantity-display {
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .cost-display {
        color: #28a745;
        font-weight: 600;
    }
    
    .batch-count {
        background: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
    
    .expiry-status {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .expiry-good {
        background: #d4edda;
        color: #155724;
    }
    
    .expiry-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .expiry-danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    .filter-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .progress-thin {
        height: 4px;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Stock Summary Report
                    </h1>
                    <p class="text-muted mb-0">Comprehensive overview of medication inventory by item</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.stock.ledger.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Ledger
                    </a>
                    <a href="{{ route('medications.stock.ledger.export', request()->query()) }}" class="btn btn-outline-success">
                        <i class="fas fa-download me-2"></i>
                        Export Data
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
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="summary-card">
                <h3>Total Medications</h3>
                <div class="display-4">{{ number_format($stockSummary->count()) }}</div>
                <p class="mb-0">Active medication types</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="summary-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #155724;">
                <h3>Total Quantity</h3>
                <div class="display-4">{{ number_format($stockSummary->sum('total_quantity')) }}</div>
                <p class="mb-0">Units in stock</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="summary-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #8b4513;">
                <h3>Total Batches</h3>
                <div class="display-4">{{ number_format($stockSummary->sum('batch_count')) }}</div>
                <p class="mb-0">Active batches</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="summary-card" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #721c24;">
                <h3>Average Cost</h3>
                <div class="display-4">${{ number_format($stockSummary->avg('average_cost'), 2) }}</div>
                <p class="mb-0">Per unit</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('medications.stock.ledger.stock-summary') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Filter by Medication</label>
                <select name="medication_id" class="form-select">
                    <option value="all" {{ (request('medication_id') ?? 'all') == 'all' ? 'selected' : '' }}>All Medications</option>
                    @foreach($stockSummary as $stock)
                    <option value="{{ $stock->medication_id }}" {{ (request('medication_id') ?? '') == $stock->medication_id ? 'selected' : '' }}>
                        {{ $stock->medication->generic_name }} {{ $stock->medication->strength ? '(' . $stock->medication->strength . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter by Location</label>
                <select name="location_id" class="form-select">
                    <option value="all" {{ (request('location_id') ?? 'all') == 'all' ? 'selected' : '' }}>All Locations</option>
                    {{-- Add locations here if available --}}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort By</label>
                <select name="sort_by" class="form-select">
                    <option value="total_quantity" {{ (request('sort_by') ?? 'total_quantity') == 'total_quantity' ? 'selected' : '' }}>Total Quantity</option>
                    <option value="average_cost" {{ (request('sort_by') ?? '') == 'average_cost' ? 'selected' : '' }}>Average Cost</option>
                    <option value="batch_count" {{ (request('sort_by') ?? '') == 'batch_count' ? 'selected' : '' }}>Batch Count</option>
                    <option value="earliest_expiry" {{ (request('sort_by') ?? '') == 'earliest_expiry' ? 'selected' : '' }}>Earliest Expiry</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('medications.stock.ledger.stock-summary') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Stock Summary Table --}}
    <div class="stock-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 25%;">Medication</th>
                        <th style="width: 15%;">Total Quantity</th>
                        <th style="width: 15%;">Average Cost</th>
                        <th style="width: 10%;">Batches</th>
                        <th style="width: 15%;">Expiry Range</th>
                        <th style="width: 15%;">Total Value</th>
                        <th style="width: 5%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockSummary as $stock)
                    <tr>
                        <td>
                            <div class="medication-info">
                                <div class="fw-bold text-primary">{{ $stock->medication->generic_name }}</div>
                                @if($stock->medication->brand_name)
                                    <div class="text-muted small">{{ $stock->medication->brand_name }}</div>
                                @endif
                                @if($stock->medication->strength)
                                    <span class="badge bg-light text-dark">{{ $stock->medication->strength }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="quantity-display text-info">
                                {{ number_format($stock->total_quantity) }}
                                <small class="text-muted d-block">units</small>
                            </div>
                        </td>
                        <td>
                            <div class="cost-display">
                                ${{ number_format($stock->average_cost, 2) }}
                                <small class="text-muted d-block">per unit</small>
                            </div>
                        </td>
                        <td>
                            <span class="batch-count">
                                {{ $stock->batch_count }} batches
                            </span>
                        </td>
                        <td>
                            @php
                                $earliestExpiry = \Carbon\Carbon::parse($stock->earliest_expiry);
                                $latestExpiry = \Carbon\Carbon::parse($stock->latest_expiry);
                                $now = \Carbon\Carbon::now();
                                $daysTillEarliest = $now->diffInDays($earliestExpiry, false);
                                
                                if ($daysTillEarliest < 0) {
                                    $expiryClass = 'expiry-danger';
                                } elseif ($daysTillEarliest <= 90) {
                                    $expiryClass = 'expiry-warning';
                                } else {
                                    $expiryClass = 'expiry-good';
                                }
                            @endphp
                            <div class="expiry-status {{ $expiryClass }}">
                                <div class="small">Earliest: {{ $earliestExpiry->format('M d, Y') }}</div>
                                <div class="small">Latest: {{ $latestExpiry->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td>
                            @php
                                $totalValue = $stock->total_quantity * $stock->average_cost;
                            @endphp
                            <div class="fw-bold text-success">
                                ${{ number_format($totalValue, 2) }}
                            </div>
                            
                            {{-- Value bar chart --}}
                            @php
                                $maxValue = $stockSummary->max(function($item) {
                                    return $item->total_quantity * $item->average_cost;
                                });
                                $percentage = $maxValue > 0 ? ($totalValue / $maxValue) * 100 : 0;
                            @endphp
                            <div class="progress progress-thin mt-1">
                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('medications.stock.ledger.index', ['medication_id' => $stock->medication_id]) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View Batches">
                                    <i class="fas fa-list"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="showBatchDetails({{ $stock->medication_id }})" 
                                        title="Batch Details">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-chart-bar text-muted fa-3x mb-3"></i>
                            <h5 class="text-muted">No Stock Data Available</h5>
                            <p class="text-muted">No medications found matching your criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Charts Section --}}
    @if($stockSummary->count() > 0)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    Stock Distribution by Quantity
                </h5>
                <canvas id="quantityChart" height="300"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-chart-bar text-success me-2"></i>
                    Stock Value by Medication
                </h5>
                <canvas id="valueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Batch Details Modal --}}
<div class="modal fade" id="batchDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="batchDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function showBatchDetails(medicationId) {
    const modal = new bootstrap.Modal(document.getElementById('batchDetailsModal'));
    modal.show();
    
    // Load batch details via AJAX
    fetch(`/medications/stock/ledger/medication/${medicationId}/batches`)
        .then(response => response.json())
        .then(data => {
            let html = '<div class="table-responsive">';
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Batch</th><th>Quantity</th><th>Cost</th><th>Expiry</th><th>GRN</th></tr></thead>';
            html += '<tbody>';
            
            data.forEach(batch => {
                html += `<tr>
                    <td><code>${batch.batch_number}</code></td>
                    <td>${batch.quantity_received}</td>
                    <td>$${parseFloat(batch.unit_cost).toFixed(2)}</td>
                    <td>${new Date(batch.expiry_date).toLocaleDateString()}</td>
                    <td>${batch.grn ? batch.grn.grn_number : 'N/A'}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            document.getElementById('batchDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('batchDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Error loading batch details</div>';
        });
}

// Charts
@if($stockSummary->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    // Quantity Distribution Chart
    const quantityCtx = document.getElementById('quantityChart').getContext('2d');
    const quantityData = {
        labels: [
            @foreach($stockSummary->take(10) as $stock)
                '{{ addslashes($stock->medication->generic_name) }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($stockSummary->take(10) as $stock)
                    {{ $stock->total_quantity }},
                @endforeach
            ],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
            ]
        }]
    };
    
    new Chart(quantityCtx, {
        type: 'doughnut',
        data: quantityData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Value Chart
    const valueCtx = document.getElementById('valueChart').getContext('2d');
    const valueData = {
        labels: [
            @foreach($stockSummary->take(10) as $stock)
                '{{ addslashes($stock->medication->generic_name) }}',
            @endforeach
        ],
        datasets: [{
            label: 'Stock Value (Tsh)',
            data: [
                @foreach($stockSummary->take(10) as $stock)
                    {{ $stock->total_quantity * $stock->average_cost }},
                @endforeach
            ],
            backgroundColor: 'rgba(40, 167, 69, 0.8)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }]
    };
    
    new Chart(valueCtx, {
        type: 'bar',
        data: valueData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Tsh' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
@endif
</script>
@endsection
