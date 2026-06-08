<div class="modal fade" id="referralLetterModal" tabindex="-1" aria-labelledby="referralLetterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="referralLetterModalLabel">
                    <i class="fas fa-file-medical-alt"></i> Generate Referral Letter
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="referralLetterForm" method="POST" target="_blank" action="{{ route('consultations.referrals.store', $consultation->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="referralHospital" class="form-label">Referral Hospital</label>
                            <select id="referralHospital" name="referral_hospital_id" class="form-select" required>
                                <option value="">Select hospital</option>
                                @foreach($referralHospitals as $hospital)
                                    <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="referralDepartment" class="form-label">Referral Department</label>
                            <select id="referralDepartment" name="referral_department_id" class="form-select" required disabled>
                                <option value="">Select department</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="letterHeading" class="form-label">Letter Heading</label>
                            <input type="text" id="letterHeading" name="letter_heading" class="form-control" required placeholder="Referral for specialist review" value="Referral for Specialist Care">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="letterTemplate" class="form-label">Referral Letter Body</label>
                        <textarea id="letterTemplate" name="letter_template" rows="8" class="form-control" required>
Dear Consultant,

I am referring the patient below for further evaluation and management.

Please find the attached case summary and relevant investigation results.
                        </textarea>
                    </div>

                    <div class="mb-3">
                        <label for="additionalNotes" class="form-label">Additional Notes</label>
                        <textarea id="additionalNotes" name="additional_notes" rows="4" class="form-control" placeholder="Add any extra clinical details, follow up requests, or specific points for the receiving team."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="letterClosing" class="form-label">Letter Closing</label>
                        <textarea id="letterClosing" name="letter_closing" rows="3" class="form-control" placeholder="Yours sincerely,\nDr. ...\nClinical Officer">Yours sincerely,

{{ Auth::user()->name ?? 'Clinical Team' }}</textarea>
                    </div>

                    <div class="alert alert-secondary">
                        <strong>Note:</strong> Submitting will save the referral in the patient referrals table and open a PDF containing the referral letter plus the case summary.
                    </div>
                </div>
                <div class="modal-footer" style="position:sticky;bottom:0;z-index:1055;background:#fff;border-top:1px solid #e9ecef;padding:0.75rem 1rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary ms-2">
                        <i class="fas fa-file-pdf"></i> Generate Referral PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
