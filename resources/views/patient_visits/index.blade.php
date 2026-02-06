@extends('layouts.app_main_layout')

@section('page_title')
    {{ isset($selectedPatient) ? 'Visits for ' . $selectedPatient->full_name : (isset($selectedDoctor) ? 'Visits by Dr. ' . $selectedDoctor->user->name : 'Patient Visits') }}
 @endsection

@section('Content_Description')
    {{ isset($selectedPatient) ? 'Manage visits for patient: ' . $selectedPatient->full_name : (isset($selectedDoctor) ? 'Manage visits by Dr. ' . $selectedDoctor->user->name : 'Manage patient visits and appointments.') }}
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
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('patient_visits.index') }}" class="mb-3">
                        @if(isset($selectedPatient))
                            <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                        @elseif(isset($selectedDoctor))
                            <input type="hidden" name="doctor_id" value="{{ $selectedDoctor->id }}">
                        @endif
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" placeholder="Search by patient name, MR number, SIC, authorization, or NHIF reference number..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('patient_visits.index') }}{{ isset($selectedPatient) ? '?patient_id=' . $selectedPatient->id : (isset($selectedDoctor) ? '?doctor_id=' . $selectedDoctor->id : '') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-list"></i> All Visits
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
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
                                    <th>Cash Amount</th>
                                    <th>Covered Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visits as $visit)
                                    <tr>
                                        <td>{{ $visit->id }}</td>
                                        @if(!isset($selectedPatient))
                                            <td>{{ $visit->patientInfo->full_name ?? 'Unknown' }}</td>
                                        @endif
                                        <td>{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $visit->visitCategory->description ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $visitTypeDesc = $visit->visitType->description ?? 'N/A';
                                                $badgeClass = 'badge-secondary'; // Default
                                                
                                                // Color coding based on visit type
                                                switch(strtolower($visitTypeDesc)) {
                                                    case 'first visit':
                                                        $badgeClass = 'badge-primary';
                                                        break;
                                                    case 'follow up':
                                                        $badgeClass = 'badge-success';
                                                        break;
                                                    case 'internal referral':
                                                        $badgeClass = 'badge-warning';
                                                        break;
                                                    case 'external referral':
                                                        $badgeClass = 'badge-info';
                                                        break;
                                                    case 'lab only':
                                                        $badgeClass = 'badge-danger';
                                                        break;
                                                }
                                                
                                                // Check if patient has active visit using model method
                                                $hasActiveVisit = false;
                                                $activeVisitId = null;
                                                if (!isset($selectedPatient)) {
                                                    $activeVisit = $visit->patientInfo->active_visit ?? null;
                                                    $hasActiveVisit = $activeVisit && $activeVisit->id == $visit->id;
                                                    $activeVisitId = $hasActiveVisit ? $visit->id : null;
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $visitTypeDesc }}</span>
                                        </td>
                                        @if(!isset($selectedDoctor))
                                            <td>{{ optional(optional($visit->doctorInfo)->user)->name ?? 'N/A' }}</td>
                                        @endif
                                        <td>${{ number_format($visit->amount_cash, 2) }}</td>
                                        <td>${{ number_format($visit->amount_covered ?? 0, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $visit->visit_status_badge_class }} text-black">{{ $visit->visit_status_label }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Lab Button for patients with active visits (only show in general view, not when filtering by patient) -->
                                                @if(!isset($selectedPatient) && $hasActiveVisit)
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="openLabModal({{ $visit->patient }}, {{ $activeVisitId }}, '{{ addslashes($visit->patientInfo->full_name ?? 'Unknown') }}')"
                                                            title="Add Lab Investigation">
                                                        <i class="fas fa-plus"></i> Lab
                                                    </button>
                                                @endif
                                                
                                                <!-- Consult Button - Most prominent (hide for lab-only visits) -->
                                                @if($visit->visitType && stripos($visit->visitType->description, 'lab only') === false && 
                                                    ($visit->visit_status == 0 || $visit->visit_status == 1) && 
                                                    (auth()->user()->is_admin || auth()->user()->is_super || 
                                                     (auth()->user()->role === 'doctor' && auth()->user()->doctor && 
                                                      auth()->user()->doctor->doctor_id == $visit->doctor)))
                                                    <a href="{{ route('consultations.show', $visit->id) }}" class="btn btn-sm btn-success" title="{{ $visit->visit_status == 0 ? 'Start Consultation' : 'Continue Consultation' }}">
                                                        <i class="fas fa-user-md"></i> Consult
                                                    </a>
                                                @elseif($visit->visitType && stripos($visit->visitType->description, 'lab only') === false && 
                                                        ($visit->visit_status == 0 || $visit->visit_status == 1) && auth()->user()->role === 'doctor')
                                                    <span class="btn btn-sm btn-secondary disabled" title="Patient not assigned to you">
                                                        <i class="fas fa-lock"></i> Not Assigned
                                                    </span>
                                                @endif
                                                
                                                <!-- Other action buttons -->
                                                <a href="{{ route('patient_visits.show', $visit->id) }}" class="btn btn-sm btn-info" title="View Visit">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('patient_visits.edit', $visit->id) }}" class="btn btn-sm btn-warning" title="Edit Visit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(auth()->user()->isAdmin())
                                                <form action="{{ route('patient_visits.destroy', $visit->id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete this visit?')" class="btn btn-sm btn-danger" title="Delete Visit">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ isset($selectedPatient) && isset($selectedDoctor) ? '7' : (isset($selectedPatient) || isset($selectedDoctor) ? '8' : '9') }}" class="text-center">
                                            @if(isset($selectedPatient))
                                                No visits found for this patient.
                                            @elseif(isset($selectedDoctor))
                                                No visits found for this doctor.
                                            @else
                                                No visits found.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $visits->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lab Investigation Modal -->
