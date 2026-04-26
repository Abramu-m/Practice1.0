@if($prescriptions->count() > 0)
    @foreach($prescriptions as $prescription)
        <div class="prescription-item">
            <div class="medication-name">
                {{ $prescription->medication->generic_name ?? $prescription->medication->name ?? 'N/A' }}
            </div>
            @if($prescription->medication && $prescription->medication->brand_name)
                <div class="text-muted" style="font-size: 0.75rem;">
                    {{ $prescription->medication->brand_name }}
                </div>
            @endif
            <div class="prescription-details">
                <strong>{{ $prescription->dosage }}</strong> | 
                {{ $prescription->frequency->frequency_name ?? 'N/A' }} | 
                {{ $prescription->duration_days ?? $prescription->duration }} days
            </div>
            <div class="prescription-details">
                Qty: {{ $prescription->quantity }} | 
                Route: {{ $prescription->administrationRoute->route_name ?? 'PO' }}
            </div>
            @if($prescription->instructions)
                <div class="prescription-details mt-1">
                    <i class="fas fa-info-circle"></i> {{ Str::limit($prescription->instructions, 60) }}
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="badge bg-{{ 
                    $prescription->status === 'dispensed' ? 'success' : 
                    ($prescription->status === 'prescribed' ? 'primary' : 'secondary') 
                }}">
                    {{ ucfirst($prescription->status) }}
                </span>
                @if(!$prescription->is_paid)
                <div class="prescription-actions">
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="deletePrescriptionFromModal({{ $prescription->id }})"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    @endforeach
@else
    <p class="text-muted text-center py-3">No prescriptions added yet.</p>
@endif
