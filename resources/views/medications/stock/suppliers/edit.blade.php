@extends('layouts.app_main_layout')

@section('page_title', 'Edit Supplier - ' . $supplier->name)

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
    
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .section-header {
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 8px;
        margin-bottom: 20px;
        margin-top: 30px;
    }
    
    .section-header:first-child {
        margin-top: 0;
    }
    
    .btn {
        margin-right: 10px;
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    
    .supplier-info {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Supplier: {{ $supplier->name }}</h3>
                </div>
                
                <div class="card-body">
                    <!-- Supplier Info Summary -->
                    <div class="supplier-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Supplier ID:</strong> #{{ $supplier->id }}<br>
                                <strong>Created:</strong> {{ $supplier->created_at->format('M d, Y') }}<br>
                                <strong>Status:</strong> 
                                <span class="badge badge-{{ $supplier->is_active ? 'success' : 'danger' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total GRNs:</strong> {{ $supplier->goodsReceivedNotes->count() }}<br>
                                <strong>Pending Amount:</strong> ${{ number_format($supplier->getPendingAmount(), 2) }}<br>
                                @if($supplier->credit_limit > 0)
                                    <strong>Credit Usage:</strong> 
                                    {{ number_format(($supplier->getPendingAmount() / $supplier->credit_limit) * 100, 1) }}%
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('medications.stock.suppliers.update', $supplier->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <h5 class="section-header">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $supplier->name) }}" 
                                           placeholder="Enter supplier name"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Full legal name of the supplier company</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $supplier->email) }}" 
                                           placeholder="supplier@example.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $supplier->phone) }}" 
                                           placeholder="+1 (555) 123-4567">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-control @error('is_active') is-invalid @enderror" 
                                            id="is_active" 
                                            name="is_active">
                                        <option value="1" {{ old('is_active', $supplier->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $supplier->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($supplier->goodsReceivedNotes->where('status', 'pending')->count() > 0)
                                        <div class="help-text text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            This supplier has pending GRNs. Consider resolving them before deactivating.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <h5 class="section-header">Address Information</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Street Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Enter complete address">{{ old('address', $supplier->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" 
                                           class="form-control @error('city') is-invalid @enderror" 
                                           id="city" 
                                           name="city" 
                                           value="{{ old('city', $supplier->city) }}" 
                                           placeholder="Enter city">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-control @error('country') is-invalid @enderror" 
                                            id="country" 
                                            name="country">
                                        <option value="">Select Country</option>
                                        <option value="United States" {{ old('country', $supplier->country) == 'United States' ? 'selected' : '' }}>United States</option>
                                        <option value="Canada" {{ old('country', $supplier->country) == 'Canada' ? 'selected' : '' }}>Canada</option>
                                        <option value="United Kingdom" {{ old('country', $supplier->country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                        <option value="Australia" {{ old('country', $supplier->country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                        <option value="Germany" {{ old('country', $supplier->country) == 'Germany' ? 'selected' : '' }}>Germany</option>
                                        <option value="France" {{ old('country', $supplier->country) == 'France' ? 'selected' : '' }}>France</option>
                                        <option value="India" {{ old('country', $supplier->country) == 'India' ? 'selected' : '' }}>India</option>
                                        <option value="China" {{ old('country', $supplier->country) == 'China' ? 'selected' : '' }}>China</option>
                                        <option value="Japan" {{ old('country', $supplier->country) == 'Japan' ? 'selected' : '' }}>Japan</option>
                                        <option value="Other" {{ old('country', $supplier->country) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" 
                                           class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" 
                                           name="postal_code" 
                                           value="{{ old('postal_code', $supplier->postal_code) }}" 
                                           placeholder="12345">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Business Information -->
                        <h5 class="section-header">Business Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tax_number" class="form-label">Tax Number</label>
                                    <input type="text" 
                                           class="form-control @error('tax_number') is-invalid @enderror" 
                                           id="tax_number" 
                                           name="tax_number" 
                                           value="{{ old('tax_number', $supplier->tax_number) }}" 
                                           placeholder="Enter tax identification number">
                                    @error('tax_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Tax ID or VAT number</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" 
                                           class="form-control @error('license_number') is-invalid @enderror" 
                                           id="license_number" 
                                           name="license_number" 
                                           value="{{ old('license_number', $supplier->license_number) }}" 
                                           placeholder="Enter business license number">
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <h5 class="section-header">Financial Information</h5>
                        @if($supplier->getPendingAmount() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> This supplier currently has ${{ number_format($supplier->getPendingAmount(), 2) }} in pending payments.
                                @if($supplier->credit_limit > 0)
                                    This represents {{ number_format(($supplier->getPendingAmount() / $supplier->credit_limit) * 100, 1) }}% of their credit limit.
                                @endif
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="credit_limit" class="form-label">Credit Limit</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control @error('credit_limit') is-invalid @enderror" 
                                               id="credit_limit" 
                                               name="credit_limit" 
                                               value="{{ old('credit_limit', $supplier->credit_limit) }}" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0.00">
                                        @error('credit_limit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="help-text">Maximum credit amount allowed</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="credit_days" class="form-label">Credit Days</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('credit_days') is-invalid @enderror" 
                                               id="credit_days" 
                                               name="credit_days" 
                                               value="{{ old('credit_days', $supplier->credit_days) }}" 
                                               min="0"
                                               placeholder="30">
                                        <span class="input-group-text">days</span>
                                        @error('credit_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="help-text">Number of days for payment</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_terms" class="form-label">Payment Terms</label>
                                    <select class="form-control @error('payment_terms') is-invalid @enderror" 
                                            id="payment_terms" 
                                            name="payment_terms">
                                        <option value="">Select Payment Terms</option>
                                        <option value="Cash on Delivery" {{ old('payment_terms', $supplier->payment_terms) == 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                                        <option value="Net 15" {{ old('payment_terms', $supplier->payment_terms) == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                        <option value="Net 30" {{ old('payment_terms', $supplier->payment_terms) == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                        <option value="Net 45" {{ old('payment_terms', $supplier->payment_terms) == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                                        <option value="Net 60" {{ old('payment_terms', $supplier->payment_terms) == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                        <option value="Net 90" {{ old('payment_terms', $supplier->payment_terms) == 'Net 90' ? 'selected' : '' }}>Net 90</option>
                                        <option value="Advance Payment" {{ old('payment_terms', $supplier->payment_terms) == 'Advance Payment' ? 'selected' : '' }}>Advance Payment</option>
                                    </select>
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Supplier
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset Changes
                                </button>
                                <a href="{{ route('medications.stock.suppliers.show', $supplier->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="{{ route('medications.stock.suppliers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>  
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Phone number formatting
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
            $(this).val(value);
        });

        // Form validation
        $('form').on('submit', function(e) {
            const name = $('#name').val().trim();
            if (!name) {
                e.preventDefault();
                alert('Supplier name is required.');
                $('#name').focus();
                return false;
            }

            const creditLimit = parseFloat($('#credit_limit').val()) || 0;
            const creditDays = parseInt($('#credit_days').val()) || 0;
            const pendingAmount = {{ $supplier->getPendingAmount() }};
            
            if (creditLimit > 0 && creditDays === 0) {
                if (!confirm('Credit limit is set but credit days is 0. Continue?')) {
                    e.preventDefault();
                    return false;
                }
            }

            // Check if new credit limit is lower than pending amount
            if (creditLimit > 0 && pendingAmount > creditLimit) {
                if (!confirm(`Warning: The new credit limit ($${creditLimit.toFixed(2)}) is lower than the current pending amount ($${pendingAmount.toFixed(2)}). This will put the supplier over their credit limit. Continue?`)) {
                    e.preventDefault();
                    return false;
                }
            }

            // Check if deactivating supplier with pending amounts
            const isActive = $('#is_active').val() === '1';
            if (!isActive && pendingAmount > 0) {
                if (!confirm(`Warning: You are deactivating a supplier with $${pendingAmount.toFixed(2)} in pending payments. Continue?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Reset form
        $('button[type="reset"]').on('click', function() {
            if (confirm('Are you sure you want to reset all changes?')) {
                // Reset to original values
                location.reload();
            }
        });

        // Credit limit warnings
        $('#credit_limit').on('change', function() {
            const newLimit = parseFloat($(this).val()) || 0;
            const pendingAmount = {{ $supplier->getPendingAmount() }};
            
            if (newLimit > 0 && pendingAmount > newLimit) {
                $(this).addClass('is-invalid');
                if ($(this).siblings('.invalid-feedback').length === 0) {
                    $(this).after('<div class="invalid-feedback">New credit limit is lower than current pending amount ($' + pendingAmount.toFixed(2) + ')</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').remove();
            }
        });
    });
</script>
@endsection
