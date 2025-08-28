@extends('layouts.app_main_layout')

@section('page_title', 'Investigation Management')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Investigation Management</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('investigations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Order Investigation
                        </a>
                        <a href="{{ route('investigations.statistics') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                        <!-- Quick Stats - Same size as buttons -->
                        <div class="btn btn-primary d-flex align-items-center">
                            <span class="fw-bold me-1">{{ $investigations->total() }}</span>
                            <span style="font-size: 0.85rem;">Total</span>
                        </div>
                        <div class="btn btn-warning d-flex align-items-center">
                            <span class="fw-bold me-1">{{ $investigations->where('status', 'ordered')->count() + $investigations->where('status', 'collected')->count() }}</span>
                            <span style="font-size: 0.85rem;">Pending</span>
                        </div>
                        <div class="btn btn-danger d-flex align-items-center">
                            <span class="fw-bold me-1">{{ $investigations->where('priority', 'urgent')->count() + $investigations->where('priority', 'stat')->count() }}</span>
                            <span style="font-size: 0.85rem;">Urgent</span>
                        </div>
                        <div class="btn btn-success d-flex align-items-center">
                            <span class="fw-bold me-1">{{ $investigations->where('status', 'resulted')->count() }}</span>
                            <span style="font-size: 0.85rem;">Completed</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('investigations.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        @foreach(App\Models\Investigation::getStatusOptions() as $key => $label)
                                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="">All</option>
                                        @foreach(App\Models\Investigation::getPriorityOptions() as $key => $label)
                                            <option value="{{ $key }}" {{ request('priority') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Doctor</label>
                                    <select name="doctor_id" class="form-select">
                                        <option value="">All Doctors</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Category</label>
                                    <select name="service_category" class="form-select">
                                        <option value="">All</option>
                                        @foreach($serviceCategories as $category)
                                            <option value="{{ $category->id }}" {{ request('service_category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Patient Search</label>
                                    <input type="text" name="patient_search" class="form-control" 
                                           placeholder="Name or ID" value="{{ request('patient_search') }}">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="date_from" class="form-control" 
                                           value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <a href="{{ route('investigations.index') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Investigations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Investigation</th>
                                    <th>Doctor</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Ordered Date</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($investigations as $investigation)
                                    <tr class="{{ $investigation->isOverdue() ? 'table-danger' : '' }}" data-investigation-id="{{ $investigation->id }}">
                                        <td>
                                            <strong>#{{ $investigation->id }}</strong>
                                            @if($investigation->isOverdue())
                                                <span class="badge bg-danger ms-1">OVERDUE</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($investigation->patient)
                                                <div>
                                                    <strong>{{ $investigation->patient->first_name }} {{ $investigation->patient->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $investigation->patient->mr_number }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown Patient</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($investigation->medicalService)
                                                <div>
                                                    <strong>{{ $investigation->medicalService->name }}</strong><br>
                                                    <small class="text-muted">{{ $investigation->medicalService->code }}</small>
                                                    @if($investigation->medicalService->requires_sample)
                                                        <br><span class="badge bg-info">Sample: {{ $investigation->medicalService->sample_type }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown Service</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($investigation->doctor)
                                                Dr. {{ $investigation->doctor->user->first_name }} {{ $investigation->doctor->user->last_name }}
                                            @else
                                                <span class="text-muted">Unknown Doctor</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $investigation->priority_badge_class }}">
                                                {{ $investigation->priority_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $investigation->status_badge_class }}">
                                                {{ $investigation->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}
                                            @if($investigation->formatted_age)
                                                <br><small class="text-muted">{{ $investigation->formatted_age }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $investigation->formatted_total_price }}</strong>
                                                @if($investigation->insurance_covered_amount > 0)
                                                    <br><small class="text-success">Covered: ${{ number_format($investigation->insurance_covered_amount, 2) }}</small>
                                                    <br><small class="text-info">Effective: {{ $investigation->formatted_effective_price }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if(Auth::user()->isAdmin())
                                                    <a href="{{ route('investigations.show', $investigation) }}" 
                                                    class="btn btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!in_array($investigation->status, ['collected', 'processing', 'resulted']))
                                                        <a href="{{ route('investigations.edit', $investigation) }}" 
                                                        class="btn btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Update Status">
                                                        <i class="fas fa-tasks"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @if($investigation->status === 'ordered' && $investigation->medicalService && $investigation->medicalService->requires_sample)
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $investigation->id }}, 'collected', 'stock')">Mark as Collected</a></li>
                                                        @endif
                                                        @if($investigation->status === 'collected')
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $investigation->id }}, 'processing', 'simple')">Mark as Processing</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item" href="{{ route('lab.results.form', $investigation->id) }}?return_to=investigations.index">
                                                                <i class="fas fa-edit"></i> Add Results
                                                            </a></li>
                                                        @endif
                                                        @if($investigation->status === 'processing')
                                                            <li><a class="dropdown-item" href="{{ route('lab.results.form', $investigation->id) }}?return_to=investigations.index">
                                                                <i class="fas fa-edit"></i> Add Results
                                                            </a></li>
                                                        @endif
                                                        @if($investigation->status === 'resulted')
                                                            <li><a class="dropdown-item" href="{{ route('lab.investigations.view-results', $investigation->id) }}">
                                                                <i class="fas fa-chart-line"></i> View Results
                                                            </a></li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        @if($investigation->status === 'ordered')
                                                            <li>
                                                                <a class="dropdown-item" href="#" onclick="showStockDetailsForInvestigation({{ $investigation->id }})">
                                                                    <i class="fas fa-boxes text-info"></i> Check Stock
                                                                </a>
                                                            </li>
                                                        @endif
                                                        @if(!in_array($investigation->status, ['resulted', 'cancelled']))
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus({{ $investigation->id }}, 'cancelled', 'simple')">Cancel Investigation</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-flask fa-3x mb-3"></i>
                                                <p>No investigations found</p>
                                                <a href="{{ route('investigations.create') }}" class="btn btn-primary">
                                                    Order First Investigation
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $investigations->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
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
            <form id="statusUpdateForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <input type="hidden" name="status" id="newStatus" value="">
                    <div class="mb-3">
                        <label class="form-label">Status Update Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span id="statusMessage"></span>
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

@endsection

@section('scripts')
<script>
function updateStatus(investigationId, newStatus, updateType = 'simple') {
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    const form = document.getElementById('statusUpdateForm');
    const statusInput = document.getElementById('newStatus');
    const statusMessage = document.getElementById('statusMessage');
    
    // Set form action based on update type
    if (updateType === 'stock' && newStatus === 'collected') {
        // Use LabController for stock-sensitive operations
        form.action = `/lab/investigations/${investigationId}/status`;
    } else {
        // Use regular InvestigationController for simple updates
        form.action = `/investigations/${investigationId}/status`;
    }
    
    // Set status
    statusInput.value = newStatus;
    
    // Set appropriate message
    const statusMessages = {
        'paid': 'Mark this investigation as paid and ready for sample collection.',
        'collected': updateType === 'stock' 
            ? 'Mark the sample as collected. This will check stock availability and deduct consumables from laboratory inventory.'
            : 'Mark the sample as collected and ready for processing.',
        'processing': 'Mark this investigation as currently being processed in the lab.',
        'resulted': 'Mark this investigation as completed with results available.',
        'cancelled': 'Cancel this investigation. This action cannot be undone.'
    };
    
    statusMessage.textContent = statusMessages[newStatus] || 'Update the investigation status.';
    
    // Add stock check indicator for collected status
    if (updateType === 'stock' && newStatus === 'collected') {
        statusMessage.innerHTML += '<br><small class="text-info"><i class="fas fa-flask"></i> Stock availability will be validated before collection.</small>';
    }
    
    // Show modal
    modal.show();
}

// Handle form submission with proper error handling for stock issues
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Get form data as JSON for proper CSRF handling
    const formData = {
        status: document.getElementById('newStatus').value,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };
    
    const notesField = this.querySelector('textarea[name="notes"]');
    if (notesField && notesField.value) {
        formData.notes = notesField.value;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;

    fetch(this.action, {
        method: 'PATCH',
        body: JSON.stringify(formData),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
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
            // Close the status modal first
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            
            // Show stock error modal
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
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Insufficient Stock
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
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

// Check stock for all investigations on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get all investigation rows
    const investigationRows = document.querySelectorAll('tr[data-investigation-id]');
    
    investigationRows.forEach(row => {
        const investigationId = row.getAttribute('data-investigation-id');
        
        if (investigationId) {
            // Check stock for this investigation
            fetch(`/lab/investigations/${investigationId}/check-stock`, {
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
                    
                    // Add warning icon to the status column
                    const statusCell = row.querySelector('td:nth-child(6)'); // Status is 6th column
                    if (statusCell) {
                        const warning = document.createElement('span');
                        warning.className = 'badge bg-warning ms-2';
                        warning.style.cursor = 'pointer';
                        warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Low Stock';
                        warning.title = 'Click to view stock details - collection blocked';
                        warning.onclick = function() {
                            showStockDetailsForInvestigation(investigationId);
                        };
                        statusCell.appendChild(warning);
                    }
                    
                    // Disable the collect button
                    const collectBtn = row.querySelector('a[onclick*="collected"]');
                    if (collectBtn) {
                        collectBtn.classList.add('disabled', 'btn-outline-secondary');
                        collectBtn.classList.remove('btn-outline-info');
                        collectBtn.style.pointerEvents = 'none';
                        collectBtn.title = 'Cannot collect - insufficient stock';
                        collectBtn.innerHTML = collectBtn.innerHTML + ' <i class="fas fa-exclamation-triangle text-warning"></i>';
                        
                        // Prevent click events
                        collectBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            alert('Cannot collect - insufficient stock available');
                        });
                    }
                    
                    // Also disable the "Add Results" button
                    const addResultsBtn = row.querySelector('a[href*="lab.results.form"]');
                    if (addResultsBtn) {
                        addResultsBtn.classList.add('disabled', 'btn-outline-secondary');
                        addResultsBtn.style.pointerEvents = 'none';
                        addResultsBtn.title = 'Cannot add results - insufficient stock for collection';
                        addResultsBtn.innerHTML = addResultsBtn.innerHTML + ' <i class="fas fa-exclamation-triangle text-warning"></i>';
                        
                        addResultsBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            alert('Cannot add results - insufficient stock for collection');
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error checking stock for investigation', investigationId, ':', error);
            });
        }
    });
});

// Function to show stock details modal (like in lab interface)
function showStockDetailsForInvestigation(investigationId) {
    if (!investigationId) {
        alert('Please specify an investigation ID');
        return;
    }
    
    // Show loading state
    const loadingHtml = `
        <div class="modal fade" id="stockLoadingModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                        <p>Checking stock availability...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loadingHtml);
    const loadingModal = new bootstrap.Modal(document.getElementById('stockLoadingModal'));
    loadingModal.show();
    
    // Fetch stock details for this specific investigation
    fetch(`/lab/investigations/${investigationId}/check-stock`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        loadingModal.hide();
        document.getElementById('stockLoadingModal').remove();
        
        if (!response.ok) {
            return response.json().then(errorData => {
                throw { status: response.status, data: errorData };
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.details) {
            // Use the same modal as the error modal but with different title and styling
            showStockDetailsModal(data.details, data.message || 'Stock information for this investigation:', data.can_proceed);
        } else {
            alert('No stock details available for this investigation.');
        }
    })
    .catch(error => {
        console.error('Error fetching stock details:', error);
        alert('Failed to fetch stock details. Please try again.');
    });
}

// Show stock details modal (enhanced version of showStockErrorModal)
function showStockDetailsModal(stockDetails, message, canProceed = false) {
    const modalTitle = canProceed ? 'Stock Information' : 'Insufficient Stock';
    const headerClass = canProceed ? 'bg-info' : 'bg-warning';
    const alertClass = canProceed ? 'alert-info' : 'alert-warning';
    
    const modalHtml = `
        <div class="modal fade" id="stockDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header ${headerClass}">
                        <h5 class="modal-title text-white">
                            <i class="fas ${canProceed ? 'fa-info-circle' : 'fa-exclamation-triangle'}"></i>
                            ${modalTitle}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="${alertClass}">
                            <i class="fas fa-info-circle"></i>
                            ${message}
                        </div>
                        
                        <h6 class="mb-3">Stock Requirements (Laboratory Location):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
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
                                ${canProceed 
                                    ? 'All required items have sufficient stock. Collection can proceed.'
                                    : 'Collection cannot proceed until all required items have sufficient stock in the Laboratory location.'
                                }
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('stockDetailsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('stockDetailsModal'));
    modal.show();
    
    // Clean up modal when hidden
    document.getElementById('stockDetailsModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Auto-refresh every 2 minutes for real-time updates
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 120000);
</script>
@endsection
