@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Medication Frequency</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-frequencies.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('medication-frequencies.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frequency_name">Frequency Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('frequency_name') is-invalid @enderror" 
                                           id="frequency_name" name="frequency_name" value="{{ old('frequency_name') }}" 
                                           placeholder="e.g., Twice Daily" required>
                                    @error('frequency_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frequency_code">Frequency Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('frequency_code') is-invalid @enderror" 
                                           id="frequency_code" name="frequency_code" value="{{ old('frequency_code') }}" 
                                           placeholder="e.g., BID" required>
                                    @error('frequency_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_order">Display Order</label>
                                    <input type="text" class="form-control @error('display_order') is-invalid @enderror" 
                                           id="display_order" name="display_order" value="{{ old('display_order', 1) }}" 
                                           min="1" max="100">
                                    @error('display_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Lower numbers appear first in lists</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Administration Times <span class="text-danger">*</span></label>
                            <div id="administration-times-container">
                                @if(old('administration_times'))
                                    @foreach(old('administration_times') as $index => $time)
                                        <div class="input-group mb-2 administration-time-row">
                                            <input type="time" class="form-control @error('administration_times.'.$index) is-invalid @enderror" 
                                                   name="administration_times[]" value="{{ $time }}" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-danger remove-time-btn" type="button">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 administration-time-row">
                                        <input type="time" class="form-control" name="administration_times[]" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-danger remove-time-btn" type="button">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-success btn-sm" id="add-time-btn">
                                <i class="fas fa-plus"></i> Add Time
                            </button>
                            @error('administration_times')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Frequency
                        </button>
                        <a href="{{ route('medication-frequencies.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('administration-times-container');
    const addBtn = document.getElementById('add-time-btn');

    addBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 administration-time-row';
        newRow.innerHTML = `
            <input type="time" class="form-control" name="administration_times[]" required>
            <div class="input-group-append">
                <button class="btn btn-danger remove-time-btn" type="button">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-time-btn') || e.target.parentElement.classList.contains('remove-time-btn')) {
            const row = e.target.closest('.administration-time-row');
            if (container.children.length > 1) {
                row.remove();
            } else {
                alert('At least one administration time is required.');
            }
        }
    });
});
</script>
@endsection
