@extends('layouts.app_main_layout')

@section('page_title')
    New Salary Payment
@endsection

@section('main_content')
<div class="container-fluid">

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-person-check"></i> Select Employee</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('hr.salary-payments.create') }}" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <select name="employee_id" class="form-select select2" onchange="this.form.submit()">
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $selectedEmployee?->id == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_number }} — {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedEmployee)
        <form action="{{ route('hr.salary-payments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-info-circle"></i> Payment Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                <input type="text" class="form-control" value="{{ $selectedEmployee->name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="text" class="form-control" value="Tsh {{ number_format($selectedEmployee->basic_salary, 2) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="pay_period_year" class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="number" name="pay_period_year" id="pay_period_year" class="form-control"
                                       value="{{ old('pay_period_year', now()->year) }}" min="2000" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="pay_period_month" class="form-label">Month <span class="text-danger">*</span></label>
                                <select name="pay_period_month" id="pay_period_month" class="form-select" required>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ old('pay_period_month', now()->month) == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-list-ul"></i> Allowances &amp; Deductions</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                            <i class="bi bi-plus-circle"></i> Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Type</th>
                                <th>Name</th>
                                <th style="width: 160px;">Amount (Tsh)</th>
                                <th style="width: 90px;">Taxable</th>
                                <th style="width: 90px;">Pre-tax</th>
                                <th style="width: 90px;">Statutory</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="card-footer text-muted">
                    PAYE will be calculated automatically based on the taxable income (basic salary + taxable allowances - pre-tax deductions) once this payment is created.
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Salary Payment
                </button>
                <a href="{{ route('hr.salary-payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    @endif

</div>
@endsection

@section('scripts')
<script>
let itemIndex = 0;

function addItemRow(item) {
    item = item || {};
    const index = itemIndex++;
    const row = $(`
        <tr>
            <td>
                <select name="items[${index}][type]" class="form-select form-select-sm">
                    <option value="allowance">Allowance</option>
                    <option value="deduction">Deduction</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${index}][name]" class="form-control form-control-sm" maxlength="100" required>
            </td>
            <td>
                <input type="number" name="items[${index}][amount]" class="form-control form-control-sm" step="0.01" min="0" required>
            </td>
            <td class="text-center">
                <input type="checkbox" name="items[${index}][is_taxable]" class="form-check-input" value="1">
            </td>
            <td class="text-center">
                <input type="checkbox" name="items[${index}][is_pre_tax]" class="form-check-input" value="1">
            </td>
            <td class="text-center">
                <input type="checkbox" name="items[${index}][is_statutory]" class="form-check-input" value="1">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `);

    row.find('select[name$="[type]"]').val(item.type || 'allowance');
    row.find('input[name$="[name]"]').val(item.name || '');
    row.find('input[name$="[amount]"]').val(item.amount !== undefined ? item.amount : '');
    row.find('input[name$="[is_taxable]"]').prop('checked', !!item.is_taxable);
    row.find('input[name$="[is_pre_tax]"]').prop('checked', !!item.is_pre_tax);
    row.find('input[name$="[is_statutory]"]').prop('checked', !!item.is_statutory);

    if (item.source_component_id) {
        row.append(`<input type="hidden" name="items[${index}][source_component_id]" value="${item.source_component_id}">`);
    }

    $('#itemsTable tbody').append(row);
}

$(document).ready(function () {
    $('.select2').select2({ width: '100%' });

    @if($selectedEmployee)
        @foreach($selectedEmployee->salaryComponents->where('is_active', true)->sortBy('sort_order') as $component)
            addItemRow({
                type: '{{ $component->type }}',
                name: '{{ $component->name }}',
                amount: {{ $component->resolveAmount((float) $selectedEmployee->basic_salary) }},
                is_taxable: {{ $component->is_taxable ? 'true' : 'false' }},
                is_pre_tax: {{ $component->is_pre_tax ? 'true' : 'false' }},
                is_statutory: {{ $component->is_statutory ? 'true' : 'false' }},
                source_component_id: {{ $component->id }}
            });
        @endforeach
    @endif
});
</script>
@endsection
