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
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th>Route</th>
                    <th>Status</th>
                    <th>Action</th>
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
                    <td>${{ number_format($prescription->unit_price ?? 0, 2) }}</td>
                    <td class="fw-bold text-success">${{ number_format($prescription->total_price ?? 0, 2) }}</td>
                    <td>{{ $prescription->administrationRoute->route_name ?? 'PO' }}</td>
                    <td>
                        <span class="badge bg-{{ 
                            $prescription->status === 'dispensed' ? 'success' : 
                            ($prescription->status === 'prescribed' ? 'primary' : 'secondary') 
                        }}">
                            {{ ucfirst($prescription->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="updatePrescriptionStatus({{ $prescription->id }})"
                                    title="Update Status">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="deletePrescription({{ $prescription->id }})"
                                    title="Delete Prescription">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="6" class="text-end">Total Prescription Cost:</th>
                    <th class="fw-bold text-primary">
                        ${{ number_format($prescriptions->sum('total_price'), 2) }}
                    </th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <p class="text-muted mb-3">No prescriptions added yet.</p>
@endif
