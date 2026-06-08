@extends('layouts.app_main_layout')

@section('page_title', 'Investigation Form Records')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-printer me-2"></i>Investigation Form Records</h5>
                </div>

                {{-- Search --}}
                <div class="card-body border-bottom pb-2">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Search patient or service</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   value="{{ request('search') }}" placeholder="Patient name or service…">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('investigation-form-records.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                    <i class="bi bi-x"></i> Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Service / Form</th>
                                    <th>Date Ordered</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                    @php
                                        $inv     = $record->investigation;
                                        $patient = $inv->patient;
                                        $service = $inv->medicalService;
                                    @endphp
                                    <tr>
                                        <td>{{ $records->firstItem() + $loop->index }}</td>
                                        <td>
                                            <strong>{{ $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name) }}</strong>
                                            @if($patient->card_number)
                                                <br><small class="text-muted">CTC: {{ $patient->card_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $service->name ?? '—' }}
                                            @if($service->form_type)
                                                <br><small class="text-muted"><code>{{ $service->form_type }}</code></small>
                                            @endif
                                        </td>
                                        <td>{{ $inv->ordered_at ? $inv->ordered_at->format('d M Y H:i') : $record->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge {{ $inv->status_badge_class }}">
                                                {{ $inv->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('investigation-form-records.show', $record->id) }}"
                                               class="btn btn-sm btn-outline-success" target="_blank" title="View & Print">
                                                <i class="bi bi-printer"></i> View & Print
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No investigation form records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($records->hasPages())
                    <div class="card-footer">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
