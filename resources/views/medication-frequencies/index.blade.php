@extends('layouts.app_main_layout')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Medication Frequencies</h3>
                    <div class="card-tools">
                        <a href="{{ route('medication-frequencies.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Frequency
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
                        <table class="table table-bordered table-striped" id="frequencies-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Frequency Name</th>
                                    <th>Code</th>
                                    <th>Administration Times</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($frequencies as $frequency)
                                    <tr>
                                        <td>{{ $frequency->display_order }}</td>
                                        <td>{{ $frequency->frequency_name }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $frequency->frequency_code }}</span>
                                        </td>
                                        <td>
                                            @if($frequency->administration_times)
                                                @foreach($frequency->administration_times as $time)
                                                    <span class="badge bg-info me-1">{{ $time }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('medication-frequencies.toggle-status', $frequency) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $frequency->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $frequency->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('medication-frequencies.show', $frequency) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medication-frequencies.edit', $frequency) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('medication-frequencies.destroy', $frequency) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this frequency?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No medication frequencies found.</td>
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
    $('#frequencies-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { targets: -1, orderable: false }
        ]
    });
});
</script>
@endsection
