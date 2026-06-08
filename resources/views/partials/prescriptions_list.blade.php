@if($prescriptions->count() > 0)
    <div style="overflow-x: hidden;"> {{-- Prevents scrollbars while keeping formatting tidy --}}
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Medication</th>
                    <th>Instructions & Regime</th>
                    <th>Qty / Route</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prescriptions as $prescription)
                    <tr>
                        <!-- Medication Details -->
                        <td>
                            <div class="fw-bold">
                                {{ $prescription->medication->generic_name ?? $prescription->medication->name ?? 'N/A' }}
                            </div>
                            @if($prescription->medication && $prescription->medication->brand_name)
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    ({{ $prescription->medication->brand_name }})
                                </small>
                            @endif
                        </td>

                        <!-- Dosage & Instructions -->
                        <td>
                            <div>
                                <span class="fw-semibold text-primary">{{ $prescription->dosage }}</span> | 
                                {{ $prescription->frequency->frequency_name ?? 'N/A' }} | 
                                <span class="text-nowrap">{{ $prescription->duration_days ?? $prescription->duration }} days</span>
                            </div>
                            @if($prescription->instructions)
                                <small class="text-muted d-block mt-1 text-wrap" style="max-width: 300px;">
                                    <i class="fas fa-info-circle text-secondary"></i> {{ Str::limit($prescription->instructions, 60) }}
                                </small>
                            @endif
                        </td>

                        <!-- Quantity and Route -->
                        <td>
                            <div><span class="text-muted">Qty:</span> {{ $prescription->quantity }}</div>
                            <small class="text-muted d-block">Route: {{ $prescription->administrationRoute->route_name ?? 'PO' }}</small>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            <span class="badge bg-{{ 
                                $prescription->status === 'dispensed' ? 'success' : 
                                ($prescription->status === 'prescribed' ? 'primary' : 'secondary') 
                            }}">
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted text-center py-3">No prescriptions added yet.</p>
@endif
