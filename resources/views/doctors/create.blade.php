@extends('layouts.app_main_layout')

@section('page_title')
    Create Doctor
@endsection

@section('Content_Description')
    Add a new doctor.
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-md"></i> Create New Doctor
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('doctors.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Doctors
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('doctors.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="doctor_id">Select User <span class="text-danger">*</span></label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" required>
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                {{ (old('doctor_id') == $user->id || (isset($selectedUser) && $selectedUser->id == $user->id)) ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Only verified users with doctor role are shown.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="designation">Designation <span class="text-danger">*</span></label>
                                    <select class="form-control @error('designation') is-invalid @enderror" id="designation" name="designation" required>
                                        <option value="">Select Designation</option>
                                        @foreach($designations as $designation)
                                            <option value="{{ $designation->designation_code }}" {{ old('designation') == $designation->designation_code ? 'selected' : '' }}>
                                                {{ $designation->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="specialization">Specialization <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                           id="specialization" name="specialization" value="{{ old('specialization') }}" 
                                           placeholder="e.g., Cardiology, Pediatrics, Surgery" required>
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="mct_number">MCT Number</label>
                                    <input type="text" class="form-control @error('mct_number') is-invalid @enderror" 
                                           id="mct_number" name="mct_number" value="{{ old('mct_number') }}" 
                                           placeholder="Medical Council of Tanzania Number">
                                    @error('mct_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Optional but recommended for licensed doctors.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="drsignature">Digital Signature</label>
                                    <textarea class="form-control @error('drsignature') is-invalid @enderror" 
                                              id="drsignature" name="drsignature" rows="3" 
                                              placeholder="Digital signature or notes">{{ old('drsignature') }}</textarea>
                                    @error('drsignature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Doctor
                                    </button>
                                    <a href="{{ route('doctors.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
@endsection