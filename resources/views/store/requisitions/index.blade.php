@extends('layouts.app_main_layout')

@section('page_title', 'Store Requisitions')
@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $statistics['total_requisitions'] ?? 0 }}</h4>
                                    <p class="mb-0">Total Requisitions</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $statistics['pending_requisitions'] ?? 0 }}</h4>
                                    <p class="mb-0">Pending Approval</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $statistics['issued_requisitions'] ?? 0 }}</h4>
                                    <p class="mb-0">Issued Today</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>${{ number_format($statistics['total_value'] ?? 0, 0) }}</h4>
                                    <p class="mb-0">Total Value</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Store Requisitions</h5>
                    <a href="{{ route('store.requisitions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Requisition
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search requisitions..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="priority" class="form-control">
                                    <option value="">All Priority</option>
                                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Requisitions Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Requisition #</th>
                                    <th>Date</th>
                                    <th>Requesting Location</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Total Cost</th>
                                    <th>Requested By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requisitions as $requisition)
                                <tr>
                                    <td>
                                        <a href="{{ route('store.requisitions.show', $requisition) }}" class="text-primary">
                                            {{ $requisition->requisition_number }}
                                        </a>
                                    </td>
                                    <td>{{ $requisition->requisition_date ? $requisition->requisition_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $requisition->requestingLocation->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $priorityColors = ['normal' => 'success', 'urgent' => 'warning', 'emergency' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $priorityColors[$requisition->priority] ?? 'secondary' }}">
                                            {{ ucfirst($requisition->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'submitted' => 'info',
                                                'approved' => 'warning',
                                                'issued' => 'success',
                                                'cancelled' => 'danger',
                                                'partially_approved' => 'warning'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$requisition->status] ?? 'secondary' }}">
                                            {{ ucwords(str_replace('_', ' ', $requisition->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light">{{ $requisition->items_count ?? 0 }} items</span>
                                    </td>
                                    <td>${{ number_format($requisition->total_estimated_cost ?? 0, 2) }}</td>
                                    <td>{{ $requisition->requestedBy->first_name ?? '' }} {{ $requisition->requestedBy->last_name ?? '' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('store.requisitions.show', $requisition) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($requisition->status === 'draft')
                                                <a href="{{ route('store.requisitions.edit', $requisition) }}" class="btn btn-outline-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user() && auth()->user()->isAdmin())
                                                @if($requisition->status === 'cancelled')
                                                    <form action="{{ route('store.requisitions.destroy', $requisition) }}" method="POST" class="d-inline" data-confirm="Are you sure you want to delete this requisition?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($requisition->status === 'submitted')
                                                    <form action="{{ route('store.requisitions.verify', $requisition) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Approve" onclick="return confirm('Approve this requisition?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($requisition->status, ['approved', 'partially_approved']))
                                                    <form action="{{ route('store.requisitions.issue', $requisition) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Issue" onclick="return confirm('Issue this requisition?')">
                                                            <i class="fas fa-shipping-fast"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                                        <p>No requisitions found</p>
                                        <a href="{{ route('store.requisitions.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create First Requisition
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Pagination -->
                    @if(isset($requisitions) && method_exists($requisitions, 'hasPages') && $requisitions->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $requisitions->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit forms with confirmation
    $('form[data-confirm]').on('submit', function(e) {
        const message = $(this).data('confirm');
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
