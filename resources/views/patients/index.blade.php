<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patients\index.blade.php -->
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
                {{-- <small class="form-text text-muted mt-1">Enter the NHIF card number and click Add — we'll open a prefilled patient form if not found.</small> --}}
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" action="{{ route('patients.index') }}" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search patients..." value="{{ request('search') }}">
                <select name="category_filter" class="form-select me-2">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>
                            {{ $category->description }}
                        </option>
                    @endforeach
                </select>
                <select name="status_filter" class="form-select me-2">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
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
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $patient->full_name }}</td>
                        <td>{{ ucfirst($patient->gender) }}</td>
                        <td>{{ $patient->date_of_birth->format('d/m/Y') }}</td>
                        <td>{{ $patient->contact ?? 'N/A' }}</td>
                        <td>
                            {{ $patient->category->description ?? 'N/A' }}
                            @if(!empty($patient->card_number))
                                <br>
                                <small class="text-muted">Card: {{ $patient->card_number }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info" style="color: black">{{ $patient->visits->count() }} visit(s)</span>
                        </td>
                        <td>
                            @if($patient->status == 'active')
                                <span class="badge badge-success" style="color: black">Active</span>
                            @else
                                <span class="badge badge-danger" style="color: black">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Patient Actions">
                                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-info" title="View Patient">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-warning" title="Edit Patient">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                @if($patient->active_visit && 
                                    (auth()->user()->is_admin || auth()->user()->is_super || 
                                     (auth()->user()->role === 'doctor' && auth()->user()->doctor && 
                                      auth()->user()->doctor->doctor_id == $patient->active_visit->doctor)))
                                    <a href="{{ route('consultations.show', $patient->active_visit->id) }}" class="btn btn-sm btn-success" title="{{ $patient->active_visit->visit_status == 0 ? 'Start Consultation' : 'Continue Consultation' }}">
                                        <i class="fas fa-user-md"></i> Consult
                                    </a>
                                @endif
                                @if(!$patient->active_visit)
                                    <a href="{{ route('patient_visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" title="Create Visit">
                                        <i class="fas fa-plus-circle"></i> Visit
                                    </a>
                                @endif
                                @if($patient->visits->count() > 0)
                                <a href="{{ route('patient_visits.index', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-secondary" title="View Visits">
                                    <i class="fas fa-list"></i> Visits
                                </a>
                                @endif
                            </div>
                            @if(auth()->user()->isAdmin())
                            <div style="margin-top: 5px;">
                                <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this patient?')" class="btn btn-sm btn-danger" title="Delete Patient">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No patients found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $patients->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Lab Investigation functionality removed from this view -->
@endsection

@section('scripts')
<script>
// Wait for document ready to ensure jQuery is loaded
$(document).ready(function() {
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

// Lab investigation UI and handlers removed from this view.

// Toastr configuration
if (typeof toastr !== 'undefined') {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": 5000
    };
}
</script>

<style>
/* Lab-related styles removed from this view. */
</style>
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

@section('footer_scripts')
<script>
// Bind quick NHIF form globally
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
        data: { card_number: card },
        success: function(response) {
            console.log('NHIF quick add response:', response);
            if (response.success && response.patient_exists === false) {
                // load form and insert prefill
                $.get('{{ route("patients.create") }}', function(html) {
                    const tmp = $('<div>').html(html);
                    // Prefer the real patients.create form (it posts to patients.store) instead of layout quick-add
                    const storeRoute = '{{ route("patients.store") }}';
                    let form = tmp.find('form').filter(function() {
                        const a = $(this).attr('action') || '';
                        return a.indexOf(storeRoute) !== -1 || a === storeRoute;
                    }).first();
                    if (!form.length) {
                        // fallback: pick first form that has first_name but exclude the quick-add modal when possible
                        form = tmp.find('form:has([name="first_name"])').filter(function() {
                            return $(this).attr('id') !== 'patientQuickAddForm';
                        }).first();
                    }
                    if (!form.length) form = tmp.find('form').first();

                    // prefill
                    const prefill = response.prefill || {};
                    Object.keys(prefill).forEach(function(key) {
                        const selector = '[name="' + key + '"]';
                        const input = form.find(selector);
                        const val = prefill[key];
                        // only set when value is not null/undefined to avoid clearing defaults
                        if (input.length && val != null) input.val(val);
                    });

                    // Attach the full NHIF response to the form as a hidden field so it is sent on submit
                    try {
                        // remove any existing field to avoid duplicates
                        form.find('[name="nhif_response"]').remove();
                        // The server expects a payload named `nhif_response` that contains the
                        // NHIF service object (AuthorizationNo, CardNo, MembershipNo, etc.).
                        // Our verification response wraps that as `response.nhif_response`.
                        const payload = response && response.nhif_response ? response.nhif_response : response;
                        const nhifInput = $('<input>').attr({
                            type: 'hidden',
                            name: 'nhif_response'
                        }).val(JSON.stringify(payload));
                        form.append(nhifInput);
                    } catch (e) {
                        // ignore errors attaching NHIF payload
                        console.warn('Could not attach NHIF response to form', e);
                    }

                    // debug logging removed

                    // insert and show modal
                    if (form.length) {
                        $('#patientCreateModalBodyPatientsIndex').html(form);
                    } else {
                        $('#patientCreateModalBodyPatientsIndex').html(html);
                    }

                    // initialize insurance toggle and other behaviors for AJAX-inserted form
                    (function initPatientCreateModal(rootSelector, prefill) {
                        const root = $(rootSelector);
                        const patientCategorySelect = root.find('#patient_category');
                        const insuranceFields = root.find('#insurance-fields');

                        if (!patientCategorySelect.length || !insuranceFields.length) return;

                        const toggleInsuranceFields = function() {
                            const selectedType = patientCategorySelect.find('option:selected').data('type');
                            if (selectedType === 'insurance') {
                                insuranceFields.show();
                                insuranceFields.find('select[name="HasSupplementary"]').attr('required', 'required');
                            } else {
                                insuranceFields.hide();
                                const allFields = insuranceFields.find('input, select');
                                allFields.each(function() {
                                    const $f = $(this);
                                    if ($f.attr('name') !== 'HasSupplementary') {
                                        $f.removeAttr('required');
                                        if (this.tagName === 'INPUT') $f.val('');
                                        else if (this.tagName === 'SELECT') $f.prop('selectedIndex', 0);
                                    }
                                });
                            }
                        };

                        // run once and bind change
                        toggleInsuranceFields();

                        // If no category selected but prefill contains NHIF/insurance data, force show insurance fields
                        try {
                            const hasInsurancePrefill = prefill && (prefill.membership_number || prefill.SchemeID || prefill.ProductCode || prefill.PackageID || prefill.SchemeName || prefill.vote);
                            if ((!patientCategorySelect.val() || patientCategorySelect.val() === '') && hasInsurancePrefill) {
                                insuranceFields.show();
                                insuranceFields.find('select[name="HasSupplementary"]').attr('required', 'required');
                            }
                        } catch (e) {
                            // ignore
                        }

                        patientCategorySelect.off('change.initInsurance').on('change.initInsurance', toggleInsuranceFields);
                    })('#patientCreateModalBodyPatientsIndex');

                    $('#patientCreateModalPatientsIndex').modal('show');
                }).fail(function() {
                    alert('Failed to load patient form');
                });
            } else if (response.success && response.patient_exists === true) {
                // If server provided a redirect URL to the patient view, follow it
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                    return;
                }
                alert('Patient already exists locally.');
            } else {
                alert(response.message || 'Verification failed');
            }
        },
        error: function(xhr) {
            console.error('Quick NHIF error:', xhr);
            const resp = xhr.responseJSON || {};
            alert(resp.error || 'Error verifying NHIF card');
        },
        complete: function() {
            btn.prop('disabled', false).html(orig);
        }
    });
});

