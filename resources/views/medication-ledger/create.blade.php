@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa fa-plus"></i> Add Medication Batch
          </h3>
          <div class="card-tools">
            <a href="{{ route('medication-ledger.index') }}" class="btn btn-secondary btn-sm">
              <i class="fa fa-arrow-left"></i> Back to Ledger
            </a>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('medication-ledger.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="medication_id">Medication <span class="text-danger">*</span></label>
                  <select name="medication_id" id="medication_id" class="form-control @error('medication_id') is-invalid @enderror" required>
                    <option value="">Select Medication</option>
                    @foreach($medications as $medication)
                      <option value="{{ $medication->id }}" {{ old('medication_id') == $medication->id ? 'selected' : '' }}>
                        {{ $medication->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('medication_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="batch_number">Batch Number <span class="text-danger">*</span></label>
                  <input type="text" name="batch_number" id="batch_number" 
                         class="form-control @error('batch_number') is-invalid @enderror" 
                         value="{{ old('batch_number') }}" required>
                  @error('batch_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="goods_received_note_id">Goods Received Note</label>
                  <select name="goods_received_note_id" id="goods_received_note_id" class="form-control @error('goods_received_note_id') is-invalid @enderror">
                    <option value="">Select GRN (Optional)</option>
                    @foreach($grns as $grn)
                      <option value="{{ $grn->id }}" {{ old('goods_received_note_id') == $grn->id ? 'selected' : '' }}>
                        GRN #{{ $grn->id }} - {{ $grn->date }}
                      </option>
                    @endforeach
                  </select>
                  @error('goods_received_note_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="supplier_name">Supplier Name</label>
                  <input type="text" name="supplier_name" id="supplier_name" 
                         class="form-control @error('supplier_name') is-invalid @enderror" 
                         value="{{ old('supplier_name') }}">
                  @error('supplier_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="initial_quantity">Initial Quantity <span class="text-danger">*</span></label>
                  <input type="text" name="initial_quantity" id="initial_quantity" 
                         class="form-control @error('initial_quantity') is-invalid @enderror" 
                         value="{{ old('initial_quantity') }}" min="1" required>
                  @error('initial_quantity')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="cost_per_unit">Cost per Unit</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">$</span>
                    </div>
                    <input type="text" name="cost_per_unit" id="cost_per_unit" 
                           class="form-control @error('cost_per_unit') is-invalid @enderror" 
                           value="{{ old('cost_per_unit') }}" step="0.01" min="0">
                    @error('cost_per_unit')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="total_cost">Total Cost</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">$</span>
                    </div>
                    <input type="text" name="total_cost" id="total_cost" 
                           class="form-control @error('total_cost') is-invalid @enderror" 
                           value="{{ old('total_cost') }}" step="0.01" min="0" readonly>
                    @error('total_cost')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="expiry_date">Expiry Date</label>
                  <input type="date" name="expiry_date" id="expiry_date" 
                         class="form-control @error('expiry_date') is-invalid @enderror" 
                         value="{{ old('expiry_date') }}">
                  @error('expiry_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="manufacture_date">Manufacture Date</label>
                  <input type="date" name="manufacture_date" id="manufacture_date" 
                         class="form-control @error('manufacture_date') is-invalid @enderror" 
                         value="{{ old('manufacture_date') }}">
                  @error('manufacture_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="storage_location">Storage Location</label>
                  <input type="text" name="storage_location" id="storage_location" 
                         class="form-control @error('storage_location') is-invalid @enderror" 
                         value="{{ old('storage_location') }}">
                  @error('storage_location')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="status">Status <span class="text-danger">*</span></label>
                  <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="recalled" {{ old('status') == 'recalled' ? 'selected' : '' }}>Recalled</option>
                    <option value="damaged" {{ old('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="depleted" {{ old('status') == 'depleted' ? 'selected' : '' }}>Depleted</option>
                  </select>
                  @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="mb-3">
                  <label for="notes">Notes</label>
                  <textarea name="notes" id="notes" rows="3" 
                            class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                  @error('notes')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save"></i> Save Batch
                </button>
                <a href="{{ route('medication-ledger.index') }}" class="btn btn-secondary">
                  <i class="fa fa-times"></i> Cancel
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const initialQuantityInput = document.getElementById('initial_quantity');
  const costPerUnitInput = document.getElementById('cost_per_unit');
  const totalCostInput = document.getElementById('total_cost');

  function calculateTotal() {
    const quantity = parseFloat(initialQuantityInput.value) || 0;
    const costPerUnit = parseFloat(costPerUnitInput.value) || 0;
    const total = quantity * costPerUnit;
    totalCostInput.value = total.toFixed(2);
  }

  initialQuantityInput.addEventListener('input', calculateTotal);
  costPerUnitInput.addEventListener('input', calculateTotal);
});
</script>
@endsection
