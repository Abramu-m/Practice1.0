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

    @if($prescriptions->count() > 0)
        <!-- Bulk Actions -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary" id="btnSelectAllRx" onclick="toggleSelectAllPrescriptions()">
                        <i class="bi bi-check2-square"></i> Select All
                    </button>
                    <div id="bulkRxPaymentMethodGroup" style="display:none;">
                        <select class="form-select form-select-sm" id="bulkRxPaymentMethod" style="min-width:140px;">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="insurance">Insurance</option>
                            <option value="nhif">NHIF</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" id="btnBulkRxPay" onclick="processBulkPrescriptions('paid')" disabled>
                        <i class="bi bi-cash-coin"></i> Process Payment
                    </button>
                    <button type="button" class="btn btn-danger" id="btnBulkRxCancel" onclick="processBulkPrescriptions('cancelled')" disabled>
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <small class="text-muted ms-2" id="rxSelectionCount"></small>
                </div>
            </div>
        </div>

        <!-- Prescriptions Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAllPrescriptionsCheckbox" onchange="toggleSelectAllPrescriptions()">
                        </th>
                        <th>Medication</th>
                        <th>Dosage & Instructions</th>
                        <th>Quantity</th>
                        <th>Cash</th>
                        <th>Covered</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescriptions as $prescription)
                    <tr id="prescription-row-{{ $prescription->id }}">
                        <td>
                            <input type="checkbox" class="prescription-checkbox" value="{{ $prescription->id }}" onchange="updateRxBulkButtons()"
                                @if($prescription->is_paid) disabled style="opacity:0.35;cursor:not-allowed;" @endif>
                        </td>
                        <td>
                            <strong>{{ $prescription->medication ? $prescription->medication->name : 'Medication not found' }}</strong>
                            @if($prescription->medication && $prescription->medication->generic_name)
                                <br><small class="text-muted">{{ $prescription->medication->generic_name }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $prescription->dosage }}</strong>
                            @if($prescription->frequency)
                                <br><small>Frequency: {{ $prescription->frequency->frequency_name ?? 'Not specified' }}</small>
                            @endif
                            @if($prescription->duration_days)
                                <br><small>Duration: {{ $prescription->duration_days }} days</small>
                            @endif
                            @if($prescription->instructions)
                                <br><small class="text-info">{{ $prescription->instructions }}</small>
                            @endif
                        </td>
                        <td>{{ $prescription->quantity }}</td>
                        <td>Tsh {{ number_format($prescription->cash_amount, 2) }}</td>
                        <td>
                            <strong>Tsh {{ number_format($prescription->insurance_covered_amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($prescription->is_paid)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Paid
                                </span>
                                @if($prescription->paid_at)
                                    <br><small class="text-muted">{{ $prescription->paid_at->format('M d, Y h:i A') }}</small>
                                @endif
                                @if($prescription->payment_method)
                                    <br><small class="text-muted">{{ ucfirst($prescription->payment_method) }}</small>
                                @endif
                            @else
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pending Payment
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(!$prescription->is_paid)
                                <div class="btn-group-vertical btn-group-sm">
                                    <button class="btn btn-success btn-sm" 
                                            onclick="markRxAsPaid({{ $prescription->id }}, {{ $prescription->cash_amount }})">
                                        <i class="bi bi-check-circle"></i> Process Payment
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="cancelPrescription({{ $prescription->id }})">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">
                                    <i class="bi bi-check-circle"></i> 
                                    {{ ucfirst($prescription->status) }}
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
                        <strong>Total Prescriptions:</strong> {{ $prescriptions->count() }}
                    </div>
                    <div class="col-md-3">
                        <strong>Total Amount:</strong> Tsh {{ number_format($prescriptions->sum('cash_amount') + $prescriptions->sum('insurance_covered_amount'), 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Pending Cash Amount:</strong> 
                        Tsh {{ number_format($prescriptions->where('is_paid', false)->sum('cash_amount'), 2) }}
                        <br><small class="text-success">Paid: {{ $prescriptions->where('is_paid', true)->sum('cash_amount') }}</small>
                    </div>
                    <div class="col-md-3">
                        <strong>Covered:</strong> 
                        Tsh {{ number_format($prescriptions->sum('insurance_covered_amount'), 2) }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
            <h5>No prescriptions found</h5>
            <p>This patient has no prescriptions for this visit.</p>
        </div>
    @endif
</div>

<!-- Payment Modal for Prescriptions -->
<div class="modal fade" id="rxPaymentModal" tabindex="-1" aria-labelledby="rxPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rxPaymentModalLabel">Process Prescription Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rxPaymentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="prescriptionId" name="prescription_id">
                    <div class="mb-3">
                        <label for="rxPaymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="rxPaymentMethod" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="insurance">Insurance</option>
                            <option value="nhif">NHIF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rxAmountPaid" class="form-label">Amount Paid</label>
                        <input type="number" class="form-control" id="rxAmountPaid" name="amount_paid" step="0.01" min="0" required>
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
function markRxAsPaid(prescriptionId, amount) {
    $('#prescriptionId').val(prescriptionId);
    $('#rxAmountPaid').val(amount);
    $('#rxPaymentModal').modal('show');
}

function cancelPrescription(prescriptionId) {
    if (confirm('Are you sure you want to cancel this prescription?')) {
        updatePrescriptionStatus(prescriptionId, 'cancelled', null, 0);
    }
}

function toggleSelectAllPrescriptions() {
    const allChecked = $('.prescription-checkbox:not(:disabled)').length === $('.prescription-checkbox:not(:disabled):checked').length;
    $('.prescription-checkbox:not(:disabled)').prop('checked', !allChecked);
    updateRxBulkButtons();
}

function updateRxBulkButtons() {
    const count = $('.prescription-checkbox:not(:disabled):checked').length;
    const total = $('.prescription-checkbox:not(:disabled)').length;
    const allSelected = count === total && total > 0;
    $('#btnSelectAllRx').toggleClass('btn-outline-secondary', !allSelected).toggleClass('btn-secondary', allSelected);
    $('#btnSelectAllRx').html(allSelected
        ? '<i class="bi bi-x-square"></i> Deselect All'
        : '<i class="bi bi-check2-square"></i> Select All');
    $('#btnBulkRxPay, #btnBulkRxCancel').prop('disabled', count === 0);
    $('#bulkRxPaymentMethodGroup').toggle(count > 0);
    $('#rxSelectionCount').text(count > 0 ? count + ' selected' : '');
}

function processBulkPrescriptions(action) {
    const selectedIds = $('.prescription-checkbox:not(:disabled):checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one prescription.');
        return;
    }
    
    const paymentMethod = $('#bulkRxPaymentMethod').val();
    
    if (action === 'paid' && !paymentMethod) {
        alert('Please select a payment method.');
        return;
    }
    
    const label = action === 'paid' ? 'mark as paid' : 'cancel';
    if (confirm(`Are you sure you want to ${label} ${selectedIds.length} prescription(s)?`)) {
        fetch('/cashier/prescriptions/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                prescription_ids: selectedIds,
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

function updatePrescriptionStatus(prescriptionId, status, paymentMethod, amountPaid) {
    fetch(`/cashier/prescriptions/${prescriptionId}/payment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: status,
            payment_method: paymentMethod,
            amount_paid: amountPaid
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
        alert('An error occurred while updating the prescription.');
    });
}

// Prescription payment form submission
$('#rxPaymentForm').on('submit', function(e) {
    e.preventDefault();
    
    const prescriptionId = $('#prescriptionId').val();
    const paymentMethod = $('#rxPaymentMethod').val();
    const amountPaid = $('#rxAmountPaid').val();
    
    updatePrescriptionStatus(prescriptionId, 'paid', paymentMethod, amountPaid);
    $('#rxPaymentModal').modal('hide');
});

// Show/hide payment method for bulk actions
$('#bulkRxStatus').on('change', function() {
    if ($(this).val() === 'paid') {
        $('#bulkRxPaymentMethodGroup').show();
    } else {
        $('#bulkRxPaymentMethodGroup').hide();
    }
});
</script>
