@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Location Details: {{ $location->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('store-locations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('store-locations.edit', $location) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Name</th>
                                    <td>{{ $location->name }}</td>
                                </tr>
                                <tr>
                                    <th>Code</th>
                                    <td><code>{{ $location->code }}</code></td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td><span class="badge bg-info">{{ ucfirst($location->type) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge {{ $location->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Manager</th>
                                    <td>{{ $location->manager_name ?: 'Not assigned' }}</td>
                                </tr>
                                <tr>
                                    <th>Manager Contact</th>
                                    <td>{{ $location->manager_contact ?: 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Can Request</th>
                                    <td>
                                        <span class="badge {{ $location->can_request ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $location->can_request ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Can Issue</th>
                                    <td>
                                        <span class="badge {{ $location->can_issue ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $location->can_issue ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Can Receive</th>
                                    <td>
                                        <span class="badge {{ $location->can_receive ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $location->can_receive ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Requires Approval</th>
                                    <td>
                                        <span class="badge {{ $location->requires_approval ? 'bg-warning' : 'bg-secondary' }}">
                                            {{ $location->requires_approval ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order</th>
                                    <td>{{ $location->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Created</th>
                                    <td>{{ $location->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($location->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Description</h5>
                                <p class="text-muted">{{ $location->description }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Stock Overview</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="stockTable">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Type</th>
                                            <th>Batch Number</th>
                                            <th>Current Quantity</th>
                                            <th>Expiry Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($locationItems as $stock)
                                            <tr class="{{ $stock->is_expired ? 'table-danger' : ($stock->is_expiring_soon ? 'table-warning' : '') }}">
                                                <td>{{ $stock->medication->generic_name ?? 'Unknown' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $stock->medication->isMedication() ? 'Medication' : 'Consumable' }}
                                                    </span>
                                                </td>
                                                <td>{{ $stock->batch_number ?: 'N/A' }}</td>
                                                <td>{{ $stock->current_quantity }}</td>
                                                <td>{{ $stock->expiry_date ? $stock->expiry_date->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    @if($stock->is_expired)
                                                        <span class="badge bg-danger">Expired</span>
                                                    @elseif($stock->is_expiring_soon)
                                                        <span class="badge bg-warning">Expiring Soon</span>
                                                    @elseif($stock->current_quantity <= 0)
                                                        <span class="badge bg-secondary">Out of Stock</span>
                                                    @else
                                                        <span class="badge bg-success">In Stock</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No stock items found for this location.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#stockTable').DataTable({
        "pageLength": 10,
        "ordering": true,
        "searching": true,
        "responsive": true
    });
});
</script>
@endpush
@endsection
