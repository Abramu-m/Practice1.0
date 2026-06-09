@extends('layouts.app_main_layout')

@section('page_title', 'Configure — Blood Transfusion Report')

@section('main_content')
<div class="container">

    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-0">Blood Transfusion — Report Configuration</h4>
            <p class="text-muted mb-0 mt-1">Ramani ya Huduma za Maabara kwa Taarifa ya Uhamishaji Damu</p>
        </div>
        <a href="{{ route('admin.reports.lab-blood-transfusion') }}" class="btn btn-outline-secondary btn-sm">
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
        The report will count all investigations of that type performed during the selected month.
    </div>

    <form method="POST" action="{{ route('admin.lab-settings.blood-transfusion.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- Blood Grouping, RH Typing & Crossmatching --}}
            @php $row1 = $rows['blood_grouping_rh_crossmatch'] ?? null; @endphp
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-droplet-fill text-danger fs-5"></i>
                        <h6 class="card-title mb-0">Blood Grouping, RH Typing &amp; Crossmatching</h6>
                        @if(!empty($row1?->service_ids))
                            <span class="badge bg-success ms-auto">Configured</span>
                        @else
                            <span class="badge bg-secondary ms-auto">Not set</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Lab Investigation</label>
                            <select name="service_id_blood_grouping_rh_crossmatch" class="form-select">
                                <option value="">— Not set —</option>
                                @foreach($labServices as $svc)
                                    <option value="{{ $svc->id }}"
                                        {{ ($row1?->service_ids[0] ?? null) == $svc->id ? 'selected' : '' }}>
                                        {{ $svc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Which lab investigation is Blood Grouping / RH Typing / Crossmatching?</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COOMB'S Test --}}
            @php $row2 = $rows['coombs_test'] ?? null; @endphp
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-eyedropper text-primary fs-5"></i>
                        <h6 class="card-title mb-0">COOMB'S Test</h6>
                        @if(!empty($row2?->service_ids))
                            <span class="badge bg-success ms-auto">Configured</span>
                        @else
                            <span class="badge bg-secondary ms-auto">Not set</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Lab Investigation</label>
                            <select name="service_id_coombs_test" class="form-select">
                                <option value="">— Not set —</option>
                                @foreach($labServices as $svc)
                                    <option value="{{ $svc->id }}"
                                        {{ ($row2?->service_ids[0] ?? null) == $svc->id ? 'selected' : '' }}>
                                        {{ $svc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Which lab investigation is the COOMB'S Test?</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
            <a href="{{ route('admin.reports.lab-blood-transfusion') }}" class="btn btn-outline-secondary ms-2">
                Back to Report
            </a>
        </div>

    </form>
</div>
@endsection
