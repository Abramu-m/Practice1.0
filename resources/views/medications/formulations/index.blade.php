@extends('layouts.app_main_layout')

@section('page_title', 'Medication Formulations')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Formulations</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{ route('medications.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Medications
                            </a>
                            <a href="{{ route('medications.formulations.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New Formulation
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($formulations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="8%">#</th>
                                        <th>Description</th>
                                        <th width="12%">Status</th>
                                        <th width="10%">Medications</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formulations as $formulation)
                                    <tr>
                                        <td>{{ $formulation->id }}</td>
                                        <td>{{ $formulation->description }}</td>
                                        <td>
                                            <span class="text-black badge bg-{{ $formulation->is_active ? 'success' : 'secondary' }} text-black">
                                                {{ $formulation->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-black">
                                                {{ $formulation->medications_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('medications.formulations.show', $formulation->id) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medications.formulations.edit', $formulation->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(!$formulation->isInUse())
                                                <form action="{{ route('medications.formulations.destroy', $formulation->id) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this formulation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @else
                                                <button class="btn btn-secondary btn-sm" disabled title="Cannot delete - formulation is in use">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($formulations, 'links'))
                            <div class="mt-3">
                                {{ $formulations->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Formulations Found</h4>
                            <p class="text-muted">Create your first medication formulation to get started.</p>
                            <a href="{{ route('medications.formulations.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Formulation
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    $(document).ready(function() {
        toastr.success('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    $(document).ready(function() {
        toastr.error('{{ session('error') }}');
    });
</script>
@endif

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
            search: "Search formulations:",
            lengthMenu: "Show _MENU_ formulations per page",
            info: "Showing _START_ to _END_ of _TOTAL_ formulations",
            infoEmpty: "No formulations found",
            infoFiltered: "(filtered from _MAX_ total formulations)"
        }
    });
});
</script>
@endsection
@endsection
