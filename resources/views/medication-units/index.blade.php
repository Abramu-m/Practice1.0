@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Units</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-units.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Unit
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="units-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Unit Name</th>
                                    <th>Code</th>
                                    <th>Symbol</th>
                                    <th>Type</th>
                                    <th>Base Unit</th>
                                    <th>Conversion Factor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $unit)
                                    <tr>
                                        <td>{{ $unit->display_order }}</td>
                                        <td>{{ $unit->unit_name }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $unit->unit_code }}</span>
                                        </td>
                                        <td>
                                            @if($unit->unit_symbol)
                                                <span class="badge bg-info text-black">{{ $unit->unit_symbol }}</span>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary text-black">{{ ucfirst($unit->unit_type) }}</span>
                                        </td>
                                        <td>
                                            @if($unit->baseUnit)
                                                {{ $unit->baseUnit->unit_name }}
                                            @else
                                                <span class="text-muted">Base Unit</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($unit->conversion_factor)
                                                {{ $unit->conversion_factor }}
                                            @else
                                                <span class="text-muted">1</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('medication-units.toggle-status', $unit) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $unit->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('medication-units.show', $unit) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medication-units.edit', $unit) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('medication-units.destroy', $unit) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this unit?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No medication units found.</td>
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
    $('#units-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { targets: -1, orderable: false }
        ]
    });
});
</script>
@endsection
