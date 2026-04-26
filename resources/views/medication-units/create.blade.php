@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Medication Unit</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-units.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('medication-units.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_name">Unit Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('unit_name') is-invalid @enderror" 
                                           id="unit_name" name="unit_name" value="{{ old('unit_name') }}" 
                                           placeholder="e.g., Milligram" required>
                                    @error('unit_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_code">Unit Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('unit_code') is-invalid @enderror" 
                                           id="unit_code" name="unit_code" value="{{ old('unit_code') }}" 
                                           placeholder="e.g., MG" required>
                                    @error('unit_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_symbol">Unit Symbol</label>
                                    <input type="text" class="form-control @error('unit_symbol') is-invalid @enderror" 
                                           id="unit_symbol" name="unit_symbol" value="{{ old('unit_symbol') }}" 
                                           placeholder="e.g., mg" maxlength="20">
                                    @error('unit_symbol')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_type">Unit Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('unit_type') is-invalid @enderror" 
                                            id="unit_type" name="unit_type" required>
                                        <option value="">Select Unit Type</option>
                                        <option value="weight" {{ old('unit_type') == 'weight' ? 'selected' : '' }}>Weight</option>
                                        <option value="volume" {{ old('unit_type') == 'volume' ? 'selected' : '' }}>Volume</option>
                                        <option value="dosage" {{ old('unit_type') == 'dosage' ? 'selected' : '' }}>Dosage</option>
                                        <option value="form" {{ old('unit_type') == 'form' ? 'selected' : '' }}>Form</option>
                                        <option value="other" {{ old('unit_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('unit_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="base_unit_id">Base Unit</label>
                                    <select class="form-control @error('base_unit_id') is-invalid @enderror" 
                                            id="base_unit_id" name="base_unit_id">
                                        <option value="">Select Base Unit (if applicable)</option>
                                        <!-- Base units will be loaded via AJAX based on unit type -->
                                    </select>
                                    @error('base_unit_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Select a base unit for conversion calculations</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="conversion_factor">Conversion Factor</label>
                                    <input type="text" class="form-control @error('conversion_factor') is-invalid @enderror" 
                                           id="conversion_factor" name="conversion_factor" value="{{ old('conversion_factor') }}" 
                                           step="0.00001" min="0" placeholder="e.g., 1000">
                                    @error('conversion_factor')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">How many of this unit equals 1 base unit</small>
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
                            <i class="fas fa-save"></i> Create Unit
                        </button>
                        <a href="{{ route('medication-units.index') }}" class="btn btn-secondary">
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
    const unitTypeSelect = document.getElementById('unit_type');
    const baseUnitSelect = document.getElementById('base_unit_id');
    const conversionFactorInput = document.getElementById('conversion_factor');

    unitTypeSelect.addEventListener('change', function() {
        const unitType = this.value;
        if (unitType) {
            // Load base units for the selected type
            fetch(`{{ route('medication-units.api.base-units') }}?type=${unitType}`)
                .then(response => response.json())
                .then(data => {
                    baseUnitSelect.innerHTML = '<option value="">Select Base Unit (if applicable)</option>';
                    data.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = unit.display_name;
                        if ({{ old('base_unit_id', 'null') }} == unit.id) {
                            option.selected = true;
                        }
                        baseUnitSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading base units:', error);
                });
        } else {
            baseUnitSelect.innerHTML = '<option value="">Select Base Unit (if applicable)</option>';
        }
    });

    baseUnitSelect.addEventListener('change', function() {
        if (this.value) {
            conversionFactorInput.setAttribute('required', 'required');
        } else {
            conversionFactorInput.removeAttribute('required');
        }
    });

    // Trigger unit type change if there's an old value
    if (unitTypeSelect.value) {
        unitTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
