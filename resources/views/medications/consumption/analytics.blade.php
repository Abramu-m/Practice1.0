@extends('layouts.app_main_layout')

@section('page_title', 'Consumption Analytics')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<style>
    .analytics-card { 
        transition: transform 0.2s;
        height: 100%;
    }
    .analytics-card:hover { 
        transform: translateY(-2px);
    }
    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }
    .metric-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .chart-container {
        position: relative;
        height: 400px;
        margin: 20px 0;
    }
    .filter-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 10px;
    }
    .progress-slim {
        height: 0.5rem;
    }
    .table-analytics {
        font-size: 0.9rem;
    }
    .category-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>
@endpush

@section('main_content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Consumption Analytics Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('medications.consumption.index') }}">Consumption</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card filter-card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>
                        Filters & Date Range
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.consumption.analytics') }}" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="date_range" class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="date_range" name="date_range" readonly>
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="store_location" class="form-label">Store Location</label>
                            <select class="form-control" id="store_location" name="store_location">
                                <option value="all">All Locations</option>
                                @foreach($storeLocations as $location)
                                    <option value="{{ $location->id }}" {{ $storeLocation == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="medication_type" class="form-label">Medication Type</label>
                            <select class="form-control" id="medication_type" name="medication_type">
                                <option value="all">All Types</option>
                                @foreach($medicationTypes as $type)
                                    <option value="{{ $type }}" {{ $medicationType === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-chart-bar mr-2"></i>Update Analytics
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Overview Metrics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="metric-card bg-info">
                        <div class="metric-label">Total Consumption</div>
                        <div class="metric-value">{{ number_format($analytics['overview']['total_consumption']) }}</div>
                        <div class="metric-label">Units dispensed</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="metric-card bg-success">
                        <div class="metric-label">Total Cost</div>
                        <div class="metric-value">₦{{ number_format($analytics['overview']['total_cost'], 2) }}</div>
                        <div class="metric-label">Value dispensed</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="metric-card bg-warning">
                        <div class="metric-label">Unique Medications</div>
                        <div class="metric-value">{{ $analytics['overview']['unique_medications'] }}</div>
                        <div class="metric-label">Different items</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="metric-card bg-purple">
                        <div class="metric-label">Daily Average</div>
                        <div class="metric-value">{{ $analytics['overview']['avg_daily_consumption'] }}</div>
                        <div class="metric-label">Units per day</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <!-- Consumption Trends -->
                <div class="col-md-8">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>
                                Consumption Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="trendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="col-md-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-2"></i>
                                By Category
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Tables Row -->
            <div class="row mb-4">
                <!-- Top Consumed Medications -->
                <div class="col-md-6">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-pills mr-2"></i>
                                Top Consumed Medications
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            @if((is_array($analytics['topMedications']) ? count($analytics['topMedications']) : $analytics['topMedications']->count()) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-analytics">
                                        <thead>
                                            <tr>
                                                <th>Medication</th>
                                                <th>Total Dispensed</th>
                                                <th>Prescriptions</th>
                                                <th>Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php 
                                                $topMedications = is_array($analytics['topMedications']) ? collect($analytics['topMedications']) : $analytics['topMedications'];
                                                $maxConsumption = $topMedications->first()->total_dispensed ?? 1; 
                                            @endphp
                                            @foreach($topMedications as $medication)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $medication->medication->name ?? 'Unknown' }}</strong>
                                                    </td>
                                                    <td>{{ number_format($medication->total_dispensed) }}</td>
                                                    <td>{{ $medication->prescription_count }}</td>
                                                    <td>
                                                        <div class="progress progress-slim">
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: {{ ($medication->total_dispensed / $maxConsumption) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-pills fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No consumption data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Consumption by Location -->
                <div class="col-md-6">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                Consumption by Location
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            @if((is_array($analytics['byLocation']) ? count($analytics['byLocation']) : $analytics['byLocation']->count()) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-analytics">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                <th>Total Dispensed</th>
                                                <th>Percentage</th>
                                                <th>Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php 
                                                $byLocation = is_array($analytics['byLocation']) ? collect($analytics['byLocation']) : $analytics['byLocation'];
                                                $totalByLocation = $byLocation->sum('total'); 
                                            @endphp
                                            @foreach($byLocation as $location)
                                                @php $percentage = $totalByLocation > 0 ? ($location['total'] / $totalByLocation) * 100 : 0; @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $location['location'] }}</strong>
                                                    </td>
                                                    <td>{{ number_format($location['total']) }}</td>
                                                    <td>{{ number_format($percentage, 1) }}%</td>
                                                    <td>
                                                        <div class="progress progress-slim">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: {{ $percentage }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-map-marker-alt fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No location data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics Row -->
            <div class="row">
                <!-- Cost Analysis -->
                <div class="col-md-4">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-dollar-sign mr-2"></i>
                                Cost Analysis
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="description-block">
                                        <h5 class="description-header text-success">
                                            ₦{{ number_format($analytics['costAnalysis']['total_cost'], 2) }}
                                        </h5>
                                        <span class="description-text">Total Cost</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="description-block">
                                        <h5 class="description-header text-info">
                                            ₦{{ number_format($analytics['costAnalysis']['avg_cost_per_item'], 2) }}
                                        </h5>
                                        <span class="description-text">Average Cost per Item</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="description-block">
                                        <h5 class="description-header text-warning">
                                            {{ number_format($analytics['costAnalysis']['total_items']) }}
                                        </h5>
                                        <span class="description-text">Total Items Dispensed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Turnover -->
                <div class="col-md-8">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Stock Turnover Analysis
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            @if((is_array($analytics['stockTurnover']) ? count($analytics['stockTurnover']) : $analytics['stockTurnover']->count()) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-analytics">
                                        <thead>
                                            <tr>
                                                <th>Medication</th>
                                                <th>Consumption</th>
                                                <th>Current Stock</th>
                                                <th>Turnover Ratio</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $stockTurnover = is_array($analytics['stockTurnover']) ? collect($analytics['stockTurnover']) : $analytics['stockTurnover']; @endphp
                                            @foreach($stockTurnover as $turnover)
                                                <tr>
                                                    <td>
                                                        @php $med = \App\Models\Medication::find($turnover['medication_id']); @endphp
                                                        <strong>{{ $med->name ?? 'Unknown' }}</strong>
                                                    </td>
                                                    <td>{{ number_format($turnover['consumption']) }}</td>
                                                    <td>{{ number_format($turnover['current_stock']) }}</td>
                                                    <td>{{ $turnover['turnover_ratio'] }}</td>
                                                    <td>
                                                        @if($turnover['turnover_ratio'] > 2)
                                                            <span class="badge badge-success text-black">High Turnover</span>
                                                        @elseif($turnover['turnover_ratio'] > 1)
                                                            <span class="badge badge-warning">Medium Turnover</span>
                                                        @else
                                                            <span class="badge badge-danger text-black">Low Turnover</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-sync-alt fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No turnover data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#date_range').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        $('input[name="date_from"]').val(picker.startDate.format('YYYY-MM-DD'));
        $('input[name="date_to"]').val(picker.endDate.format('YYYY-MM-DD'));
    });

    $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('input[name="date_from"]').val('');
        $('input[name="date_to"]').val('');
    });

    // Set initial date range
    @if($dateFrom && $dateTo)
    $('#date_range').val('{{ date("m/d/Y", strtotime($dateFrom)) }} - {{ date("m/d/Y", strtotime($dateTo)) }}');
    @endif

    // Consumption Trends Chart
    const trendsData = @json($analytics['trends'] ?? []);
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendsData.map(item => item.formatted_date || ''),
            datasets: [{
                label: 'Daily Consumption',
                data: trendsData.map(item => item.total || 0),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Category Breakdown Chart
    const categoryData = @json($analytics['byCategory'] ?? []);
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    
    const categoryColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
    ];
    
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category || 'Unknown'),
            datasets: [{
                data: categoryData.map(item => item.total || 0),
                backgroundColor: categoryColors.slice(0, categoryData.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10,
                        fontSize: 12
                    }
                }
            }
        }
    });

    // Auto-submit form when filters change
    $('#store_location, #medication_type').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
