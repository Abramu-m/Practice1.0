@extends('layouts.app_main_layout')

@section('page_title', 'Dashboard - Practice 1.0')

@section('main_content')
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Welcome, {{ auth()->user()->name }}!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Role:</strong> {{ ucfirst(auth()->user()->role) }}</p>
                            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                            <p><strong>Status:</strong> 
                                @if(auth()->user()->is_verified)
                                    <span class="badge badge-success text-black">Verified</span>
                                @else
                                    <span class="badge badge-warning">Pending Verification</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if(auth()->user()->is_admin|| auth()->user()->is_super)
                                <div class="alert alert-info">
                                    <i class="fas fa-shield-alt"></i> You have administrative privileges.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if(auth()->user()->is_admin|| auth()->user()->is_super)
    @if($systemAlerts['low_stock'] > 0 || $systemAlerts['pending_users'] > 0 || $systemAlerts['pending_requisitions'] > 0 || $systemAlerts['pending_payments'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> System Alerts
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($systemAlerts['low_stock'] > 0)
                        <div class="col-md-3">
                            <div class="alert alert-warning">
                                <strong>{{ $systemAlerts['low_stock'] }}</strong> medications are low on stock
                                <br><a href="{{ route('medications.index') }}" class="btn btn-sm btn-warning mt-1">View Medications</a>
                            </div>
                        </div>
                        @endif
                        @if($systemAlerts['pending_users'] > 0)
                        <div class="col-md-3">
                            <div class="alert alert-info">
                                <strong>{{ $systemAlerts['pending_users'] }}</strong> users pending verification
                                <br><a href="{{ route('users.pending-verification') }}" class="btn btn-sm btn-info mt-1">Review Users</a>
                            </div>
                        </div>
                        @endif
                        @if($systemAlerts['pending_requisitions'] > 0)
                        <div class="col-md-3">
                            <div class="alert alert-primary">
                                <strong>{{ $systemAlerts['pending_requisitions'] }}</strong> store requisitions pending
                                <br><a href="{{ route('store.requisitions.index') }}" class="btn btn-sm btn-primary mt-1">View Requisitions</a>
                            </div>
                        </div>
                        @endif
                        @if($systemAlerts['pending_payments'] > 0)
                        <div class="col-md-3">
                            <div class="alert alert-danger">
                                <strong>{{ $systemAlerts['pending_payments'] }}</strong> payments pending
                                <br><a href="{{ route('financial.transactions.index') }}" class="btn btn-sm btn-danger mt-1">View Payments</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Main Statistics Cards -->
    <div class="row mb-4">
        <!-- Clinical Statistics -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPatients }}</h3>
                    <p>Total Patients</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('patients.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $todaysVisits }}</h3>
                    <p>Today's Visits</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <a href="{{ route('patient_visits.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingConsultations }}</h3>
                    <p>Pending Consultations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('patient_visits.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $activeConsultations }}</h3>
                    <p>Active Consultations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <a href="{{ route('patient_visits.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Financial & Investigation Statistics -->
    @if(auth()->user()->is_admin|| auth()->user()->is_super)
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3>TSh {{ number_format($todaysRevenue, 0) }}</h3>
                    <p>Today's Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <a href="{{ route('financial.dashboard') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3>{{ $totalInvestigations }}</h3>
                    <p>Total Investigations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-microscope"></i>
                </div>
                <a href="{{ route('investigations.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3>{{ $pendingInvestigations }}</h3>
                    <p>Pending Investigations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-flask"></i>
                </div>
                <a href="{{ route('investigations.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-danger">
                <div class="inner">
                    <h3>{{ $totalMedications }}</h3>
                    <p>Medications Available</p>
                </div>
                <div class="icon">
                    <i class="fas fa-pills"></i>
                </div>
                <a href="{{ route('medications.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Financial Transactions for Admins -->
    @if($recentTransactions->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Recent Financial Transactions
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('financial.transactions.index') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-eye"></i> View All Transactions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Patient</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td><strong>{{ $transaction->transaction_number }}</strong></td>
                                        <td>{{ $transaction->patient->first_name ?? 'N/A' }} {{ $transaction->patient->last_name ?? '' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $transaction->transaction_type === 'income' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->category) }}
                                            </span>
                                        </td>
                                        <td>TSh {{ number_format($transaction->amount, 0) }}</td>
                                        <td>{{ $transaction->transaction_date->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Pending Consultations Quick Access -->
    @if($recentPendingVisits->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-stethoscope"></i> Pending Consultations - Quick Start
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('patient_visits.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> View All Visits
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Visit Date</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPendingVisits as $visit)
                                    <tr>
                                        <td>
                                            <strong>{{ $visit->patientInfo->full_name ?? 'Unknown' }}</strong><br>
                                            <small class="text-muted">{{ $visit->patientInfo->mr_number ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>{{ optional(optional($visit->doctorInfo)->user)->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Waiting
                                            </span>
                                        </td>
                                        <td>
                                            @if($visit->visitType && stripos($visit->visitType->description, 'lab only') === false && 
                                                (auth()->user()->is_admin || auth()->user()->is_super || 
                                                 (auth()->user()->role === 'doctor' && auth()->user()->doctor && 
                                                  auth()->user()->doctor->doctor_id == $visit->doctor)))
                                                <a href="{{ route('consultations.show', $visit->id) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-user-md"></i> Start Consultation
                                                </a>
                                            @elseif($visit->visitType && stripos($visit->visitType->description, 'lab only') !== false)
                                                <a href="{{ route('patient_visits.index', ['patient_id' => $visit->patient, 'visit_id' => $visit->id, 'patient_name' => urlencode($visit->patientInfo->full_name ?? 'Unknown')]) }}" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Add lab investigations for this visit">
                                                    <i class="fas fa-flask"></i> Add Labs
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-lock"></i> Not Assigned
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Admin Management Sections -->
    @if(auth()->user()->is_admin|| auth()->user()->is_super)
    <div class="row mb-4">
        <!-- User Management -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users-cog"></i> User Management
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Verified</span>
                                    <span class="info-box-number">{{ $verifiedUsers }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending</span>
                                    <span class="info-box-number">{{ $pendingUsers }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('users.pending-verification') }}" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-user-check"></i> Review Pending
                            </a>
                            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-list"></i> All Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Management -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Financial Overview
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monthly Revenue</span>
                                    <span class="info-box-number">TSh {{ number_format($monthlyRevenue, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('financial.dashboard') }}" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-chart-bar"></i> Financial Dashboard
                            </a>
                            <a href="{{ route('financial.transactions.index') }}" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-list"></i> Transactions
                            </a>
                            <a href="{{ route('financial.receipts.index') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-receipt"></i> Receipts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Store & Medication Management -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-store"></i> Store Management
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-boxes"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Medications</span>
                                    <span class="info-box-number">{{ $totalMedications }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Low Stock</span>
                                    <span class="info-box-number">{{ $lowStockMedications }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('medications.index') }}" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-pills"></i> Medications
                            </a>
                            <a href="{{ route('medications.dashboard') }}" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-warehouse"></i> Store Dashboard
                            </a>
                            <a href="{{ route('store.requisitions.index') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-clipboard-list"></i> Requisitions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investigation Management -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-microscope"></i> Laboratory Management
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-vial"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Today's Tests</span>
                                    <span class="info-box-number">{{ $todaysInvestigations }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Completed</span>
                                    <span class="info-box-number">{{ $completedInvestigations }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('investigations.index') }}" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-list"></i> All Investigations
                            </a>
                            <a href="{{ route('medical_services.index') }}" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-cogs"></i> Medical Services
                            </a>
                            <a href="{{ route('sample_types.index') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-test-tube"></i> Sample Types
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Navigation for All Users -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-rocket"></i> Quick Navigation
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('patients.index') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-users"></i><br>Patients
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('doctors.index') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-user-md"></i><br>Doctors
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('patient_categories.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-tags"></i><br>Categories
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('visit_types.index') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-calendar-check"></i><br>Visit Types
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('medications.dashboard') }}" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-pills"></i><br>Medications
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('investigations.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-microscope"></i><br>Lab Tests
                            </a>
                        </div>
                    </div>
                    
                    @if(auth()->user()->is_admin|| auth()->user()->is_super)
                    <hr>
                    <h5><i class="fas fa-cog"></i> Administrative Tools</h5>
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <a href="{{ route('financial.dashboard') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-chart-line"></i><br>Financial
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('medications.dashboard') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-warehouse"></i><br>Store
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-users-cog"></i><br>Users
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('medications.formulations.index') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-capsules"></i><br>Formulations
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('administration-routes.index') }}" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-route"></i><br>Routes
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('service_categories.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-sitemap"></i><br>Service Categories
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
