@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Claims Management')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">NHIF Claims Management</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">NHIF</li>
                        <li class="breadcrumb-item active">Claims Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Claims Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-primary">{{ number_format($stats['total_claims'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Total Claims</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-medical text-primary" style="font-size: 2rem;"></i>
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
                            <h3 class="text-warning">{{ number_format($stats['draft_claims'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Draft Claims</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-edit text-warning" style="font-size: 2rem;"></i>
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
                            <h3 class="text-success">{{ number_format($stats['submitted_claims'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Submitted Claims</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane text-success" style="font-size: 2rem;"></i>
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
                            <h3 class="text-info">{{ number_format($stats['total_amount'] ?? 0, 0) }}</h3>
                            <p class="text-muted mb-0">Total Amount (TSH)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left column: actions -->
        <div class="col-lg-4">

            <!-- Create New Claim -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle text-success me-2"></i>
                        Create New Claim
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="patient_visit_select" class="form-label">Select Patient Visit <span class="text-danger">*</span></label>
                        <select class="form-control" id="patient_visit_select" name="patient_visit_id">
                            <option value="">Choose Patient Visit...</option>
                            @foreach($patientVisits ?? [] as $visit)
                                <option value="{{ $visit->id }}">
                                    {{ $visit->patientInfo->full_name }} - {{ $visit->created_at->format('M d, Y') }}
                                    ({{ $visit->patientInfo->nhifMember->card_no ?? 'No NHIF' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" id="previewClaimBtn">
                        <i class="fas fa-search me-1"></i> Preview &amp; Create Claim
                    </button>
                </div>
            </div>

            <!-- Submit Single Claim -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paper-plane text-primary me-2"></i>
                        Submit Single Claim
                    </h5>
                </div>
                <div class="card-body">
                    <form id="submitClaimForm">
                        @csrf
                        <div class="mb-3">
                            <label for="claim_to_submit" class="form-label">Select Draft Claim <span class="text-danger">*</span></label>
                            <select class="form-control" id="claim_to_submit" name="claim_id" required>
                                <option value="">Choose Draft Claim...</option>
                                @foreach($draftClaims ?? [] as $claim)
                                    <option value="{{ $claim->id }}">
                                        Folio #{{ $claim->folio_no }} — {{ $claim->patient->full_name ?? 'N/A' }}
                                        ({{ number_format($claim->total_amount_claimed, 0) }} TSH)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit to NHIF
                        </button>
                    </form>
                </div>
            </div>

            <!-- Submit Batch -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group text-info me-2"></i>
                        Submit Batch
                    </h5>
                </div>
                <div class="card-body">
                    <form id="submitBatchForm">
                        @csrf
                        <div class="mb-3">
                            <label for="batch_to_submit" class="form-label">Select Batch <span class="text-danger">*</span></label>
                            <select class="form-control" id="batch_to_submit" name="batch_id" required>
                                <option value="">Choose Batch...</option>
                                @foreach($batches ?? [] as $batch)
                                    <option value="{{ $batch->id }}">
                                        {{ $batch->claim_no }}
                                        ({{ $batch->claims_count ?? $batch->claims->count() }} claims — {{ ucfirst($batch->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-layer-group me-1"></i> Submit Batch
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Claims List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">All Claims</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportClaims()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshClaims()">
                            <i class="fas fa-refresh me-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="claimsTable">
                            <thead>
                                <tr>
                                    <th>Folio No</th>
                                    <th>Patient</th>
                                    <th>Card No</th>
                                    <th>Amount (TSH)</th>
                                    <th>Status</th>
                                    <th>Submission Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allClaims as $claim)
                                <tr>
                                    <td><code>{{ $claim->folio_no }}</code></td>
                                    <td>{{ $claim->patient->full_name ?? 'N/A' }}</td>
                                    <td><code>{{ $claim->card_no }}</code></td>
                                    <td class="text-end">{{ number_format($claim->total_amount_claimed, 0) }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft'     => 'warning',
                                                'queued'    => 'info',
                                                'submitted' => 'success',
                                                'pending'   => 'secondary',
                                                'approved'  => 'primary',
                                                'rejected'  => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$claim->claim_status] ?? 'secondary' }}">
                                            {{ ucfirst($claim->claim_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $claim->submission_date?->format('M d, Y H:i') ?? 'Not submitted' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" title="View" onclick="viewClaim('{{ $claim->id }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($claim->claim_status === 'draft')
                                            <button class="btn btn-outline-danger" title="Delete" onclick="deleteClaim('{{ $claim->id }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                            @if($claim->response_data)
                                            <button class="btn btn-outline-success" title="NHIF Response" onclick="viewNhifResponse('{{ $claim->id }}')">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- Claim Preview Modal (shown before creating)                  -->
<!-- ============================================================ -->
<div class="modal fade" id="claimPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Preview Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="claimPreviewContent">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Loading claim preview...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmCreateClaimBtn">
                    <i class="fas fa-check me-1"></i> Confirm &amp; Create Claim
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- Claim Details Modal (view)                                   -->
<!-- ============================================================ -->
<div class="modal fade" id="claimDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Claim Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="claimDetailsContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- Response Modal                                               -->
<!-- ============================================================ -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalTitle">Action Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="responseContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // DataTable
    $('#claimsTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']],
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ],
    });

    // Select2
    $('#patient_visit_select, #claim_to_submit, #batch_to_submit').select2({
        placeholder: 'Search...',
        allowClear: true,
    });

    // ── Preview & Create Claim ─────────────────────────────────────────────
    $('#previewClaimBtn').on('click', function () {
        const visitId = $('#patient_visit_select').val();
        if (!visitId) {
            alert('Please select a patient visit first.');
            return;
        }
        previewClaim(visitId);
    });

    // Confirm & Create
    $('#confirmCreateClaimBtn').on('click', function () {
        const visitId = $(this).data('visit-id');
        if (!visitId) return;

        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Creating...').prop('disabled', true);

        $.ajax({
            url: '{{ route("nhif.create-claim") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                patient_visit_id: visitId,
            },
            success: function (response) {
                $('#claimPreviewModal').modal('hide');
                if (response.success) {
                    showResponse('Claim Created', response, 'success');
                    $('#patient_visit_select').val(null).trigger('change');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showResponse('Claim Creation Failed', response, 'error');
                }
            },
            error: function (xhr) {
                $('#claimPreviewModal').modal('hide');
                showResponse('Claim Creation Error', xhr.responseJSON || {}, 'error');
            },
            complete: function () {
                btn.html('<i class="fas fa-check me-1"></i> Confirm &amp; Create Claim').prop('disabled', false);
            },
        });
    });

    // ── Submit Single Claim ────────────────────────────────────────────────
    $('#submitClaimForm').on('submit', function (e) {
        e.preventDefault();

        const btn = $(this).find('button[type="submit"]');
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...').prop('disabled', true);

        $.ajax({
            url: '{{ route("nhif.submit-claim") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    showResponse('Claim Queued', response, 'success');
                    $('#claim_to_submit').val(null).trigger('change');
                    setTimeout(() => location.reload(), 4000);
                } else {
                    showResponse('Submission Failed', response, 'error');
                }
            },
            error: function (xhr) {
                showResponse('Submission Error', xhr.responseJSON || {}, 'error');
            },
            complete: function () {
                btn.html('<i class="fas fa-paper-plane me-1"></i> Submit to NHIF').prop('disabled', false);
            },
        });
    });

    // ── Submit Batch ───────────────────────────────────────────────────────
    $('#submitBatchForm').on('submit', function (e) {
        e.preventDefault();

        const btn = $(this).find('button[type="submit"]');
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...').prop('disabled', true);

        $.ajax({
            url: '{{ route("nhif.submit-batch") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                showResponse('Batch Queued', response, response.success ? 'success' : 'warning');
                if (response.success) {
                    setTimeout(() => location.reload(), 4000);
                }
            },
            error: function (xhr) {
                showResponse('Batch Submission Error', xhr.responseJSON || {}, 'error');
            },
            complete: function () {
                btn.html('<i class="fas fa-layer-group me-1"></i> Submit Batch').prop('disabled', false);
            },
        });
    });
});

// ── Preview claim (fetch + show modal) ────────────────────────────────────
function previewClaim(visitId) {
    $('#claimPreviewContent').html(
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="text-muted mt-2">Loading...</p></div>'
    );
    $('#confirmCreateClaimBtn').prop('disabled', true).removeData('visit-id');
    $('#claimPreviewModal').modal('show');

    $.get('/nhif/preview-claim/' + visitId, function (data) {
        let diagRows = '';
        if (data.diagnoses && data.diagnoses.length) {
            data.diagnoses.forEach(d => {
                diagRows += `<tr><td><code>${d.code}</code></td><td>${d.status}</td></tr>`;
            });
        } else {
            diagRows = '<tr><td colspan="2" class="text-muted">No diagnoses recorded</td></tr>';
        }

        let itemRows = '';
        let total = 0;
        if (data.items && data.items.length) {
            data.items.forEach(i => {
                total += parseFloat(i.amount) || 0;
                const ref = i.approval_ref ? `<code>${i.approval_ref}</code>` : '<span class="text-warning">None</span>';
                itemRows += `<tr>
                    <td>${i.name}</td>
                    <td class="text-center">${i.qty}</td>
                    <td class="text-end">${Number(i.unit_price).toLocaleString()}</td>
                    <td class="text-end">${Number(i.amount).toLocaleString()}</td>
                    <td>${ref}</td>
                    <td><span class="badge bg-secondary">${i.type}</span></td>
                </tr>`;
            });
        } else {
            itemRows = '<tr><td colspan="6" class="text-muted">No items to claim</td></tr>';
        }

        const html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th style="width:140px">Patient</th><td>${data.patient_name}</td></tr>
                        <tr><th>Card No</th><td><code>${data.card_no}</code></td></tr>
                        <tr><th>Gender</th><td>${data.gender}</td></tr>
                        <tr><th>Date of Birth</th><td>${data.dob ?? 'N/A'}</td></tr>
                        <tr><th>Contact</th><td>${data.contact ?? 'N/A'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th style="width:150px">Doctor</th><td>${data.doctor_name}</td></tr>
                        <tr><th>Attendance Date</th><td>${data.attendance_date}</td></tr>
                        <tr><th>Claim Period</th><td><strong>${data.claim_period}</strong></td></tr>
                        <tr><th>Auth No</th><td>${data.authorization_no ?? '<span class="text-warning">Not set</span>'}</td></tr>
                    </table>
                </div>
            </div>

            <h6 class="mt-2">Diagnoses</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>ICD Code</th><th>Status</th></tr></thead>
                    <tbody>${diagRows}</tbody>
                </table>
            </div>

            <h6>Items Claimed</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Amount</th>
                            <th>Approval Ref</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>${itemRows}</tbody>
                    <tfoot>
                        <tr class="table-info fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end">${Number(data.total_amount).toLocaleString()} TSH</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;

        $('#claimPreviewContent').html(html);
        $('#confirmCreateClaimBtn').prop('disabled', false).data('visit-id', visitId);

    }).fail(function (xhr) {
        const msg = xhr.responseJSON?.message || 'Failed to load claim preview.';
        $('#claimPreviewContent').html(
            `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>${msg}</div>`
        );
    });
}

// ── View stored claim details ──────────────────────────────────────────────
function viewClaim(claimId) {
    $('#claimDetailsContent').html(
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>'
    );
    $('#claimDetailsModal').modal('show');

    $.get('/nhif/claims/' + claimId, function (data) {
        let diagRows = '';
        (data.claim_diseases || []).forEach(d => {
            diagRows += `<tr><td><code>${d.disease_code}</code></td><td>${d.remarks ?? ''}</td></tr>`;
        });

        let itemRows = '';
        (data.claim_items || []).forEach(i => {
            const ref = i.approval_ref_no ? `<code>${i.approval_ref_no}</code>` : '—';
            itemRows += `<tr>
                <td>${i.item_name}</td>
                <td class="text-center">${i.item_quantity}</td>
                <td class="text-end">${Number(i.unit_price).toLocaleString()}</td>
                <td class="text-end">${Number(i.amount_claimed).toLocaleString()}</td>
                <td>${ref}</td>
            </tr>`;
        });

        let feedbackHtml = '';
        if (data.claim_feedback && data.claim_feedback.length) {
            let fbRows = '';
            data.claim_feedback.forEach(f => {
                fbRows += `<tr>
                    <td>${f.submission_no ?? '—'}</td>
                    <td>${f.date_submitted ?? '—'}</td>
                    <td>${f.amount_claimed ? Number(f.amount_claimed).toLocaleString() : '—'}</td>
                    <td>${f.remarks ?? '—'}</td>
                </tr>`;
            });
            feedbackHtml = `
                <h6 class="mt-3 text-success"><i class="fas fa-check-circle me-1"></i>NHIF Feedback</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-success"><tr><th>Submission No</th><th>Date</th><th>Amount</th><th>Remarks</th></tr></thead>
                        <tbody>${fbRows}</tbody>
                    </table>
                </div>`;
        }

        let errorsHtml = '';
        if (data.claim_errors && data.claim_errors.length) {
            let errRows = '';
            data.claim_errors.forEach(e => {
                errRows += `<tr>
                    <td>${e.error_message}</td>
                    <td><span class="badge bg-${e.status === 'resolved' ? 'success' : 'danger'}">${e.status}</span></td>
                    <td>${e.resolution_notes ?? '—'}</td>
                </tr>`;
            });
            errorsHtml = `
                <h6 class="mt-3 text-danger"><i class="fas fa-exclamation-circle me-1"></i>Errors</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-danger"><tr><th>Error</th><th>Status</th><th>Resolution</th></tr></thead>
                        <tbody>${errRows}</tbody>
                    </table>
                </div>`;
        }

        const statusColors = { draft:'warning', queued:'info', submitted:'success', pending:'secondary', approved:'primary', rejected:'danger' };
        const statusColor  = statusColors[data.claim_status] ?? 'secondary';

        const html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th style="width:150px">Folio No</th><td><code>${data.folio_no}</code></td></tr>
                        <tr><th>Patient</th><td>${data.patient?.full_name ?? 'N/A'}</td></tr>
                        <tr><th>Card No</th><td><code>${data.card_no}</code></td></tr>
                        <tr><th>Auth No</th><td>${data.authorization_no ?? 'N/A'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th style="width:150px">Status</th><td><span class="badge bg-${statusColor}">${data.claim_status}</span></td></tr>
                        <tr><th>Claim Period</th><td>${data.claim_month}/${data.claim_year}</td></tr>
                        <tr><th>Total Amount</th><td><strong>${Number(data.total_amount_claimed).toLocaleString()} TSH</strong></td></tr>
                        <tr><th>Submission Date</th><td>${data.submission_date ?? 'Not submitted'}</td></tr>
                    </table>
                </div>
            </div>

            <h6>Diagnoses</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>ICD Code</th><th>Status</th></tr></thead>
                    <tbody>${diagRows || '<tr><td colspan="2" class="text-muted">None recorded</td></tr>'}</tbody>
                </table>
            </div>

            <h6>Items Claimed</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Amount</th><th>Approval Ref</th></tr>
                    </thead>
                    <tbody>${itemRows || '<tr><td colspan="5" class="text-muted">No items</td></tr>'}</tbody>
                    <tfoot>
                        <tr class="table-info fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end">${Number(data.total_amount_claimed).toLocaleString()} TSH</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            ${feedbackHtml}
            ${errorsHtml}`;

        $('#claimDetailsContent').html(html);
    }).fail(function () {
        $('#claimDetailsContent').html('<div class="alert alert-danger">Failed to load claim details.</div>');
    });
}

// ── Delete claim ───────────────────────────────────────────────────────────
function deleteClaim(claimId) {
    if (!confirm('Delete this claim? This cannot be undone.')) return;

    $.ajax({
        url: '/nhif/claims/' + claimId,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (response) {
            showResponse(response.success ? 'Claim Deleted' : 'Delete Failed', response, response.success ? 'success' : 'error');
            if (response.success) setTimeout(() => location.reload(), 1500);
        },
        error: function (xhr) {
            showResponse('Delete Error', xhr.responseJSON || {}, 'error');
        },
    });
}

// ── View NHIF API response for a claim ────────────────────────────────────
function viewNhifResponse(claimId) {
    $.get('/nhif/claims/' + claimId + '/response', function (data) {
        showResponse('NHIF Response', data, 'info');
    }).fail(function (xhr) {
        showResponse('Response Error', xhr.responseJSON || {}, 'error');
    });
}

function exportClaims() {
    window.open('/nhif/export-claims', '_blank');
}

function refreshClaims() {
    location.reload();
}

// ── Generic response modal ─────────────────────────────────────────────────
function showResponse(title, response, type) {
    const alertClass = { success: 'alert-success', error: 'alert-danger', warning: 'alert-warning', info: 'alert-info' }[type] || 'alert-info';

    let warningsHtml = '';
    if (response.warnings && response.warnings.length) {
        const items = response.warnings.map(w => `<li>${w}</li>`).join('');
        warningsHtml = `<div class="alert alert-warning mt-2"><strong>Warnings:</strong><ul class="mb-0 mt-1">${items}</ul></div>`;
    }

    let errorsHtml = '';
    if (response.errors && response.errors.length) {
        const items = response.errors.map(e => `<li>${e}</li>`).join('');
        errorsHtml = `<div class="alert alert-danger mt-2"><strong>Errors:</strong><ul class="mb-0 mt-1">${items}</ul></div>`;
    }

    $('#responseContent').html(`
        <div class="alert ${alertClass}">
            <strong>${title}</strong>
            <p class="mb-0 mt-1">${response.message || 'No message provided'}</p>
        </div>
        ${warningsHtml}${errorsHtml}
        <pre class="bg-light p-3 mt-3" style="max-height: 300px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
    `);
    $('#responseModalTitle').text(title);
    $('#responseModal').modal('show');
}
</script>
@endsection
