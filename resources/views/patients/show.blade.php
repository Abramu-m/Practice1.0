<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patients\show.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Patient Details' }}
 @endsection

@section('Content_Description')
    {{ 'View patient details.' }}
@endsection

@section('main_content')

    <div class="card">
        <div class="card-header">
            <h3>Patient: {{ $patient->full_name }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Personal Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">MR Number:</th>
                            <td><strong class="text-primary">{{ $patient->mr_number }}</strong></td>
                        </tr>
                        <tr>
                            <th width="150">First Name:</th>
                            <td>{{ $patient->first_name }}</td>
                        </tr>
                        <tr>
                            <th>Middle Name:</th>
                            <td>{{ $patient->middle_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Last Name:</th>
                            <td>{{ $patient->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth:</th>
                            <td>{{ $patient->date_of_birth->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td>{{ ucfirst($patient->gender) }}</td>
                        </tr>
                        <tr>
                            <th>Contact:</th>
                            <td>{{ $patient->contact ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Residence:</th>
                            <td>{{ $patient->residence ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Occupation:</th>
                            <td>{{ $patient->occupation ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>NIDA:</th>
                            <td>{{ $patient->nida ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Medical Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Category:</th>
                            <td>{{ $patient->category->description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th width="150">Authorization</th>
                            <td>
                                @if($patient->nhifMember)
                                    @php
                                        $authFacility = $patient->nhifMember->getAuthorizationFacility();
                                        $authDateRaw = $patient->nhifMember->getAuthorizationDate();

                                            $authDateRaw && \Carbon\Carbon::parse($authDateRaw)->isToday() || 
                                            ($authDateRaw && \Carbon\Carbon::parse($authDateRaw)->isFuture());
                                        try {
                                            $authDate = $authDateRaw ? \Carbon\Carbon::parse($authDateRaw)->format('d/m/Y') : null;
                                        } catch (\Exception $e) {
                                            // fallback to raw value if parsing fails
                                            $authDate = $authDateRaw;
                                        }
                                    @endphp
                                    <div><strong>Facility:</strong> {{ $authFacility ?? 'N/A' }}</div>
                                    <div><strong>Date:</strong> {{ $authDate ?? 'N/A' }}</div>
                                    <div>
                                        @php
                                            // prepare data attributes for client-side checks
                                            $cardAttr = $patient->card_number ?? '';
                                            $authFacilityAttr = $authFacility ?? '';
                                            $authDateAttr = $authDateRaw ?? '';
                                        @endphp
                                        <button type="button" id="authorizeBtn" class="btn btn-sm btn-success" title="Authorize patient with NHIF"
                                            data-card="{{ $cardAttr }}"
                                            data-authfacility="{{ $authFacilityAttr }}"
                                            data-authdate="{{ $authDateAttr }}"
                                            @if(empty($cardAttr)) disabled aria-disabled="true" @endif
                                        >
                                            <i class="fas fa-check-circle me-1" aria-hidden="true"></i>
                                            <span class="visually-hidden">Authorize</span>
                                            <span aria-hidden="true">Authorize</span>
                                        </button>
                                    </div>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Card Number:</th>
                            <td>{{ $patient->card_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Membership Number:</th>
                            <td>{{ $patient->membership_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Vote:</th>
                            <td>{{ $patient->vote ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Scheme ID:</th>
                            <td>{{ $patient->SchemeID ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Product Code:</th>
                            <td>{{ $patient->ProductCode ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Package ID:</th>
                            <td>{{ $patient->PackageID ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Scheme Name:</th>
                            <td>{{ $patient->SchemeName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Has Supplementary:</th>
                            <td>{{ $patient->HasSupplementary }}</td>
                        </tr>
                        <tr>
                            <th>Mtuha New:</th>
                            <td>{{ $patient->mtuha_new }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($patient->status == 'active')
                                    <span class="badge badge-success" style="color:black; background-color:#28a745; border:1px solid #28a745;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="color:black; background-color:#dc3545; border:1px solid #dc3545;">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h5>System Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Created By:</th>
                            <td>{{ $patient->creator->first_name ?? 'N/A' }} {{ $patient->creator->last_name ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $patient->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $patient->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Recent Visits Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Recent Visits</h5>
                    @if($patient->visits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Visit Date</th>
                                        <th>Doctor</th>
                                        <th>Visit Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->visits->sortByDesc('visit_date')->take(5) as $visit)
                                        <tr>
                                            <td>{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ optional(optional($visit->doctorInfo)->user)->name ?? 'N/A' }}</td>
                                            <td>{{ $visit->visitType->description ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $visit->visit_status_badge_class }}">
                                                    {{ $visit->visit_status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if(($visit->visit_status == 0 || $visit->visit_status == 1) && 
                                                        (auth()->user()->is_admin || auth()->user()->is_super || 
                                                         (auth()->user()->role === 'doctor' && auth()->user()->doctor && 
                                                          auth()->user()->doctor->doctor_id == $visit->doctor)))
                                                        <a href="{{ route('consultations.show', $visit->id) }}" class="btn btn-success btn-sm" title="{{ $visit->visit_status == 0 ? 'Start Consultation' : 'Continue Consultation' }}">
                                                            <i class="fas fa-user-md"></i> Consult
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('patient_visits.show', $visit->id) }}" class="btn btn-info btn-sm" title="View Visit">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('patient_visits.index', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> View All Visits ({{ $patient->visits->count() }})
                            </a>
                            @if(!$patient->active_visit)
                                <a href="{{ route('patient_visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> Create New Visit
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No visits found for this patient.
                            <a href="{{ route('patient_visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-success btn-sm ml-2">
                                <i class="fas fa-plus"></i> Create First Visit
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-primary">Edit Patient</a>
            @if(!$patient->active_visit)
                <a href="{{ route('patient_visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-success">Add Visit</a>
            @else
                <span class="btn btn-warning disabled" title="Patient has an active visit - complete current visit before creating new one">
                    <i class="fas fa-exclamation-triangle"></i> Active Visit In Progress
                </span>
            @endif
            <a href="{{ route('patients.index') }}" class="btn btn-secondary">Back to List</a>
            @if(auth()->user()->isAdmin())
            <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" style="display:inline;" class="float-right">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Are you sure you want to delete this patient?')" class="btn btn-danger">Delete Patient</button>
            </form>
            @endif
        </div>
    </div>
@endsection

@section('extra_footer_content')

<!-- NHIF Authorize Modal -->
<div class="modal fade" id="authorizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Authorize NHIF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="authorizeForm">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">

                    <div class="mb-3">
                        <label for="auth_card_number" class="form-label">NHIF Card Number</label>
                        <input type="text" class="form-control" id="auth_card_number" name="card_number" value="{{ $patient->card_number ?? '' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="auth_visit_type" class="form-label">Visit Type</label>
                        <select id="auth_visit_type" name="visit_type_id" class="form-control">
                            <option value="1">Normal Visit</option>
                            <option value="2">Emergency</option>
                            <option value="3">Referral</option>
                            <option value="4">Follow up Visit</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="auth_referral" class="form-label">Referral Number (optional)</label>
                        <input type="text" id="auth_referral" name="referral_number" class="form-control" placeholder="Referral number">
                    </div>

                    <div class="mb-3">
                        <label for="auth_remarks" class="form-label">Remarks</label>
                        <textarea id="auth_remarks" name="remarks" class="form-control" rows="3">Authorization request</textarea>
                    </div>

                    <div id="authorizeResponse" class="mt-2"></div>
                    <div id="authorizeAlert" class="mt-2"></div>
                    <div id="authorizeOverride" class="mt-2 d-none">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="authorizeOverrideCheck" name="override_emergency" value="1">
                            <label class="form-check-label" for="authorizeOverrideCheck">
                                I confirm an emergency authorization is required despite an earlier authorization today at another facility.
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="authorizeSubmitBtn">
                    <i class="fas fa-spinner fa-spin d-none me-1" aria-hidden="true"></i>
                    Authorize
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(function(){
    // Open modal and prefill values when authorize button clicked
    $('#authorizeBtn').on('click', function(e){
        e.preventDefault();

        var btn = $(this);
        var card = btn.data('card') || '';
        var existingFacility = btn.data('authfacility') || '';
        var existingDate = btn.data('authdate') || '';

        // If no card number, prevent opening and show inline alert
        if (!card) {
            var noCard = '<div class="alert alert-warning"><strong>No NHIF card</strong><p>This patient has no NHIF card number set. Please add a card number before authorizing.</p></div>';
            $('#authorizeAlert').html(noCard);
            // small visual feedback: briefly show then clear
            setTimeout(function(){ $('#authorizeAlert').html(''); }, 5000);
            return;
        }

        // Prefill fields from blade variables (server-side values)
        $('#auth_card_number').val(card);
        $('#auth_referral').val('');
        $('#auth_remarks').val('Authorization request');
        $('#authorizeResponse').html('');
        $('#authorizeAlert').html('');

        // If there is an existing authorization date and facility, and the date is today,
        // restrict to emergency only (per requirement) unless facility matches this site.
    if (existingDate) {
            try {
                var parsed = new Date(existingDate);
                var today = new Date();
                if (parsed.getFullYear() === today.getFullYear() && parsed.getMonth() === today.getMonth() && parsed.getDate() === today.getDate()) {
                    // If facility provided and seems different, force/emphasize emergency
                    if (existingFacility && existingFacility.trim().length > 0) {
            // Set visit type to 2 (Emergency) and disable other options
            $('#auth_visit_type').val('2');
            $('#auth_visit_type option').prop('disabled', true);
            $('#auth_visit_type option[value="2"]').prop('disabled', false);

                        var warn = '<div class="alert alert-warning"><strong>Existing authorization today</strong><p>Patient was authorized at another facility today (<em>' + existingFacility + '</em>). Only Emergency authorizations are allowed in this case.</p></div>';
                        $('#authorizeAlert').html(warn);
                        // show override checkbox and require user confirmation before submit
                        $('#authorizeOverride').removeClass('d-none');
                        $('#authorizeOverrideCheck').prop('checked', false);
                        $('#authorizeSubmitBtn').prop('disabled', true);
                    }
                } else {
                    // ensure selection enabled
                    $('#auth_visit_type option').prop('disabled', false);
                }
            } catch (e) {
                // ignore parse errors and proceed
                $('#auth_visit_type option').prop('disabled', false);
            }
        } else {
            $('#auth_visit_type option').prop('disabled', false);
            $('#auth_visit_type').val('1');
            // hide override checkbox when not applicable
            $('#authorizeOverride').addClass('d-none');
            $('#authorizeOverrideCheck').prop('checked', false);
            $('#authorizeSubmitBtn').prop('disabled', false);
        }

        // show modal
        var modalEl = new bootstrap.Modal(document.getElementById('authorizeModal'));
        modalEl.show();
    });

    // Submit authorization via AJAX to existing NHIF verify endpoint
    $('#authorizeSubmitBtn').on('click', function(e){
        e.preventDefault();
        var btn = $(this);
        var spinner = btn.find('i.fas');
        spinner.removeClass('d-none');
        btn.prop('disabled', true);

        // Client-side guard: ensure card is present
        var cardVal = $('#auth_card_number').val() || '';
        if (!cardVal) {
            $('#authorizeResponse').html('<div class="alert alert-warning">No card number set. Cannot authorize.</div>');
            spinner.addClass('d-none');
            btn.prop('disabled', false);
            return;
        }

        // If visit_type select has other options disabled (emergency-only), ensure selected value is 3
        var visitType = $('#auth_visit_type').val();
        var onlyEmergency = $('#auth_visit_type option').length > 0 && $('#auth_visit_type option').filter(':disabled').length >= 2 && $('#auth_visit_type option[value="2"]').is(':enabled');
        if (onlyEmergency && visitType !== '2') {
            $('#authorizeResponse').html('<div class="alert alert-danger">Only Emergency authorizations are allowed when patient has same-day authorization at another facility.</div>');
            spinner.addClass('d-none');
            btn.prop('disabled', false);
            return;
        }

        // If override checkbox is visible, it must be checked to proceed
        if (!$('#authorizeOverride').hasClass('d-none')) {
            if (!$('#authorizeOverrideCheck').is(':checked')) {
                $('#authorizeResponse').html('<div class="alert alert-warning">Please confirm the emergency override before proceeding.</div>');
                spinner.addClass('d-none');
                btn.prop('disabled', false);
                return;
            }
        }

        $.ajax({
            url: '{{ route('nhif.authorize') }}',
            type: 'POST',
            data: $('#authorizeForm').serialize(),
            success: function(response) {
                // Show response inside modal
                var content = '';
                if (response.success) {
                    content += '<div class="alert alert-success"><strong>' + (response.message || 'Authorized') + '</strong></div>';
                    // Render pretty JSON for debugging
                    content += '<pre class="bg-light p-2" style="max-height:300px;overflow:auto;">' + JSON.stringify(response, null, 2) + '</pre>';
                    $('#authorizeResponse').html(content);

                    // close modal briefly then reload or redirect to updated patient
                    setTimeout(function(){
                        try { bootstrap.Modal.getInstance(document.getElementById('authorizeModal')).hide(); } catch (e) {}
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            // reload to show updated NHIF fields on patient page
                            location.reload();
                        }
                    }, 900);
                } else {
                    content += '<div class="alert alert-danger"><strong>' + (response.message || 'Authorization failed') + '</strong></div>';
                    content += '<pre class="bg-light p-2" style="max-height:300px;overflow:auto;">' + JSON.stringify(response, null, 2) + '</pre>';
                    $('#authorizeResponse').html(content);
                }
            },
            error: function(xhr) {
                var r = xhr.responseJSON || {};
                var err = '<div class="alert alert-danger"><strong>Authorization error</strong><p>' + (r.message || 'Unable to contact server') + '</p></div>';
                err += '<pre class="bg-light p-2" style="max-height:300px;overflow:auto;">' + JSON.stringify(r, null, 2) + '</pre>';
                $('#authorizeResponse').html(err);
            },
            complete: function() {
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });
    
    // UX: enable submit when override checkbox is checked
    $('#authorizeOverrideCheck').on('change', function(){
        if ($(this).is(':checked')) {
            $('#authorizeSubmitBtn').prop('disabled', false);
        } else {
            // if override hidden, keep enabled; if visible and unchecked, disable
            if (!$('#authorizeOverride').hasClass('d-none')) {
                $('#authorizeSubmitBtn').prop('disabled', true);
            }
        }
    });
});
</script>
@endsection

@endsection