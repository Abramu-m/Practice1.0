<div class="container-fluid" style="max-width: 800px; font-size: 12px;">
    {{-- Official Header --}}
    <div class="text-center mb-2">
        <div class="d-flex justify-content-center align-items-center mb-1">
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiMwMDY2MzMiLz4KPHN0YXIgY3g9IjIwIiBjeT0iMjAiIHI9IjEwIiBmaWxsPSIjRkZGRjAwIi8+Cjwvc3ZnPgo=" alt="Coat of Arms" style="width: 30px; height: 30px;" class="me-2">
            <div>
                <div style="font-size: 11px; font-weight: bold; line-height: 1.1;">The United Republic of Tanzania</div>
                <div style="font-size: 10px; font-weight: bold; color: #006633;">MINISTRY OF HEALTH</div>
                <div style="font-size: 9px;">National TB and Leprosy Programme</div>
            </div>
        </div>
        <div style="font-size: 11px; font-weight: bold; text-decoration: underline; margin-top: 8px;">
            REQUEST AND REPORT FORM FOR BIOLOGICAL SPECIMEN FOR TB AND LEPROSY
        </div>
    </div>

    {{-- Patient Information Section --}}
    <div class="row g-1 mb-2" style="font-size: 11px;">
        <div class="col-6">
            <div class="row">
                <div class="col-5"><strong>Name of health facility:</strong></div>
                <div class="col-7">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ config('app.clinic_name', 'BRIGITA General Clinic') }}" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" readonly>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-4"><strong>Date of request:</strong></div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ \Carbon\Carbon::parse($visit->created_at ?? now())->format('Y-m-d') }}" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-1 mb-2" style="font-size: 11px;">
        <div class="col-6">
            <div class="row">
                <div class="col-4"><strong>Name of Patient:</strong></div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ strtoupper(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" readonly>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div class="row">
                <div class="col-4"><strong>Age:</strong></div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ $visit->patientInfo->age ?? '' }}" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" readonly>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div class="row">
                <div class="col-4"><strong>Sex:</strong></div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ strtoupper(substr($visit->patientInfo->gender ?? '', 0, 1)) }}" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" readonly>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div class="row">
                <div class="col-5"><strong>TB District No:</strong></div>
                <div class="col-7">
                    <input type="text" class="form-control form-control-sm" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" 
                           name="tb_district_no">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-1 mb-3" style="font-size: 11px;">
        <div class="col-6">
            <div class="row">
                <div class="col-4"><strong>Physical address:</strong></div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" 
                           value="{{ $visit->patientInfo->address ?? '' }}" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" readonly>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-4"><strong>Laboratory Serial No:</strong></div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" 
                           style="height: 22px; font-size: 10px; border: none; border-bottom: 1px solid #000; background: transparent;" 
                           name="lab_serial_no">
                </div>
            </div>
        </div>
    </div>

    {{-- Reason for Examination --}}
    <div class="mb-2">
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">Reason for examination</div>
        <div class="d-flex flex-wrap gap-3" style="font-size: 10px;">
            <label class="form-check-label">
                <input class="form-check-input" type="radio" name="reason" value="diagnosis" id="diagnosis" checked style="transform: scale(0.8);">
                <span class="ms-1">● Diagnosis</span>
                <span class="ms-2">If diagnosis: ● TB ○ MDR ○ Leprosy</span>
            </label>
        </div>
        <div class="mt-1" style="font-size: 10px;">
            <label class="form-check-label">
                <input class="form-check-input" type="radio" name="reason" value="followup" id="followup" style="transform: scale(0.8);">
                <span class="ms-1">○ Follow-up. If follow up, specify at treatment:</span>
            </label>
        </div>
    </div>

    {{-- Clinical Information --}}
    <div class="row g-1 mb-2" style="font-size: 11px;">
        <div class="col-6">
            <div><strong>HIV status:</strong></div>
            <div class="d-flex gap-2 mt-1" style="font-size: 10px;">
                <label><input type="radio" name="hiv_status" value="reactive" style="transform: scale(0.8);"> ○ Reactive</label>
                <label><input type="radio" name="hiv_status" value="non_reactive" style="transform: scale(0.8);"> ○ Non Reactive</label>
                <label><input type="radio" name="hiv_status" value="unknown" checked style="transform: scale(0.8);"> ● Unknown</label>
            </div>
        </div>
        <div class="col-6">
            <div><strong>Previously treated for TB:</strong></div>
            <div class="d-flex gap-2 mt-1" style="font-size: 10px;">
                <label><input type="radio" name="previous_tb" value="yes" style="transform: scale(0.8);"> ○ Yes</label>
                <label><input type="radio" name="previous_tb" value="no" checked style="transform: scale(0.8);"> ● No</label>
            </div>
        </div>
    </div>

    {{-- Specimen Information --}}
    <div class="mb-2">
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">Specimen type:</div>
        <div class="d-flex flex-wrap gap-2" style="font-size: 10px;">
            <label><input type="radio" name="specimen" value="sputum" checked style="transform: scale(0.8);"> ● Sputum</label>
            <label><input type="radio" name="specimen" value="csf" style="transform: scale(0.8);"> ○ CSF</label>
            <label><input type="radio" name="specimen" value="peritoneal" style="transform: scale(0.8);"> ○ Peritoneal fluid</label>
            <label><input type="radio" name="specimen" value="skin" style="transform: scale(0.8);"> ○ Skin smear</label>
            <label><input type="radio" name="specimen" value="pleural" style="transform: scale(0.8);"> ○ Pleural fluid</label>
            <label><input type="radio" name="specimen" value="lymph" style="transform: scale(0.8);"> ○ Lymph node</label>
        </div>
    </div>

    {{-- Test Requested --}}
    <div class="mb-2">
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">Test(s) requested:</div>
        <div class="d-flex gap-3" style="font-size: 10px;">
            <label><input type="checkbox" name="test_requested[]" value="microscopy" style="transform: scale(0.8);"> ☐ Microscopy</label>
            <label><input type="checkbox" name="test_requested[]" value="xpert" checked style="transform: scale(0.8);"> ☑ Xpert MTB/RIF</label>
        </div>
    </div>

    {{-- Clinical Symptoms (Required) --}}
    <div class="mb-3">
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">
            Clinical Symptoms: <span class="text-danger">*</span>
        </div>
        <textarea class="form-control" name="clinical_symptoms" required rows="2" 
                  placeholder="Please describe patient symptoms (e.g., cough, fever, weight loss, night sweats...)" 
                  style="font-size: 10px; border: 1px solid #ddd;"></textarea>
        <small class="text-muted" style="font-size: 9px;">This field is required for TB investigation</small>
    </div>

    {{-- Requesting Officer --}}
    <div class="row g-1 mb-2" style="font-size: 10px;">
        <div class="col-6">
            <strong>Name and signature of person requesting examination:</strong>
            <div style="border-bottom: 1px solid #000; height: 20px; margin-top: 5px;">
                {{ optional(optional($visit->doctorInfo)->user)->name ?? 'Dr. ' . (optional($visit->doctorInfo)->first_name ?? '') . ' ' . (optional($visit->doctorInfo)->last_name ?? '') }}
            </div>
        </div>
        <div class="col-6">
            <strong>Area leader signature:</strong>
            <div style="border-bottom: 1px solid #000; height: 20px; margin-top: 5px;"></div>
        </div>
    </div>
    {{-- Contact Information --}}
    <div class="row g-1 mb-3" style="font-size: 10px;">
            <div class="col-6">
            <strong>Requesting clinician:</strong>
            <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">
                {{ optional(optional($visit->doctorInfo)->user)->name ?? '' }}
            </div>
        </div>
        <div class="col-6">
            <strong>Feedback contact (RTLC / DTLC):</strong>
            <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">
                Dr. Iddi K. Njarita (0627826480)
            </div>
        </div>
    </div>
