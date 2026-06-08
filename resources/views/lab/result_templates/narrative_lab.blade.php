{{-- Narrative / Free-text Lab Result Template --}}
<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">

    @if(isset($investigation) && $investigation->medicalService)
    @php
        $existingValue = null;
        if (isset($existingData['parameters'])) {
            $params = $existingData['parameters'];
            if (is_string($params)) $params = json_decode($params, true);
            $existingValue = $params[0]['value'] ?? null;
        }
    @endphp
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ $investigation->medicalService->name }} — Result
        </label>
        <textarea class="form-control" name="parameters[0][value]" rows="6"
                  placeholder="Enter the result findings here...">{{ $existingValue }}</textarea>
        <input type="hidden" name="parameters[0][parameter_name]" value="{{ $investigation->medicalService->name }}">
        <input type="hidden" name="parameters[0][status]" value="normal">
    </div>
    @else
    <div class="mb-3">
        <label class="form-label fw-semibold">Result</label>
        <textarea class="form-control" name="parameters[0][value]" rows="6"
                  placeholder="Enter the result findings here..."></textarea>
        <input type="hidden" name="parameters[0][parameter_name]" value="Result">
        <input type="hidden" name="parameters[0][status]" value="normal">
    </div>
    @endif

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
                           value="{{ isset($currentUser) ? $currentUser->name : (auth()->user()->name ?? '') }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Analysis Date:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="analysis_date"
                           value="{{ now()->format('Y-m-d\TH:i') }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
                </div>
            </div>
            <div class="row g-2 align-items-start mt-2">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Additional Comments:</strong></label>
                    <textarea class="form-control form-control-sm" name="additional_comments" rows="2"
                              placeholder="Any additional observations or comments...">{{ $existingData['additional_comments'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

</div>
