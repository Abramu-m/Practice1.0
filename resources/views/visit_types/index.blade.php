@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Visit Types' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Visit Types</h4>
                    <a href="{{ route('visit_types.create') }}" class="btn btn-primary">Add New Visit Type</a>
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
                                <th>Description</th>
                                <th>NHIF Code</th>
                                <th>Patient Categories</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visitTypes as $visitType)
                                <tr>
                                    <td>{{ $visitType->id }}</td>
                                    <td>{{ $visitType->description }}</td>
                                    <td>
                                        @if($visitType->nhif_visit_type_code)
                                            <span class="badge bg-primary">{{ $visitType->nhif_visit_type_code }}</span>
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($visitType->patientCategories->isEmpty())
                                            <span class="badge bg-secondary">All categories</span>
                                        @else
                                            @foreach($visitType->patientCategories as $category)
                                                <span class="badge bg-info text-dark">{{ $category->description }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{ $visitType->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('visit_types.show', $visitType) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ route('visit_types.edit', $visitType) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('visit_types.destroy', $visitType) }}" method="POST" style="display: inline-block;">
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
            search: "Search visit types:",
            lengthMenu: "Show _MENU_ visit types per page",
            info: "Showing _START_ to _END_ of _TOTAL_ visit types",
            infoEmpty: "No visit types found",
            infoFiltered: "(filtered from _MAX_ total visit types)"
        }
    });
});
</script>
@endsection

@section('extra_footer_content')
@endsection
