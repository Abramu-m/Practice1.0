{{-- TB Investigation Form - matches official NTLP paper form --}}
<style>
.tb-form, .tb-form * { box-sizing: border-box; }
.tb-form { font-family: Arial, sans-serif; font-size: 9px; max-width: 760px; margin: 0 auto; background: #fff; padding: 14px 18px; color: #000; line-height: 1.3; }
.tb-form table { border-collapse: collapse; width: 100%; }
.tb-form .grid td { border: none; padding: 1px 3px; vertical-align: middle; }
.tb-form .results-table td, .tb-form .results-table th { border: 1px solid #000; padding: 2px 3px; vertical-align: middle; font-size: 8.5px; }
.tb-form .results-table th { text-align: center; font-weight: bold; }
.tb-form input[type="text"],
.tb-form input[type="date"],
.tb-form input[type="time"] {
    border: none; border-bottom: 1px solid #000;
    background: transparent; font-size: 9px; font-family: Arial, sans-serif;
    padding: 0 1px; height: 14px; outline: none;
}
.tb-form input[type="text"].cell-input { width: 100%; height: 13px; }
.tb-form input[type="date"].cell-input,
.tb-form input[type="time"].cell-input { width: 100%; height: 13px; }
.tb-form select { font-size: 8px; font-family: Arial, sans-serif; border: none; border-bottom: 1px solid #000; background: transparent; padding: 0; width: 100%; height: 14px; outline: none; -webkit-appearance: none; -moz-appearance: none; appearance: none; }
.tb-form input[type="radio"],
.tb-form input[type="checkbox"] { margin: 0 1px; transform: scale(0.85); vertical-align: middle; cursor: pointer; }
.tb-form label { cursor: pointer; }
.tb-form .pre-filled { font-weight: bold; font-style: italic; border-bottom: 1px solid #000; display: inline-block; min-width: 60px; color: #cc0000; line-height: 13px; }
.tb-form .sig-line { border-bottom: 1px solid #000; display: inline-block; width: 90px; height: 13px; }
.tb-form .section-italic { font-style: italic; font-weight: bold; font-size: 9px; margin: 5px 0 2px; }
.tb-form .footnote { font-size: 7.5px; font-style: italic; line-height: 1.4; }
.tb-form .bordered { border: 1px solid #000; padding: 3px 5px; }

@media print {
    .tb-form { padding: 8px 12px; }
    .tb-form input, .tb-form select { color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="tb-form" id="tb-investigation-form" data-printable="true">

    {{-- ===== HEADER ===== --}}
    @php
        $tzLogoPath = public_path('images/tzlogo.jpg');
        $tzLogoSrc  = file_exists($tzLogoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tzLogoPath))
            : null;
    @endphp
    <div style="text-align: center; margin-bottom: 5px;">
        @if($tzLogoSrc)
            <img src="{{ $tzLogoSrc }}" alt="Coat of Arms"
                 style="width: 40px; height: 40px; display: block; margin: 0 auto 2px;">
        @endif
        <div style="font-size: 9px;">The United Republic of Tanzania</div>
        <div style="font-size: 10px; font-weight: bold;">MINISTRY OF HEALTH</div>
        <div style="font-size: 9px;">National TB and Leprosy Programme</div>
    </div>
    <div style="text-align: center; font-weight: bold; font-size: 10px; text-decoration: underline; margin-bottom: 7px;">
        REQUEST AND REPORT FORM FOR BIOLOGICAL SPECIMEN FOR TB AND LEPROSY
    </div>

    {{-- ===== PATIENT INFO ROWS ===== --}}
    <table class="grid" style="margin-bottom: 3px;">
        {{-- Row 1: Facility | Date of request | Time --}}
        <tr>
            <td style="width:50%;">
                <strong>Name of health facility:</strong>
                <span class="pre-filled">{{ $facility->name ?? config('app.clinic_name', '') }}</span>
            </td>
            <td style="width:28%;">
                <strong>Date of request:</strong>
                <span class="pre-filled">{{ \Carbon\Carbon::parse($visit->created_at ?? now())->format('Y-m-d') }}</span>
            </td>
            <td style="width:22%;">
                <strong>Time:</strong>
                <span class="pre-filled">{{ \Carbon\Carbon::parse($visit->created_at ?? now())->format('H:i') }}</span>
            </td>
        </tr>
        {{-- Row 2: Patient name | Age + Sex | Date of collection --}}
        <tr>
            <td>
                <strong>Name of Patient:</strong>
                <span class="pre-filled" style="min-width:130px;">{{ strtoupper(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}</span>
            </td>
            <td>
                <strong>Age:</strong>
                <span class="pre-filled" style="min-width:50px;">{{ $visit->patientInfo->age ?? '' }}</span>
                &nbsp;<strong>Sex(M/F):</strong>
                <span class="pre-filled" style="min-width:40px;">{{ ucfirst($visit->patientInfo->gender ?? '') }}</span>
            </td>
            <td>
                <strong>Date of collection:</strong>
                <input type="date" name="date_collection" value="{{ now()->format('Y-m-d') }}" style="width:95px;">
            </td>
        </tr>
        {{-- Row 3: Physical address | Time of collection --}}
        <tr>
            <td colspan="2">
                <strong>Physical adress (ward, street, village, house number):</strong>
                <span class="pre-filled" style="min-width:100px;">{{ $visit->patientInfo->address ?? '' }}</span>
            </td>
            <td>
                <strong>Time:</strong>
                <input type="time" name="time_collection" value="{{ now()->format('H:i') }}" style="width:62px;">
            </td>
        </tr>
        {{-- Row 4: Contact telephone (full width) --}}
        <tr>
            <td colspan="3">
                <strong>Contact telephone/mobile no:</strong>
                <input type="text" name="contact_phone" value="{{ $visit->patientInfo->phone_number ?? '' }}" style="width:130px;">
            </td>
        </tr>
        {{-- Row 5: Area leader | TB District No | Laboratory Serial No --}}
        <tr>
            <td>
                <strong>Area leader/ neighbor:</strong>
                <input type="text" name="area_leader" style="width:120px;">
            </td>
            <td>
                <strong>TB District No:</strong>
                <input type="text" name="tb_district_no" style="width:80px;">
            </td>
            <td>
                <strong>Laboratory Serial No:</strong>
                <input type="text" name="lab_serial_no" style="width:70px;">
            </td>
        </tr>
    </table>

    {{-- ===== REASON FOR EXAMINATION ===== --}}
    <div style="margin-bottom: 3px;">
        <strong>Reason for examination</strong><br>
        <label><input type="radio" name="reason" value="diagnosis" checked>
            Diagnosis. If diagnosis:
        </label>&nbsp;
        <label><input type="radio" name="diagnosis_type" value="tb" checked> TB</label>&nbsp;
        <label><input type="radio" name="diagnosis_type" value="mdr"> MDR</label>&nbsp;
        <label><input type="radio" name="diagnosis_type" value="leprosy"> Leprosy</label>
        <br>
        Or &nbsp;<label><input type="radio" name="reason" value="followup">
            Follow-up.&nbsp; If follow up, month on treatment:
            <input type="text" name="followup_month" style="width:80px;">
        </label>
    </div>

    {{-- ===== HIV / SPECIMEN / TESTS GRID ===== --}}
    <table class="grid" style="margin-bottom: 3px;">
        <tr style="vertical-align: top;">
            <td style="width:22%;">
                <strong>HIV status</strong><br>
                <label><input type="radio" name="hiv_status" value="reactive"> Reactive</label><br>
                <label><input type="radio" name="hiv_status" value="non_reactive" checked> Non Reactive</label><br>
                <label><input type="radio" name="hiv_status" value="unknown"> Unknown</label>
            </td>
            <td style="width:18%;">
                <strong>Previously treated for TB</strong><br>
                <label><input type="radio" name="previous_tb" value="yes"> Yes</label><br>
                <label><input type="radio" name="previous_tb" value="no" checked> No</label>
            </td>
            <td style="width:35%;">
                <strong>Specimen type</strong><br>
                <label><input type="radio" name="specimen" value="sputum" checked> Sputum</label>&nbsp;
                <label><input type="radio" name="specimen" value="csf"> CSF</label>&nbsp;
                <label><input type="radio" name="specimen" value="skin"> Skin smear</label><br>
                <label><input type="radio" name="specimen" value="peritoneal"> Peritoneal fluid</label>&nbsp;
                <label><input type="radio" name="specimen" value="lymph"> Lymph node</label><br>
                <label><input type="radio" name="specimen" value="urine"> Urine</label>&nbsp;
                <label><input type="radio" name="specimen" value="other_spec"> Other</label>
            </td>
            <td style="width:25%;">
                <strong>Test(s) requested</strong><br>
                <label><input type="checkbox" name="test_requested[]" value="microscopy"> Microscopy</label><br>
                <label><input type="checkbox" name="test_requested[]" value="xpert" checked> Xpert MTB/RIF</label><br>
                <label><input type="checkbox" name="test_requested[]" value="tb_lf_lam"> TB LF-LAM</label><br>
                <label><input type="checkbox" name="test_requested[]" value="other_test"> Other</label>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="padding-top:3px;">
                <strong>Name and signature of person requesting examination:</strong>
                <span class="pre-filled" style="min-width:200px;">
                    {{ optional(optional($visit->doctorInfo)->user)->name ?? '' }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ===== CONTACTS FOR RESULTS FEEDBACK ===== --}}
    <div class="bordered" style="margin-bottom: 6px;">
        <strong>Contacts for results feedback (if RR for Xpert MTB/RIF) DTLC / RTLC</strong>
        <table class="grid" style="margin-top:2px;">
            <tr>
                <td style="width:50%;"><strong>RTLC Name:</strong> <input type="text" name="rtlc_name" value="Dr. Iddi K. Njarita" style="width:150px;"></td>
                <td><strong>Email contact:</strong> <input type="text" name="rtlc_email" value="0627826480" style="width:130px;"></td>
            </tr>
            <tr>
                <td><strong>DTLC Name:</strong> <input type="text" name="dtlc_name" value="Dr. Richard Juma" style="width:150px;"></td>
                <td><strong>Email contact:</strong> <input type="text" name="dtlc_email" style="width:130px;"></td>
            </tr>
        </table>
    </div>

    {{-- ===== RESULTS SECTION ===== --}}
    <div class="section-italic">Results (to be completed in the laboratory)</div>

    {{-- Lab serial / reception header --}}
    <table class="grid" style="margin-bottom:3px;">
        <tr>
            <td style="width:36%;">
                <strong>Laboratory Serial No:</strong>
                <input type="text" name="lab_serial_results" style="width:90px;">
            </td>
            <td style="width:32%;">
                <strong>Date of reception:</strong>
                <input type="date" name="date_reception" value="{{ now()->format('Y-m-d') }}" style="width:97px;">
            </td>
            <td style="width:18%;">
                <strong>Time:</strong>
                <input type="time" name="time_reception" value="{{ now()->format('H:i') }}" style="width:60px;">
            </td>
            <td style="width:14%;">
                <label><input type="radio" name="zn_fm" value="zn"> ZN</label>&nbsp;
                <label><input type="radio" name="zn_fm" value="fm"> FM</label>
            </td>
        </tr>
    </table>

    {{-- ===== MICROSCOPY + XPERT + LF-LAM TABLE ===== --}}
    <table class="results-table" style="margin-bottom:2px;">
        <thead>
            <tr>
                <th rowspan="2" style="width:11%;">Date</th>
                <th rowspan="2" style="width:10%;">Specimen</th>
                <th rowspan="2" style="width:13%;">Received by</th>
                <th rowspan="2" style="width:14%;">Appearance*</th>
                <th colspan="5">Result ( Tick one)</th>
            </tr>
            <tr>
                <th style="width:9%;">neg</th>
                <th style="width:9%;">Scanty</th>
                <th style="width:9%;">1+</th>
                <th style="width:9%;">2+</th>
                <th style="width:9%;">3+</th>
            </tr>
        </thead>
        <tbody>
            {{-- Microscopy rows A, B, C --}}
            @foreach(['A','B','C'] as $r)
            <tr>
                <td style="text-align:center; font-size:8px;">
                    <strong>{{ $r }}</strong><br>
                    <input type="date" name="micro_date_{{ $r }}" class="cell-input" style="font-size:7.5px;">
                </td>
                <td>
                    <select name="micro_specimen_{{ $r }}">
                        <option value=""></option>
                        <option>Sputum</option><option>CSF</option><option>Urine</option>
                        <option>Pleural fluid</option><option>Peritoneal fluid</option><option>Tissue</option>
                    </select>
                </td>
                <td><input type="text" name="micro_received_{{ $r }}" class="cell-input"></td>
                <td>
                    <select name="micro_appearance_{{ $r }}">
                        <option value="">—</option>
                        <option>Salivary</option><option>Mucoid</option><option>Purulent</option>
                        <option>Blood-stained</option><option>Mucopurulent</option><option>Other</option>
                    </select>
                </td>
                <td style="text-align:center;"><input type="radio" name="micro_result_{{ $r }}" value="neg"></td>
                <td style="text-align:center;"><input type="radio" name="micro_result_{{ $r }}" value="scanty"></td>
                <td style="text-align:center;"><input type="radio" name="micro_result_{{ $r }}" value="1+"></td>
                <td style="text-align:center;"><input type="radio" name="micro_result_{{ $r }}" value="2+"></td>
                <td style="text-align:center;"><input type="radio" name="micro_result_{{ $r }}" value="3+"></td>
            </tr>
            @endforeach

            {{-- Xpert MTB/RIF row --}}
            <tr>
                <td style="font-size:7.5px;">
                    <input type="date" name="xpert_date" value="{{ now()->format('Y-m-d') }}" class="cell-input" style="font-size:7.5px;">
                </td>
                <td style="font-weight:bold; font-size:8px;">Xpert MTB/RIF</td>
                <td><input type="text" name="xpert_received_by" class="cell-input" value="{{ auth()->user()->name ?? '' }}"></td>
                <td>
                    <select name="xpert_appearance">
                        <option value="">—</option>
                        <option>Salivary</option><option>Mucoid</option><option selected>Purulent</option>
                        <option>Blood-stained</option><option>Mucopurulent</option><option>Other</option>
                    </select>
                </td>
                <td colspan="5" style="padding:2px;">
                    <table style="width:100%; border:none;"><tr>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="xpert_result" value="negative"> Negative</label></td>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="xpert_result" value="positive"> Positive</label></td>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="xpert_result" value="indeterminate"> Indeterminate</label></td>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="xpert_result" value="invalid"> Invalid</label></td>
                    </tr></table>
                </td>
            </tr>
            {{-- Xpert code legend sub-row --}}
            <tr style="background:#f9f9f9;">
                <td colspan="4" style="font-size:7.5px; font-style:italic; border-right:none;"></td>
                <td style="text-align:center; font-size:7.5px; font-style:italic;">3*</td>
                <td style="text-align:center; font-size:7.5px; font-style:italic;">T*</td>
                <td style="text-align:center; font-size:7.5px; font-style:italic;">TI*</td>
                <td style="text-align:center; font-size:7.5px; font-style:italic;">RR*</td>
                <td style="text-align:center; font-size:7.5px; font-style:italic;">I*</td>
            </tr>

            {{-- TB LF-LAM row --}}
            <tr>
                <td style="font-size:7.5px;">
                    <input type="date" name="lflam_date" class="cell-input" style="font-size:7.5px;">
                </td>
                <td style="font-weight:bold; font-size:8px;">TB LF-LAM</td>
                <td><input type="text" name="lflam_received_by" class="cell-input"></td>
                <td></td>
                <td colspan="5" style="padding:2px;">
                    <table style="width:100%; border:none;"><tr>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="lflam_result" value="negative"> Negative</label></td>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="lflam_result" value="positive"> Positive</label></td>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="lflam_result" value="indeterminate"> Indeterminate</label></td>
                        <td style="border:none; text-align:center;"><label><input type="radio" name="lflam_result" value="invalid"> Invalid</label></td>
                    </tr></table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Footnote --}}
    <div class="footnote" style="margin-bottom:7px;">
        *Visual appearance of sputum (blood stained, purulent, mucous, mucopurulent, salivary)<br>
        *N = MTB not detected, T = MTB detected, Rifampicin resistance not detected, RR = MTB detected, Rifampicin resistance detected<br>
        :TI = MTB detected, Rifampicin resistance indeterminate, I = Error / No result / Invalid
    </div>

    {{-- ===== SKIN SMEAR RESULTS TABLE ===== --}}
    <div class="section-italic">Skin smear result (to be completed in laboratory)</div>

    <table class="results-table" style="margin-bottom:5px;">
        <thead>
            <tr>
                <th rowspan="2" style="width:11%;">Date</th>
                <th rowspan="2" style="width:20%;">Specimen</th>
                <th rowspan="2" style="width:18%;">Received by</th>
                <th colspan="5">Result ( Tick one)</th>
            </tr>
            <tr>
                <th style="width:10%;">neg</th>
                <th style="width:10%;">Scanty</th>
                <th style="width:10%;">1+</th>
                <th style="width:10%;">2+</th>
                <th style="width:10%;">3+</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['Left Earlobe' => 'left_earlobe', 'Right Earlobe' => 'right_earlobe', 'Lesion 1' => 'lesion_1', 'Lesion 2' => 'lesion_2'] as $label => $key)
            <tr>
                <td><input type="date" name="skin_date_{{ $key }}" class="cell-input" style="font-size:7.5px;"></td>
                <td style="font-size:8px;">{{ $label }}</td>
                <td><input type="text" name="skin_received_{{ $key }}" class="cell-input"></td>
                <td style="text-align:center;"><input type="radio" name="skin_result_{{ $key }}" value="neg"></td>
                <td style="text-align:center;"><input type="radio" name="skin_result_{{ $key }}" value="scanty"></td>
                <td style="text-align:center;"><input type="radio" name="skin_result_{{ $key }}" value="1+"></td>
                <td style="text-align:center;"><input type="radio" name="skin_result_{{ $key }}" value="2+"></td>
                <td style="text-align:center;"><input type="radio" name="skin_result_{{ $key }}" value="3+"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ===== EXAMINER / REVIEWER SIGNATURE ROWS ===== --}}
    <table class="grid" style="margin-bottom:6px;">
        <tr>
            <td style="width:24%;">
                <strong>Date:</strong>
                <input type="date" name="examined_date" value="{{ now()->format('Y-m-d') }}" style="width:95px;">
            </td>
            <td style="width:20%;">
                <strong>Time:</strong>
                <input type="time" name="examined_time" value="{{ now()->format('H:i') }}" style="width:60px;">
            </td>
            <td style="width:32%;">
                <strong>Examined by:</strong>
                <input type="text" name="examined_by" value="{{ auth()->user()->name ?? '' }}" style="width:110px;">
            </td>
            <td>
                <strong>Signature</strong> <span class="sig-line"></span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Date:</strong>
                <input type="date" name="reviewed_date" value="{{ now()->format('Y-m-d') }}" style="width:95px;">
            </td>
            <td>
                <strong>Time:</strong>
                <input type="time" name="reviewed_time" value="{{ now()->format('H:i') }}" style="width:60px;">
            </td>
            <td>
                <strong>Reviewed by:</strong>
                <input type="text" name="reviewed_by" placeholder="Supervisor/Pathologist" style="width:110px;">
            </td>
            <td>
                <strong>Signature</strong> <span class="sig-line"></span>
            </td>
        </tr>
    </table>

    {{-- ===== COMMENTS / VERIFICATION ===== --}}
    <div style="border-top: 1px solid #000; padding-top:5px;">
        <strong>COMMENTS:</strong>
        <input type="text" name="comments" style="width: calc(100% - 90px);">

        <table class="grid" style="margin-top:4px;">
            <tr>
                <td style="width:38%;">
                    <strong>Result report verified by:</strong>
                    <input type="text" name="verified_by" style="width:110px;">
                </td>
                <td style="width:22%;">
                    <strong>Date:</strong>
                    <input type="date" name="verified_date" style="width:95px;">
                </td>
                <td style="width:18%;">
                    <strong>Time:</strong>
                    <input type="time" name="verified_time" style="width:60px;">
                </td>
                <td>
                    <strong>Signature</strong> <span class="sig-line"></span>
                </td>
            </tr>
        </table>
    </div>

</div>
