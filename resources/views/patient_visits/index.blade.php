@extends('layouts.app_main_layout')

@section('page_title')
    {{ isset($selectedPatient) ? 'Visits for ' . $selectedPatient->full_name : (isset($selectedDoctor) ? 'Visits by Dr. ' . $selectedDoctor->user->name : 'Patient Visits') }}
 @endsection

@section('main_content')
<div class="container-fluid">

    @if(!isset($selectedPatient) && !isset($selectedDoctor))
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" id="pv_date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" id="pv_date_to" value="{{ $dateTo }}">
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check"></i> 
                        {{ isset($selectedPatient) ? 'Visits for ' . $selectedPatient->full_name : (isset($selectedDoctor) ? 'Visits by Dr. ' . $selectedDoctor->user->name : 'Patient Visits') }}
                    </h3>
                    <div class="card-tools">
                        @if(isset($selectedPatient))
                            <a href="{{ route('patients.show', $selectedPatient->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-user"></i> View Patient
                            </a>
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="openCreateVisitModal({{ $selectedPatient->id }})">
                                <i class="fas fa-plus"></i> New Visit
                            </button>
                            <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Patients
                            </a>
                        @elseif(isset($selectedDoctor))
                            <a href="{{ route('doctors.show', $selectedDoctor->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-user-md"></i> View Doctor
                            </a>
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="openCreateVisitModal(null, {{ $selectedDoctor->id }})">
                                <i class="fas fa-plus"></i> New Visit
                            </button>
                            <a href="{{ route('doctors.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Doctors
                            </a>
                        @else
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="openCreateVisitModal()">
                                <i class="fas fa-plus"></i> New Visit
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-bordered table-hover" id="visitsTable">
                        <thead>
                            <tr>
                                @if(!isset($selectedPatient))
                                    <th>Patient</th>
                                @endif
                                <th>Visit Date</th>
                                <th>Category</th>
                                @if(!isset($selectedDoctor))
                                    <th>Doctor</th>
                                @endif
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Lab Investigation Modal Component --}}
@include('partials.lab_investigation_modal')

{{-- Include Prescription Modal Component --}}
@include('partials.prescription_modal')

