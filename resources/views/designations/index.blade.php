@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Designations' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Designations</h4>
                    <a href="{{ route('designations.create') }}" class="btn btn-primary">Add New Designation</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $designation)
                                <tr>
                                    <td>{{ $designation->id }}</td>
                                    <td>{{ $designation->designation_code }}</td>
                                    <td>{{ $designation->description ?? 'N/A' }}</td>
                                    <td>{{ $designation->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('designations.show', $designation) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ route('designations.edit', $designation) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('designations.destroy', $designation) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ],
        language: {
            search: "Search designations:",
            lengthMenu: "Show _MENU_ designations per page",
            info: "Showing _START_ to _END_ of _TOTAL_ designations",
            infoEmpty: "No designations found",
            infoFiltered: "(filtered from _MAX_ total designations)"
        }
    });
});
</script>
@endsection

@section('extra_footer_content')
@endsection