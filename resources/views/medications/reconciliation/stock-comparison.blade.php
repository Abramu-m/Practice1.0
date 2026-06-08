@extends('layouts.app_main_layout')

@section('page_title', 'Stock Comparison')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-balance-scale text-primary me-2"></i>
                        Stock Comparison
                    </h1>
                    <p class="text-muted mb-0">Compare medication record balance against distributed location stock</p>
                </div>
                <a href="{{ route('medications.reconciliation.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Medication Selector --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('medications.reconciliation.comparison') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Select Medication</label>
                            <select name="medication_id" class="form-select" required>
                                <option value="">— choose a medication —</option>
                                @foreach($medications as $med)
                                    <option value="{{ $med->id }}"
                                        {{ optional($selectedMedication)->id == $med->id ? 'selected' : '' }}>
                                        {{ $med->name }}{{ $med->generic_name ? ' (' . $med->generic_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Run Comparison
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($comparison && $selectedMedication)

        {{-- Summary Cards --}}
        @php
            $severityColour = match($comparison['severity']) {
                'high'   => 'danger',
                'medium' => 'warning',
                default  => 'success',
            };
            $statusLabel = match($comparison['status']) {
                'balanced'       => 'Balanced',
                'ledger_excess'  => 'Ledger Excess',
                'location_excess'=> 'Location Excess',
                default          => 'Unknown',
            };
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Medication Record</div>
                        <div class="display-6 fw-bold text-primary">{{ number_format($comparison['ledger_quantity'], 2) }}</div>
                        <div class="text-muted small">stock_quantity</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Total Location Stock</div>
                        <div class="display-6 fw-bold text-info">{{ number_format($comparison['total_location_stock'], 2) }}</div>
                        <div class="text-muted small">sum across all locations</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Difference</div>
                        <div class="display-6 fw-bold text-{{ $comparison['difference'] == 0 ? 'success' : $severityColour }}">
                            {{ $comparison['difference'] > 0 ? '+' : '' }}{{ number_format($comparison['difference'], 2) }}
                        </div>
                        <div class="text-muted small">record − locations</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Status</div>
                        <div class="mt-2">
                            <span class="badge bg-{{ $severityColour }} fs-6 px-3 py-2">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        @if(!$comparison['is_balanced'])
                            <div class="text-muted small mt-2">Severity: {{ ucfirst($comparison['severity']) }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Alert --}}
        @if($comparison['is_balanced'])
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Balanced.</strong> The medication record matches the total stock held across all locations.
            </div>
        @else
            <div class="alert alert-{{ $severityColour }} mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Discrepancy detected.</strong>
                @if($comparison['status'] === 'ledger_excess')
                    The medication record shows <strong>{{ number_format(abs($comparison['difference']), 2) }}</strong>
                    more units than are physically distributed across locations.
                    This may indicate unallocated stock or a missing location entry.
                @else
                    Location stock totals exceed the medication record by
                    <strong>{{ number_format(abs($comparison['difference']), 2) }}</strong> units.
                    This may indicate a double-allocation or a missed outward movement.
                @endif
            </div>
        @endif

        {{-- Location Breakdown Table --}}
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-warehouse me-2"></i>Location Breakdown</h5>
                        <span class="badge bg-secondary">{{ count($comparison['location_breakdown']) }} location(s)</span>
                    </div>
                    <div class="card-body p-0">
                        @if(count($comparison['location_breakdown']) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Location</th>
                                            <th class="text-end">Available</th>
                                            <th class="text-end">Reserved</th>
                                            <th class="text-end">Total</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comparison['location_breakdown'] as $loc)
                                            @php
                                                $locTotal = ($loc['available_quantity'] ?? 0) + ($loc['reserved_quantity'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td>{{ $loc['location_name'] }}</td>
                                                <td class="text-end">{{ number_format($loc['available_quantity'] ?? 0, 2) }}</td>
                                                <td class="text-end text-muted">{{ number_format($loc['reserved_quantity'] ?? 0, 2) }}</td>
                                                <td class="text-end fw-bold">{{ number_format($locTotal, 2) }}</td>
                                                <td class="text-muted small">{{ $loc['last_updated'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td><strong>Total</strong></td>
                                            <td class="text-end fw-bold">
                                                {{ number_format(collect($comparison['location_breakdown'])->sum('available_quantity'), 2) }}
                                            </td>
                                            <td class="text-end fw-bold text-muted">
                                                {{ number_format(collect($comparison['location_breakdown'])->sum('reserved_quantity'), 2) }}
                                            </td>
                                            <td class="text-end fw-bold text-info">
                                                {{ number_format($comparison['total_location_stock'], 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No location stock entries found for this medication.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions Panel --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body d-grid gap-2">
                        @if(!$comparison['is_balanced'])
                            <a href="{{ route('medications.reconciliation.corrections.form') }}?medication_id={{ $selectedMedication->id }}"
                               class="btn btn-warning">
                                <i class="fas fa-wrench me-2"></i>Apply Manual Correction
                            </a>
                        @endif
                        <a href="{{ route('medications.reconciliation.medications.validate', $selectedMedication->id) }}"
                           class="btn btn-outline-info">
                            <i class="fas fa-check-double me-2"></i>Full Balance Validation
                        </a>
                        <a href="{{ route('medications.reconciliation.audit') }}?medication_id={{ $selectedMedication->id }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-history me-2"></i>Movement History
                        </a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Medication Info</h6>
                    </div>
                    <div class="card-body small">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">Name</td>
                                <td>{{ $selectedMedication->name }}</td>
                            </tr>
                            @if($selectedMedication->generic_name)
                            <tr>
                                <td class="text-muted">Generic</td>
                                <td>{{ $selectedMedication->generic_name }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Record Qty</td>
                                <td class="fw-bold">{{ number_format($selectedMedication->stock_quantity, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Reorder Level</td>
                                <td>{{ $selectedMedication->reorder_level ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    @elseif(request('medication_id'))
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>Medication not found.
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-balance-scale fa-4x mb-3 opacity-25"></i>
            <h5>Select a medication above to run a comparison</h5>
            <p>This tool shows how the medication record balance is distributed across store locations and flags any variance.</p>
        </div>
    @endif

</div>
@endsection
