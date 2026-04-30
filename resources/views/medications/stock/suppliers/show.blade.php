@extends('layouts.app_main_layout')

@section('page_title', 'Supplier Details - ' . $supplier->name)

@section('styles')
<style>
    .info-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .info-card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
    }
    
    .info-card-body {
        padding: 20px;
    }
    
    .info-item {
        margin-bottom: 15px;
    }
    
    .info-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 5px;
        display: block;
    }
    
    .info-value {
        color: #3a3b45;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .credit-status {
        padding: 8px 15px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    
    .credit-ok {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .credit-warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .credit-exceeded {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .stat-card {
        text-align: center;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .btn-action {
        margin: 0 2px;
        padding: 4px 8px;
        font-size: 12px;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">{{ $supplier->name }}</h2>
                    <p class="text-muted mb-0">Supplier ID: #{{ $supplier->id }}</p>
                </div>
                <div>
                    <a href="{{ route('medications.stock.suppliers.edit', $supplier->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Supplier
                    </a>
                    <a href="{{ route('medications.stock.suppliers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-primary text-white">
                <div class="stat-number">{{ $supplier->goodsReceivedNotes->count() }}</div>
                <div class="stat-label">Total GRNs</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-success text-white">
                <div class="stat-number">${{ number_format($supplier->goodsReceivedNotes->where('status', 'approved')->sum('total_amount'), 2) }}</div>
                <div class="stat-label">Total Value</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-warning text-white">
                <div class="stat-number">${{ number_format($supplier->getPendingAmount(), 2) }}</div>
                <div class="stat-label">Pending Amount</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Basic Information -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <span class="info-label">Supplier Name:</span>
                        <span class="info-value">{{ $supplier->name }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="status-badge {{ $supplier->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value">{{ $supplier->created_at->format('M d, Y \a\t H:i') }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value">{{ $supplier->updated_at->format('M d, Y \a\t H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Address Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <span class="info-label">Address:</span>
                        <span class="info-value">
                            @if($supplier->address)
                                {{ $supplier->address }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">City:</span>
                        <span class="info-value">
                            @if($supplier->city)
                                {{ $supplier->city }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Country:</span>
                        <span class="info-value">
                            @if($supplier->country)
                                {{ $supplier->country }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Postal Code:</span>
                        <span class="info-value">
                            @if($supplier->postal_code)
                                {{ $supplier->postal_code }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    @if($supplier->full_address)
                        <div class="info-item">
                            <span class="info-label">Full Address:</span>
                            <div class="info-value">
                                <small class="text-muted">{{ $supplier->full_address }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Business Information -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <h5 class="mb-0"><i class="fas fa-building"></i> Business Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <span class="info-label">Tax Number:</span>
                        <span class="info-value">
                            @if($supplier->tax_number)
                                {{ $supplier->tax_number }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">License Number:</span>
                        <span class="info-value">
                            @if($supplier->license_number)
                                {{ $supplier->license_number }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Financial Information</h5>
                </div>
                <div class="info-card-body">
                    <!-- Credit Status -->
                    @if($supplier->credit_limit > 0)
                        @php
                            $pendingAmount = $supplier->getPendingAmount();
                            $creditUsage = ($pendingAmount / $supplier->credit_limit) * 100;
                        @endphp
                        
                        @if($supplier->isCreditExceeded())
                            <div class="credit-status credit-exceeded">
                                <strong>Credit Limit Exceeded!</strong><br>
                                Pending: ${{ number_format($pendingAmount, 2) }} / Limit: ${{ number_format($supplier->credit_limit, 2) }}
                            </div>
                        @elseif($creditUsage > 80)
                            <div class="credit-status credit-warning">
                                <strong>Credit Warning!</strong><br>
                                {{ number_format($creditUsage, 1) }}% of credit limit used
                            </div>
                        @else
                            <div class="credit-status credit-ok">
                                <strong>Credit Status: OK</strong><br>
                                {{ number_format($creditUsage, 1) }}% of credit limit used
                            </div>
                        @endif
                    @endif
                    
                    <div class="info-item">
                        <span class="info-label">Credit Limit:</span>
                        <span class="info-value">
                            @if($supplier->credit_limit)
                                ${{ number_format($supplier->credit_limit, 2) }}
                            @else
                                <span class="text-muted">No limit set</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Credit Days:</span>
                        <span class="info-value">
                            @if($supplier->credit_days)
                                {{ $supplier->credit_days }} days
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Payment Terms:</span>
                        <span class="info-value">
                            @if($supplier->payment_terms)
                                {{ $supplier->payment_terms }}
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Pending Amount:</span>
                        <span class="info-value">
                            <strong>${{ number_format($supplier->getPendingAmount(), 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent GRNs -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Goods Received Notes (GRNs)</h5>
                </div>
                <div class="card-body">
                    @if($recentGRNs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>GRN Number</th>
                                        <th>Date</th>
                                        <th>Invoice Number</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentGRNs as $grn)
                                        <tr>
                                            <td>
                                                <strong>{{ $grn->grn_number }}</strong>
                                            </td>
                                            <td>{{ $grn->grn_date ? $grn->grn_date->format('M d, Y') : '-' }}</td>
                                            <td>{{ $grn->invoice_number ?? '-' }}</td>
                                            <td>${{ number_format($grn->total_amount ?? 0, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $grn->status == 'approved' ? 'success' : ($grn->status == 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($grn->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('medications.stock.grn.show', $grn->id) }}" 
                                                   class="btn btn-info btn-action" 
                                                   title="View GRN">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($supplier->goodsReceivedNotes->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('medications.stock.grn.index', ['supplier' => $supplier->id]) }}" 
                                   class="btn btn-outline-primary">
                                    View All GRNs for this Supplier
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>No GRNs Found</h5>
                                <p>This supplier doesn't have any goods received notes yet.</p>
                                <a href="{{ route('medications.stock.grn.create', ['supplier_id' => $supplier->id]) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create GRN
                                </a>
                            </div>
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
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can add AJAX calls here to refresh stats if needed
        }, 30000);
    });
</script>
@endsection
