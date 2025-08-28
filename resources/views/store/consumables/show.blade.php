@extends('layouts.app_main_layout')

@section('page_title', 'Consumable Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Consumable Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('store.consumables.edit', $consumable->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name:</label>
                                <p class="form-control-plaintext">{{ $consumable->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code:</label>
                                <p class="form-control-plaintext">{{ $consumable->code }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category:</label>
                                <p class="form-control-plaintext">
                                    @if($consumable->category)
                                        <span class="badge badge-info text-black">{{ $consumable->category->name }}</span>
                                    @else
                                        <span class="text-muted">No Category</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit:</label>
                                <p class="form-control-plaintext">
                                    @if($consumable->unit)
                                        {{ $consumable->unit->name }} ({{ $consumable->unit->abbreviation }})
                                    @else
                                        <span class="text-muted">No Unit</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <p class="form-control-plaintext">{{ $consumable->description ?: 'No description available' }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reorder Level:</label>
                                <p class="form-control-plaintext">{{ number_format($consumable->reorder_level) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Maximum Level:</label>
                                <p class="form-control-plaintext">{{ number_format($consumable->maximum_level) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Barcode:</label>
                                <p class="form-control-plaintext">{{ $consumable->barcode ?: 'No barcode' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status:</label>
                                <p class="form-control-plaintext">
                                    @if($consumable->is_active)
                                        <span class="badge badge-success text-black">Active</span>
                                    @else
                                        <span class="badge badge-danger text-black">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Created At:</label>
                                <p class="form-control-plaintext">{{ $consumable->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Updated:</label>
                                <p class="form-control-plaintext">{{ $consumable->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
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
                        <span class="info-box-icon bg-{{ $consumable->getCurrentStock() > 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Stock</span>
                            <span class="info-box-number">{{ number_format($consumable->getCurrentStock()) }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $consumable->stock_status_color }}">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stock Status</span>
                            <span class="info-box-number">{{ ucfirst(str_replace('_', ' ', $consumable->stock_status)) }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reorder Alert</span>
                            <span class="info-box-number">
                                @if($consumable->needsReorder())
                                    <span class="badge badge-warning">Needs Reorder</span>
                                @else
                                    <span class="badge badge-success text-black">OK</span>
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
                        <a href="{{ route('store.consumables.edit', $consumable->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Consumable
                        </a>
                        <button type="button" class="btn btn-success" onclick="addStock()">
                            <i class="fas fa-plus"></i> Add Stock
                        </button>
                        <button type="button" class="btn btn-warning" onclick="adjustStock()">
                            <i class="fas fa-adjust"></i> Adjust Stock
                        </button>
                        <form action="{{ route('store.consumables.destroy', $consumable->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this consumable?')">
                                <i class="fas fa-trash"></i> Delete Consumable
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
function addStock() {
    // Implementation for adding stock
    alert('Add Stock functionality will be implemented');
}

function adjustStock() {
    // Implementation for adjusting stock
    alert('Adjust Stock functionality will be implemented');
}
</script>
@endsection
