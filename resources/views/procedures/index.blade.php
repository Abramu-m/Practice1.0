@extends('layouts.app_main_layout')

@section('page_title',
    $user->role === 'nurse' ? 'Nursing Procedures' :
    ($user->role === 'doctor' ? 'Doctor Procedures' :
    ($user->role === 'radiologist' ? 'Radiology Procedures' :
    'Procedure Results'))
)

@section('main_content')
<div class="container-fluid {{ $user->role }}-theme">

    <!-- Filters -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search Patient</label>
                    <input type="text" class="form-control form-control-sm" id="proc_patient_search"
                           placeholder="Name or MR number" autocomplete="off">
                </div>

                @if($user->role === 'doctor')
                <div class="col-md-2">
                    <label class="form-label mb-1">Filter Type</label>
                    <select class="form-select form-select-sm" id="proc_filter_type"
                            onchange="proceduresTable.draw()">
                        <option value="">All</option>
                        <option value="procedures">Procedures</option>
                        <option value="radiology">Radiology</option>
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label mb-1">Category</label>
                    <select class="form-select form-select-sm" id="proc_service_category"
                            onchange="proceduresTable.draw()">
                        <option value="">All Categories</option>
                        @foreach($serviceCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($user->role !== 'doctor')
                <div class="col-md-2">
                    <label class="form-label mb-1">Doctor</label>
                    <select class="form-select form-select-sm" id="proc_doctor_id"
                            onchange="proceduresTable.draw()">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}">
                                Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label mb-1">Priority</label>
                    <select class="form-select form-select-sm" id="proc_priority"
                            onchange="proceduresTable.draw()">
                        <option value="">All Priorities</option>
                        <option value="stat">STAT</option>
                        <option value="urgent">Urgent</option>
                        <option value="routine">Routine</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="proc_date_from"
                           value="{{ $dateFrom }}" onchange="proceduresTable.draw()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="proc_date_to"
                           value="{{ $dateTo }}" onchange="proceduresTable.draw()">
                </div>
            </div>
        </div>
    </div>

    <!-- Procedures Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                @if($user->role === 'nurse')
                    <i class="fas fa-user-nurse text-primary"></i> Nursing Procedures
                @elseif($user->role === 'doctor')
                    <i class="fas fa-user-md text-success"></i> Doctor Procedures
                @elseif($user->role === 'radiologist')
                    <i class="fas fa-x-ray text-info"></i> Radiology Procedures
                @else
                    <i class="fas fa-clipboard-list text-secondary"></i> Procedure Results
                @endif
                <small class="text-muted ms-2">({{ ucfirst($user->role) }} View)</small>
            </h4>
        </div>
        <div class="card-body p-0">
            <table id="proceduresTable" class="table table-striped table-hover w-100">
                <thead class="table-dark">
                    <tr>
                        <th>SN</th>
                        <th>MR Number</th>
                        <th>Patient Name</th>
                        <th>Age</th>
                        @if($user->role !== 'doctor')
                            <th>Ordered By</th>
                        @endif
                        <th>
                            @if($user->role === 'nurse') Nursing Procedure
                            @elseif($user->role === 'radiologist') Study Name
                            @else Procedure Name
                            @endif
                        </th>
                        <th>Priority</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Result Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@section('styles')
