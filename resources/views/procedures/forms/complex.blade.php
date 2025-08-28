@if(isset($investigation) && $investigation && $investigation->id)

@php
    // Get existing form data if available
    $existingResult = $investigation->results->first();
    $formData = $existingResult && $existingResult->form_data ? $existingResult->form_data : [];
    $isReadOnly = $existingResult && $existingResult->form_status === 'final';
@endphp

<form action="{{ route('procedures.store-result', $investigation->id) }}" method="POST" enctype="multipart/form-data" class="procedure-form">
    @csrf
    <input type="hidden" name="result_type" value="{{ $investigation->medicalService->resultTemplate->code }}">
    <input type="hidden" name="investigation_id" value="{{ $investigation->id }}">

    <div class="row">
        <div class="col-md-12 mb-3">
            <div id="save-indicator" class="float-end"></div>
            <h6 class="text-primary mb-3">
                <i class="fas fa-clipboard-list"></i>
                Specialized Investigation Form
                <small class="text-muted">{{ $investigation->medicalService->name ?? 'Complex Assessment' }}</small>
            </h6>
        </div>
    </div>

    @if(str_contains(strtolower($investigation->medicalService->name ?? ''), 'fertility'))
        <!-- Fertility Assessment Questionnaire -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <h6 class="text-secondary mb-3">Fertility Assessment Questionnaire</h6>
                
                <!-- Patient History -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Medical History</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="age_at_menarche" class="form-control" min="8" max="20">
                                    <label>Age at Menarche</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select name="menstrual_cycle_regularity" class="form-select">
                                        <option value="">Select</option>
                                        <option value="regular">Regular (21-35 days)</option>
                                        <option value="irregular">Irregular</option>
                                        <option value="absent">Absent</option>
                                    </select>
                                    <label>Menstrual Cycle</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="cycle_length" class="form-control" min="15" max="60">
                                    <label>Average Cycle Length (days)</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="flow_duration" class="form-control" min="1" max="15">
                                    <label>Flow Duration (days)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reproductive History -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Reproductive History</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="gravida" class="form-control" min="0">
                                    <label>Gravida (Total Pregnancies)</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="para" class="form-control" min="0">
                                    <label>Para (Live Births)</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="abortions" class="form-control" min="0">
                                    <label>Abortions/Miscarriages</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="trying_to_conceive_months" class="form-control" min="0">
                                    <label>Months Trying to Conceive</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select name="previous_fertility_treatment" class="form-select">
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                    <label>Previous Fertility Treatment</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lifestyle Factors -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Lifestyle Factors</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select name="smoking_status" class="form-select">
                                        <option value="never">Never</option>
                                        <option value="former">Former</option>
                                        <option value="current">Current</option>
                                    </select>
                                    <label>Smoking Status</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select name="alcohol_consumption" class="form-select">
                                        <option value="none">None</option>
                                        <option value="occasional">Occasional</option>
                                        <option value="moderate">Moderate</option>
                                        <option value="heavy">Heavy</option>
                                    </select>
                                    <label>Alcohol Consumption</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select name="exercise_frequency" class="form-select">
                                        <option value="none">None</option>
                                        <option value="occasional">Occasional</option>
                                        <option value="regular">Regular</option>
                                        <option value="intense">Intense</option>
                                    </select>
                                    <label>Exercise Frequency</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif(str_contains(strtolower($investigation->medicalService->name ?? ''), 'cardiac'))
        <!-- Cardiac Assessment -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <h6 class="text-secondary mb-3">Cardiac Function Assessment</h6>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Echocardiogram Results</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="ejection_fraction" class="form-control" step="0.1" min="0" max="100">
                                    <label>Ejection Fraction (%)</label>
                                </div>
                                <small class="normal-range">Normal: ≥55%</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select name="wall_motion" class="form-select">
                                        <option value="normal">Normal</option>
                                        <option value="hypokinetic">Hypokinetic</option>
                                        <option value="akinetic">Akinetic</option>
                                        <option value="dyskinetic">Dyskinetic</option>
                                    </select>
                                    <label>Wall Motion</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="valve_function" class="form-control">
                                    <label>Valve Function</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" name="chamber_dimensions" class="form-control">
                                    <label>Chamber Dimensions</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Generic Complex Form -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <h6 class="text-secondary mb-3">Assessment Parameters</h6>
                
                <!-- Dynamic Assessment Fields -->
                <div id="assessment-parameters">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Assessment Parameter</label>
                            <input type="text" name="assessments[0][parameter]" class="form-control" 
                                   placeholder="Parameter name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Finding/Value</label>
                            <input type="text" name="assessments[0][value]" class="form-control" 
                                   placeholder="Result or finding">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Assessment</label>
                            <select name="assessments[0][assessment]" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="abnormal">Abnormal</option>
                                <option value="borderline">Borderline</option>
                                <option value="not_assessed">Not Assessed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Actions</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="addDynamicField('assessment-parameters', 'assessments')">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Clinical Observations -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Clinical Observations</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <textarea name="physical_examination" class="form-control" style="min-height: 100px;" 
                                  placeholder="Physical examination findings"></textarea>
                        <label>Physical Examination</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <textarea name="clinical_impression" class="form-control" style="min-height: 100px;" 
                                  placeholder="Clinical impression and assessment"></textarea>
                        <label>Clinical Impression</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6 class="text-secondary mb-3">Recommendations & Follow-up</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <textarea name="recommendations" class="form-control" style="min-height: 80px;" 
                                  placeholder="Treatment recommendations"></textarea>
                        <label>Recommendations</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-floating">
                        <textarea name="follow_up_plan" class="form-control" style="min-height: 80px;" 
                                  placeholder="Follow-up schedule and additional tests"></textarea>
                        <label>Follow-up Plan</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Conclusion -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="overall_assessment" class="form-select" required>
                    <option value="">Select Overall Assessment</option>
                    <option value="normal">Normal</option>
                    <option value="abnormal">Abnormal</option>
                    <option value="inconclusive">Inconclusive</option>
                    <option value="requires_further_testing">Requires Further Testing</option>
                </select>
                <label>Overall Assessment <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <select name="urgency_level" class="form-select">
                    <option value="routine">Routine</option>
                    <option value="urgent">Urgent</option>
                    <option value="immediate">Immediate Attention</option>
                </select>
                <label>Urgency Level</label>
            </div>
        </div>
    </div>

    <!-- Specialist Details -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="text" name="specialist_name" class="form-control" 
                       value="{{ auth()->user()->name ?? '' }}" required>
                <label>Specialist Name <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-floating">
                <input type="datetime-local" name="assessment_date" class="form-control" 
                       value="{{ now()->format('Y-m-d\TH:i') }}" required>
                <label>Assessment Date <span class="text-danger">*</span></label>
            </div>
        </div>
    </div>

    <!-- File Attachments -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <label class="form-label"><strong>Supporting Documents</strong></label>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Primary Report/Images</label>
                    <input type="file" name="primary_images[]" class="form-control" 
                           accept="image/*,.pdf,.doc,.docx" multiple>
                    <small class="text-muted">Multiple files allowed (Max: 10MB each)</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Additional Documentation</label>
                    <input type="file" name="additional_docs[]" class="form-control" 
                           accept=".pdf,.doc,.docx,.txt" multiple>
                    <small class="text-muted">Reports, referrals, previous results</small>
                </div>
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
                </div>
                <div>
                    @if(!$isReadOnly)
                        <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                            <i class="fas fa-save"></i> Save as Draft
                        </button>
                        <button type="submit" name="action" value="preliminary" class="btn btn-warning">
                            <i class="fas fa-clock"></i> Submit as Preliminary
                        </button>
                        <button type="submit" name="action" value="final" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Submit as Final
                        </button>
                    @else
                        @if($investigation->results && $investigation->results->first() && $investigation->results->first()->form_status !== 'final')
                            <a href="{{ route('procedures.show', $investigation->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Results
                            </a>
                        @endif
                    @endif
                </div>
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
