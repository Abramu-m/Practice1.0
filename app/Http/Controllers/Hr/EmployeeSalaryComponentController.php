<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryComponentController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $employee->salaryComponents()->create($this->validateComponent($request) + ['created_by' => Auth::id()]);

        return back()->with('success', 'Salary component added.');
    }

    public function update(Request $request, EmployeeSalaryComponent $salaryComponent)
    {
        $salaryComponent->update($this->validateComponent($request));

        return back()->with('success', 'Salary component updated.');
    }

    public function destroy(EmployeeSalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();

        return back()->with('success', 'Salary component removed.');
    }

    private function validateComponent(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'in:allowance,deduction'],
            'name' => ['required', 'string', 'max:100'],
            'calculation_type' => ['required', 'in:fixed,percentage_of_basic'],
            'amount' => ['nullable', 'numeric', 'min:0', 'required_if:calculation_type,fixed'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:calculation_type,percentage_of_basic'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['is_taxable'] = $request->boolean('is_taxable');
        $validated['is_pre_tax'] = $request->boolean('is_pre_tax');
        $validated['is_statutory'] = $request->boolean('is_statutory');
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
