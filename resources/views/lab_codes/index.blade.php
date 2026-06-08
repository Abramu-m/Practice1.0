@extends('layouts.app_main_layout')

@section('page_title', 'LOINC / SNOMED Lab Codes')

@section('main_content')
<div class="container-fluid">
    <h3 class="mb-3">Lab code library — LOINC &amp; SNOMED CT</h3>
    <p class="text-muted">
        Reference library of LOINC and SNOMED CT codes for laboratory/medical services. Attach a
        code to a service from its edit form — search by code or name there to map it.
    </p>

    <form method="GET" action="{{ route('lab-codes.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="term" class="form-control" placeholder="Search code or name"
                       value="{{ old('term', request('term')) }}">
            </div>
            <div class="col-md-3">
                <select name="system" class="form-select" onchange="this.form.submit()">
                    <option value="">All coding systems</option>
                    <option value="loinc" {{ request('system') === 'loinc' ? 'selected' : '' }}>LOINC</option>
                    <option value="snomed" {{ request('system') === 'snomed' ? 'selected' : '' }}>SNOMED CT</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-secondary" type="submit">Search</button>
                <a href="{{ route('lab-codes.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>System</th>
                <th>Code</th>
                <th>Display name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($labCodes as $item)
            <tr>
                <td>
                    @if($item->coding_system === 'loinc')
                        <span class="badge bg-info text-dark">LOINC</span>
                    @else
                        <span class="badge bg-primary">SNOMED CT</span>
                    @endif
                </td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->display_name }}</td>
                <td>
                    @if($item->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">
                    No lab codes found. Import the library via
                    <code>php artisan codes:import loinc database/seeders/data/loinc-codes-sample.csv</code> or
                    <code>php artisan codes:import snomed database/seeders/data/snomed-codes-sample.csv</code>
                    (replace the sample file with a real code list when available).
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-3">
        {{ $labCodes->links() }}
    </div>
</div>
@endsection
