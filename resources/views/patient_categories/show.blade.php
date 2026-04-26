<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patient_categories\show.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Patient Category Details' }}
 @endsection

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>Patient Category: {{ $patientCategory->description }}</h3>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="200">ID:</th>
                    <td>{{ $patientCategory->id }}</td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td>{{ $patientCategory->description }}</td>
                </tr>
                <tr>
                    <th>Type:</th>
                    <td>
                        @if($patientCategory->type == 'cash')
                            <span class="badge bg-primary text-black">Cash</span>
                        @else
                            <span class="badge bg-info text-black">Insurance</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        @if($patientCategory->is_active)
                            <span class="badge bg-success text-black">Active</span>
                        @else
                            <span class="badge bg-danger text-black">Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Created By:</th>
                    <td>{{ $patientCategory->creator->first_name ?? 'N/A' }} {{ $patientCategory->creator->last_name ?? '' }}</td>
                </tr>
                <tr>
                    <th>Created At:</th>
                    <td>{{ $patientCategory->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Updated At:</th>
                    <td>{{ $patientCategory->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>
        <div class="card-footer">
            <a href="{{ route('patient_categories.edit', $patientCategory->id) }}" class="btn btn-primary">Edit Category</a>
            <a href="{{ route('patient_categories.index') }}" class="btn btn-secondary">Back to List</a>
            <form action="{{ route('patient_categories.destroy', $patientCategory->id) }}" method="POST" style="display:inline;" class="float-end">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Are you sure you want to delete this category?')" class="btn btn-danger">Delete Category</button>
            </form>
        </div>
    </div>
@endsection

@section('extra_footer_content')
@endsection