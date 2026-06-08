<div class="d-flex gap-1 w-100" role="group" aria-label="Patient Actions">
    <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-info flex-fill" title="View Patient">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-warning flex-fill" title="Edit Patient">
        <i class="fas fa-edit"></i>
    </a>
    
    @if($patient->active_visit && 
        $patient->active_visit->visitType && 
        stripos($patient->active_visit->visitType->description, 'lab only') !== false)
        <a href="{{ route('patient_visits.index') }}?search={{ $patient->mr_number ?? '' }}" class="btn btn-sm btn-success flex-fill" title="Add lab investigations for this visit">
            <i class="fas fa-flask"></i>
        </a>
    @elseif($patient->active_visit && 
        (auth()->user()->is_admin || auth()->user()->is_super || 
         (auth()->user()->role === 'doctor' && auth()->user()->doctor && 
          auth()->user()->doctor->doctor_id == $patient->active_visit->doctor)))
        <a href="{{ route('consultations.show', $patient->active_visit->id) }}" class="btn btn-sm btn-success flex-fill" title="{{ $patient->active_visit->visit_status == 0 ? 'Start Consultation' : 'Continue Consultation' }}">
            <i class="fas fa-user-md"></i> 
        </a>
    @endif
    @if(!$patient->active_visit)
        <a href="{{ route('patient_visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary flex-fill" title="Create Visit">
            <i class="fas fa-plus-circle"></i> 
        </a>
    @endif
    @if($patient->visits->count() > 0)
    <a href="{{ route('patient_visits.index', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-secondary flex-fill" title="View Visits">
        <i class="fas fa-list"></i> 
    </a>
    @endif
    
    @if(auth()->user()->isAdmin())
        <button type="button" class="btn btn-sm btn-danger flex-fill" title="Delete Patient" onclick="if(confirm('Delete this patient?')) { document.getElementById('delete-patient-{{ $patient->id }}').submit(); }">
            <i class="fas fa-trash"></i> 
        </button>
    @endif
</div>

@if(auth()->user()->isAdmin())
<form id="delete-patient-{{ $patient->id }}" action="{{ route('patients.destroy', $patient->id) }}" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endif
