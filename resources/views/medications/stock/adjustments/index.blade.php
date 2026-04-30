@extends('layouts.app_main_layout')

@section('page_title', 'Stock Adjustments')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stock Adjustments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('medications.index') }}">Medications</a></li>
                        <li class="breadcrumb-item active">Stock Adjustments</li>
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
                            <h3>{{ $statistics['total_adjustments'] ?? 0 }}</h3>
                            <p>Total Adjustments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $statistics['positive_adjustments'] ?? 0 }}</h3>
                            <p>Stock Increases</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $statistics['negative_adjustments'] ?? 0 }}</h3>
                            <p>Stock Decreases</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>${{ number_format($statistics['total_value_adjusted'] ?? 0, 2) }}</h3>
                            <p>Total Value Adjusted</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="card-title">Stock Adjustments</h3>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('medications.stock.adjustments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Adjustment
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
                                    <label for="type">Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All Types</option>
                                        <option value="increase" {{ $type == 'increase' ? 'selected' : '' }}>Increase</option>
                                        <option value="decrease" {{ $type == 'decrease' ? 'selected' : '' }}>Decrease</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="reason">Reason</label>
                                    <select name="reason" id="reason" class="form-control">
                                        <option value="all" {{ $reason == 'all' ? 'selected' : '' }}>All Reasons</option>
                                        @foreach($adjustmentReasons ?? [] as $key => $value)
                                            <option value="{{ $key }}" {{ $reason == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="location">Location</label>
                                    <select name="location" id="location" class="form-control">
                                        <option value="all" {{ $location == 'all' ? 'selected' : '' }}>All Locations</option>
                                        @foreach($storeLocations ?? [] as $storeLocation)
                                            <option value="{{ $storeLocation->id }}" {{ $location == $storeLocation->id ? 'selected' : '' }}>
                                                {{ $storeLocation->name }}
                                            </option>
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
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="date_to">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                                </div>
                            </div>
                            <div class="col-md-8"></div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label>&nbsp;</label>
                                    <a href="{{ route('medications.stock.adjustments.index') }}" class="btn btn-secondary w-100">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Adjustments Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Medication</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>User</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adjustments as $adjustment)
                                    <tr>
                                        <td>
                                            <strong>{{ $adjustment->reference_number }}</strong>
                                            @if($adjustment->batch_number)
                                                <br><small class="text-muted">Batch: {{ $adjustment->batch_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $adjustment->movement_date ? \Carbon\Carbon::parse($adjustment->movement_date)->format('M d, Y') : 'N/A' }}
                                            <br><small class="text-muted">{{ $adjustment->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $adjustment->medication->name ?? 'Unknown' }}</strong>
                                            @if($adjustment->medication && $adjustment->medication->strength)
                                                <br><small class="text-muted">{{ $adjustment->medication->strength }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $adjustment->fromLocation->name ?? $adjustment->toLocation->name ?? 'Unknown' }}</td>
                                        <td>
                                            @if($adjustment->quantity > 0)
                                                <span class="badge bg-success text-black">
                                                    <i class="fas fa-arrow-up"></i> Increase
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-arrow-down"></i> Decrease
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ abs($adjustment->quantity) }}</strong>
                                            @if($adjustment->unit_cost > 0)
                                                <br><small class="text-muted">${{ number_format($adjustment->unit_cost, 2) }} each</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-black">{{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}</span>
                                        </td>
                                        <td>{{ $adjustment->user->name ?? 'System' }}</td>
                                        <td>
                                            @if($adjustment->notes)
                                                <span class="text-truncate" style="max-width: 150px;" 
                                                      title="{{ $adjustment->notes }}">{{ $adjustment->notes }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <h5>No adjustments found</h5>
                                            <p>Try adjusting your search criteria or create a new adjustment.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($adjustments) && $adjustments->hasPages())
                        <div class="row mt-3">
                            <div class="col-sm-5">
                                <div class="dataTables_info">
                                    Showing {{ $adjustments->firstItem() }} to {{ $adjustments->lastItem() }} of {{ $adjustments->total() }} results
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="float-end">
                                    {{ $adjustments->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Adjustment Reasons Breakdown Card -->
            @if(!empty($statistics['reasons_breakdown']))
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Adjustment Reasons Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($statistics['reasons_breakdown'] as $reason => $count)
                                <div class="col-md-3 col-6">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-clipboard-list"></i>
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
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#type, #reason, #location').change(function() {
        $(this).closest('form').submit();
    });
    
    // Clear date inputs when clear button is clicked
    $('.btn-secondary').click(function(e) {
        e.preventDefault();
        $('#search, #date_from, #date_to').val('');
        $('#type, #reason, #location').val('all');
        window.location.href = $(this).attr('href');
    });
});
</script>
@endsection
