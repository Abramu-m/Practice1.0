@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Patients' }}
 @endsection

@section('Content_Description')
    {{ 'Manage patients.' }}
@endsection

@section('main_content')
    <div class="row mb-2">
        <div class="col-md-6">
            <a href="{{ route('patients.create') }}" class="btn btn-primary">Add Patient</a>
        </div>
        <div class="col-md-6">
            <form id="quickNhifForm" class="">
                @csrf
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-id-card"></i></span>
                    <input type="text" name="card_number" class="form-control" placeholder="Enter NHIF card number" aria-label="NHIF Card Number">
                    <button class="btn btn-primary" type="submit" title="Add patient by NHIF card"><i class="fas fa-plus-circle me-1"></i> Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex gap-2">
                <select id="category_filter" class="form-select" style="max-width: 200px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->description }}</option>
                    @endforeach
                </select>
                <select id="status_filter" class="form-select" style="max-width: 200px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="button" class="btn btn-outline-secondary" id="clearFilters" title="Clear all filters">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-body">
            <table class="table table-bordered table-hover" id="patientsTable">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Contact</th>
                        <th>Category</th>
                        <th>Visits</th>
                        <th>Status</th>
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
$(document).ready(function() {
    // Initialize DataTable with AJAX
    const table = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("patients.index") }}',
            data: function(d) {
                d.category_filter = $('#category_filter').val();
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'full_name', name: 'full_name' },
            { data: 'gender', name: 'gender' },
            { data: 'date_of_birth', name: 'date_of_birth' },
            { data: 'contact', name: 'contact' },
            { data: 'category', name: 'category' },
            { data: 'visits', name: 'visits', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        responsive: true,
        language: {
            search: "Search patients:",
            lengthMenu: "Show _MENU_ patients per page",
            info: "Showing _START_ to _END_ of _TOTAL_ patients",
            infoEmpty: "No patients found",
            infoFiltered: "(filtered from _MAX_ total patients)"
        }
    });

    // Filter by category
    $('#category_filter').on('change', function() {
        table.draw();
    });

    // Filter by status
    $('#status_filter').on('change', function() {
        table.draw();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#category_filter').val('');
        $('#status_filter').val('');
        table.search('').draw();
    });

    // Toastr configuration
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": 5000
        };
    }
});
</script>
@endsection

@section('footer_scripts')
<script>
// NHIF Quick Add form handler
$(document).on('submit', '#quickNhifForm', function(e) {
    e.preventDefault();
    const card = $(this).find('[name="card_number"]').val();
    if (!card) return alert('Enter card number');

    const btn = $(this).find('button[type="submit"]');
    const orig = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '{{ route("nhif.verify-member") }}',
        type: 'POST',
        data: { card_number: card, _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success && response.patient_exists === false) {
                // Redirect to create page with prefilled data
                window.location.href = '{{ route("patients.create") }}?nhif=' + encodeURIComponent(card);
            } else if (response.success && response.patient_exists === true) {
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    alert('Patient already exists locally.');
                }
            } else {
                alert(response.message || 'Verification failed');
            }
        },
        error: function(xhr) {
            const resp = xhr.responseJSON || {};
            alert(resp.error || 'Error verifying NHIF card');
        },
        complete: function() {
            btn.prop('disabled', false).html(orig);
        }
    });
});
</script>
@endsection

@section('extra_modals')
<!-- Patient Create Modal (AJAX loaded) for Patients index -->
<div class="modal fade" id="patientCreateModalPatientsIndex" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="patientCreateModalBodyPatientsIndex">
                <div class="text-center p-4">Loading patient form...</div>
            </div>
        </div>
    </div>
</div>
@endsection
