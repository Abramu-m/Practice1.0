@extends('layouts.app_main_layout')

@section('page_title', 'Configure — Malaria Vipimo Report')

@section('main_content')
<div class="container">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-0">Malaria Vipimo — Report Configuration</h4>
            <p class="text-muted mb-0 mt-1">Fomu ya Taarifa ya Vipimo vya Malaria</p>
        </div>
        <a href="{{ route('admin.reports.malaria-vipimo') }}" class="btn btn-outline-secondary btn-sm">
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
        <strong>How this works:</strong> Select which lab investigation corresponds to each test slot.
        The report will only count results entered using the <strong>required template</strong> shown on each panel —
        make sure lab staff select that template when recording results.
    </div>

    <form method="POST" action="{{ route('settings.reports.malaria-vipimo.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- mRDT --}}
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-droplet-half text-danger fs-5"></i>
                        <h6 class="card-title mb-0">mRDT — Malaria Rapid Diagnostic Test</h6>
                        @if($config['malaria_mrdt_service_id'])
                            <span class="badge bg-success ms-auto">Configured</span>
                        @else
                            <span class="badge bg-secondary ms-auto">Not set</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lab Investigation</label>
                            <select name="malaria_mrdt_service_id" class="form-select">
                                <option value="">— Not set —</option>
                                @foreach($labServices as $svc)
                                    <option value="{{ $svc->id }}" {{ $config['malaria_mrdt_service_id'] == $svc->id ? 'selected' : '' }}>
                                        {{ $svc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Which lab investigation is the Malaria RDT?</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Required Result Template</label>
                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded border">
                                <i class="bi bi-lock-fill text-secondary"></i>
                                <span class="fw-semibold">{{ $mrdtTemplate->name ?? 'mRDT Malaria' }}</span>
                                <code class="ms-1 text-muted small">{{ $mrdtTemplate->code ?? 'mrdt_malaria' }}</code>
                            </div>
                            <div class="form-text">
                                Fixed by the report logic. Lab staff must select this template when entering mRDT results.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Blood Smear --}}
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-eyedropper text-primary fs-5"></i>
                        <h6 class="card-title mb-0">BS — Peripheral Blood Smear</h6>
                        @if($config['malaria_bs_service_id'])
                            <span class="badge bg-success ms-auto">Configured</span>
                        @else
                            <span class="badge bg-secondary ms-auto">Not set</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lab Investigation</label>
                            <select name="malaria_bs_service_id" class="form-select">
                                <option value="">— Not set —</option>
                                @foreach($labServices as $svc)
                                    <option value="{{ $svc->id }}" {{ $config['malaria_bs_service_id'] == $svc->id ? 'selected' : '' }}>
                                        {{ $svc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Which lab investigation is the Malaria Blood Smear?</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Required Result Template</label>
                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded border">
                                <i class="bi bi-lock-fill text-secondary"></i>
                                <span class="fw-semibold">{{ $bsTemplate->name ?? 'PBS – Malaria Parasites' }}</span>
                                <code class="ms-1 text-muted small">{{ $bsTemplate->code ?? 'pbs_malaria' }}</code>
                            </div>
                            <div class="form-text">
                                Fixed by the report logic. Lab staff must select this template when entering Blood Smear results.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
