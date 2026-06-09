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
        <strong>How this works:</strong> Select which lab investigation corresponds to each form row.
        Rows marked <span class="badge bg-info text-dark">LOW / HIGH</span> will also count results
        that fall below or above the reference range.
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
                            <th style="width:35%">Form Row</th>
                            <th>Lab Investigation</th>
                            <th style="width:130px" class="text-center">Tracks</th>
                            <th style="width:110px" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td class="align-middle fw-semibold">{{ $row->row_label }}</td>
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
