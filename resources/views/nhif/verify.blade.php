@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Member Verification')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">NHIF Member Verification</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('nhif.index') }}">NHIF</a></li>
                        <li class="breadcrumb-item active">Member Verification</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Member Verification Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-check text-success me-2"></i>
                        Verify NHIF Member
                    </h5>
                </div>
                <div class="card-body">
                    <form id="verifyMemberForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">NHIF Card Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" 
                                           placeholder="Enter NHIF card number" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patient_select" class="form-label">Select Patient <span class="text-danger">*</span></label>
                                    <select class="form-control" id="patient_select" name="patient_id" required>
                                        <option value="">Choose Patient...</option>
                                        @foreach($patients ?? [] as $patient)
                                            <option value="{{ $patient->id }}">{{ $patient->full_name }} - {{ $patient->contact }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="visit_type_id" class="form-label">Visit Type</label>
                                    <select class="form-control" id="visit_type_id" name="visit_type_id">
                                        <option value="1">Outpatient</option>
                                        <option value="2">Inpatient</option>
                                        <option value="3">Emergency</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="referral_number" class="form-label">Referral Number (Optional)</label>
                                    <input type="text" class="form-control" id="referral_number" name="referral_number" 
                                           placeholder="Enter referral number if applicable">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                                      placeholder="Enter any additional remarks">Member verification for treatment</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Verify Member
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="fas fa-refresh me-1"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Verification Guide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">How to Verify:</h6>
                        <ol class="mb-0">
                            <li>Enter the NHIF card number</li>
                            <li>Select the patient from the dropdown</li>
                            <li>Choose appropriate visit type</li>
                            <li>Add referral number if available</li>
                            <li>Click "Verify Member"</li>
                        </ol>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Note:</h6>
                        <p class="mb-0">Verification requires active internet connection and valid NHIF credentials.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Today's Verifications</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-success">{{ $todayTotal ?? 0 }}</h3>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-primary">{{ $todayActive ?? 0 }}</h3>
                            <p class="text-muted mb-0">Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Verifications -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Verifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Card No</th>
                                    <th>Patient Name</th>
                                    <th>Full Name (NHIF)</th>
                                    <th>Status</th>
                                    <th>Authorization</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVerifications ?? [] as $member)
                                <tr>
                                    <td><code>{{ $member->card_no }}</code></td>
                                    <td>{{ $member->patient->full_name ?? 'N/A' }}</td>
                                    <td>{{ $member->full_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $member->isActive() ? 'success' : 'danger' }}">
                                            {{ $member->card_status }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $member->authorization_status === 'ACCEPTED' ? 'success' : 'warning' }}">
                                            {{ $member->authorization_status }}
                                        </span>
                                    </td>
                                    <td>{{ $member->verification_date?->format('M d, Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewDetails('{{ $member->id }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No verifications found</td>
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

<!-- Member Details Modal -->
<div class="modal fade" id="memberDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">NHIF Member Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="memberDetailsContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verification Response</h5>
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
    // Initialize Select2 for patient selection
    $('#patient_select').select2({
        placeholder: 'Search for a patient...',
        allowClear: true
    });

    // Member verification form
    $('#verifyMemberForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Verifying...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("nhif.verify-member") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showResponse('Member Verification Successful', response, 'success');
                    $('#verifyMemberForm')[0].reset();
                    $('#patient_select').val(null).trigger('change');
                    
                    // Refresh the page after 3 seconds to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    showResponse('Member Verification Failed', response, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                showResponse('Member Verification Error', response, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

function clearForm() {
    $('#verifyMemberForm')[0].reset();
    $('#patient_select').val(null).trigger('change');
}

function viewDetails(memberId) {
    // You can implement this to show detailed member information
    $('#memberDetailsModal').modal('show');
}

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
</script>
@endsection
