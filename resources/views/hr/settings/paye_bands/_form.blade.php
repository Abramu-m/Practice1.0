@php
    $band = $band ?? null;
@endphp

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="band_order" class="form-label">Order <span class="text-danger">*</span></label>
            <input type="number" name="band_order" id="band_order" class="form-control @error('band_order') is-invalid @enderror"
                   value="{{ old('band_order', $band?->band_order ?? $nextOrder ?? 1) }}" min="1" required>
            @error('band_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Bands are applied in this order, lowest first.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="min_income" class="form-label">Min Income (Tsh) <span class="text-danger">*</span></label>
            <input type="number" name="min_income" id="min_income" step="0.01" min="0" class="form-control @error('min_income') is-invalid @enderror"
                   value="{{ old('min_income', $band?->min_income) }}" required>
            @error('min_income')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="max_income" class="form-label">Max Income (Tsh)</label>
            <input type="number" name="max_income" id="max_income" step="0.01" min="0" class="form-control @error('max_income') is-invalid @enderror"
                   value="{{ old('max_income', $band?->max_income) }}">
            @error('max_income')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Leave blank for the top/unbounded band.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="rate" class="form-label">Rate (%) <span class="text-danger">*</span></label>
            <input type="number" name="rate" id="rate" step="0.01" min="0" max="100" class="form-control @error('rate') is-invalid @enderror"
                   value="{{ old('rate', $band?->rate) }}" required>
            @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $band?->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Active</label>
</div>
