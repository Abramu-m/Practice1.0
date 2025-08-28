@extends('layouts.app_main_layout')

@section('page_title', 'Ledger Entry Details')

@section('styles')
<style>
    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-radius: 0.5rem;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .info-value {
        color: #6c757d;
        margin-bottom: 1rem;
    }
    
    .status-badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .status-active { background-color: #28a745; color: white; }
    .status-expired { background-color: #dc3545; color: white; }
    .status-damaged { background-color: #fd7e14; color: white; }
    .status-disposed { background-color: #6c757d; color: white; }
    
    .batch-display {
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        background: #e9ecef;
        padding: 0.75rem;
        border-radius: 0.375rem;
        text-align: center;
        font-weight: bold;
    }
    
    .expiry-warning { 
        color: #fd7e14; 
        font-weight: 600; 
        background: #fff3cd;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    
    .expiry-danger { 
        color: #dc3545; 
        font-weight: 600; 
        background: #f8d7da;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    
    .expiry-safe { 
        color: #28a745; 
        font-weight: 600; 
        background: #d4edda;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    
    .calculation-table {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    
    .timeline-item {
        border-left: 3px solid #007bff;
        padding-left: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-file-medical text-primary me-2"></i>
                        Ledger Entry Details
                    </h1>
                    <p class="text-muted mb-0">Detailed information for ledger entry #{{ $ledger->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.stock.ledger.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Ledger
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Information --}}
        <div class="col-md-8">
            {{-- Medication Information --}}
            <div class="info-card">
                <h5 class="text-primary mb-3">
                    <i class="fas fa-pills"></i> Medication Information
                </h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Generic Name</div>
                        <div class="info-value h5">{{ $ledger->medication->generic_name ?? 'N/A' }}</div>
                        
                        @if($ledger->medication->brand_name)
                        <div class="info-label">Brand Name</div>
                        <div class="info-value">{{ $ledger->medication->brand_name }}</div>
                        @endif
                        
                        @if($ledger->medication->strength)
                        <div class="info-label">Strength</div>
                        <div class="info-value">
                            <span class="badge bg-info">{{ $ledger->medication->strength }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        @if($ledger->medication->formulation)
                        <div class="info-label">Formulation</div>
                        <div class="info-value">{{ $ledger->medication->formulation->description }}</div>
                        @endif
                        
                        <div class="info-label">Medication ID</div>
                        <div class="info-value">
                            <code>{{ $ledger->medication->id }}</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Batch Information --}}
            <div class="info-card">
                <h5 class="text-success mb-3">
                    <i class="fas fa-barcode"></i> Batch Information
                </h5>
                
                <div class="batch-display mb-3">
                    Batch: {{ $ledger->batch_number }}
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-label">Manufacture Date</div>
                        <div class="info-value">
                            @if($ledger->manufacture_date)
                                {{ \Carbon\Carbon::parse($ledger->manufacture_date)->format('M d, Y') }}
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-label">Expiry Date</div>
                        <div class="info-value">
                            @php
                                $expiryDate = \Carbon\Carbon::parse($ledger->expiry_date);
                                $now = \Carbon\Carbon::now();
                                $daysDiff = (int) $now->diffInDays($expiryDate, false);
                                
                                if ($daysDiff < 0) {
                                    $expiryClass = 'expiry-danger';
                                    $expiryText = 'Expired ' . abs($daysDiff) . ' days ago';
                                } elseif ($daysDiff <= 30) {
                                    $expiryClass = 'expiry-danger';
                                    $expiryText = 'Expires in ' . $daysDiff . ' days';
                                } elseif ($daysDiff <= 180) {
                                    $expiryClass = 'expiry-warning';
                                    $expiryText = 'Expires in ' . $daysDiff . ' days';
                                } else {
                                    $expiryClass = 'expiry-safe';
                                    $expiryText = 'Valid for ' . $daysDiff . ' days';
                                }
                            @endphp
                            <div class="{{ $expiryClass }}">
                                {{ $expiryDate->format('M d, Y') }}
                                <br><small>{{ $expiryText }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-label">Shelf Life</div>
                        <div class="info-value">
                            @if($ledger->manufacture_date && $ledger->expiry_date)
                                @php
                                    $shelfLife = (int) \Carbon\Carbon::parse($ledger->manufacture_date)
                                        ->diffInDays(\Carbon\Carbon::parse($ledger->expiry_date));
                                @endphp
                                {{ $shelfLife }} days
                            @else
                                <span class="text-muted">Cannot calculate</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRN Information --}}
            <div class="info-card">
                <h5 class="text-info mb-3">
                    <i class="fas fa-truck"></i> GRN Information
                </h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">GRN Number</div>
                        <div class="info-value">
                            @if($ledger->grn)
                                <a href="{{ route('medications.stock.grn.show', $ledger->grn) }}" class="text-decoration-none">
                                    {{ $ledger->grn->grn_number }}
                                </a>
                            @else
                                <span class="text-muted">N/A (GRN Deleted)</span>
                            @endif
                        </div>
                        
                        <div class="info-label">GRN Date</div>
                        <div class="info-value">
                            @if($ledger->grn && $ledger->grn->grn_date)
                                {{ \Carbon\Carbon::parse($ledger->grn->grn_date)->format('M d, Y') }}
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-label">Supplier</div>
                        <div class="info-value">
                            {{ $ledger->grn && $ledger->grn->supplier ? $ledger->grn->supplier->name : 'N/A' }}
                        </div>
                        
                        <div class="info-label">GRN Item ID</div>
                        <div class="info-value">
                            <code>{{ $ledger->grn_item_id }}</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quantity & Cost Information --}}
            <div class="info-card">
                <h5 class="text-warning mb-3">
                    <i class="fas fa-calculator"></i> Quantity & Cost Analysis
                </h5>
                
                <div class="calculation-table">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-label">Quantity Received</div>
                            <div class="info-value h4 text-primary">
                                {{ number_format($ledger->quantity_received) }} units
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Unit Cost</div>
                            <div class="info-value h4 text-success">
                                ${{ number_format($ledger->unit_cost, 2) }}
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Total Value</div>
                            <div class="info-value h3 text-success">
                                ${{ number_format($ledger->quantity_received * $ledger->unit_cost, 2) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Cost per Day (until expiry)</div>
                            <div class="info-value">
                                @php
                                    $totalValue = $ledger->quantity_received * $ledger->unit_cost;
                                    $daysToExpiry = max(1, (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($ledger->expiry_date), false));
                                    $costPerDay = $daysToExpiry > 0 ? $totalValue / $daysToExpiry : 0;
                                @endphp
                                ${{ number_format($costPerDay, 2) }} / day
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Information --}}
        <div class="col-md-4">
            {{-- Status & Location --}}
            <div class="info-card">
                <h5 class="text-secondary mb-3">
                    <i class="fas fa-info-circle"></i> Status & Location
                </h5>
                
                <div class="info-label">Current Status</div>
                <div class="info-value">
                    <span class="badge status-badge status-{{ $ledger->status }}">
                        {{ ucfirst($ledger->status) }}
                    </span>
                </div>
                
                <div class="info-label">Storage Location</div>
                <div class="info-value">
                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                    {{ $ledger->location->name ?? 'Not specified' }}
                </div>
                
                @if($ledger->notes)
                <div class="info-label">Notes</div>
                <div class="info-value">
                    <div class="bg-light p-2 rounded">
                        {{ $ledger->notes }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Timeline --}}
            <div class="info-card">
                <h5 class="text-secondary mb-3">
                    <i class="fas fa-clock"></i> Timeline
                </h5>
                
                <div class="timeline-item">
                    <div class="fw-bold">Ledger Entry Created</div>
                    <div class="text-muted">{{ $ledger->created_at->format('M d, Y H:i') }}</div>
                    <small class="text-muted">{{ $ledger->created_at->diffForHumans() }}</small>
                </div>
                
                @if($ledger->grn && $ledger->grn->grn_date)
                <div class="timeline-item">
                    <div class="fw-bold">GRN Date</div>
                    <div class="text-muted">{{ \Carbon\Carbon::parse($ledger->grn->grn_date)->format('M d, Y') }}</div>
                </div>
                @endif
                
                @if($ledger->manufacture_date)
                <div class="timeline-item">
                    <div class="fw-bold">Manufactured</div>
                    <div class="text-muted">{{ \Carbon\Carbon::parse($ledger->manufacture_date)->format('M d, Y') }}</div>
                </div>
                @endif
                
                <div class="timeline-item">
                    <div class="fw-bold">
                        @if(\Carbon\Carbon::parse($ledger->expiry_date) < \Carbon\Carbon::now())
                            Expired
                        @else
                            Will Expire
                        @endif
                    </div>
                    <div class="text-muted">{{ \Carbon\Carbon::parse($ledger->expiry_date)->format('M d, Y') }}</div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="info-card">
                <h5 class="text-secondary mb-3">
                    <i class="fas fa-tools"></i> Quick Actions
                </h5>
                
                @if($ledger->status === 'active')
                <div class="d-grid gap-2">
                    <button class="btn btn-danger" onclick="markAsUnfit('damaged')">
                        <i class="fas fa-times-circle me-2"></i>
                        Damaged, Remove from Stock
                    </button>
                </div>
                @elseif($ledger->status === 'expired')
                <div class="d-grid gap-2">
                    <button class="btn btn-warning" onclick="markAsUnfit('expired')">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Expired, Remove from Stock
                    </button>
                </div>
                @elseif($ledger->status === 'exhausted' && $ledger->quantity_received <= 0)
                {{-- If the entry is active but has zero quantity, allow deletion --}}
                <div class="alert alert-info">
                    <button class="btn btn-warning" onclick="markAsUnfit('exhausted')">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete from Ledger (Zero Quantity)
                    </button>
                </div>
                @elseif($ledger->status === 'damaged')
                <div class="d-grid gap-2">
                    <button class="btn btn-danger" onclick="markAsUnfit('damaged')">
                        <i class="fas fa-times-circle me-2"></i>
                        Damaged, Remove from Stock
                    </button>
                </div>
                @endif
            </div>

            {{-- Related Information --}}
            <div class="info-card">
                <h5 class="text-secondary mb-3">
                    <i class="fas fa-link"></i> Related Information
                </h5>
                
                <div class="d-grid gap-2">
                    @if($ledger->grn)
                        <a href="{{ route('medications.stock.grn.show', $ledger->grn) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-truck me-2"></i>
                            View GRN Details
                        </a>
                    @else
                        <button class="btn btn-outline-secondary btn-sm" disabled>
                            <i class="fas fa-truck me-2"></i>
                            GRN Not Available
                        </button>
                    @endif
                    <a href="{{ route('medications.stock.ledger.index', ['medication_id' => $ledger->medication_id]) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-pills me-2"></i>
                        Other Batches of This Medication
                    </a>
                    <a href="{{ route('medications.stock.ledger.index', ['grn_id' => $ledger->grn_id]) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-list me-2"></i>
                        Other Items from This GRN
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Unfit Medication Modal --}}
<div class="modal fade" id="unfitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove from Stock - Unfit Medication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="unfitForm">
                <div class="modal-body">
                    <input type="hidden" id="unfitReason">
                    
                    <div class="alert alert-info">
                        <strong>Note:</strong> This action will move the medication to the unfit medications table for proper disposal tracking.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <input type="text" class="form-control" id="reasonDisplay" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantity to Discard</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="quantityDiscarded" 
                                           min="1" max="{{ $ledger->quantity_received }}" 
                                           value="{{ $ledger->quantity_received }}" step="0.01">
                                    <span class="input-group-text">units</span>
                                </div>
                                <small class="text-muted">Available: {{ number_format($ledger->quantity_received) }} units</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Disposal Method</label>
                        <select class="form-select" id="disposalMethod" required>
                            <option value="">Select disposal method...</option>
                            <option value="incineration">Incineration</option>
                            <option value="return_supplier">Return to Supplier</option>
                            <option value="secure_disposal">Secure Disposal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="unfitNotes" rows="3" 
                                  placeholder="Add details about the condition, disposal method, or other relevant information..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requiresVerification">
                            <label class="form-check-label" for="requiresVerification">
                                Requires supervisor verification before disposal
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove from Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function markAsUnfit(reason) {
    // For exhausted entries, delete directly without modal
    if (reason === 'exhausted') {
        if (confirm('Are you sure you want to delete this exhausted ledger entry? This action cannot be undone.')) {
            fetch(`{{ route('medications.stock.ledger.mark-unfit', $ledger) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    reason: 'exhausted',
                    quantity_discarded: {{ $ledger->quantity_received }},
                    disposal_method: 'other', // Default for exhausted
                    notes: 'Exhausted entry removed from ledger',
                    requires_verification: false
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Exhausted ledger entry deleted successfully', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route('medications.stock.ledger.index') }}';
                    }, 1500);
                } else {
                    showToast(data.message || 'Failed to delete entry', 'error');
                }
            })
            .catch(error => {
                showToast('Error deleting entry', 'error');
            });
        }
        return;
    }
    
    // For other reasons, show the modal
    document.getElementById('unfitReason').value = reason;
    document.getElementById('reasonDisplay').value = reason.charAt(0).toUpperCase() + reason.slice(1);
    
    // For expired medications, default to full quantity
    // For damaged medications, let user specify quantity
    if (reason === 'expired') {
        document.getElementById('quantityDiscarded').value = {{ $ledger->quantity_received }};
        document.getElementById('quantityDiscarded').readOnly = true;
        document.getElementById('requiresVerification').checked = false;
    } else {
        document.getElementById('quantityDiscarded').readOnly = false;
        document.getElementById('requiresVerification').checked = true;
    }
    
    // Reset other fields
    document.getElementById('disposalMethod').value = '';
    document.getElementById('unfitNotes').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('unfitModal'));
    modal.show();
}

document.getElementById('unfitForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const reason = document.getElementById('unfitReason').value;
    const quantityDiscarded = document.getElementById('quantityDiscarded').value;
    const disposalMethod = document.getElementById('disposalMethod').value;
    const notes = document.getElementById('unfitNotes').value;
    const requiresVerification = document.getElementById('requiresVerification').checked;
    
    if (!disposalMethod) {
        showToast('Please select a disposal method', 'error');
        return;
    }
    
    if (parseFloat(quantityDiscarded) <= 0 || parseFloat(quantityDiscarded) > {{ $ledger->quantity_received }}) {
        showToast('Invalid quantity specified', 'error');
        return;
    }
    
    fetch(`{{ route('medications.stock.ledger.mark-unfit', $ledger) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            reason: reason,
            quantity_discarded: quantityDiscarded,
            disposal_method: disposalMethod,
            notes: notes,
            requires_verification: requiresVerification
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = 'Medication marked as unfit and moved to disposal tracking';
            if (data.disposal_reference) {
                message += ` (Reference: ${data.disposal_reference})`;
            }
            showToast(message, 'success');
            
            // Handle redirect based on what happened
            if (data.redirect_to === 'index') {
                // Entry was deleted (expired or full quantity damaged) - redirect to index
                setTimeout(() => {
                    window.location.href = '{{ route('medications.stock.ledger.index') }}';
                }, 1500);
            } else {
                // Partial quantity removed - reload the show page
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showToast(data.message || 'Failed to process unfit medication', 'error');
        }
    })
    .catch(error => {
        showToast('Error processing unfit medication', 'error');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('unfitModal')).hide();
});

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const container = document.querySelector('.toast-container') || createToastContainer();
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}
</script>
@endsection
