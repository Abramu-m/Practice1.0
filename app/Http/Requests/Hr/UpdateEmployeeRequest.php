<?php

namespace App\Http\Requests\Hr;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id', Rule::unique(Employee::class, 'user_id')->ignore($this->route('employee'))],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:permanent,contract,casual,volunteer'],
            'date_joined' => ['nullable', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'tin_number' => ['nullable', 'string', 'max:255'],
            'nssf_number' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,bank_transfer,mobile_money,cheque'],
            'status' => ['required', 'in:active,inactive,terminated'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
