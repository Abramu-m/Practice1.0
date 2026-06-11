{{-- General / Free-Text Lab Result Template --}}
@php
    $formData = $existingData ?? [];
    $isReadOnly = $isReadOnly ?? false;
    $editMode = $editMode ?? false;
    $ro = $isReadOnly ? 'readonly' : '';
    $dis = $isReadOnly ? 'disabled' : '';

    $resultValue = $formData['results'] ?? '';
    $additionalComments = $formData['additional_comments'] ?? '';
    // Analyzed By always reflects who is currently analyzing (overwritten on load by
    // form.blade.php's loadResultTemplate JS); when read-only, show the recorded value.
    $analyzedBy = $isReadOnly ? ($formData['analyzed_by'] ?? '') : (auth()->user()->name ?? '');
    $analysisDate = isset($formData['analysis_date'])
        ? \Carbon\Carbon::parse($formData['analysis_date'])->format('Y-m-d\TH:i')
        : now()->format('Y-m-d\TH:i');

    $switchTemplates = \App\Models\ResultTemplate::where('is_active', true)
        ->where('code', '!=', 'general')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['code', 'name']);
@endphp

<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">

    <h6 class="text-primary mb-3">
        <i class="fas fa-clipboard-list"></i>
        {{ $investigation->medicalService->name ?? 'General' }} — Result
        @if($editMode)
            <small class="text-muted">
                - {{ ($formData['_result_status'] ?? '') === 'draft' ? 'Editing Draft' : 'Editing Saved Result' }}
            </small>
        @endif
    </h6>

    <div class="mb-3">
        <label class="form-label fw-semibold">Result</label>
        <textarea class="form-control" name="results" rows="6"
                  placeholder="Enter the result findings here..." {{ $ro }}>{{ $resultValue }}</textarea>
    </div>

    {{-- Quality Control --}}
    <div class="card mt-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Quality Control</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analyzed By:</strong></label>
                    <input type="text" class="form-control form-control-sm" name="analyzed_by"
                           value="{{ $analyzedBy }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analysis Date:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="analysis_date"
                           value="{{ $analysisDate }}" {{ $ro }}
                           style="{{ $isReadOnly ? 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;' : '' }}">
                </div>
            </div>
            <div class="row g-2 align-items-start mt-2">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Additional Comments:</strong></label>
                    <textarea class="form-control form-control-sm" name="additional_comments" rows="2"
                              placeholder="Any additional observations or comments..." {{ $ro }}>{{ $additionalComments }}</textarea>
                </div>
            </div>
        </div>
    </div>

    @if(!$isReadOnly)
    {{-- Switch to a different result template --}}
    <div class="card mt-3 border-dashed">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap">
                        <i class="fas fa-exchange-alt text-muted"></i> Need a different result form?
                    </label>
                    <select id="general_template_switch" class="form-select form-select-sm" style="width:auto; min-width:220px">
                        <option value="">— Switch template —</option>
                        @foreach($switchTemplates as $t)
                            <option value="{{ $t->code }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var switcher = document.getElementById('general_template_switch');
            if (switcher) {
                switcher.addEventListener('change', function() {
                    if (this.value && typeof loadResultTemplate === 'function') {
                        loadResultTemplate(this.value);
                    }
                });
            }
        })();
    </script>
    @endif

</div>
