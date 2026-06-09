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
        <strong>How this works:</strong> Each test slot (mRDT and BS) is paired to a lab investigation <em>and</em> a result template.
        The report will only count results that were entered using the specified template.
        Make sure lab staff select the correct template when recording results for each of these tests.
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
                        @if($config['malaria_mrdt_service_id'] && !$config['malaria_mrdt_template_name'])
                            <span class="badge bg-warning text-dark ms-auto">Template not set</span>
                        @elseif($config['malaria_mrdt_service_id'] && $config['malaria_mrdt_template_name'])
                            <span class="badge bg-success ms-auto">Configured</span>
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
                            <div class="form-text">Which investigation is the Malaria RDT?</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Expected Result Template</label>
                            <select name="malaria_mrdt_template_name" class="form-select">
                                <option value="">— No template filter —</option>
                                @foreach($availableTemplates as $tpl)
                                    <option value="{{ $tpl }}" {{ $config['malaria_mrdt_template_name'] === $tpl ? 'selected' : '' }}>
                                        {{ $tpl }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Results will only be counted if they use this template.
                                Leave blank to count results from any template (not recommended).
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
                        <h6 class="card-title mb-0">BS — Blood Smear (Peripheral Blood Smear)</h6>
                        @if($config['malaria_bs_service_id'] && !$config['malaria_bs_template_name'])
                            <span class="badge bg-warning text-dark ms-auto">Template not set</span>
                        @elseif($config['malaria_bs_service_id'] && $config['malaria_bs_template_name'])
                            <span class="badge bg-success ms-auto">Configured</span>
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
                            <div class="form-text">Which investigation is the Malaria Blood Smear?</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Expected Result Template</label>
                            <select name="malaria_bs_template_name" class="form-select">
                                <option value="">— No template filter —</option>
                                @foreach($availableTemplates as $tpl)
                                    <option value="{{ $tpl }}" {{ $config['malaria_bs_template_name'] === $tpl ? 'selected' : '' }}>
                                        {{ $tpl }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Results will only be counted if they use this template.
                                Leave blank to count results from any template (not recommended).
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