<div class="modal fade" id="labInvestigationModal" tabindex="-1" aria-labelledby="labInvestigationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labInvestigationModalLabel">
                    <i class="fas fa-flask"></i> Add Lab Investigation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="labInvestigationForm">
                    @csrf
                    <input type="hidden" id="modal_patient_id" name="patient_id">
                    <input type="hidden" id="modal_visit_id" name="visit_id">
                    <input type="hidden" id="modal_patient_category_id" name="patient_category_id">
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>Patient:</strong> <span id="modal_patient_name"></span><br>
                                <small class="text-muted">Adding lab investigation to visit</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Investigations Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list"></i> Current Investigations for this Visit
                                        <span class="badge bg-secondary ms-2" id="investigations_count">0</span>
                                    </h6>
                                </div>
                                <div class="card-body py-2" id="current_investigations_section">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Loading investigations...
                                    </div>
                                </div>
                            </div>
                        </div>
                                    <!-- Complex Results Modal (reused from consultations) -->
                                    <div class="modal fade" id="complexResultsModal" tabindex="-1" role="dialog" aria-labelledby="complexResultsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="complexResultsModalLabel">
                                                        <i class="fas fa-chart-line"></i> Investigation Results
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body" id="complexResultsContent" style="max-height: 70vh; overflow-y: auto;">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="spinner-border" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <a href="#" id="printComplexResult" class="btn btn-primary">
                                                        <i class="fas fa-print"></i> Print Results
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Search Medical Service *</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="modal_service_search" 
                                           placeholder="Type to search for lab services..." autocomplete="off">
                                    <div id="modal_service_suggestions" class="suggestions-dropdown">
                                        <!-- Service suggestions will be populated here -->
                                    </div>
                                </div>
                                <input type="hidden" name="medical_service_id" id="modal_selected_service_id" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="quantity" value="1" min="1" max="10" id="modal_investigation_quantity">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-control" name="priority">
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Clinical Notes</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Clinical indication for investigation..."></textarea>
                    </div>
                    
                    <div id="modal-service-info" class="alert alert-info" style="display: none;"></div>
                    <!-- Form type info and form display (populated when a service requires a form) -->
                    <div id="form-type-info-container" style="display: none;" class="mt-3"></div>
                    <div id="form-display-container" style="display: none;" class="mt-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="saveLabInvestigation()">
                    <i class="fas fa-save"></i> Order Investigation
                </button>
            </div>
        </div>
    </div>
