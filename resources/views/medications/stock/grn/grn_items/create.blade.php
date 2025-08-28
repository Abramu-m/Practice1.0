@extends('layouts.app_main_layout')

@section('page_title', 'Add Items to GRN')

@section('styles')
<style>
    .grn-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .grn-summary h3 {
        margin: 0;
        font-weight: 600;
    }
    
    .grn-summary .grn-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }
    
    .grn-summary .grn-details span {
        display: block;
        margin-bottom: 5px;
    }
    
    .wizard-steps {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .step {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .step.completed {
        background: #d4edda;
        border-left: 4px solid #28a745;
    }
    
    .step.active {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }
    
    .step.pending {
        background: #f8f9fa;
        border-left: 4px solid #6c757d;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: bold;
    }
    
    .step.completed .step-number {
        background: #28a745;
        color: white;
    }
    
    .step.active .step-number {
        background: #ffc107;
        color: #212529;
    }
    
    .step.pending .step-number {
        background: #6c757d;
        color: white;
    }
    
    .step-content h5 {
        margin: 0 0 5px 0;
        font-weight: 600;
    }
    
    .step-content p {
        margin: 0;
        color: #6c757d;
    }
    
    .action-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .action-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease;
    }
    
    .action-card:hover {
        transform: translateY(-5px);
    }
    
    .action-card .icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    
    .action-card.add-items .icon {
        color: #28a745;
    }
    
    .action-card.manage-items .icon {
        color: #007bff;
    }
    
    .action-card.review-grn .icon {
        color: #ffc107;
    }
    
    .quick-stats {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin-top: 20px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-value {
        font-weight: bold;
        font-size: 1.1em;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('medications.dashboard') }}">Medications</a></li>
            <li class="breadcrumb-item"><a href="{{ route('medications.stock.grn.index') }}">GRN</a></li>
            <li class="breadcrumb-item"><a href="{{ route('medications.stock.grn.show', $grn) }}">{{ $grn->grn_number }}</a></li>
            <li class="breadcrumb-item active">Add Items</li>
        </ol>
    </nav>

    <!-- GRN Summary -->
    <div class="grn-summary">
        <h3>{{ $grn->grn_number }} - Add Items</h3>
        <div class="grn-info">
            <div class="grn-details">
                <span><strong>Supplier:</strong> {{ $grn->supplier->name ?? 'N/A' }}</span>
                <span><strong>Date:</strong> {{ $grn->grn_date ? \Carbon\Carbon::parse($grn->grn_date)->format('d M Y') : 'N/A' }}</span>
                <span><strong>Status:</strong> {{ ucfirst($grn->status) }}</span>
            </div>
            <div class="grn-totals">
                <h4>${{ number_format($grn->total_amount, 2) }}</h4>
                <small>Current Total</small>
            </div>
        </div>
    </div>

    <!-- Process Steps -->
    <div class="wizard-steps">
        <h4 class="mb-3">GRN Processing Steps</h4>
        
        <div class="step completed">
            <div class="step-number">1</div>
            <div class="step-content">
                <h5>GRN Created</h5>
                <p>Basic GRN information has been entered and saved.</p>
            </div>
        </div>
        
        <div class="step {{ $grn->items->count() > 0 ? 'completed' : 'active' }}">
            <div class="step-number">2</div>
            <div class="step-content">
                <h5>Add Items</h5>
                <p>Add medications and consumables to the GRN with quantities and pricing.</p>
            </div>
        </div>
        
        <div class="step {{ $grn->status === 'received' ? 'active' : 'pending' }}">
            <div class="step-number">3</div>
            <div class="step-content">
                <h5>Mark as Received</h5>
                <p>Confirm that all items have been physically received and verified.</p>
            </div>
        </div>
        
        <div class="step {{ $grn->status === 'verified' ? 'active' : 'pending' }}">
            <div class="step-number">4</div>
            <div class="step-content">
                <h5>Verify & Approve</h5>
                <p>Review and verify all items, quantities, and pricing before posting.</p>
            </div>
        </div>
        
        <div class="step {{ $grn->status === 'posted' ? 'completed' : 'pending' }}">
            <div class="step-number">5</div>
            <div class="step-content">
                <h5>Post to Inventory</h5>
                <p>Post the GRN to update inventory levels and medication stock.</p>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="action-cards">
        @if(in_array($grn->status, ['draft', 'received']))
            <div class="action-card add-items">
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5>Add New Item</h5>
                <p>Add medications or consumables to this GRN</p>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus mr-2"></i>Add Item
                </button>
            </div>
        @endif
        
        @if($grn->items->count() > 0)
            <div class="action-card manage-items">
                <div class="icon">
                    <i class="fas fa-list-ul"></i>
                </div>
                <h5>Manage Items</h5>
                <p>View, edit, or remove items from this GRN</p>
                <a href="{{ route('medications.stock.grn.items.index', $grn) }}" class="btn btn-primary">
                    <i class="fas fa-cog mr-2"></i>Manage Items
                </a>
            </div>
        @endif
        
        <div class="action-card review-grn">
            <div class="icon">
                <i class="fas fa-eye"></i>
            </div>
            <h5>Review GRN</h5>
            <p>View complete GRN details and status</p>
            <a href="{{ route('medications.stock.grn.show', $grn) }}" class="btn btn-warning">
                <i class="fas fa-file-alt mr-2"></i>View GRN
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    @if($grn->items->count() > 0)
        <div class="quick-stats">
            <h5 class="mb-3">Current GRN Summary</h5>
            
            <div class="stat-item">
                <span>Total Items:</span>
                <span class="stat-value">{{ $grn->items->count() }}</span>
            </div>
            
            <div class="stat-item">
                <span>Total Quantity:</span>
                <span class="stat-value">{{ number_format($grn->items->sum('received_quantity'), 2) }}</span>
            </div>
            
            <div class="stat-item">
                <span>Gross Amount:</span>
                <span class="stat-value">${{ number_format($grn->items->sum('total_cost'), 2) }}</span>
            </div>
            
            <div class="stat-item">
                <span>Total Discount:</span>
                <span class="stat-value text-danger">${{ number_format($grn->items->sum('discount_amount'), 2) }}</span>
            </div>
            
            <div class="stat-item">
                <span>Total Tax:</span>
                <span class="stat-value text-info">${{ number_format($grn->items->sum('tax_amount'), 2) }}</span>
            </div>
            
            <div class="stat-item">
                <span class="font-weight-bold">Net Amount:</span>
                <span class="stat-value text-success">${{ number_format($grn->items->sum('net_amount'), 2) }}</span>
            </div>
        </div>
    @endif

    <!-- Recent Items -->
    @if($grn->items->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Items Added</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Item</th>
                                <th>Batch</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Net Amount</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grn->items->take(5) as $item)
                                <tr>
                                    <td>
                                        @if($item->medication)
                                            <strong>{{ $item->medication->generic_name }}</strong><br>
                                            <small class="text-muted">{{ $item->medication->brand_name ?? 'N/A' }}</small>
                                        @else
                                            <strong>Unknown Item</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $item->batch_number }}</span>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($item->received_quantity, 2) }}
                                    </td>
                                    <td class="text-right">
                                        <strong>${{ number_format($item->net_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($grn->items->count() > 5)
                    <div class="card-footer text-center">
                        <a href="{{ route('medications.stock.grn.items.index', $grn) }}" class="btn btn-link">
                            View All {{ $grn->items->count() }} Items
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Add Item Modal -->
@include('medications.stock.grn.grn_items.add-modal')

@endsection

@section('scripts')
<script>
    // Auto-refresh the page when an item is added successfully
    @if(session('success'))
        setTimeout(function() {
            location.reload();
        }, 2000);
    @endif
</script>
@endsection
