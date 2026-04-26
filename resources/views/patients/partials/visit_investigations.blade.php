@if($investigations->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead class="table-light">
                <tr>
                    <th>Service</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Ordered</th>
                    <th>Price</th>
                    <th width="80">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($investigations as $investigation)
                <tr id="investigation_row_{{ $investigation->id }}" 
                    @if($investigation->status === 'cancelled') class="text-muted" style="opacity: 0.6;" @endif>
                    <td>
                        <strong>{{ $investigation->medicalService->name ?? 'N/A' }}</strong>
                        @if($investigation->medicalService && $investigation->medicalService->code)
                            <br><small class="text-muted">{{ $investigation->medicalService->code }}</small>
                        @endif
                        @if($investigation->status === 'cancelled')
                            <br><small class="text-danger"><i class="fas fa-ban"></i> Cancelled</small>
                        @endif
                    </td>
                    <td>
                        @if($investigation->doctor && $investigation->doctor->user)
                            {{ $investigation->doctor->user->name }}
                        @else
                            <span class="text-muted">Lab Only</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $investigation->status_badge_class ?? 'bg-secondary' }}">
                            {{ ucfirst($investigation->status) }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $investigation->ordered_at ? $investigation->ordered_at->format('M d, Y H:i') : 'N/A' }}</small>
                    </td>
                    <td>
                        @if($investigation->status !== 'cancelled')
                            <strong>${{ number_format($investigation->total_price, 2) }}</strong>
                            @if($investigation->is_paid)
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Paid</small>
                            @else
                                <br><small class="text-warning"><i class="fas fa-clock"></i> Pending</small>
                            @endif
                        @else
                            <small class="text-muted"><s>${{ number_format($investigation->total_price, 2) }}</s></small>
                        @endif
                    </td>
                    <td>
                        @if($investigation->status === 'ordered' && !$investigation->is_paid)
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteInvestigation({{ $investigation->id }})"
                                    title="Cancel Investigation">
                                <i class="fas fa-times"></i>
                            </button>
                        @elseif($investigation->status === 'cancelled')
                            <small class="text-muted"><i class="fas fa-ban"></i> Cancelled</small>
                        @else
                            <small class="text-muted">N/A</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center text-muted py-3">
        <i class="fas fa-flask fa-2x mb-2"></i>
        <p>No investigations ordered for this visit yet.</p>
    </div>
@endif
