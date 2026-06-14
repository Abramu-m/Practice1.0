<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalaryPaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SalaryPayment::with('employee')->orderByDesc('id');

            if ($request->filled('pay_period_year')) {
                $query->where('pay_period_year', $request->pay_period_year);
            }

            if ($request->filled('pay_period_month')) {
                $query->where('pay_period_month', $request->pay_period_month);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            return DataTables::of($query)
                ->addColumn('employee_name', fn ($payment) => e($payment->employee->name))
                ->addColumn('period', fn ($payment) => $payment->period_label)
                ->addColumn('net_salary_display', fn ($payment) => number_format($payment->net_salary, 2))
                ->addColumn('status_badge', fn ($payment) => '<span class="badge ' . $payment->status_badge . '">' . ucfirst($payment->status) . '</span>')
                ->addColumn('actions', function ($payment) {
                    return '<a href="' . route('hr.salary-payments.show', $payment) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $employees = Employee::active()->orderBy('first_name')->get();

        return view('hr.salary_payments.index', compact('employees'));
    }

    public function create(Request $request)
    {
        $employees = Employee::active()->orderBy('first_name')->get();

        $selectedEmployee = null;

        if ($request->filled('employee_id')) {
            $selectedEmployee = Employee::with('salaryComponents')->findOrFail($request->employee_id);
        }

        return view('hr.salary_payments.create', compact('employees', 'selectedEmployee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'pay_period_year' => ['required', 'integer', 'min:2000'],
            'pay_period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.type' => ['required_with:items', 'in:allowance,deduction'],
            'items.*.name' => ['required_with:items', 'string', 'max:100'],
            'items.*.amount' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $exists = SalaryPayment::where('employee_id', $employee->id)
            ->forPeriod($validated['pay_period_year'], $validated['pay_period_month'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'employee_id' => 'A salary payment already exists for this employee and period.',
            ]);
        }

        $payment = DB::transaction(function () use ($employee, $validated, $request) {
            $payment = SalaryPayment::create([
                'employee_id' => $employee->id,
                'pay_period_year' => $validated['pay_period_year'],
                'pay_period_month' => $validated['pay_period_month'],
                'basic_salary' => $employee->basic_salary,
                'net_salary' => $employee->basic_salary,
                'payment_method' => $employee->payment_method,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->input('items', []) as $item) {
                $payment->items()->create([
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'amount' => $item['amount'],
                    'is_taxable' => !empty($item['is_taxable']),
                    'is_pre_tax' => !empty($item['is_pre_tax']),
                    'is_statutory' => !empty($item['is_statutory']),
                    'source_component_id' => $item['source_component_id'] ?? null,
                ]);
            }

            $payment->recalculatePaye();

            return $payment;
        });

        return redirect()->route('hr.salary-payments.show', $payment)->with('success', 'Salary payment created.');
    }

    public function show(SalaryPayment $salaryPayment)
    {
        $salaryPayment->load(['employee', 'items', 'creator', 'approver', 'payer', 'canceller', 'financialTransaction']);

        return view('hr.salary_payments.show', compact('salaryPayment'));
    }

    public function edit(SalaryPayment $salaryPayment)
    {
        abort_unless($salaryPayment->status === 'draft', 403, 'Only draft payments can be edited.');

        $salaryPayment->load(['employee', 'items']);

        return view('hr.salary_payments.edit', compact('salaryPayment'));
    }

    public function update(Request $request, SalaryPayment $salaryPayment)
    {
        abort_unless($salaryPayment->status === 'draft', 403, 'Only draft payments can be edited.');

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.type' => ['required_with:items', 'in:allowance,deduction'],
            'items.*.name' => ['required_with:items', 'string', 'max:100'],
            'items.*.amount' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($salaryPayment, $request, $validated) {
            $salaryPayment->update(['notes' => $validated['notes'] ?? null]);

            $salaryPayment->items()->delete();

            foreach ($request->input('items', []) as $item) {
                $salaryPayment->items()->create([
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'amount' => $item['amount'],
                    'is_taxable' => !empty($item['is_taxable']),
                    'is_pre_tax' => !empty($item['is_pre_tax']),
                    'is_statutory' => !empty($item['is_statutory']),
                    'source_component_id' => $item['source_component_id'] ?? null,
                ]);
            }

            $salaryPayment->recalculateTotals();
        });

        return redirect()->route('hr.salary-payments.show', $salaryPayment)->with('success', 'Salary payment updated.');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'pay_period_year' => ['required', 'integer', 'min:2000'],
            'pay_period_month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $employees = Employee::active()->with('salaryComponents')->get();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($employees, $validated, &$created, &$skipped) {
            foreach ($employees as $employee) {
                $exists = SalaryPayment::where('employee_id', $employee->id)
                    ->forPeriod($validated['pay_period_year'], $validated['pay_period_month'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $payment = SalaryPayment::create([
                    'employee_id' => $employee->id,
                    'pay_period_year' => $validated['pay_period_year'],
                    'pay_period_month' => $validated['pay_period_month'],
                    'basic_salary' => $employee->basic_salary,
                    'net_salary' => $employee->basic_salary,
                    'payment_method' => $employee->payment_method,
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                ]);

                foreach ($employee->salaryComponents->where('is_active', true) as $component) {
                    $payment->items()->create([
                        'type' => $component->type,
                        'name' => $component->name,
                        'amount' => $component->resolveAmount((float) $employee->basic_salary),
                        'is_taxable' => $component->is_taxable,
                        'is_pre_tax' => $component->is_pre_tax,
                        'is_statutory' => $component->is_statutory,
                        'source_component_id' => $component->id,
                    ]);
                }

                $payment->recalculatePaye();

                $created++;
            }
        });

        $period = sprintf('%04d-%02d', $validated['pay_period_year'], $validated['pay_period_month']);

        return redirect()->route('hr.salary-payments.index')
            ->with('success', "Generated {$created} salary payment(s) for {$period}. {$skipped} skipped (already exist).");
    }

    public function recalculatePaye(SalaryPayment $salaryPayment)
    {
        abort_unless($salaryPayment->status === 'draft', 403, 'Only draft payments can be recalculated.');

        $salaryPayment->recalculatePaye();

        return back()->with('success', 'PAYE recalculated.');
    }

    public function approve(SalaryPayment $salaryPayment)
    {
        abort_unless($salaryPayment->status === 'draft', 403, 'Only draft payments can be approved.');

        $salaryPayment->approve(Auth::user());

        return back()->with('success', 'Salary payment approved.');
    }

    public function pay(Request $request, SalaryPayment $salaryPayment)
    {
        abort_unless($salaryPayment->status === 'approved', 403, 'Only approved payments can be marked as paid.');

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,mobile_money,cheque'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $salaryPayment->markAsPaid(Auth::user(), $validated);

        return redirect()->route('hr.salary-payments.show', $salaryPayment)
            ->with('success', 'Salary payment marked as paid and recorded in Financial Management.');
    }

    public function cancel(Request $request, SalaryPayment $salaryPayment)
    {
        abort_if($salaryPayment->status === 'cancelled', 403, 'This payment is already cancelled.');

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string'],
        ]);

        $salaryPayment->cancel(Auth::user(), $validated['cancellation_reason']);

        return redirect()->route('hr.salary-payments.show', $salaryPayment)->with('success', 'Salary payment cancelled.');
    }

    public function payslip(SalaryPayment $salaryPayment)
    {
        $salaryPayment->load(['employee', 'items']);

        return view('hr.salary_payments.payslip', compact('salaryPayment'));
    }
}
