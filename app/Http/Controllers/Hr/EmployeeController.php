<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreEmployeeRequest;
use App\Http\Requests\Hr\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->orderBy('first_name')->get();

        return view('hr.employees.index', compact('employees'));
    }

    public function create()
    {
        $linkableUsers = User::whereDoesntHave('employee')->orderBy('first_name')->get();

        return view('hr.employees.create', compact('linkableUsers'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->validated() + ['created_by' => Auth::id()]);

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Employee added successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'user',
            'salaryComponents',
            'salaryPayments' => fn ($query) => $query->orderByDesc('pay_period_year')->orderByDesc('pay_period_month'),
        ]);

        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $linkableUsers = User::whereDoesntHave('employee')
            ->orWhere('id', $employee->user_id)
            ->orderBy('first_name')
            ->get();

        return view('hr.employees.edit', compact('employee', 'linkableUsers'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->update([
            'status' => $employee->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Employee status updated.');
    }
}
