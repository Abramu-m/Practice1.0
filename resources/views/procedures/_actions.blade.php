<div class="btn-group btn-group-sm">
    @php
        $result = $investigation->results->first();
        $canEdit = $result && $result->form_status !== 'final';
    @endphp
    
    <!-- Role-specific Main Action Button -->
    @if($user->role === 'nurse')
        <!-- Nurse: Focus on procedures/starting -->
        @if($investigation->status === 'ordered')
            <button class="btn btn-sm btn-outline-primary" 
                   onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                   title="Start Procedure">
                <i class="fas fa-play"></i> Start Procedure
            </button>
        @elseif($investigation->status === 'processing')
            <a href="{{ route('procedures.show', $investigation) }}" 
               class="btn btn-sm btn-outline-success" 
               title="Complete Procedure">
                <i class="fas fa-check"></i> Complete
            </a>
        @else
            <a href="{{ route('procedures.show', $investigation) }}" 
               class="btn btn-sm btn-outline-info" 
               title="View Procedure">
                <i class="fas fa-eye"></i> View
            </a>
        @endif
        
    @elseif($user->role === 'radiologist')
        <!-- Radiologist: Focus on imaging studies -->
        @if(in_array($investigation->status, ['collected', 'processing']) || ($result && $result->form_status !== 'final'))
            @if(!$result || $result->form_status === 'draft' || $result->form_status === 'preliminary')
                <a href="{{ route('procedures.show', $investigation) }}" 
                   class="btn btn-sm {{ $result ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                   title="{{ $result ? 'Edit Report' : 'Create Report' }}">
                    <i class="fas {{ $result ? 'fa-edit' : 'fa-plus' }}"></i>
                    {{ $result ? 'Edit Report' : 'Create Report' }}
                </a>
            @endif
        @endif
        
        @if($investigation->status === 'resulted' && $result)
            <a href="{{ route('procedures.show', $investigation) }}" 
               class="btn btn-sm btn-outline-info" 
               title="View Report">
                <i class="fas fa-eye"></i> View Report
            </a>
        @endif
        
    @else
        <!-- Doctor/Lab: Standard result entry -->
        @if(in_array($investigation->status, ['collected', 'processing']) || ($result && $result->form_status !== 'final'))
            @if(!$result || $result->form_status === 'draft' || $result->form_status === 'preliminary')
                <a href="{{ route('procedures.show', $investigation) }}" 
                   class="btn btn-sm {{ $result ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                   title="{{ $result ? 'Edit Results' : 'Add Results' }}">
                    <i class="fas {{ $result ? 'fa-edit' : 'fa-plus' }}"></i>
                    {{ $result ? 'Edit' : 'Add' }}
                </a>
            @endif
        @endif
        
        @if($investigation->status === 'resulted' && $result)
            <a href="{{ route('procedures.show', $investigation) }}" 
               class="btn btn-sm btn-outline-info" 
               title="View Results">
                <i class="fas fa-eye"></i> View
            </a>
        @endif
    @endif

    <!-- Role-specific Status Update Buttons -->
    @if($user->role === 'nurse')
        <!-- Nurse-specific actions -->
        @if($investigation->status === 'ordered' && $investigation->medicalService && $investigation->medicalService->requires_sample)
            <button class="btn btn-sm btn-outline-info" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'collected')"
                    title="Mark Sample Collected (Stock will be deducted)">
                <i class="fas fa-flask"></i>
            </button>
        @endif
        @if($investigation->status === 'ordered' && (!$investigation->medicalService || !$investigation->medicalService->requires_sample))
            <button class="btn btn-sm btn-outline-primary" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                    title="Start Procedure (⚠️ Stock will be deducted)">
                <i class="fas fa-play"></i>
            </button>
        @endif
        @if($investigation->status === 'collected')
            <button class="btn btn-sm btn-outline-primary" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                    title="Start Procedure (No additional stock deduction)">
                <i class="fas fa-play"></i>
            </button>
        @endif
        
    @elseif($user->role === 'radiologist')
        <!-- Radiologist-specific actions -->
        @if($investigation->status === 'ordered' && (!$investigation->medicalService || !$investigation->medicalService->requires_sample))
            <button class="btn btn-sm btn-outline-primary" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                    title="Start Study (⚠️ Stock will be deducted)">
                <i class="fas fa-x-ray"></i>
            </button>
        @endif
        @if($investigation->status === 'collected')
            <button class="btn btn-sm btn-outline-primary" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                    title="Start Study (No additional stock deduction)">
                <i class="fas fa-x-ray"></i>
            </button>
        @endif
        
    @else
        <!-- Doctor/Lab actions -->
        @if($investigation->status === 'ordered' && $investigation->medicalService && $investigation->medicalService->requires_sample)
            <button class="btn btn-sm btn-outline-info" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'collected')"
                    title="Mark Sample Collected (Stock will be deducted)">
                <i class="fas fa-flask"></i>
            </button>
        @endif
        @if($investigation->status === 'ordered' && (!$investigation->medicalService || !$investigation->medicalService->requires_sample))
            <button class="btn btn-sm btn-outline-primary" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                    title="Start Processing (⚠️ Stock will be deducted)">
                <i class="fas fa-spinner"></i>
            </button>
        @endif
        @if($investigation->status === 'collected')
            <button class="btn btn-sm btn-outline-primary" 
                    onclick="updateInvestigationStatus({{ $investigation->id }}, 'processing')"
                    title="Start Processing (No additional stock deduction)">
                <i class="fas fa-spinner"></i>
            </button>
        @endif
    @endif

    <!-- Common action buttons -->
    @if($result)
        <a href="{{ route('procedures.view-results', $investigation) }}" 
           class="btn btn-sm btn-outline-danger"
           title="Generate Report">
            <i class="fas fa-file-pdf"></i>
        </a>
    @endif
    
    <button class="btn btn-sm btn-outline-secondary" 
            onclick="showStockDetailsForInvestigation({{ $investigation->id }})"
            title="Check Stock">
        <i class="fas fa-boxes"></i>
    </button>
    
    @if(in_array($investigation->status, ['ordered', 'collected', 'processing']))
        <button class="btn btn-sm btn-outline-danger" 
                onclick="updateInvestigationStatus({{ $investigation->id }}, 'cancelled')"
                title="Cancel">
            <i class="fas fa-times"></i>
        </button>
    @endif
</div>
