@extends('layouts.app_main_layout')

@section('page_title', 'Configure — ALu Report')

@section('main_content')
<div class="container">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-0">ALu Report — Configuration</h4>
            <p class="text-muted mb-0 mt-1">Fomu ya Taarifa ya Mwezi ya Dawa za Matibabu ya Malaria</p>
        </div>
        <a href="{{ route('admin.reports.alu-monthly') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-eye me-1"></i> View Report
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>How this works:</strong> Select which medication in the catalogue corresponds to each ALu pack size.
        The report sums quantities dispensed for that medication and groups them by patient age band.
    </div>

    <form method="POST" action="{{ route('settings.reports.alu-monthly.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            @php
                $packs = [
                    'alu_1x6_medication_id' => 'ALu ya 1x6',
                    'alu_2x6_medication_id' => 'ALu ya 2x6',
                    'alu_3x6_medication_id' => 'ALu ya 3x6',
                    'alu_4x6_medication_id' => 'ALu ya 4x6',
                ];
            @endphp

            @foreach($packs as $key => $label)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-capsule text-success fs-5"></i>
                        <h6 class="card-title mb-0">{{ $label }}</h6>
                        @if($config[$key])
                            <span class="badge bg-success ms-auto">Configured</span>
                        @else
                            <span class="badge bg-secondary ms-auto">Not set</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Medication</label>
                        <select name="{{ $key }}" class="form-select">
                            <option value="">— Not set —</option>
                            @foreach($medications as $med)
                                <option value="{{ $med->id }}" {{ $config[$key] == $med->id ? 'selected' : '' }}>
                                    {{ $med->brand_name ? "{$med->generic_name} ({$med->brand_name})" : $med->generic_name }}
                                    @if($med->strength) — {{ $med->strength }} @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Which medication in the catalogue is {{ $label }}?</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary ms-2">
                Back to Settings
            </a>
        </div>

    </form>
</div>
@endsection
