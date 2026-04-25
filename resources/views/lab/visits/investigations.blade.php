@extends('layouts.app_main_layout')

@section('page_title', 'Lab Investigations - ' . $visit->patientInfo->first_name . ' ' . $visit->patientInfo->last_name)

@section('main_content')
<div class="container-fluid">
    <!-- Patient Information Header -->
    <div class="alert alert-info mb-4">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user"></i> Patient Information</h6>
                <strong>{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</strong>
                @if($visit->patientInfo->middle_name)
                    {{ $visit->patientInfo->middle_name }}
                @endif
                <br>
                <small>
                    MR: {{ $visit->patientInfo->mr_number ?? 'N/A' }} | 
                    Visit ID: {{ $visit->id }} |
                    Age: {{ $visit->patientInfo->age ?? 'N/A' }} |
                    Gender: {{ ucfirst($visit->patientInfo->gender ?? 'N/A') }}
                </small>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-calendar"></i> Visit Details</h6>
                <strong>Date:</strong> {{ $visit->visit_date ? $visit->visit_date->format('M d, Y h:i A') : 'N/A' }}<br>
                <strong>Doctor:</strong>
                @if(optional($visit->doctorInfo)->user)
                    Dr. {{ optional($visit->doctorInfo->user)->first_name }} {{ optional($visit->doctorInfo->user)->last_name }}
                @else
                    <span class="text-muted">Not assigned</span>
                @endif
                <strong>Status:</strong> 
                <span class="badge {{ $visit->visit_status_badge_class }}">{{ $visit->visit_status_label }}</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('lab.visits.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Lab Visits
            </a>
        </div>
        <div>
            <button class="btn btn-info" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Lab Investigations -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-vial text-primary"></i> 
                Lab Investigations ({{ $investigations->count() }})
            </h5>
            <div>
                @php
                    $urgentCount = $investigations->whereIn('priority', ['urgent', 'stat'])->count();
                    $pendingCount = $investigations->whereNotIn('status', ['resulted', 'cancelled'])->count();
                @endphp
                @if($urgentCount > 0)
                    <span class="badge bg-danger">{{ $urgentCount }} Urgent</span>
                @endif
                @if($pendingCount > 0)
                    <span class="badge bg-warning">{{ $pendingCount }} Pending</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($investigations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Investigation</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Ordered</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($investigations as $investigation)
                            <tr class="{{ $investigation->priority === 'stat' ? 'table-danger' : ($investigation->priority === 'urgent' ? 'table-warning' : '') }}" 
                                data-investigation-id="{{ $investigation->id }}" 
                                data-investigation-status="{{ $investigation->status }}">
                                <td>
                                    <div>
                                        <strong>{{ $investigation->medicalService->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Code: {{ $investigation->medicalService->code ?? 'N/A' }} |
                                            ID: {{ $investigation->id }}
                                        </small>
                                        @if($investigation->notes)
                                            <br><small class="text-info">
                                                <i class="fas fa-sticky-note"></i> {{ Str::limit($investigation->notes, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $investigation->medicalService->serviceCategory->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $priorityClass = match($investigation->priority) {
                                            'stat' => 'danger',
                                            'urgent' => 'warning',
                                            'routine' => 'secondary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $priorityClass }}">
                                        {{ strtoupper($investigation->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($investigation->status) {
                                            'ordered' => 'warning',
                                            'collected' => 'info',
                                            'processing' => 'primary',
                                            'resulted' => 'success',
                                            'cancelled' => 'secondary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($investigation->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        {{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y') : 'N/A' }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $investigation->ordered_at ? $investigation->ordered_at->format('H:i A') : '' }}
                                            @if($investigation->formatted_age)
                                                <br>{{ $investigation->formatted_age }}
                                            @endif
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($investigation->ordered_at)
                                            <small class="text-success">
                                                <i class="fas fa-check"></i> Ordered
                                                ({{ $investigation->ordered_at->format('M d, H:i') }})
                                            </small>
                                        @endif
                                        @if($investigation->collected_at)
                                            <small class="text-info">
                                                <i class="fas fa-check"></i> Collected
                                                ({{ $investigation->collected_at->format('M d, H:i') }})
                                            </small>
                                        @endif
                                        @if($investigation->resulted_at)
                                            <small class="text-primary">
                                                <i class="fas fa-check"></i> Resulted
                                                ({{ $investigation->resulted_at->format('M d, H:i') }})
                                            </small>
                                        @endif
                                        @if($investigation->results->count() > 0)
                                            <small class="text-success">
                                                <a href="{{ route('lab.investigations.view-results', $investigation->id) }}" 
                                                   class="text-success text-decoration-none">
                                                    <i class="fas fa-chart-line"></i> {{ $investigation->results->count() }} result(s)
                                                </a>
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($investigation->is_paid)
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        @if(in_array($investigation->status, ['ordered', 'collected', 'processing']))
                                            @if($investigation->status === 'ordered')
                                                <button class="btn btn-outline-info" 
                                                        onclick="updateInvestigationStatus({{ $investigation->id }}, 'collected')"
                                                        title="Mark as Collected">
                                                    <i class="fas fa-flask"></i> Collect
                                                </button>
                                            @endif
                                            
                                            <a href="{{ route('lab.results.form', $investigation->id) }}" 
                                               class="btn btn-primary" title="Add Results">
                                                <i class="fas fa-edit"></i> Add Results
                                            </a>
                                        @endif

                                        @if($investigation->status === 'resulted')
                                            <a href="{{ route('lab.investigations.view-results', $investigation->id) }}" 
                                               class="btn btn-success" title="View Results">
                                                <i class="fas fa-chart-line"></i> View Results
                                            </a>
                                        @endif

                                        <!-- Status Update Dropdown -->
                                        @if(!in_array($investigation->status, ['resulted', 'cancelled']))
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown" title="Update Status">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if(in_array($investigation->status, ['ordered', 'collected']))
                                                    <li><button class="dropdown-item" 
                                                               onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')">
                                                        <i class="fas fa-spinner text-primary"></i> Mark Processing
                                                    </button></li>
                                                @endif
                                                @if(in_array($investigation->status, ['ordered', 'collected', 'processing']))
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button class="dropdown-item text-danger" 
                                                               onclick="updateInvestigationStatus({{ $investigation->id }}, 'cancelled')">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button></li>
                                                @endif
                                            </ul>
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                    <span class="badge bg-warning text-dark">Pending Payment</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-vial text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted">No lab investigations found for this visit</h5>
                    <p class="text-muted">Lab investigations will appear here when they are ordered by the doctor.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Investigation Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusUpdateForm">
                <div class="modal-body">
                    <input type="hidden" id="investigation_id" name="investigation_id">
                    <input type="hidden" id="new_status" name="status">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" id="status_display" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
function updateInvestigationStatus(investigationId, status) {
    // Set modal values
    document.getElementById('investigation_id').value = investigationId;
    document.getElementById('new_status').value = status;
    document.getElementById('status_display').value = status.charAt(0).toUpperCase() + status.slice(1);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const investigationId = formData.get('investigation_id');
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch(`{{ route('lab.investigations.update-status', ':id') }}`.replace(':id', investigationId), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: formData.get('status'),
            notes: formData.get('notes')
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw { status: response.status, data: errorData };
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            
            // Show success message
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            } else {
                alert(data.message);
            }
            
            // Reload page to show updated status
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        
        // Check if this is a stock availability error (422 status)
        if (error.status === 422 && error.data.stock_details) {
            showStockErrorModal(error.data.stock_details, error.data.message);
        } else {
            const message = error.data?.message || error.message || 'Failed to update status';
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert(message);
            }
        }
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

function showStockErrorModal(stockDetails, message) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="stockErrorModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Stock Information
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>${message}</strong>
                        </div>
                        
                        <h6 class="mb-3">Stock Requirements (Laboratory Location):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Required</th>
                                        <th>Available (Lab)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${stockDetails.map(item => `
                                        <tr class="${!item.is_available && !item.is_optional ? 'table-danger' : item.is_available ? 'table-success' : 'table-warning'}">
                                            <td>
                                                ${item.medication_name || 'Unknown Item'}
                                                ${item.is_optional ? '<small class="text-muted">(Optional)</small>' : ''}
                                            </td>
                                            <td>${item.required_quantity}</td>
                                            <td>${item.available_quantity}</td>
                                            <td>
                                                ${item.is_available 
                                                    ? '<span class="badge bg-success">Sufficient</span>' 
                                                    : item.is_optional 
                                                        ? '<span class="badge bg-warning">Low Stock (Optional)</span>'
                                                        : '<span class="badge bg-danger">Insufficient</span>'
                                                }
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Collection cannot proceed until all required items have sufficient stock in the Laboratory location.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('stockErrorModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('stockErrorModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('stockErrorModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function showStockDetailsForInvestigation(investigationId) {
    // Fetch stock details for this specific investigation
    fetch(`{{ route('lab.investigations.check-stock', ':id') }}`.replace(':id', investigationId), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Stock check response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Stock check response data:', data);
        
        if (data.success) {
            // Check if we have details (the correct property name)
            if (data.details && data.details.length > 0) {
                showStockErrorModal(data.details, data.message || 'Stock information for this investigation:');
            } else if (!data.can_proceed) {
                // If can't proceed but no details, show a generic message
                if (typeof toastr !== 'undefined') {
                    toastr.warning('This investigation has stock issues but no detailed information is available');
                } else {
                    alert('This investigation has stock issues but no detailed information is available');
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.info('No stock issues found for this investigation');
                } else {
                    alert('No stock issues found for this investigation');
                }
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error('Unable to fetch stock details');
            } else {
                alert('Unable to fetch stock details');
            }
        }
    })
    .catch(error => {
        console.error('Error fetching stock details:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Error fetching stock details');
        } else {
            alert('Error fetching stock details');
        }
    });
}

function attemptCollectionForStockDetails(investigationId) {
    // Simulate a collection attempt to get detailed stock error
    fetch(`{{ route('lab.investigations.update-status', ':id') }}`.replace(':id', investigationId), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: 'collected',
            notes: 'Stock check only - do not actually update'
        })
    })
    .then(response => {
        if (response.status === 422) {
            return response.json().then(errorData => {
                if (errorData.stock_details) {
                    showStockErrorModal(errorData.stock_details, errorData.message || 'Stock requirements for this investigation:');
                } else {
                    throw new Error('No detailed stock information available');
                }
            });
        } else {
            throw new Error('Unable to get stock details');
        }
    })
    .catch(error => {
        console.error('Error getting detailed stock info:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Unable to get detailed stock information');
        } else {
            alert('Unable to get detailed stock information');
        }
    });
}

// Check stock for all 'ordered' investigations when page loads
document.addEventListener('DOMContentLoaded', function() {
    const orderedInvestigations = document.querySelectorAll('tr[data-investigation-status="ordered"]');
    
    orderedInvestigations.forEach(function(row) {
        const investigationId = row.getAttribute('data-investigation-id');
        
        if (investigationId) {
            fetch(`{{ route('lab.investigations.check-stock', ':id') }}`.replace(':id', investigationId), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && !data.can_proceed) {
                    // Add low stock indicator to the row
                    row.classList.add('table-warning');
                    
                    // Add warning icon to the row
                    const statusCell = row.querySelector('td:nth-child(4)'); // Assuming status is 4th column
                    if (statusCell) {
                        const warning = document.createElement('span');
                        warning.className = 'badge bg-warning ms-2';
                        warning.style.cursor = 'pointer';
                        warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Low Stock';
                        warning.title = 'Click to view stock details';
                        warning.setAttribute('data-investigation-id', investigationId);
                        warning.addEventListener('click', function() {
                            showStockDetailsForInvestigation(investigationId);
                        });
                        statusCell.appendChild(warning);
                    }
                    
                    // Disable the collect button and add tooltip
                    const collectBtn = row.querySelector('button[onclick*="collected"]');
                    if (collectBtn) {
                        collectBtn.classList.add('disabled');
                        collectBtn.disabled = true;
                        collectBtn.title = 'Cannot collect - insufficient stock';
                        collectBtn.innerHTML = '<i class="fas fa-flask"></i> Collect <i class="fas fa-exclamation-triangle text-warning"></i>';
                    }
                    
                    // Also disable the "Add Results" button
                    const addResultsBtn = row.querySelector('a[href*="lab.results.form"]');
                    if (addResultsBtn) {
                        addResultsBtn.classList.add('disabled', 'btn-outline-secondary');
                        addResultsBtn.classList.remove('btn-primary');
                        addResultsBtn.style.pointerEvents = 'none';
                        addResultsBtn.title = 'Cannot add results - insufficient stock for collection';
                        addResultsBtn.innerHTML = '<i class="fas fa-edit"></i> Add Results <i class="fas fa-exclamation-triangle text-warning"></i>';
                        
                        // Prevent click events as well
                        addResultsBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        });
                    } else {
                        // Try alternative selector if the first one doesn't work
                        const altAddResultsBtn = row.querySelector('a.btn-primary[title="Add Results"]');
                        if (altAddResultsBtn) {
                            altAddResultsBtn.classList.add('disabled', 'btn-outline-secondary');
                            altAddResultsBtn.classList.remove('btn-primary');
                            altAddResultsBtn.style.pointerEvents = 'none';
                            altAddResultsBtn.title = 'Cannot add results - insufficient stock for collection';
                            altAddResultsBtn.innerHTML = '<i class="fas fa-edit"></i> Add Results <i class="fas fa-exclamation-triangle text-warning"></i>';
                            
                            altAddResultsBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            });
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error checking stock for investigation ' + investigationId + ':', error);
            });
        }
    });
});
</script>
@endsection
@endsection
