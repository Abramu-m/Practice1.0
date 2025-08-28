@extends('layouts.app_main_layout')

@section('page_title', 'Edit Category')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Category</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store.dashboard') }}">Store</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store-categories.index') }}">Categories</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('store-categories.show', $storeCategory->id) }}">{{ $storeCategory->description }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                                <i class="fas fa-edit mr-2"></i>
                                Edit Category: {{ $storeCategory->description }}
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('store-categories.show', $storeCategory->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="{{ route('store-categories.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Categories
                                </a>
                            </div>
                        </div>
                        
                        <form action="{{ route('store-categories.update', $storeCategory->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        Category Description <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('description') is-invalid @enderror" 
                                           id="description" 
                                           name="description" 
                                           value="{{ old('description', $storeCategory->description) }}" 
                                           placeholder="Enter category description"
                                           required 
                                           maxlength="255">
                                    <small class="form-text text-muted">
                                        Update the descriptive name for this category. This will be used to organize store items.
                                    </small>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Current Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle mr-2"></i>Current Information</h6>
                                            <ul class="mb-0">
                                                <li><strong>ID:</strong> {{ $storeCategory->id }}</li>
                                                <li><strong>Items in category:</strong> {{ $storeCategory->medications()->count() }}</li>
                                                <li><strong>Created:</strong> {{ $storeCategory->created_at->format('M d, Y') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if($storeCategory->medications()->count() > 0)
                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Important Note</h6>
                                                <p class="mb-0">
                                                    This category contains {{ $storeCategory->medications()->count() }} items. 
                                                    Changing the description will update how this category appears throughout the system.
                                                </p>
                                            </div>
                                        @else
                                            <div class="alert alert-success">
                                                <h6><i class="fas fa-check-circle mr-2"></i>Safe to Edit</h6>
                                                <p class="mb-0">
                                                    This category has no items yet, so you can safely modify it without affecting any products.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="{{ route('store-categories.show', $storeCategory->id) }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <a href="{{ route('store-categories.index') }}" class="btn btn-outline-secondary ml-2">
                                            <i class="fas fa-list"></i> Back to List
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> Update Category
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Related Items Preview (if any) -->
                    @if($storeCategory->medications()->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-boxes mr-2"></i>
                                Items That Will Be Affected ({{ $storeCategory->medications()->count() }})
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Generic Name</th>
                                            <th>Status</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($storeCategory->medications()->limit(10)->get() as $medication)
                                        <tr>
                                            <td>
                                                <strong>{{ $medication->name }}</strong>
                                                @if($medication->brand_name)
                                                    <br><small class="text-muted">{{ $medication->brand_name }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $medication->generic_name ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-sm {{ $medication->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $medication->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm {{ $medication->stock_quantity > 0 ? 'badge-info' : 'badge-danger' }}">
                                                    {{ number_format($medication->stock_quantity, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($storeCategory->medications()->count() > 10)
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <small>... and {{ $storeCategory->medications()->count() - 10 }} more items</small>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('store-categories.show', $storeCategory->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View All Items in Category
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Delete Option (if no items) -->
                    @if($storeCategory->medications()->count() == 0)
                    <div class="card mt-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h3 class="card-title">
                                <i class="fas fa-trash mr-2"></i>
                                Danger Zone
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                Since this category has no items, you can safely delete it if it's no longer needed.
                            </p>
                            <form action="{{ route('store-categories.destroy', $storeCategory->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete Category
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
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

.alert-warning {
    background-color: #fff8e1;
    border-color: #ffecb3;
    color: #8a6d3b;
}

.alert-success {
    background-color: #e8f5e8;
    border-color: #c3e6c3;
    color: #2d5a2d;
}

.text-danger {
    color: #dc3545 !important;
}

.badge-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus on the description field
    $('#description').focus().select();
    
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
    
    // Trigger counter on load
    $('#description').trigger('input');
});
</script>
@endsection
