@extends('layouts.app_main_layout')

@section('page_title', 'Configure — Haematology Report')

@section('main_content')
<div class="container">

    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-0">Haematology — Report Configuration</h4>
            <p class="text-muted mb-0 mt-1">Ramani ya Huduma za Maabara kwa Taarifa ya Damu</p>
        </div>
        <a href="{{ route('admin.reports.lab-hematology') }}" class="btn btn-outline-secondary btn-sm">
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
        <strong>How this works:</strong> Select which lab investigation maps to each form row.
        Lab staff <strong>must</strong> use the listed result template when recording results —
        otherwise LOW / HIGH extraction will not find any data.
        Configuring <strong>FBP</strong> automatically covers WBC COUNT, WBC DIFF, Platelets,
        Reticulocytes, Peripheral Blood film, PCV and RBC Count.
    </div>

    <form method="POST" action="{{ route('admin.lab-settings.hematology.update') }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-tint me-2 text-danger"></i>Test Row Mapping</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:22%">Form Row</th>
                            <th style="width:28%">Lab Investigation</th>
                            <th>Required Result Template</th>
                            <th style="width:110px" class="text-center">Tracks</th>
                            <th style="width:100px" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td class="align-middle fw-semibold">
                                {{ $row->row_label }}
                                @if($row->row_key === 'fbp')
                                    <div class="text-muted small fw-normal mt-1">
                                        Also covers: WBC COUNT, WBC DIFF, Platelets,
                                        Reticulocytes, Peripheral Blood film, PCV, RBC Count
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle">
                                <select name="service_id_{{ $row->row_key }}" class="form-select form-select-sm">
                                    <option value="">— Not set —</option>
                                    @foreach($labServices as $svc)
                                        <option value="{{ $svc->id }}"
                                            {{ ($row->service_ids[0] ?? null) == $svc->id ? 'selected' : '' }}>
                                            {{ $svc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                @if($row->required_template_name)
                                    @php $code = $templateCodes[$row->required_template_name] ?? null; @endphp
                                    <div class="d-flex align-items-center gap-2 p-2 bg-light rounded border">
                                        <i class="bi bi-lock-fill text-secondary"></i>
                                        <span class="fw-semibold small">{{ $row->required_template_name }}</span>
                                        @if($code)
                                            <code class="ms-1 text-muted" style="font-size:0.75em">{{ $code }}</code>
                                        @endif
                                    </div>
                                    <div class="form-text">Lab staff must select this template when entering results.</div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($row->track_low_high)
                                    <span class="badge bg-info text-dark">LOW / HIGH</span>
                                @else
                                    <span class="badge bg-light text-muted border">Count only</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if(!empty($row->service_ids))
                                    <span class="badge bg-success">Configured</span>
                                @else
                                    <span class="badge bg-secondary">Not set</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
            <a href="{{ route('admin.reports.lab-hematology') }}" class="btn btn-outline-secondary ms-2">
                Back to Report
            </a>
        </div>

    </form>
</div>
@endsection
