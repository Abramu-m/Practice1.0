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
                        <li class="breadcrumb-item"><a href="{{ route('nhif.index') }}">NHIF</a></li>
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
        <!-- Claims Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle text-success me-2"></i>
                        Create New Claim
                    </h5>
                </div>
                <div class="card-body">
                    <form id="createClaimForm">
                        @csrf
                        <div class="mb-3">
                            <label for="patient_visit_select" class="form-label">Select Patient Visit <span class="text-danger">*</span></label>
                            <select class="form-control" id="patient_visit_select" name="patient_visit_id" required>
                                <option value="">Choose Patient Visit...</option>
                                @foreach($patientVisits ?? [] as $visit)
                                    <option value="{{ $visit->id }}">
                                        {{ $visit->patientInfo->full_name }} - {{ $visit->created_at->format('M d, Y') }}
                                        ({{ $visit->patient->nhifMember->card_no ?? 'No NHIF' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="claim_month" class="form-label">Claim Month</label>
                            <select class="form-control" id="claim_month" name="claim_month">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="claim_year" class="form-label">Claim Year</label>
                            <select class="form-control" id="claim_year" name="claim_year">
                                @for($year = date('Y'); $year >= date('Y') - 2; $year--)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Create Claim
                        </button>
                    </form>
                </div>
            </div>

            <!-- Submit Claim -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paper-plane text-primary me-2"></i>
                        Submit Claim to NHIF
                    </h5>
                </div>
                <div class="card-body">
                    <form id="submitClaimForm">
                        @csrf
                        <div class="mb-3">
                            <label for="claim_to_submit" class="form-label">Select Draft Claim <span class="text-danger">*</span></label>
                            <select class="form-control" id="claim_to_submit" name="patient_visit_id" required>
                                <option value="">Choose Draft Claim...</option>
                                @foreach($draftClaims ?? [] as $claim)
                                    <option value="{{ $claim->patient_visit_id }}">
                                        Folio #{{ $claim->folio_no }} - {{ $claim->patient->full_name }}
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
                                                'draft' => 'warning',
                                                'submitted' => 'success',
                                                'approved' => 'primary',
                                                'rejected' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$claim->claim_status] ?? 'secondary' }}">
                                            {{ ucfirst($claim->claim_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $claim->submission_date?->format('M d, Y H:i') ?? 'Not submitted' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" onclick="viewClaim('{{ $claim->id }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($claim->claim_status === 'draft')
                                            <button class="btn btn-outline-primary" onclick="editClaim('{{ $claim->id }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @endif
                                            @if($claim->response_data)
                                            <button class="btn btn-outline-success" onclick="viewResponse('{{ $claim->id }}')">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- Pagination links provided by controller --}}
                        <div class="mt-2">
                            {{ $allClaims->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Claim Details Modal -->
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

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Action Response</h5>
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
$(document).ready(function() {
    // Initialize DataTable
    $('#claimsTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']],
        responsive: true
    });

    // Initialize Select2
    $('#patient_visit_select, #claim_to_submit').select2({
        placeholder: 'Search...',
        allowClear: true
    });

    // Create claim form
    $('#createClaimForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Creating...').prop('disabled', true);
        
        $.ajax({
            url: '/nhif/create-claim',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showResponse('Claim Created Successfully', response, 'success');
                    $('#createClaimForm')[0].reset();
                    $('#patient_visit_select').val(null).trigger('change');
                    
                    // Refresh page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showResponse('Claim Creation Failed', response, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                showResponse('Claim Creation Error', response, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Submit claim form
    $('#submitClaimForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("nhif.submit-claim") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showResponse('Claim Submitted Successfully', response, 'success');
                    $('#submitClaimForm')[0].reset();
                    $('#claim_to_submit').val(null).trigger('change');
                    
                    // Refresh page after 3 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    showResponse('Claim Submission Failed', response, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                showResponse('Claim Submission Error', response, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

function viewClaim(claimId) {
    // Load claim details and show in modal
    $('#claimDetailsModal').modal('show');
}

function editClaim(claimId) {
    // Redirect to edit page or show edit form
    window.location.href = '/nhif/claims/' + claimId + '/edit';
}

function viewResponse(claimId) {
    // Show NHIF response for the claim
    $.get('/nhif/claims/' + claimId + '/response', function(data) {
        showResponse('NHIF Response', data, 'info');
    });
}

function exportClaims() {
    window.open('/nhif/export-claims', '_blank');
}

function refreshClaims() {
    location.reload();
}

function showResponse(title, response, type) {
    const alertColors = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'info': 'alert-info'
    };
    
    const alertClass = alertColors[type] || 'alert-info';
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
</script>
@endsection
