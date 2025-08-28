<!-- GRN Items Section (to be included in GRN show page) -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list-ul mr-2"></i>GRN Items
            <span class="badge badge-light text-dark ml-2">{{ $grn->items->count() }} items</span>
        </h5>
        <div>
            @if(in_array($grn->status, ['draft', 'received']))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal" onclick="console.log('Add Item button clicked');">
                    <i class="fas fa-plus mr-1"></i>Add Item
                </button>
            @endif
            <a href="{{ route('medications.stock.grn.items.index', $grn) }}" class="btn btn-light btn-sm">
                <i class="fas fa-external-link-alt mr-1"></i>Manage Items
            </a>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($grn->items && $grn->items->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Item</th>
                            <th>Batch</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Net Amount</th>
                            <th>Expiry</th>
                            @if(in_array($grn->status, ['draft', 'received']))
                                <th width="100">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @foreach($grn->items as $item)
                            @php $totalAmount += $item->net_amount; @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">
                                        @if($item->medication)
                                            {{ $item->medication->generic_name }}
                                        @else
                                            Unknown Item
                                        @endif
                                    </div>
                                    @if($item->medication)
                                        <small class="text-muted">
                                            {{ $item->medication->brand_name ?? 'N/A' }} - {{ $item->medication->strength ?? 'N/A' }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $item->batch_number }}</span>
                                </td>
                                <td class="text-right">
                                    {{ number_format($item->received_quantity, 2) }}
                                </td>
                                <td class="text-right">
                                    ${{ number_format($item->unit_cost, 2) }}
                                </td>
                                <td class="text-right font-weight-bold">
                                    ${{ number_format($item->net_amount, 2) }}
                                </td>
                                <td>
                                    @if($item->expiry_date)
                                        <span class="text-{{ \Carbon\Carbon::parse($item->expiry_date)->isPast() ? 'danger' : (\Carbon\Carbon::parse($item->expiry_date)->diffInMonths(now()) < 6 ? 'warning' : 'success') }}">
                                            {{ \Carbon\Carbon::parse($item->expiry_date)->format('M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                @if(in_array($grn->status, ['draft', 'received']))
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm" 
                                                onclick="editItemInline({{ $item->id }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="removeItemInline({{ $item->id }})" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <th colspan="{{ in_array($grn->status, ['draft', 'received']) ? '4' : '3' }}">
                                Total ({{ $grn->items->count() }} items)
                            </th>
                            <th class="text-right">
                                ${{ number_format($totalAmount, 2) }}
                            </th>
                            <th colspan="{{ in_array($grn->status, ['draft', 'received']) ? '2' : '1' }}"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No Items Added</h5>
                <p class="text-muted">This GRN doesn't have any items yet.</p>
                @if(in_array($grn->status, ['draft', 'received']))
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="fas fa-plus mr-2"></i>Add First Item
                    </button>
                    <a href="{{ route('medications.stock.grn.items.create', $grn) }}" class="btn btn-outline-primary">
                        <i class="fas fa-magic mr-2"></i>Add Items Wizard
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Add Item Modal (Compact Version) -->
@include('medications.stock.grn.grn_items.add-modal')

<!-- Edit Item Modal (Compact Version) -->
@include('medications.stock.grn.grn_items.edit-modal')

<script>
function testModal() {
    console.log('Testing modal functionality...');
    
    // Check if Bootstrap 5 is loaded
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap 5 is loaded');
        
        // Check if the modal element exists
        const modalElement = document.getElementById('addItemModal');
        if (modalElement) {
            console.log('✅ Modal element found:', modalElement);
            
            // Try to show the modal with Bootstrap 5
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('✅ Modal show command executed (Bootstrap 5)');
        } else {
            console.error('❌ Modal element #addItemModal not found in DOM');
            alert('Modal element not found! Check if add-modal.blade.php is included.');
        }
    } else {
        console.error('❌ Bootstrap 5 not loaded');
        alert('Bootstrap 5 not loaded! Check if Bootstrap JS is included.');
    }
}

function editItemInline(itemId) {
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
            populateEditForm(data);
            
            // Use Bootstrap 5 syntax
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

function populateEditForm(item) {
    console.log('Populating edit form with:', item);
    
    // Set hidden item ID
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
    
    // Populate form fields
    document.getElementById('edit_batch_number').value = item.batch_number || '';
    document.getElementById('edit_manufacture_date').value = item.manufacture_date || '';
    document.getElementById('edit_expiry_date').value = item.expiry_date || '';
    document.getElementById('edit_received_quantity').value = item.received_quantity || '';
    document.getElementById('edit_unit_cost').value = item.unit_cost || '';
    document.getElementById('edit_discount_percentage').value = item.discount_percentage || '';
    document.getElementById('edit_tax_percentage').value = item.tax_percentage || '';
    document.getElementById('edit_notes').value = item.notes || '';
    
    // Update form action URL
    document.getElementById('editItemForm').action = `/medications/goods-received-notes/{{ $grn->id }}/items/${item.id}`;
}

function removeItemInline(itemId) {
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

// Add debug logging to the Add Item button click
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler to debug the Add Item button
    const addItemButtons = document.querySelectorAll('[data-bs-target="#addItemModal"]');
    
    addItemButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('🔍 Add Item button clicked via event listener');
            console.log('🔍 Button element:', this);
            console.log('🔍 Button data-bs-target:', this.getAttribute('data-bs-target'));
            
            // Check if modal exists
            const modal = document.getElementById('addItemModal');
            console.log('🔍 Modal element found:', modal !== null);
            
            if (!modal) {
                console.error('❌ Modal element not found!');
                e.preventDefault();
                alert('Modal not found! Please check if the modal is properly included.');
                return false;
            }
            
            // Let Bootstrap 5 handle this automatically - don't prevent default
            console.log('✅ Letting Bootstrap 5 handle modal automatically');
        });
    });
    
    // Add event listeners for proper modal cleanup
    const addModal = document.getElementById('addItemModal');
    const editModal = document.getElementById('editItemModal');
    
    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function () {
            console.log('Add modal hidden - cleaning up');
            // Clear form if needed
            const form = addModal.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    }
    
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function () {
            console.log('Edit modal hidden - cleaning up');
            // Clear form if needed
            const form = editModal.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    }
});
</script>
