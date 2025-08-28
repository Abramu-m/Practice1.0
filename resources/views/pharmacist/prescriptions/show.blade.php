@extends('layouts.app_main_layout')

@section('page_title', 'Prescription Details')

@section('main_content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Prescription Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pharmacist.dashboard') }}">Pharmacist</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pharmacist.prescriptions.index') }}">Prescriptions</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Patient Information -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-person"></i>
                                Patient Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Name:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->first_name }} {{ $visit->patientInfo->last_name }}</dd>
                                
                                <dt class="col-sm-5">MR Number:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->mr_number }}</dd>
                                
                                <dt class="col-sm-5">Age:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->age ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Gender:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->gender ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">{{ $visit->patientInfo->phone_number ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Visit Information -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-calendar-check"></i>
                                Visit Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Visit Date:</dt>
                                <dd class="col-sm-7">{{ $visit->created_at->format('M d, Y h:i A') }}</dd>
                                
                                @if($visit->consultation && $visit->consultation->doctor)
                                <dt class="col-sm-5">Doctor:</dt>
                                <dd class="col-sm-7">Dr. {{ $visit->consultation->doctor->name ?? 'N/A' }}</dd>
                                @endif
                                
                                <dt class="col-sm-5">Visit Type:</dt>
                                <dd class="col-sm-7">{{ $visit->visit_type ?? 'General' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-check-circle"></i>
                                System Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Ready to Process</strong>
                                <br>
                                Prescriptions are available for dispensing.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prescriptions -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-capsule"></i>
                                Prescriptions
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($visit->consultation && $visit->consultation->prescriptions->count() > 0)
                                @foreach($visit->consultation->prescriptions as $prescription)
                                <div class="card mb-3 prescription-item" data-prescription-id="{{ $prescription->id }}">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="card-title">
                                                    {{ $prescription->medication->brand_name ?? $prescription->medication->generic_name }}
                                                    @if($prescription->medication->strength)
                                                        <small class="text-muted">({{ $prescription->medication->strength }})</small>
                                                    @endif
                                                </h5>
                                                <div class="prescription-details">
                                                    <p class="mb-1">
                                                        <strong>Quantity:</strong> {{ $prescription->quantity }}
                                                        @if($prescription->medication->unit)
                                                            {{ $prescription->medication->unit }}
                                                        @endif
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Frequency:</strong> {{ $prescription->frequency->description ?? 'As directed' }}
                                                    </p>
                                                    @if($prescription->administrationRoute)
                                                        <p class="mb-1">
                                                            <strong>Route:</strong> {{ $prescription->administrationRoute->route }}
                                                        </p>
                                                    @endif
                                                    @if($prescription->duration)
                                                        <p class="mb-1">
                                                            <strong>Duration:</strong> {{ $prescription->duration }} days
                                                        </p>
                                                    @endif
                                                    @if($prescription->instructions)
                                                        <p class="mb-1">
                                                            <strong>Instructions:</strong> {{ $prescription->instructions }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <!-- Status Badge -->
                                                <div class="mb-2">
                                                    @switch($prescription->status)
                                                        @case('prescribed')
                                                        @case('prepared')
                                                            <span class="badge badge-warning badge-lg">
                                                                <i class="bi bi-clock"></i> Pending
                                                            </span>
                                                            @break
                                                        @case('dispensed')
                                                            <span class="badge badge-success badge-lg">
                                                                <i class="bi bi-check-circle"></i> Dispensed
                                                            </span>
                                                            @break
                                                        @case('cancelled')
                                                            <span class="badge badge-danger badge-lg">
                                                                <i class="bi bi-x-circle"></i> Unavailable
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </div>

                                                <!-- Actions -->
                                                @if(in_array($prescription->status, ['prescribed', 'prepared']))
                                                    <div class="btn-group-vertical d-block">
                                                        <button type="button" class="btn btn-success btn-sm mb-1" 
                                                                onclick="dispensePrescription({{ $prescription->id }})">
                                                            <i class="bi bi-check"></i> Dispense
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                onclick="markUnavailable({{ $prescription->id }})">
                                                            <i class="bi bi-x"></i> Mark Unavailable
                                                        </button>
                                                    </div>
                                                @elseif($prescription->status === 'dispensed')
                                                    <div class="text-muted">
                                                        <small>
                                                            Dispensed: {{ $prescription->dispensed_at ? $prescription->dispensed_at->format('M d, Y h:i A') : 'N/A' }}
                                                            <br>
                                                            Quantity: {{ $prescription->quantity_dispensed ?? $prescription->quantity }}
                                                        </small>
                                                    </div>
                                                @elseif($prescription->status === 'cancelled')
                                                    <div class="text-muted">
                                                        <small>
                                                            Reason: {{ $prescription->pharmacist_notes ?? 'Not specified' }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if($prescription->pharmacist_notes)
                                            <div class="mt-2">
                                                <hr>
                                                <small class="text-muted">
                                                    <i class="bi bi-chat-text"></i>
                                                    <strong>Pharmacist Notes:</strong> {{ $prescription->pharmacist_notes }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2">No prescriptions found for this visit.</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('pharmacist.prescriptions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Prescriptions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Dispense Modal -->
<div class="modal fade" id="dispenseModal" tabindex="-1" role="dialog" aria-labelledby="dispenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="dispenseForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="dispenseModalLabel">Dispense Prescription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="quantity_dispensed">Quantity to Dispense</label>
                        <input type="number" class="form-control" id="quantity_dispensed" name="quantity_dispensed" 
                               min="0" step="0.01" required>
                        <small class="text-muted">Maximum: <span id="max_quantity"></span></small>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Any additional notes about dispensing..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check"></i> Dispense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unavailable Modal -->
<div class="modal fade" id="unavailableModal" tabindex="-1" role="dialog" aria-labelledby="unavailableModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="unavailableForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="unavailableModalLabel">Mark as Unavailable</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required
                                  placeholder="Please specify why this medication is unavailable..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-x"></i> Mark Unavailable
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function dispensePrescription(prescriptionId) {
    // Find the prescription data
    const prescriptionCard = document.querySelector(`[data-prescription-id="${prescriptionId}"]`);
    const quantityText = prescriptionCard.querySelector('.prescription-details p').textContent;
    const quantity = quantityText.match(/\d+(\.\d+)?/)[0];
    
    // Set up the form
    document.getElementById('quantity_dispensed').value = quantity;
    document.getElementById('quantity_dispensed').max = quantity;
    document.getElementById('max_quantity').textContent = quantity;
    document.getElementById('dispenseForm').action = `{{ url('/pharmacist/prescriptions') }}/${prescriptionId}/dispense`;
    
    // Show modal
    $('#dispenseModal').modal('show');
}

function markUnavailable(prescriptionId) {
    // Set up the form
    document.getElementById('unavailableForm').action = `{{ url('/pharmacist/prescriptions') }}/${prescriptionId}/unavailable`;
    
    // Show modal
    $('#unavailableModal').modal('show');
}

$(document).ready(function() {
    // Handle form submissions
    $('#dispenseForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
    $('#unavailableForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
});
</script>
@endsection

@push('styles')
<style>
.prescription-item {
    border-left: 4px solid #17a2b8;
}

.prescription-item.dispensed {
    border-left-color: #28a745;
}

.prescription-item.unavailable {
    border-left-color: #dc3545;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

@media print {
    .card-tools,
    .card-footer,
    .btn,
    .modal {
        display: none !important;
    }
}
</style>
@endpush
