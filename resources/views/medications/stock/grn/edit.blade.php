@extends('layouts.app_main_layout')

@section('page_title', 'Edit GRN - ' . $grn->grn_number)

@section('styles')
<style>
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .card-header h3 {
        margin: 0;
        color: #495057;
    }
    
    .input-group-text {
        background-color: #e9ecef;
        border-color: #ced4da;
    }
    
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    h5 {
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 8px;
    }
    
    .btn {
        margin-right: 10px;
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    .status-badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .status-draft { background-color: #ffc107; color: #212529; }
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Edit GRN - {{ $grn->grn_number }}
                        <span class="badge status-badge status-{{ $grn->status }} ml-2">
                            {{ ucfirst($grn->status) }}
                        </span>
                    </h3>
                </div>
                
                <div class="card-body">
                    @if($grn->status !== 'draft')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Notice:</strong> Only draft GRNs can be edited. This GRN has status: <strong>{{ ucfirst($grn->status) }}</strong>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> You are editing a draft GRN. Make sure all information is accurate before saving.
                        </div>
                    @endif
                    
                    <form action="{{ route('medications.stock.grn.update', $grn) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="grn_number" class="form-label">GRN Number <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('grn_number') is-invalid @enderror" 
                                           id="grn_number" 
                                           name="grn_number" 
                                           value="{{ old('grn_number', $grn->grn_number) }}" 
                                           placeholder="Auto-generated or enter manually"
                                           required
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('grn_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="grn_date" class="form-label">GRN Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('grn_date') is-invalid @enderror" 
                                           id="grn_date" 
                                           name="grn_date" 
                                           value="{{ old('grn_date', $grn->grn_date->format('Y-m-d')) }}" 
                                           required
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('grn_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Supplier Information -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('supplier_id') is-invalid @enderror" 
                                            id="supplier_id" 
                                            name="supplier_id" 
                                            required
                                            {{ $grn->status !== 'draft' ? 'disabled' : '' }}>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                {{ old('supplier_id', $grn->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }} (#{{ $supplier->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" 
                                           class="form-control @error('invoice_number') is-invalid @enderror" 
                                           id="invoice_number" 
                                           name="invoice_number" 
                                           value="{{ old('invoice_number', $grn->invoice_number) }}" 
                                           placeholder="Enter supplier invoice number"
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('invoice_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" 
                                           class="form-control @error('invoice_date') is-invalid @enderror" 
                                           id="invoice_date" 
                                           name="invoice_date" 
                                           value="{{ old('invoice_date', $grn->invoice_date ? $grn->invoice_date->format('Y-m-d') : '') }}"
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('invoice_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="delivery_note_number" class="form-label">Delivery Note Number</label>
                                    <input type="text" 
                                           class="form-control @error('delivery_note_number') is-invalid @enderror" 
                                           id="delivery_note_number" 
                                           name="delivery_note_number" 
                                           value="{{ old('delivery_note_number', $grn->delivery_note_number) }}" 
                                           placeholder="Enter delivery note number"
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('delivery_note_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="delivery_date" class="form-label">Delivery Date</label>
                                    <input type="date" 
                                           class="form-control @error('delivery_date') is-invalid @enderror" 
                                           id="delivery_date" 
                                           name="delivery_date" 
                                           value="{{ old('delivery_date', $grn->delivery_date ? $grn->delivery_date->format('Y-m-d') : '') }}"
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required
                                            {{ $grn->status !== 'draft' ? 'disabled' : '' }}>
                                        <option value="">Select Status</option>
                                        <option value="draft" {{ old('status', $grn->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="received" {{ old('status', $grn->status) == 'received' ? 'selected' : '' }}>Received</option>
                                        <option value="verified" {{ old('status', $grn->status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="posted" {{ old('status', $grn->status) == 'posted' ? 'selected' : '' }}>Posted</option>
                                        <option value="cancelled" {{ old('status', $grn->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <h5 class="mt-4 mb-3">Financial Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="total_amount" class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control @error('total_amount') is-invalid @enderror" 
                                               id="total_amount" 
                                               name="total_amount" 
                                               value="{{ old('total_amount', $grn->total_amount) }}" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0.00"
                                               {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                        @error('total_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="discount_amount" class="form-label">Discount Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control @error('discount_amount') is-invalid @enderror" 
                                               id="discount_amount" 
                                               name="discount_amount" 
                                               value="{{ old('discount_amount', $grn->discount_amount) }}" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0.00"
                                               {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                        @error('discount_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tax_amount" class="form-label">Tax Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control @error('tax_amount') is-invalid @enderror" 
                                               id="tax_amount" 
                                               name="tax_amount" 
                                               value="{{ old('tax_amount', $grn->tax_amount) }}" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0.00"
                                               {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                        @error('tax_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="net_amount" class="form-label">Net Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control @error('net_amount') is-invalid @enderror" 
                                               id="net_amount" 
                                               name="net_amount" 
                                               value="{{ old('net_amount', $grn->net_amount) }}" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0.00"
                                               readonly>
                                        @error('net_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personnel Information -->
                        <h5 class="mt-4 mb-3">Personnel Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="received_by" class="form-label">Received By</label>
                                    <select class="form-control select2 @error('received_by') is-invalid @enderror" 
                                            id="received_by" 
                                            name="received_by"
                                            {{ $grn->status !== 'draft' ? 'disabled' : '' }}>
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                {{ old('received_by', $grn->received_by) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('received_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="received_at" class="form-label">Received At</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('received_at') is-invalid @enderror" 
                                           id="received_at" 
                                           name="received_at" 
                                           value="{{ old('received_at', $grn->received_at ? $grn->received_at->format('Y-m-d\TH:i') : '') }}"
                                           {{ $grn->status !== 'draft' ? 'readonly' : '' }}>
                                    @error('received_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Enter any additional notes or comments"
                                              {{ $grn->status !== 'draft' ? 'readonly' : '' }}>{{ old('notes', $grn->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                @if($grn->status === 'draft')
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update GRN
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                @endif
                                <a href="{{ route('medications.stock.grn.show', $grn) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> View GRN
                                </a>
                                <a href="{{ route('medications.stock.grn.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </form>
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
                </div>
            </div>  
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Only initialize if editing is allowed
        @if($grn->status === 'draft')
            // Initialize Select2
            $('#supplier_id').select2({
                placeholder: 'Select Supplier',
                allowClear: true
            });
            
            $('#received_by').select2({
                placeholder: 'Select User',
                allowClear: true
            });

            // Auto-calculate net amount
            function calculateNetAmount() {
                const totalAmount = parseFloat($('#total_amount').val()) || 0;
                const discountAmount = parseFloat($('#discount_amount').val()) || 0;
                const taxAmount = parseFloat($('#tax_amount').val()) || 0;
                
                const netAmount = totalAmount - discountAmount + taxAmount;
                $('#net_amount').val(netAmount.toFixed(2));
            }

            // Bind calculation to amount fields
            $('#total_amount, #discount_amount, #tax_amount').on('input', calculateNetAmount);

            // Auto-populate received_at when received_by is selected
            $('#received_by').on('change', function() {
                if ($(this).val() && !$('#received_at').val()) {
                    const now = new Date();
                    const formattedDateTime = now.toISOString().slice(0, 16);
                    $('#received_at').val(formattedDateTime);
                }
            });

            // Status-based field management
            $('#status').on('change', function() {
                const status = $(this).val();
                
                // Clear fields based on status
                if (status === 'draft') {
                    $('#received_by').val('').trigger('change');
                    $('#received_at').val('');
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                const status = $('#status').val();
                const receivedBy = $('#received_by').val();

                // Validate status-dependent fields
                if (status === 'received' && !receivedBy) {
                    e.preventDefault();
                    alert('Please select who received the goods when status is "Received".');
                    return false;
                }
            });
        @endif
    }); 
</script>
@endsection
