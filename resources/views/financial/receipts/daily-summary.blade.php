@extends('layouts.app_main_layout')

@section('page_title', 'Daily Summary — ' . $summary_date->format('d M Y'))

@section('styles')
<style>
    .report-card { border: 1px solid #dee2e6; border-radius: 4px; }
    .report-header { text-align: center; padding: 12px 0 8px; border-bottom: 2px solid #333; margin-bottom: 12px; }
    .report-header .facility-name { font-size: 18px; font-weight: 700; }
    .report-title { text-align: center; font-size: 16px; font-weight: 700; color: #1a6196; letter-spacing: 1px; margin: 10px 0 4px; }
    .report-meta { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 8px; }
    .daily-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .daily-table th, .daily-table td { border: 1px solid #aaa; padding: 5px 8px; }
    .daily-table th { background: #e9ecef; font-weight: 700; }
    .daily-table .item-cell { font-weight: 700; color: #c0392b; vertical-align: middle; }
    .daily-table .total-row td { font-weight: 700; background: #f5f5f5; }
    .daily-table .amount { text-align: right; }
    .daily-table .count  { text-align: center; }
    .section-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .section-table th, .section-table td { border: 1px solid #ccc; padding: 4px 8px; }
    .section-table th { background: #f0f0f0; font-weight: 700; }
    .section-table .amount { text-align: right; }
    .section-title { font-size: 14px; font-weight: 700; color: #1a6196; margin: 0 0 6px; }
    .balance-line { text-align: right; font-size: 15px; font-weight: 700; color: #1a6196; margin-top: 10px; }
    @media print {
        .app-header,
        .app-sidebar,
        .app-footer,
        .no-print { display: none !important; }
        .app-wrapper, .app-main, .app-content, .container-fluid {
            margin: 0 !important; padding: 0 !important;
            width: 100% !important; background: #fff !important;
        }
        @page { margin: 10mm 12mm; }
        .report-card { border: none; }
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">

    {{-- Filter toolbar --}}
    <div class="card mb-3 no-print">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('financial.receipts.daily.summary') }}" class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-semibold">Date:</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}"
                           onchange="this.form.submit()" style="width:160px">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-semibold">Collector:</label>
                    <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width:200px">
                        <option value="">All Collectors</option>
                        @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}" @selected($userId == $collector->id)>
                                {{ $collector->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Show</button>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </form>
        </div>
    </div>

    {{-- Report body --}}
    <div class="report-card p-3">

        {{-- Header --}}
        <div class="report-header">
            <div class="facility-name">{{ $facility->name ?? config('app.clinic_name', 'Medical Facility') }}</div>
            <div style="font-size:12px; color:#555;">
                {{ $facility->address ?? config('app.clinic_address', '') }}
                @if($facility->phone ?? config('app.clinic_phone', ''))
                    &nbsp;|&nbsp; Tel: {{ $facility->phone ?? config('app.clinic_phone', '') }}
                @endif
            </div>
        </div>

        <div class="report-title">DAILY REPORT</div>

        <div class="report-meta">
            <span>DATE: <strong>{{ $summary_date->format('Y-m-d') }}</strong></span>
            @if($userId && $collectors->firstWhere('id', $userId))
                <span>Collected By: <strong>{{ $collectors->firstWhere('id', $userId)->name }}</strong></span>
            @endif
        </div>

        {{-- Main revenue table --}}
        @php
            $hasConsultation  = $consultationGroups->isNotEmpty();
            $hasInvestigation = $investigationGroups->isNotEmpty();
            $hasPharmacy      = $pharmacyGroups->isNotEmpty();

            // Grand total (cash collected from patients)
            $grandCash    = 0;
            $grandCovered = 0;
        @endphp

        <table class="daily-table mb-3">
            <thead>
                <tr>
                    <th style="width:22%">Item</th>
                    <th>Category</th>
                    <th class="count" style="width:8%">Total</th>
                    <th class="amount" style="width:16%">Covered (Insured)</th>
                    <th class="amount" style="width:16%">Amount (Cash)</th>
                </tr>
            </thead>
            <tbody>

                {{-- Consultation --}}
                @if($hasConsultation)
                    @foreach($consultationGroups as $scheme => $rows)
                        @php
                            $cash    = $rows->sum('patient_paid_amount');
                            $covered = $rows->sum('insurance_covered_amount');
                            $grandCash    += $cash;
                            $grandCovered += $covered;
                        @endphp
                        <tr>
                            @if($loop->first)
                                <td class="item-cell" rowspan="{{ $consultationGroups->count() }}">Consultation</td>
                            @endif
                            <td>{{ $scheme }}</td>
                            <td class="count">{{ $rows->count() }}</td>
                            <td class="amount">{{ $covered > 0 ? number_format($covered) : '0' }}</td>
                            <td class="amount">{{ number_format($cash) }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- Investigations --}}
                @if($hasInvestigation)
                    @foreach($investigationGroups as $subcat => $rows)
                        @php
                            $cash    = $rows->sum('patient_paid_amount');
                            $covered = $rows->sum('insurance_covered_amount');
                            $grandCash    += $cash;
                            $grandCovered += $covered;
                        @endphp
                        <tr>
                            @if($loop->first)
                                <td class="item-cell" rowspan="{{ $investigationGroups->count() }}">Investigations</td>
                            @endif
                            <td>{{ ucfirst(str_replace('_', ' ', $subcat ?: 'Other')) }}</td>
                            <td class="count">{{ $rows->count() }}</td>
                            <td class="amount">{{ $covered > 0 ? number_format($covered) : '0' }}</td>
                            <td class="amount">{{ number_format($cash) }}</td>
                        </tr>
                    @endforeach
                @endif

                {{-- Pharmacy --}}
                @if($hasPharmacy)
                    @foreach($pharmacyGroups as $type => $rows)
                        @php
                            $cash    = $rows->sum('patient_paid_amount');
                            $covered = $rows->sum('insurance_covered_amount');
                            $grandCash    += $cash;
                            $grandCovered += $covered;
                        @endphp
                        <tr>
                            @if($loop->first)
                                <td class="item-cell" rowspan="{{ $pharmacyGroups->count() }}">Pharmacy</td>
                            @endif
                            <td>{{ $type }}</td>
                            <td class="count">{{ $rows->count() }}</td>
                            <td class="amount">{{ $covered > 0 ? number_format($covered) : '0' }}</td>
                            <td class="amount">{{ number_format($cash) }}</td>
                        </tr>
                    @endforeach
                @endif

                @if(!$hasConsultation && !$hasInvestigation && !$hasPharmacy)
                    <tr><td colspan="5" class="text-center text-muted py-3">No income transactions for this date.</td></tr>
                @endif

                {{-- Grand Total --}}
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td class="count"></td>
                    <td class="amount">{{ number_format($grandCovered) }}</td>
                    <td class="amount">{{ number_format($grandCash) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Income & Expenditure side by side --}}
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <p class="section-title">Income</p>
                <table class="section-table">
                    <thead>
                        <tr>
                            <th style="width:8%">S/N</th>
                            <th>Description</th>
                            <th class="amount">Amount</th>
                            <th>Received by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($otherIncome as $i => $txn)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $txn->description }}</td>
                                <td class="amount">{{ number_format($txn->patient_paid_amount ?: $txn->amount) }}</td>
                                <td>{{ $txn->creator?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">—</td></tr>
                        @endforelse
                        <tr style="font-weight:700; background:#f5f5f5;">
                            <td colspan="2">Total</td>
                            <td class="amount">{{ number_format($otherIncome->sum('patient_paid_amount') ?: $otherIncome->sum('amount')) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6 mb-3">
                <p class="section-title">Expenditure</p>
                <table class="section-table">
                    <thead>
                        <tr>
                            <th style="width:8%">S/N</th>
                            <th>Description</th>
                            <th class="amount">Amount</th>
                            <th>Issued by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $i => $exp)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $exp->description }}</td>
                                <td class="amount">{{ number_format($exp->amount) }}</td>
                                <td>{{ $exp->creator?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">—</td></tr>
                        @endforelse
                        <tr style="font-weight:700; background:#f5f5f5;">
                            <td colspan="2">Total</td>
                            <td class="amount">{{ number_format($total_expenses) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Balance --}}
        <div class="balance-line">
            Balance: {{ number_format($grandCash - $total_expenses) }}
        </div>

        <div style="font-size:11px; color:#888; margin-top:8px; text-align:right;">
            Report generated: {{ now()->format('d M Y H:i') }}
            &nbsp;|&nbsp; By: {{ auth()->user()->name ?? 'System' }}
        </div>

    </div>{{-- /report-card --}}

</div>
@endsection
