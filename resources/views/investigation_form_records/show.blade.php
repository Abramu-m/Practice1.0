@extends('layouts.app_main_layout')

@section('page_title', 'Investigation Form — View & Print')

@section('styles')
<style>
    @media print {
        /* Hide AdminLTE chrome */
        .app-header,
        .app-sidebar,
        .app-footer,
        .no-print { display: none !important; }

        /* Reset layout wrappers so the form fills the page */
        .app-wrapper,
        .app-main,
        .app-content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #fff !important;
        }

        @page { margin: 10mm 12mm; }
    }

    #printable-form { background: #fff; }

    .print-toolbar {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
</style>
@endsection

@section('main_content')
<div id="printable-form">

    {{-- Toolbar (hidden on print) --}}
    <div class="print-toolbar no-print">
        <a href="{{ route('investigation-form-records.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-success">
            <i class="bi bi-printer"></i> Print
        </button>

        <span class="text-muted ms-1" style="font-size:13px;">
            {{ $record->investigation->patient->full_name ?? '' }}
            &nbsp;·&nbsp;
            {{ $record->investigation->medicalService->name ?? '' }}
            &nbsp;·&nbsp;
            {{ $record->investigation->ordered_at?->format('d M Y H:i') ?? $record->created_at->format('d M Y H:i') }}
        </span>

        {{-- Result status badge --}}
        @if($resultStatus)
            @php
                $badgeClass = match($resultStatus) {
                    'final'       => 'bg-success',
                    'preliminary' => 'bg-warning text-dark',
                    'draft'       => 'bg-secondary',
                    default       => 'bg-secondary',
                };
            @endphp
            <span class="badge {{ $badgeClass }} ms-1" title="Results recorded{{ $resultReportedAt ? ' on ' . $resultReportedAt->format('d M Y H:i') : '' }}">
                Results: {{ ucfirst($resultStatus) }}
            </span>
        @else
            <span class="badge bg-light text-secondary border ms-1">No results yet</span>
        @endif
    </div>

    {{-- The form itself --}}
    <div style="padding: 16px;">
        @if($formType && view()->exists('lab.result_templates.' . $formType))
            @include('lab.result_templates.' . $formType, ['visit' => $visit])
        @else
            <div class="alert alert-warning">
                Form template <strong>{{ $formType ?? '(none)' }}</strong> not found.
            </div>
        @endif
    </div>

</div>

{{-- Inject saved form data: request fields first, then result fields (result takes priority on overlap) --}}
<script>
(function () {
    const requestData = @json($record->form_data ?? []);
    const resultData  = @json($resultData ?? []);

    // Merge: result data overwrites request data for any overlapping field names
    const combined = Object.assign({}, requestData, resultData);

    if (!combined || typeof combined !== 'object' || !Object.keys(combined).length) return;

    function fillFormData(data) {
        Object.entries(data).forEach(function ([name, value]) {
            // Checkboxes (array values)
            if (Array.isArray(value)) {
                value.forEach(function (v) {
                    const el = document.querySelector(
                        'input[type="checkbox"][name="' + CSS.escape(name) + '[]"][value="' + CSS.escape(v) + '"],' +
                        'input[type="checkbox"][name="' + CSS.escape(name) + '"][value="' + CSS.escape(v) + '"]'
                    );
                    if (el) el.checked = true;
                });
                return;
            }

            // Radio buttons
            const radio = document.querySelector(
                'input[type="radio"][name="' + CSS.escape(name) + '"][value="' + CSS.escape(String(value)) + '"]'
            );
            if (radio) { radio.checked = true; return; }

            // Select
            const select = document.querySelector('select[name="' + CSS.escape(name) + '"]');
            if (select) { select.value = value; return; }

            // Textarea
            const textarea = document.querySelector('textarea[name="' + CSS.escape(name) + '"]');
            if (textarea) { textarea.value = value; return; }

            // Text / date / time / number — skip readonly pre-filled patient fields
            const input = document.querySelector(
                'input:not([type="radio"]):not([type="checkbox"])[name="' + CSS.escape(name) + '"]'
            );
            if (input && !input.readOnly) { input.value = value; }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { fillFormData(combined); });
    } else {
        fillFormData(combined);
    }
})();
</script>
@endsection
