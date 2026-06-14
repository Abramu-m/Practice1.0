@extends('layouts.app_main_layout')

@section('page_title')
    Salary Payment — {{ $salaryPayment->payment_number }}
@endsection

@section('main_content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-cash-coin"></i> {{ $salaryPayment->payment_number }}
            <span class="badge {{ $salaryPayment->status_badge }}">{{ ucfirst($salaryPayment->status) }}</span>
        </h3>
        <div>
            <a href="{{ route('hr.salary-payments.payslip', $salaryPayment) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-printer"></i> Payslip
            </a>
            <a href="{{ route('hr.salary-payments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-person"></i> Employee</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td><strong>Name</strong></td><td>{{ $salaryPayment->employee->name }}</td></tr>
                        <tr><td><strong>Employee #</strong></td><td>{{ $salaryPayment->employee->employee_number }}</td></tr>
                        <tr><td><strong>Job Title</strong></td><td>{{ $salaryPayment->employee->job_title ?: '—' }}</td></tr>
                        <tr><td><strong>Department</strong></td><td>{{ $salaryPayment->employee->department ?: '—' }}</td></tr>
                        <tr><td><strong>Period</strong></td><td>{{ $salaryPayment->period_label }}</td></tr>
                    </table>
                    <a href="{{ route('hr.employees.show', $salaryPayment->employee) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View Employee
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-clock-history"></i> Workflow</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td><strong>Created</strong></td><td>{{ $salaryPayment->created_at?->format('d M Y H:i') }} by {{ $salaryPayment->creator?->name ?? '—' }}</td></tr>
                        @if($salaryPayment->approved_at)
                            <tr><td><strong>Approved</strong></td><td>{{ $salaryPayment->approved_at->format('d M Y H:i') }} by {{ $salaryPayment->approver?->name ?? '—' }}</td></tr>
                        @endif
                        @if($salaryPayment->paid_at)
                            <tr><td><strong>Paid</strong></td><td>{{ $salaryPayment->paid_at->format('d M Y H:i') }} by {{ $salaryPayment->payer?->name ?? '—' }}</td></tr>
                            <tr><td><strong>Payment Date</strong></td><td>{{ $salaryPayment->payment_date?->format('d M Y') }}</td></tr>
                            <tr><td><strong>Payment Method</strong></td><td>{{ ucwords(str_replace('_', ' ', $salaryPayment->payment_method)) }}</td></tr>
                            <tr><td><strong>Payment Reference</strong></td><td>{{ $salaryPayment->payment_reference ?: '—' }}</td></tr>
                        @endif
                        @if($salaryPayment->financialTransaction)
                            <tr>
                                <td><strong>Financial Transaction</strong></td>
                                <td>
                                    <a href="{{ route('financial.transactions.show', $salaryPayment->financialTransaction) }}">
                                        {{ $salaryPayment->financialTransaction->transaction_number }}
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($salaryPayment->cancelled_at)
                            <tr><td><strong>Cancelled</strong></td><td>{{ $salaryPayment->cancelled_at->format('d M Y H:i') }} by {{ $salaryPayment->canceller?->name ?? '—' }}</td></tr>
                            <tr><td><strong>Reason</strong></td><td>{{ $salaryPayment->cancellation_reason }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-list-ul"></i> Payslip Breakdown</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount (Tsh)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">Basic</span></td>
                                <td>Basic Salary</td>
                                <td class="text-end">{{ number_format($salaryPayment->basic_salary, 2) }}</td>
                            </tr>
                            @foreach($salaryPayment->items->where('type', 'allowance') as $item)
                                <tr>
                                    <td><span class="badge bg-success">Allowance</span></td>
                                    <td>
                                        {{ $item->name }}
                                        @if($item->is_statutory)<span class="badge bg-secondary">Statutory</span>@endif
                                    </td>
                                    <td class="text-end">+ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            @foreach($salaryPayment->items->where('type', 'deduction') as $item)
                                <tr>
                                    <td><span class="badge bg-danger">Deduction</span></td>
                                    <td>
                                        {{ $item->name }}
                                        @if($item->is_statutory)<span class="badge bg-secondary">Statutory</span>@endif
                                    </td>
                                    <td class="text-end">- {{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total Allowances</th>
                                <th class="text-end">{{ number_format($salaryPayment->total_allowances, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-end">Total Deductions</th>
                                <th class="text-end">{{ number_format($salaryPayment->total_deductions, 2) }}</th>
                            </tr>
                            <tr class="table-active">
                                <th colspan="2" class="text-end">Net Salary</th>
                                <th class="text-end">Tsh {{ number_format($salaryPayment->net_salary, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($salaryPayment->notes)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-sticky"></i> Notes</h3>
                    </div>
                    <div class="card-body">
                        {{ $salaryPayment->notes }}
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-gear"></i> Actions</h3>
                </div>
                <div class="card-body">
                    @if($salaryPayment->status === 'draft')
                        <a href="{{ route('hr.salary-payments.edit', $salaryPayment) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('hr.salary-payments.approve', $salaryPayment) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Approve this salary payment?')">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                        </form>
                    @endif

                    @if($salaryPayment->status === 'approved')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                            <i class="bi bi-cash"></i> Mark as Paid
                        </button>
                    @endif

                    @if(in_array($salaryPayment->status, ['draft', 'approved', 'paid']))
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hr.salary-payments.pay', $salaryPayment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark Salary Payment as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>This will record an expense of <strong>Tsh {{ number_format($salaryPayment->net_salary, 2) }}</strong> in Financial Management.</p>
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money', 'cheque' => 'Cheque'] as $value => $label)
                                <option value="{{ $value }}" {{ $salaryPayment->payment_method == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Reference</label>
                        <input type="text" name="payment_reference" class="form-control" maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hr.salary-payments.cancel', $salaryPayment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Salary Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($salaryPayment->status === 'paid')
                        <p class="text-danger">This payment has already been paid. Cancelling will also cancel the linked financial transaction.</p>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
