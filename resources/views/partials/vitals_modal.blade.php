<!-- Vitals Modal -->
<div class="modal fade" id="vitalsModal" tabindex="-1" aria-labelledby="vitalsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div class="d-flex flex-column">
                    <h5 class="modal-title" id="vitalsModalLabel">
                        <i class="fas fa-thermometer-half me-2"></i> Full Vitals & History
                    </h5>
                    <small class="text-muted">
                        <span class="badge bg-info" id="vitalsPatientBadge">
                            <i class="fas fa-user"></i> <span id="vitalsPatientName"></span>
                        </span>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Current Vitals Display -->
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-line"></i> Current Vital Signs</h6>
                            </div>
                            <div class="card-body" id="currentVitalsDisplay">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Loading current vitals...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vitals History -->
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-history"></i> Vitals History</h6>
                                <button type="button" class="btn btn-sm btn-light" onclick="toggleVitalsForm()">
                                    <i class="fas fa-plus"></i> Add New Vitals
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="vitalsHistoryTable">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Loading history...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Update Vitals Form (Initially Hidden) -->
                    <div class="col-md-12 mb-3" id="vitalsFormContainer" style="display: none;">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-edit"></i> Record Vital Signs</h6>
                                <button type="button" class="btn btn-sm btn-light" onclick="toggleVitalsForm()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            <div class="card-body">
                                <form id="vitalsForm">
                                    @csrf
                                    <input type="hidden" name="visit_id" id="modal_vitals_visit_id" value="">
                                    
                                    <div class="row">
                                        <!-- Blood Pressure -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-heartbeat text-danger"></i> Blood Pressure</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="systolic_bp" id="modal_systolic_bp"
                                                       placeholder="Systolic" min="0" max="300">
                                                <span class="input-group-text">/</span>
                                                <input type="number" class="form-control" name="diastolic_bp" id="modal_diastolic_bp"
                                                       placeholder="Diastolic" min="0" max="200">
                                            </div>
                                            <small class="text-muted">mmHg (e.g., 120/80)</small>
                                        </div>

                                        <!-- Pulse Rate -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-heartbeat text-danger"></i> Pulse Rate</label>
                                            <input type="number" class="form-control" name="pulse_rate" id="modal_pulse_rate"
                                                   placeholder="Enter pulse rate" min="0" max="220" step="1">
                                            <small class="text-muted">beats/min</small>
                                        </div>

                                        <!-- Temperature -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-thermometer-half text-warning"></i> Temperature</label>
                                            <input type="number" class="form-control" name="temperature" id="modal_temperature"
                                                   placeholder="Enter temperature" min="30" max="50" step="0.1">
                                            <small class="text-muted">°C</small>
                                        </div>

                                        <!-- Respiratory Rate -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-lungs text-info"></i> Respiratory Rate</label>
                                            <input type="number" class="form-control" name="respiratory_rate" id="modal_respiratory_rate"
                                                   placeholder="Enter rate" min="0" max="60" step="1">
                                            <small class="text-muted">breaths/min</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Weight -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-weight text-primary"></i> Weight</label>
                                            <input type="number" class="form-control" name="weight" id="modal_weight"
                                                   placeholder="Enter weight" min="0" max="500" step="0.1">
                                            <small class="text-muted">kg</small>
                                        </div>

                                        <!-- Height -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-ruler-vertical text-primary"></i> Height</label>
                                            <input type="number" class="form-control" name="height" id="modal_height"
                                                   placeholder="Enter height" min="0" max="300" step="0.1">
                                            <small class="text-muted">cm</small>
                                        </div>

                                        <!-- Oxygen Saturation -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-wind text-info"></i> Oxygen Saturation</label>
                                            <input type="number" class="form-control" name="oxygen_saturation" id="modal_oxygen_saturation"
                                                   placeholder="Enter SpO2" min="0" max="100" step="1">
                                            <small class="text-muted">%</small>
                                        </div>

                                        <!-- BMI (Auto-calculated) -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label"><i class="fas fa-calculator text-success"></i> BMI</label>
                                            <input type="number" class="form-control" name="bmi" id="modal_bmi"
                                                   placeholder="Auto-calculated" readonly>
                                            <small class="text-muted">kg/m²</small>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-3">
                                        <label class="form-label"><i class="fas fa-notes-medical"></i> Notes</label>
                                        <textarea class="form-control" name="notes" id="modal_vitals_notes" rows="2"
                                                  placeholder="Additional observations or notes..."></textarea>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" onclick="toggleVitalsForm()">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                        <button type="button" class="btn btn-success" id="saveVitalsBtn">
                                            <i class="fas fa-save"></i> <span class="btn-text">Save Vitals</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
