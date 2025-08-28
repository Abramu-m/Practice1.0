@extends('layouts.app_main_layout')

@section('page_title', 'Investigation Details')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Investigation #{{ $investigation->id }}</h4>
                    <div>
                        <a href="{{ route('investigations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        @if(!in_array($investigation->status, ['collected', 'processing', 'resulted']))
                            <a href="{{ route('investigations.edit', $investigation) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Investigation Info -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Investigation Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Investigation:</strong></td>
                                            <td>
                                                {{ $investigation->medicalService->name ?? 'Unknown' }}
                                                @if($investigation->medicalService)
                                                    <br><small class="text-muted">Code: {{ $investigation->medicalService->code }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Category:</strong></td>
                                            <td>{{ $investigation->medicalService->serviceCategory->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Priority:</strong></td>
                                            <td>
                                                <span class="badge {{ $investigation->priority_badge_class }}">
                                                    {{ $investigation->priority_label }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge {{ $investigation->status_badge_class }}">
                                                    {{ $investigation->status_label }}
                                                </span>
                                                @if($investigation->isOverdue())
                                                    <span class="badge bg-danger ms-1">OVERDUE</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payment Status:</strong></td>
                                            <td>
                                                @if($investigation->is_paid)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Paid
                                                    </span>
                                                    @if($investigation->paid_at)
                                                        <br><small class="text-muted">{{ $investigation->paid_at->format('M d, Y h:i A') }}</small>
                                                    @endif
                                                    @if($investigation->payment_method)
                                                        <br><small class="text-muted">via {{ ucfirst($investigation->payment_method) }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Pending Payment
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quantity:</strong></td>
                                            <td>{{ $investigation->quantity }}</td>
                                        </tr>
                                        @if($investigation->requiresSample())
                                            <tr>
                                                <td><strong>Sample Required:</strong></td>
                                                <td>
                                                    <span class="badge bg-info">{{ $investigation->medicalService->sample_type }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Info -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Patient Information</h5>
                                </div>
                                <div class="card-body">
                                    @if($investigation->patient)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td>{{ $investigation->patient->first_name }} {{ $investigation->patient->last_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>MR Number:</strong></td>
                                                <td>{{ $investigation->patient->mr_number }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Age:</strong></td>
                                                <td>{{ $investigation->patient->age ?? 'Unknown' }} years</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gender:</strong></td>
                                                <td>{{ ucfirst($investigation->patient->gender ?? 'Unknown') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone:</strong></td>
                                                <td>{{ $investigation->patient->phone_number ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">Patient information not available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Doctor & Consultation Info -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Doctor & Consultation</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Ordered By:</strong></td>
                                            <td>
                                                @if($investigation->doctor)
                                                    Dr. {{ $investigation->doctor->first_name }} {{ $investigation->doctor->last_name }}
                                                    @if($investigation->doctor->specialization)
                                                        <br><small class="text-muted">{{ $investigation->doctor->specialization }}</small>
                                                    @endif
                                                @else
                                                    Unknown Doctor
                                                @endif
                                            </td>
                                        </tr>
                                        @if($investigation->consultation)
                                            <tr>
                                                <td><strong>Consultation:</strong></td>
                                                <td>
                                                    <a href="/consultations/{{ $investigation->consultation->id }}" class="btn btn-sm btn-outline-primary">
                                                        View Consultation #{{ $investigation->consultation->id }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Ordered Date:</strong></td>
                                            <td>
                                                {{ $investigation->ordered_at ? $investigation->ordered_at->format('F j, Y \a\t g:i A') : 'N/A' }}
                                                @if($investigation->age_in_hours)
                                                    <br><small class="text-muted">{{ $investigation->age_in_hours }} hours ago</small>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Info -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Pricing Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Unit Price:</strong></td>
                                            <td>${{ number_format($investigation->unit_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quantity:</strong></td>
                                            <td>{{ $investigation->quantity }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Price:</strong></td>
                                            <td><strong>{{ $investigation->formatted_total_price }}</strong></td>
                                        </tr>
                                        @if($investigation->insurance_covered_amount > 0)
                                            <tr>
                                                <td><strong>Insurance Covered:</strong></td>
                                                <td class="text-success">${{ number_format($investigation->insurance_covered_amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Effective Price:</strong></td>
                                                <td class="text-info"><strong>{{ $investigation->formatted_effective_price }}</strong></td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Investigation Timeline</h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        @if($investigation->ordered_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-warning"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Investigation Ordered</h6>
                                                    <p class="timeline-text">{{ $investigation->ordered_at->format('F j, Y \a\t g:i A') }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($investigation->paid_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Payment Received</h6>
                                                    <p class="timeline-text">
                                                        {{ $investigation->paid_at->format('F j, Y \a\t g:i A') }}
                                                        @if($investigation->payment_method)
                                                            <br><small class="text-muted">Payment method: {{ ucfirst($investigation->payment_method) }}</small>
                                                        @endif
                                                        @if($investigation->amount_paid && $investigation->amount_paid != $investigation->total_price)
                                                            <br><small class="text-muted">Amount paid: ${{ number_format($investigation->amount_paid, 2) }}</small>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($investigation->collected_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-info"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Sample Collected</h6>
                                                    <p class="timeline-text">
                                                        {{ $investigation->collected_at->format('F j, Y \a\t g:i A') }}
                                                        @if($investigation->collectedBy)
                                                            <br><small class="text-muted">by {{ $investigation->collectedBy->name }}</small>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($investigation->resulted_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Results Available</h6>
                                                    <p class="timeline-text">
                                                        {{ $investigation->resulted_at->format('F j, Y \a\t g:i A') }}
                                                        @if($investigation->resultedBy)
                                                            <br><small class="text-muted">by {{ $investigation->resultedBy->name }}</small>
                                                        @endif
                                                        @if($investigation->turnaround_time)
                                                            <br><small class="text-info">Turnaround time: {{ $investigation->turnaround_time }} hours</small>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($investigation->notes)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            {{ $investigation->notes }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Stock Consumptions -->
                    @if($investigation->investigationConsumptions && $investigation->investigationConsumptions->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Stock Consumptions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Batch</th>
                                                        <th>Quantity Used</th>
                                                        <th>Cost</th>
                                                        <th>Location</th>
                                                        <th>Consumed At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($investigation->investigationConsumptions as $consumption)
                                                        <tr>
                                                            <td>{{ $consumption->medication->generic_name ?? 'Unknown' }}</td>
                                                            <td>{{ $consumption->batch_number }}</td>
                                                            <td>{{ $consumption->quantity_used }}</td>
                                                            <td>${{ number_format($consumption->cost_per_unit * $consumption->quantity_used, 2) }}</td>
                                                            <td>{{ $consumption->location->name ?? 'Unknown' }}</td>
                                                            <td>{{ $consumption->consumed_at->format('M d, Y h:i A') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group me-2">
                                        @if($investigation->status === 'ordered')
                                            @if(!$investigation->is_paid)
                                                <button class="btn btn-outline-success" onclick="updateStatus('paid')">
                                                    <i class="fas fa-credit-card"></i> Mark as Paid
                                                </button>
                                            @endif
                                            <button class="btn btn-outline-info" onclick="updateStatus('collected')">
                                                <i class="fas fa-vial"></i> Mark as Collected
                                            </button>
                                        @endif
                                        @if($investigation->status === 'collected')
                                            <button class="btn btn-outline-warning" onclick="updateStatus('processing')">
                                                <i class="fas fa-cogs"></i> Mark as Processing
                                            </button>
                                        @endif
                                        
                                        @if(in_array($investigation->status, ['collected', 'processing']))
                                            <a href="{{ route('lab.results.form', $investigation->id) }}?return_to=investigations.show&investigation_id={{ $investigation->id }}" class="btn btn-primary">
                                                <i class="fas fa-edit"></i> Add Results
                                            </a>
                                        @endif
                                        @if(!in_array($investigation->status, ['resulted', 'cancelled']))
                                            <button class="btn btn-outline-danger" onclick="updateStatus('cancelled')">
                                                <i class="fas fa-times"></i> Cancel Investigation
                                            </button>
                                        @endif
                                    </div>
                                    
                                    @if($investigation->status === 'resulted' && $investigation->results->count() > 0)
                                        <a href="{{ route('lab.investigations.view-results', $investigation->id) }}" class="btn btn-success">
                                            <i class="fas fa-chart-line"></i> View Results
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 15px;
    width: 2px;
    height: calc(100% + 10px);
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-title {
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-text {
    margin-bottom: 0;
    color: #6c757d;
}
</style>
@endsection

@section('scripts')
<script>
function updateStatus(newStatus) {
    // Set appropriate message based on status
    let confirmMessage = `Are you sure you want to update the status to "${newStatus}"?`;
    if (newStatus === 'collected') {
        confirmMessage = 'Are you sure you want to mark this investigation as collected? This will validate stock availability and deduct consumables from laboratory inventory.';
    }
    
    if (confirm(confirmMessage)) {
        // Use LabController for 'collected' status (stock-sensitive), regular controller for others
        const actionUrl = newStatus === 'collected' 
            ? `/lab/investigations/{{ $investigation->id }}/status`
            : `/investigations/{{ $investigation->id }}/status`;
        
        // Use fetch for better error handling, especially for stock issues
        fetch(actionUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus
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
        });
    }
}

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
</script>
@endsection
