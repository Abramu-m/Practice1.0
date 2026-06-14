@extends('layouts.app_main_layout')

@section('page_title')
    Add Employee
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-person-plus"></i> Add Employee</h3>
            <div class="card-tools">
                <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Employees
                </a>
            </div>
        </div>
        <form action="{{ route('hr.employees.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('hr.employees._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Save Employee
                </button>
                <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });
});
</script>
@endsection
