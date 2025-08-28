@if($investigations->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead class="table-light">
                <tr>
                    <th>Service</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($investigations as $investigation)
                <tr>
                    <td>
                        <strong>{{ $investigation->medicalService->name ?? 'N/A' }}</strong><br>
                        <small class="text-muted">Code: {{ $investigation->medicalService->code ?? '' }}</small>
                    </td>
                    <td>{{ $investigation->quantity }}</td>
                    <td>
                        @if($investigation->unit_price > 0)
                            TSh {{ number_format($investigation->unit_price, 2) }}
                        @else
                            <span class="text-muted">No price set</span>
                        @endif
                    </td>
                    <td>
                        @if($investigation->total_price > 0)
                            <strong>TSh {{ number_format($investigation->total_price, 2) }}</strong>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ 
                            $investigation->status === 'completed' ? 'success' : 
                            ($investigation->status === 'in_progress' ? 'warning' : 'secondary') 
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $investigation->status)) }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $investigation->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="viewInvestigation({{ $investigation->id }})"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(!$investigation->is_paid)
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="removeInvestigation({{ $investigation->id }})"
                                    title="Remove Investigation">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="3" class="text-end">Total Investigations Cost:</th>
                    <th class="fw-bold">
                        @php
                            $totalCost = $investigations->sum('total_price');
                        @endphp
                        TSh {{ number_format($totalCost, 2) }}
                    </th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <p class="text-muted">No investigations ordered yet.</p>
@endif
