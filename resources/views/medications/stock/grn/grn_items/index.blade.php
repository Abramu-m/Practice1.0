@extends('layouts.app_main_layout')

@section('page_title', 'GRN Items - ' . $grn->grn_number)

@section('styles')
<style>
    .items-section {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .items-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        margin-bottom: 0;
    }
    
    .items-header h4 {
        margin: 0;
        font-weight: 600;
    }
    
    .items-content {
        padding: 20px;
    }
    
    .add-item-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .add-item-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        color: white;
    }
    
    .items-table {
        margin-top: 15px;
    }
    
    .items-table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #495057;
        padding: 12px 8px;
    }
    
    .items-table td {
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    .item-name {
        font-weight: 600;
        color: #495057;
    }
    
    .item-details {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .batch-info {
        font-size: 0.75rem;
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
        margin-top: 2px;
    }
    
    .expiry-warning {
        color: #dc3545;
        font-weight: 600;
    }
    
    .expiry-normal {
        color: #28a745;
    }
    
    .quantity-info {
        text-align: right;
    }
    
    .amount-info {
        text-align: right;
        font-weight: 600;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        margin: 0 2px;
    }
    
    .total-row {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }
    
    .status-draft { background-color: #ffc107; color: #212529; }
    .status-received { background-color: #17a2b8; color: white; }
    .status-verified { background-color: #28a745; color: white; }
    .status-posted { background-color: #007bff; color: white; }
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
            <li class="breadcrumb-item active">Items</li>
        </ol>
    </nav>

    <!-- GRN Basic Info -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">GRN Number</h6>
                            <p class="mb-0 fw-bold">{{ $grn->grn_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Supplier</h6>
                            <p class="mb-0">{{ $grn->supplier->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Status</h6>
                            <span class="badge status-badge status-{{ $grn->status }}">
                                {{ ucfirst($grn->status) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Total Amount</h6>
                            <p class="mb-0 fw-bold text-success">${{ number_format($grn->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="items-section">
        <div class="items-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-list-ul me-2"></i> GRN Items</h4>
            <div>
                @if(in_array($grn->status, ['draft', 'received']))
                    <button type="button" class="btn add-item-btn" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                @endif
                <a href="{{ route('medications.stock.grn.show', $grn) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to GRN
                </a>
            </div>
        </div>
        
        <div class="items-content">
            @if($grn->items && $grn->items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered items-table mb-0">
                        <thead>
                            <tr>
                                <th width="25%">Item Details</th>
                                <th width="15%">Batch Info</th>
                                <th width="10%">Qty Received</th>
                                <th width="10%">Unit Cost</th>
                                <th width="10%">Discount</th>
                                <th width="10%">Tax</th>
                                <th width="10%">Net Amount</th>
                                @if(in_array($grn->status, ['draft', 'received']))
                                    <th width="10%">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalAmount = 0; @endphp
                            @foreach($grn->items as $item)
                                @php $totalAmount += $item->net_amount; @endphp
                                <tr>
                                    <td>
                                        <div class="item-name">
                                            @if($item->medication)
                                                {{ $item->medication->generic_name }}
                                            @else
                                                Unknown Item
                                            @endif
                                        </div>
                                        @if($item->medication)
                                            <div class="item-details">
                                                Brand: {{ $item->medication->brand_name ?? 'N/A' }}<br>
                                                Strength: {{ $item->medication->strength ?? 'N/A' }}
                                            </div>
                                        @endif
                                        @if($item->notes)
                                            <small class="text-muted"><i class="fas fa-sticky-note me-1"></i>{{ $item->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="batch-info">{{ $item->batch_number }}</div>
                                        @if($item->manufacture_date)
                                            <br><small class="text-muted">Mfg: {{ \Carbon\Carbon::parse($item->manufacture_date)->format('M Y') }}</small>
                                        @endif
                                        @if($item->expiry_date)
                                            <br><small class="{{ \Carbon\Carbon::parse($item->expiry_date)->isPast() ? 'expiry-warning' : 'expiry-normal' }}">
                                                Exp: {{ \Carbon\Carbon::parse($item->expiry_date)->format('M Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="quantity-info">
                                        {{ number_format($item->received_quantity, 2) }}
                                    </td>
                                    <td class="amount-info">
                                        ${{ number_format($item->unit_cost, 2) }}
                                    </td>
                                    <td class="amount-info">
                                        @if($item->discount_amount > 0)
                                            ${{ number_format($item->discount_amount, 2) }}
                                            @if($item->discount_percentage > 0)
                                                <br><small class="text-muted">({{ $item->discount_percentage }}%)</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="amount-info">
                                        @if($item->tax_amount > 0)
                                            ${{ number_format($item->tax_amount, 2) }}
                                            @if($item->tax_percentage > 0)
                                                <br><small class="text-muted">({{ $item->tax_percentage }}%)</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="amount-info">
                                        ${{ number_format($item->net_amount, 2) }}
                                    </td>
                                    @if(in_array($grn->status, ['draft', 'received']))
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="editItem({{ $item->id }})" title="Edit Item">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="removeItem({{ $item->id }})" title="Remove Item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="{{ in_array($grn->status, ['draft', 'received']) ? '6' : '5' }}">
                                    <strong>Total Items: {{ $grn->items->count() }}</strong>
                                </td>
                                <td class="amount-info">
                                    <strong>${{ number_format($totalAmount, 2) }}</strong>
                                </td>
                                @if(in_array($grn->status, ['draft', 'received']))
                                    <td></td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h5>No Items Added</h5>
                    <p class="text-muted">This GRN doesn't have any items yet.</p>
                    @if(in_array($grn->status, ['draft', 'received']))
                        <button type="button" class="btn add-item-btn" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="fas fa-plus me-2"></i>Add First Item
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Item Modal -->
@include('medications.stock.grn.grn_items.add-modal')

<!-- Edit Item Modal -->
@include('medications.stock.grn.grn_items.edit-modal')

@endsection

@section('scripts')
<script>
    function editItem(itemId) {
        console.log('Edit item called for ID:', itemId);
        // Load item data and show edit modal
        fetch(`/medications/goods-received-notes/{{ $grn->id }}/items/${itemId}`)
            .then(response => {
                console.log('Edit API Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Edit API Data received:', data);
                // Populate edit form with item data
                populateEditForm(data);
                
                // Use Bootstrap 5 syntax instead of jQuery
                const editModalElement = document.getElementById('editItemModal');
                if (editModalElement) {
                    const editModal = new bootstrap.Modal(editModalElement);
                    editModal.show();
                } else {
                    console.error('Edit modal element not found');
                    alert('Edit modal not found');
                }
            })
            .catch(error => {
                console.error('Error loading item data:', error);
                alert('Error loading item data: ' + error.message);
            });
    }

    function removeItem(itemId) {
        if (confirm('Are you sure you want to remove this item from the GRN?')) {
            // Create and submit form to remove item
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/medications/goods-received-notes/{{ $grn->id }}/items/${itemId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function populateEditForm(item) {
        console.log('Populating edit form with:', item);
        
        // Load units first
        loadEditUnits();
        
        // Wait a bit for units to load then populate
        setTimeout(() => {
            document.getElementById('edit_item_id').value = item.id;
            document.getElementById('edit_item_id_hidden').value = item.item_id;
            
            // Display item name (read-only)
            let itemDisplay = 'Unknown Item';
            if (item.medication) {
                itemDisplay = item.medication.generic_name;
                if (item.medication.brand_name) {
                    itemDisplay += ` (${item.medication.brand_name})`;
                }
                if (item.medication.strength) {
                    itemDisplay += ` - ${item.medication.strength}`;
                }
            }
            document.getElementById('edit_item_display').value = itemDisplay;
            
            // Populate unit fields
            if (item.store_unit_id) {
                document.getElementById('edit_store_unit_id').value = item.store_unit_id;
            }
            if (item.dispensing_unit_id) {
                document.getElementById('edit_dispensing_unit_id').value = item.dispensing_unit_id;
            }
            if (item.conversion_factor) {
                document.getElementById('edit_conversion_factor').value = item.conversion_factor;
            }
            
            // Populate form fields
            document.getElementById('edit_batch_number').value = item.batch_number || '';
            document.getElementById('edit_manufacture_date').value = item.manufacture_date || '';
            document.getElementById('edit_expiry_date').value = item.expiry_date || '';
            document.getElementById('edit_received_quantity').value = item.received_quantity || '';
            document.getElementById('edit_unit_cost').value = item.unit_cost || '';
            document.getElementById('edit_discount_percentage').value = item.discount_percentage || '';
            document.getElementById('edit_tax_percentage').value = item.tax_percentage || '';
            document.getElementById('edit_notes').value = item.notes || '';
            
            // Update form action URL to use correct API endpoint
            document.getElementById('editItemForm').action = `/medications/goods-received-notes/{{ $grn->id }}/items/${item.id}`;
            
            // Update conversion display
            updateEditConversionDisplay();
            
            // Trigger calculations
            calculateEditTotals();
        }, 500); // Wait 500ms for units to load
    }

    function calculateAddTotals() {
        const quantity = parseFloat(document.getElementById('received_quantity').value) || 0;
        const unitCost = parseFloat(document.getElementById('unit_cost').value) || 0;
        const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
        const taxPercentage = parseFloat(document.getElementById('tax_percentage').value) || 0;
        
        const totalCost = quantity * unitCost;
        const discountAmount = (totalCost * discountPercentage) / 100;
        const subtotal = totalCost - discountAmount;
        const taxAmount = (subtotal * taxPercentage) / 100;
        const netAmount = subtotal + taxAmount;
        
        document.getElementById('total_cost_display').textContent = 'Tsh' + totalCost.toFixed(2);
        document.getElementById('discount_amount_display').textContent = 'Tsh' + discountAmount.toFixed(2);
        document.getElementById('tax_amount_display').textContent = 'Tsh' + taxAmount.toFixed(2);
        document.getElementById('net_amount_display').textContent = 'Tsh' + netAmount.toFixed(2);
    }

    function calculateEditTotals() {
        const quantity = parseFloat(document.getElementById('edit_received_quantity').value) || 0;
        const unitCost = parseFloat(document.getElementById('edit_unit_cost').value) || 0;
        const discountPercentage = parseFloat(document.getElementById('edit_discount_percentage').value) || 0;
        const taxPercentage = parseFloat(document.getElementById('edit_tax_percentage').value) || 0;
        
        const totalCost = quantity * unitCost;
        const discountAmount = (totalCost * discountPercentage) / 100;
        const subtotal = totalCost - discountAmount;
        const taxAmount = (subtotal * taxPercentage) / 100;
        const netAmount = subtotal + taxAmount;
        
        document.getElementById('edit_total_cost_display').textContent = 'Tsh' + totalCost.toFixed(2);
        document.getElementById('edit_discount_amount_display').textContent = 'Tsh' + discountAmount.toFixed(2);
        document.getElementById('edit_tax_amount_display').textContent = 'Tsh' + taxAmount.toFixed(2);
        document.getElementById('edit_net_amount_display').textContent = 'Tsh' + netAmount.toFixed(2);
    }

    // Add event listeners for calculation
    document.addEventListener('DOMContentLoaded', function() {
        // Add form calculations
        ['received_quantity', 'unit_cost', 'discount_percentage', 'tax_percentage'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', calculateAddTotals);
            }
        });

        // Edit form calculations
        ['edit_received_quantity', 'edit_unit_cost', 'edit_discount_percentage', 'edit_tax_percentage'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', calculateEditTotals);
            }
        });
    });
</script>
@endsection
