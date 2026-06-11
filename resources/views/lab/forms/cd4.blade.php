{{-- CD4 Request Form — compact official style --}}
<style>
.cd4-form, .cd4-form * { box-sizing: border-box; }
.cd4-form {
    font-family: Arial, sans-serif; font-size: 9px;
    max-width: 680px; margin: 0 auto;
    background: #fff; padding: 12px 16px; color: #000; line-height: 1.3;
}
.cd4-form table { border-collapse: collapse; width: 100%; }
.cd4-form .grid td { border: none; padding: 1px 3px; vertical-align: middle; }
.cd4-form .data-table td, .cd4-form .data-table th {
    border: 1px solid #000; padding: 2px 4px; vertical-align: middle; font-size: 8.5px;
}
.cd4-form .data-table th { text-align: center; font-weight: bold; background: #f5f5f5; }
.cd4-form input[type="text"],
.cd4-form input[type="date"],
.cd4-form input[type="time"],
.cd4-form input[type="number"] {
    border: none; border-bottom: 1px solid #000;
    background: transparent; font-size: 9px; font-family: Arial, sans-serif;
    padding: 0 1px; height: 14px; outline: none; width: 100%;
}
.cd4-form select {
    font-size: 8px; font-family: Arial, sans-serif;
    border: none; border-bottom: 1px solid #000;
    background: transparent; padding: 0; height: 14px; outline: none; width: 100%;
    -webkit-appearance: none; appearance: none;
}
.cd4-form input[type="radio"],
.cd4-form input[type="checkbox"] { margin: 0 2px; transform: scale(0.85); vertical-align: middle; cursor: pointer; }
.cd4-form label { cursor: pointer; }
.cd4-form .pre-filled {
    font-weight: bold; font-style: italic; color: #000;
    border-bottom: 1px solid #000; display: inline-block; min-width: 60px; line-height: 13px;
}
.cd4-form .sig-line { border-bottom: 1px solid #000; display: inline-block; width: 100px; height: 13px; }
.cd4-form .section-label { font-style: italic; font-weight: bold; font-size: 9px; margin: 5px 0 2px; }
.cd4-form .bordered { border: 1px solid #000; padding: 3px 5px; }

/* ── Screen-only: larger, more readable ── */
@media screen {
    .cd4-form { font-size: 13px !important; padding: 20px 24px !important; }
    .cd4-form .grid td { padding: 3px 6px !important; }
    .cd4-form .data-table td, .cd4-form .data-table th { font-size: 12px !important; padding: 4px 6px !important; }
    .cd4-form input[type="text"], .cd4-form input[type="date"],
    .cd4-form input[type="time"], .cd4-form input[type="number"] { font-size: 12px !important; height: 20px !important; }
    .cd4-form select { font-size: 12px !important; height: 20px !important; }
    .cd4-form .pre-filled { font-size: 13px !important; line-height: 20px !important; }
    .cd4-form .section-label { font-size: 13px !important; }
    .cd4-form input[type="radio"], .cd4-form input[type="checkbox"] { transform: scale(1.1); }
}

@media print {
    .cd4-form { padding: 6px 10px; }
    .cd4-form input, .cd4-form select { color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="cd4-form" id="cd4-request-form" data-printable="true">

    {{-- ===== HEADER ===== --}}
    <div style="text-align: center; margin-bottom: 5px;">
        <div style="font-size: 9px;">{{ config('app.clinic_name', 'Medical Facility') }}</div>
        <div style="font-size: 11px; font-weight: bold; text-decoration: underline; margin-top: 3px;">
            CD4 REQUEST FORM
        </div>
    </div>

    {{-- ===== PATIENT INFO ===== --}}
    <table class="grid" style="margin-bottom: 3px;">
        <tr>
            <td style="width: 40%;">
                <strong>Date of request:</strong>
                <span class="pre-filled">{{ \Carbon\Carbon::parse($visit->date ?? now())->format('d/m/Y') }}</span>
            </td>
            <td style="width: 30%;">
                <strong>Time:</strong>
                <span class="pre-filled">{{ \Carbon\Carbon::parse($visit->created_at ?? now())->format('H:i') }}</span>
            </td>
            <td style="width: 30%;">
                <strong>CTC No:</strong>
                <input type="text" name="ctc_number" value="" style="width: 90px;">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Patient Name:</strong>
                <span class="pre-filled" style="min-width: 160px;">{{ strtoupper(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}</span>
            </td>
            <td>
                <strong>Sex:</strong>
                <span class="pre-filled" style="min-width: 40px;">{{ ucfirst(substr($visit->patientInfo->gender ?? '', 0, 1)) }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Age:</strong>
                <span class="pre-filled" style="min-width: 80px;">{{ $visit->patientInfo->age ?? '' }}</span>
            </td>
            <td colspan="2">
                <strong>Address:</strong>
                <span class="pre-filled" style="min-width: 140px;">{{ $visit->patientInfo->address ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 2px;">
                <strong>Ordered by:</strong>
                <span class="pre-filled" style="min-width: 200px;">
                    {{ optional(optional($visit->doctorInfo)->user)->name ?? 'Dr. ' . (optional($visit->doctorInfo)->first_name ?? '') . ' ' . (optional($visit->doctorInfo)->last_name ?? '') }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ===== INDICATION FOR CD4 ===== --}}
    <div class="bordered" style="margin-bottom: 5px;">
        <div style="font-weight: bold; margin-bottom: 3px;">Indication for CD4:</div>
        <table class="grid">
            <tr>
                <td style="width: 50%;">
                    <label><input type="radio" name="cd4_indication" value="reactive_bioline_unigold"> Reactive Bioline and Unigold tests</label>
                </td>
                <td>
                    <label><input type="radio" name="cd4_indication" value="art_6_months_routine"> ART 6 months routine test</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label><input type="radio" name="cd4_indication" value="unknown_but_needed"> Unknown but needed CD4 test</label>
                </td>
                <td>
                    <label><input type="radio" name="cd4_indication" value="bad_condition"> Bad condition of the patient</label>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label>
                        <input type="radio" name="cd4_indication" value="others" id="cd4_indication_others">
                        Others, specify:
                    </label>
                    <input type="text" name="cd4_indication_other" id="cd4_indication_other" style="width: 200px;" disabled>
                </td>
            </tr>
        </table>
    </div>

    <hr style="border: 1.5px solid #000; margin: 6px 0;">

    {{-- ===== RESULTS SECTION ===== --}}
    <div class="section-label">Results (to be completed in the laboratory)</div>

    <table class="grid" style="margin-bottom: 4px;">
        <tr>
            <td style="width: 33%;">
                <strong>Lab Serial No:</strong>
                <input type="text" name="lab_serial_no" style="width: 90px;">
            </td>
            <td style="width: 33%;">
                <strong>Date Received:</strong>
                <input type="date" name="date_received" style="width: 97px;">
            </td>
            <td style="width: 34%;">
                <strong>Date Analyzed:</strong>
                <input type="date" name="date_analyzed" style="width: 97px;">
            </td>
        </tr>
    </table>

    {{-- CD4 Measurements Table --}}
    <table class="data-table" style="margin-bottom: 4px;">
        <thead>
            <tr>
                <th style="width: 35%;">Parameter</th>
                <th style="width: 30%;">Result</th>
                <th style="width: 35%;">Normal Range</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CD4+ T-Cell Count</td>
                <td>
                    <input type="text" name="cd4_count" id="cd4_count" placeholder="—" style="width: 70px;">
                    &nbsp;cells/μL<br>
                    <label><input type="radio" name="cd4_advanced_result" value="below_200"> &lt; 200</label>
                    <label><input type="radio" name="cd4_advanced_result" value="above_200"> &ge; 200</label>
                </td>
                <td style="font-size: 8px; color: #555;">500–1200 cells/μL (adults)</td>
            </tr>
            <tr>
                <td>CD4 Percentage</td>
                <td>
                    <input type="number" name="cd4_percentage" min="0" max="100" step="0.1" placeholder="—" style="width: 55px;">
                    &nbsp;%
                </td>
                <td style="font-size: 8px; color: #555;">30–60%</td>
            </tr>
            <tr>
                <td>Total Lymphocyte Count</td>
                <td>
                    <input type="number" name="total_lymphocytes" min="0" placeholder="—" style="width: 70px;">
                    &nbsp;cells/μL
                </td>
                <td></td>
            </tr>
            <tr>
                <td>CD8+ T-Cell Count</td>
                <td>
                    <input type="number" name="cd8_count" min="0" placeholder="—" style="width: 70px;">
                    &nbsp;cells/μL
                </td>
                <td></td>
            </tr>
            <tr>
                <td>CD4/CD8 Ratio</td>
                <td>
                    <input type="number" name="cd4_cd8_ratio" min="0" step="0.01" placeholder="—" style="width: 55px;">
                </td>
                <td style="font-size: 8px; color: #555;">Normal: 1.0–2.5</td>
            </tr>
            <tr>
                <td>Test Method</td>
                <td colspan="2">
                    <select name="test_method">
                        <option value="">— Select —</option>
                        <option value="cd4_advanced_disease" selected>CD4 Advanced Disease Test</option>
                        <option value="flow_cytometry">Flow Cytometry</option>
                        <option value="facs_count">FACS Count</option>
                        <option value="cyflow">CyFlow</option>
                        <option value="other">Other</option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- HIV Category --}}
    <div class="bordered" style="margin-bottom: 4px;">
        <strong>HIV Immunological Category:</strong>
        <table class="grid" style="margin-top: 2px;">
            <tr>
                <td style="width: 50%;">
                    <label><input type="radio" name="hiv_category" value="normal"> Normal immunity (CD4 &gt; 500)</label><br>
                    <label><input type="radio" name="hiv_category" value="mild"> Mild suppression (350–500)</label><br>
                    <label><input type="radio" name="hiv_category" value="moderate"> Moderate suppression (200–349)</label>
                </td>
                <td>
                    <label><input type="radio" name="hiv_category" value="severe"> Severe suppression (&lt; 200)</label><br>
                    <label><input type="radio" name="hiv_category" value="aids"> AIDS-defining (&lt; 100)</label>
                </td>
            </tr>
        </table>
    </div>

    {{-- Comments --}}
    <div style="margin-bottom: 4px;">
        <strong>Clinical Significance / Comments:</strong><br>
        <textarea name="clinical_significance" rows="2"
                  style="width:100%; font-size:9px; border:1px solid #000; font-family:Arial,sans-serif; padding:2px; margin-top:2px;"></textarea>
    </div>

    {{-- Personnel --}}
    <table class="grid" style="margin-bottom: 3px;">
        <tr>
            <td style="width: 50%;">
                <strong>Examined by:</strong>
                <input type="text" name="technician" value="{{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? '' }}" style="width: 140px;">
            </td>
            <td>
                <strong>Reviewed by:</strong>
                <input type="text" name="reviewed_by" style="width: 130px;">
            </td>
        </tr>
    </table>

    {{-- Signature row --}}
    <table class="grid">
        <tr>
            <td style="width: 50%;">
                <strong>Date:</strong>
                <input type="date" name="result_date" value="{{ now()->format('Y-m-d') }}" style="width: 97px;">&nbsp;
                <strong>Time:</strong>
                <input type="time" name="result_time" value="{{ now()->format('H:i') }}" style="width: 60px;">
            </td>
            <td>
                <strong>Signature:</strong> <span class="sig-line"></span>
            </td>
        </tr>
    </table>

</div>

<script>
(function () {
    var othersRadio = document.getElementById('cd4_indication_others');
    var otherInput  = document.getElementById('cd4_indication_other');
    if (!othersRadio || !otherInput) return;

    function sync() {
        otherInput.disabled = !othersRadio.checked;
        if (!othersRadio.checked) otherInput.value = '';
    }

    document.querySelectorAll('input[name="cd4_indication"]').forEach(function (r) {
        r.addEventListener('change', sync);
    });
    sync();
})();

(function () {
    var cd4CountInput = document.getElementById('cd4_count');
    if (!cd4CountInput) return;

    var values = { below_200: '<200', above_200: '≥200' };

    document.querySelectorAll('input[name="cd4_advanced_result"]').forEach(function (r) {
        r.addEventListener('change', function () {
            cd4CountInput.value = values[r.value] || '';
        });
    });
})();
</script>