</div>
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

// Open lab investigation modal
function openLabModal(patientId, visitId, patientName) {
    console.log('openLabModal called', { patientId: patientId, visitId: visitId, patientName: patientName });
    $('#modal_patient_id').val(patientId);
    $('#modal_visit_id').val(visitId);
    $('#modal_patient_name').text(patientName);
    
    // Get patient category from the visit - we'll need to make an AJAX call for this
    $.ajax({
        url: `/patient-visits/${visitId}/category`,
        method: 'GET',
        success: function(response) {
            $('#modal_patient_category_id').val(response.category_id);
        },
        error: function(xhr) {
            console.error('Failed to get patient category:', xhr);
            $('#modal_patient_category_id').val('1'); // Default fallback
        }
    });
    
    // Reset form
    $('#labInvestigationForm')[0].reset();
    $('#modal_service_search').val('');
    $('#modal_selected_service_id').val('');
    $('#modal-service-info').hide();
    hideModalServiceSuggestions();
    
    // Load existing investigations for this visit
    loadExistingInvestigations(visitId);
    
    // Show modal
    $('#labInvestigationModal').modal('show');

    // If the modal was opened with a pre-selected service (or the search input has stored
    // form metadata), automatically show the required form. This preserves the default
    // behaviour (no form shown) but helps cases where the UI re-opens with a selection.
    try {
        const preServiceId = $('#modal_selected_service_id').val();
        const preRequires = $('#modal_service_search').data('requires-form') || $('#modal_selected_service_id').data('requires-form');
        const preFormType = $('#modal_service_search').data('form-type') || $('#modal_selected_service_id').data('form-type');
        const prePrice = $('#modal_service_search').data('price') || $('#modal_selected_service_id').data('service-price') || 0;
        const preCategory = $('#modal_service_search').data('category') || '';
        const preHasPricing = $('#modal_service_search').data('has-pricing') || false;

        if ((preRequires === true || preRequires === 'true') && preFormType) {
            // Populate the service info area and load the form preview
            showModalServiceInfo(preServiceId || null, $('#modal_service_search').val() || '', prePrice, preCategory, preHasPricing, true, preFormType);
        }
    } catch (e) {
        console.error('Error auto-loading preselected service form:', e);
    }
}

