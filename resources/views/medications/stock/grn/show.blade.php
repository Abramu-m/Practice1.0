@extends('layouts.app_main_layout')

@section('page_title', 'GRN Details - ' . $grn->grn_number)

@section('styles')
<style>
    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    
    .info-value {
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    .status-badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }

    .status-draft { background-color: #ffc107; color: #212529; }

    @media print {
        .app-header,
        .app-sidebar,
        .app-footer,
        .no-print { display: none !important; }

        .app-wrapper, .app-main, .app-content, .container-fluid {
            margin: 0 !important; padding: 0 !important;
            width: 100% !important; background: #fff !important;
        }

        @page { margin: 10mm 12mm; }
    }
    .status-received { background-color: #17a2b8; color: white; }
    .status-verified { background-color: #28a745; color: white; }
    .status-posted { background-color: #007bff; color: white; }
    .status-cancelled { background-color: #dc3545; color: white; }
    
    .card-header h3 {
        margin: 0;
        color: #495057;
    }
    
    .btn {
        margin-right: 10px;
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
    
    .financial-summary {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 5px;
        padding: 15px;
    }
    
    .amount-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .amount-label {
        font-weight: 500;
    }
    
    .amount-value {
        font-weight: 600;
    }
    
    .net-amount {
        border-top: 2px solid #2196f3;
        padding-top: 8px;
        font-size: 1.1rem;
        color: #1976d2;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        GRN Details - {{ $grn->grn_number }}
                        <span class="badge status-badge status-{{ $grn->status }} ms-2">
                            {{ ucfirst($grn->status) }}
                        </span>
                    </h3>
                    <div>
                        @if($grn->status === 'draft')
                            <a href="{{ route('medications.stock.grn.edit', $grn) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('medications.stock.grn.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle"></i> Basic Information
                                </h5>
                                
                                <div class="info-label">GRN Number</div>
                                <div class="info-value">{{ $grn->grn_number }}</div>
                                
                                <div class="info-label">GRN Date</div>
                                <div class="info-value">
                                    @if($grn->grn_date instanceof \Carbon\Carbon)
                                        {{ $grn->grn_date->format('d M Y') }}
                                    @elseif($grn->grn_date)
                                        {{ \Carbon\Carbon::parse($grn->grn_date)->format('d M Y') }}
                                    @else
                                        <span class="text-muted">Not Set</span>
                                    @endif
                                </div>
                                
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="badge status-badge status-{{ $grn->status }}">
                                        {{ ucfirst($grn->status) }}
                                    </span>
                                </div>
                                
                                @if($grn->notes)
                                    <div class="info-label">Notes</div>
                                    <div class="info-value">{{ $grn->notes }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Supplier Information -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-truck"></i> Supplier Information
                                </h5>
                                
                                <div class="info-label">Supplier</div>
                                <div class="info-value">{{ $grn->supplier->name ?? 'N/A' }}</div>
                                
                                @if($grn->invoice_number)
                                    <div class="info-label">Invoice Number</div>
                                    <div class="info-value">{{ $grn->invoice_number }}</div>
                                @endif
                                
                                @if($grn->invoice_date)
                                    <div class="info-label">Invoice Date</div>
                                    <div class="info-value">{{ $grn->invoice_date->format('d M Y') }}</div>
                                @endif
                                
                                @if($grn->delivery_note_number)
                                    <div class="info-label">Delivery Note Number</div>
                                    <div class="info-value">{{ $grn->delivery_note_number }}</div>
                                @endif
                                
                                @if($grn->delivery_date)
                                    <div class="info-label">Delivery Date</div>
                                    <div class="info-value">{{ $grn->delivery_date->format('d M Y') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Personnel Information -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5 class="text-info mb-3">
                                    <i class="fas fa-users"></i> Personnel Information
                                </h5>
                                
                                @if($grn->receivedBy)
                                    <div class="info-label">Received By</div>
                                    <div class="info-value">{{ $grn->receivedBy->name }}</div>
                                @endif
                                
                                @if($grn->received_at)
                                    <div class="info-label">Received At</div>
                                    <div class="info-value">{{ $grn->received_at->format('d M Y H:i') }}</div>
                                @endif
                                
                                @if($grn->verifiedBy)
                                    <div class="info-label">Verified By</div>
                                    <div class="info-value">{{ $grn->verifiedBy->name }}</div>
                                @endif
                                
                                @if($grn->verified_at)
                                    <div class="info-label">Verified At</div>
                                    <div class="info-value">{{ $grn->verified_at->format('d M Y H:i') }}</div>
                                @endif
                                
                                @if($grn->postedBy)
                                    <div class="info-label">Posted By</div>
                                    <div class="info-value">{{ $grn->postedBy->name }}</div>
                                @endif
                                
                                @if($grn->posted_at)
                                    <div class="info-label">Posted At</div>
                                    <div class="info-value">{{ $grn->posted_at->format('d M Y H:i') }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Financial Summary -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5 class="text-warning mb-3">
                                    <i class="fas fa-money-bill-wave"></i> Financial Summary
                                </h5>
                                
                                <div class="financial-summary">
                                    <div class="amount-row">
                                        <span class="amount-label">Total Amount:</span>
                                        <span class="amount-value">Tsh {{ number_format($grn->total_amount, 2) }}</span>
                                    </div>
                                    
                                    @if($grn->discount_amount > 0)
                                        <div class="amount-row">
                                            <span class="amount-label">Discount:</span>
                                            <span class="amount-value text-danger">-${{ number_format($grn->discount_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($grn->tax_amount > 0)
                                        <div class="amount-row">
                                            <span class="amount-label">Tax:</span>
                                            <span class="amount-value">+${{ number_format($grn->tax_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="amount-row net-amount">
                                        <span class="amount-label">Net Amount:</span>
                                        <span class="amount-value">Tsh {{ number_format($grn->net_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audit Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-card">
                                <h5 class="text-secondary mb-3">
                                    <i class="fas fa-history"></i> Audit Information
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-label">Created At</div>
                                        <div class="info-value">{{ $grn->created_at ? $grn->created_at->format('d M Y H:i:s') : 'Not Available' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-label">Last Updated</div>
                                        <div class="info-value">{{ $grn->updated_at ? $grn->updated_at->format('d M Y H:i:s') : 'Not Available' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row mt-4 no-print">
                        <div class="col-md-12">
                            @if($grn->status === 'draft')
                                <button type="button" class="btn btn-success" onclick="updateStatus('received')">
                                    <i class="fas fa-check"></i> Mark as Received
                                </button>
                                <a href="{{ route('medications.stock.grn.edit', $grn) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit GRN
                                </a>
                                <button type="button" class="btn btn-danger" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancel GRN
                                </button>
                            @elseif($grn->status === 'received')
                                <button type="button" class="btn btn-info" onclick="updateStatus('verified')">
                                    <i class="fas fa-check-double"></i> Mark as Verified
                                </button>
                                <button type="button" class="btn btn-danger" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancel GRN
                                </button>
                            @elseif($grn->status === 'verified')
                                <button type="button" class="btn btn-primary" onclick="updateStatus('posted')">
                                    <i class="fas fa-paper-plane"></i> Post to Inventory
                                </button>
                                <button type="button" class="btn btn-danger" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancel GRN
                                </button>
                            @endif
                            
                            <button type="button" class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print GRN
                            </button>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Note:</strong> Ensure all details are correct before marking the GRN as received or verified.
                            </div>
                        </div>
                        @if(session('error'))
                            <div class="col-md-12">
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="col-md-12">
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include GRN Items Section -->
    @include('medications.stock.grn.grn_items.items-section')
</div>

<!-- Status Update Form (Hidden) -->
<form id="statusUpdateForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" id="newStatus">
</form>

@endsection

@section('scripts')
<!-- Select2 assets are loaded globally in the layout; keep initialization only -->

<script>
    function updateStatus(newStatus) {
        if (confirm('Are you sure you want to update the status to ' + newStatus + '?')) {
            document.getElementById('newStatus').value = newStatus;
            document.getElementById('statusUpdateForm').action = '{{ route("medications.stock.grn.update", $grn) }}';
            document.getElementById('statusUpdateForm').submit();
        }
    }
    
    // Print styling
    window.addEventListener('beforeprint', function() {
        document.querySelector('.card-header .btn').style.display = 'none';
        document.querySelector('.row.mt-4').style.display = 'none';
    });
    
    window.addEventListener('afterprint', function() {
        document.querySelector('.card-header .btn').style.display = '';
        document.querySelector('.row.mt-4').style.display = '';
    });
</script>
@endsection
