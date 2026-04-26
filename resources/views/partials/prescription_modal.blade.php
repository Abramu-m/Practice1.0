<!-- Prescription Modal -->
<div class="modal fade" id="prescriptionModal" tabindex="-1" aria-labelledby="prescriptionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prescriptionModalLabel">
                    <i class="fas fa-prescription"></i> Add Prescription
                    <span class="badge bg-info ms-2" id="modal_prescription_patient_name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left: Prescription Form -->
                    <div class="col-md-7">
                        <form id="prescriptionModalForm">
                            @csrf
                            <input type="hidden" id="modal_prescription_patient_id" name="patient_id">
                            <input type="hidden" id="modal_prescription_visit_id" name="visit_id">
                            <input type="hidden" id="modal_prescription_patient_category_id" name="patient_category_id">
                            
                            <!-- Medication Search -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Medication <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="modal_medication_search" 
                                           placeholder="Type at least 2 characters to search medications..." 
                                           autocomplete="off">
                                    <div id="modal_medication_suggestions" 
                                         class="position-absolute w-100 bg-white border shadow-lg d-none" 
                                         style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                        <!-- Medication suggestions will be populated here -->
                                    </div>
                                </div>
                                <input type="hidden" name="medication_id" id="modal_selected_medication_id" required>
                                <small class="text-muted">Search by generic name, brand name, or code</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dosage <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="dosage" 
                                           placeholder="e.g., 500mg"
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                    <select class="form-control" name="frequency_id" required>
                                        <option value="">Select Frequency</option>
                                        @if(isset($frequencies))
                                            @foreach($frequencies as $frequency)
                                                <option value="{{ $frequency->id }}">{{ $frequency->frequency_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="duration_days" 
                                           placeholder="e.g., 7"
                                           min="1"
                                           required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="quantity" 
                                           placeholder="Qty"
                                           min="1"
                                           required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Route</label>
                                    <select class="form-control" name="administration_route_id">
                                        <option value="">Select Route</option>
                                        @if(isset($routes))
                                            @foreach($routes as $route)
                                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Instructions</label>
                                <textarea class="form-control" 
                                          name="instructions" 
                                          rows="2"
                                          placeholder="Special instructions for taking medication..."></textarea>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Right: Current Prescriptions -->
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    Current Prescriptions
                                    <span class="badge bg-secondary ms-2" id="modal_prescriptions_count">0</span>
                                </h6>
                            </div>
                            <div class="card-body p-2" style="max-height: 500px; overflow-y: auto;">
                                <div id="modal_current_prescriptions_section">
                                    <p class="text-muted text-center py-3">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" id="savePrescriptionModalBtn" class="btn btn-warning" onclick="savePrescriptionFromModal()">
                    <i class="fas fa-save"></i> Add Prescription
                </button>
            </div>
        </div>
    </div>
</div>