// Load existing investigations for a visit
function loadExistingInvestigations(visitId) {
    // Show loading state
    $('#current_investigations_section').html(`
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin"></i> Loading investigations...
        </div>
    `);
    $('#investigations_count').text('0');
    
    // Make AJAX call to get investigations
    $.ajax({
        url: `/patient-visits/${visitId}/investigations-partial`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#current_investigations_section').html(response.html);
                $('#investigations_count').text(response.count);
            } else {
                $('#current_investigations_section').html(`
                    <div class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-triangle"></i> Failed to load investigations
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Failed to load investigations:', xhr);
            $('#current_investigations_section').html(`
                <div class="text-center text-danger py-3">
                    <i class="fas fa-exclamation-triangle"></i> Error loading investigations
                </div>
            `);
        }
    });
}

// Delete an investigation
function deleteInvestigation(investigationId) {
    if (!confirm('Are you sure you want to delete this investigation? This action cannot be undone.')) {
        return;
    }
    
    // Show loading state on the button
    const button = $(`button[onclick="deleteInvestigation(${investigationId})"]`);
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: `/investigations/${investigationId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Investigation deleted successfully!');
                
                // Refresh the investigations list to show updated status
                const visitId = $('#modal_visit_id').val();
                if (visitId) {
                    loadExistingInvestigations(visitId);
                }
            } else {
                toastr.error(response.message || 'Failed to delete investigation.');
                // Restore button
                button.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            console.error('Investigation deletion error:', xhr);
            let errorMessage = 'Failed to delete investigation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            toastr.error(errorMessage);
            
            // Restore button
            button.prop('disabled', false).html(originalHtml);
        }
    });
}

// Cancel an investigation (set status to cancelled)
function cancelInvestigation(investigationId) {
    if (!confirm('Are you sure you want to cancel this investigation? This will set its status to cancelled.')) {
        return;
    }
    
    // Show loading state on the button
    const button = $(`button[onclick="cancelInvestigation(${investigationId})"]`);
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: `/investigations/${investigationId}/cancel`,
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Investigation cancelled successfully!');
                
                // Refresh the investigations list to show updated status
                const visitId = $('#modal_visit_id').val();
                if (visitId) {
                    loadExistingInvestigations(visitId);
                }
            } else {
                toastr.error(response.message || 'Failed to cancel investigation.');
                // Restore button
                button.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            console.error('Investigation cancellation error:', xhr);
            let errorMessage = 'Failed to cancel investigation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            toastr.error(errorMessage);
            
            // Restore button
            button.prop('disabled', false).html(originalHtml);
        }
    });
}

// Initialize medical service search for modal
$(document).ready(function() {
    $('#modal_service_search').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchModalMedicalServices(query);
        } else {
            hideModalServiceSuggestions();
        }
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#modal_service_search').length && 
            !$(e.target).closest('#modal_service_suggestions').length) {
            hideModalServiceSuggestions();
        }
    });
    
    // Quantity is always 1 for investigations in this modal; no dynamic total updates required.
    // Ensure the quantity field stays at 1 to avoid accidental changes.
    $('#modal_investigation_quantity').val(1).attr('min', 1).attr('max', 1).prop('readonly', true);
});

// Search medical services for modal
function searchModalMedicalServices(query) {
    const patientCategoryId = $('#modal_patient_category_id').val() || '1';
    
    $.ajax({
        url: '/api/medical-services/search',
        method: 'GET',
        data: { 
            query: query, 
            limit: 10,
            patient_category_id: patientCategoryId
            // lab_only: true // Filter for lab services only
        },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                showModalServiceSuggestions(response.data);
            } else {
                hideModalServiceSuggestions();
            }
        },
        error: function(xhr) {
            console.error('Medical service search error:', xhr);
            hideModalServiceSuggestions();
        }
    });
}

