@extends('layouts.app_main_layout')

@section('page_title', 'Investigation Management')

@section('main_content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search Patient</label>
                    <input type="text" class="form-control form-control-sm" id="inv_patient_search"
                           placeholder="Name or ID" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select class="form-select form-select-sm" id="inv_status"
                            onchange="invTable.draw()">
                        <option value="">All Statuses</option>
                        @foreach(App\Models\Investigation::getStatusOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">Priority</label>
                    <select class="form-select form-select-sm" id="inv_priority"
                            onchange="invTable.draw()">
                        <option value="">All</option>
                        @foreach(App\Models\Investigation::getPriorityOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Doctor</label>
                    <select class="form-select form-select-sm" id="inv_doctor_id"
                            onchange="invTable.draw()">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">
                                Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">Category</label>
                    <select class="form-select form-select-sm" id="inv_service_category"
                            onchange="invTable.draw()">
                        <option value="">All</option>
                        @foreach($serviceCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="inv_date_from"
                           value="{{ $dateFrom }}" onchange="invTable.draw()">
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="inv_date_to"
                           value="{{ $dateTo }}" onchange="invTable.draw()">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('investigations.create') }}" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-plus"></i> Order
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Investigations Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-flask text-primary"></i> Investigation Management</h4>
        </div>
        <div class="card-body p-0">
            <table id="investigationsTable" class="table table-striped table-hover w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Investigation</th>
                        <th>Doctor</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Ordered Date</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Investigation Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <input type="hidden" name="status" id="newStatus" value="">
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span id="statusMessage"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var invTable;

$(document).ready(function () {
    invTable = $('#investigationsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: '{{ route("investigations.index") }}',
            data: function (d) {
                d.status           = $('#inv_status').val();
                d.priority         = $('#inv_priority').val();
                d.doctor_id        = $('#inv_doctor_id').val();
                d.service_category = $('#inv_service_category').val();
                d.patient_search   = $('#inv_patient_search').val();
                d.date_from        = $('#inv_date_from').val();
                d.date_to          = $('#inv_date_to').val();
            },
            error: function (xhr) {
                console.error('DataTables AJAX error:', xhr.responseText);
            }
        },
        columns: [
            { data: 'id_display',            name: 'id',                    orderable: true  },
            { data: 'patient_display',        name: 'patient.first_name',    orderable: true  },
            { data: 'investigation_display',  name: 'medicalService.name',   orderable: true  },
            { data: 'doctor_display',         name: 'doctor.user.first_name', orderable: true  },
            { data: 'priority',              name: 'priority',              orderable: true  },
            { data: 'status',                name: 'status',                orderable: true  },
            { data: 'ordered_date',          name: 'ordered_at',            orderable: true  },
            { data: 'price_display',         name: 'price_display',         orderable: false, searchable: false },
            { data: 'actions',               name: 'actions',               orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [-1] }]
    });

    let _t;
    $('#inv_patient_search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { invTable.draw(); }, 500);
    });
});

function updateStatus(investigationId, newStatus, updateType) {
    updateType = updateType || 'simple';
    var modal  = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    var form   = document.getElementById('statusUpdateForm');

    form.action = (updateType === 'stock' && newStatus === 'collected')
        ? '/lab/investigations/' + investigationId + '/status'
        : '/investigations/' + investigationId + '/status';

    document.getElementById('newStatus').value = newStatus;

    var messages = {
        paid:       'Mark this investigation as paid and ready for sample collection.',
        collected:  updateType === 'stock'
            ? 'Mark the sample as collected. Stock will be checked and consumables deducted from laboratory inventory.'
            : 'Mark the sample as collected and ready for processing.',
        processing: 'Mark this investigation as currently being processed in the lab.',
        resulted:   'Mark this investigation as completed with results available.',
        cancelled:  'Cancel this investigation. This action cannot be undone.'
    };

    var msg = document.getElementById('statusMessage');
    msg.textContent = messages[newStatus] || 'Update the investigation status.';
    if (updateType === 'stock' && newStatus === 'collected') {
        msg.innerHTML += '<br><small class="text-info"><i class="fas fa-flask"></i> Stock availability will be validated.</small>';
    }

    modal.show();
}

document.getElementById('statusUpdateForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = this.querySelector('button[type="submit"]');
    var orig = btn.innerHTML;
    var formData = {
        status: document.getElementById('newStatus').value,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };
    var notes = this.querySelector('textarea[name="notes"]');
    if (notes && notes.value) formData.notes = notes.value;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    btn.disabled  = true;

    fetch(this.action, {
        method: 'PATCH',
        body: JSON.stringify(formData),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': formData._token
        }
    })
    .then(function (response) {
        if (!response.ok) {
            return response.json().then(function (e) { throw { status: response.status, data: e }; });
        }
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            invTable.ajax.reload();
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(function (error) {
        if (error.status === 422 && error.data && error.data.stock_details) {
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            showStockErrorModal(error.data.stock_details, error.data.message);
        } else {
            alert(error.data ? error.data.message : (error.message || 'Failed to update status'));
        }
    })
    .finally(function () {
        btn.innerHTML = orig;
        btn.disabled  = false;
    });
});

function showStockErrorModal(stockDetails, message) {
    var existing = document.getElementById('stockErrorModal');
    if (existing) existing.remove();

    var rows = stockDetails.map(function (item) {
        var cls   = !item.is_available && !item.is_optional ? 'table-danger'
                  : item.is_available ? 'table-success' : 'table-warning';
        var badge = item.is_available  ? '<span class="badge bg-success">Sufficient</span>'
                  : item.is_optional   ? '<span class="badge bg-warning">Low Stock (Optional)</span>'
                  : '<span class="badge bg-danger">Insufficient</span>';
        return '<tr class="' + cls + '"><td>' + (item.medication_name || 'Unknown Item') +
               (item.is_optional ? ' <small class="text-muted">(Optional)</small>' : '') +
               '</td><td>' + item.required_quantity +
               '</td><td>' + item.available_quantity +
               '</td><td>' + badge + '</td></tr>';
    }).join('');

    document.body.insertAdjacentHTML('beforeend',
        '<div class="modal fade" id="stockErrorModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg"><div class="modal-content">' +
        '<div class="modal-header bg-danger text-white">' +
        '<h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Insufficient Stock</h5>' +
        '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>' +
        '<div class="modal-body"><div class="alert alert-danger"><strong>' + message + '</strong></div>' +
        '<h6>Stock Requirements (Laboratory):</h6>' +
        '<table class="table table-sm"><thead><tr><th>Item</th><th>Required</th><th>Available</th><th>Status</th></tr></thead>' +
        '<tbody>' + rows + '</tbody></table></div>' +
        '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>' +
        '</div></div></div>'
    );
    var modal = new bootstrap.Modal(document.getElementById('stockErrorModal'));
    modal.show();
    document.getElementById('stockErrorModal').addEventListener('hidden.bs.modal', function () { this.remove(); });
}

function showStockDetailsForInvestigation(investigationId) {
    if (!investigationId) { alert('Please specify an investigation ID'); return; }

    fetch('/lab/investigations/' + investigationId + '/check-stock', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success && data.details) {
            showStockErrorModal(data.details, data.message || 'Stock information for this investigation:');
        } else {
            alert('No stock details available for this investigation.');
        }
    })
    .catch(function () { alert('Failed to fetch stock details. Please try again.'); });
}
</script>
@endsection
