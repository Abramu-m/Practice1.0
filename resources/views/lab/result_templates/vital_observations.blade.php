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

    <h6 class="text-primary mb-2">
        <i class="fas fa-heartbeat"></i>
        {{ $investigation->medicalService->name ?? 'Vital Signs & Observations' }} — Result
        @if($editMode)
            <small class="text-muted">
                - {{ ($formData['_result_status'] ?? '') === 'draft' ? 'Editing Draft' : 'Editing Saved Result' }}
            </small>
        @endif
    </h6>

    {{-- Vitals & Measurements --}}
    <div class="text-secondary small text-uppercase fw-semibold mb-1">Vitals & Measurements</div>
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Sys BP (mmHg)</label>
            <input type="text" name="systolic_bp" class="form-control form-control-sm" title="Normal: 90-140"
                   value="{{ $formData['systolic_bp'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Dia BP (mmHg)</label>
            <input type="text" name="diastolic_bp" class="form-control form-control-sm" title="Normal: 60-90"
                   value="{{ $formData['diastolic_bp'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Heart Rate (bpm)</label>
            <input type="text" name="heart_rate" class="form-control form-control-sm" title="Normal: 60-100"
                   value="{{ $formData['heart_rate'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Resp Rate (/min)</label>
            <input type="text" name="respiratory_rate" class="form-control form-control-sm" title="Normal: 12-20"
                   value="{{ $formData['respiratory_rate'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Temp (°C)</label>
            <input type="text" name="temperature" class="form-control form-control-sm" title="Normal: 36.1-37.2"
                   value="{{ $formData['temperature'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Temp Site</label>
            @php $tempSite = $formData['temperature_site'] ?? 'oral'; @endphp
            <select name="temperature_site" class="form-select form-select-sm" {{ $dis }}>
                <option value="oral" {{ $tempSite === 'oral' ? 'selected' : '' }}>Oral</option>
                <option value="axillary" {{ $tempSite === 'axillary' ? 'selected' : '' }}>Axillary</option>
                <option value="rectal" {{ $tempSite === 'rectal' ? 'selected' : '' }}>Rectal</option>
                <option value="tympanic" {{ $tempSite === 'tympanic' ? 'selected' : '' }}>Tympanic</option>
                <option value="temporal" {{ $tempSite === 'temporal' ? 'selected' : '' }}>Temporal</option>
            </select>
        </div>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">SpO2 (%)</label>
            <input type="text" name="oxygen_saturation" class="form-control form-control-sm" title="Normal: ≥95%"
                   value="{{ $formData['oxygen_saturation'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">O2 Delivery</label>
            @php $o2Delivery = $formData['oxygen_delivery'] ?? 'room_air'; @endphp
            <select name="oxygen_delivery" class="form-select form-select-sm" {{ $dis }}>
                <option value="room_air" {{ $o2Delivery === 'room_air' ? 'selected' : '' }}>Room Air</option>
                <option value="nasal_cannula" {{ $o2Delivery === 'nasal_cannula' ? 'selected' : '' }}>Nasal Cannula</option>
                <option value="face_mask" {{ $o2Delivery === 'face_mask' ? 'selected' : '' }}>Face Mask</option>
                <option value="non_rebreather" {{ $o2Delivery === 'non_rebreather' ? 'selected' : '' }}>Non-Rebreather</option>
                <option value="ventilator" {{ $o2Delivery === 'ventilator' ? 'selected' : '' }}>Ventilator</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Weight (kg)</label>
            <input type="text" name="weight" id="vo_weight" class="form-control form-control-sm"
                   value="{{ $formData['weight'] ?? '' }}" {{ $ro }} onchange="vitalObsCalculateBMI()">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Height (cm)</label>
            <input type="text" name="height" id="vo_height" class="form-control form-control-sm"
                   value="{{ $formData['height'] ?? '' }}" {{ $ro }} onchange="vitalObsCalculateBMI()">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">BMI (kg/m²)</label>
            <input type="text" name="bmi" id="vo_bmi" class="form-control form-control-sm" readonly
                   value="{{ $formData['bmi'] ?? '' }}">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">BMI Category</label>
            <input type="text" name="bmi_category" id="vo_bmi_category" class="form-control form-control-sm" readonly
                   value="{{ $formData['bmi_category'] ?? '' }}">
        </div>
    </div>

    {{-- Fluid Balance & Other Observations --}}
    <div class="text-secondary small text-uppercase fw-semibold mb-1">Fluid Balance & Other Observations</div>
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Dehydration</label>
            @php $dehydration = $formData['dehydration'] ?? ''; @endphp
            <select name="dehydration" class="form-select form-select-sm" {{ $dis }}>
                <option value="">—</option>
                <option value="none" {{ $dehydration === 'none' ? 'selected' : '' }}>None</option>
                <option value="mild" {{ $dehydration === 'mild' ? 'selected' : '' }}>Mild</option>
                <option value="moderate" {{ $dehydration === 'moderate' ? 'selected' : '' }}>Moderate</option>
                <option value="severe" {{ $dehydration === 'severe' ? 'selected' : '' }}>Severe</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Fluid Input</label>
            <input type="text" name="fluid_input" class="form-control form-control-sm" placeholder="e.g. NS 500ml"
                   value="{{ $formData['fluid_input'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Fluid Output</label>
            <input type="text" name="fluid_output" class="form-control form-control-sm" placeholder="e.g. 300ml urine"
                   value="{{ $formData['fluid_output'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Blood Sugar</label>
            <input type="text" name="blood_sugar" class="form-control form-control-sm" placeholder="mg/dL or mmol/L"
                   value="{{ $formData['blood_sugar'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Urine Ketones</label>
            @php $urineKetones = $formData['urine_ketones'] ?? ''; @endphp
            <select name="urine_ketones" class="form-select form-select-sm" {{ $dis }}>
                <option value="">—</option>
                @foreach(['Negative','Trace','1+','2+','3+','4+'] as $opt)
                    <option value="{{ $opt }}" {{ $urineKetones === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Pain (0-10)</label>
            @php $painScale = $formData['pain_scale'] ?? ''; @endphp
            <select name="pain_scale" class="form-select form-select-sm" {{ $dis }}>
                <option value="" {{ $painScale === '' ? 'selected' : '' }}>No Pain</option>
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}" {{ $painScale == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Pain & Neurological Assessment --}}
    <div class="text-secondary small text-uppercase fw-semibold mb-1">Pain & Neurological Assessment</div>
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Pain Location</label>
            <input type="text" name="pain_location" class="form-control form-control-sm" placeholder="e.g. Chest"
                   value="{{ $formData['pain_location'] ?? '' }}" {{ $ro }}>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Pain Character</label>
            @php $painCharacter = $formData['pain_character'] ?? ''; @endphp
            <select name="pain_character" class="form-select form-select-sm" {{ $dis }}>
                <option value="" {{ $painCharacter === '' ? 'selected' : '' }}>—</option>
                <option value="sharp" {{ $painCharacter === 'sharp' ? 'selected' : '' }}>Sharp</option>
                <option value="dull" {{ $painCharacter === 'dull' ? 'selected' : '' }}>Dull</option>
                <option value="burning" {{ $painCharacter === 'burning' ? 'selected' : '' }}>Burning</option>
                <option value="cramping" {{ $painCharacter === 'cramping' ? 'selected' : '' }}>Cramping</option>
                <option value="throbbing" {{ $painCharacter === 'throbbing' ? 'selected' : '' }}>Throbbing</option>
                <option value="stabbing" {{ $painCharacter === 'stabbing' ? 'selected' : '' }}>Stabbing</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Consciousness</label>
            @php $consciousness = $formData['consciousness_level'] ?? 'alert'; @endphp
            <select name="consciousness_level" class="form-select form-select-sm" {{ $dis }}>
                <option value="alert" {{ $consciousness === 'alert' ? 'selected' : '' }}>Alert & Oriented</option>
                <option value="drowsy" {{ $consciousness === 'drowsy' ? 'selected' : '' }}>Drowsy</option>
                <option value="confused" {{ $consciousness === 'confused' ? 'selected' : '' }}>Confused</option>
                <option value="unresponsive" {{ $consciousness === 'unresponsive' ? 'selected' : '' }}>Unresponsive</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Pupils</label>
            @php $pupils = $formData['pupils_reaction'] ?? 'equal_reactive'; @endphp
            <select name="pupils_reaction" class="form-select form-select-sm" {{ $dis }}>
                <option value="equal_reactive" {{ $pupils === 'equal_reactive' ? 'selected' : '' }}>Equal & Reactive</option>
                <option value="equal_sluggish" {{ $pupils === 'equal_sluggish' ? 'selected' : '' }}>Equal & Sluggish</option>
                <option value="unequal" {{ $pupils === 'unequal' ? 'selected' : '' }}>Unequal</option>
                <option value="fixed" {{ $pupils === 'fixed' ? 'selected' : '' }}>Fixed</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Motor Response</label>
            @php $motorResponse = $formData['motor_response'] ?? 'normal'; @endphp
            <select name="motor_response" class="form-select form-select-sm" {{ $dis }}>
                <option value="normal" {{ $motorResponse === 'normal' ? 'selected' : '' }}>Normal Movement</option>
                <option value="weakness" {{ $motorResponse === 'weakness' ? 'selected' : '' }}>Weakness</option>
                <option value="paralysis" {{ $motorResponse === 'paralysis' ? 'selected' : '' }}>Paralysis</option>
                <option value="not_assessed" {{ $motorResponse === 'not_assessed' ? 'selected' : '' }}>Not Assessed</option>
            </select>
        </div>
    </div>

    {{-- Nursing Observations --}}
    <div class="mb-2">
        <label class="form-label small mb-1 fw-semibold">Nursing Observations</label>
        <textarea name="nursing_notes" class="form-control form-control-sm" rows="2"
                  placeholder="General appearance, behavior, comfort level, any concerns" {{ $ro }}>{{ $formData['nursing_notes'] ?? '' }}</textarea>
    </div>

    {{-- Recording Details --}}
    <div class="card mt-2">
        <div class="card-body py-2">
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
    <div class="card mt-2 border-dashed">
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
