<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patient_categories\edit.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Edit Patient Category' }}
 @endsection

@section('Content_Description')
    {{ 'Edit patient category details.' }}
@endsection

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>Edit Patient Category: {{ $patientCategory->description }}</h3>
        </div>
        <form action="{{ route('patient_categories.update', $patientCategory->id) }}" method="POST">
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

                <div class="form-group">
                    <label for="description">Description *</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $patientCategory->description) }}" required maxlength="50">
                    <small class="form-text text-muted">Maximum 50 characters</small>
                </div>

                <div class="form-group">
                    <label for="type">Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="cash" {{ old('type', $patientCategory->type) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="insurance" {{ old('type', $patientCategory->type) == 'insurance' ? 'selected' : '' }}>Insurance</option>
                    </select>
                    <small class="form-text text-muted">Choose whether this category is for cash or insurance patients</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $patientCategory->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label for="code">Code (optional)</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $patientCategory->code) }}" maxlength="30">
                    <small class="form-text text-muted">Short code for programmatic checks (e.g., NHIF)</small>
                </div>
                <!-- is_insurance and is_nhif flags removed; use `type` and `code` instead -->
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="{{ route('patient_categories.show', $patientCategory->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('extra_footer_content')
@endsection