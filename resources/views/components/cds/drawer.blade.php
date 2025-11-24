@props(['alerts' => collect()])

@if($alerts && $alerts->count() > 0)
    <div id="cds-drawer" class="card mt-3">
        <div class="card-body">
            @foreach($alerts as $alert)
                <div class="alert alert-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'high' ? 'warning' : 'info') }} alert-sm mb-2 p-2" data-alert-id="{{ $alert->id }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ ucfirst($alert->severity) }} Alert</div>
                            <div class="small mb-1">{{ $alert->message }}</div>
                            @if($alert->rationale)
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ Str::limit($alert->rationale, 100) }}
                                </div>
                            @endif
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-success me-1" onclick="ackCdsAlert({{ $alert->id }}, 'accept')" title="Accept Alert">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="ackCdsAlertWithReason({{ $alert->id }}, 'override')" title="Override with Reason">
                                    <i class="fas fa-exclamation"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="ackCdsAlert({{ $alert->id }}, 'dismiss')" title="Dismiss">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    {{-- Hidden placeholder for CDS drawer that JavaScript can find and replace --}}
    <div id="cds-drawer" style="display: none;"></div>
@endif
