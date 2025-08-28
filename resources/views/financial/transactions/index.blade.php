@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Financial Transactions' }}
 @endsection

@section('Content_Description')
    {{ 'Manage and view all financial transactions in the system.' }}
@endsection

@section('main_content')
<div class="container-fluid">
    <!-- Filter Form -->
    <div class="card collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Advanced Filters
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <form method="GET" action="{{ route('financial.transactions.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select name="transaction_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="income" {{ request('transaction_type') == 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ request('transaction_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                <option value="consultation_fee" {{ request('category') == 'consultation_fee' ? 'selected' : '' }}>Consultation Fee</option>
                                <option value="investigation_fee" {{ request('category') == 'investigation_fee' ? 'selected' : '' }}>Investigation Fee</option>
                                <option value="medication_sale" {{ request('category') == 'medication_sale' ? 'selected' : '' }}>Medication Sale</option>
                                <option value="supplier_payment" {{ request('category') == 'supplier_payment' ? 'selected' : '' }}>Supplier Payment</option>
                                <option value="general_expense" {{ request('category') == 'general_expense' ? 'selected' : '' }}>General Expense</option>
                                <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Patient</label>
                            <input type="text" name="patient_search" class="form-control" 
                                   placeholder="Patient name..." value="{{ request('patient_search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Amount Range</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" name="amount_min" class="form-control" 
                                           placeholder="Min" step="0.01" value="{{ request('amount_min') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="amount_max" class="form-control" 
                                           placeholder="Max" step="0.01" value="{{ request('amount_max') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-block">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="{{ route('financial.transactions.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon">
                    <i class="fas fa-list"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transactions</span>
                    <span class="info-box-number">{{ $transactions->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon">
                    <i class="fas fa-arrow-up"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Income</span>
                    <span class="info-box-number">${{ number_format($summary['total_income'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon">
                    <i class="fas fa-arrow-down"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Expenses</span>
                    <span class="info-box-number">${{ number_format($summary['total_expenses'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box {{ $summary['net_balance'] >= 0 ? 'bg-success' : 'bg-warning' }}">
                <span class="info-box-icon">
                    <i class="fas fa-balance-scale"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Net Balance</span>
                    <span class="info-box-number">${{ number_format($summary['net_balance'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Financial Transactions
            </h3>
            <div class="card-tools">
                <a href="{{ route('financial.transactions.export', request()->query()) }}" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Export CSV
                </a>
                <a href="{{ route('financial.transactions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Transaction
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'transaction_number', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                Transaction #
                                @if(request('sort') == 'transaction_number')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'transaction_date', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                Date
                                @if(request('sort') == 'transaction_date')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                Amount
                                @if(request('sort') == 'amount')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Patient</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <a href="{{ route('financial.transactions.show', $transaction) }}" 
                                   class="text-primary">
                                    {{ $transaction->transaction_number }}
                                </a>
                            </td>
                            <td>{{ $transaction->transaction_date->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $transaction->transaction_type_badge }}">
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->category)) }}</td>
                            <td>
                                <span class="{{ $transaction->transaction_type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->transaction_type == 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->patient)
                                    <a href="{{ route('patients.show', $transaction->patient) }}" class="text-info">
                                        {{ $transaction->patient->first_name }} {{ $transaction->patient->last_name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($transaction->description, 50) }}</td>
                            <td>
                                <span class="badge {{ $transaction->status_badge }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('financial.transactions.show', $transaction) }}" 
                                       class="btn btn-info btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($transaction->status == 'pending')
                                        <button type="button" class="btn btn-success btn-sm" 
                                                onclick="approveTransaction({{ $transaction->id }})" 
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="cancelTransaction({{ $transaction->id }})" 
                                                title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    @if($transaction->status == 'completed' && $transaction->can_edit)
                                        <a href="{{ route('financial.transactions.edit', $transaction) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                No transactions found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalTitle">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                Are you sure you want to perform this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmationModalConfirm">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
<script>
function approveTransaction(transactionId) {
    $('#confirmationModalTitle').text('Approve Transaction');
    $('#confirmationModalBody').text('Are you sure you want to approve this transaction?');
    $('#confirmationModalConfirm').removeClass('btn-danger').addClass('btn-success').text('Approve');
    
    $('#confirmationModalConfirm').off('click').on('click', function() {
        $.post(`/financial/transactions/${transactionId}/approve`, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Error processing request. Please try again.');
        });
        $('#confirmationModal').modal('hide');
    });
    
    $('#confirmationModal').modal('show');
}

function cancelTransaction(transactionId) {
    $('#confirmationModalTitle').text('Cancel Transaction');
    $('#confirmationModalBody').text('Are you sure you want to cancel this transaction? This action cannot be undone.');
    $('#confirmationModalConfirm').removeClass('btn-success').addClass('btn-danger').text('Cancel Transaction');
    
    $('#confirmationModalConfirm').off('click').on('click', function() {
        $.post(`/financial/transactions/${transactionId}/cancel`, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Error processing request. Please try again.');
        });
        $('#confirmationModal').modal('hide');
    });
    
    $('#confirmationModal').modal('show');
}

// Auto-refresh every 2 minutes for real-time updates
setInterval(function() {
    if (!$('.modal').hasClass('show')) {
        location.reload();
    }
}, 120000);
</script>
@endsection
