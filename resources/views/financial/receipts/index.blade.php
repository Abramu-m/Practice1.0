@extends('layouts.app_main_layout')

@section('page_title', 'Receipt Management')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Receipt Management</h2>
                    <p class="text-muted mb-0">Generate and manage patient receipts and financial reports</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateReceiptModal">
                        <i class="fas fa-plus"></i> Generate Receipt
                    </button>
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#patientStatementModal">
                        <i class="fas fa-file-invoice"></i> Patient Statement
                    </button>
                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#dailySummaryModal">
                        <i class="fas fa-chart-line"></i> Daily Summary
                    </button>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-white-75 small">Today's Receipts</div>
                                    <div class="text-lg fw-bold">{{ $todayReceipts ?? 0 }}</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-receipt fa-2x text-white-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-white-75 small">Today's Revenue</div>
                                    <div class="text-lg fw-bold">${{ number_format($todayRevenue ?? 0, 2) }}</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x text-white-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-white-75 small">Pending Statements</div>
                                    <div class="text-lg fw-bold">{{ $pendingStatements ?? 0 }}</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-white-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-white-75 small">This Month</div>
                                    <div class="text-lg fw-bold">${{ number_format($monthRevenue ?? 0, 2) }}</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-bar fa-2x text-white-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Receipts Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Receipts</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" placeholder="Search receipts..." id="searchReceipts" style="width: 200px;">
                        <select class="form-select form-select-sm" id="filterCategory" style="width: 150px;">
                            <option value="">All Categories</option>
                            <option value="consultation">Consultation</option>
                            <option value="investigation">Investigation</option>
                            <option value="medication">Medication</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="receiptsTable">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReceipts ?? [] as $receipt)
                                <tr>
                                    <td>
                                        <strong>{{ $receipt->receipt_number ?? 'R-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                    </td>
                                    <td>{{ $receipt->transaction_date->format('M d, Y H:i') }}</td>
                                    <td>
                                        @if($receipt->patient)
                                            <div>
                                                <strong>{{ $receipt->patient->first_name }} {{ $receipt->patient->last_name }}</strong>
                                                <br><small class="text-muted">ID: {{ $receipt->patient->patient_number ?? $receipt->patient->id }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $receipt->category === 'consultation' ? 'primary' : ($receipt->category === 'investigation' ? 'info' : ($receipt->category === 'medication' ? 'success' : 'secondary')) }}">
                                            {{ ucfirst($receipt->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($receipt->amount, 2) }}</strong>
                                        @if($receipt->insurance_covered_amount > 0)
                                            <br><small class="text-success">Insurance: ${{ number_format($receipt->insurance_covered_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst(str_replace('_', ' ', $receipt->payment_method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $receipt->status === 'completed' ? 'success' : ($receipt->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($receipt->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewReceipt({{ $receipt->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadReceipt({{ $receipt->id }})">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="emailReceipt({{ $receipt->id }})">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="printReceipt({{ $receipt->id }})">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-receipt fa-3x mb-3"></i>
                                            <p>No receipts found</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReceiptModal">
                                                Generate First Receipt
                                            </button>
                                        </div>
                                    </td>
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

<!-- Generate Receipt Modal -->
<div class="modal fade" id="generateReceiptModal" tabindex="-1" aria-labelledby="generateReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateReceiptModalLabel">Generate Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generateReceiptForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patient_id" class="form-label">Patient</label>
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">
                                        {{ $patient->first_name }} {{ $patient->last_name }} (ID: {{ $patient->patient_number ?? $patient->id }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_id" class="form-label">Transaction</label>
                                <select class="form-select" id="transaction_id" name="transaction_id" required>
                                    <option value="">Select Transaction</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="receipt_format" class="form-label">Format</label>
                                <select class="form-select" id="receipt_format" name="format" required>
                                    <option value="pdf">PDF Receipt</option>
                                    <option value="html">HTML Receipt</option>
                                    <option value="thermal">Thermal Print</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="delivery_method" class="form-label">Delivery</label>
                                <select class="form-select" id="delivery_method" name="delivery_method">
                                    <option value="download">Download</option>
                                    <option value="email">Email to Patient</option>
                                    <option value="print">Print Now</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="additional_notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3" placeholder="Any special notes for this receipt..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Patient Statement Modal -->
<div class="modal fade" id="patientStatementModal" tabindex="-1" aria-labelledby="patientStatementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patientStatementModalLabel">Generate Patient Statement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="patientStatementForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="statement_patient_id" class="form-label">Patient</label>
                                <select class="form-select" id="statement_patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">
                                        {{ $patient->first_name }} {{ $patient->last_name }} (ID: {{ $patient->patient_number ?? $patient->id }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_insurance" name="include_insurance" checked>
                            <label class="form-check-label" for="include_insurance">
                                Include insurance information
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate Statement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Daily Summary Modal -->
<div class="modal fade" id="dailySummaryModal" tabindex="-1" aria-labelledby="dailySummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailySummaryModalLabel">Generate Daily Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dailySummaryForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="summary_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="summary_date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_hourly" name="include_hourly" checked>
                            <label class="form-check-label" for="include_hourly">
                                Include hourly breakdown
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_categories" name="include_categories" checked>
                            <label class="form-check-label" for="include_categories">
                                Include category details
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Generate Summary</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize search functionality
    $('#searchReceipts').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#receiptsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Initialize category filter
    $('#filterCategory').on('change', function() {
        var value = $(this).val().toLowerCase();
        $('#receiptsTable tbody tr').filter(function() {
            if (value === '') {
                $(this).show();
            } else {
                var categoryText = $(this).find('td:nth-child(4)').text().toLowerCase();
                $(this).toggle(categoryText.indexOf(value) > -1);
            }
        });
    });

    // Patient selection change - load transactions
    $('#patient_id').on('change', function() {
        var patientId = $(this).val();
        var transactionSelect = $('#transaction_id');
        
        transactionSelect.empty().append('<option value="">Select Transaction</option>');
        
        if (patientId) {
            // Load patient transactions
            $.get(`/api/patients/${patientId}/transactions`, function(transactions) {
                transactions.forEach(function(transaction) {
                    transactionSelect.append(`
                        <option value="${transaction.id}">
                            ${transaction.description} - $${parseFloat(transaction.amount).toFixed(2)} (${new Date(transaction.transaction_date).toLocaleDateString()})
                        </option>
                    `);
                });
            });
        }
    });

    // Generate Receipt Form
    $('#generateReceiptForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var transactionId = $('#transaction_id').val();
        var submitBtn = $(this).find('button[type="submit"]');
        
        if (!transactionId) {
            showAlert('error', 'Please select a transaction');
            return;
        }
        
        submitBtn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: `/financial/receipts/${transactionId}/generate`,
            method: 'GET',
            data: {
                format: $('#receipt_format').val(),
                delivery_method: $('#delivery_method').val()
            },
            success: function(response) {
                if (response.success) {
                    if (response.download_url) {
                        window.open(response.download_url, '_blank');
                    }
                    
                    $('#generateReceiptModal').modal('hide');
                    
                    // Show success message
                    showAlert('success', 'Receipt generated successfully!');
                    
                    // Reload the page to show new receipt
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', response.message || 'Failed to generate receipt');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to generate receipt';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Generate Receipt');
            }
        });
    });

    // Patient Statement Form
    $('#patientStatementForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: '/financial/receipts/patient-statement',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (response.download_url) {
                        window.open(response.download_url, '_blank');
                    }
                    
                    $('#patientStatementModal').modal('hide');
                    showAlert('success', 'Patient statement generated successfully!');
                } else {
                    showAlert('error', response.message || 'Failed to generate statement');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to generate statement';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Generate Statement');
            }
        });
    });

    // Daily Summary Form
    $('#dailySummaryForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: '/financial/receipts/daily-summary',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (response.download_url) {
                        window.open(response.download_url, '_blank');
                    }
                    
                    $('#dailySummaryModal').modal('hide');
                    showAlert('success', 'Daily summary generated successfully!');
                } else {
                    showAlert('error', response.message || 'Failed to generate summary');
                }
            },
            error: function(xhr) {
                var errorMessage = 'Failed to generate summary';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Generate Summary');
            }
        });
    });
});

// Receipt action functions
function viewReceipt(id) {
    window.open(`/financial/receipts/${id}/view`, '_blank');
}

function downloadReceipt(id) {
    window.open(`/financial/receipts/${id}/download`, '_blank');
}

function emailReceipt(id) {
    if (confirm('Send receipt via email to patient?')) {
        $.post(`/financial/receipts/${id}/email`, function(response) {
            if (response.success) {
                showAlert('success', 'Receipt sent via email successfully!');
            } else {
                showAlert('error', response.message || 'Failed to send email');
            }
        }).fail(function() {
            showAlert('error', 'Failed to send email');
        });
    }
}

function printReceipt(id) {
    window.open(`/financial/receipts/${id}/print`, '_blank');
}

// Alert helper function
function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at top of container
    $('.container-fluid').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection
