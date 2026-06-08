{{-- PDF view for prescriptions list --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
    <tr>
        <td style="text-align:center; padding-bottom:10px; border-bottom:2px solid #dee2e6;">
            <div style="font-size:1.1rem; font-weight:700;">{{ $facility->name ?? 'Medical Facility' }}</div>
            @if($facility->address ?? null)
                <div style="font-size:0.85rem; color:#6c757d;">{{ $facility->address }}</div>
            @endif
            @if(($facility->phone ?? null) || ($facility->email ?? null))
                <div style="font-size:0.85rem; color:#6c757d;">
                    @if($facility->phone ?? null) Phone: {{ $facility->phone }} @endif
                    @if(($facility->phone ?? null) && ($facility->email ?? null)) &nbsp;|&nbsp; @endif
                    @if($facility->email ?? null) Email: {{ $facility->email }} @endif
                </div>
            @endif
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-bottom:14px;">
    <tr>
        <td style="font-size:0.95rem; font-weight:700; padding-bottom:6px; border-bottom:1px solid #dee2e6;">
            Prescriptions
        </td>
    </tr>
    <tr>
        <td style="padding-top:6px; font-size:0.875rem;">
            <strong>Patient:</strong> {{ optional($consultation->visit->patientInfo)->first_name }} {{ optional($consultation->visit->patientInfo)->last_name }}
            &nbsp;&nbsp;
            <strong>Visit Date:</strong> {{ optional($consultation->visit->visit_date)->format('d M Y') }}
        </td>
    </tr>
</table>

<div class="mt-3">
    @includeIf('consultations.partials.prescriptions', ['prescriptions' => $prescriptions])
</div>
