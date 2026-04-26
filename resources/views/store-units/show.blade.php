@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <!-- Navigation Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Inventory</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('store-units.index') }}">Store Units</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $storeUnit->name }} Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row"> 
        <!-- Main Content -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Unit Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('store-units.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('store-units.edit', $storeUnit) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ID</th>
                                    <td>{{ $storeUnit->id }}</td>
                                </tr>
                                <tr>
                                    <th>Unit Name</th>
                                    <td>{{ $storeUnit->name }}</td>
                                </tr>
                                <tr>
                                    <th>Unit Code</th>
                                    <td><span class="badge bg-secondary">{{ $storeUnit->code }}</span></td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>
                                        @if($storeUnit->type === 'store')
                                            <span class="badge bg-info">Store Only</span>
                                        @elseif($storeUnit->type === 'dispensing')
                                            <span class="badge bg-warning">Dispensing Only</span>
                                        @else
                                            <span class="badge bg-success">Both Store & Dispensing</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge {{ $storeUnit->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $storeUnit->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Created At</th>
                                    <td>{{ $storeUnit->created_at ? $storeUnit->created_at->format('d M Y, H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $storeUnit->updated_at ? $storeUnit->updated_at->format('d M Y, H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Usage Capabilities</th>
                                    <td>
                                        @if($storeUnit->canBeUsedForStore())
                                            <span class="badge bg-info me-1">Store</span>
                                        @endif
                                        @if($storeUnit->canBeUsedForDispensing())
                                            <span class="badge bg-warning">Dispensing</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Display Name</th>
                                    <td>{{ $storeUnit->display_name }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($storeUnit->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Description</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $storeUnit->description }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Usage Statistics -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Usage Statistics</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-box"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">GRN Items (Store)</span>
                                            <span class="info-box-number">{{ $storeUnit->grnItemsAsStore()->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-pills"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">GRN Items (Dispensing)</span>
                                            <span class="info-box-number">{{ $storeUnit->grnItemsAsDispensing()->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-calculator"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Usage</span>
                                            <span class="info-box-number">{{ $storeUnit->grnItemsAsStore()->count() + $storeUnit->grnItemsAsDispensing()->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('store-units.edit', $storeUnit) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Unit
                            </a>
                            <form action="{{ route('store-units.toggle-status', $storeUnit) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="btn {{ $storeUnit->is_active ? 'btn-secondary' : 'btn-success' }}">
                                    <i class="fas fa-toggle-{{ $storeUnit->is_active ? 'off' : 'on' }}"></i> 
                                    {{ $storeUnit->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                        <div>
                            @if($storeUnit->grnItemsAsStore()->count() == 0 && $storeUnit->grnItemsAsDispensing()->count() == 0)
                                <form action="{{ route('store-units.destroy', $storeUnit) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this store unit?')">
                                        <i class="fas fa-trash"></i> Delete Unit
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-info-circle"></i> Cannot delete unit that is being used
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
