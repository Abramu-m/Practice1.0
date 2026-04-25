<div class="btn-group btn-group-sm">
    @if(Auth::user()->isAdmin())
        <a href="{{ route('investigations.show', $investigation) }}" 
        class="btn btn-outline-primary" title="View">
            <i class="fas fa-eye"></i>
        </a>
        @if(!in_array($investigation->status, ['collected', 'processing', 'resulted']))
            <a href="{{ route('investigations.edit', $investigation) }}" 
            class="btn btn-outline-warning" title="Edit">
                <i class="fas fa-edit"></i>
            </a>
        @endif
    @endif
    @if($investigation->is_paid)
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-info dropdown-toggle" 
                    data-bs-toggle="dropdown" title="Update Status">
                <i class="fas fa-tasks"></i>
            </button>
            <ul class="dropdown-menu">
                @if($investigation->status === 'ordered' && $investigation->medicalService && $investigation->medicalService->requires_sample)
                    <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $investigation->id }}, 'collected', 'stock')">Mark as Collected</a></li>
                @endif
                @if($investigation->status === 'collected')
                    <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $investigation->id }}, 'processing', 'simple')">Mark as Processing</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('lab.results.form', $investigation->id) }}?return_to=investigations.index">
                        <i class="fas fa-edit"></i> Add Results
                    </a></li>
                @endif
                @if($investigation->status === 'processing')
                    <li><a class="dropdown-item" href="{{ route('lab.results.form', $investigation->id) }}?return_to=investigations.index">
                        <i class="fas fa-edit"></i> Add Results
                    </a></li>
                @endif
                @if($investigation->status === 'resulted')
                    <li><a class="dropdown-item" href="{{ route('lab.investigations.view-results', $investigation->id) }}">
                        <i class="fas fa-chart-line"></i> View Results
                    </a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                @if($investigation->status === 'ordered')
                    <li>
                        <a class="dropdown-item" href="#" onclick="showStockDetailsForInvestigation({{ $investigation->id }})">
                            <i class="fas fa-boxes text-info"></i> Check Stock
                        </a>
                    </li>
                @endif
                @if(!in_array($investigation->status, ['resulted', 'cancelled']))
                    <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus({{ $investigation->id }}, 'cancelled', 'simple')">Cancel Investigation</a></li>
                @endif
            </ul>
        </div>
    @endif
</div>
