@extends('layouts.app_main_layout')

@section('page_title', 'Configure — Medicine Dispensing Report')

@section('main_content')
<div class="container">

    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-0">Medicine Dispensing Report — Configuration</h4>
            <p class="text-muted mb-0 mt-1">Ramani ya Dawa kwa Taarifa ya Mwezi ya Kutolea Dawa</p>
        </div>
        <a href="{{ route('admin.reports.medicines-monthly') }}" class="btn btn-outline-secondary btn-sm">
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
        <strong>How this works:</strong> Select which medication in the catalogue corresponds to each row of the
        official Monthly Drug Dispensing Form (Taarifa ya Mwezi ya Kutolea Dawa). Quantities dispensed are pulled
        from pharmacy dispensing records and split by patient age. Rows left as "— Not set —" will show blank
        on the report.
    </div>

    <form method="POST" action="{{ route('admin.pharmacy-settings.medicine-dispensing.update') }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-pills me-2 text-success"></i>Form Row Mapping</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">Na</th>
                            <th style="width:25%">Dawa (Drug)</th>
                            <th style="width:15%">Kipimo (Unit)</th>
                            <th>Medication</th>
                            <th style="width:100px" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td class="align-middle">{{ $row->row_no }}</td>
                            <td class="align-middle fw-semibold">{{ $row->display_label }}</td>
                            <td class="align-middle">{{ $row->unit_label }}</td>
                            <td class="align-middle">
                                <select name="medication_id_{{ $row->row_key }}" class="form-select form-select-sm select2-medication" style="width:100%">
                                    <option value="">— Not set —</option>
                                    @foreach($medications as $med)
                                        <option value="{{ $med->id }}" {{ $row->medication_id == $med->id ? 'selected' : '' }}>
                                            {{ $med->generic_name }}{{ $med->brand_name ? ' ('.$med->brand_name.')' : '' }}{{ $med->strength ? ' — '.$med->strength : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle text-center">
                                @if($row->medication_id)
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
            <a href="{{ route('admin.reports.medicines-monthly') }}" class="btn btn-outline-secondary ms-2">
                Back to Report
            </a>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('.select2-medication').select2({
        placeholder: 'Type to search for a medication...',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection
