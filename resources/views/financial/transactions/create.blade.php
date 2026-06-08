@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Create Financial Transaction' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Create New Financial Transaction
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('financial.transactions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Transactions
                        </a>
                    </div>
                </div>
                <form action="{{ route('financial.transactions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Transaction Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_type">Transaction Type <span class="text-danger">*</span></label>
                                    <select name="transaction_type" id="transaction_type" class="form-control @error('transaction_type') is-invalid @enderror" required>
                                        <option value="">Select Transaction Type</option>
                                        <option value="income" {{ old('transaction_type') == 'income' ? 'selected' : '' }}>Income</option>
                                        <option value="expense" {{ old('transaction_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                                    </select>
                                    @error('transaction_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Transaction Date -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_date">Transaction Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="transaction_date" id="transaction_date" 
                                           class="form-control @error('transaction_date') is-invalid @enderror" 
                                           value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        <option value="consultation_fee" {{ old('category') == 'consultation_fee' ? 'selected' : '' }}>Consultation Fee</option>
                                        <option value="investigation_fee" {{ old('category') == 'investigation_fee' ? 'selected' : '' }}>Investigation Fee</option>
                                        <option value="medication_sale" {{ old('category') == 'medication_sale' ? 'selected' : '' }}>Medication Sale</option>
                                        <option value="supplier_payment" {{ old('category') == 'supplier_payment' ? 'selected' : '' }}>Supplier Payment</option>
                                        <option value="general_expense" {{ old('category') == 'general_expense' ? 'selected' : '' }}>General Expense</option>
                                        <option value="utilities" {{ old('category') == 'utilities' ? 'selected' : '' }}>Utilities</option>
                                        <option value="staff_payment" {{ old('category') == 'staff_payment' ? 'selected' : '' }}>Staff Payment</option>
                                        <option value="equipment" {{ old('category') == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Subcategory -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subcategory">Subcategory</label>
                                    <input type="text" name="subcategory" id="subcategory" 
                                           class="form-control @error('subcategory') is-invalid @enderror" 
                                           value="{{ old('subcategory') }}" placeholder="Enter subcategory (optional)">
                                    @error('subcategory')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Amount -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Tsh</span>
                                        </div>
                                        <input type="number" name="amount" id="amount" 
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               value="{{ old('amount') }}" step="0.01" min="0" required
                                               placeholder="0.00">
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="insurance" {{ old('payment_method') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Payment Reference -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_reference">Payment Reference</label>
                                    <input type="text" name="payment_reference" id="payment_reference" 
                                           class="form-control @error('payment_reference') is-invalid @enderror" 
                                           value="{{ old('payment_reference') }}" 
                                           placeholder="Reference number, check number, etc.">
                                    @error('payment_reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Source Type -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="source_type">Source Type</label>
                                    <select name="source_type" id="source_type" class="form-control @error('source_type') is-invalid @enderror">
                                        <option value="general_expense" {{ old('source_type') == 'general_expense' ? 'selected' : '' }}>General Expense</option>
                                        <option value="consultation" {{ old('source_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                        <option value="investigation" {{ old('source_type') == 'investigation' ? 'selected' : '' }}>Investigation</option>
                                        <option value="medication" {{ old('source_type') == 'medication' ? 'selected' : '' }}>Medication</option>
                                        <option value="grn_purchase" {{ old('source_type') == 'grn_purchase' ? 'selected' : '' }}>GRN Purchase</option>
                                    </select>
                                    @error('source_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror" 
                                      required placeholder="Enter detailed description of the transaction">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes">Additional Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Any additional notes or comments (optional)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Pending transactions require approval before being included in financial reports.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Transaction
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2">
                                    <i class="fas fa-undo"></i> Reset Form
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('financial.transactions.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_footer_content')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update subcategory based on category selection
    const categorySelect = document.getElementById('category');
    const subcategoryInput = document.getElementById('subcategory');
    
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        
        // Suggest subcategories based on main category
        const suggestions = {
            'consultation_fee': 'Standard consultation',
            'investigation_fee': 'Laboratory test',
            'medication_sale': 'Prescription medication',
            'supplier_payment': 'Inventory purchase',
            'general_expense': 'Office supplies',
            'utilities': 'Electricity/Water',
            'staff_payment': 'Salary/Allowance',
            'equipment': 'Medical equipment'
        };
        
        if (suggestions[category]) {
            subcategoryInput.placeholder = `e.g., ${suggestions[category]}`;
        }
    });
    
    // Format amount input
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('input', function() {
        let value = this.value;
        if (value < 0) {
            this.value = 0;
        }
    });
});
</script>
@endsection
