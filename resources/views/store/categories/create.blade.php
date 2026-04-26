@extends('layouts.app_main_layout')

@section('page_title', 'Create Category')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create New Category</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store.dashboard') }}">Store</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store-categories.index') }}">Categories</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus me-2"></i>
                                Create New Category
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('store-categories.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Categories
                                </a>
                            </div>
                        </div>
                        
                        <form action="{{ route('store-categories.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        Category Description <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('description') is-invalid @enderror" 
                                           id="description" 
                                           name="description" 
                                           value="{{ old('description') }}" 
                                           placeholder="Enter category description (e.g., Medications, Consumables, Equipment)"
                                           required 
                                           maxlength="255">
                                    <small class="form-text text-muted">
                                        Enter a descriptive name for this category. This will be used to organize store items.
                                    </small>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> Categories help organize and classify items in your store inventory. 
                                    Choose clear, descriptive names that will make sense to all users.
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="{{ route('store-categories.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Category
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Usage Tips Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb me-2"></i>
                                Category Guidelines
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-success">Good Examples:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Medications</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Medical Equipment</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Disposable Items</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Laboratory Supplies</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Emergency Supplies</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-warning">Tips:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-star text-warning me-2"></i>Keep names concise but descriptive</li>
                                        <li><i class="fas fa-star text-warning me-2"></i>Use consistent naming conventions</li>
                                        <li><i class="fas fa-star text-warning me-2"></i>Avoid special characters</li>
                                        <li><i class="fas fa-star text-warning me-2"></i>Consider future needs when naming</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('styles')
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b3d7ff;
    color: #004085;
}

.text-danger {
    color: #dc3545 !important;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on the description field
    $('#description').focus();
    
    // Add character counter
    $('#description').on('input', function() {
        var length = $(this).val().length;
        var maxLength = 255;
        var remaining = maxLength - length;
        
        // Remove any existing counter
        $(this).parent().find('.char-counter').remove();
        
        // Add counter below the input
        $(this).after('<small class="char-counter text-muted">Characters remaining: ' + remaining + '</small>');
        
        // Change color if getting close to limit
        if (remaining < 20) {
            $(this).parent().find('.char-counter').removeClass('text-muted').addClass('text-warning');
        }
        if (remaining < 10) {
            $(this).parent().find('.char-counter').removeClass('text-warning').addClass('text-danger');
        }
    });
});
</script>
@endsection
