@extends('layouts.app_main_layout')

@section('page_title', 'Create Stock Adjustment')

@section('main_content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Stock Adjustment</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('medications.index') }}">Medications</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('medications.stock.adjustments.index') }}">Stock Adjustments</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Alert Messages -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6><i class="icon fas fa-ban"></i> Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Main Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-balance-scale"></i> Stock Adjustment Details
                    </h3>
                </div>
                
                <form action="{{ route('medications.stock.adjustments.store') }}" method="POST" id="adjustmentForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Location Selection -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_id">Store Location <span class="text-danger">*</span></label>
                                    <select name="location_id" id="location_id" class="form-control select2" required>
                                        <option value="">Select Store Location</option>
                                        @foreach($storeLocations as $location)
                                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                                @if($location->description)
                                                    - {{ $location->description }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Medication Selection -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="medication_id">Medication <span class="text-danger">*</span></label>
                                    <select name="medication_id" id="medication_id" class="form-control select2" required>
                                        <option value="">Select Medication</option>
                                        @foreach($medications as $medication)
                                            <option value="{{ $medication->id }}" {{ old('medication_id') == $medication->id ? 'selected' : '' }}
                                                data-strength="{{ $medication->strength }}"
                                                data-form="{{ $medication->form }}">
                                                {{ $medication->name }}
                                                @if($medication->strength)
                                                    - {{ $medication->strength }}
                                                @endif
                                                @if($medication->form)
                                                    ({{ $medication->form }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medication_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Stock Display -->
                        <div class="row" id="current-stock-display" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Current Stock Information</h6>
                                    <div id="stock-info">
                                        <!-- Stock info will be loaded here via AJAX -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Adjustment Type -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="adjustment_type">Adjustment Type <span class="text-danger">*</span></label>
                                    <select name="adjustment_type" id="adjustment_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="increase" {{ old('adjustment_type') == 'increase' ? 'selected' : '' }}>
                                            <i class="fas fa-arrow-up"></i> Increase Stock
                                        </option>
                                        <option value="decrease" {{ old('adjustment_type') == 'decrease' ? 'selected' : '' }}>
                                            <i class="fas fa-arrow-down"></i> Decrease Stock
                                        </option>
                                    </select>
                                    @error('adjustment_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                    <input type="text" name="quantity" id="quantity" class="form-control" 
                                           value="{{ old('quantity') }}" step="0.01" min="0.01" required
                                           placeholder="Enter quantity">
                                    @error('quantity')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Unit Cost -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="unit_cost">Unit Cost</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="text" name="unit_cost" id="unit_cost" class="form-control" 
                                               value="{{ old('unit_cost') }}" step="0.01" min="0"
                                               placeholder="0.00">
                                    </div>
                                    @error('unit_cost')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Reason -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reason">Reason <span class="text-danger">*</span></label>
                                    <select name="reason" id="reason" class="form-control" required>
                                        <option value="">Select Reason</option>
                                        @foreach($adjustmentReasons as $key => $value)
                                            <option value="{{ $key }}" {{ old('reason') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('reason')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Batch Number -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_number">Batch Number</label>
                                    <input type="text" name="batch_number" id="batch_number" class="form-control" 
                                           value="{{ old('batch_number') }}" maxlength="50"
                                           placeholder="Optional batch number">
                                    @error('batch_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              maxlength="500" placeholder="Additional notes or comments...">{{ old('notes') }}</textarea>
                                    <small class="form-text text-muted">
                                        <span id="notes-counter">0</span>/500 characters
                                    </small>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Stock Warning -->
                        <div class="row" id="stock-warning" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Stock Warning</h6>
                                    <p id="warning-message"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('medications.stock.adjustments.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> Process Adjustment
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Notes character counter
    $('#notes').on('input', function() {
        var length = $(this).val().length;
        $('#notes-counter').text(length);
        
        if (length > 450) {
            $('#notes-counter').addClass('text-warning');
        } else {
            $('#notes-counter').removeClass('text-warning');
        }
    });

    // Load current stock when both location and medication are selected
    function loadCurrentStock() {
        var locationId = $('#location_id').val();
        var medicationId = $('#medication_id').val();
        
        if (locationId && medicationId) {
            $.ajax({
                url: '{{ route("api.stock.current") }}',
                method: 'GET',
                data: {
                    location_id: locationId,
                    medication_id: medicationId
                },
                success: function(response) {
                    if (response.success) {
                        var stock = response.data;
                        var stockInfo = '<div class="row">' +
                            '<div class="col-md-3"><strong>Current Quantity:</strong> ' + (stock.quantity_current || 0) + '</div>' +
                            '<div class="col-md-3"><strong>Reorder Level:</strong> ' + (stock.reorder_level || 'Not set') + '</div>' +
                            '<div class="col-md-3"><strong>Max Level:</strong> ' + (stock.max_level || 'Not set') + '</div>' +
                            '<div class="col-md-3"><strong>Last Updated:</strong> ' + (stock.updated_at || 'Never') + '</div>' +
                            '</div>';
                        
                        $('#stock-info').html(stockInfo);
                        $('#current-stock-display').show();
                        
                        // Store current quantity for validation
                        $('#current-stock-display').data('current-quantity', stock.quantity_current || 0);
                    } else {
                        $('#stock-info').html('<p class="text-muted">No stock information available</p>');
                        $('#current-stock-display').show();
                        $('#current-stock-display').data('current-quantity', 0);
                    }
                },
                error: function() {
                    $('#stock-info').html('<p class="text-danger">Error loading stock information</p>');
                    $('#current-stock-display').show();
                    $('#current-stock-display').data('current-quantity', 0);
                }
            });
        } else {
            $('#current-stock-display').hide();
        }
    }

    // Load stock when selection changes
    $('#location_id, #medication_id').change(function() {
        loadCurrentStock();
        validateStockAdjustment();
    });

    // Validate stock adjustment
    function validateStockAdjustment() {
        var adjustmentType = $('#adjustment_type').val();
        var quantity = parseFloat($('#quantity').val()) || 0;
        var currentQuantity = parseFloat($('#current-stock-display').data('current-quantity')) || 0;
        
        if (adjustmentType === 'decrease' && quantity > currentQuantity) {
            var shortfall = quantity - currentQuantity;
            $('#warning-message').html('Warning: Attempting to decrease stock by ' + quantity + 
                ' but only ' + currentQuantity + ' units available. Shortfall: ' + shortfall + ' units.');
            $('#stock-warning').show();
            $('#submit-btn').addClass('btn-warning').removeClass('btn-primary');
        } else {
            $('#stock-warning').hide();
            $('#submit-btn').addClass('btn-primary').removeClass('btn-warning');
        }
    }

    // Validate on input change
    $('#adjustment_type, #quantity').on('change input', function() {
        validateStockAdjustment();
    });

    // Form submission
    $('#adjustmentForm').submit(function(e) {
        var adjustmentType = $('#adjustment_type').val();
        var quantity = parseFloat($('#quantity').val()) || 0;
        var currentQuantity = parseFloat($('#current-stock-display').data('current-quantity')) || 0;
        
        if (adjustmentType === 'decrease' && quantity > currentQuantity) {
            e.preventDefault();
            
            if (!confirm('This adjustment will result in negative stock. Are you sure you want to continue?')) {
                return false;
            }
        }
        
        // Disable submit button to prevent double submission
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    });

    // Initialize character counter
    $('#notes').trigger('input');
});
</script>
@endsection
