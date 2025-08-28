@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Locations</h3>
                    <div class="card-tools">
                        <a href="{{ route('store-locations.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Location
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Manager</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                    <tr>
                                        <td>
                                            <strong>{{ $location->name }}</strong>
                                            @if($location->description)
                                                <br><small class="text-muted">{{ $location->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $location->code }}</code>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($location->type) }}</span>
                                        </td>
                                        <td>
                                            {{ $location->manager_name ?: '-' }}
                                            @if($location->manager_contact)
                                                <br><small class="text-muted">{{ $location->manager_contact }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($location->can_request)
                                                    <span class="badge badge-success badge-sm">Request</span>
                                                @endif
                                                @if($location->can_issue)
                                                    <span class="badge badge-warning badge-sm">Issue</span>
                                                @endif
                                                @if($location->can_receive)
                                                    <span class="badge badge-info badge-sm">Receive</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $location->is_active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('store-locations.show', $location) }}" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('store-locations.edit', $location) }}" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('store-locations.destroy', $location) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Are you sure you want to delete this location?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No store locations found.</td>
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
@endsection
