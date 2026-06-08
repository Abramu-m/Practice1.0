@extends('layouts.app_main_layout')

@section('page_title', 'Store Consumables')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Store Consumables</h3>
                    <div class="card-tools">
                        <a href="{{ route('store.consumables.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Consumable
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Current Stock</th>
                                    <th>Reorder Level</th>
                                    <th>Status</th>
                                    <th>Stock Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consumables as $consumable)
                                <tr>
                                    <td>{{ $consumable->code }}</td>
                                    <td>
                                        <strong>{{ $consumable->name }}</strong>
                                        @if($consumable->description)
                                            <br><small class="text-muted">{{ Str::limit($consumable->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($consumable->category)
                                            <span class="badge bg-info text-black">{{ $consumable->category->name }}</span>
                                        @else
                                            <span class="text-muted">No Category</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($consumable->unit)
                                            {{ $consumable->unit->name }} ({{ $consumable->unit->abbreviation }})
                                        @else
                                            <span class="text-muted">No Unit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-black badge bg-{{ $consumable->current_stock > 0 ? 'success' : 'danger' }}">
                                            {{ number_format($consumable->current_stock) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($consumable->reorder_level) }}</td>
                                    <td>
                                        @if($consumable->is_active)
                                            <span class="badge bg-success text-black">Active</span>
                                        @else
                                            <span class="badge bg-danger text-black">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-black badge bg-{{ $consumable->stock_status_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $consumable->stock_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('store.consumables.show', $consumable->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('store.consumables.edit', $consumable->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('store.consumables.destroy', $consumable->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No consumables found.</p>
                                            <a href="{{ route('store.consumables.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add First Consumable
                                            </a>
                                        </div>
                                    </td>
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

@section('scripts')
<script>
$(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        order: [[1, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [-1] }
        ]
    });
});
</script>
@endsection