<style>
.nurse-theme .table-dark       { background-color: #0d6efd !important; }
.doctor-theme .table-dark      { background-color: #198754 !important; }
.radiologist-theme .table-dark { background-color: #0dcaf0 !important; color: #000 !important; }
.radiologist-theme .table-dark th { color: #000 !important; }

.table-row-urgent { border-left: 4px solid #dc3545; }
.table-row-stat   { border-left: 4px solid #fd7e14; animation: pulse 2s infinite; }

@keyframes pulse {
    0%   { border-left-color: #fd7e14; }
    50%  { border-left-color: #dc3545; }
    100% { border-left-color: #fd7e14; }
}
</style>
@endsection

@section('scripts')
<script>
var proceduresTable;

$(document).ready(function () {
    proceduresTable = $('#proceduresTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: '{{ route("procedures.index") }}',
            data: function (d) {
                d.filter_type      = $('#proc_filter_type').val();
                d.service_category = $('#proc_service_category').val();
                d.doctor_id        = $('#proc_doctor_id').val();
                d.priority         = $('#proc_priority').val();
                d.patient_search   = $('#proc_patient_search').val();
                d.date_from        = $('#proc_date_from').val();
                d.date_to          = $('#proc_date_to').val();
            },
            error: function (xhr) {
                console.error('DataTables AJAX error:', xhr.responseText);
            }
        },
        columns: [
            { data: null, name: 'DT_RowIndex', orderable: false, searchable: false,
              render: function (data, type, row, meta) {
                  return meta.row + meta.settings._iDisplayStart + 1 + '.';
              }
            },
            { data: 'mr_number',      name: 'patient.mr_number',      orderable: false },
            { data: 'patient_name',   name: 'patient.first_name',     orderable: true  },
            { data: 'age',            name: 'age',                    orderable: false, searchable: false },
            @if($user->role !== 'doctor')
            { data: 'ordered_by',     name: 'doctor.user.first_name', orderable: true  },
            @endif
            { data: 'procedure_name', name: 'medicalService.name',    orderable: true  },
            { data: 'priority',       name: 'priority',               orderable: true  },
            { data: 'date',           name: 'ordered_at',             orderable: true  },
            { data: 'time',           name: 'ordered_at',             orderable: false, searchable: false },
            { data: 'status',         name: 'status',                 orderable: true  },
            { data: 'result_status',  name: 'result_status',          orderable: false, searchable: false },
            { data: 'actions',        name: 'actions',                orderable: false, searchable: false }
        ],
        order: [[{{ $user->role === 'doctor' ? '6' : '7' }}, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [-1] }]
    });

    let _t;
    $('#proc_patient_search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { proceduresTable.draw(); }, 500);
    });
});

function updateStatus(investigationId, newStatus) {
    if (confirm('Mark investigation as ' + newStatus + '?')) {
        fetch('/investigations/' + investigationId + '/status', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                proceduresTable.ajax.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(function () { alert('Error updating status'); });
    }
}

function showLowStockAlert(userRole, actionType) {
    var messages = {
        collect: userRole === 'nurse'
            ? 'Cannot collect sample - insufficient stock in laboratory'
            : 'Cannot mark as collected - insufficient stock available',
        process: userRole === 'nurse'
            ? 'Cannot start procedure - insufficient supplies available'
            : userRole === 'radiologist'
                ? 'Cannot start study - insufficient materials available'
                : 'Cannot mark as processing - insufficient stock available',
        results: userRole === 'radiologist'
            ? 'Cannot create report - procedure cannot proceed due to low stock'
            : 'Cannot add results - collection blocked due to insufficient stock'
    };
    alert(messages[actionType] || 'Cannot proceed - insufficient stock available');
}

function updateInvestigationStatus(investigationId, newStatus) {
    if (!confirm('Are you sure you want to mark this procedure as ' + newStatus + '?')) return;

    fetch('/lab/investigations/' + investigationId + '/status', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(function (response) {
        if (!response.ok) {
            return response.json().then(function (e) { throw { status: response.status, data: e }; });
        }
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            proceduresTable.ajax.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(function (error) {
        if (error.status === 422 && error.data && error.data.stock_details) {
            showStockErrorModal(error.data.stock_details, error.data.message,
                                error.data.stock_location || 'Laboratory');
        } else {
            alert('Failed to update status. Please try again.');
        }
    });
}

function showStockErrorModal(stockDetails, message, stockLocation) {
    stockLocation = stockLocation || 'Laboratory';
    var existing = document.getElementById('stockErrorModal');
    if (existing) existing.remove();

    var rows = stockDetails.map(function (item) {
        var cls   = !item.is_available && !item.is_optional ? 'table-danger'
                  : item.is_available ? 'table-success' : 'table-warning';
        var badge = item.is_available  ? '<span class="badge bg-success">Sufficient</span>'
                  : item.is_optional   ? '<span class="badge bg-warning">Low Stock</span>'
                  : '<span class="badge bg-danger">Insufficient</span>';
        return '<tr class="' + cls + '"><td>' + (item.medication_name || 'Unknown') +
               (item.is_optional ? ' <small class="text-muted">(Optional)</small>' : '') +
               '</td><td>' + item.required_quantity +
               '</td><td>' + item.available_quantity +
               '</td><td>' + badge + '</td></tr>';
    }).join('');

    document.body.insertAdjacentHTML('beforeend',
        '<div class="modal fade" id="stockErrorModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg"><div class="modal-content">' +
        '<div class="modal-header bg-warning">' +
        '<h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Insufficient Stock (' + stockLocation + ')</h5>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
        '<div class="modal-body"><div class="alert alert-warning">' + message + '</div>' +
        '<table class="table table-sm table-bordered"><thead class="table-light">' +
        '<tr><th>Item</th><th>Required</th><th>Available</th><th>Status</th></tr></thead>' +
        '<tbody>' + rows + '</tbody></table></div>' +
        '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>' +
        '</div></div></div>'
    );
    var modal = new bootstrap.Modal(document.getElementById('stockErrorModal'));
    modal.show();
    document.getElementById('stockErrorModal').addEventListener('hidden.bs.modal', function () { this.remove(); });
}

function showStockDetailsForInvestigation(investigationId) {
    fetch('/lab/investigations/' + investigationId + '/check-stock', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success && data.details) {
            showStockErrorModal(data.details, data.message || 'Stock information:',
                                data.stock_location || 'Laboratory');
        } else {
            alert('No stock details available.');
        }
    })
    .catch(function () { alert('Failed to fetch stock details.'); });
}
</script>
@endsection
