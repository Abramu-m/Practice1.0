@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Cash Sale Details</h1>
                <a href="{{ route('medication-cash-sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Sales
                </a>
            </div>

            <!-- Cancellation Alert for All Users -->
            @if($medicationCashSale->status === 'cancelled')
            <div class="alert alert-danger mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-times-circle"></i> This Sale Has Been Cancelled
                        </h5>
                        <p class="mb-0">
                            <strong>Cancelled:</strong> {{ $medicationCashSale->cancelled_at ? $medicationCashSale->cancelled_at->format('M d, Y \a\t H:i') : 'Date not available' }}
                            @if($medicationCashSale->cancelled_by_user)
                            by <strong>{{ $medicationCashSale->cancelled_by_user->name }}</strong>
                            @endif
                        </p>
                        @if($medicationCashSale->cancellation_reason)
                        <p class="mb-0 mt-2">
                            <strong>Reason:</strong> {{ $medicationCashSale->cancellation_reason }}
                        </p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 1rem;">
                            CANCELLED
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Stock Warning Alert -->
            @if($hasStockIssues && $medicationCashSale->is_paid && !$medicationCashSale->isCompleted())
            <div class="alert alert-warning mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="alert-heading mb-2">
                            <i class="fas fa-exclamation-triangle"></i> Stock Shortage Warning
                        </h5>
                        <p class="mb-0">
                            Some medications in this sale have insufficient stock for dispensing. Please check individual items below.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-warning rounded-pill px-3 py-2" style="font-size: 1rem;">
                            STOCK ISSUES
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Receptionist/Cashier Payment Status -->
            @if(Auth::user()->isReceptionist() || Auth::user()->isCashier() || Auth::user()->isAdmin())
            <div class="row mb-4">
                <div class="col-md-12">
                    @if($medicationCashSale->canBePaid())
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Payment Processing Required</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-0">Sale: <strong>{{ $medicationCashSale->sale_number }}</strong></h4>
                                    <p class="text-muted mb-2">{{ $medicationCashSale->items->count() }} item(s) ready for payment</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h3 class="text-primary mb-2">TSh {{ number_format($medicationCashSale->final_amount, 2) }}</h3>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                        <i class="fas fa-money-bill"></i> Process Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($medicationCashSale->is_paid)
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle"></i> Payment Completed</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-0">Sale: <strong>{{ $medicationCashSale->sale_number }}</strong></h4>
                                    <p class="text-muted mb-0">
                                        Paid {{ $medicationCashSale->paid_at ? $medicationCashSale->paid_at->diffForHumans() : '' }} 
                                        via {{ ucfirst(str_replace('_', ' ', $medicationCashSale->payment_method)) }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h3 class="text-success mb-0">TSh {{ number_format($medicationCashSale->amount_paid, 2) }}</h3>
                                    @if($medicationCashSale->amount_paid > $medicationCashSale->final_amount)
                                    <small class="text-muted">Change: TSh {{ number_format($medicationCashSale->amount_paid - $medicationCashSale->final_amount, 2) }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($medicationCashSale->status === 'cancelled')
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-times-circle"></i> Sale Cancelled</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-0">Sale: <strong>{{ $medicationCashSale->sale_number }}</strong></h4>
                                    <p class="text-muted mb-0">
                                        Cancelled {{ $medicationCashSale->cancelled_at ? $medicationCashSale->cancelled_at->diffForHumans() : '' }}
                                        @if($medicationCashSale->cancelled_by_user)
                                        by {{ $medicationCashSale->cancelled_by_user->name }}
                                        @endif
                                    </p>
                                    @if($medicationCashSale->cancellation_reason)
                                    <p class="text-muted mb-0 mt-2">
                                        <strong>Reason:</strong> {{ $medicationCashSale->cancellation_reason }}
                                    </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-end">
                                    <h3 class="text-danger mb-0">CANCELLED</h3>
                                    <small class="text-muted">Original Amount: TSh {{ number_format($medicationCashSale->final_amount, 2) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Dispensing Status for All Users -->
            @if($medicationCashSale->isCompleted())
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-pills"></i> All Medications Dispensed</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-0">Sale: <strong>{{ $medicationCashSale->sale_number }}</strong></h4>
                                    <p class="text-muted mb-0">
                                        All medications dispensed {{ $medicationCashSale->dispensed_at ? $medicationCashSale->dispensed_at->diffForHumans() : '' }}
                                        @if($medicationCashSale->dispenser)
                                        by <strong>{{ $medicationCashSale->dispenser->name }}</strong>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-success rounded-pill px-3 py-2" style="font-size: 1.1rem;">
                                        <i class="fas fa-check-circle"></i> COMPLETED
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($medicationCashSale->is_paid && $medicationCashSale->dispensed_at)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-pills"></i> Partially Dispensed</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-0">Sale: <strong>{{ $medicationCashSale->sale_number }}</strong></h4>
                                    @php
                                        $dispensedCount = $medicationCashSale->items->where('status', 'dispensed')->count();
                                        $totalCount = $medicationCashSale->items->count();
                                    @endphp
                                    <p class="text-muted mb-0">
                                        {{ $dispensedCount }} of {{ $totalCount }} medications dispensed
                                        @if($medicationCashSale->dispenser)
                                        by <strong>{{ $medicationCashSale->dispenser->name }}</strong>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-info rounded-pill px-3 py-2" style="font-size: 1.1rem;">
                                        <i class="fas fa-clock"></i> IN PROGRESS
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sale Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Sale Information</h6>
                    <span class="badge bg-{{ $medicationCashSale->status_color }} rounded-pill">
                        {{ $medicationCashSale->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Sale Number:</strong></td>
                                    <td>{{ $medicationCashSale->sale_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $medicationCashSale->sale_type == 'otc' ? 'Over-the-Counter' : 'External Prescription' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Patient Category:</strong></td>
                                    <td>{{ $medicationCashSale->patientCategory->description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>{{ $medicationCashSale->creator->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created At:</strong></td>
                                    <td>{{ $medicationCashSale->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($medicationCashSale->dispensed_at)
                                <tr>
                                    <td><strong>Dispensed By:</strong></td>
                                    <td>{{ $medicationCashSale->dispenser->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dispensed At:</strong></td>
                                    <td>{{ $medicationCashSale->dispensed_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                                
                                @if($medicationCashSale->paid_at)
                                <tr>
                                    <td><strong>Paid By:</strong></td>
                                    <td>{{ $medicationCashSale->cashier->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Paid At:</strong></td>
                                    <td>{{ $medicationCashSale->paid_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ ucwords(str_replace('_', ' ', $medicationCashSale->payment_method)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                                
                                @if($medicationCashSale->cancelled_at)
                                <tr>
                                    <td><strong>Cancelled By:</strong></td>
                                    <td>{{ $medicationCashSale->cancelled_by_user->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cancelled At:</strong></td>
                                    <td>{{ $medicationCashSale->cancelled_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($medicationCashSale->cancellation_reason)
                                <tr>
                                    <td><strong>Cancellation Reason:</strong></td>
                                    <td>
                                        <div class="text-danger">
                                            {{ $medicationCashSale->cancellation_reason }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($medicationCashSale->external_prescription_details)
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <h6><strong>External Prescription Details:</strong></h6>
                            <p class="text-muted">{{ $medicationCashSale->external_prescription_details }}</p>
                        </div>
                    </div>
                    @endif

                    @if($medicationCashSale->notes)
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <h6><strong>Notes:</strong></h6>
                            <p class="text-muted">{{ $medicationCashSale->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Medications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Medications</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Quantity</th>
                                    <th>Frequency</th>
                                    <th>Route</th>
                                    <th>Duration</th>
                                    <th>Type</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Batches Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medicationCashSale->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->medication->generic_name }}</strong>
                                        @if($item->medication->strength)
                                        <br><small class="text-muted">{{ $item->medication->strength }}</small>
                                        @endif
                                        @if($item->instructions)
                                        <br><small class="text-info"><i>{{ $item->instructions }}</i></small>
                                        @endif
                                    </td>
                                    <td>{{ $item->dosage ?: '-' }}</td>
                                    <td>
                                        {{ $item->quantity }}
                                        @if($item->dispensing_type === 'individual' && $item->quantity_dispensed > 0)
                                        <br><small class="text-success">Dispensed: {{ $item->quantity_dispensed }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->medicationFrequency->frequency_name ?? '-' }}</td>
                                    <td>{{ $item->administrationRoute->route_name ?? '-' }}</td>
                                    <td>{{ $item->duration_days ? $item->duration_days . ' days' : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $item->dispensing_type === 'individual' ? 'bg-info' : 'bg-secondary' }}">
                                            {{ ucfirst($item->dispensing_type) }}
                                        </span>
                                    </td>
                                    <td>TSh {{ number_format($item->unit_price, 2) }}</td>
                                    <td>TSh {{ number_format($item->total_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status_color }}">
                                            {{ ucwords($item->status) }}
                                        </span>
                                        @if($item->dispensed_at)
                                        <br><small class="text-muted">{{ $item->dispensed_at->format('M d, Y H:i') }}</small>
                                        @elseif($item->status === 'cancelled' && $item->notes)
                                        @php
                                            $cancellationData = json_decode($item->notes, true);
                                        @endphp
                                        @if($cancellationData && isset($cancellationData['cancelled_at']))
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($cancellationData['cancelled_at'])->format('M d, Y H:i') }}</small>
                                        @if(isset($cancellationData['reason']))
                                        <br><small class="text-danger" title="{{ $cancellationData['reason'] }}">{{ Str::limit($cancellationData['reason'], 30) }}</small>
                                        @endif
                                        @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->batches_used)
                                        <div class="text-small">
                                            @foreach($item->batches_used as $batch)
                                            <div>
                                                <strong>{{ $batch['batch_number'] }}</strong>: {{ $batch['quantity'] }} units
                                                <br><small class="text-muted">Exp: {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('M Y') }}</small>
                                            </div>
                                            @if(!$loop->last)<hr class="my-1">@endif
                                            @endforeach
                                        </div>
                                        @else
                                        <span class="text-muted">Not dispensed</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- If item is dispensed (has dispensed_at timestamp), show completion feedback --}}
                                        @if($item->dispensed_at)
                                            <div class="text-success">
                                                <i class="fas fa-check-circle"></i> Dispensed
                                                <br><small class="text-muted">{{ $item->dispensed_at->format('M d, H:i') }}</small>
                                            </div>
                                        @elseif($medicationCashSale->canBeDispensed())
                                            {{-- Dispensing Actions - For pharmacists and other authorized roles (not cashiers/receptionists) --}}
                                            @if(Auth::user()->isPharmacist() || Auth::user()->isAdmin())
                                                @if($item->status === 'pending' && $item->canBeDispensed())
                                                    @php
                                                        $stockData = $stockInfo[$item->id] ?? ['available' => 0, 'sufficient' => false];
                                                        $availableStock = $stockData['available'];
                                                        $hasSufficientStock = $stockData['sufficient'];
                                                    @endphp
                                                    @if($hasSufficientStock)
                                                        <form method="POST" action="{{ route('medication-cash-sales.dispense-item', $item) }}" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="quantity_to_dispense" value="{{ $item->quantity }}">
                                                            <button type="submit" class="btn btn-sm btn-success mb-1" onclick="return confirm('Dispense this item?')" title="Dispense Item">
                                                                <i class="fas fa-pills"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-secondary mb-1" disabled title="Insufficient stock: {{ $availableStock }} available, {{ $item->quantity }} required">
                                                            <i class="fas fa-pills"></i>
                                                        </button>
                                                        <br><small class="text-danger">Stock: {{ $availableStock }}/{{ $item->quantity }}</small>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-info mt-1"
                                                                onclick="checkRequisitions({{ $item->medication_id }}, '{{ addslashes($item->medication->generic_name) }}')"
                                                                title="Check / create stock requisition">
                                                            <i class="fas fa-boxes"></i> Request Stock
                                                        </button>
                                                    @endif
                                                @endif
                                                
                                                @if($item->status === 'pending')
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelItemModal{{ $item->id }}" title="Cancel Item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <small class="text-muted">Dispensing handled by pharmacy</small>
                                            @endif
                                        @else
                                            <small class="text-muted">Payment required</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="9" class="text-end">Subtotal:</th>
                                    <th>TSh {{ number_format($medicationCashSale->total_amount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                                @if($medicationCashSale->discount_amount > 0)
                                <tr>
                                    <th colspan="9" class="text-end">Discount:</th>
                                    <th>TSh {{ number_format($medicationCashSale->discount_amount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <th colspan="9" class="text-end">Final Total:</th>
                                    <th>TSh {{ number_format($medicationCashSale->final_amount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <!-- Receptionist/Cashier Payment Section -->
                    @if(Auth::user()->isReceptionist() || Auth::user()->isCashier() || Auth::user()->isAdmin())
                        @if($medicationCashSale->canBePaid())
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Payment Required:</strong> This sale is ready for payment processing.
                        </div>
                        <button type="button" class="btn btn-primary btn-lg mb-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fas fa-money-bill"></i> Process Payment
                        </button>
                        @elseif($medicationCashSale->is_paid)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Payment Complete:</strong> This sale has been paid successfully.
                            @if($medicationCashSale->paid_at)
                            <br><small>Paid on {{ $medicationCashSale->paid_at->format('M d, Y \a\t H:i') }} by {{ $medicationCashSale->cashier->name ?? 'System' }}</small>
                            @endif
                        </div>
                        @endif
                    @endif

                    {{-- Dispense All - For pharmacists and administrators (not cashiers/receptionists) --}}
                    @if($medicationCashSale->canBeDispensed())
                        @if(Auth::user()->isPharmacist() || Auth::user()->isAdmin())
                            @if($hasStockIssues)
                                <button type="button" class="btn btn-secondary btn-lg" disabled title="Insufficient stock for some medications">
                                    <i class="fas fa-pills"></i> Dispense All Medications
                                </button>
                                <small class="text-danger d-block mt-2">
                                    <i class="fas fa-exclamation-triangle"></i> Cannot dispense: Insufficient stock for some medications
                                </small>
                            @else
                                <form method="POST" action="{{ route('medication-cash-sales.dispense', $medicationCashSale) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Dispense medications for this sale?')">
                                        <i class="fas fa-pills"></i> Dispense All Medications
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endif

                    {{-- Administrative Actions - Only for non-cashier/non-receptionist roles --}}
                    @if(!Auth::user()->isCashier() && !Auth::user()->isReceptionist())
                        @if(!$medicationCashSale->is_paid && $medicationCashSale->status != 'cancelled' && !$medicationCashSale->dispensed_at)
                        <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete Sale
                        </button>
                        @endif

                        @if(!$medicationCashSale->isCompleted())
                        <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-ban"></i> Cancel Sale
                        </button>
                        @endif
                    @endif

                    @if($medicationCashSale->isCompleted())
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle"></i> This sale has been completed successfully.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
{{-- @if($medicationCashSale->canBePaid()) --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('medication-cash-sales.process-payment', $medicationCashSale) }}">
                @csrf
                <input type="hidden" name="debug_token" value="{{ csrf_token() }}">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment - {{ $medicationCashSale->sale_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Payment Summary</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">Subtotal:</div>
                                        <div class="col-6 text-end">TSh {{ number_format($medicationCashSale->total_amount, 2) }}</div>
                                    </div>
                                    @if($medicationCashSale->discount_amount > 0)
                                    <div class="row">
                                        <div class="col-6">Discount:</div>
                                        <div class="col-6 text-end">TSh {{ number_format($medicationCashSale->discount_amount, 2) }}</div>
                                    </div>
                                    @endif
                                    <hr>
                                    <div class="row">
                                        <div class="col-6"><strong>Total Due:</strong></div>
                                        <div class="col-6 text-end"><strong>TSh {{ number_format($medicationCashSale->final_amount, 2) }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Payment Method *</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Amount Paid *</label>
                                <input type="number" name="amount_paid" class="form-control" step="0.01" min="{{ $medicationCashSale->final_amount }}" value="{{ $medicationCashSale->final_amount }}" required>
                                <small class="form-text text-muted">Minimum: TSh {{ number_format($medicationCashSale->final_amount, 2) }}</small>
                            </div>
                            
                            @if(Auth::user()->isReceptionist() || Auth::user()->isCashier() || Auth::user()->isAdmin())
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="print_receipt" id="print_receipt" value="1" checked>
                                    <label class="form-check-label" for="print_receipt">
                                        Print Receipt After Payment
                                    </label>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg" id="processPaymentBtn">
                        <i class="fas fa-money-bill"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- @endif --}}

<!-- Delete Sale Modal (for unpaid sales) -->
@if(!Auth::user()->isCashier() && !Auth::user()->isReceptionist() && !$medicationCashSale->is_paid && $medicationCashSale->status != 'cancelled')
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('medication-cash-sales.destroy', $medicationCashSale) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Sale - {{ $medicationCashSale->sale_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action will permanently delete this cash sale and cannot be undone.
                    </div>
                    <p>Are you sure you want to delete this sale?</p>
                    <ul class="text-muted">
                        <li>Sale Number: <strong>{{ $medicationCashSale->sale_number }}</strong></li>
                        <li>Total Amount: <strong>TSh {{ number_format($medicationCashSale->final_amount, 2) }}</strong></li>
                        <li>Status: <strong>{{ ucfirst($medicationCashSale->status) }}</strong></li>
                    </ul>
                    
                    <div class="mb-3 mt-3">
                        <label for="delete_reason">Reason for Deletion *</label>
                        <textarea name="delete_reason" id="delete_reason" class="form-control" rows="3" required 
                                  placeholder="Please provide a reason for deleting this sale..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Cancel Paid Sale Modal (for paid sales) -->
@if(!Auth::user()->isCashier() && !Auth::user()->isReceptionist() && $medicationCashSale->is_paid && $medicationCashSale->status != 'cancelled')
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('medication-cash-sales.cancel-paid', $medicationCashSale) }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Cancel Paid Sale - {{ $medicationCashSale->sale_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This will cancel a paid sale. The sale will be marked as cancelled but financial records will be maintained for auditing purposes.
                    </div>
                    
                    <p>Are you sure you want to cancel this paid sale?</p>
                    <ul class="text-muted">
                        <li>Sale Number: <strong>{{ $medicationCashSale->sale_number }}</strong></li>
                        <li>Total Amount: <strong>TSh {{ number_format($medicationCashSale->final_amount, 2) }}</strong></li>
                        <li>Paid At: <strong>{{ $medicationCashSale->paid_at ? $medicationCashSale->paid_at->format('M d, Y H:i') : 'N/A' }}</strong></li>
                    </ul>
                    
                    <div class="mb-3 mt-3">
                        <label for="cancel_reason">Reason for Cancellation *</label>
                        <textarea name="cancel_reason" id="cancel_reason" class="form-control" rows="4" required 
                                  placeholder="Please provide a detailed reason for cancelling this paid sale..."></textarea>
                        <small class="form-text text-muted">This reason will be recorded for audit purposes.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="refund_required">Refund Required?</label>
                        <select name="refund_required" id="refund_required" class="form-control">
                            <option value="no">No Refund Required</option>
                            <option value="yes">Refund Required</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban"></i> Cancel Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Cancel Item Modals -->
<!-- Requisitions Modal -->
<div class="modal fade" id="requisitionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-boxes"></i> Open Requisitions &mdash; Main Pharmacy
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="csRequisitionsLoading" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading...</p>
                </div>
                <div id="csRequisitionsContent" style="display:none">
                    <p id="csRequisitionsEmpty" class="text-center text-muted py-3" style="display:none">
                        <i class="fas fa-inbox"></i> No open requisitions from Main Pharmacy.
                    </p>
                    <table class="table table-sm table-bordered" id="csRequisitionsTable" style="display:none">
                        <thead class="table-light">
                            <tr>
                                <th>Req #</th>
                                <th>From</th>
                                <th>Requested By</th>
                                <th>Date</th>
                                <th>Required By</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="csRequisitionsTableBody"></tbody>
                    </table>
                    <div id="csReqItemsPanel" class="card card-body bg-light mt-2" style="display:none">
                        <strong id="csReqItemsPanelTitle" class="d-block mb-2"></strong>
                        <ul id="csReqItemsList" class="mb-0 ps-3"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="csOpenNewRequisitionModal()">
                    <i class="fas fa-plus"></i> New Requisition
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add to Requisition Modal -->
<div class="modal fade" id="csAddItemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="csAddItemModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Medication: <strong id="csAddItemMedName"></strong></p>
                <div class="mb-3">
                    <label for="csAddItemQty">Quantity <span class="text-danger">*</span></label>
                    <input type="number" id="csAddItemQty" class="form-control" min="1" step="1" placeholder="Enter quantity">
                    <div id="csAddItemError" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="csAddItemCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="csAddItemSubmitBtn" onclick="csSubmitAddItem()">
                    <span id="csAddItemBtnText">Add Item</span>
                    <span id="csAddItemSpinner" class="spinner-border spinner-border-sm ms-1" style="display:none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@foreach($medicationCashSale->items as $item)
@if($item->status === 'pending')
<div class="modal fade" id="cancelItemModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('medication-cash-sales.cancel-item', $item) }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Cancel Item - {{ $item->medication->generic_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This will cancel this medication item from the sale.
                    </div>
                    
                    <p>Are you sure you want to cancel this item?</p>
                    <ul class="text-muted">
                        <li>Medication: <strong>{{ $item->medication->generic_name }}</strong></li>
                        <li>Quantity: <strong>{{ $item->quantity }}</strong></li>
                        <li>Unit Price: <strong>TSh {{ number_format($item->unit_price, 2) }}</strong></li>
                        <li>Total: <strong>TSh {{ number_format($item->total_price, 2) }}</strong></li>
                    </ul>
                    
                    <div class="mb-3 mt-3">
                        <label for="cancel_reason_{{ $item->id }}">Reason for Cancellation *</label>
                        <textarea name="cancel_reason" id="cancel_reason_{{ $item->id }}" class="form-control" rows="3" required 
                                  placeholder="Please provide a reason for cancelling this item..."></textarea>
                        <small class="form-text text-muted">This reason will be stored for record keeping.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Item</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-times"></i> Cancel Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection

@section('scripts')
<script>
// ── Requisitions helpers (reuse pharmacist routes) ──────────────────────────
const csOpenRequisitionsUrl = '{{ route("pharmacist.requisitions.open") }}';
const csAddItemBaseUrl      = '{{ url("/pharmacist/requisitions") }}';
const csNewWithItemUrl      = '{{ route("pharmacist.requisitions.new-with-item") }}';
const csCsrfToken           = '{{ csrf_token() }}';

let _csMedId   = null;
let _csMedName = '';
let _csTargetReqId = null;

const csBsModal = (id) => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

function checkRequisitions(medicationId, medicationName) {
    _csMedId   = medicationId;
    _csMedName = medicationName;
    document.getElementById('csRequisitionsLoading').style.display = 'block';
    document.getElementById('csRequisitionsContent').style.display = 'none';
    csBsModal('requisitionsModal').show();

    $.get(csOpenRequisitionsUrl)
        .done(function(data) {
            document.getElementById('csRequisitionsLoading').style.display = 'none';
            document.getElementById('csRequisitionsContent').style.display = 'block';

            const reqs = data.requisitions;
            if (!reqs.length) {
                document.getElementById('csRequisitionsEmpty').style.display = 'block';
                document.getElementById('csRequisitionsTable').style.display = 'none';
                return;
            }
            document.getElementById('csRequisitionsEmpty').style.display = 'none';
            document.getElementById('csRequisitionsTable').style.display = 'table';

            const statusBadge = (s) => ({
                draft:            '<span class="badge bg-secondary">Draft</span>',
                submitted:        '<span class="badge bg-info">Submitted</span>',
                verified:         '<span class="badge bg-primary">Verified</span>',
                approved:         '<span class="badge bg-warning">Approved</span>',
                partially_issued: '<span class="badge bg-warning">Part. Issued</span>',
            }[s] || '<span class="badge bg-light">' + s + '</span>');

            const rows = reqs.map(r => {
                const existingItem = r.medication_items.find(i => i.medication_id == _csMedId);
                const alreadyIn = !!existingItem;
                const alreadyBadge = alreadyIn
                    ? ' <span class="badge bg-warning" title="Already requested: ' + existingItem.requested_quantity + ' units">Already in req</span>'
                    : '';
                const addBtn = alreadyIn
                    ? '<button class="btn btn-sm btn-warning me-1" onclick="csOpenAddToReq(' + r.id + ', \'' + r.requisition_number.replace(/\'/g, "\\'") + '\', ' + existingItem.requested_quantity + ')">' +
                        '<i class="fas fa-plus"></i> Add more</button>'
                    : '<button class="btn btn-sm btn-success me-1" onclick="csOpenAddToReq(' + r.id + ', \'' + r.requisition_number.replace(/\'/g, "\\'") + '\', 0)">' +
                        '<i class="fas fa-plus"></i> Add to Req</button>';
                const itemsCell = r.medication_items.length
                    ? '<button class="btn btn-sm btn-link p-0" onclick="csShowReqItems(' + r.id + ', \'' + r.requisition_number.replace(/\'/g, "\\'") + '\')">'
                        + r.items_count + ' item(s)</button>'
                    : '0';
                return '<tr>' +
                    '<td>' + r.requisition_number + alreadyBadge + '</td>' +
                    '<td>' + r.requesting_location + '</td>' +
                    '<td>' + r.requested_by + '</td>' +
                    '<td>' + (r.requisition_date || '-') + '</td>' +
                    '<td>' + (r.required_date || '-') + '</td>' +
                    '<td>' + itemsCell + '</td>' +
                    '<td>' + statusBadge(r.status) + '</td>' +
                    '<td class="text-nowrap">' + addBtn +
                        '<a href="' + r.show_url + '" class="btn btn-sm btn-outline-primary" target="_blank">View</a>' +
                    '</td></tr>';
            }).join('');
            document.getElementById('csRequisitionsTableBody').innerHTML = rows;
            window._csReqData = reqs;
        })
        .fail(function() {
            document.getElementById('csRequisitionsLoading').style.display = 'none';
            document.getElementById('csRequisitionsContent').style.display = 'block';
            document.getElementById('csRequisitionsEmpty').style.display = 'block';
            document.getElementById('csRequisitionsEmpty').textContent = 'Error loading requisitions.';
        });
}

function csShowReqItems(reqId, reqNumber) {
    const req = (window._csReqData || []).find(r => r.id == reqId);
    const panel = document.getElementById('csReqItemsPanel');
    const title = document.getElementById('csReqItemsPanelTitle');
    const list  = document.getElementById('csReqItemsList');
    if (!req || !req.medication_items.length) { panel.style.display = 'none'; return; }
    title.textContent = 'Items in ' + reqNumber + ':';
    list.innerHTML = req.medication_items.map(i =>
        '<li>' + i.name + ' &mdash; Requested: <strong>' + i.requested_quantity + '</strong>' +
        (i.issued_quantity > 0 ? ', Issued: <strong>' + i.issued_quantity + '</strong>' : '') +
        (i.medication_id == _csMedId ? ' <span class="badge bg-warning">Current med</span>' : '') +
        '</li>'
    ).join('');
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function csOpenAddToReq(reqId, reqNumber, existingQty) {
    _csTargetReqId = reqId;
    document.getElementById('csAddItemModalTitle').textContent = 'Add to Requisition: ' + reqNumber;
    document.getElementById('csAddItemMedName').textContent = _csMedName;
    document.getElementById('csAddItemQty').value = '';
    document.getElementById('csAddItemQty').classList.remove('is-invalid');
    document.getElementById('csAddItemBtnText').textContent = 'Add Item';
    let warningEl = document.getElementById('csAddItemWarning');
    if (!warningEl) {
        warningEl = document.createElement('div');
        warningEl.id = 'csAddItemWarning';
        warningEl.className = 'alert alert-warning py-1 px-2 mt-2 mb-0';
        document.getElementById('csAddItemQty').parentNode.insertAdjacentElement('afterend', warningEl);
    }
    if (existingQty > 0) {
        warningEl.style.display = 'block';
        warningEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <strong>' + _csMedName + '</strong> is already in this requisition with <strong>' + existingQty + '</strong> unit(s). Submitting will add to the existing quantity.';
    } else {
        warningEl.style.display = 'none';
    }
    document.getElementById('csAddItemCancelBtn').onclick = function() { csBsModal('csAddItemModal').hide(); csBsModal('requisitionsModal').show(); };
    csBsModal('requisitionsModal').hide();
    csBsModal('csAddItemModal').show();
}

function csOpenNewRequisitionModal() {
    _csTargetReqId = null;
    document.getElementById('csAddItemModalTitle').textContent = 'New Requisition';
    document.getElementById('csAddItemMedName').textContent = _csMedName;
    document.getElementById('csAddItemQty').value = '';
    document.getElementById('csAddItemQty').classList.remove('is-invalid');
    document.getElementById('csAddItemBtnText').textContent = 'Create Requisition';
    const warningEl = document.getElementById('csAddItemWarning');
    if (warningEl) warningEl.style.display = 'none';
    document.getElementById('csAddItemCancelBtn').onclick = function() { csBsModal('csAddItemModal').hide(); csBsModal('requisitionsModal').show(); };
    csBsModal('requisitionsModal').hide();
    csBsModal('csAddItemModal').show();
}

function csSubmitAddItem() {
    const qty = parseInt(document.getElementById('csAddItemQty').value);
    const qtyInput = document.getElementById('csAddItemQty');
    const errDiv   = document.getElementById('csAddItemError');
    if (!qty || qty < 1) {
        qtyInput.classList.add('is-invalid');
        errDiv.textContent = 'Please enter a valid quantity (minimum 1).';
        return;
    }
    qtyInput.classList.remove('is-invalid');
    const url  = _csTargetReqId ? (csAddItemBaseUrl + '/' + _csTargetReqId + '/add-item') : csNewWithItemUrl;
    const btn  = document.getElementById('csAddItemSubmitBtn');
    const spin = document.getElementById('csAddItemSpinner');
    btn.disabled = true;
    spin.style.display = 'inline-block';
    $.ajax({
        url: url,
        method: 'POST',
        data: { medication_id: _csMedId, quantity: qty, _token: csCsrfToken },
        success: function(res) {
            btn.disabled = false;
            spin.style.display = 'none';
            csBsModal('csAddItemModal').hide();
            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
            toast.style.cssText = 'bottom:20px;right:20px;z-index:9999;min-width:280px';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + res.message +
                (res.show_url ? ' <a href="' + res.show_url + '" target="_blank" class="alert-link ms-1">View</a>' : '') +
                '<button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>';
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 5000);
        },
        error: function(xhr) {
            btn.disabled = false;
            spin.style.display = 'none';
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Request failed.';
            qtyInput.classList.add('is-invalid');
            errDiv.textContent = msg;
        }
    });
}
// ─────────────────────────────────────────────────────────────────────────────

$(document).ready(function() {
    console.log('Document ready - payment form loaded');
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Debug: Check if payment form exists
    const paymentForm = $('#paymentModal form');
    console.log('Payment form found:', paymentForm.length > 0);
    if (paymentForm.length > 0) {
        console.log('Form action:', paymentForm.attr('action'));
        console.log('Form method:', paymentForm.attr('method'));
    }
    
    // Add form submission debugging
    $('#paymentModal form').on('submit', function(e) {
        console.log('Payment form submission started');
        console.log('Form action:', $(this).attr('action'));
        console.log('CSRF token:', $('input[name="_token"]').val());
        console.log('Payment method:', $('select[name="payment_method"]').val());
        console.log('Amount paid:', $('input[name="amount_paid"]').val());
        
        // Check if form is valid
        const paymentMethod = $('select[name="payment_method"]').val();
        const amountPaid = $('input[name="amount_paid"]').val();
        
        if (!paymentMethod) {
            console.error('Payment method not selected');
            alert('Please select a payment method');
            e.preventDefault();
            return false;
        }
        
        if (!amountPaid || parseFloat(amountPaid) <= 0) {
            console.error('Invalid amount paid');
            alert('Please enter a valid payment amount');
            e.preventDefault();
            return false;
        }
        
        console.log('Form validation passed, allowing normal submission...');
        
        // Disable submit button to prevent double submission
        $('#processPaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Allow normal form submission
        return true;
    });
    
    // Calculate change for cash payments
    $('select[name="payment_method"]').change(function() {
        if ($(this).val() === 'cash') {
            $('input[name="amount_paid"]').on('input', function() {
                const amountPaid = parseFloat($(this).val()) || 0;
                const totalDue = {{ $medicationCashSale->final_amount }};
                const change = amountPaid - totalDue;
                
                if (change > 0) {
                    if (!$('#changeDisplay').length) {
                        $('<div id="changeDisplay" class="mt-2 alert alert-info">Change: TSh ' + change.toFixed(2) + '</div>').insertAfter($(this).parent());
                    } else {
                        $('#changeDisplay').text('Change: TSh ' + change.toFixed(2));
                    }
                } else {
                    $('#changeDisplay').remove();
                }
            });
        } else {
            $('#changeDisplay').remove();
        }
    });

    // Receipt printing functionality
    @if(session('print_receipt'))
    $(document).ready(function() {
        // Auto-trigger print dialog for receipt
        if (confirm('Payment processed successfully! Would you like to print the receipt now?')) {
            printReceipt();
        }
    });
    
    function printReceipt() {
        var receiptData = @json(session('receipt_data', []));
        var receiptWindow = window.open('', '_blank', 'width=300,height=400,scrollbars=yes');
        
        var receiptContent = `
        <html>
        <head>
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; width: 250px; margin: 0; padding: 10px; }
                .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
                .row { display: flex; justify-content: space-between; margin: 5px 0; }
                .total { font-weight: bold; border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; }
                .footer { text-align: center; margin-top: 20px; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h3>PAYMENT RECEIPT</h3>
                <p>{{ config('app.name', 'Medical Practice') }}</p>
                <p>${new Date().toLocaleString()}</p>
            </div>
            <div class="row">
                <span>Sale Number:</span>
                <span>${receiptData.sale_number || ''}</span>
            </div>
            <div class="row">
                <span>Payment Method:</span>
                <span>${receiptData.payment_method ? receiptData.payment_method.replace('_', ' ').toUpperCase() : ''}</span>
            </div>
            <div class="total">
                <div class="row">
                    <span>Total Due:</span>
                    <span>TSh ${receiptData.total ? parseFloat(receiptData.total).toFixed(2) : '0.00'}</span>
                </div>
                <div class="row">
                    <span>Amount Paid:</span>
                    <span>TSh ${receiptData.paid ? parseFloat(receiptData.paid).toFixed(2) : '0.00'}</span>
                </div>
                ${receiptData.change > 0 ? `<div class="row"><span>Change:</span><span>TSh ${parseFloat(receiptData.change).toFixed(2)}</span></div>` : ''}
            </div>
            <div class="footer">
                <p>Thank you for your payment!</p>
                <p>Keep this receipt for your records</p>
            </div>
        </body>
        </html>
        `;
        
        receiptWindow.document.write(receiptContent);
        receiptWindow.document.close();
        receiptWindow.focus();
        receiptWindow.print();
    }
    @endif
});
</script>
@endsection
