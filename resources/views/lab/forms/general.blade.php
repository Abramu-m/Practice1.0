{{-- General Investigation Form - Official Format --}}
<div class="investigation-form-container" style="font-family: Arial, sans-serif; font-size: 10px; max-width: 100%; margin: 0 auto;">

    {{-- Header Section --}}
    <div class="text-center mb-3" style="border-bottom: 2px solid #000; padding-bottom: 10px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="width: 60px; height: 60px; border: 1px solid #000; display: flex; align-items: center; justify-content: center;">
                <strong style="font-size: 8px;">COAT OF<br>ARMS</strong>
            </div>
            <div style="flex: 1; text-align: center;">
                <div style="font-size: 12px; font-weight: bold;">UNITED REPUBLIC OF TANZANIA</div>
                <div style="font-size: 11px; font-weight: bold;">MINISTRY OF HEALTH</div>
                <div style="font-size: 10px; margin-top: 5px;">{{ $visit->facility->name ?? 'MEDICAL FACILITY' }}</div>
                <div style="font-size: 14px; font-weight: bold; margin-top: 8px; text-decoration: underline;">
                    GENERAL LABORATORY INVESTIGATION REQUEST FORM
                </div>
            </div>
            <div style="width: 60px; height: 60px; border: 1px solid #000; display: flex; align-items: center; justify-content: center;">
                <strong style="font-size: 8px;">FACILITY<br>LOGO</strong>
            </div>
        </div>
    </div>

    {{-- Patient Information Section --}}
    <div class="mb-3" style="border: 1px solid #000; padding: 8px;">
        <div class="row g-1 mb-2">
            <div class="col-3">
                <strong>Patient Name:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ strtoupper(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}
                </div>
            </div>
            <div class="col-2">
                <strong>Age:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ $visit->patientInfo->age ?? '' }}
                </div>
            </div>
            <div class="col-2">
                <strong>Sex:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ strtoupper($visit->patientInfo->gender ?? '') }}
                </div>
            </div>
            <div class="col-2">
                <strong>Date:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ date('d/m/Y') }}
                </div>
            </div>
            <div class="col-3">
                <strong>File No:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ $visit->patientInfo->file_number ?? $visit->patientInfo->id ?? '' }}
                </div>
            </div>
        </div>

        <div class="row g-1 mb-2">
            <div class="col-4">
                <strong>Address:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ $visit->patientInfo->address ?? '' }}
                </div>
            </div>
            <div class="col-3">
                <strong>Ward/Clinic:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ $visit->department ?? 'OPD' }}
                </div>
            </div>
            <div class="col-2">
                <strong>Time:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ date('H:i') }}
                </div>
            </div>
            <div class="col-3">
                <strong>Phone:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 3px;">
                    {{ $visit->patientInfo->phone_number ?? '' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Investigation Request Section --}}
    <div class="mb-3" style="border: 1px solid #000; padding: 8px;">
        <div class="mb-2"><strong>INVESTIGATION REQUESTED:</strong></div>

        <div class="row">
            <div class="col-6">
                <div class="mb-2">
                    <strong>Laboratory Tests:</strong>
                    <div class="mt-1">
                        <div><label><input type="checkbox" name="lab_tests[]" value="blood_count"> Complete Blood Count (CBC)</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="glucose"> Blood Glucose</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="urea"> Urea &amp; Electrolytes</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="liver"> Liver Function Tests</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="lipid"> Lipid Profile</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="urine"> Urine Analysis</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="malaria"> Malaria Test</label></div>
                        <div><label><input type="checkbox" name="lab_tests[]" value="hiv"> HIV Test</label></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-2">
                    <strong>Imaging Studies:</strong>
                    <div class="mt-1">
                        <div><label><input type="checkbox" name="imaging[]" value="xray_chest"> Chest X-Ray</label></div>
                        <div><label><input type="checkbox" name="imaging[]" value="xray_abdomen"> Abdominal X-Ray</label></div>
                        <div><label><input type="checkbox" name="imaging[]" value="ultrasound"> Ultrasound</label></div>
                        <div><label><input type="checkbox" name="imaging[]" value="ct_scan"> CT Scan</label></div>
                        <div><label><input type="checkbox" name="imaging[]" value="ecg"> ECG</label></div>
                        <div><label><input type="checkbox" name="imaging[]" value="echo"> Echocardiogram</label></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2">
            <strong>Other (specify):</strong>
            <div style="border-bottom: 1px solid #000; height: 20px; margin-top: 3px;"></div>
        </div>
    </div>

    {{-- Clinical Information Section --}}
    <div class="mb-3" style="border: 1px solid #000; padding: 8px;">
        <div class="mb-2"><strong>CLINICAL INFORMATION:</strong></div>
        <div class="mb-2">
            <strong>Provisional/Confirmed Diagnosis:</strong>
        </div>
        <textarea class="form-control" name="clinical_notes" required rows="3"
                  placeholder="Enter clinical notes and provisional/confirmed diagnosis..."
                  style="font-size: 10px; border: 1px solid #ddd;"></textarea>

        <div class="row g-1 mt-2">
            <div class="col-6">
                <strong>Requesting Clinician:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">
                    {{ auth()->user()->name ?? optional(optional($visit->doctorInfo)->user)->name ?? '' }}
                </div>
            </div>
            <div class="col-6">
                <strong>Signature &amp; Stamp:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;"></div>
            </div>
        </div>
    </div>

    {{-- Specimen Collection Section --}}
    <div class="mb-3" style="border: 1px solid #000; padding: 8px;">
        <div class="mb-2"><strong>SPECIMEN COLLECTION:</strong></div>
        <div class="row g-1">
            <div class="col-4">
                <strong>Collection Date:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;"></div>
            </div>
            <div class="col-4">
                <strong>Collection Time:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;"></div>
            </div>
            <div class="col-4">
                <strong>Collected by:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;"></div>
            </div>
        </div>
    </div>

    <hr style="border: 2px solid #000; margin: 20px 0;">

    {{-- Laboratory Results Section --}}
    <div style="text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px;">
        LABORATORY RESULTS
    </div>

    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px;">
        <div class="row g-1 mb-2">
            <div class="col-4">
                <strong>Lab Serial No:</strong>
                <input type="text" class="form-control form-control-sm" name="lab_serial_no"
                       placeholder="e.g., LAB-{{ date('Y') }}-001" style="margin-top: 3px;">
            </div>
            @php
                $dateReceived = isset($investigation) ? $investigation->collected_at : null;
                $dateReported = isset($investigation) ? $investigation->resulted_at : null;
            @endphp
            <div class="col-4">
                <strong>Date Received:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">
                    {{ optional($dateReceived)->format('d/m/Y H:i') }}
                </div>
            </div>
            <div class="col-4">
                <strong>Date Reported:</strong>
                <div style="border-bottom: 1px solid #000; height: 15px; margin-top: 5px;">
                    {{ optional($dateReported)->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <div class="mb-3">
            <strong>Results:</strong>
            <textarea class="form-control" name="lab_results" rows="5"
                      style="margin-top: 5px; font-size: 10px;"
                      placeholder="Enter detailed laboratory findings..."></textarea>
        </div>

        <div class="row g-1">
            <div class="col-6">
                <strong>Technologist:</strong>
                <input type="text" class="form-control form-control-sm" name="technologist"
                       value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}"
                       style="margin-top: 3px;">
            </div>
            <div class="col-6">
                <strong>Pathologist/Supervisor:</strong>
                <input type="text" class="form-control form-control-sm" name="supervisor"
                       placeholder="Supervisor name and signature" style="margin-top: 3px;">
            </div>
        </div>
    </div>

</div>
