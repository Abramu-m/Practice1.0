<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patient_categories\create.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Add Patient Category' }}
@endsection

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>Add New Patient Category</h3>
        </div>
        <form action="{{ route('patient_categories.store') }}" method="POST">
            @csrf
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

                <div class="mb-3">
                    <label for="description">Description *</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" required maxlength="50">
                    <small class="form-text text-muted">Maximum 50 characters</small>
                </div>

                <div class="mb-3">
                    <label for="type">Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="cash" {{ old('type', 'cash') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="insurance" {{ old('type') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                    </select>
                    <small class="form-text text-muted">Choose whether this category is for cash or insurance patients</small>
                </div>

                <div class="mb-3 mt-2">
                    <label for="code">Code (optional)</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" maxlength="30">
                    <small class="form-text text-muted">Short code for programmatic checks (e.g., NHIF)</small>
                </div>

                <div class="mb-3">
                    <label for="tariffs_table">Tariffs Table</label>
                    <select name="tariffs_table" class="form-select">
                        <option value="">— None —</option>
                        @foreach($tariffTables as $table)
                            <option value="{{ $table }}" {{ old('tariffs_table') == $table ? 'selected' : '' }}>{{ $table }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        Insurance only. The price table this insurer's tariffs are read from
                        (e.g. <code>nhif_tariffs</code>). Leave as <em>None</em> for cash categories.
                    </small>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Create Category</button>
                <a href="{{ route('patient_categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('extra_footer_content')
@endsection
