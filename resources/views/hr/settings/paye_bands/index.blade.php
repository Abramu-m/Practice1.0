@extends('layouts.app_main_layout')

@section('page_title')
    PAYE Tax Bands
@endsection

@section('main_content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-percent"></i> PAYE Tax Bands</h3>
        <a href="{{ route('hr.settings.paye-bands.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Band
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Min Income (Tsh)</th>
                        <th>Max Income (Tsh)</th>
                        <th>Rate</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bands as $band)
                        <tr>
                            <td>{{ $band->band_order }}</td>
                            <td>{{ number_format($band->min_income, 2) }}</td>
                            <td>{{ $band->max_income !== null ? number_format($band->max_income, 2) : 'No limit' }}</td>
                            <td>{{ number_format($band->rate, 2) }}%</td>
                            <td>
                                <span class="badge {{ $band->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $band->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('hr.settings.paye-bands.edit', $band) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('hr.settings.paye-bands.destroy', $band) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this PAYE band?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No PAYE bands configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
