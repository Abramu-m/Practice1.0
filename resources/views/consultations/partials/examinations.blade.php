@if(isset($examinations) && $examinations->count() > 0)
    <div class="mb-3">
        @foreach($examinations as $exam)
        <div class="card mb-2" data-exam-id="{{ $exam->id }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">{{ $exam->examination_type ?? 'Systemic Examination' }}</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editExamination({{ $exam->id }})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <small class="text-muted align-self-center">{{ $exam->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>

                @if($exam->general_findings)
                <div class="mb-2">
                    <strong>General Findings:</strong>
                    <p class="mb-1">{{ $exam->general_findings }}</p>
                </div>
                @endif

                <div class="row">
                    @if($exam->cardiovascular_system)
                    <div class="col-md-6 mb-2">
                        <strong>Cardiovascular:</strong> {{ $exam->cardiovascular_system }}
                    </div>
                    @endif
                    @if($exam->respiratory_system)
                    <div class="col-md-6 mb-2">
                        <strong>Respiratory:</strong> {{ $exam->respiratory_system }}
                    </div>
                    @endif
                    @if($exam->gastrointestinal_system)
                    <div class="col-md-6 mb-2">
                        <strong>GI System:</strong> {{ $exam->gastrointestinal_system }}
                    </div>
                    @endif
                    @if($exam->nervous_system)
                    <div class="col-md-6 mb-2">
                        <strong>CNS:</strong> {{ $exam->nervous_system }}</div>
                    @endif
                    @if($exam->musculoskeletal_system)
                    <div class="col-md-6 mb-2">
                        <strong>Musculoskeletal:</strong> {{ $exam->musculoskeletal_system }}</div>
                    @endif
                    @if($exam->genitourinary_system)
                    <div class="col-md-6 mb-2">
                        <strong>GU System:</strong> {{ $exam->genitourinary_system }}</div>
                    @endif
                    @if($exam->skin_examination)
                    <div class="col-md-6 mb-2">
                        <strong>Skin:</strong> {{ $exam->skin_examination }}</div>
                    @endif
                    @if($exam->psychiatric_assessment)
                    <div class="col-md-6 mb-2">
                        <strong>Psychiatric:</strong> {{ $exam->psychiatric_assessment }}</div>
                    @endif
                </div>

                @if($exam->notes)
                <div class="mt-2">
                    <strong>Additional Notes:</strong>
                    <p class="mb-0 text-muted">{{ $exam->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No systemic examinations recorded yet.
    </div>
@endif
