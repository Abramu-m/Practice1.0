@if($investigations->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead class="table-light">
                <tr>
                    <th>Service</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Ordered</th>
                    <th>Cash</th>
                    <th>Covered</th>
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
                            <strong>Tsh {{ number_format($investigation->cash_amount, 2) }}</strong>
                            @if($investigation->is_paid)
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Paid</small>
                            @else
                                <br><small class="text-warning"><i class="fas fa-clock"></i> Pending</small>
                            @endif
                        @else
                            <small class="text-muted"><s>Tsh {{ number_format($investigation->cash_amount, 2) }}</s></small>
                        @endif
                    </td>
                    <td>
                        @if($investigation->insurance_covered_amount > 0)
                            <strong>Tsh {{ number_format($investigation->insurance_covered_amount, 2) }}</strong>
                        @else
                            <span class="text-muted">0.00</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            @if(isset($investigation->templateResults) && $investigation->templateResults->count() > 0)
                                @php $latest = $investigation->templateResults->first(); @endphp
                                <button type="button" class="btn btn-sm btn-primary" 
                                        onclick="viewComplexResult({{ $investigation->id }}, {{ $latest->id }})"
                                        title="View Investigation Form">
                                    <i class="fas fa-file-alt"></i>
                                </button>
                            @endif

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
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-muted text-center py-3">
        <i class="fas fa-flask" style="font-size: 2rem; opacity: 0.5;"></i>
        <p class="mb-0 mt-2">No investigations found for this visit.</p>
    </div>
@endif
