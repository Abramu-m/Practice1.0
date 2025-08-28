<!-- filepath: c:\xampp\htdocs\Practice1.0\resources\views\patient_categories\index.blade.php -->
@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Patient Categories' }}
 @endsection

@section('Content_Description')
    {{ 'Manage patient categories.' }}
@endsection

@section('main_content')
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('patient_categories.create') }}" class="btn btn-primary">Add Patient Category</a>
        </div>
        <div class="col-md-6">
            <form method="GET" action="{{ route('patient_categories.index') }}" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search categories..." value="{{ request('search') }}">
                <select name="filter" class="form-select me-2">
                    <option value="">All Status</option>
                    <option value="1" {{ request('filter') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('filter') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                <select name="type_filter" class="form-select me-2">
                    <option value="">All Types</option>
                    <option value="cash" {{ request('type_filter') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="insurance" {{ request('type_filter') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patientCategories as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $category->description }}</td>
                        <td>
                            @if($category->type == 'cash')
                                <span class="badge badge-primary text-black">Cash</span>
                            @else
                                <span class="badge badge-info text-black">Insurance</span>
                            @endif
                        </td>
                        <td>
                            @if($category->is_active)
                                <span class="badge badge-success text-black">Active</span>
                            @else
                                <span class="badge badge-danger text-black">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $category->creator->first_name ?? 'N/A' }} {{ $category->creator->last_name ?? '' }}</td>
                        <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('patient_categories.show', $category->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('patient_categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('patient_categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this category?')" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No patient categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $patientCategories->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

@section('extra_footer_content')
@endsection