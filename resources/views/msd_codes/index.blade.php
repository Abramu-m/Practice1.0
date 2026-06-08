@extends('layouts.app_main_layout')

@section('page_title', 'MSD Item Codes')

@section('main_content')
<div class="container-fluid">
    <h3 class="mb-3">MSD item code library</h3>
    <p class="text-muted">
        Reference library of Medical Stores Department (MSD) national item codes. Attach a code to
        a medication from the medication's edit form — search by code or name there to map it.
    </p>

    <form method="GET" action="{{ route('msd-codes.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="term" class="form-control" placeholder="Search code or name"
                       value="{{ old('term', request('term')) }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-secondary" type="submit">Search</button>
                <a href="{{ route('msd-codes.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Unit</th>
                <th>Category</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($msdCodes as $item)
            <tr>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->category }}</td>
                <td>
                    @if($item->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">
                    No MSD codes found. Import the library via
                    <code>php artisan codes:import msd &lt;file&gt;</code>.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-3">
        {{ $msdCodes->links() }}
    </div>
</div>
@endsection