{{-- Visit Create/Edit Modal --}}
<div class="modal fade" id="visitModal" tabindex="-1" aria-labelledby="visitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visitModalLabel"><i class="fas fa-calendar-plus"></i> New Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="visitForm">
                @csrf
                <input type="hidden" id="vm_method" name="_method" value="POST">
                <input type="hidden" id="vm_visit_id" name="visit_id" value="">
                <input type="hidden" id="vm_patient_value" name="patient" value="">
                <div class="modal-body">
                    <div id="vm_alert" class="alert d-none"></div>
                    <div class="row">
                        {{-- Patient: Select2 for create, readonly display for edit --}}
                        <div class="col-md-6" id="vm_patient_select_wrap">
                            <div class="mb-3">
                                <label>Patient <span class="text-danger">*</span></label>
                                <select class="form-control" id="vm_patient">
                                    <option value="">Search patient...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 d-none" id="vm_patient_display_wrap">
                            <div class="mb-3">
                                <label>Patient</label>
                                <input type="text" class="form-control" id="vm_patient_display" readonly>
                                <small class="text-muted">Patient cannot be changed once visit is created.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Visit Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="vm_visit_date" name="visit_date" required>
                            </div>
                        </div>
                    </div>
                    <div id="vm_status_alert" class="d-none">
                        <div class="alert alert-warning mb-2" id="vm_in_treatment_msg" style="display:none!important">
                            <i class="fas fa-info-circle"></i> Patient is currently in treatment. Only doctor and visit type (to Internal Referral) can be changed.
                        </div>
                        <div class="alert alert-danger mb-2" id="vm_discharged_msg" style="display:none!important">
                            <i class="fas fa-exclamation-triangle"></i> Patient has been discharged. Limited fields can be modified.
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Patient Category <span class="text-danger">*</span></label>
                                <select class="form-control" id="vm_visit_category" name="visit_category" required>
                                    <option value="">Select Category</option>
                                    @foreach($patientCategories as $category)
                                        <option value="{{ $category->id }}" data-type="{{ $category->type }}">{{ $category->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Visit Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="vm_visit_type" name="visit_type" required>
                                    <option value="">Select Visit Type</option>
                                    @foreach($visitTypes as $vt)
                                        <option value="{{ $vt->id }}">{{ $vt->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" id="vm_doctor_wrap">
                            <div class="mb-3">
                                <label>Attending Doctor</label>
                                <select class="form-control" id="vm_doctor" name="doctor">
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->doctor_id }}">{{ $doctor->user->name ?? 'N/A' }} - {{ $doctor->specialization }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Consultation Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">Tsh</span>
                                    <input type="text" class="form-control" id="vm_fee_display" readonly placeholder="Auto-calculated">
                                    <button class="btn btn-outline-secondary" type="button" id="vm_apply_fee_btn">
                                        <i class="fas fa-check"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label id="vm_cash_label">Cash Amount <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vm_amount_cash" name="amount_cash" value="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label id="vm_covered_label">Covered Amount</label>
                                <input type="text" class="form-control" id="vm_amount_covered" name="amount_covered" value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>SIC Number</label>
                                <input type="text" class="form-control" id="vm_sic_no" name="sic_no" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Authorization Number</label>
                                <input type="text" class="form-control" id="vm_authorization_no" name="authorization_no" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>NHIF Reference Number</label>
                                <input type="text" class="form-control" id="vm_nhif_reference_no" name="nhif_reference_no" maxlength="30">
                            </div>
                        </div>
                    </div>
                    {{-- Post status — edit only --}}
                    <div class="row d-none" id="vm_post_status_wrap">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Post Status</label>
                                <select class="form-control" id="vm_post_status" name="post_status">
                                    <option value="0">Not Posted</option>
                                    <option value="1">Posted</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="vm_submit_btn">
                        <i class="fas fa-save"></i> <span id="vm_submit_text">Create Visit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var visitsTable;
var vmIsEdit   = false;
var vmVisitData = {};

$(document).ready(function() {
    var columns = [
        @if(!isset($selectedPatient))
        { data: 'patient_name', name: 'patientInfo.first_name' },
        @endif
        { data: 'visit_date', name: 'visit_date' },
        { data: 'category', name: 'visitCategory.description' },
        @if(!isset($selectedDoctor))
        { data: 'doctor_name', name: 'doctorInfo.user.name' },
        @endif
        { data: 'status', name: 'visit_status' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ];

    visitsTable = $('#visitsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("patient_visits.index") }}',
            data: function(d) {
                @if(isset($selectedPatient))
                d.patient_id = {{ $selectedPatient->id }};
                @endif
                @if(isset($selectedDoctor))
                d.doctor_id = {{ $selectedDoctor->id }};
                @endif
                @if(!isset($selectedPatient) && !isset($selectedDoctor))
                d.date_from = $('#pv_date_from').val();
                d.date_to   = $('#pv_date_to').val();
                @endif
            }
        },
        columns: columns,
        order: [[@if(!isset($selectedPatient)) 1 @else 0 @endif, 'desc']],
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
            search: "Search visits:",
            lengthMenu: "Show _MENU_ visits per page",
            info: "Showing _START_ to _END_ of _TOTAL_ visits",
            infoEmpty: "No visits found",
            infoFiltered: "(filtered from _MAX_ total visits)"
        }
    });

    @if(!isset($selectedPatient) && !isset($selectedDoctor))
    $('#pv_date_from, #pv_date_to').on('change', function () { visitsTable.draw(); });
    @endif

    // ---- Select2 patient search in modal ----
    $('#vm_patient').select2({
        dropdownParent: $('#visitModal'),
        placeholder: 'Type to search for patient...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("patients.search") }}',
            dataType: 'json', delay: 300,
            data: function(params) { return { q: params.term, page: params.page || 1 }; },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        language: {
            inputTooShort: function() { return 'Type 2 or more characters to search'; },
            searching: function()     { return 'Searching patients...'; },
            noResults: function()     { return 'No patients found'; }
        }
    });

    // Keep the patient hidden value in sync with Select2 selection
    $('#vm_patient').on('select2:select', function(e) {
        $('#vm_patient_value').val(e.params.data.id);
    }).on('select2:clear', function() {
        $('#vm_patient_value').val('');
    });

    // ---- Consultation fee lookup ----
    function vmLookupFee() {
        var doctorId    = $('#vm_doctor').val();
        var categoryId  = $('#vm_visit_category').val();
        var visitTypeId = $('#vm_visit_type').val();
        if (vmVisitData.is_waiting === false) return; // locked in edit mode
        if (doctorId && categoryId && visitTypeId) {
            $('#vm_fee_display').val('Loading...');
            $.get('{{ route("consultation_fees.get_fee") }}', {
                doctor_id: doctorId, patient_category_id: categoryId, visit_type_id: visitTypeId
            }).done(function(data) {
                if (data.cash_amount || data.covered_amount) {
                    var cash    = parseFloat(data.cash_amount)    || 0;
                    var covered = parseFloat(data.covered_amount) || 0;
                    $('#vm_fee_display').val((cash + covered).toFixed(2))
                        .removeClass('text-warning text-danger').addClass('text-success fw-bold')
                        .data('cash', cash).data('covered', covered);
                    $('#vm_apply_fee_btn').prop('disabled', false);
                } else {
                    $('#vm_fee_display').val('No fee structure found')
                        .removeClass('text-success fw-bold').addClass('text-warning');
                    $('#vm_apply_fee_btn').prop('disabled', true);
                }
            }).fail(function() {
                $('#vm_fee_display').val('Error loading fee').addClass('text-danger');
                $('#vm_apply_fee_btn').prop('disabled', true);
            });
        } else {
            $('#vm_fee_display').val('').removeClass('text-success text-warning text-danger fw-bold');
        }
    }

    $('#vm_apply_fee_btn').on('click', function() {
        var cash    = parseFloat($('#vm_fee_display').data('cash'))    || 0;
        var covered = parseFloat($('#vm_fee_display').data('covered')) || 0;
        $('#vm_amount_cash').val(cash.toFixed(2));
        $('#vm_amount_covered').val(covered.toFixed(2));
    });

    // ---- Lab Only toggle ----
    function vmCheckLabOnly() {
        var text = $('#vm_visit_type option:selected').text().toLowerCase();
        if (text.includes('lab only')) {
            $('#vm_doctor_wrap').hide();
            $('#vm_doctor').val('');
            $('#vm_fee_display').val('Not applicable').removeClass('text-success fw-bold');
            $('#vm_apply_fee_btn').prop('disabled', true);
            $('#vm_amount_covered').val('0.00').prop('disabled', true);
            $('#vm_covered_label').html('Covered Amount <small class="text-muted">(Not applicable)</small>');
            $('#vm_cash_label').html('Cash Amount <span class="text-danger">*</span> <small class="text-success">(Cash Only)</small>');
        } else {
            $('#vm_doctor_wrap').show();
            $('#vm_amount_covered').prop('disabled', false);
            $('#vm_covered_label').html('Covered Amount');
            $('#vm_cash_label').html('Cash Amount <span class="text-danger">*</span>');
            vmLookupFee();
        }
    }

    $('#vm_visit_type').on('change', vmCheckLabOnly);
    $('#vm_doctor, #vm_visit_category').on('change', vmLookupFee);

    // ---- Form submit ----
    $('#visitForm').on('submit', function(e) {
        e.preventDefault();
        $('#vm_alert').addClass('d-none').text('');
        $('#vm_submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        var url  = vmIsEdit
            ? '{{ url("patient_visits") }}/' + $('#vm_visit_id').val()
            : '{{ route("patient_visits.store") }}';

        var data = $('#visitForm').serialize();
        if (vmIsEdit) data += '&_method=PUT';

        // If visit_category is locked (visually readonly), ensure its value is submitted
        if (vmIsEdit && !vmVisitData.is_waiting && vmVisitData.visit_category) {
            data += '&visit_category=' + vmVisitData.visit_category;
        }
        // If doctor is locked (discharged), ensure its value is submitted
        if (vmIsEdit && vmVisitData.is_discharged && vmVisitData.doctor) {
            data += '&doctor=' + vmVisitData.doctor;
        }

        $.ajax({
            url: url, type: 'POST', data: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    $('#visitModal').modal('hide');
                    visitsTable.ajax.reload();
                    showPageAlert('success', res.message);
                } else {
                    showVmAlert('danger', res.message || 'An error occurred.');
                }
            },
            error: function(xhr) {
                var msg = 'An error occurred.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                }
                showVmAlert('danger', msg);
            },
            complete: function() {
                $('#vm_submit_btn').prop('disabled', false)
                    .html('<i class="fas fa-save"></i> <span>' + (vmIsEdit ? 'Update Visit' : 'Create Visit') + '</span>');
            }
        });
    });

    // Clear Select2 when modal hidden
    $('#visitModal').on('hidden.bs.modal', function() {
        $('#vm_patient').val(null).trigger('change');
        vmVisitData = {};
    });
});