</div>

    {{-- Clinical Information Section --}}
    <div class="mb-4" style="border: 1px solid #000; padding: 10px; font-size: 10px;">
        <div class="text-center mb-2"><strong>CLINICAL INFORMATION</strong></div>
        <div class="mb-2">
            <strong>Clinical symptoms and signs (mandatory):</strong>
        </div>

    <hr style="border: 2px solid #000; margin: 20px 0;">
    
    {{-- Laboratory Results Section --}}
    <div style="text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px;">
        LABORATORY RESULTS
    </div>

    {{-- Xpert MTB/RIF Result Section --}}
    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px; font-size: 10px;">
        <div class="text-center mb-2"><strong>Xpert MTB/RIF Result</strong></div>
        
        <div class="row g-1 mb-2">
            <div class="col-6">
                <strong>Laboratory Serial No:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">775/2023</div>
            </div>
            <div class="col-6">
                <strong>Date of Reception:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">30/12/2023 - 10:12</div>
            </div>
        </div>

        <div class="row g-1 mb-2">
                    <div class="col-6">
                <strong>ZN / FM:</strong>
                <div class="d-flex gap-2 mt-1">
                    <label><input type="checkbox" name="zn_fm[]" value="zn"> ZN</label>
                    <label><input type="checkbox" name="zn_fm[]" value="fm"> FM</label>
                </div>
            </div>
            <div class="col-6">
                <strong>Specimen Appearance:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">Salivary</div>
            </div>
        </div>

        <div class="mb-2">
            <strong>Xpert Result:</strong>
            <div class="mt-1">
                <div><label><input type="radio" name="xpert_result" value="not_detected"> MTB Not Detected (N)</label></div>
                <div><label><input type="radio" name="xpert_result" value="detected_susceptible" checked> MTB Detected - Rifampicin Resistance Not Detected (T)</label></div>
                <div><label><input type="radio" name="xpert_result" value="detected_resistant"> MTB Detected - Rifampicin Resistance Detected (RR)</label></div>
                <div><label><input type="radio" name="xpert_result" value="detected_indeterminate"> MTB Detected - Rifampicin Resistance Indeterminate (TI)</label></div>
                <div><label><input type="radio" name="xpert_result" value="error"> Error / No Result / Invalid (I)</label></div>
            </div>
        </div>

        <div class="row g-1">
            <div class="col-6">
                <strong>Date of Exam:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">30/12/2023 - 12:50</div>
            </div>
            <div class="col-6">
                <strong>Examined by:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">Charles Omary</div>
            </div>
        </div>
    </div>

    {{-- Skin Smear Result Section --}}
    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px; font-size: 10px;">
        <div class="text-center mb-2"><strong>Skin Smear Result</strong></div>
        
        <div class="row g-1 mb-2">
            <div class="col-3"><strong>Left Earlobe:</strong></div>
            <div class="col-3">
                <select name="left_earlobe" class="form-select form-select-sm" style="font-size: 9px;">
                    <option>neg</option><option>Scanty</option><option>1+</option><option>2+</option><option>3+</option>
                </select>
            </div>
            <div class="col-3"><strong>Right Earlobe:</strong></div>
            <div class="col-3">
                <select name="right_earlobe" class="form-select form-select-sm" style="font-size: 9px;">
                    <option>neg</option><option>Scanty</option><option>1+</option><option>2+</option><option>3+</option>
                </select>
            </div>
        </div>

        <div class="row g-1 mb-2">
            <div class="col-3"><strong>Lesion 1:</strong></div>
            <div class="col-3">
                <select name="lesion_1" class="form-select form-select-sm" style="font-size: 9px;">
                    <option>neg</option><option>Scanty</option><option>1+</option><option>2+</option><option>3+</option>
                </select>
            </div>
            <div class="col-3"><strong>Lesion 2:</strong></div>
            <div class="col-3">
                <select name="lesion_2" class="form-select form-select-sm" style="font-size: 9px;">
                    <option>neg</option><option>Scanty</option><option>1+</option><option>2+</option><option>3+</option>
                </select>
            </div>
        </div>

        <div class="row g-1 mb-2">
            <div class="col-6">
                <strong>Reviewed by:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">Rebecca Korduni</div>
            </div>
            <div class="col-6">
                <strong>Review Date:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">30/12/2023 - 12:50</div>
            </div>
        </div>

        <div class="mb-2">
            <strong>Comments:</strong>
            <div style="border: 1px solid #000; height: 30px; margin-top: 5px;"></div>
        </div>

        <div class="row g-1">
            <div class="col-6">
                <strong>Result Verified by:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;"></div>
            </div>
            <div class="col-6">
                <strong>Signature / Time:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;"></div>
            </div>
        </div>
    </div>

</div>
