@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Doctors Management' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-md"></i> Doctors Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('doctors.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add New Doctor
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('doctors.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name, email, specialization, or MCT number..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status_filter" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status_filter') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status_filter') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('doctors.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Doctor</th>
                                    <th>Email</th>
                                    <th>Designation</th>
                                    <th>Specialization</th>
                                    <th>MCT Number</th>
                                    <th>Total Visits</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($doctors as $doctor)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    <i class="fas fa-user-md text-primary"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $doctor->user->name ?? 'N/A' }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $doctor->user->email ?? 'N/A' }}</td>
                                        <td>{{ $doctor->designationInfo->description ?? 'N/A' }}</td>
                                        <td>{{ $doctor->specialization ?? 'N/A' }}</td>
                                        <td>{{ $doctor->mct_number ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-info text-black">{{ $doctor->visits->count() }} visit(s)</span>
                                        </td>
                                        <td>
                                            @if($doctor->status == 1)
                                                <span class="badge badge-success text-black">Active</span>
                                            @else
                                                <span class="badge badge-danger text-black">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $doctor->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('doctors.show', $doctor->doctor_id) }}" class="btn btn-sm btn-info" title="View Doctor">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('doctors.edit', $doctor->doctor_id) }}" class="btn btn-sm btn-warning" title="Edit Doctor">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($doctor->visits->count() > 0)
                                                    <a href="{{ route('patient_visits.index', ['doctor_id' => $doctor->doctor_id]) }}" class="btn btn-sm btn-primary" title="View Visits">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </a>
                                                @endif
                                            </div>
                                            <div style="margin-top: 5px;">
                                                <form action="{{ route('doctors.destroy', $doctor->doctor_id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this doctor?')" class="btn btn-sm btn-danger" title="Delete Doctor">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No doctors found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $doctors->appends(request()->query())->links('pagination::bootstrap-5') }}
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
        order: [[1, 'asc']],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ],
        language: {
            search: "Search doctors:",
            lengthMenu: "Show _MENU_ doctors per page",
            info: "Showing _START_ to _END_ of _TOTAL_ doctors",
            infoEmpty: "No doctors found",
            infoFiltered: "(filtered from _MAX_ total doctors)"
        }
    });
});
</script>
@endsection

@section('extra_footer_content')
@endsection