function showVmAlert(type, msg) {
    $('#vm_alert').removeClass('d-none alert-success alert-danger alert-warning')
        .addClass('alert-' + type).html(msg);
}

function showPageAlert(type, msg) {
    var el = $('<div class="alert alert-' + type + ' alert-dismissible">' +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' + msg + '</div>');
    $('.card-body').first().prepend(el);
    setTimeout(function() { el.fadeOut(function() { el.remove(); }); }, 4000);
}

function vmResetForm() {
    vmVisitData = {};
    $('#visitForm')[0].reset();
    $('#vm_patient_value').val('');
    $('#vm_amount_cash').val('0.00');
    $('#vm_amount_covered').val('0.00').prop('disabled', false);
    $('#vm_fee_display').val('').removeClass('text-success text-warning text-danger fw-bold');
    $('#vm_apply_fee_btn').prop('disabled', true);
    $('#vm_doctor_wrap').show();
    $('#vm_covered_label').html('Covered Amount');
    $('#vm_cash_label').html('Cash Amount <span class="text-danger">*</span>');
    $('#vm_alert').addClass('d-none');
    $('#vm_post_status_wrap').addClass('d-none');
    $('#vm_in_treatment_msg, #vm_discharged_msg').hide();
    $('#vm_visit_date, #vm_amount_cash, #vm_amount_covered, #vm_sic_no, #vm_authorization_no, #vm_nhif_reference_no')
        .prop('readonly', false);
    $('#vm_visit_category, #vm_visit_type, #vm_doctor').prop('disabled', false)
        .css({'pointer-events': '', 'background-color': '', 'opacity': ''});
}

function openCreateVisitModal(patientId, doctorId) {
    vmIsEdit = false;
    vmResetForm();
    $('#visitModal .modal-title').html('<i class="fas fa-calendar-plus"></i> New Visit');
    $('#vm_submit_btn span').text('Create Visit');
    $('#vm_visit_id').val('');
    $('#vm_method').val('POST');
    $('#vm_patient_select_wrap').removeClass('d-none');
    $('#vm_patient_display_wrap').addClass('d-none');
    $('#vm_visit_date').val('{{ date("Y-m-d") }}');

    if (patientId) {
        // Fetch patient info to pre-populate Select2
        $.get('{{ route("patients.search") }}', { q: patientId, by_id: 1 }, function(data) {
            if (data.results && data.results.length) {
                var r = data.results[0];
                $('#vm_patient').append(new Option(r.text, r.id, true, true)).trigger('change');
                $('#vm_patient_value').val(r.id);
            }
        });
    }
    if (doctorId) {
        $('#vm_doctor').val(doctorId);
    }
    $('#visitModal').modal('show');
}

function openEditVisitModal(visitId) {
    vmIsEdit = true;
    vmResetForm();
    $('#visitModal .modal-title').html('<i class="fas fa-edit"></i> Edit Visit');
    $('#vm_submit_btn span').text('Update Visit');
    $('#vm_visit_id').val(visitId);
    $('#vm_method').val('PUT');
    $('#vm_patient_select_wrap').addClass('d-none');
    $('#vm_patient_display_wrap').removeClass('d-none');
    $('#vm_post_status_wrap').removeClass('d-none');

    $.ajax({
        url: '{{ url("patient_visits") }}/' + visitId + '/edit',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        success: function(v) {
            vmVisitData = v;
            $('#vm_patient_display').val(v.patient_name);
            $('#vm_patient_value').val(v.patient_id);
            $('#vm_visit_date').val(v.visit_date);
            $('#vm_visit_category').val(v.visit_category);
            $('#vm_visit_type').val(v.visit_type);
            $('#vm_doctor').val(v.doctor);
            $('#vm_amount_cash').val(parseFloat(v.amount_cash || 0).toFixed(2));
            $('#vm_amount_covered').val(parseFloat(v.amount_covered || 0).toFixed(2));
            $('#vm_sic_no').val(v.sic_no || '');
            $('#vm_authorization_no').val(v.authorization_no || '');
            $('#vm_nhif_reference_no').val(v.nhif_reference_no || '');
            $('#vm_post_status').val(v.post_status);

            if (v.is_lab_only) {
                $('#vm_doctor_wrap').hide();
                $('#vm_amount_covered').prop('disabled', true);
            }

            if (!v.is_waiting) {
                // Visually lock these fields — they are still enabled (will serialize), but look readonly
                $('#vm_visit_date, #vm_amount_cash, #vm_sic_no, #vm_authorization_no, #vm_nhif_reference_no')
                    .prop('readonly', true);
                $('#vm_amount_covered').prop('readonly', true);
                $('#vm_visit_category').css({'pointer-events': 'none', 'background-color': '#e9ecef', 'opacity': '0.7'});
                $('#vm_apply_fee_btn').prop('disabled', true);
                $('#vm_fee_display').val('Fees locked');
            }
            if (v.is_in_treatment) {
                $('#vm_in_treatment_msg').show();
            }
            if (v.is_discharged) {
                $('#vm_discharged_msg').show();
                $('#vm_doctor').css({'pointer-events': 'none', 'background-color': '#e9ecef', 'opacity': '0.7'});
            }

            $('#visitModal').modal('show');
        },
        error: function() {
            showPageAlert('danger', 'Failed to load visit data.');
        }
    });
}
</script>

{{-- Lab Investigation Modal JavaScript --}}
<script src="{{ asset('js/lab-investigation-modal.js') }}"></script>

{{-- Prescription Modal JavaScript --}}
<script src="{{ asset('js/prescription-modal.js') }}"></script>
@endsection

@section('extra_footer_content')
{{-- Lab Investigation Modal Styles --}}
<link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">

{{-- Prescription Modal Styles --}}
<link rel="stylesheet" href="{{ asset('css/prescription-modal.css') }}">

<style>
/* Enhanced Visit Type Badge Styling */
.bg-primary { background-color: #007bff !important; color: white !important; }
.bg-success { background-color: #28a745 !important; color: white !important; }
.bg-warning { background-color: #ffc107 !important; color: black !important; }
.bg-info { background-color: #17a2b8 !important; color: white !important; }
.bg-danger { background-color: #dc3545 !important; color: white !important; }
.bg-secondary { background-color: #6c757d !important; color: white !important; }

.badge { font-size: 0.85em; padding: 0.4em 0.6em; }
</style>
@endsection
