@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Integration Dashboard')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">NHIF Integration Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">NHIF</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-primary">{{ $stats['total_members'] }}</h3>
                            <p class="text-muted mb-0">Total NHIF Members</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-success">{{ $stats['active_members'] }}</h3>
                            <p class="text-muted mb-0">Active Members</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-info">{{ $stats['total_claims'] }}</h3>
                            <p class="text-muted mb-0">Total Claims</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-medical text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-warning">{{ $stats['submitted_claims'] }}</h3>
                            <p class="text-muted mb-0">Submitted Claims</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NHIF Tools -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Member Verification</h5>
                </div>
                <div class="card-body">
                    <form id="verifyMemberForm">
                        @csrf
                        <div class="mb-3">
                            <label for="card_number" class="form-label">NHIF Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Verify Member
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tariffs Synchronization</h5>
                </div>
                <div class="card-body">
                    <form id="syncTariffsForm">
                        @csrf
                        <div class="mb-3">
                            <label for="facility_code" class="form-label">Facility Code</label>
                            <input type="text" class="form-control" id="facility_code" name="facility_code" 
                                   value="{{ config('nhif.facility_code', '') }}" required>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-sync"></i> Sync Tariffs
                        </button>
                    </form>
                    <div class="mt-3">
                        <small class="text-muted">
                            This will download and sync the latest NHIF tariffs for your facility.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Verifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Card No</th>
                                    <th>Patient</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVerifications as $verification)
                                <tr>
                                    <td>{{ $verification->card_no }}</td>
                                    <td>{{ $verification->patient->full_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $verification->isActive() ? 'success' : 'danger' }}">
                                            {{ $verification->card_status }}
                                        </span>
                                    </td>
                                    <td>{{ $verification->verification_date?->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent verifications</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Claims</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentClaims as $claim)
                                <tr>
                                    <td>{{ $claim->patient->full_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($claim->total_amount_claimed, 0) }} TSH</td>
                                    <td>
                                        <span class="badge bg-{{ $claim->claim_status === 'submitted' ? 'success' : 'warning' }}">
                                            {{ ucfirst($claim->claim_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $claim->submission_date?->format('M d, Y') ?? 'Not submitted' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent claims</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">NHIF Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="responseContent"></div>
            </div>
        </div>
    </div>
</div>
            <!-- Patient Create Modal (AJAX loaded) -->
            <div class="modal fade" id="patientCreateModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create Patient</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="patientCreateModalBody">
                            <div class="text-center p-4">Loading patient form...</div>
                        </div>
                    </div>
                </div>
            </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Member verification form
    $('#verifyMemberForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Verifying...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("nhif.verify-member") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
        console.log('NHIF verify response:', response);
            if (response.success) {
                    if (response.patient_exists === false) {
            console.log('Opening quick-add patient modal with prefill:', response.prefill);
            // If the admin layout Quick Add is available, use it to prefill and open the modal.
            if (window.prefillQuickAdd && typeof window.prefillQuickAdd === 'function') {
                window.prefillQuickAdd(response.prefill || {});
            } else {
                // Fallback to the older patient create modal loader; pass nhif_response as second arg
                openPatientCreateModal(response.prefill || {}, response.nhif_response || null);
            }
                        return;
                    }

                    showResponse('Member Verification Successful', response, 'success');
                    $('#verifyMemberForm')[0].reset();
                } else {
                    showResponse('Member Verification Failed', response, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
        console.log('NHIF verify error response:', response, xhr);
                showResponse('Member Verification Error', response, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Tariffs sync form
    $('#syncTariffsForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Syncing...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("nhif.sync-tariffs") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showResponse('Tariffs Sync Successful', response, 'success');
                } else {
                    showResponse('Tariffs Sync Failed', response, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                showResponse('Tariffs Sync Error', response, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    function showResponse(title, response, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const content = `
            <div class="alert ${alertClass}">
                <strong>${title}</strong>
                <p class="mb-1">${response.message || 'No message provided'}</p>
            </div>
            <pre class="bg-light p-3 mt-3" style="max-height: 300px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
        `;
        
        $('#responseContent').html(content);
        $('#responseModal').modal('show');
    }
    
    // Helper to open patient create form in modal with prefill
    function openPatientCreateModal(prefill, nhifResponse) {
        const modalBody = $('#patientCreateModalBody');
        modalBody.html('<div class="text-center p-4">Loading patient form...</div>');

        $.get('{{ route("patients.create") }}', function(html) {
            console.log('Loaded patient create page HTML (length):', html?.length);
            // Extract the form from returned HTML
            const tmp = $('<div>').html(html);
            console.log('Temp parsed nodes count:', tmp.children().length);
            // Prefer the patients.create form that posts to patients.store to avoid picking layout quick-add
            const storeRoute = '{{ route("patients.store") }}';
            let form = tmp.find('form').filter(function() {
                const a = $(this).attr('action') || '';
                return a.indexOf(storeRoute) !== -1 || a === storeRoute;
            }).first();
            if (!form.length) {
                // fallback: pick first form with first_name excluding quick add
                form = tmp.find('form:has([name="first_name"])').filter(function() {
                    return $(this).attr('id') !== 'patientQuickAddForm';
                }).first();
            }
            if (!form.length) form = tmp.find('form').first();
            console.log('Found forms in page:', tmp.find('form').length);

            // Prefill inputs
            Object.keys(prefill || {}).forEach(function(key) {
                const selector = '[name="' + key + '"]';
                const input = form.find(selector);
                const val = (prefill || {})[key];
                if (input.length && val != null) {
                    input.val(val);
                }
            });

            // debug logging removed

            // If patient_category provided, set it on the form
            if (prefill && prefill.patient_category) {
                form.find('[name="patient_category"]').val(prefill.patient_category);
            }

            // Attach NHIF raw response into the form as a hidden input so the server can consume it
            if (nhifResponse) {
                let hidden = form.find('input[name="nhif_response"]');
                if (!hidden.length) {
                    hidden = $('<input>').attr('type', 'hidden').attr('name', 'nhif_response');
                    form.append(hidden);
                }
                hidden.val(JSON.stringify(nhifResponse));
            }

            // Replace modal body with the form and show modal. If no form found, show full HTML for debugging.
                if (form.length) {
                modalBody.html(form);

                // Scoped toggle logic to show/hide insurance fields inside the modal form
                (function(scope, prefill) {
                    const patientCategorySelect = scope.find('[name="patient_category"]');
                    const insuranceFields = scope.find('#insurance-fields');

                    function toggleInsuranceFields() {
                        const selectedOption = patientCategorySelect.find('option:selected');
                        const categoryType = selectedOption.data('type');
                        if (categoryType === 'insurance') {
                            insuranceFields.show();
                            insuranceFields.find('select[name="HasSupplementary"]').attr('required', 'required');
                        } else {
                            insuranceFields.hide();
                            const allFields = insuranceFields.find('input, select');
                            allFields.each(function() {
                                const field = $(this);
                                if (field.attr('name') !== 'HasSupplementary') {
                                    field.removeAttr('required');
                                    if (field.is('input')) {
                                        field.val('');
                                    } else if (field.is('select')) {
                                        field.prop('selectedIndex', 0);
                                    }
                                }
                            });
                        }
                    }

                    // Bind change and run once to set correct initial state
                    patientCategorySelect.on('change', toggleInsuranceFields);
                    toggleInsuranceFields();

                    // If no category selected but prefill contains NHIF/insurance data, force show insurance fields
                    const hasInsurancePrefill = prefill && (prefill.membership_number || prefill.SchemeID || prefill.ProductCode || prefill.PackageID || prefill.SchemeName || prefill.vote);
                    if ((!patientCategorySelect.val() || patientCategorySelect.val() === '') && hasInsurancePrefill) {
                        insuranceFields.show();
                        insuranceFields.find('select[name="HasSupplementary"]').attr('required', 'required');
                    }
                })(modalBody.find('form').first(), prefill || {});

            } else {
                modalBody.html(html);
            }
            $('#patientCreateModal').modal('show');
        }).fail(function() {
            alert('Failed to load patient form');
        });
    }

        // Handle AJAX submit of patient create form inside NHIF modal
        $(document).on('submit', '#patientCreateModalBody form', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]').first();
            const orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: form.attr('action'),
                method: form.attr('method') || 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Patient created');
                        $('#patientCreateModal').modal('hide');
                        // Optionally refresh patient list or insert new row
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
});
</script>
@endsection
