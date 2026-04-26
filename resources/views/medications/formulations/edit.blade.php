@extends('layouts.app_main_layout')

@section('page_title', 'Edit Formulation')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Formulation: {{ $formulation->description }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('medications.formulations.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('medications.formulations.show', $formulation->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                <form action="{{ route('medications.formulations.update', $formulation->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description', $formulation->description) }}" required
                                   placeholder="e.g., Tablet, Capsule, Syrup, etc.">
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Enter a clear description of the formulation type.</small>
                        </div>

                        <div class="mb-3">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $formulation->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Only active formulations will be available for selection in medication forms.</small>
                        </div>

                        @if($formulation->isInUse())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> This formulation is currently being used by {{ $formulation->medications()->count() }} medication(s). 
                            Changes will affect existing medications.
                        </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Formulation
                        </button>
                        <a href="{{ route('medications.formulations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulation Info</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>ID:</th>
                            <td>{{ $formulation->id }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="text-black badge bg-{{ $formulation->is_active ? 'success' : 'secondary' }}">
                                    {{ $formulation->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Medications:</th>
                            <td>{{ $formulation->medications()->count() }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $formulation->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Updated:</th>
                            <td>{{ $formulation->updated_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Guidelines</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb text-warning"></i> Tips for Formulations</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Use standard pharmaceutical terms</li>
                        <li><i class="fas fa-check text-success"></i> Keep descriptions concise and clear</li>
                        <li><i class="fas fa-check text-success"></i> Examples: Tablet, Capsule, Injection, Cream</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
