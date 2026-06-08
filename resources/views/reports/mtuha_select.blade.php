@extends('layouts.app_main_layout')

@section('page_title', 'MTUHA Monthly Report')

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-columns-reverse"></i> MTUHA Monthly Report</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Chagua mwezi na mwaka kabla ya kupakia ripoti.</p>
                    <form method="GET" action="{{ route('reports.mtuha.month') }}">
                        <div class="mb-3">
                            <label class="form-label">Mwezi (Month)</label>
                            <select name="mwezi" class="form-select" required>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create($year, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mwaka (Year)</label>
                            <select name="mwaka" class="form-select" required>
                                @for ($y = $year; $y >= $year - 5; $y--)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Tazama Ripoti
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
