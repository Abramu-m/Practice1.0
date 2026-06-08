@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'Consultation Fees Management' }}
 @endsection

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave"></i> Consultation Fees Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('consultation_fees.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add New Fee Structure
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('consultation_fees.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by doctor, category, visit type, or fee..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="doctor_filter" class="form-control">
                                    <option value="">All Doctors</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->doctor_id }}" {{ request('doctor_filter') == $doctor->doctor_id ? 'selected' : '' }}>
                                            {{ $doctor->user->name ?? 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
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
                                    <a href="{{ route('consultation_fees.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="consultationFees">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Doctor</th>
                                    <th>Patient Category</th>
                                    <th>Visit Type</th>
                                    <th>Cash Amount</th>
                                    <th>Covered Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consultationFees as $fee)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-md text-primary me-2"></i>
                                                <strong>{{ $fee->doctor->user->name ?? 'Unknown' }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $fee->patientCategory->description ?? 'N/A' }}</td>
                                        <td>{{ $fee->visitType->description ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success badge-lg">
                                                ${{ number_format($fee->cash_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info badge-lg">
                                                ${{ number_format($fee->covered_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>{{ $fee->description ? Str::limit($fee->description, 50) : 'N/A' }}</td>
                                        <td>
                                            @if($fee->status == 1)
                                                <span class="badge bg-success text-black">Active</span>
                                            @else
                                                <span class="badge bg-danger text-black">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $fee->creator->name ?? 'System' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('consultation_fees.show', $fee->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('consultation_fees.edit', $fee->id) }}" class="btn btn-sm btn-warning" title="Edit Fee">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                            <div style="margin-top: 5px;">
                                                <form action="{{ route('consultation_fees.destroy', $fee->id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this consultation fee?')" class="btn btn-sm btn-danger" title="Delete Fee">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No consultation fees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $consultationFees->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#consultationFees').DataTable({
        responsive: true,
        order: [[1, 'asc']],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ],
        language: {
            search: "Search consultation fees:",
            lengthMenu: "Show _MENU_ fees per page",
            info: "Showing _START_ to _END_ of _TOTAL_ fees",
            infoEmpty: "No consultation fees found",
            infoFiltered: "(filtered from _MAX_ total fees)"
        }
    });
});
</script>
@endsection

@section('extra_footer_content')
@endsection
