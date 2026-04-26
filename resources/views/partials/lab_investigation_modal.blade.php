{{-- Lab Investigation Modal Component --}}
{{-- This modal can be included in any view to provide lab investigation ordering functionality --}}
{{-- Usage: @include('partials.lab_investigation_modal') --}}

<!-- Lab Investigation Modal -->
<div class="modal fade" id="labInvestigationModal" tabindex="-1" aria-labelledby="labInvestigationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labInvestigationModalLabel">
                    <i class="fas fa-flask"></i> Add Lab Investigation - <span id="modal_patient_name" class="badge bg-info text-dark"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="labInvestigationForm">
                    @csrf
                    <input type="hidden" id="modal_patient_id" name="patient_id">
                    <input type="hidden" id="modal_visit_id" name="visit_id">
                    <input type="hidden" id="modal_patient_category_id" name="patient_category_id">
                    <input type="hidden" name="quantity" value="1" id="modal_investigation_quantity">
                    
                    <!-- Current Investigations Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list"></i> Current Investigations for this Visit
                                        <span class="badge bg-secondary ms-2" id="investigations_count">0</span>
                                    </h6>
                                </div>
                                <div class="card-body py-2" id="current_investigations_section">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Loading investigations...
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Complex Results Modal (nested modal for viewing results) -->
                        <div class="modal fade" id="complexResultsModal" tabindex="-1" role="dialog" aria-labelledby="complexResultsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="complexResultsModalLabel">
                                            <i class="fas fa-chart-line"></i> Investigation Results
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="complexResultsContent" style="max-height: 70vh; overflow-y: auto;">
                                        <div class="d-flex justify-content-center">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="#" id="printComplexResult" class="btn btn-primary">
                                            <i class="fas fa-print"></i> Print Results
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Service Selection -->
                    <div class="row">
                        <div class="col-9">
                            <div class="mb-3">
                                <label class="form-label">Search Medical Service *</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="modal_service_search" 
                                           placeholder="Type to search for Investigations/Procedures..." autocomplete="off">
                                    <div id="modal_service_suggestions" class="suggestions-dropdown">
                                        <!-- Service suggestions will be populated here -->
                                    </div>
                                </div>
                                <input type="hidden" name="medical_service_id" id="modal_selected_service_id" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-control" name="priority">
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clinical Notes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Clinical Notes</label>
                                <textarea class="form-control" name="notes" rows="3" 
                                        placeholder="Clinical indication for investigation..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Service Info Display -->
                    <div id="modal-service-info" class="alert alert-info" style="display: none;"></div>
                    
                    <!-- Form type info and form display (populated when a service requires a form) -->
                    <div id="form-type-info-container" style="display: none;" class="mt-3"></div>
                    <div id="form-display-container" style="display: none;" class="mt-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveLabInvestigationBtn" class="btn btn-warning" onclick="saveLabInvestigation()">
                    <i class="fas fa-save"></i> Order Investigation
                </button>
            </div>
        </div>
    </div>
</div>
