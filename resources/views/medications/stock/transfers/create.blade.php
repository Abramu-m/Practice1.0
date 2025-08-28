@extends('layouts.app_main_layout')

@section('page_title', 'Create Stock Transfer')

@section('main_content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-plus text-primary me-2"></i>
                        Create Stock Transfer
                    </h1>
                    <p class="text-muted mb-0">Transfer medications between store locations</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medications.stock.transfers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Transfers
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Transfer Form --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-form text-primary me-2"></i>
                        Transfer Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('medications.stock.transfers.store') }}" method="POST" id="transferForm">
                        @csrf
                        
                        {{-- Basic Information --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">From Location <span class="text-danger">*</span></label>
                                <select name="from_location_id" class="form-select @error('from_location_id') is-invalid @enderror" required>
                                    <option value="">Select Source Location</option>
                                    @foreach($storeLocations as $location)
                                    <option value="{{ $location->id }}" {{ old('from_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} 
                                        @if($location->code ?? false)
                                        ({{ $location->code }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('from_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">To Location <span class="text-danger">*</span></label>
                                <select name="to_location_id" class="form-select @error('to_location_id') is-invalid @enderror" required>
                                    <option value="">Select Destination Location</option>
                                    @foreach($storeLocations as $location)
                                    <option value="{{ $location->id }}" {{ old('to_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} 
                                        @if($location->code ?? false)
                                        ({{ $location->code }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('to_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" 
                                       class="form-control @error('transfer_date') is-invalid @enderror" 
                                       value="{{ old('transfer_date', date('Y-m-d')) }}" 
                                       min="{{ date('Y-m-d') }}" required>
                                @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" 
                                          class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Add transfer notes or reason...">{{ old('notes') }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Transfer Items --}}
                        <div class="card border border-primary">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-pills me-2"></i>
                                        Transfer Items
                                    </h6>
                                    <button type="button" class="btn btn-light btn-sm" onclick="addTransferItem()">
                                        <i class="fas fa-plus me-1"></i>
                                        Add Item
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="transferItems">
                                    @if(old('items'))
                                        @foreach(old('items') as $index => $item)
                                        <div class="transfer-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Medication <span class="text-danger">*</span></label>
                                                    <select name="items[{{ $index }}][medication_id]" class="form-select medication-select" required>
                                                        <option value="">Select Medication</option>
                                                        @foreach($medications as $medication)
                                                        <option value="{{ $medication->id }}" {{ $item['medication_id'] == $medication->id ? 'selected' : '' }}>
                                                            {{ $medication->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                    <input type="text" name="items[{{ $index }}][quantity]" 
                                                           class="form-control" step="0.01" min="0.01" 
                                                           value="{{ $item['quantity'] }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Unit Cost</label>
                                                    <input type="text" name="items[{{ $index }}][unit_cost]" 
                                                           class="form-control" step="0.01" min="0" 
                                                           value="{{ $item['unit_cost'] }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Batch Number</label>
                                                    <input type="text" name="items[{{ $index }}][batch_number]" 
                                                           class="form-control" 
                                                           value="{{ $item['batch_number'] }}">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeTransferItem(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <div class="stock-info text-muted small d-none">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <span class="stock-details"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                    {{-- Default empty item --}}
                                    <div class="transfer-item border rounded p-3 mb-3" data-index="0">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Medication <span class="text-danger">*</span></label>
                                                <select name="items[0][medication_id]" class="form-select medication-select" required>
                                                    <option value="">Select Medication</option>
                                                    @foreach($medications as $medication)
                                                    <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                <input type="text" name="items[0][quantity]" 
                                                       class="form-control" step="0.01" min="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit Cost</label>
                                                <input type="text" name="items[0][unit_cost]" 
                                                       class="form-control" step="0.01" min="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Batch Number</label>
                                                <input type="text" name="items[0][batch_number]" class="form-control">
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeTransferItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="stock-info text-muted small d-none">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <span class="stock-details"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- No Items Message --}}
                                <div id="noItemsMessage" class="text-center py-4 d-none">
                                    <i class="fas fa-pills fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No transfer items added yet.</p>
                                    <button type="button" class="btn btn-primary" onclick="addTransferItem()">
                                        <i class="fas fa-plus me-1"></i>
                                        Add First Item
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('medications.stock.transfers.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Create Transfer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Transfer Item Template --}}
<template id="transferItemTemplate">
    <div class="transfer-item border rounded p-3 mb-3" data-index="">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Medication <span class="text-danger">*</span></label>
                <select name="items[][medication_id]" class="form-select medication-select" required>
                    <option value="">Select Medication</option>
                    @foreach($medications as $medication)
                    <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="text" name="items[][quantity]" 
                       class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit Cost</label>
                <input type="text" name="items[][unit_cost]" 
                       class="form-control" step="0.01" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Batch Number</label>
                <input type="text" name="items[][batch_number]" class="form-control">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger" onclick="removeTransferItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="stock-info text-muted small d-none">
                    <i class="fas fa-info-circle me-1"></i>
                    <span class="stock-details"></span>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
let itemIndex = {{ old('items') ? count(old('items')) : 1 }};

function addTransferItem() {
    const template = document.getElementById('transferItemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Update the index
    const transferItem = clone.querySelector('.transfer-item');
    transferItem.setAttribute('data-index', itemIndex);
    
    // Update input names
    const inputs = clone.querySelectorAll('input, select');
    inputs.forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace('[]', `[${itemIndex}]`));
        }
    });
    
    // Add to container
    document.getElementById('transferItems').appendChild(clone);
    
    // Initialize medication change handler for new item
    const medicationSelect = transferItem.querySelector('.medication-select');
    medicationSelect.addEventListener('change', function() {
        checkStockAvailability(this);
    });
    
    itemIndex++;
    updateItemsVisibility();
}

function removeTransferItem(button) {
    const transferItem = button.closest('.transfer-item');
    transferItem.remove();
    updateItemsVisibility();
}

function updateItemsVisibility() {
    const items = document.querySelectorAll('.transfer-item');
    const noItemsMessage = document.getElementById('noItemsMessage');
    
    if (items.length === 0) {
        noItemsMessage.classList.remove('d-none');
    } else {
        noItemsMessage.classList.add('d-none');
    }
}

function checkStockAvailability(medicationSelect) {
    const fromLocationId = document.querySelector('[name="from_location_id"]').value;
    const medicationId = medicationSelect.value;
    const transferItem = medicationSelect.closest('.transfer-item');
    const stockInfo = transferItem.querySelector('.stock-info');
    const stockDetails = transferItem.querySelector('.stock-details');
    
    if (!fromLocationId || !medicationId) {
        stockInfo.classList.add('d-none');
        return;
    }
    
    // Show loading
    stockDetails.textContent = 'Checking stock availability...';
    stockInfo.classList.remove('d-none');
    
    fetch(`/medications/stock/availability?medication_id=${medicationId}&location_id=${fromLocationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                stockDetails.textContent = `Available: ${data.total_available} units`;
                if (data.total_available <= 0) {
                    stockInfo.className = 'stock-info text-danger small';
                } else if (data.total_available <= 10) {
                    stockInfo.className = 'stock-info text-warning small';
                } else {
                    stockInfo.className = 'stock-info text-success small';
                }
            } else {
                stockDetails.textContent = 'Unable to check stock availability';
                stockInfo.className = 'stock-info text-muted small';
            }
        })
        .catch(error => {
            stockDetails.textContent = 'Error checking stock availability';
            stockInfo.className = 'stock-info text-danger small';
        });
}

// Initialize existing medication selects
document.addEventListener('DOMContentLoaded', function() {
    const medicationSelects = document.querySelectorAll('.medication-select');
    medicationSelects.forEach(select => {
        select.addEventListener('change', function() {
            checkStockAvailability(this);
        });
        
        // Check initial stock if medication is already selected
        if (select.value) {
            checkStockAvailability(select);
        }
    });
    
    // Update visibility on load
    updateItemsVisibility();
    
    // Prevent same location selection
    const fromLocationSelect = document.querySelector('[name="from_location_id"]');
    const toLocationSelect = document.querySelector('[name="to_location_id"]');
    
    function validateLocationSelection() {
        if (fromLocationSelect.value && toLocationSelect.value && 
            fromLocationSelect.value === toLocationSelect.value) {
            toLocationSelect.setCustomValidity('Destination location must be different from source location');
        } else {
            toLocationSelect.setCustomValidity('');
        }
    }
    
    fromLocationSelect.addEventListener('change', function() {
        validateLocationSelection();
        // Re-check stock availability for all items
        document.querySelectorAll('.medication-select').forEach(select => {
            if (select.value) {
                checkStockAvailability(select);
            }
        });
    });
    
    toLocationSelect.addEventListener('change', validateLocationSelection);
});

// Form validation
document.getElementById('transferForm').addEventListener('submit', function(e) {
    const transferItems = document.querySelectorAll('.transfer-item');
    
    if (transferItems.length === 0) {
        e.preventDefault();
        alert('Please add at least one transfer item.');
        return false;
    }
    
    // Additional validation can be added here
});
</script>
@endpush
