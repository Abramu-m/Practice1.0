@if($assignedConsumables->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Consumable</th>
                    <th>Quantity Required</th>
                    <th>Optional</th>
                    <th>Notes</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignedConsumables as $consumable)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $consumable->medication->generic_name ?? 'N/A' }}</div>
                            @if($consumable->medication->brand_name)
                                <small class="text-muted">{{ $consumable->medication->brand_name }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ $consumable->quantity_required }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $consumable->is_optional ? 'bg-warning text-dark' : 'bg-primary' }}">
                                {{ $consumable->is_optional ? 'Optional' : 'Required' }}
                            </span>
                        </td>
                        <td>
                            @if($consumable->notes)
                                <span class="text-muted">{{ Str::limit($consumable->notes, 50) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-warning" onclick="editConsumable({{ $consumable->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteConsumable({{ $consumable->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No consumable requirements defined for this service yet.
        <br>
        <small class="text-muted">Click "Add Consumable Requirement" to define what consumables are needed for this medical service.</small>
    </div>
@endif
