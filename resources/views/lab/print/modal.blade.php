{{-- PDF view for all visit investigation results --}}

{{-- Facility header --}}
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

{{-- Patient info --}}
<div style="font-size:0.9rem; font-weight:700; border-bottom:1px solid #dee2e6; padding-bottom:4px; margin-bottom:8px;">Patient Information</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:0.875rem;">
    <tr>
        <td style="padding:5px 10px; width:40%; border:1px solid #dee2e6; background:#f8f9fa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Name</span>
            <div style="font-weight:600;">{{ trim($visit->patientInfo->first_name . ' ' . ($visit->patientInfo->middle_name ?? '') . ' ' . $visit->patientInfo->last_name) }}</div>
        </td>
        <td style="padding:5px 10px; width:20%; border:1px solid #dee2e6; background:#f8f9fa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">DOB</span>
            <div style="font-weight:600;">{{ $visit->patientInfo->date_of_birth ? \Carbon\Carbon::parse($visit->patientInfo->date_of_birth)->format('d M Y') : 'N/A' }}</div>
        </td>
        <td style="padding:5px 10px; width:20%; border:1px solid #dee2e6; background:#f8f9fa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Gender</span>
            <div style="font-weight:600;">{{ ucfirst($visit->patientInfo->gender ?? 'N/A') }}</div>
        </td>
        <td style="padding:5px 10px; width:20%; border:1px solid #dee2e6; background:#f8f9fa;">
            <span style="font-size:0.7rem; color:#6c757d; text-transform:uppercase;">Contact</span>
            <div style="font-weight:600;">{{ $visit->patientInfo->contact ?? 'N/A' }}</div>
        </td>
    </tr>
</table>

@foreach($results as $result)
    @include('lab.print.partials.result_card', ['result' => $result])
@endforeach