// Handle AJAX submit of patient create form inside patients index modal
$(document).on('submit', '#patientCreateModalBodyPatientsIndex form', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('button[type="submit"]').first();
    const orig = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    // debug: log submitted form data so we can inspect what would be sent
    // try {
    //     // If the form contains file inputs, build a FormData snapshot for clearer output
    //     if (form.find('input[type="file"]').length) {
    //         const fd = new FormData(form[0]);
    //         const entries = {};
    //         for (const pair of fd.entries()) {
    //             // File objects will appear as File instances; keep them as-is
    //             entries[pair[0]] = pair[1];
    //         }
    //         console.log('Submitting patient form (FormData):', entries);
    //     } else {
    //         console.log('Submitting patient form (serialized):', form.serialize());
    //     }
    // } catch (e) {
    //     console.log('Error preparing form data for logging:', e);
    // }
    $.ajax({
        url: form.attr('action'),
        method: form.attr('method') || 'POST',
        data: form.serialize(),
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Patient created');
                $('#patientCreateModalPatientsIndex').modal('hide');
                // If server returned a redirect_url, navigate there to show only the created patient
                if (response.redirect_url) {
                    // small delay to let modal hide animation finish
                    setTimeout(function() { window.location.href = response.redirect_url; }, 250);
                    return;
                }
                // Optionally update patients table (left as future work)
            } else {
                toastr.error(response.message || 'Failed to create patient');
            }
        },
        error: function(xhr) {
            const resp = xhr.responseJSON || {};
            toastr.error(resp.message || 'Error creating patient');
        },
        complete: function() {
            btn.prop('disabled', false).html(orig);
        }
    });
});
</script>
@endsection