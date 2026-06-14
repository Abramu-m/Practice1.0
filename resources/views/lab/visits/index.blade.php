@extends('layouts.app_main_layout')

@section('page_title', 'Lab - Patient Visits')

@section('main_content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search Patient</label>
                    <input type="text" class="form-control form-control-sm" id="lab_patient_search"
                           placeholder="Name or MR number" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Doctor</label>
                    <select class="form-select form-select-sm" id="lab_doctor"
                            onchange="labTable.draw()">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}">
                                Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Priority</label>
                    <select class="form-select form-select-sm" id="lab_priority"
                            onchange="labTable.draw()">
                        <option value="">All Priorities</option>
                        <option value="stat">STAT</option>
                        <option value="urgent">Urgent</option>
                        <option value="routine">Routine</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="lab_date_from"
                           value="{{ $dateFrom }}" onchange="labTable.draw()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="lab_date_to"
                           value="{{ $dateTo }}" onchange="labTable.draw()">
                </div>
            </div>
            <div class="form-text text-muted mt-2 mb-0">
                @if($isNurse)
                    Open visits (<strong>Waiting</strong> / <strong>In Treatment</strong>) with paid procedures still pending are always shown, even if their visit date is outside the range above.
                @else
                    Open visits (<strong>Waiting</strong> / <strong>In Treatment</strong>) with paid investigations still awaiting lab work are always shown, even if their visit date is outside the range above.
                @endif
            </div>
        </div>
    </div>

    <!-- Lab Visits Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                @if($isNurse)
                    <i class="fas fa-syringe text-primary"></i>
                    Procedures — Patient Visits with Pending Procedures
                @else
                    <i class="fas fa-vial text-primary"></i>
                    Lab — Patient Visits with Pending Investigations
                @endif
            </h4>
        </div>
        <div class="card-body p-0">
            <table id="labVisitsTable" class="table table-hover w-100">
                <thead class="table-light">
                    <tr>
                        <th>Patient</th>
                        <th>Visit Date</th>
                        <th>Doctor</th>
                        <th>{{ $isNurse ? 'Procedures' : 'Lab Investigations' }}</th>
                        <th>Priority Status</th>
                        <th>Visit Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
var labTable;

$(document).ready(function () {
    labTable = $('#labVisitsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: '{{ route("lab.visits.index") }}',
            data: function (d) {
                d.patient_search = $('#lab_patient_search').val();
                d.doctor_id      = $('#lab_doctor').val();
                d.priority       = $('#lab_priority').val();
                d.date_from      = $('#lab_date_from').val();
                d.date_to        = $('#lab_date_to').val();
            }
        },
        columns: [
            { data: 'patient_info',        name: 'patientInfo.first_name' },
            { data: 'visit_date_formatted', name: 'visit_date' },
            { data: 'doctor_name',          name: 'doctorInfo.user.first_name', orderable: false },
            { data: 'investigations_info',  name: 'investigations_info',        orderable: false, searchable: false },
            { data: 'priority_status',      name: 'priority_status',            orderable: false, searchable: false },
            { data: 'visit_status',         name: 'visit_status',               orderable: false, searchable: false },
            { data: 'actions',              name: 'actions',                    orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [2, 3, 4, 5, 6] }]
    });

    // Debounced patient search
    let _t;
    $('#lab_patient_search').on('input', function () {
        clearTimeout(_t);
        _t = setTimeout(function () { labTable.draw(); }, 500);
    });
});
</script>
@endsection