// Show service suggestions
function showModalServiceSuggestions(services) {
    const container = $('#modal_service_suggestions');
    
    let html = '';
    
    services.forEach(function(service) {
        const price = service.price || 0;
        const categoryName = service.category || 'General';
        const hasPricing = service.has_pricing || false;
        const formattedPrice = parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        html += `
            <div class="service-suggestion-item p-3 border-bottom bg-white" 
                 data-service-id="${service.id}" 
                 data-service-name="${service.name}"
                 data-service-code="${service.code || ''}"
                 data-service-price="${price}"
                 data-service-category="${categoryName}"
                 data-has-pricing="${hasPricing ? 'true' : 'false'}"
                 data-requires-form="${service.requires_form ? 'true' : 'false'}"
                 data-form-type="${service.form_type || ''}"
                 style="border: 1px solid #dee2e6; margin-bottom: 1px;">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${service.name}</strong>
                        ${service.code ? `<small class="text-muted d-block">(${service.code})</small>` : ''}
                        <small class="text-muted">${categoryName}</small>
                        ${service.requires_form ? `<br><small class="text-warning"><i class="fas fa-file-alt"></i> Requires form: ${service.form_type || 'Form'}</small>` : ''}
                    </div>
                    <div class="text-end">
                        <strong class="text-primary">TSh ${formattedPrice}</strong>
                        ${!hasPricing ? '<br><small class="text-warning">No pricing</small>' : ''}
                    </div>
                 </div>
            </div>
        `;
    });
    
    container.html(html);
    
    // Add click handlers
            container.find('.service-suggestion-item').on('click', function() {
                console.log('modal service suggestion clicked', {
                    id: $(this).data('service-id'),
                    name: $(this).data('service-name'),
                    requiresForm: $(this).data('requires-form'),
                    formType: $(this).data('form-type')
                });
            const serviceId = $(this).data('service-id');
            const serviceName = $(this).data('service-name');
            const serviceCode = $(this).data('service-code');
            const servicePrice = $(this).data('service-price');
            const serviceCategory = $(this).data('service-category');
            const hasPricing = $(this).data('has-pricing');
            const requiresForm = $(this).data('requires-form');
            const formType = $(this).data('form-type') || null;

            // Set selected service
            $('#modal_service_search').val(serviceName + (serviceCode ? ` (${serviceCode})` : ''));
            $('#modal_selected_service_id').val(serviceId);

            // Store useful metadata on the search input so other helpers can read them
            $('#modal_service_search').data('price', servicePrice);
            $('#modal_service_search').data('category', serviceCategory);
            $('#modal_service_search').data('has-pricing', hasPricing);
            $('#modal_service_search').data('requires-form', requiresForm ? 'true' : 'false');
            $('#modal_service_search').data('form-type', formType || '');

            // Show service info and form preview if required
            showModalServiceInfo(serviceId, serviceName, servicePrice, serviceCategory, hasPricing, requiresForm, formType);

            // Hide suggestions
            hideModalServiceSuggestions();
        });
    
    // Add hover effects
    container.find('.service-suggestion-item').on('mouseenter', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', function() {
        $(this).removeClass('bg-light');
    });
}

// Hide service suggestions
function hideModalServiceSuggestions() {
    $('#modal_service_suggestions').empty();
}


// Show form type information and load the form into the modal if required
function showModalServiceInfo(serviceId, serviceName, servicePrice, serviceCategory, hasPricing = false, requiresForm = false, formType = null) {
    console.log('showModalServiceInfo called', { serviceId: serviceId, serviceName: serviceName, requiresForm: requiresForm, formType: formType });
    const info = $('#modal-service-info');
    const quantity = parseInt($('#modal_investigation_quantity').val()) || 1;
    const total = (parseFloat(servicePrice) * quantity).toFixed(2);

    let html = `
        <div class="row">
            <div class="col-md-8">
                <strong>${serviceName}</strong><br>
                <small class="text-muted">Category: ${serviceCategory}</small>
            </div>
            <div class="col-md-4 text-end">
                <strong>Unit Price: TSh ${parseFloat(servicePrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong><br>
                <strong>Total: TSh ${parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
            </div>
        </div>
    `;

    if (!hasPricing) {
        html += '<div class="mt-2"><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> No pricing information available</small></div>';
    }

    info.html(html).show();

    // If the service requires a form, show info and load the form
    if (requiresForm && formType) {
        showFormTypeInfo(formType);
    console.log('showModalServiceInfo: requesting loadFormDisplay for', formType);
    loadFormDisplay(formType);
    } else {
        hideFormTypeInfo();
    }
}

function showFormTypeInfo(formType) {
    const container = $('#form-type-info-container');
    const formDisplay = $('#form-display-container');

    const infoHtml = `
        <div class="alert alert-info">
            <i class="fas fa-file-alt"></i>
            This investigation requires the <strong>${formType}</strong> form to be completed. The form will be included when ordering.
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="toggleFormDisplay('${formType}')">Toggle Form</button>
        </div>
    `;

    container.html(infoHtml).show();
    formDisplay.show();
}

