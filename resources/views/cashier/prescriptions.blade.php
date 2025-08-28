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

    @if($prescriptions->count() > 0)
        <!-- Bulk Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-check2-all"></i> Bulk Actions</h6>
            </div>
            <div class="card-body">
                <form id="bulkPrescriptionsForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Action</label>
                            <select class="form-select" id="bulkRxStatus" name="status" required>
                                <option value="">Select Action</option>
                                <option value="paid">Mark as Paid</option>
                                <option value="cancelled">Cancel</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="bulkRxPaymentMethodGroup" style="display: none;">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="bulkRxPaymentMethod" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="insurance">Insurance</option>
                                <option value="nhif">NHIF</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="processBulkPrescriptions()">
                                    <i class="bi bi-check-all"></i> Apply to Selected
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-secondary" onclick="selectAllPrescriptions()">
                                    <i class="bi bi-check2-square"></i> Select All
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Prescriptions Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAllPrescriptionsCheckbox" onchange="toggleAllPrescriptions()">
                        </th>
                        <th>Medication</th>
                        <th>Dosage & Instructions</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                        <th>Payment Status</th>
                        <th>Clinical Status</th>
                        <th>Prescribed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescriptions as $prescription)
                    <tr id="prescription-row-{{ $prescription->id }}">
                        <td>
                            <input type="checkbox" class="prescription-checkbox" value="{{ $prescription->id }}">
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
                        <td>Tsh {{ number_format($prescription->unit_price, 2) }}</td>
                        <td>
                            <strong>Tsh {{ number_format($prescription->total_price, 2) }}</strong>
                        </td>
                        <td>
                            @php
                                $statusClass = match($prescription->status) {
                                    'paid' => 'success',
                                    'dispensed' => 'primary',
                                    'cancelled' => 'danger',
                                    'prescribed' => 'warning',
                                    'approved' => 'info',
                                    'prepared' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($prescription->status) }}
                            </span>
                            @if($prescription->paid_at)
                                <br><small class="text-muted">Paid: {{ $prescription->paid_at->format('M d, Y h:i A') }}</small>
                            @endif
                            @if($prescription->dispensed_at)
                                <br><small class="text-success">Dispensed: {{ $prescription->dispensed_at->format('M d, Y h:i A') }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $prescription->doctor && $prescription->doctor->user ? $prescription->doctor->user->name : 'N/A' }}
                            <br><small class="text-muted">{{ $prescription->prescribed_at ? $prescription->prescribed_at->format('M d, Y h:i A') : $prescription->created_at->format('M d, Y h:i A') }}</small>
                        </td>
                        <td>
                            @if(!$prescription->is_paid)
                                <div class="btn-group-vertical btn-group-sm">
                                    <button class="btn btn-success btn-sm" 
                                            onclick="markRxAsPaid({{ $prescription->id }}, {{ $prescription->total_price }})">
                                        <i class="bi bi-check-circle"></i> Mark Paid
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
                        <strong>Total Amount:</strong> Tsh {{ number_format($prescriptions->sum('total_price'), 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Pending Amount:</strong> 
                        Tsh {{ number_format($prescriptions->whereNotIn('status', ['paid', 'dispensed', 'cancelled'])->sum('total_price'), 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Average per Item:</strong> 
                        Tsh {{ number_format($prescriptions->avg('total_price'), 2) }}
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

function selectAllPrescriptions() {
    $('.prescription-checkbox').prop('checked', true);
}

function toggleAllPrescriptions() {
    const isChecked = $('#selectAllPrescriptionsCheckbox').is(':checked');
    $('.prescription-checkbox').prop('checked', isChecked);
}

function processBulkPrescriptions() {
    const selectedIds = $('.prescription-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one prescription.');
        return;
    }
    
    const status = $('#bulkRxStatus').val();
    const paymentMethod = $('#bulkRxPaymentMethod').val();
    
    if (!status) {
        alert('Please select an action.');
        return;
    }
    
    if (status === 'paid' && !paymentMethod) {
        alert('Please select a payment method.');
        return;
    }
    
    if (confirm(`Are you sure you want to ${status} ${selectedIds.length} prescription(s)?`)) {
        fetch('/cashier/prescriptions/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                prescription_ids: selectedIds,
                action: status,
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

function updatePrescriptionStatus(prescriptionId, status, paymentMethod, amountPaid) {
    fetch(`/cashier/prescriptions/${prescriptionId}/payment`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: status,
            payment_method: paymentMethod,
            amount_paid: amountPaid
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
