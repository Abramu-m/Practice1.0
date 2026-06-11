@if($prescriptions->count() > 0)
    <div class="table-responsive mb-3">
        <table class="table table-sm table-striped">
            <thead class="table-light">
                <tr>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Qty</th>
                    <th>Cash</th>
                    <th>Insurance</th>
                    <th>Route</th>
                    <th>Status</th>
                    @if($showActions ?? true)
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($prescriptions as $prescription)
                <tr>
                    <td>
                        <strong>{{ $prescription->medication->generic_name ?? $prescription->medication->name ?? 'N/A' }}</strong>
                        @if($prescription->medication && $prescription->medication->brand_name)
                            <br><small class="text-muted">{{ $prescription->medication->brand_name }}</small>
                        @endif
                        @if($prescription->status === 'dispensed' && $prescription->dispensed_at)
                            <br><small class="text-success">
                                <i class="fas fa-check"></i> Dispensed {{ $prescription->dispensed_at->format('d/m/Y H:i') }}
                            </small>
                        @endif
                    </td>
                    <td>{{ $prescription->dosage }}</td>
                    <td>{{ $prescription->frequency->frequency_name ?? 'N/A' }}</td>
                    <td>{{ $prescription->duration_days ?? $prescription->duration }} days</td>
                    <td>{{ $prescription->quantity }}</td>
                    <td>Tsh {{ number_format($prescription->cash_amount ?? 0, 2) }}</td>
                    <td class="fw-bold text-success">Tsh {{ number_format($prescription->insurance_covered_amount ?? 0, 2) }}</td>
                    <td>{{ $prescription->administrationRoute->route_name ?? 'PO' }}</td>
                    <td>
                        <span class="badge bg-{{
                            $prescription->status === 'dispensed' ? 'success' :
                            ($prescription->status === 'prescribed' ? 'primary' : 'secondary')
                        }}">
                            {{ ucfirst($prescription->status) }}
                        </span>
                    </td>
                    @if($showActions ?? true)
                        <td>
                            @if(!$prescription->is_paid)
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="updatePrescriptionStatus({{ $prescription->id }})"
                                        title="Update Status">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deletePrescription({{ $prescription->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            @endif
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="2" class="text-end">Total Prescription Cost:</th>
                    <th class="text-end">Cash:</th>
                    <th colspan="2" class="fw-bold text-primary">
                        Tsh {{ number_format($prescriptions->sum('cash_amount'), 2) }}
                    </th>
                    <th class="text-end">Covered:</th>
                    <th colspan="2" class="fw-bold text-primary">
                        Tsh {{ number_format($prescriptions->sum('insurance_covered_amount'), 2) }}
                    </th>
                    <th colspan="{{ ($showActions ?? true) ? 2 : 1 }}"></th>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <p class="text-muted mb-3">No prescriptions added yet.</p>
@endif
