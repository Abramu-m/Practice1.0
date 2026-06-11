{{-- Vital Signs & Observations Result Template --}}
@php
    $formData = $existingData ?? [];
    $isReadOnly = $isReadOnly ?? (($formData['_result_status'] ?? null) === 'final');
    $editMode = $editMode ?? false;
    $ro = $isReadOnly ? 'readonly' : '';
    $dis = $isReadOnly ? 'disabled' : '';

    $observationTime = isset($formData['observation_time'])
        ? \Carbon\Carbon::parse($formData['observation_time'])->format('Y-m-d\TH:i')
        : now()->format('Y-m-d\TH:i');
    $observerName = $formData['observer_name'] ?? (auth()->user()->name ?? '');

    $switchTemplates = \App\Models\ResultTemplate::where('is_active', true)
        ->where('code', '!=', 'vital_observations')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['code', 'name']);
@endphp

<div class="result-template-container" style="background-color:#fff;padding:15px;border-radius:5px;">

    <h6 class="text-primary mb-3">
        <i class="fas fa-heartbeat"></i>
        {{ $investigation->medicalService->name ?? 'Vital Signs & Observations' }} — Result
        @if($editMode)
            <small class="text-muted">
                - {{ ($formData['_result_status'] ?? '') === 'draft' ? 'Editing Draft' : 'Editing Saved Result' }}
            </small>
        @endif
    </h6>

    {{-- Primary Vital Signs --}}
    <h6 class="text-secondary mb-2">Primary Vital Signs</h6>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="systolic_bp" class="form-control"
                       value="{{ $formData['systolic_bp'] ?? '' }}" {{ $ro }}>
                <label>Systolic BP (mmHg)</label>
            </div>
            <small class="text-muted">Normal: 90-140 mmHg</small>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="diastolic_bp" class="form-control"
                       value="{{ $formData['diastolic_bp'] ?? '' }}" {{ $ro }}>
                <label>Diastolic BP (mmHg)</label>
            </div>
            <small class="text-muted">Normal: 60-90 mmHg</small>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="heart_rate" class="form-control"
                       value="{{ $formData['heart_rate'] ?? '' }}" {{ $ro }}>
                <label>Heart Rate (bpm)</label>
            </div>
            <small class="text-muted">Normal: 60-100 bpm</small>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="respiratory_rate" class="form-control"
                       value="{{ $formData['respiratory_rate'] ?? '' }}" {{ $ro }}>
                <label>Respiratory Rate (/min)</label>
            </div>
            <small class="text-muted">Normal: 12-20 /min</small>
        </div>
    </div>

    {{-- Temperature & Oxygenation --}}
    <h6 class="text-secondary mb-2">Temperature & Oxygenation</h6>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="temperature" class="form-control"
                       value="{{ $formData['temperature'] ?? '' }}" {{ $ro }}>
                <label>Temperature (°C)</label>
            </div>
            <small class="text-muted">Normal: 36.1-37.2°C</small>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <select name="temperature_site" class="form-select" {{ $dis }}>
                    @php $tempSite = $formData['temperature_site'] ?? 'oral'; @endphp
                    <option value="oral" {{ $tempSite === 'oral' ? 'selected' : '' }}>Oral</option>
                    <option value="axillary" {{ $tempSite === 'axillary' ? 'selected' : '' }}>Axillary</option>
                    <option value="rectal" {{ $tempSite === 'rectal' ? 'selected' : '' }}>Rectal</option>
                    <option value="tympanic" {{ $tempSite === 'tympanic' ? 'selected' : '' }}>Tympanic</option>
                    <option value="temporal" {{ $tempSite === 'temporal' ? 'selected' : '' }}>Temporal</option>
                </select>
                <label>Temperature Site</label>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="oxygen_saturation" class="form-control"
                       value="{{ $formData['oxygen_saturation'] ?? '' }}" {{ $ro }}>
                <label>Oxygen Saturation (%)</label>
            </div>
            <small class="text-muted">Normal: ≥95%</small>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <select name="oxygen_delivery" class="form-select" {{ $dis }}>
                    @php $o2Delivery = $formData['oxygen_delivery'] ?? 'room_air'; @endphp
                    <option value="room_air" {{ $o2Delivery === 'room_air' ? 'selected' : '' }}>Room Air</option>
                    <option value="nasal_cannula" {{ $o2Delivery === 'nasal_cannula' ? 'selected' : '' }}>Nasal Cannula</option>
                    <option value="face_mask" {{ $o2Delivery === 'face_mask' ? 'selected' : '' }}>Face Mask</option>
                    <option value="non_rebreather" {{ $o2Delivery === 'non_rebreather' ? 'selected' : '' }}>Non-Rebreather</option>
                    <option value="ventilator" {{ $o2Delivery === 'ventilator' ? 'selected' : '' }}>Ventilator</option>
                </select>
                <label>Oxygen Delivery</label>
            </div>
        </div>
    </div>

    {{-- Physical Measurements --}}
    <h6 class="text-secondary mb-2">Physical Measurements</h6>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="weight" id="vo_weight" class="form-control"
                       value="{{ $formData['weight'] ?? '' }}" {{ $ro }} onchange="vitalObsCalculateBMI()">
                <label>Weight (kg)</label>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="height" id="vo_height" class="form-control"
                       value="{{ $formData['height'] ?? '' }}" {{ $ro }} onchange="vitalObsCalculateBMI()">
                <label>Height (cm)</label>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="bmi" id="vo_bmi" class="form-control" readonly
                       value="{{ $formData['bmi'] ?? '' }}">
                <label>BMI (kg/m²)</label>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="form-floating">
                <input type="text" name="bmi_category" id="vo_bmi_category" class="form-control" readonly
                       value="{{ $formData['bmi_category'] ?? '' }}">
                <label>BMI Category</label>
            </div>
        </div>
    </div>

    {{-- Pain & Comfort Assessment --}}
    <h6 class="text-secondary mb-2">Pain & Comfort Assessment</h6>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="pain_scale" class="form-select" {{ $dis }}>
                    @php $painScale = $formData['pain_scale'] ?? ''; @endphp
                    <option value="" {{ $painScale === '' ? 'selected' : '' }}>No Pain</option>
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ $painScale == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
                <label>Pain Scale (0-10)</label>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <input type="text" name="pain_location" class="form-control"
                       placeholder="e.g., Chest, Abdomen, Back"
                       value="{{ $formData['pain_location'] ?? '' }}" {{ $ro }}>
                <label>Pain Location</label>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="pain_character" class="form-select" {{ $dis }}>
                    @php $painCharacter = $formData['pain_character'] ?? ''; @endphp
                    <option value="" {{ $painCharacter === '' ? 'selected' : '' }}>Select Character</option>
                    <option value="sharp" {{ $painCharacter === 'sharp' ? 'selected' : '' }}>Sharp</option>
                    <option value="dull" {{ $painCharacter === 'dull' ? 'selected' : '' }}>Dull</option>
                    <option value="burning" {{ $painCharacter === 'burning' ? 'selected' : '' }}>Burning</option>
                    <option value="cramping" {{ $painCharacter === 'cramping' ? 'selected' : '' }}>Cramping</option>
                    <option value="throbbing" {{ $painCharacter === 'throbbing' ? 'selected' : '' }}>Throbbing</option>
                    <option value="stabbing" {{ $painCharacter === 'stabbing' ? 'selected' : '' }}>Stabbing</option>
                </select>
                <label>Pain Character</label>
            </div>
        </div>
    </div>

    {{-- Neurological Status --}}
    <h6 class="text-secondary mb-2">Neurological Status</h6>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="consciousness_level" class="form-select" {{ $dis }}>
                    @php $consciousness = $formData['consciousness_level'] ?? 'alert'; @endphp
                    <option value="alert" {{ $consciousness === 'alert' ? 'selected' : '' }}>Alert & Oriented</option>
                    <option value="drowsy" {{ $consciousness === 'drowsy' ? 'selected' : '' }}>Drowsy</option>
                    <option value="confused" {{ $consciousness === 'confused' ? 'selected' : '' }}>Confused</option>
                    <option value="unresponsive" {{ $consciousness === 'unresponsive' ? 'selected' : '' }}>Unresponsive</option>
                </select>
                <label>Level of Consciousness</label>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="pupils_reaction" class="form-select" {{ $dis }}>
                    @php $pupils = $formData['pupils_reaction'] ?? 'equal_reactive'; @endphp
                    <option value="equal_reactive" {{ $pupils === 'equal_reactive' ? 'selected' : '' }}>Equal & Reactive</option>
                    <option value="equal_sluggish" {{ $pupils === 'equal_sluggish' ? 'selected' : '' }}>Equal & Sluggish</option>
                    <option value="unequal" {{ $pupils === 'unequal' ? 'selected' : '' }}>Unequal</option>
                    <option value="fixed" {{ $pupils === 'fixed' ? 'selected' : '' }}>Fixed</option>
                </select>
                <label>Pupil Reaction</label>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-floating">
                <select name="motor_response" class="form-select" {{ $dis }}>
                    @php $motorResponse = $formData['motor_response'] ?? 'normal'; @endphp
                    <option value="normal" {{ $motorResponse === 'normal' ? 'selected' : '' }}>Normal Movement</option>
                    <option value="weakness" {{ $motorResponse === 'weakness' ? 'selected' : '' }}>Weakness</option>
                    <option value="paralysis" {{ $motorResponse === 'paralysis' ? 'selected' : '' }}>Paralysis</option>
                    <option value="not_assessed" {{ $motorResponse === 'not_assessed' ? 'selected' : '' }}>Not Assessed</option>
                </select>
                <label>Motor Response</label>
            </div>
        </div>
    </div>

    {{-- Nursing Observations --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Nursing Observations</label>
        <textarea name="nursing_notes" class="form-control" rows="3"
                  placeholder="General appearance, behavior, comfort level, any concerns" {{ $ro }}>{{ $formData['nursing_notes'] ?? '' }}</textarea>
    </div>

    {{-- Recording Details --}}
    <div class="card mt-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-check-circle"></i> Recording Details</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Observation Time:</strong></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="observation_time"
                           value="{{ $observationTime }}" {{ $ro }}
                           style="{{ $isReadOnly ? 'background:#f0f0f0;pointer-events:none;cursor:not-allowed;' : '' }}">
                </div>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><strong>Recorded By:</strong></label>
                    <input type="text" class="form-control form-control-sm" name="observer_name"
                           value="{{ $observerName }}" readonly
                           style="background:#f0f0f0;pointer-events:none;cursor:not-allowed;">
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
                    <select id="vital_observations_template_switch" class="form-select form-select-sm" style="width:auto; min-width:220px">
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
            var switcher = document.getElementById('vital_observations_template_switch');
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

<script>
    function vitalObsCalculateBMI() {
        var weightInput = document.getElementById('vo_weight');
        var heightInput = document.getElementById('vo_height');
        var bmiInput = document.getElementById('vo_bmi');
        var bmiCategoryInput = document.getElementById('vo_bmi_category');
        if (!weightInput || !heightInput || !bmiInput || !bmiCategoryInput) return;

        var weight = parseFloat(weightInput.value) || 0;
        var height = parseFloat(heightInput.value) || 0;

        if (weight > 0 && height > 0) {
            var heightInMeters = height / 100;
            var bmi = weight / (heightInMeters * heightInMeters);
            bmiInput.value = bmi.toFixed(1);

            var category;
            if (bmi < 18.5) category = 'Underweight';
            else if (bmi < 25) category = 'Normal weight';
            else if (bmi < 30) category = 'Overweight';
            else category = 'Obesity';

            bmiCategoryInput.value = category;
        }
    }
</script>
