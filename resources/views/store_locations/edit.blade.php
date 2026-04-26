@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Store Location</h3>
                    <div class="card-tools">
                        <a href="{{ route('store-locations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('store-locations.show', $location) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
                <form action="{{ route('store-locations.update', $location) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="required">Location Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $location->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="required">Location Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $location->code) }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="required">Location Type</label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="store" {{ old('type', $location->type) === 'store' ? 'selected' : '' }}>Store</option>
                                        <option value="dispensing" {{ old('type', $location->type) === 'dispensing' ? 'selected' : '' }}>Dispensing</option>
                                        <option value="radiology" {{ old('type', $location->type) === 'radiology' ? 'selected' : '' }}>Radiology</option>
                                        <option value="laboratory" {{ old('type', $location->type) === 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                                        <option value="nursing" {{ old('type', $location->type) === 'nursing' ? 'selected' : '' }}>Nursing</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="required">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', $location->is_active ? 'active' : 'inactive') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $location->is_active ? 'active' : 'inactive') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $location->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manager_name">Manager Name</label>
                                    <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                           id="manager_name" name="manager_name" value="{{ old('manager_name', $location->manager_name) }}">
                                    @error('manager_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manager_contact">Manager Contact</label>
                                    <input type="text" class="form-control @error('manager_contact') is-invalid @enderror" 
                                           id="manager_contact" name="manager_contact" value="{{ old('manager_contact', $location->manager_contact) }}">
                                    @error('manager_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $location->sort_order) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Permissions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="can_request" name="can_request" value="1" 
                                                   {{ old('can_request', $location->can_request) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="can_request">
                                                Can Request
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="can_issue" name="can_issue" value="1" 
                                                   {{ old('can_issue', $location->can_issue) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="can_issue">
                                                Can Issue
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="can_receive" name="can_receive" value="1" 
                                                   {{ old('can_receive', $location->can_receive) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="can_receive">
                                                Can Receive
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="requires_approval" name="requires_approval" value="1" 
                                                   {{ old('requires_approval', $location->requires_approval) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="requires_approval">
                                                Requires Approval
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Location
                        </button>
                        <a href="{{ route('store-locations.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
