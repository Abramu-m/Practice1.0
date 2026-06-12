@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Designation Details' }}
 @endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Designation Details</h4>
                    <div>
                        <a href="{{ route('designations.edit', $designation) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('designations.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>ID:</strong></div>
                        <div class="col-md-9">{{ $designation->id }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Designation Code:</strong></div>
                        <div class="col-md-9">{{ $designation->designation_code }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Description:</strong></div>
                        <div class="col-md-9">{{ $designation->description ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Created At:</strong></div>
                        <div class="col-md-9">{{ $designation->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Updated At:</strong></div>
                        <div class="col-md-9">{{ $designation->updated_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4>Doctors with this Designation</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="designation-doctors-table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>MCT Number</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($doctors as $doctor)
                                    <tr>
                                        <td>{{ $doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $doctor->user->email ?? 'N/A' }}</td>
                                        <td>{{ $doctor->specialization ?? 'N/A' }}</td>
                                        <td>{{ $doctor->mct_number ?? 'N/A' }}</td>
                                        <td>
                                            @if($doctor->status == 1)
                                                <span class="badge bg-success text-black">Active</span>
                                            @else
                                                <span class="badge bg-danger text-black">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('doctors.show', $doctor->doctor_id) }}" class="btn btn-sm btn-info" title="View Doctor">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#designation-doctors-table').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ],
        language: {
            emptyTable: "No doctors with this designation."
        }
    });
});
</script>
@endsection

@section('extra_footer_content')
@endsection