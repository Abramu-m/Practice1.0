<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Referral Letter and Case Summary</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 24px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin-bottom: 4px; font-size: 22px; }
        .header p { margin: 0; color: #555; font-size: 12px; }
        .section-title { margin: 18px 0 8px; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; color: #222; }
        .address-block { margin-bottom: 12px; font-size: 12px; }
        .letter-body { white-space: pre-wrap; line-height: 1.55; font-size: 12px; margin-bottom: 18px; }
        .letter-body strong { font-weight: 600; }
        .letter-block { border: 1px solid #ddd; padding: 16px; border-radius: 8px; background: #f8f9fa; margin-bottom: 24px; }
        .letter-footer { margin-top: 18px; white-space: pre-wrap; font-size: 12px; }
        .small { font-size: 11px; color: #555; }
        .badge { display: inline-block; padding: 0.35em 0.65em; font-size: 75%; font-weight: 700; line-height: 1; color: #fff; text-align: center; border-radius: 0.25rem; }
        .bg-secondary { background-color: #6c757d; }
        .card { border: 1px solid #ddd; border-radius: 0.5rem; margin-bottom: 18px; background-color: #fff; }
        .card-header { padding: 10px 14px; border-bottom: 1px solid #ddd; background-color: #f7f7f7; font-weight: 700; }
        .card-body { padding: 14px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12px; }
        .table th, .table td { padding: 6px 8px; border: 1px solid #ddd; vertical-align: top; }
        .table th { background-color: #f2f2f2; font-weight: 700; }
        ul { margin: 0; padding-left: 20px; }
        ul li { margin-bottom: 4px; }
        .summary-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
        .summary-item { min-width: 200px; flex: 1 1 200px; }
        .summary-item strong { display: block; font-size: 12px; color: #333; margin-bottom: 2px; }
        .summary-content { margin-bottom: 16px; font-size: 12px; line-height: 1.45; color: #222; }
        .text-muted { color: #6c757d; }
        .fw-medium { font-weight: 500; }
        .fw-semibold { font-weight: 600; }
        .border { border: 1px solid #ddd; }
        .rounded { border-radius: 0.375rem; }
        .p-3 { padding: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-3 { margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Referral Letter</h1>
        <p>{{ config('app.clinic_name', 'Medical Facility') }} | {{ config('app.clinic_address', 'Clinic Address') }} | {{ config('app.clinic_phone', 'Phone Number') }}</p>
    </div>

    <div class="letter-block">
        <div class="address-block">
            <strong>To:</strong><br>
            {{ $hospital->name }}<br>
            {{ $hospital->address ?? 'Address not available' }}<br>
            @if($hospital->phone) Phone: {{ $hospital->phone }}<br>@endif
            @if($hospital->email) Email: {{ $hospital->email }}<br>@endif
            <br>
            <strong>Department:</strong> {{ $department->name }}
        </div>

        <div class="section-title">{{ $referral->letter_heading }}</div>
        <div class="letter-body">{{ trim($referral->letter_template) }}</div>

        @if($referral->additional_notes)
            <div class="letter-body">{{ trim($referral->additional_notes) }}</div>
        @endif

        <div class="letter-footer">{{ trim($referral->letter_closing) }}</div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 style="font-size:14px; margin:0;">Case Summary</h2>
        </div>
        <div class="card-body p-0">
            @include('consultations.partials.case_summary_content')
        </div>
    </div>
</body>
</html>
