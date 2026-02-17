<!-- Past Medical History Modal -->
<div class="modal fade" id="pastMedicalHistoryModal" tabindex="-1" aria-labelledby="pastMedicalHistoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div class="d-flex flex-column">
                    <h5 class="modal-title" id="pastMedicalHistoryModalLabel">
                        <i class="fas fa-history me-2"></i> Past Medical History
                    </h5>
                    <small class="text-muted">
                        <span class="badge bg-info" id="pastMedicalHistoryPatientBadge">
                            <i class="fas fa-user"></i> <span id="pastMedicalHistoryPatientName"></span>
                        </span>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pastMedicalHistoryForm">
                    @csrf
                    <input type="hidden" name="patient_id" id="modalPatientId" value="">
                    
                    <div class="row">
                        <!-- Critical Information -->
                        <div class="col-12 mb-3">
                            <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Critical Information</h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label text-danger fw-bold mb-1">
                                <i class="fas fa-pills"></i> Drug Allergies
                            </label>
                            <div class="input-group mb-2">
                                <select id="modalDrugAllergiesSelect" class="form-select select2" data-placeholder="Search and select medication" data-allow-clear="true" style="width:100%">
                                    <option value=""></option>
                                </select>
                                <button type="button" id="modalAddDrugAllergyBtn" class="btn btn-outline-danger">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            <div id="modalDrugAllergyTags" class="mb-2">
                                <!-- dynamically added tags -->
                            </div>
                            <input type="hidden" name="drug_allergies" id="modalDrugAllergiesInput" value="">
                            <small class="text-muted">Select each offending drug and click Add. Remove by clicking the × on a tag.</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-danger fw-bold">
                                <i class="fas fa-exclamation-circle"></i> Other Allergies (Food / Environmental / Reactions):
                            </label>
                            <textarea class="form-control border-danger" name="allergies" id="modalAllergies" rows="2"
                                placeholder="Peanuts – anaphylaxis; Pollen – rhinitis; Latex – rash..."
                                style="resize: vertical;"></textarea>
                            <small class="text-danger">⚠️ Be specific about reactions (rash, anaphylaxis, etc.)</small>
                        </div>

                        <!-- Medical Conditions -->
                        <div class="col-12 mb-3">
                            <h6 class="text-warning"><i class="fas fa-heartbeat"></i> Medical Conditions</h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Chronic Conditions:</label>
                            <textarea class="form-control" name="chronic_conditions" id="modalChronicConditions" rows="2" 
                                    placeholder="Diabetes, Hypertension, Heart Disease, etc."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Current Medications:</label>
                            <textarea class="form-control" name="current_medications" id="modalCurrentMedications" rows="2" 
                                    placeholder="List all current medications with dosages..."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <!-- Surgical History -->
                        <div class="col-12 mb-3">
                            <h6 class="text-info"><i class="fas fa-cut"></i> Surgical History</h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Previous Surgeries:</label>
                            <textarea class="form-control" name="previous_surgeries" id="modalPreviousSurgeries" rows="2" 
                                    placeholder="List surgeries with dates and complications if any..."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <!-- Social History -->
                        <div class="col-12 mb-3">
                            <h6 class="text-secondary"><i class="fas fa-user-friends"></i> Social History</h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Smoking Status:</label>
                            <select class="form-control" name="smoking_status" id="modalSmokingStatus">
                                <option value="">Select status</option>
                                <option value="non_smoker">Non-smoker</option>
                                <option value="former_smoker">Former smoker</option>
                                <option value="current_smoker">Current smoker</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alcohol Use:</label>
                            <select class="form-control" name="alcohol_use" id="modalAlcoholUse">
                                <option value="">Select usage</option>
                                <option value="none">None</option>
                                <option value="occasional">Occasional</option>
                                <option value="moderate">Moderate</option>
                                <option value="heavy">Heavy</option>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Social History Details:</label>
                            <textarea class="form-control" name="social_history" id="modalSocialHistory" rows="2" 
                                    placeholder="Living situation, support system, occupation details..."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Occupational History:</label>
                            <textarea class="form-control" name="occupational_history" id="modalOccupationalHistory" rows="2" 
                                    placeholder="Work history, exposure to hazards, occupational injuries..."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <!-- Family History -->
                        <div class="col-12 mb-3">
                            <h6 class="text-primary"><i class="fas fa-users"></i> Family History</h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Family Medical History:</label>
                            <textarea class="form-control" name="family_history" id="modalFamilyHistory" rows="3" 
                                    placeholder="Family history of diabetes, heart disease, cancer, etc. Include relationship and age at diagnosis..."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-12 mb-3">
                            <h6 class="text-success"><i class="fas fa-plus-circle"></i> Additional Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Immunization History:</label>
                            <textarea class="form-control" name="immunization_history" id="modalImmunizationHistory" rows="2" 
                                    placeholder="Recent vaccinations, immunization status..."
                                    style="resize: vertical;"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reproductive History:</label>
                            <textarea class="form-control" name="reproductive_history" id="modalReproductiveHistory" rows="2" 
                                    placeholder="For female patients: pregnancies, menstrual history..."
                                    style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-success" id="savePastMedicalHistoryBtn">
                    <i class="fas fa-save"></i>
                    <span class="btn-text">Save Medical History</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Drug Allergy Detail Modal (nested for severity/reaction) -->
<div class="modal fade" id="modalDrugAllergyModal" tabindex="-1" aria-labelledby="modalDrugAllergyModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalDrugAllergyModalLabel">Drug Allergy Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Drug:</strong> <span id="modalDrugName"></span></p>
                <div class="mb-3">
                    <label class="form-label">Reaction (optional):</label>
                    <input type="text" class="form-control" id="modalReaction" placeholder="Rash, anaphylaxis, etc." />
                </div>
                <div class="mb-3">
                    <label class="form-label">Severity:</label>
                    <select class="form-control" id="modalSeverity">
                        <option value="">Select severity (optional)</option>
                        <option value="mild">Mild</option>
                        <option value="moderate">Moderate</option>
                        <option value="severe">Severe</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="saveModalDrugAllergyBtn" data-update-id="">
                    <i class="fas fa-save"></i> <span class="btn-text">Add Allergy</span>
                </button>
            </div>
        </div>
    </div>
</div>
