@if(isset($investigation) && $investigation && $investigation->id)
@php
    // Get existing form data for prefilling
    $formData = $existingData ?? [];
    $isReadOnly = isset($existingData['_result_status']) && $existingData['_result_status'] === 'final';
@endphp

@if($isReadOnly)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Final Results - Read Only:</strong> This report has been finalized and cannot be modified.
    </div>
@endif

<form action="{{ route('procedures.store-result', $investigation->id) }}" method="POST" class="procedure-form">
    @csrf
    <input type="hidden" name="result_type" value="{{ $investigation->medicalService->resultTemplate->code }}">
    <input type="hidden" name="investigation_id" value="{{ $investigation->id }}">

    <div class="row">
        <div class="col-md-12 mb-3">
            <div id="save-indicator" class="float-end"></div>
            <h6 class="text-primary mb-3">
                <i class="fas fa-heartbeat"></i>
                Vital Signs & Physical Observations
                @if($editMode)
                    <small class="text-muted">
                        - {{ $existingData['_result_status'] === 'draft' ? 'Editing Draft' : 'Editing Preliminary Results' }}
                    </small>
                @endif
            </h6>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Bedrest Monitoring:</strong> Record vital signs and observations for patient monitoring during hospital stay.
            </div>
        </div>
    </div>

    <!-- Vital Signs -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Primary Vital Signs</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="systolic_bp" class="form-control" step="1" min="50" max="300"
                               value="{{ $formData['systolic_bp'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Systolic BP (mmHg)</label>
                    </div>
                    <small class="normal-range">Normal: 90-140 mmHg</small>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="diastolic_bp" class="form-control" step="1" min="30" max="200"
                               value="{{ $formData['diastolic_bp'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Diastolic BP (mmHg)</label>
                    </div>
                    <small class="normal-range">Normal: 60-90 mmHg</small>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="heart_rate" class="form-control" step="1" min="30" max="250"
                               value="{{ $formData['heart_rate'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Heart Rate (bpm)</label>
                    </div>
                    <small class="normal-range">Normal: 60-100 bpm</small>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="respiratory_rate" class="form-control" step="1" min="5" max="50"
                               value="{{ $formData['respiratory_rate'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Respiratory Rate (/min)</label>
                    </div>
                    <small class="normal-range">Normal: 12-20 /min</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Temperature & Oxygen -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Temperature & Oxygenation</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="temperature" class="form-control" step="0.1" min="30" max="45"
                               value="{{ $formData['temperature'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Temperature (°C)</label>
                    </div>
                    <small class="normal-range">Normal: 36.1-37.2°C</small>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <select name="temperature_site" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                            <option value="oral" {{ ($formData['temperature_site'] ?? 'oral') === 'oral' ? 'selected' : '' }}>Oral</option>
                            <option value="axillary" {{ ($formData['temperature_site'] ?? '') === 'axillary' ? 'selected' : '' }}>Axillary</option>
                            <option value="rectal" {{ ($formData['temperature_site'] ?? '') === 'rectal' ? 'selected' : '' }}>Rectal</option>
                            <option value="tympanic" {{ ($formData['temperature_site'] ?? '') === 'tympanic' ? 'selected' : '' }}>Tympanic</option>
                            <option value="temporal" {{ ($formData['temperature_site'] ?? '') === 'temporal' ? 'selected' : '' }}>Temporal</option>
                        </select>
                        <label>Temperature Site</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="oxygen_saturation" class="form-control" step="1" min="70" max="100"
                               value="{{ $formData['oxygen_saturation'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Oxygen Saturation (%)</label>
                    </div>
                    <small class="normal-range">Normal: ≥95%</small>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <select name="oxygen_delivery" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                            <option value="room_air" {{ ($formData['oxygen_delivery'] ?? 'room_air') === 'room_air' ? 'selected' : '' }}>Room Air</option>
                            <option value="nasal_cannula" {{ ($formData['oxygen_delivery'] ?? '') === 'nasal_cannula' ? 'selected' : '' }}>Nasal Cannula</option>
                            <option value="face_mask" {{ ($formData['oxygen_delivery'] ?? '') === 'face_mask' ? 'selected' : '' }}>Face Mask</option>
                            <option value="non_rebreather" {{ ($formData['oxygen_delivery'] ?? '') === 'non_rebreather' ? 'selected' : '' }}>Non-Rebreather</option>
                            <option value="ventilator" {{ ($formData['oxygen_delivery'] ?? '') === 'ventilator' ? 'selected' : '' }}>Ventilator</option>
                        </select>
                        <label>Oxygen Delivery</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Physical Measurements -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Physical Measurements</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="weight" id="weight" class="form-control" step="0.1" 
                               value="{{ $formData['weight'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}
                               onchange="calculateBMI()">
                        <label>Weight (kg)</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="height" id="height" class="form-control" step="0.1" 
                               value="{{ $formData['height'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}
                               onchange="calculateBMI()">
                        <label>Height (cm)</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="bmi" id="bmi" class="form-control" step="0.1" readonly
                               value="{{ $formData['bmi'] ?? '' }}">
                        <label>BMI (kg/m²)</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-floating">
                        <input type="text" name="bmi_category" id="bmi_category" class="form-control" readonly
                               value="{{ $formData['bmi_category'] ?? '' }}">
                        <label>BMI Category</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pain Assessment -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Pain & Comfort Assessment</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="pain_scale" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                            <option value="">No Pain</option>
                            <option value="1" {{ ($formData['pain_scale'] ?? '') === '1' ? 'selected' : '' }}>1 - Mild</option>
                            <option value="2" {{ ($formData['pain_scale'] ?? '') === '2' ? 'selected' : '' }}>2 - Mild</option>
                            <option value="3" {{ ($formData['pain_scale'] ?? '') === '3' ? 'selected' : '' }}>3 - Moderate</option>
                            <option value="4" {{ ($formData['pain_scale'] ?? '') === '4' ? 'selected' : '' }}>4 - Moderate</option>
                            <option value="5" {{ ($formData['pain_scale'] ?? '') === '5' ? 'selected' : '' }}>5 - Moderate</option>
                            <option value="6" {{ ($formData['pain_scale'] ?? '') === '6' ? 'selected' : '' }}>6 - Severe</option>
                            <option value="7" {{ ($formData['pain_scale'] ?? '') === '7' ? 'selected' : '' }}>7 - Severe</option>
                            <option value="8" {{ ($formData['pain_scale'] ?? '') === '8' ? 'selected' : '' }}>8 - Very Severe</option>
                            <option value="9" {{ ($formData['pain_scale'] ?? '') === '9' ? 'selected' : '' }}>9 - Very Severe</option>
                            <option value="10" {{ ($formData['pain_scale'] ?? '') === '10' ? 'selected' : '' }}>10 - Worst Possible</option>
                        </select>
                        <label>Pain Scale (0-10)</label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <input type="text" name="pain_location" class="form-control" 
                               placeholder="e.g., Chest, Abdomen, Back"
                               value="{{ $formData['pain_location'] ?? '' }}" {{ $isReadOnly ? 'readonly' : '' }}>
                        <label>Pain Location</label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="pain_character" class="form-select" {{ $isReadOnly ? 'disabled' : '' }}>
                            <option value="">Select Character</option>
                            <option value="sharp" {{ ($formData['pain_character'] ?? '') === 'sharp' ? 'selected' : '' }}>Sharp</option>
                            <option value="dull" {{ ($formData['pain_character'] ?? '') === 'dull' ? 'selected' : '' }}>Dull</option>
                            <option value="burning" {{ ($formData['pain_character'] ?? '') === 'burning' ? 'selected' : '' }}>Burning</option>
                            <option value="cramping" {{ ($formData['pain_character'] ?? '') === 'cramping' ? 'selected' : '' }}>Cramping</option>
                            <option value="throbbing" {{ ($formData['pain_character'] ?? '') === 'throbbing' ? 'selected' : '' }}>Throbbing</option>
                            <option value="stabbing" {{ ($formData['pain_character'] ?? '') === 'stabbing' ? 'selected' : '' }}>Stabbing</option>
                        </select>
                        <label>Pain Character</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Neurological Assessment -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Neurological Status</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="consciousness_level" class="form-select">
                            <option value="alert">Alert & Oriented</option>
                            <option value="drowsy">Drowsy</option>
                            <option value="confused">Confused</option>
                            <option value="unresponsive">Unresponsive</option>
                        </select>
                        <label>Level of Consciousness</label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="pupils_reaction" class="form-select">
                            <option value="equal_reactive">Equal & Reactive</option>
                            <option value="equal_sluggish">Equal & Sluggish</option>
                            <option value="unequal">Unequal</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        <label>Pupil Reaction</label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-floating">
                        <select name="motor_response" class="form-select">
                            <option value="normal">Normal Movement</option>
                            <option value="weakness">Weakness</option>
                            <option value="paralysis">Paralysis</option>
                            <option value="not_assessed">Not Assessed</option>
                        </select>
                        <label>Motor Response</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Observation Notes -->
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="form-floating">
                <textarea name="nursing_notes" class="form-control" style="min-height: 100px;" 
                          placeholder="General appearance, behavior, comfort level, any concerns"
                          {{ $isReadOnly ? 'readonly' : '' }}>{{ $formData['nursing_notes'] ?? '' }}</textarea>
                <label>Nursing Observations</label>
            </div>
        </div>
    </div>

    <!-- Observation Time -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="datetime-local" name="observation_time" class="form-control" 
                       value="{{ isset($formData['observation_time']) ? \Carbon\Carbon::parse($formData['observation_time'])->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" 
                       {{ $isReadOnly ? 'readonly' : '' }} required>
                <label>Observation Time <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="observer_name" class="form-control" 
                       value="{{ $formData['observer_name'] ?? auth()->user()->name ?? '' }}" 
                       {{ $isReadOnly ? 'readonly' : '' }} required>
                <label>Observer Name <span class="text-danger">*</span></label>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('procedures.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if($editMode && isset($existingData['_result_id']))
                        <a href="{{ route('procedures.view-results', $investigation) }}" class="btn btn-outline-info ms-2" target="_blank">
                            <i class="fas fa-file-pdf"></i> View Current Report
                        </a>
                    @endif
                </div>
                @if(!$isReadOnly)
                    <div>
                        @if($editMode)
                            <!-- Edit mode buttons -->
                            @if($existingData['_result_status'] === 'draft')
                                <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                    <i class="fas fa-save"></i> Update Draft
                                </button>
                                <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                    <i class="fas fa-clock"></i> Save as Preliminary
                                </button>
                                <button type="submit" name="action" value="final" class="btn btn-success">
                                    <i class="fas fa-lock"></i> Finalize Results
                                </button>
                            @elseif($existingData['_result_status'] === 'preliminary')
                                <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Update Preliminary
                                </button>
                                <button type="submit" name="action" value="final" class="btn btn-success">
                                    <i class="fas fa-lock"></i> Finalize Results
                                </button>
                            @endif
                        @else
                            <!-- New result mode buttons -->
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                                <i class="fas fa-clock"></i> Submit as Preliminary
                            </button>
                            <button type="submit" name="action" value="final" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Submit Final Results
                            </button>
                        @endif
                    </div>
                @else
                    <div>
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-lock"></i> Results Finalized
                        </span>
                        @if(isset($existingData['_updated_at']))
                            <small class="text-muted ms-2">
                                Finalized: {{ \Carbon\Carbon::parse($existingData['_updated_at'])->format('M d, Y H:i') }}
                            </small>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>
@else
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Error: Investigation not found. Please return to the procedures list and try again.
    </div>
@endif
