@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Integration Settings')

@section('main_content')
<div class="container">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0">NHIF Integration Settings</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-shield-check me-2"></i>Mode &amp; Credentials</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('nhif.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Environment</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_test" value="test"
                                       {{ old('mode', $settings->mode) === 'test' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_test">Test / Sandbox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_production" value="production"
                                       {{ old('mode', $settings->mode) === 'production' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_production">Production / Live</label>
                            </div>
                        </div>
                        @error('mode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <div class="form-text">Switches which NHIF servers the integration talks to. The same credentials below are used for both.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', $settings->username) }}" autocomplete="off">
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               value="" placeholder="{{ $settings->password ? '••••••••' : '' }}" autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Leave blank to keep the current password unchanged.</div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Active Endpoints &amp; Reference</h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Currently running in
                <span class="badge {{ $settings->mode === 'production' ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ ucfirst($settings->mode) }}
                </span>
                mode. These NHIF API endpoints are fixed by NHIF and shown here for reference only.
            </p>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold mb-1">NHIF Facility Code</label>
                    <div class="form-control bg-light">{{ $facility->nhif_facility_code ?: '— not set —' }}</div>
                    <div class="form-text">Edit this on the main <a href="{{ route('settings.index') }}">Settings</a> page.</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 220px;">Endpoint</th>
                            <th>URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($endpoints as $label => $url)
                            <tr>
                                <td class="fw-semibold">{{ $label }}</td>
                                <td class="text-break">{{ $url ?: '— not configured —' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
