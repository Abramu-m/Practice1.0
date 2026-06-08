@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Transaction Details' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Transaction Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Transaction Details
                    </h3>
                    <div class="card-tools">
                        <!-- Receipt Actions -->
                        @if($transaction->transaction_type == 'income')
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-print"></i> Receipt
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('financial.receipts.preview', $transaction) }}" target="_blank">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                                <a class="dropdown-item" href="{{ route('financial.receipts.generate', $transaction) }}?format=pdf" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Download PDF
                                </a>
                                <a class="dropdown-item" href="{{ route('financial.receipts.print', $transaction) }}" target="_blank">
                                    <i class="fas fa-print"></i> Print (Thermal)
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="emailReceipt({{ $transaction->id }})">
                                    <i class="fas fa-envelope"></i> Email Receipt
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        <a href="{{ route('financial.transactions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Transactions
                        </a>
                        @if($transaction->status == 'completed' && $transaction->can_edit)
                            <a href="{{ route('financial.transactions.edit', $transaction) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Transaction Number:</strong></td>
                                    <td>{{ $transaction->transaction_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ $transaction->transaction_date->format('F j, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge {{ $transaction->transaction_type_badge }}">
                                            {{ ucfirst($transaction->transaction_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->category)) }}</td>
                                </tr>
                                @if($transaction->subcategory)
                                <tr>
                                    <td><strong>Subcategory:</strong></td>
                                    <td>{{ $transaction->subcategory }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td>
                                        <span class="h4 {{ $transaction->transaction_type == 'income' ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->transaction_type == 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</td>
                                </tr>
                                @if($transaction->payment_reference)
                                <tr>
                                    <td><strong>Payment Reference:</strong></td>
                                    <td>{{ $transaction->payment_reference }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Source Type:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->source_type)) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge {{ $transaction->status_badge }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>{{ $transaction->creator->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created At:</strong></td>
                                    <td>{{ $transaction->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($transaction->description)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Description</h5>
                            <p class="bg-light p-3 rounded">{{ $transaction->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($transaction->notes)
                    <div class="row">
                        <div class="col-12">
                            <h5>Notes</h5>
                            <p class="bg-light p-3 rounded">{{ $transaction->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Payment Breakdown (for insurance transactions) -->
                    @if($transaction->patient_paid_amount > 0 || $transaction->insurance_covered_amount > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Payment Breakdown</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Patient Paid</span>
                                            <span class="info-box-number">Tsh {{ number_format($transaction->patient_paid_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Insurance Covered</span>
                                            <span class="info-box-number">Tsh {{ number_format($transaction->insurance_covered_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Approval Information -->
                    @if($transaction->approved_by)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Approval Information</h5>
                            <table class="table table-borderless bg-light">
                                <tr>
                                    <td><strong>Approved By:</strong></td>
                                    <td>{{ $transaction->approver->name ?? 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Approved At:</strong></td>
                                    <td>{{ $transaction->approved_at ? $transaction->approved_at->format('M j, Y g:i A') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                @if($transaction->status == 'pending')
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success" 
                                    onclick="approveTransaction({{ $transaction->id }})">
                                <i class="fas fa-check"></i> Approve Transaction
                            </button>
                            <button type="button" class="btn btn-danger ms-2" 
                                    onclick="cancelTransaction({{ $transaction->id }})">
                                <i class="fas fa-times"></i> Cancel Transaction
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <!-- Related Information -->
            @if($transaction->patient)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Patient Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $transaction->patient->first_name }} {{ $transaction->patient->last_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>MR Number:</strong></td>
                            <td>{{ $transaction->patient->mr_number ?? 'N/A' }}</td>
                        </tr>
                        @if($transaction->visit)
                        <tr>
                            <td><strong>Visit Date:</strong></td>
                            <td>{{ $transaction->visit->visit_date ? $transaction->visit->visit_date->format('M j, Y') : 'N/A' }}</td>
                        </tr>
                        @endif
                    </table>
                    @if($transaction->patient)
                    <a href="{{ route('patients.show', $transaction->patient) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> View Patient Details
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Recent Related Transactions -->
            @if($transaction->patient)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Recent Patient Transactions
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $recentTransactions = \App\Models\FinancialTransaction::where('patient_id', $transaction->patient_id)
                            ->where('id', '!=', $transaction->id)
                            ->latest('transaction_date')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @forelse($recentTransactions as $recent)
                        <div class="mb-2 p-2 border rounded">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">{{ $recent->transaction_date->format('M j, Y') }}</small>
                                <span class="badge {{ $recent->transaction_type_badge }}">
                                    {{ $recent->transaction_type == 'income' ? '+' : '-' }}${{ number_format($recent->amount, 2) }}
                                </span>
                            </div>
                            <div class="text-sm">{{ $recent->category }}</div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No recent transactions found</p>
                    @endforelse
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('financial.transactions.create') }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-plus"></i> Create New Transaction
                    </a>
                    <a href="{{ route('financial.transactions.export', ['transaction_id' => $transaction->id]) }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-download"></i> Export This Transaction
                    </a>
                    <a href="{{ route('financial.dashboard') }}" class="btn btn-info w-100">
                        <i class="fas fa-chart-line"></i> View Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                Are you sure you want to perform this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    $('#confirmationModalBody').text('Are you sure you want to approve this transaction? This action cannot be undone.');
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

function emailReceipt(transactionId) {
    $('#emailReceiptModal').modal('show');
}

function sendReceiptEmail() {
    const email = $('#receiptEmail').val();
    const transactionId = {{ $transaction->id }};
    
    if (!email) {
        alert('Please enter an email address.');
        return;
    }
    
    $.post(`/financial/receipts/${transactionId}/email`, {
        email: email,
        _token: '{{ csrf_token() }}'
    }).done(function(response) {
        if (response.success) {
            alert('Receipt emailed successfully!');
            $('#emailReceiptModal').modal('hide');
            $('#receiptEmail').val('');
        } else {
            alert('Error: ' + response.message);
        }
    }).fail(function() {
        alert('Error sending email. Please try again.');
    });
}
</script>

<!-- Email Receipt Modal -->
<div class="modal fade" id="emailReceiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="receiptEmail">Email Address:</label>
                    <input type="email" 
                           class="form-control" 
                           id="receiptEmail" 
                           value="{{ $transaction->patient->email ?? '' }}" 
                           placeholder="Enter email address">
                </div>
                <p class="text-muted">
                    <small>The receipt will be sent as a PDF attachment to the specified email address.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendReceiptEmail()">
                    <i class="fas fa-envelope"></i> Send Receipt
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
