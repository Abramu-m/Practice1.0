@php
    $hasActiveVisit = false;
    $activeVisitId = null;
    $consultationId = $visit->consultation->id ?? null;
    if (!isset($selectedPatient)) {
        $activeVisit = $visit->patientInfo->active_visit ?? null;
        $hasActiveVisit = $activeVisit && $activeVisit->id == $visit->id;
        $activeVisitId = $hasActiveVisit ? $visit->id : null;
    }
@endphp

<div class="d-flex gap-1 w-100" role="group">
    {{-- Lab Button for active visits --}}
    @if(!isset($selectedPatient) && $hasActiveVisit)
        <button type="button" class="btn btn-sm btn-success flex-fill" 
                onclick="openLabModal({{ json_encode($visit) }})"
                title="Add Lab Investigation">
            <i class="fas fa-flask"></i>
        </button>
    @endif
    
    {{-- Prescription Button --}}
    @if($visit->visit_status == 1 && $visit->consultation)
        <button type="button" class="btn btn-sm btn-primary flex-fill" 
                onclick="openPrescriptionModal({{ json_encode($visit) }}, 'visit' )"
                title="Add Prescription">
            <i class="fas fa-prescription-bottle-alt"></i>
        </button>
    @endif
    
    {{-- Consult Button --}}
    @if($visit->visitType && stripos($visit->visitType->description, 'lab only') === false && 
        // ($visit->visit_status == 0 || $visit->visit_status == 1) && 
        ($user->is_admin || $user->is_super || 
         ($user->role === 'doctor' && $user->doctor && 
          $user->doctor->doctor_id == $visit->doctor)))
        <a href="{{ route('consultations.show', $visit->id) }}" class="btn btn-sm btn-success flex-fill" title="{{ $visit->visit_status == 0 ? 'Start Consultation' : 'Continue Consultation' }}">
            <i class="fas fa-user-md"></i>
        </a>
    @elseif($visit->visitType && stripos($visit->visitType->description, 'lab only') === false && 
            ($visit->visit_status == 0 || $visit->visit_status == 1) && $user->role === 'doctor')
        <span class="btn btn-sm btn-secondary disabled flex-fill" title="Patient not assigned to you">
            <i class="fas fa-lock"></i>
        </span>
    @endif
    
    {{-- View --}}
    {{-- 
    <a href="{{ route('patient_visits.show', $visit->id) }}" class="btn btn-sm btn-info flex-fill" title="View Visit">
        <i class="fas fa-eye"></i>
    </a>
    --}}
    
    {{-- Print Prescriptions --}}
    @if($consultationId)
        <button type="button" class="btn btn-sm btn-outline-success flex-fill"
                onclick="viewVisitPrescriptionsModal({{ $consultationId }})"
                title="Print Prescriptions">
            <i class="fas fa-file-prescription"></i>
        </button>
    @endif

    {{-- Print Investigation Results --}}
    <button type="button" class="btn btn-sm btn-outline-info flex-fill"
            onclick="viewVisitResultsModal({{ $visit->id }})"
            title="Print Investigation Results">
        <i class="fas fa-microscope"></i>
    </button>

    {{-- Edit --}}
    <button type="button" class="btn btn-sm btn-warning flex-fill" title="Edit Visit"
            onclick="openEditVisitModal({{ $visit->id }})">
        <i class="fas fa-edit"></i>
    </button>
    
    {{-- Delete --}}
    @if($user->isAdmin())
        <button type="button" class="btn btn-sm btn-danger flex-fill" title="Delete Visit" 
                onclick="if(confirm('Delete this visit?')) { document.getElementById('delete-visit-{{ $visit->id }}').submit(); }">
            <i class="fas fa-trash"></i>
        </button>
    @endif
</div>

@if($user->isAdmin())
<form id="delete-visit-{{ $visit->id }}" action="{{ route('patient_visits.destroy', $visit->id) }}" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endif
