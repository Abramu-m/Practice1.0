<!-- Systemic Examination Modal -->
<div class="modal fade" id="systemicExaminationModal" tabindex="-1" aria-labelledby="systemicExaminationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <div>
                    <h5 class="modal-title" id="systemicExaminationModalLabel">
                        <i class="fas fa-user-md"></i> Systemic Examination
                    </h5>
                    <div id="systemicExaminationPatientBadge" class="badge bg-info mt-1"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Main Examination Form (Left Side) -->
                    <div class="col-md-7">
                        <form id="systemicExaminationModalForm">
                            @csrf
                            <input type="hidden" name="examination_id" id="modalExaminationId">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label"><strong>Examination Type</strong></label>
                                    <select class="form-control" name="examination_type">
                                        <option value="General">General Examination</option>
                                        <option value="Systemic" selected>Systemic Examination</option>
                                        <option value="Focused">Focused Examination</option>
                                        <option value="Follow-up">Follow-up Examination</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label"><strong>General Findings</strong></label>
                                    <textarea class="form-control" name="general_findings" rows="2" 
                                            placeholder="General examination findings..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-heart text-danger"></i> Cardiovascular System</label>
                                    <textarea class="form-control form-control-sm" name="cardiovascular_system" rows="2" 
                                            placeholder="Heart sounds, murmurs, peripheral pulses..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-lungs text-info"></i> Respiratory System</label>
                                    <textarea class="form-control form-control-sm" name="respiratory_system" rows="2" 
                                            placeholder="Breath sounds, chest expansion..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-stomach text-warning"></i> Gastrointestinal System</label>
                                    <textarea class="form-control form-control-sm" name="gastrointestinal_system" rows="2" 
                                            placeholder="Abdomen inspection, palpation, bowel sounds..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-brain text-primary"></i> Nervous System</label>
                                    <textarea class="form-control form-control-sm" name="nervous_system" rows="2" 
                                            placeholder="Consciousness, reflexes, motor/sensory function..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-bone text-secondary"></i> Musculoskeletal System</label>
                                    <textarea class="form-control form-control-sm" name="musculoskeletal_system" rows="2" 
                                            placeholder="Joint mobility, muscle strength, deformities..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-kidneys text-info"></i> Genitourinary System</label>
                                    <textarea class="form-control form-control-sm" name="genitourinary_system" rows="2" 
                                            placeholder="Renal angle tenderness, urogenital examination..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-hand-paper text-warning"></i> Skin Examination</label>
                                    <textarea class="form-control form-control-sm" name="skin_examination" rows="2" 
                                            placeholder="Skin color, rashes, lesions, temperature..."></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-head-side-virus text-danger"></i> Psychiatric Assessment</label>
                                    <textarea class="form-control form-control-sm" name="psychiatric_assessment" rows="2" 
                                            placeholder="Mental state, orientation, mood, behavior..."></textarea>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label"><strong>Additional Notes</strong></label>
                                    <textarea class="form-control" name="notes" rows="2" 
                                            placeholder="Any additional examination notes..."></textarea>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="window.closeSystemicExaminationModal()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-danger me-2" id="modalDeleteSystemicExamBtn">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <button type="button" class="btn btn-success" id="modalSaveSystemicExamBtn">
                                    <i class="fas fa-save"></i> 
                                    <span class="btn-text">Save Examination</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Examinations List (Right Side) -->
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-list"></i> Recorded Examinations</h6>
                            </div>
                            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                                <div id="modalExaminationsList">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-spinner fa-spin"></i> Loading examinations...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
