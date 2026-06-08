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
                <small>{{ $visit->patientInfo->mr_number }} | Visit ID: {{ $visit->id }}</small>
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
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary" id="btnSelectAllInv" onclick="toggleSelectAllInvestigations()">
                        <i class="bi bi-check2-square"></i> Select All
                    </button>
                    <div id="bulkInvPaymentMethodGroup" style="display:none;">
                        <select class="form-select form-select-sm" id="bulkPaymentMethod" style="min-width:140px;">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="insurance">Insurance</option>
                            <option value="nhif">NHIF</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" id="btnBulkInvPay" onclick="processBulkInvestigations('paid')" disabled>
                        <i class="bi bi-cash-coin"></i> Process Payment
                    </button>
                    <button type="button" class="btn btn-danger" id="btnBulkInvCancel" onclick="processBulkInvestigations('cancelled')" disabled>
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <small class="text-muted ms-2" id="invSelectionCount"></small>
                </div>
            </div>
        </div>

        <!-- Investigations Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAllInvestigationsCheckbox" onchange="toggleSelectAllInvestigations()">
                        </th>
                        <th>Investigation</th>
                        <th>Quantity</th>
                        <th>Cash Amount</th>
                        <th>Insurance Amount</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($investigations as $investigation)
                    <tr id="investigation-row-{{ $investigation->id }}">
                        <td>
                            <input type="checkbox" class="investigation-checkbox" value="{{ $investigation->id }}" onchange="updateInvBulkButtons()"
                                @if($investigation->is_paid) disabled style="opacity:0.35;cursor:not-allowed;" @endif>
                        </td>
                        <td>
                            <strong>{{ $investigation->medicalService ? $investigation->medicalService->name : 'Service not found' }}</strong>
                            @if($investigation->notes)
                                <br><small class="text-muted">{{ $investigation->notes }}</small>
                            @endif
                        </td>
                        <td>{{ $investigation->quantity }}</td>
                        <td>Tsh {{ number_format($investigation->cash_amount, 2) }}</td>
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
                            @if(!$investigation->is_paid && $investigation->status !== 'cancelled')
                                <div class="btn-group-vertical btn-group-sm">
                                    <button class="btn btn-success btn-sm" 
                                            onclick="markAsPaid({{ $investigation->id }}, {{ $investigation->total_price }})">
                                        <i class="bi bi-check-circle"></i> Process Payment
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

function toggleSelectAllInvestigations() {
    const allChecked = $('.investigation-checkbox:not(:disabled)').length === $('.investigation-checkbox:not(:disabled):checked').length;
    $('.investigation-checkbox:not(:disabled)').prop('checked', !allChecked);
    updateInvBulkButtons();
}

function toggleAllInvestigations() {
    updateInvBulkButtons();
}

function updateInvBulkButtons() {
    const count = $('.investigation-checkbox:not(:disabled):checked').length;
    const total = $('.investigation-checkbox:not(:disabled)').length;
    const allSelected = count === total && total > 0;
    $('#btnSelectAllInv').toggleClass('btn-outline-secondary', !allSelected).toggleClass('btn-secondary', allSelected);
    $('#btnSelectAllInv').html(allSelected
        ? '<i class="bi bi-x-square"></i> Deselect All'
        : '<i class="bi bi-check2-square"></i> Select All');
    $('#btnBulkInvPay, #btnBulkInvCancel').prop('disabled', count === 0);
    $('#bulkInvPaymentMethodGroup').toggle(count > 0);
    $('#invSelectionCount').text(count > 0 ? count + ' selected' : '');
}

function processBulkInvestigations(action) {
    const selectedIds = $('.investigation-checkbox:not(:disabled):checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one investigation.');
        return;
    }
    
    const paymentMethod = $('#bulkPaymentMethod').val();
    
    if (action === 'paid' && !paymentMethod) {
        alert('Please select a payment method.');
        return;
    }
    
    const label = action === 'paid' ? 'mark as paid' : 'cancel';
    if (confirm(`Are you sure you want to ${label} ${selectedIds.length} investigation(s)?`)) {
        fetch('/cashier/investigations/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                investigation_ids: selectedIds,
                action: action,
                payment_method: paymentMethod
            })
        })
        .then(async response => {
            const contentType = response.headers.get('content-type') || '';
            const text = await response.text();
            const data = contentType.includes('application/json') ? JSON.parse(text) : null;

            if (!response.ok) {
                const message = data?.message || `Request failed (${response.status})`;
                throw new Error(message);
            }

            if (!data) {
                throw new Error(`Expected JSON response, received ${contentType || 'unknown content type'}`);
            }

            return data;
        })
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
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            payment_method: paymentMethod,
            amount_paid: amountPaid,
            discount_percent: discountPercent
        })
    })
    .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        const text = await response.text();
        const data = contentType.includes('application/json') ? JSON.parse(text) : null;

        if (!response.ok) {
            const message = data?.message || `Request failed (${response.status})`;
            throw new Error(message);
        }

        if (!data) {
            throw new Error(`Expected JSON response, received ${contentType || 'unknown content type'}`);
        }

        return data;
    })
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
