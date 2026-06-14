@extends('layouts.app_main_layout')

@section('page_title')
    {{ $employee->name }}
@endsection

@section('main_content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-person-badge"></i> {{ $employee->name }}</h3>
                    <div class="card-tools">
                        <span class="badge {{ $employee->status_badge }}">{{ ucfirst($employee->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td><strong>Employee #</strong></td><td>{{ $employee->employee_number }}</td></tr>
                        <tr><td><strong>Job Title</strong></td><td>{{ $employee->job_title ?: '—' }}</td></tr>
                        <tr><td><strong>Department</strong></td><td>{{ $employee->department ?: '—' }}</td></tr>
                        <tr><td><strong>Employment Type</strong></td><td>{{ ucfirst($employee->employment_type) }}</td></tr>
                        <tr><td><strong>Date Joined</strong></td><td>{{ $employee->date_joined?->format('d M Y') ?: '—' }}</td></tr>
                        <tr><td><strong>Basic Salary</strong></td><td>Tsh {{ number_format($employee->basic_salary, 2) }}</td></tr>
                        <tr><td><strong>Payment Method</strong></td><td>{{ ucwords(str_replace('_', ' ', $employee->payment_method)) }}</td></tr>
                        <tr><td><strong>Phone</strong></td><td>{{ $employee->phone ?: '—' }}</td></tr>
                        <tr><td><strong>Email</strong></td><td>{{ $employee->email ?: '—' }}</td></tr>
                        <tr><td><strong>Linked Account</strong></td><td>{{ $employee->user?->name ?? '—' }}</td></tr>
                        <tr><td><strong>Bank</strong></td><td>{{ $employee->bank_name ?: '—' }} {{ $employee->bank_account_number ? '(' . $employee->bank_account_number . ')' : '' }}</td></tr>
                        <tr><td><strong>TIN</strong></td><td>{{ $employee->tin_number ?: '—' }}</td></tr>
                        <tr><td><strong>NSSF</strong></td><td>{{ $employee->nssf_number ?: '—' }}</td></tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('hr.salary-payments.create', ['employee_id' => $employee->id]) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-cash-coin"></i> New Salary Payment
                    </a>
                    <form action="{{ route('hr.employees.toggle-status', $employee) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-repeat"></i> {{ $employee->status === 'active' ? 'Set Inactive' : 'Set Active' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Recurring Salary Components -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-list-ul"></i> Recurring Allowances / Deductions</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#componentModal" onclick="resetComponentModal()">
                            <i class="bi bi-plus-circle"></i> Add Component
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Calculation</th>
                                <th>Flags</th>
                                <th>Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->salaryComponents->sortBy('sort_order') as $component)
                                <tr>
                                    <td>
                                        <span class="badge {{ $component->type === 'allowance' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($component->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $component->name }}</td>
                                    <td>
                                        @if($component->calculation_type === 'fixed')
                                            Tsh {{ number_format($component->amount, 2) }} (fixed)
                                        @else
                                            {{ number_format($component->percentage, 2) }}% of basic
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->is_taxable)<span class="badge bg-secondary">Taxable</span>@endif
                                        @if($component->is_pre_tax)<span class="badge bg-secondary">Pre-tax</span>@endif
                                        @if($component->is_statutory)<span class="badge bg-secondary">Statutory</span>@endif
                                    </td>
                                    <td>{{ $component->is_active ? 'Yes' : 'No' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal" data-bs-target="#componentModal"
                                                onclick='openComponentModal(@json($component))'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('hr.salary-components.destroy', $component) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Remove this salary component?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No recurring components defined.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Payment History -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clock-history"></i> Salary Payment History</h3>
        </div>
        <div class="card-body p-0">
            <table id="salaryHistoryTable" class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Period</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->salaryPayments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->period_label }}</td>
                            <td>Tsh {{ number_format($payment->net_salary, 2) }}</td>
                            <td><span class="badge {{ $payment->status_badge }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>{{ $payment->payment_date?->format('d M Y') ?: '—' }}</td>
                            <td>
                                <a href="{{ route('hr.salary-payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Salary Component Modal -->
<div class="modal fade" id="componentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="componentForm" method="POST" action="{{ route('hr.employees.salary-components.store', $employee) }}">
                @csrf
                <input type="hidden" name="_method" id="component_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="componentModalLabel">Add Salary Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="component_type" class="form-select" required>
                            <option value="allowance">Allowance</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="component_name" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Calculation</label>
                        <select name="calculation_type" id="component_calculation_type" class="form-select" onchange="toggleComponentAmountFields()" required>
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage_of_basic">Percentage of Basic Salary</option>
                        </select>
                    </div>
                    <div class="mb-3" id="component_amount_group">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Tsh</span>
                            <input type="number" name="amount" id="component_amount" step="0.01" min="0" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="component_percentage_group">
                        <label class="form-label">Percentage of Basic Salary</label>
                        <div class="input-group">
                            <input type="number" name="percentage" id="component_percentage" step="0.01" min="0" max="100" class="form-control">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="component_sort_order" min="0" class="form-control" value="0">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_taxable" id="component_is_taxable" value="1" checked>
                        <label class="form-check-label" for="component_is_taxable">Taxable (counts toward PAYE income, for allowances)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_pre_tax" id="component_is_pre_tax" value="1">
                        <label class="form-check-label" for="component_is_pre_tax">Pre-tax (reduces PAYE income, for deductions)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_statutory" id="component_is_statutory" value="1">
                        <label class="form-check-label" for="component_is_statutory">Statutory (e.g. NSSF/PSSSF)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="component_is_active" value="1" checked>
                        <label class="form-check-label" for="component_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Component</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleComponentAmountFields() {
    const isPercentage = $('#component_calculation_type').val() === 'percentage_of_basic';
    $('#component_amount_group').toggleClass('d-none', isPercentage);
    $('#component_percentage_group').toggleClass('d-none', !isPercentage);
}

function resetComponentModal() {
    $('#componentModalLabel').text('Add Salary Component');
    $('#componentForm').attr('action', '{{ route("hr.employees.salary-components.store", $employee) }}');
    $('#component_method').val('POST');
    $('#component_type').val('allowance');
    $('#component_name').val('');
    $('#component_calculation_type').val('fixed');
    $('#component_amount').val('');
    $('#component_percentage').val('');
    $('#component_sort_order').val(0);
    $('#component_is_taxable').prop('checked', true);
    $('#component_is_pre_tax').prop('checked', false);
    $('#component_is_statutory').prop('checked', false);
    $('#component_is_active').prop('checked', true);
    toggleComponentAmountFields();
}

function openComponentModal(component) {
    $('#componentModalLabel').text('Edit Salary Component');
    $('#componentForm').attr('action', '/hr/salary-components/' + component.id);
    $('#component_method').val('PUT');
    $('#component_type').val(component.type);
    $('#component_name').val(component.name);
    $('#component_calculation_type').val(component.calculation_type);
    $('#component_amount').val(component.amount);
    $('#component_percentage').val(component.percentage);
    $('#component_sort_order').val(component.sort_order);
    $('#component_is_taxable').prop('checked', !!component.is_taxable);
    $('#component_is_pre_tax').prop('checked', !!component.is_pre_tax);
    $('#component_is_statutory').prop('checked', !!component.is_statutory);
    $('#component_is_active').prop('checked', !!component.is_active);
    toggleComponentAmountFields();
}

$(document).ready(function () {
    $('#salaryHistoryTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [-1] }
        ]
    });

    toggleComponentAmountFields();
});
</script>
@endsection
