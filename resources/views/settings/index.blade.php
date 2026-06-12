@extends('layouts.app_main_layout')

@section('page_title', 'Settings')

@section('main_content')
<div class="container">
    <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0">Settings</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-hospital me-2"></i>Facility Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.facility.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Facility Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $facility->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Slogan</label>
                        <input type="text" name="slogan" class="form-control"
                               value="{{ old('slogan', $facility->slogan) }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Facility Logo</label>
                        @if($facility->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $facility->logo) }}" alt="Current Facility Logo" class="img-thumbnail" width="100">
                            </div>
                        @endif
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Recommended: square PNG or JPG, max 2MB. Used in the sidebar branding.</small>
                        @if($facility->logo)
                            <div class="form-check mt-2">
                                <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="remove_logo">
                                <label class="form-check-label" for="remove_logo">Remove current logo</label>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $facility->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $facility->phone) }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $facility->address) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control"
                               value="{{ old('country', $facility->country) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Region</label>
                        <input type="text" name="region" class="form-control"
                               value="{{ old('region', $facility->region) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">District</label>
                        <input type="text" name="district" class="form-control"
                               value="{{ old('district', $facility->district) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Locale / Ward</label>
                        <input type="text" name="locale" class="form-control"
                               value="{{ old('locale', $facility->locale) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Postal</label>
                        <input type="text" name="postal" class="form-control"
                               value="{{ old('postal', $facility->postal) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">NHIF Facility Code</label>
                        <input type="text" name="nhif_facility_code" class="form-control"
                               value="{{ old('nhif_facility_code', $facility->nhif_facility_code) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">HFR Code</label>
                        <input type="text" name="hfr_code" class="form-control"
                               value="{{ old('hfr_code', $facility->hfr_code) }}"
                               placeholder="MoH Health Facility Registry code">
                        <small class="text-muted">Official MoH Health Facility Registry identifier (separate from the NHIF facility code).</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">In Charge</label>
                        <select name="in_charge" class="form-select">
                            <option value="">— None —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('in_charge', $facility->in_charge) == $user->id)>
                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- CONFIGURE REPORTS                                   --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="container" id="report-config">
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Configure Reports</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Each report has its own dedicated configuration page.</p>
            <a href="{{ route('settings.reports.malaria-vipimo') }}" class="btn btn-outline-primary">
                <i class="bi bi-virus me-1 text-danger"></i> Malaria Vipimo — Configure
            </a>
            <a href="{{ route('settings.reports.alu-monthly') }}" class="btn btn-outline-primary">
                <i class="bi bi-capsule me-1 text-danger"></i> ALu Report — Configure
            </a>
        </div>
    </div>
</div>
@endsection
