@extends('layouts.app_main_layout')

@section('page_title')
    Employees
@endsection

@section('main_content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-people"></i> Employees</h3>
        <a href="{{ route('hr.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Employee
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body p-0">
            <table id="employeesTable" class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th>Employee #</th>
                        <th>Name</th>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Employment Type</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_number }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->job_title }}</td>
                            <td>{{ $employee->department }}</td>
                            <td>{{ ucfirst($employee->employment_type) }}</td>
                            <td>Tsh {{ number_format($employee->basic_salary, 2) }}</td>
                            <td><span class="badge {{ $employee->status_badge }}">{{ ucfirst($employee->status) }}</span></td>
                            <td>
                                <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#employeesTable').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ]
    });
});
</script>
@endsection
