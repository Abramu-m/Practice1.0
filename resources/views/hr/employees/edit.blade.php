@extends('layouts.app_main_layout')

@section('page_title')
    Edit Employee
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-person-gear"></i> Edit Employee — {{ $employee->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Employee
                </a>
            </div>
        </div>
        <form action="{{ route('hr.employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('hr.employees._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Employee
                </button>
                <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-outline-secondary">Cancel</a>
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
