{{-- Indication Form — simple clinical justification for Procedures & Specialized Investigations --}}
<style>
.indication-form, .indication-form * { box-sizing: border-box; }
.indication-form {
    font-family: Arial, sans-serif; font-size: 13px;
    max-width: 680px; margin: 0 auto;
    background: #fff; padding: 16px 20px; color: #000; line-height: 1.4;
}
.indication-form table { border-collapse: collapse; width: 100%; }
.indication-form .grid td { border: none; padding: 2px 4px; vertical-align: middle; }
.indication-form .pre-filled {
    font-weight: bold; font-style: italic; color: #000;
    border-bottom: 1px solid #000; display: inline-block; min-width: 60px;
}
.indication-form textarea {
    width: 100%; font-size: 13px; font-family: Arial, sans-serif;
    border: 1px solid #000; padding: 6px; margin-top: 4px; resize: vertical;
    outline: none;
}
.indication-form .section-label { font-weight: bold; margin: 8px 0 2px; }

@media print {
    .indication-form { padding: 6px 10px; }
    .indication-form textarea { border: 1px solid #000; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="indication-form" id="indication-form" data-printable="true">

    <div style="text-align: center; margin-bottom: 8px;">
        <div style="font-size: 13px;">{{ config('app.clinic_name', 'Medical Facility') }}</div>
        <div style="font-size: 14px; font-weight: bold; text-decoration: underline; margin-top: 3px;">
            CLINICAL INDICATION
        </div>
    </div>

    <table class="grid" style="margin-bottom: 6px;">
        <tr>
            <td style="width: 50%;">
                <strong>Patient:</strong>
                <span class="pre-filled" style="min-width: 160px;">{{ strtoupper(($visit->patientInfo->first_name ?? '') . ' ' . ($visit->patientInfo->last_name ?? '')) }}</span>
            </td>
            <td style="width: 25%;">
                <strong>Age:</strong>
                <span class="pre-filled">{{ $visit->patientInfo->age ?? '' }}</span>
            </td>
            <td style="width: 25%;">
                <strong>Sex:</strong>
                <span class="pre-filled">{{ ucfirst(substr($visit->patientInfo->gender ?? '', 0, 1)) }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Date:</strong>
                <span class="pre-filled">{{ \Carbon\Carbon::parse($visit->date ?? now())->format('d/m/Y') }}</span>
            </td>
            <td colspan="2">
                <strong>Ordered by:</strong>
                <span class="pre-filled" style="min-width: 160px;">
                    {{ auth()->user()->name ?? optional(optional($visit->doctorInfo)->user)->name ?? '' }}
                </span>
            </td>
        </tr>
    </table>

    <div class="section-label">Indication / Clinical reason for this request:</div>
    <textarea name="indication" rows="4" required placeholder="Describe the clinical indication for this procedure/investigation..."></textarea>

</div>
