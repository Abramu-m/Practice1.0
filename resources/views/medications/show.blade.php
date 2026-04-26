@extends('layouts.app_main_layout')

@section('page_title', 'Medication Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('medications.edit', $medication->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Generic Name:</label>
                                <p class="form-control-plaintext"><strong>{{ $medication->generic_name ?: 'N/A' }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Brand Name:</label>
                                <p class="form-control-plaintext"><strong>{{ $medication->brand_name ?: 'N/A' }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="form-control-plaintext">Strength: <strong>{{ $medication->strength ?: 'N/A' }}</strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="form-control-plaintext">Formulation: <strong>{{ $medication->formulation->description ?? 'N/A' }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="form-control-plaintext">Dispensing Unit: 
                                    <strong>
                                        @if($medication->dispensingUnit)
                                            {{ $medication->dispensingUnit->unit_name }} ({{ $medication->dispensingUnit->unit_code }})
                                            @if($medication->dispensingUnit->unit_symbol)
                                                - {{ $medication->dispensingUnit->unit_symbol }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="form-control-plaintext">Barcode: <strong>{{ $medication->barcode ?: 'N/A' }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="form-control-plaintext">
                                    Category: 
                                    @if($medication->storeCategory)
                                        <span class="badge bg-info text-black">{{ $medication->storeCategory->description }}</span>
                                    @else
                                        <span class="text-muted">No Category</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="form-control-plaintext">Description: <strong>{{ $medication->description ?: 'No description available' }}</strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Stock Quantity:</label>
                                <p class="form-control-plaintext"><strong>{{ number_format($medication->stock_quantity) }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Reorder Level:</label>
                                <p class="form-control-plaintext"><strong>{{ number_format($medication->reorder_level) }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Status:</label>
                                <p class="form-control-plaintext">
                                    @if($medication->is_active)
                                        <span class="badge bg-success text-black">Active</span>
                                    @else
                                        <span class="badge bg-danger text-black">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Stock Status:</label>
                                <p class="form-control-plaintext">
                                    <span class="text-black badge badge-{{ $medication->stock_badge_class }} text-black">
                                        {{ $medication->stock_status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Created At:</label>
                                <p class="form-control-plaintext">{{ $medication->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Last Updated:</label>
                                <p class="form-control-plaintext">{{ $medication->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prescription History Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Prescription History</h3>
                </div>
                <div class="card-body">
                    @if($medication->prescriptions && $medication->prescriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($medication->prescriptions->take(10) as $prescription)
                                        <tr>
                                            <td>{{ $prescription->created_at->format('M d, Y') }}</td>
                                            <td>{{ $prescription->patient->name ?? 'N/A' }}</td>
                                            <td>{{ $prescription->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($medication->prescriptions->count() > 10)
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    Showing last 10 prescriptions of {{ $medication->prescriptions->count() }} total
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>No prescription history found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Stock Information Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Stock Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $medication->stock_badge_class }}">
                            <i class="fas fa-pills"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Stock</span>
                            <span class="info-box-number">{{ number_format($medication->stock_quantity) }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $medication->stock_badge_class }}">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stock Status</span>
                            <span class="info-box-number">{{ $medication->stock_status }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reorder Alert</span>
                            <span class="info-box-number">
                                @if($medication->is_low_stock)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success text-black">OK</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('medications.edit', $medication->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Medication
                        </a>
                        <button type="button" class="btn btn-success" onclick="adjustStock()">
                            <i class="fas fa-plus"></i> Adjust Stock
                        </button>
                        <button type="button" class="btn btn-warning" onclick="prescribe()">
                            <i class="fas fa-prescription"></i> Prescribe
                        </button>
                        <form action="{{ route('medications.destroy', $medication->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this medication?')">
                                <i class="fas fa-trash"></i> Delete Medication
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function adjustStock() {
    // Implementation for adjusting stock
    alert('Adjust Stock functionality will be implemented');
}

function prescribe() {
    // Implementation for prescribing medication
    alert('Prescribe functionality will be implemented');
}
</script>
@endsection
