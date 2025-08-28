@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Administration Routes</h3>
                    <div class="card-tools">
                        <a href="{{ route('administration-routes.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Route
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="routes-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Route Name</th>
                                    <th>Code</th>
                                    <th>Abbreviation</th>
                                    <th>Description</th>
                                    <th>Requires Prescription</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($routes as $route)
                                    <tr>
                                        <td>{{ $route->display_order }}</td>
                                        <td>{{ $route->route_name }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $route->route_code }}</span>
                                        </td>
                                        <td>
                                            @if($route->route_abbreviation)
                                                <span class="badge badge-info text-black">{{ $route->route_abbreviation }}</span>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($route->description)
                                                {{ Str::limit($route->description, 50) }}
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($route->requires_prescription)
                                                <span class="badge badge-warning">Yes</span>
                                            @else
                                                <span class="badge badge-success text-black">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('administration-routes.toggle-status', $route) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $route->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $route->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('administration-routes.show', $route) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('administration-routes.edit', $route) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('administration-routes.destroy', $route) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this route?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No administration routes found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#routes-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { targets: -1, orderable: false }
        ]
    });
});
</script>
@endsection
