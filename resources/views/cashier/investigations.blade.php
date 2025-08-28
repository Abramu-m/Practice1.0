<div class="container-fluid">
    <!-- Patient Information Header -->
    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="bi bi-person"></i> Patient Information</h6>
                <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong>
                @if($visit->patientInfo->middle_name)
                    {{ $visit->patientInfo->middle_name }}
                @endif
                <br>
                <small>MR: {{ $visit->patientInfo->mr_number }} | Visit ID: {{ $visit->id }}</small>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-calendar"></i> Visit Details</h6>
                <strong>Date:</strong> {{ $visit->visit_date ? $visit->visit_date->format('M d, Y h:i A') : 'N/A' }}<br>
                <strong>Doctor:</strong> {{ optional(optional($visit->doctorInfo)->user)->name ?? 'Not assigned' }}
            </div>
        </div>
    </div>

    @if($investigations->count() > 0)
        <!-- Bulk Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-check2-all"></i> Bulk Actions</h6>
            </div>
            <div class="card-body">
                <form id="bulkInvestigationsForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Action</label>
                            <select class="form-select" id="bulkStatus" name="action" required>
                                <option value="">Select Action</option>
                                <option value="paid">Mark as Paid</option>
                                <option value="cancelled">Cancel</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="bulkPaymentMethodGroup" style="display: none;">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="bulkPaymentMethod" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="insurance">Insurance</option>
                                <option value="nhif">NHIF</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="processBulkInvestigations()">
                                    <i class="bi bi-check-all"></i> Apply to Selected
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-secondary" onclick="selectAllInvestigations()">
                                    <i class="bi bi-check2-square"></i> Select All
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Investigations Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAllInvestigationsCheckbox" onchange="toggleAllInvestigations()">
                        </th>
                        <th>Investigation</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                        <th>Payment Status</th>
                        <th>Clinical Status</th>
                        <th>Ordered By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($investigations as $investigation)
                    <tr id="investigation-row-{{ $investigation->id }}">
                        <td>
                            <input type="checkbox" class="investigation-checkbox" value="{{ $investigation->id }}">
                        </td>
                        <td>
                            <strong>{{ $investigation->medicalService ? $investigation->medicalService->name : 'Service not found' }}</strong>
                            @if($investigation->notes)
                                <br><small class="text-muted">{{ $investigation->notes }}</small>
                            @endif
                        </td>
                        <td>{{ $investigation->quantity }}</td>
                        <td>Tsh {{ number_format($investigation->unit_price, 2) }}</td>
                        <td>
                            <strong>Tsh {{ number_format($investigation->total_price, 2) }}</strong>
                            @if($investigation->insurance_covered_amount > 0)
                                <br><small class="text-success">Insurance: Tsh {{ number_format($investigation->insurance_covered_amount, 2) }}</small>
                            @endif
                            @if($investigation->is_discount && $investigation->discount_percent > 0)
                                <br><small class="text-warning">Discount: {{ $investigation->discount_percent }}%</small>
                                @php
                                    $discountAmount = ($investigation->total_price * $investigation->discount_percent) / 100;
                                    $finalAmount = $investigation->total_price - $discountAmount;
                                @endphp
                                <br><small class="text-info">Final: Tsh {{ number_format($finalAmount, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($investigation->is_paid)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Paid
                                </span>
                                @if($investigation->paid_at)
                                    <br><small class="text-muted">{{ $investigation->paid_at->format('M d, Y h:i A') }}</small>
                                @endif
                                @if($investigation->payment_method)
                                    <br><small class="text-muted">{{ ucfirst($investigation->payment_method) }}</small>
                                @endif
                            @else
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pending Payment
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = match($investigation->status) {
                                    'cancelled' => 'danger',
                                    'draft' => 'secondary',
                                    'ordered' => 'warning',
                                    'collected' => 'info',
                                    'processing' => 'primary',
                                    'resulted' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($investigation->status) }}
                            </span>
                        </td>
                        <td>
                            {{ $investigation->doctor && $investigation->doctor->user ? $investigation->doctor->user->name : 'N/A' }}
                            <br><small class="text-muted">{{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y h:i A') : 'N/A' }}</small>
                        </td>
                        <td>
                            @if(!$investigation->is_paid && $investigation->status !== 'cancelled')
                                <div class="btn-group-vertical btn-group-sm">
                                    <button class="btn btn-success btn-sm" 
                                            onclick="markAsPaid({{ $investigation->id }}, {{ $investigation->total_price }})">
                                        <i class="bi bi-check-circle"></i> Mark Paid
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="cancelInvestigation({{ $investigation->id }})">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">
                                    @if($investigation->is_paid)
                                        <i class="bi bi-check-circle"></i> Paid
                                    @else
                                        <i class="bi bi-x-circle"></i> Cancelled
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Total Investigations:</strong> {{ $investigations->count() }}
                    </div>
                    <div class="col-md-3">
                        <strong>Total Amount:</strong> Tsh {{ number_format($investigations->sum('total_price'), 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Pending Amount:</strong> 
                        Tsh {{ number_format($investigations->where('is_paid', false)->where('status', '!=', 'cancelled')->sum('total_price'), 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Paid Amount:</strong> 
                        Tsh {{ number_format($investigations->where('is_paid', true)->sum('amount_paid'), 2) }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
            <h5>No investigations found</h5>
            <p>This patient has no investigations for this visit.</p>
        </div>
    @endif
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="investigationId" name="investigation_id">
                    <input type="hidden" id="originalAmount" name="original_amount">
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="insurance">Insurance</option>
                            <option value="nhif">NHIF</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discountPercent" class="form-label">Discount %</label>
                                <input type="number" class="form-control" id="discountPercent" name="discount_percent" 
                                       step="0.01" min="0" max="100" value="0" onchange="calculateFinalAmount()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amountPaid" class="form-label">Amount to Pay</label>
                                <input type="number" class="form-control" id="amountPaid" name="amount_paid" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <strong>Original Amount:</strong> <span id="displayOriginalAmount">Tsh 0.00</span><br>
                            <strong>Discount:</strong> <span id="displayDiscountAmount">Tsh 0.00</span><br>
                            <strong>Final Amount:</strong> <span id="displayFinalAmount">Tsh 0.00</span>
                        </small>
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

<script>
function markAsPaid(investigationId, amount) {
    $('#investigationId').val(investigationId);
    $('#originalAmount').val(amount);
    $('#amountPaid').val(amount);
    $('#discountPercent').val(0);
    $('#displayOriginalAmount').text('Tsh ' + parseFloat(amount).toFixed(2));
    $('#displayDiscountAmount').text('Tsh 0.00');
    $('#displayFinalAmount').text('Tsh ' + parseFloat(amount).toFixed(2));
    $('#paymentModal').modal('show');
}

function calculateFinalAmount() {
    const originalAmount = parseFloat($('#originalAmount').val()) || 0;
    const discountPercent = parseFloat($('#discountPercent').val()) || 0;
    const discountAmount = (originalAmount * discountPercent) / 100;
    const finalAmount = originalAmount - discountAmount;
    
    $('#displayOriginalAmount').text('Tsh ' + originalAmount.toFixed(2));
    $('#displayDiscountAmount').text('Tsh ' + discountAmount.toFixed(2));
    $('#displayFinalAmount').text('Tsh ' + finalAmount.toFixed(2));
    $('#amountPaid').val(finalAmount.toFixed(2));
}

function cancelInvestigation(investigationId) {
    if (confirm('Are you sure you want to cancel this investigation?')) {
        updateInvestigationStatus(investigationId, 'cancelled', null, 0, 0);
    }
}

function selectAllInvestigations() {
    $('.investigation-checkbox').prop('checked', true);
}

function toggleAllInvestigations() {
    const isChecked = $('#selectAllInvestigationsCheckbox').is(':checked');
    $('.investigation-checkbox').prop('checked', isChecked);
}

function processBulkInvestigations() {
    const selectedIds = $('.investigation-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one investigation.');
        return;
    }
    
    const action = $('#bulkStatus').val();
    const paymentMethod = $('#bulkPaymentMethod').val();
    
    if (!action) {
        alert('Please select an action.');
        return;
    }
    
    if (action === 'paid' && !paymentMethod) {
        alert('Please select a payment method.');
        return;
    }
    
    if (confirm(`Are you sure you want to ${action} ${selectedIds.length} investigation(s)?`)) {
        fetch('/cashier/investigations/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                investigation_ids: selectedIds,
                action: action,
                payment_method: paymentMethod
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload the main page
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
        });
    }
}
function updateInvestigationStatus(investigationId, action, paymentMethod, amountPaid, discountPercent) {
    fetch(`/cashier/investigations/${investigationId}/payment`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            payment_method: paymentMethod,
            amount_paid: amountPaid,
            discount_percent: discountPercent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Reload the main page
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the investigation.');
    });
}
// Payment form submission
$('#paymentForm').on('submit', function(e) {
    e.preventDefault();
    
    const investigationId = $('#investigationId').val();
    const paymentMethod = $('#paymentMethod').val();
    const amountPaid = $('#amountPaid').val();
    const discountPercent = $('#discountPercent').val();
    
    updateInvestigationStatus(investigationId, 'paid', paymentMethod, amountPaid, discountPercent);
    $('#paymentModal').modal('hide');
});

// Show/hide payment method for bulk actions
$('#bulkStatus').on('change', function() {
    if ($(this).val() === 'paid') {
        $('#bulkPaymentMethodGroup').show();
    } else {
        $('#bulkPaymentMethodGroup').hide();
    }
});
</script>