function hideFormTypeInfo() {
    $('#form-type-info-container').hide().empty();
    $('#form-display-container').hide().empty();
}

// Load and display a consultation investigation form into the modal via API
function loadFormDisplay(formType) {
    const formContainer = $('#form-display-container');
    formContainer.html('<div class="text-center py-3"><div class="spinner-border"></div></div>');

    // Build URL; the consultations code uses /api/investigation-form/{formType}
    const url = '/api/investigation-form/' + encodeURIComponent(formType);
    console.log('loadFormDisplay called for formType:', formType, 'url:', url);

    $.get(url, function(data) {
        console.log('loadFormDisplay: received response for', formType);
        // Insert the markup
        formContainer.html(data).show();
        // Temporary visual highlight to help debugging visibility issues
        formContainer.css('outline', '3px solid rgba(0,123,255,0.25)');
        setTimeout(function() { formContainer.css('outline', 'none'); }, 2000);
        try {
            // Force container visible
            formContainer.css('display', 'block');

            // Ensure the form container is visible and expanded so users see the loaded form
            if (!formContainer.is(':visible')) {
                formContainer.slideDown('fast');
            }

            // Try to scroll the modal body so the form is centered in view
            const modalBody = formContainer.closest('.modal-body');
            if (modalBody && modalBody.length) {
                // Compute offset relative to modal body
                try {
                    const top = formContainer.position().top + modalBody.scrollTop() - 20;
                    modalBody.animate({ scrollTop: top }, 250);
                } catch (err) {
                    // Fallback: scroll the element into view on the page
                    try { formContainer[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
                }
            } else {
                try { formContainer[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
            }

            // Focus the first input to draw attention (after a slight delay to allow any insertion scripts to run)
            setTimeout(function() {
                const firstField = formContainer.find('input, select, textarea').filter(':visible').first();
                if (firstField && firstField.length) {
                    try { firstField.focus(); } catch (e) {}
                }
            }, 50);

            console.log('Form loaded and displayed for formType:', formType);
        } catch (e) {
            console.error('Error showing loaded form:', e);
        }
    }).fail(function(xhr, status, err) {
        console.error('Failed to load form from', url, 'status:', status, 'error:', err, 'response:', xhr && xhr.responseText);
        formContainer.html(`<div class="alert alert-warning">Form preview not available for <strong>${formType}</strong>. Check console/network for details.</div>`).show();
    });
}

function toggleFormDisplay(formType) {
    const formContainer = $('#form-display-container');
    if (formContainer.is(':visible') && formContainer.children().length > 0) {
    formContainer.slideUp();
    } else {
    loadFormDisplay(formType);
    // Ensure we expand in case loadFormDisplay inserted content synchronously
    formContainer.slideDown('fast');
    }
}

// updateModalTotalPrice removed — investigations always ordered with quantity = 1, so no dynamic price recalculation is needed.

// Save lab investigation
function saveLabInvestigation() {
    const form = $('#labInvestigationForm');
    const button = $('.modal-footer .btn-warning');
    
    // Basic validation
    if (!$('#modal_selected_service_id').val()) {
        toastr.error('Please select a medical service');
        return;
    }
    
    // Show saving state
    button.prop('disabled', true);
    const originalText = button.html();
    button.html('<i class="fas fa-spinner fa-spin"></i> Ordering...');
    
    // Serialize all inputs inside the modal so dynamically-loaded form fields
    // (inserted into #form-display-container) are included in the POST payload.
    const formData = $('#labInvestigationModal').find('input, select, textarea').not(':disabled').serialize();
    
    $.ajax({
        url: '/investigations',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                toastr.success('Lab investigation ordered successfully!');
                
                // Refresh the investigations list in the modal
                const visitId = $('#modal_visit_id').val();
                if (visitId) {
                    loadExistingInvestigations(visitId);
                }
                
                // Reset the form but keep the modal open
                // unload the form displayed through loadFormDisplay if any
                // use the shared helper to ensure both the info container and the loaded form container are cleared
                hideFormTypeInfo();
                $('#labInvestigationForm')[0].reset();
                $('#modal_service_search').val('');
                $('#modal_selected_service_id').val('');
                $('#modal-service-info').hide();
                hideModalServiceSuggestions();
            } else {
                toastr.error(response.message || 'Failed to order investigation.');
            }
        },
        error: function(xhr) {
            console.error('Investigation save error:', xhr);
            let errorMessage = 'Failed to order investigation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
        },
        complete: function() {
            // Restore button
            button.prop('disabled', false);
            button.html(originalText);
        }
    });
}

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
<!-- Reuse consultation investigations helpers to render investigation forms in the patient visits modal -->
<script src="{{ asset('js/consultation/investigations.js') }}"></script>
<script>
// MutationObserver: ensure that when the consultation form partial is inserted into
// #form-display-container we make it visible and scroll to it. This handles cases
// where the consultation JS inserts HTML asynchronously and the modal doesn't
// automatically bring it into view.
(function() {
    try {
        const target = document.getElementById('form-display-container');
        if (!target) return;

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.addedNodes && m.addedNodes.length > 0) {
                    // Make sure container is visible
                    try {
                        const $container = $('#form-display-container');
                        if ($container.length) {
                            if (!$container.is(':visible')) {
                                $container.slideDown('fast');
                            }
                            // Focus first input and scroll into view
                            const first = $container.find('input, select, textarea').filter(':visible').first();
                            if (first && first.length) {
                                first.focus();
                                // Scroll the modal body so the form is visible
                                const modalBody = $container.closest('.modal-body');
                                if (modalBody && modalBody.length) {
                                    modalBody.animate({ scrollTop: $container.position().top + modalBody.scrollTop() - 20 }, 250);
                                }
                            }
                            console.log('MutationObserver: form inserted, ensured visible and focused');
                        }
                    } catch (err) { console.error('Observer error', err); }
                }
            });
        });

        observer.observe(target, { childList: true, subtree: true });
    } catch (e) {
        console.error('Failed to initialize form insertion observer', e);
    }
})();
</script>
@endsection

