@extends('layouts.app_main_layout')

@section('page_title')
    Edit Salary Payment — {{ $salaryPayment->payment_number }}
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-pencil-square"></i> Edit Salary Payment — {{ $salaryPayment->payment_number }}
        </h3>
        <a href="{{ route('hr.salary-payments.show', $salaryPayment) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <input type="text" class="form-control" value="{{ $salaryPayment->employee->name }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Period</label>
                    <input type="text" class="form-control" value="{{ $salaryPayment->period_label }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Basic Salary</label>
                    <input type="text" class="form-control" value="Tsh {{ number_format($salaryPayment->basic_salary, 2) }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('hr.salary-payments.update', $salaryPayment) }}" method="POST">
        @csrf
        @method('PUT')

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
                Edit allowances/deductions, then click "Recalculate PAYE" below to refresh the PAYE line based on the new taxable income.
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $salaryPayment->notes) }}</textarea>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Save Changes
            </button>
            <a href="{{ route('hr.salary-payments.show', $salaryPayment) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <form action="{{ route('hr.salary-payments.recalculate-paye', $salaryPayment) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-warning">
            <i class="bi bi-arrow-repeat"></i> Recalculate PAYE
        </button>
    </form>

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
    @foreach($salaryPayment->items as $item)
        addItemRow({
            type: '{{ $item->type }}',
            name: @json($item->name),
            amount: {{ $item->amount }},
            is_taxable: {{ $item->is_taxable ? 'true' : 'false' }},
            is_pre_tax: {{ $item->is_pre_tax ? 'true' : 'false' }},
            is_statutory: {{ $item->is_statutory ? 'true' : 'false' }},
            source_component_id: {{ $item->source_component_id ?? 'null' }}
        });
    @endforeach
});
</script>
@endsection
