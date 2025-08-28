@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Cash Sales - Medication</h1>
                <a href="{{ route('medication-cash-sales.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Cash Sale
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_sales'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-pills fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Awaiting Payment</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unpaid_sales'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ready to Dispense</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['paid_ready_to_dispense'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-pills fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_sales'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-success shadow">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Revenue</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">TSh {{ number_format($stats['daily_revenue'], 2) }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-3x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Sales</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Awaiting Payment</option>
                                <option value="dispensed" {{ request('status') == 'dispensed' ? 'selected' : '' }}>Dispensed - Payment Required</option>
                                <option value="ready_to_dispense" {{ request('status') == 'ready_to_dispense' ? 'selected' : '' }}>Paid - Ready to Dispense</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="sale_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="otc" {{ request('sale_type') == 'otc' ? 'selected' : '' }}>Over-the-Counter</option>
                                <option value="external_prescription" {{ request('sale_type') == 'external_prescription' ? 'selected' : '' }}>External Prescription</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Sale number..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cash Sales</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sale Number</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashSales as $sale)
                                <tr>
                                    <td>
                                        <strong>{{ $sale->sale_number }}</strong>
                                        @if($sale->status === 'cancelled')
                                            <br><small class="text-danger">
                                                <i class="fas fa-ban"></i> Cancelled
                                            </small>
                                        @elseif(isset($stockInfo[$sale->id]) && $stockInfo[$sale->id]['has_issues'])
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Stock insufficient
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $sale->sale_type == 'otc' ? 'OTC' : 'External Rx' }}
                                        </span>
                                    </td>
                                    <td>{{ $sale->patientCategory->description }}</td>
                                    <td>{{ $sale->items->count() }} item(s)</td>
                                    <td>TSh {{ number_format($sale->final_amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $sale->status_color }}">
                                            {{ $sale->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $sale->creator->name }}</td>
                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('medication-cash-sales.show', $sale) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        {{-- Only pharmacists and administrators can see dispense button --}}
                                        @if($sale->canBeDispensed() && (Auth::user()->isPharmacist() || (!Auth::user()->isCashier() && !Auth::user()->isReceptionist())))
                                            @php
                                                $hasStockIssues = isset($stockInfo[$sale->id]) && $stockInfo[$sale->id]['has_issues'];
                                            @endphp
                                            @if($hasStockIssues)
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Insufficient stock for some medications">
                                                    <i class="fas fa-pills"></i> Dispense
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('medication-cash-sales.dispense', $sale) }}" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Dispense this sale?')">
                                                        <i class="fas fa-pills"></i> Dispense
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        {{-- Receptionists and cashiers can process payments --}}
                                        @if($sale->canBePaid() && (Auth::user()->isReceptionist() || Auth::user()->isCashier() || Auth::user()->isAdmin()))
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $sale->id }}">
                                            <i class="fas fa-money-bill"></i> Pay
                                        </button>
                                        @endif

                                        {{-- Cancel button logic --}}
                                        @if(!$sale->is_paid && !$sale->dispensed_at)
                                            {{-- For unpaid sales - simple cancel (will delete) --}}
                                            <form method="POST" action="{{ route('medication-cash-sales.cancel', $sale) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this sale?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @elseif($sale->is_paid && Auth::user()->isAdmin())
                                            {{-- For paid sales - only admins can cancel with reason --}}
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $sale->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Payment Modal -->
                                @if($sale->canBePaid())
                                <div class="modal fade" id="paymentModal{{ $sale->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('medication-cash-sales.process-payment', $sale) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Process Payment - {{ $sale->sale_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Amount Due</label>
                                                        <input type="text" class="form-control" value="TSh {{ number_format($sale->final_amount, 2) }}" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Payment Method *</label>
                                                        <select name="payment_method" class="form-control" required>
                                                            <option value="">Select Method</option>
                                                            <option value="cash">Cash</option>
                                                            <option value="card">Card</option>
                                                            <option value="mobile_money">Mobile Money</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Amount Paid *</label>
                                                        <input type="number" name="amount_paid" class="form-control" step="0.01" min="{{ $sale->final_amount }}" value="{{ $sale->final_amount }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Process Payment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Cancellation Modal for Paid Sales -->
                                @if($sale->is_paid && Auth::user()->isAdmin())
                                <div class="modal fade" id="cancelModal{{ $sale->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('medication-cash-sales.cancel', $sale) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Cancel Paid Sale - {{ $sale->sale_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <strong>Warning:</strong> You are about to cancel a paid sale worth TSh {{ number_format($sale->final_amount, 2) }}.
                                                        This action requires administrator approval and a detailed reason.
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Cancellation Reason *</label>
                                                        <textarea name="cancel_reason" class="form-control" rows="4" placeholder="Provide a detailed reason for cancelling this paid sale (minimum 15 characters)..." required minlength="15"></textarea>
                                                        <small class="form-text text-muted">This reason will be logged for audit purposes.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this paid sale? This action will be logged.')">
                                                        <i class="fas fa-times"></i> Cancel Sale
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No cash sales found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $cashSales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>
@endsection