@section('extra_footer_content')
<style>
/* Enhanced Visit Type Badge Styling */
.badge-primary { background-color: #007bff !important; color: white !important; }
.badge-success { background-color: #28a745 !important; color: white !important; }
.badge-warning { background-color: #ffc107 !important; color: black !important; }
.badge-info { background-color: #17a2b8 !important; color: white !important; }
.badge-danger { background-color: #dc3545 !important; color: white !important; }
.badge-secondary { background-color: #6c757d !important; color: white !important; }

/* Modal and service selection styling */
.min-height-100 { min-height: 100px; }
.service-item { background-color: #f8f9fa; }
.service-item:hover { background-color: #e9ecef; }

/* Service suggestion styling */
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1060;
    max-height: 250px;
    overflow-y: auto;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.service-suggestion-item {
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
}

.service-suggestion-item:hover {
    background-color: #f8f9fa !important;
}

.service-suggestion-item:last-child {
    border-bottom: none !important;
}

.badge {
    font-size: 0.75rem;
}

#modal_service_suggestions {
    display: block !important;
    visibility: visible !important;
}

/* Lab button styling */
.btn-group .btn { margin-right: 2px; }

/* Current investigations table styling */
#current_investigations .table th {
    background-color: #f8f9fa;
    font-size: 0.85rem;
    border-top: none;
}

#current_investigations .table td {
    font-size: 0.85rem;
    vertical-align: middle;
}

#current_investigations .badge {
    font-size: 0.7rem;
}
.btn-group .btn:last-child { margin-right: 0; }

/* Table responsiveness for visit type column */
.table th, .table td { white-space: nowrap; }
.badge { font-size: 0.85em; padding: 0.4em 0.6em; }
</style>

@endsection