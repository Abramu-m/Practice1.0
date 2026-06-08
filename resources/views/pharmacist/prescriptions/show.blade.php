@extends('layouts.app_main_layout')

@section('page_title', 'Prescription Details')

@section('main_content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Prescription Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pharmacist.dashboard') }}">Pharmacist</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pharmacist.prescriptions.index') }}">Prescriptions</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Patient Information -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-person"></i>
                                Patient Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Name:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</dd>
                                
                                <dt class="col-sm-5">MR Number:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->mr_number }}</dd>
                                
                                <dt class="col-sm-5">Age:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->age ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Gender:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->gender ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->phone_number ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Visit Information -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-calendar-check"></i>
                                Visit Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Visit Date:</dt>
                                <dd class="col-sm-7">{{ $visit->created_at->format('M d, Y h:i A') }}</dd>
                                
                                @if($visit->consultation && $visit->consultation->doctor)
                                <dt class="col-sm-5">Doctor:</dt>
                                <dd class="col-sm-7">Dr. {{ $visit->consultation->doctor->name ?? 'N/A' }}</dd>
                                @endif
                                
                                <dt class="col-sm-5">Visit Type:</dt>
                                <dd class="col-sm-7">{{ $visit->visit_type ?? 'General' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Prescriptions -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-capsule"></i>
                                Prescriptions
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($visit->consultation && $visit->consultation->prescriptions->count() > 0)
                                @foreach($visit->consultation->prescriptions as $prescription)
                                <div class="card mb-3 prescription-item" data-prescription-id="{{ $prescription->id }}">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="card-title">
                                                    {{ $prescription->medication->brand_name ?? $prescription->medication->generic_name }}
                                                    @if($prescription->medication->strength)
                                                        <small class="text-muted">({{ $prescription->medication->strength }})</small>
                                                    @endif
                                                </h5>
                                                <div class="prescription-details">
                                                    <p class="mb-1">
                                                        <strong>Quantity:</strong> {{ $prescription->quantity }}
                                                        @if($prescription->medication->unit)
                                                            {{ $prescription->medication->unit }}
                                                        @endif
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Frequency:</strong> {{ $prescription->frequency->description ?? 'As directed' }}
                                                    </p>
                                                    @if($prescription->administrationRoute)
                                                        <p class="mb-1">
                                                            <strong>Route:</strong> {{ $prescription->administrationRoute->route }}
                                                        </p>
                                                    @endif
                                                    @if($prescription->duration)
                                                        <p class="mb-1">
                                                            <strong>Duration:</strong> {{ $prescription->duration }} days
                                                        </p>
                                                    @endif
                                                    @if($prescription->instructions)
                                                        <p class="mb-1">
                                                            <strong>Instructions:</strong> {{ $prescription->instructions }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <!-- Status Badge -->
                                                <div class="mb-2">
                                                    @switch($prescription->status)
                                                        @case('prescribed')
                                                        @case('prepared')
                                                            <span class="badge bg-warning badge-lg">
                                                                <i class="bi bi-clock"></i> Pending
                                                            </span>
                                                            @break
                                                        @case('dispensed')
                                                            <span class="badge bg-success badge-lg">
                                                                <i class="bi bi-check-circle"></i> Dispensed
                                                            </span>
                                                            @break
                                                        @case('cancelled')
                                                            <span class="badge bg-danger badge-lg">
                                                                <i class="bi bi-x-circle"></i> Unavailable
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </div>

                                                <!-- Actions -->
                                                @if(in_array($prescription->status, ['prescribed', 'prepared']))
                                                    @php
                                                        $availableStock = $prescription->medication->getTotalStockAt($dispensingLocation);
                                                        $hasEnoughStock = $availableStock >= $prescription->quantity;
                                                    @endphp
                                                    <div class="mb-1">
                                                        <small class="text-muted">
                                                            Stock ({{ $dispensingLocation }}): 
                                                            <strong class="{{ $hasEnoughStock ? 'text-success' : 'text-danger' }}">
                                                                {{ $availableStock }}
                                                            </strong>
                                                        </small>
                                                        @php $reqQty = $pendingReqQtys[$prescription->medication_id] ?? 0; @endphp
                                                        @if(!$hasEnoughStock && $reqQty > 0)
                                                            <br>
                                                            <small class="text-warning">
                                                                <i class="bi bi-arrow-up-circle"></i> {{ $reqQty }} requested in open req(s)
                                                            </small>
                                                        @endif
                                                        <br>
                                                        @if($prescription->is_paid)
                                                            <small class="text-success"><i class="bi bi-check-circle-fill"></i> Paid</small>
                                                        @else
                                                            <small class="text-danger"><i class="bi bi-x-circle-fill"></i> Not paid</small>
                                                        @endif
                                                    </div>
                                                    <div class="btn-group-vertical d-block">
                                                        @if($hasEnoughStock)
                                                            @if($prescription->is_paid)
                                                                <button type="button" class="btn btn-success btn-sm mb-1" 
                                                                        onclick="dispensePrescription({{ $prescription->id }})">
                                                                    <i class="bi bi-check"></i> Dispense
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-secondary btn-sm mb-1" disabled
                                                                        title="Payment required before dispensing">
                                                                    <i class="bi bi-lock"></i> Unpaid
                                                                </button>
                                                            @endif
                                                        @else
                                                            <button type="button" class="btn btn-danger btn-sm mb-1" disabled
                                                                    title="Insufficient stock at {{ $dispensingLocation }}">
                                                                <i class="bi bi-x-circle"></i> Low Stock
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm"
                                                                    onclick="checkRequisitions({{ $prescription->medication_id }}, '{{ addslashes($prescription->medication->generic_name) }}')">
                                                                <i class="bi bi-box-arrow-in-down"></i> Check Requisitions
                                                            </button>
                                                        @endif
                                                    </div>
                                                @elseif($prescription->status === 'dispensed')
                                                    <div class="text-muted">
                                                        <small>
                                                            Dispensed: {{ $prescription->dispensed_at ? $prescription->dispensed_at->format('M d, Y h:i A') : 'N/A' }}
                                                            <br>
                                                            Quantity: {{ $prescription->quantity_dispensed ?? $prescription->quantity }}
                                                        </small>
                                                    </div>
                                                @elseif($prescription->status === 'cancelled')
                                                    <div class="text-muted">
                                                        <small>
                                                            Reason: {{ $prescription->pharmacist_notes ?? 'Not specified' }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if($prescription->pharmacist_notes)
                                            <div class="mt-2">
                                                <hr>
                                                <small class="text-muted">
                                                    <i class="bi bi-chat-text"></i>
                                                    <strong>Pharmacist Notes:</strong> {{ $prescription->pharmacist_notes }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2">No prescriptions found for this visit.</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('pharmacist.prescriptions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Prescriptions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Dispense Modal -->
<div class="modal fade" id="dispenseModal" tabindex="-1" role="dialog" aria-labelledby="dispenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="dispenseForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="dispenseModalLabel">Dispense Prescription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quantity_dispensed">Quantity to Dispense</label>
                        <input type="number" class="form-control" id="quantity_dispensed" name="quantity_dispensed" 
                               min="0" step="0.01" required>
                        <small class="text-muted">Maximum: <span id="max_quantity"></span></small>
                    </div>
                    <div class="mb-3">
                        <label for="notes">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Any additional notes about dispensing..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check"></i> Dispense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Requisitions Modal -->
<div class="modal fade" id="requisitionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-in-down"></i> Open Requisitions &mdash; Main Pharmacy
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="requisitionsLoading" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading...</p>
                </div>
                <div id="requisitionsContent" style="display:none">
                    <p id="requisitionsEmpty" class="text-center text-muted py-3" style="display:none">
                        <i class="bi bi-inbox"></i> No open requisitions from Main Pharmacy.
                    </p>
                    <table class="table table-sm table-bordered" id="requisitionsTable" style="display:none">
                        <thead class="thead-light">
                            <tr>
                                <th>Req #</th>
                                <th>From</th>
                                <th>Requested By</th>
                                <th>Date</th>
                                <th>Required By</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="requisitionsTableBody"></tbody>
                    </table>
                    <!-- Collapsible medication items panel -->
                    <div id="reqItemsPanel" class="card card-body bg-light mt-2" style="display:none">
                        <strong id="reqItemsPanelTitle" class="d-block mb-2"></strong>
                        <ul id="reqItemsList" class="mb-0 ps-3"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="openNewRequisitionModal()">
                    <i class="bi bi-plus"></i> New Requisition
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add to Requisition Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Medication: <strong id="addItemMedName"></strong></p>
                <div class="mb-3 mb-0">
                    <label for="addItemQty">Quantity <span class="text-danger">*</span></label>
                    <input type="number" id="addItemQty" class="form-control" min="1" step="1" placeholder="Enter quantity">
                    <div id="addItemError" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="addItemCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="addItemSubmitBtn" onclick="submitAddItem()">
                    <span id="addItemBtnText">Add Item</span>
                    <span id="addItemSpinner" class="spinner-border spinner-border-sm ms-1" style="display:none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Bootstrap 5 modal helpers
const bsModal = (id) => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

function dispensePrescription(prescriptionId) {
    const prescriptionCard = document.querySelector(`[data-prescription-id="${prescriptionId}"]`);
    const quantityText = prescriptionCard.querySelector('.prescription-details p').textContent;
    const quantity = quantityText.match(/\d+(\.\d+)?/)[0];
    document.getElementById('quantity_dispensed').value = quantity;
    document.getElementById('quantity_dispensed').max = quantity;
    document.getElementById('max_quantity').textContent = quantity;
    document.getElementById('dispenseForm').action = `{{ url('/pharmacist/prescriptions') }}/${prescriptionId}/dispense`;
    bsModal('dispenseModal').show();
}

const openRequisitionsUrl = '{{ route("pharmacist.requisitions.open") }}';
const addItemBaseUrl     = '{{ url("/pharmacist/requisitions") }}';
const newWithItemUrl     = '{{ route("pharmacist.requisitions.new-with-item") }}';
const csrfToken          = '{{ csrf_token() }}';

let _currentMedId   = null;
let _currentMedName = '';
let _targetReqId    = null; // null = new requisition

function checkRequisitions(medicationId, medicationName) {
    _currentMedId   = medicationId;
    _currentMedName = medicationName;
    document.getElementById('requisitionsLoading').style.display = 'block';
    document.getElementById('requisitionsContent').style.display = 'none';
    bsModal('requisitionsModal').show();

    $.get(openRequisitionsUrl)
        .done(function(data) {
            document.getElementById('requisitionsLoading').style.display = 'none';
            document.getElementById('requisitionsContent').style.display = 'block';

            const reqs = data.requisitions;
            if (!reqs.length) {
                document.getElementById('requisitionsEmpty').style.display = 'block';
                document.getElementById('requisitionsTable').style.display = 'none';
                return;
            }

            document.getElementById('requisitionsEmpty').style.display = 'none';
            document.getElementById('requisitionsTable').style.display = 'table';

            const statusBadge = (s) => ({
                draft:            '<span class="badge bg-secondary">Draft</span>',
                submitted:        '<span class="badge bg-info">Submitted</span>',
                verified:         '<span class="badge bg-primary">Verified</span>',
                approved:         '<span class="badge bg-warning">Approved</span>',
                partially_issued: '<span class="badge bg-warning">Part. Issued</span>',
            }[s] || '<span class="badge bg-light">' + s + '</span>');

            const priorityBadge = (p) => p === 'high'
                ? '<span class="badge bg-danger">High</span>'
                : (p === 'medium' ? '<span class="badge bg-warning">Med</span>' : '<span class="badge bg-secondary">Low</span>');

            const rows = reqs.map(r => {
                const existingItem = r.medication_items.find(i => i.medication_id == _currentMedId);
                const alreadyIn = !!existingItem;
                const alreadyBadge = alreadyIn
                    ? ' <span class="badge bg-warning" title="Already requested: ' + existingItem.requested_quantity + ' units">Already in req</span>'
                    : '';
                const addBtn = alreadyIn
                    ? '<button class="btn btn-xs btn-warning me-1" onclick="openAddToReq(' + r.id + ', \'' + r.requisition_number.replace(/'/g, "\\'") + '\', ' + existingItem.requested_quantity + ')">' +
                        '<i class="bi bi-plus"></i> Add more' +
                      '</button>'
                    : '<button class="btn btn-xs btn-success me-1" onclick="openAddToReq(' + r.id + ', \'' + r.requisition_number.replace(/'/g, "\\'") + '\', 0)">' +
                        '<i class="bi bi-plus"></i> Add to Req' +
                      '</button>';
                const itemsCell = r.medication_items.length
                    ? '<button class="btn btn-xs btn-link p-0" onclick="showReqItems(' + r.id + ', \'' + r.requisition_number.replace(/'/g, "\\'") + '\')">' +
                        r.items_count + ' item(s)' +
                      '</button>'
                    : '0';
                return '<tr data-req-id="' + r.id + '">' +
                    '<td>' + r.requisition_number + alreadyBadge + ' ' + priorityBadge(r.priority) + '</td>' +
                    '<td>' + r.requesting_location + '</td>' +
                    '<td>' + r.requested_by + '</td>' +
                    '<td>' + (r.requisition_date || '-') + '</td>' +
                    '<td>' + (r.required_date || '-') + '</td>' +
                    '<td>' + itemsCell + '</td>' +
                    '<td>' + statusBadge(r.status) + '</td>' +
                    '<td class="text-nowrap">' +
                        addBtn +
                        '<a href="' + r.show_url + '" class="btn btn-xs btn-outline-primary" target="_blank">View</a>' +
                    '</td>' +
                    '</tr>';
            }).join('');
            document.getElementById('requisitionsTableBody').innerHTML = rows;
            // Store full data for the items panel
            window._reqData = reqs;
        })
        .fail(function() {
            document.getElementById('requisitionsLoading').style.display = 'none';
            document.getElementById('requisitionsContent').style.display = 'block';
            document.getElementById('requisitionsEmpty').style.display = 'block';
            document.getElementById('requisitionsEmpty').textContent = 'Error loading requisitions.';
        });
}

function openAddToReq(reqId, reqNumber, existingQty) {
    _targetReqId = reqId;
    document.getElementById('addItemModalTitle').textContent = 'Add to Requisition: ' + reqNumber;
    document.getElementById('addItemMedName').textContent = _currentMedName;
    document.getElementById('addItemQty').value = '';
    document.getElementById('addItemQty').classList.remove('is-invalid');
    document.getElementById('addItemBtnText').textContent = 'Add Item';

    // Show warning if medication already present in this requisition
    let warningEl = document.getElementById('addItemWarning');
    if (!warningEl) {
        warningEl = document.createElement('div');
        warningEl.id = 'addItemWarning';
        warningEl.className = 'alert alert-warning py-1 px-2 mt-2 mb-0';
        document.getElementById('addItemQty').parentNode.insertAdjacentElement('afterend', warningEl);
    }
    if (existingQty > 0) {
        warningEl.style.display = 'block';
        warningEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>' + _currentMedName + '</strong> is already in this requisition with <strong>' + existingQty + '</strong> unit(s) requested. Submitting will add to the existing quantity.';
    } else {
        warningEl.style.display = 'none';
    }

    document.getElementById('addItemCancelBtn').onclick = function() { bsModal('addItemModal').hide(); bsModal('requisitionsModal').show(); };
    bsModal('requisitionsModal').hide();
    bsModal('addItemModal').show();
}

function openNewRequisitionModal() {
    _targetReqId = null;
    document.getElementById('addItemModalTitle').textContent = 'New Requisition';
    document.getElementById('addItemMedName').textContent = _currentMedName;
    document.getElementById('addItemQty').value = '';
    document.getElementById('addItemQty').classList.remove('is-invalid');
    document.getElementById('addItemBtnText').textContent = 'Create Requisition';
    const warningEl = document.getElementById('addItemWarning');
    if (warningEl) warningEl.style.display = 'none';
    document.getElementById('addItemCancelBtn').onclick = function() { bsModal('addItemModal').hide(); bsModal('requisitionsModal').show(); };
    bsModal('requisitionsModal').hide();
    bsModal('addItemModal').show();
}

function showReqItems(reqId, reqNumber) {
    const req = (window._reqData || []).find(r => r.id == reqId);
    const panel = document.getElementById('reqItemsPanel');
    const title = document.getElementById('reqItemsPanelTitle');
    const list  = document.getElementById('reqItemsList');
    if (!req || !req.medication_items.length) {
        panel.style.display = 'none';
        return;
    }
    title.textContent = 'Items in ' + reqNumber + ':';
    list.innerHTML = req.medication_items.map(i =>
        '<li>' + i.name +
        ' &mdash; Requested: <strong>' + i.requested_quantity + '</strong>' +
        (i.issued_quantity > 0 ? ', Issued: <strong>' + i.issued_quantity + '</strong>' : '') +
        (i.medication_id == _currentMedId ? ' <span class="badge bg-warning">Current med</span>' : '') +
        '</li>'
    ).join('');
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function submitAddItem() {
    const qty = parseInt(document.getElementById('addItemQty').value);
    const qtyInput = document.getElementById('addItemQty');
    const errDiv   = document.getElementById('addItemError');
    if (!qty || qty < 1) {
        qtyInput.classList.add('is-invalid');
        errDiv.textContent = 'Please enter a valid quantity (minimum 1).';
        return;
    }
    qtyInput.classList.remove('is-invalid');

    const url  = _targetReqId ? (addItemBaseUrl + '/' + _targetReqId + '/add-item') : newWithItemUrl;
    const btn  = document.getElementById('addItemSubmitBtn');
    const spin = document.getElementById('addItemSpinner');
    btn.disabled = true;
    spin.style.display = 'inline-block';

    $.ajax({
        url: url,
        method: 'POST',
        data: { medication_id: _currentMedId, quantity: qty, _token: csrfToken },
        success: function(res) {
            btn.disabled = false;
            spin.style.display = 'none';
            bsModal('addItemModal').hide();
            const toastEl = document.createElement('div');
            toastEl.className = 'alert alert-success alert-dismissible fade show position-fixed';
            toastEl.style.cssText = 'bottom:20px;right:20px;z-index:9999;min-width:280px';
            toastEl.innerHTML = '<i class="bi bi-check-circle"></i> ' + res.message +
                (res.show_url ? ' <a href="' + res.show_url + '" target="_blank" class="alert-link ms-1">View</a>' : '') +
                '<button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>';
            document.body.appendChild(toastEl);
            setTimeout(function() { toastEl.remove(); }, 5000);
        },
        error: function(xhr) {
            btn.disabled = false;
            spin.style.display = 'none';
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Request failed.';
            qtyInput.classList.add('is-invalid');
            errDiv.textContent = msg;
        }
    });
}

$(document).ready(function() {
    // Handle form submissions
    $('#dispenseForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
});
</script>
@endsection

@section('styles')
<style>
.prescription-item {
    border-left: 4px solid #17a2b8;
}

.prescription-item.dispensed {
    border-left-color: #28a745;
}

.prescription-item.unavailable {
    border-left-color: #dc3545;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

@media print {
    .app-header,
    .app-sidebar,
    .app-footer,
    .no-print,
    .card-tools,
    .card-footer,
    .content-header { display: none !important; }

    .app-wrapper, .app-main, .app-content, .container-fluid {
        margin: 0 !important; padding: 0 !important;
        width: 100% !important; background: #fff !important;
    }

    @page { margin: 10mm 12mm; }
}
</style>
@endsection
