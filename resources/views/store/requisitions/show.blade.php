@extends('layouts.app_main_layout')

@section('page_title', 'Requisition Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Requisition Details - {{ $requisition->requisition_number }}</h5>
                    <div class="d-flex gap-2">
                        <!-- Status Badge -->
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'submitted' => 'info',
                                'approved' => 'warning',
                                'partially_issued' => 'warning',
                                'fully_issued' => 'success',
                                'cancelled' => 'danger',
                                'rejected' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$requisition->status] ?? 'secondary' }} badge-lg">
                            {{ ucwords(str_replace('_', ' ', $requisition->status)) }}
                        </span>
                        
                        <a href="{{ route('store.requisitions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Requisition Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Requisition Number:</strong></td>
                                    <td>{{ $requisition->requisition_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Requisition Date:</strong></td>
                                    <td>{{ $requisition->requisition_date ? $requisition->requisition_date->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Requesting Location:</strong></td>
                                    <td>{{ $requisition->requestingLocation->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>
                                        @php
                                            $priorityColors = ['normal' => 'success', 'urgent' => 'warning', 'emergency' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $priorityColors[$requisition->priority] ?? 'secondary' }}">
                                            {{ ucfirst($requisition->priority) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Requested By:</strong></td>
                                    <td>{{ $requisition->requestedBy->first_name ?? '' }} {{ $requisition->requestedBy->last_name ?? '' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $statusColors[$requisition->status] ?? 'secondary' }}">
                                            {{ ucwords(str_replace('_', ' ', $requisition->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($requisition->approved_by)
                                <tr>
                                    <td><strong>Approved By:</strong></td>
                                    <td>{{ $requisition->approvedBy->first_name ?? '' }} {{ $requisition->approvedBy->last_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Approved At:</strong></td>
                                    <td>{{ $requisition->approved_at ? $requisition->approved_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                @endif
                                @if($requisition->issued_by)
                                <tr>
                                    <td><strong>Issued By:</strong></td>
                                    <td>{{ $requisition->issuedBy->first_name ?? '' }} {{ $requisition->issuedBy->last_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Issued At:</strong></td>
                                    <td>{{ $requisition->issued_at ? $requisition->issued_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Total Estimated Cost:</strong></td>
                                    <td><strong>Tsh {{ number_format($requisition->total_estimated_cost, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($requisition->purpose)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Purpose/Notes:</h6>
                                    <p class="mb-0">{{ $requisition->purpose }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    @if(in_array($requisition->status, ['submitted', 'approved', 'partially_issued']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                @if(auth()->user()->isAdmin())
                                    @if($requisition->status === 'submitted')
                                        <form action="{{ route('store.requisitions.verify', $requisition) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to approve this requisition?')">
                                                <i class="fas fa-check"></i> Approve Requisition
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(in_array($requisition->status, ['approved', 'partially_issued']))
                                        <form action="{{ route('store.requisitions.issue', $requisition) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to issue this requisition? This will transfer items from the main store.')">
                                                <i class="fas fa-shipping-fast"></i> Issue Requisition
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                
                                @if(!in_array($requisition->status, ['fully_issued', 'cancelled', 'rejected']))
                                    <form action="{{ route('store.requisitions.cancel', $requisition) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this requisition?')">
                                            <i class="fas fa-times"></i> Cancel Requisition
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger mt-2">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Items Table -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Requisition Items</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Medication</th>
                                            <th>Requested Qty</th>
                                            <th>Unit Cost</th>
                                            <th>Total Cost</th>
                                            <th>Status</th>
                                            @if(in_array($requisition->status, ['fully_issued', 'partially_issued']))
                                            <th>Issued Qty</th>
                                            @endif
                                            <th>Justification</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($requisition->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($item->item_type === 'medication' && $item->medication)
                                                    <strong>{{ $item->medication->generic_name }}</strong>
                                                    @if($item->medication->brand_name)
                                                        <br><small class="text-muted">{{ $item->medication->brand_name }}</small>
                                                    @endif
                                                    @if($item->medication->strength)
                                                        <br><small class="text-muted">{{ $item->medication->strength }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Unknown Item</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($item->requested_quantity) }}</td>
                                            <td>Tsh {{ number_format($item->unit_cost, 2) }}</td>
                                            <td>Tsh {{ number_format($item->total_cost, 2) }}</td>
                                            <td>
                                                @php
                                                    $itemStatusColors = [
                                                        'pending' => 'secondary',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'issued' => 'success',
                                                        'fully_issued' => 'success',
                                                        'partially_issued' => 'warning'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $itemStatusColors[$item->status ?? 'pending'] ?? 'secondary' }}">
                                                    {{ ucwords(str_replace('_', ' ', $item->status ?? 'pending')) }}
                                                </span>
                                            </td>
                                            @if(in_array($requisition->status, ['fully_issued', 'partially_issued']))
                                            <td>{{ number_format($item->issued_quantity ?? 0) }}</td>
                                            @endif
                                            <td>
                                                @if($item->justification)
                                                    <small>{{ $item->justification }}</small>
                                                @else
                                                    <small class="text-muted">No justification provided</small>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No items found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Movements (if issued) -->
                    @if(in_array($requisition->status, ['fully_issued', 'partially_issued']) && $stockMovements->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Stock Movements</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Medication</th>
                                            <th>Batch</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Quantity</th>
                                            <th>Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stockMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->movement_date->format('M d, Y H:i') }}</td>
                                            <td>{{ $movement->medication->generic_name ?? 'N/A' }}</td>
                                            <td>{{ $movement->batch_number }}</td>
                                            <td>{{ $movement->fromLocation->name ?? 'Main Store' }}</td>
                                            <td>{{ $movement->toLocation->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($movement->quantity) }}</td>
                                            <td>Tsh {{ number_format($movement->total_cost, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
