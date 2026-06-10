@extends('layouts.app_main_layout')

@section('page_title', 'Reports Dashboard')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-chart-bar"></i> Reports Dashboard
            </h1>
            <p class="text-muted">Generate and view facility reports for disease surveillance and operations</p>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row">
        <!-- Monthly Reports Section -->
        <div class="col-md-12 mb-4">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt"></i> Monthly Reports
            </h3>
        </div>

        <!-- Malaria Monthly Report -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-virus"></i> Malaria Monthly
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Clinical diagnoses and laboratory-confirmed malaria cases by age and gender
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <form method="GET" action="{{ route('admin.reports.malaria-monthly') }}" class="form-inline">
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            <option value="">Select Month</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(date('Y'), $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ date('Y') }}">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- MTUHA OPD Report -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-stethoscope"></i> MTUHA OPD Monthly
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Out-patient department monthly statistics (already available via existing route)
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('reports.mtuha.select') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </div>
        </div>

        <!-- STD/STI Report -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> STD/STI Monthly
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Sexually transmitted infection cases by type, age, and gender
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <form method="GET" action="{{ route('admin.reports.std-sti-monthly') }}" class="form-inline">
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            <option value="">Select Month</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(date('Y'), $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ date('Y') }}">
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Medicines Monthly Report -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-pills"></i> Medicines Monthly
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Monthly medication dispensing and consumption by category
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <form method="GET" action="{{ route('admin.reports.medicines-monthly') }}" class="form-inline">
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            <option value="">Select Month</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(date('Y'), $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ date('Y') }}">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ALu Report -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-capsules"></i> ALu Report
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Monthly antimalarial treatment drugs (ALu / Artesunate) dispensing
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <form method="GET" action="{{ route('admin.reports.alu-monthly') }}" class="form-inline">
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            <option value="">Select Month</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(date('Y'), $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ date('Y') }}">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tracer Medicines Report -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> Tracer Medicines
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Essential medicines consumption tracking and status
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <form method="GET" action="{{ route('admin.reports.tracer-medicines') }}" class="form-inline">
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            <option value="">Select Month</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(date('Y'), $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ date('Y') }}">
                        <button type="submit" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- IDSR Weekly Report -->
        <div class="col-md-12 mb-4 mt-3">
            <h3 class="section-title">
                <i class="fas fa-hospital"></i> IDSR Reports (Weekly Disease Surveillance)
            </h3>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-microscope"></i> IDSR Weekly
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Integrated Disease Surveillance and Response (IDSR) weekly disease cases
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <form method="GET" action="{{ route('admin.reports.idsr-weekly') }}" class="form-inline">
                        <select name="week" class="form-control form-control-sm mr-2" required>
                            <option value="">Select Week</option>
                            @for ($w = 1; $w <= 53; $w++)
                                <option value="{{ $w }}" {{ $w == date('W') ? 'selected' : '' }}>
                                    Week {{ $w }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ date('Y') }}">
                        <button type="submit" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- On-Demand Reports -->
        <div class="col-md-12 mb-4 mt-3">
            <h3 class="section-title">
                <i class="fas fa-toolbox"></i> On-Demand Reports
            </h3>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card report-card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle"></i> Low Stock Alert
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        Current inventory status: medicines below reorder level
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('admin.reports.low-stock-medicines') }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('admin.reports.low-stock-medicines') }}?pdf=1" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.section-title {
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-top: 20px;
}

.report-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #dee2e6;
}

.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
    font-weight: 600;
    padding: 1rem;
}

.form-inline {
    gap: 0.5rem;
}

.form-inline .form-control {
    flex: 1;
    min-width: 120px;
}
</style>
@endsection
