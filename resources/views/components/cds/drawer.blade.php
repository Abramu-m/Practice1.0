@props(['alerts' => collect()])

@if($alerts && $alerts->count() > 0)
    <div id="cds-drawer" class="card mt-3">
        <div class="card-header bg-secondary text-white">
            <strong><i class="fas fa-bell"></i> Clinical Decision Support</strong>
        </div>
        <div class="card-body">
            @foreach($alerts as $alert)
                <div class="mb-2 p-2 border rounded">
                    <x-cds.alert :severity="$alert->severity" :message="$alert->message" :rationale="$alert->rationale" />
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-success" onclick="ackCdsAlert({{ $alert->id }}, 'accept')">
                            Accept
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="ackCdsAlertWithReason({{ $alert->id }}, 'override')">
                            Override
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="ackCdsAlert({{ $alert->id }}, 'dismiss')">
                            Dismiss
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
