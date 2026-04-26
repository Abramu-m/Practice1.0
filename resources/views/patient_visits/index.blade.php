@extends('layouts.app_main_layout')

@section('page_title')
    {{ isset($selectedPatient) ? 'Visits for ' . $selectedPatient->full_name : (isset($selectedDoctor) ? 'Visits by Dr. ' . $selectedDoctor->user->name : 'Patient Visits') }}
 @endsection

@section('main_content')
<div class="container-fluid">
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
                            <a href="{{ route('patient_visits.create', ['patient_id' => $selectedPatient->id]) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> New Visit
                            </a>
                            <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Patients
                            </a>
                        @elseif(isset($selectedDoctor))
                            <a href="{{ route('doctors.show', $selectedDoctor->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-user-md"></i> View Doctor
                            </a>
                            <a href="{{ route('patient_visits.create', ['doctor_id' => $selectedDoctor->id]) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> New Visit
                            </a>
                            <a href="{{ route('doctors.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Doctors
                            </a>
                        @else
                            <a href="{{ route('patient_visits.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> New Visit
                            </a>
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
                                <th>Visit ID</th>
                                @if(!isset($selectedPatient))
                                    <th>Patient</th>
                                @endif
                                <th>Visit Date</th>
                                <th>Category</th>
                                <th>Visit Type</th>
                                @if(!isset($selectedDoctor))
                                    <th>Doctor</th>
                                @endif
                                <th>Cash/Covered</th>
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

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Define columns based on context
    var columns = [
        { data: 'id', name: 'id' },
        @if(!isset($selectedPatient))
        { data: 'patient_name', name: 'patientInfo.first_name' },
        @endif
        { data: 'visit_date', name: 'visit_date' },
        { data: 'category', name: 'visitCategory.description' },
        { data: 'visit_type', name: 'visitType.description' },
        @if(!isset($selectedDoctor))
        { data: 'doctor_name', name: 'doctorInfo.user.name' },
        @endif
        {
            data: 'cash_amount',
            name: 'amount_cash',
            orderable: true,
            render: function(data, type, row) {
                if (type === 'sort' || type === 'type') {
                    return data;
                }

                var cash = row.cash_amount || '$0.00';
                var covered = row.covered_amount || '$0.00';
                return cash + ' / ' + covered;
            }
        },
        { data: 'status', name: 'visit_status' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ];

    // Initialize DataTable
    const table = $('#visitsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("patient_visits.index") }}',
            data: function(d) {
                @if(isset($selectedPatient))
                d.patient_id = {{ $selectedPatient->id }};
                @endif
                @if(isset($selectedDoctor))
                d.doctor_id = {{ $selectedDoctor->id }};
                @endif
            }
        },
        columns: columns,
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            search: "Search visits:",
            lengthMenu: "Show _MENU_ visits per page",
            info: "Showing _START_ to _END_ of _TOTAL_ visits",
            infoEmpty: "No visits found",
            infoFiltered: "(filtered from _MAX_ total visits)"
        }
    });
});
</script>

{{-- Lab Investigation Modal JavaScript --}}
<script src="{{ asset('js/lab-investigation-modal.js') }}"></script>
@endsection

@section('extra_footer_content')
{{-- Lab Investigation Modal Styles --}}
<link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">

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
