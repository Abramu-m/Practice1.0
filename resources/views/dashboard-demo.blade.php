@extends('layouts.app_main_layout')

@section('page_title', 'Dashboard Demo - Role-Specific Views')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        Role-Specific Dashboard Demo
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-4">
                        This page demonstrates the new role-specific dashboards that have been created for each user type. 
                        Each dashboard is tailored to the specific needs and workflows of that role.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-md mr-2"></i>
                                        Doctor Dashboard
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Clinical-focused dashboard with patient consultations, medical procedures, and claims management.
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i>Today's consultations</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Pending procedures</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Active patients</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Claims status</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Clinical performance metrics</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('dashboard.doctor') }}" class="btn btn-primary btn-block">
                                        <i class="fas fa-eye mr-1"></i>View Doctor Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-nurse mr-2"></i>
                                        Nurse Dashboard
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Nursing care dashboard with triage center, CTC services, and ward management.
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i>Triage patient prioritization</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Vital signs tracking</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Ward stock management</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>CTC services overview</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Nursing procedures</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('dashboard.nurse') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-eye mr-1"></i>View Nurse Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-tie mr-2"></i>
                                        Receptionist Dashboard
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Front desk operations with patient registration, cashier functions, and scheduling.
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i>Patient registration</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Daily revenue tracking</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Appointment scheduling</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Payment processing</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Results collection</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('dashboard.receptionist') }}" class="btn btn-info btn-block">
                                        <i class="fas fa-eye mr-1"></i>View Receptionist Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-microscope mr-2"></i>
                                        Lab Technician Dashboard
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Laboratory operations with test processing, quality control, and equipment monitoring.
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i>Test queue management</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Quality control tracking</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Equipment status</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Specialized forms</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Performance metrics</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('dashboard.lab_technician') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-eye mr-1"></i>View Lab Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-pills mr-2"></i>
                                        Pharmacist Dashboard
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Enhanced pharmacy operations with prescription management and stock control.
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i>Prescription queue</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Stock management</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Cash sales tracking</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Performance metrics</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Alerts & notifications</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('pharmacist.dashboard') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-eye mr-1"></i>View Pharmacist Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-dark">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-shield mr-2"></i>
                                        Admin Dashboard
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Comprehensive administrative dashboard with system-wide overview and management tools.
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i>System statistics</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>User management</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Financial overview</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>System alerts</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>All department access</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('dashboard') }}" class="btn btn-dark btn-block">
                                        <i class="fas fa-eye mr-1"></i>View Admin Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle mr-2"></i>
                            Implementation Notes
                        </h5>
                        <ul class="mb-0">
                            <li><strong>Automatic Redirection:</strong> Users are automatically redirected to their role-specific dashboard when accessing /dashboard</li>
                            <li><strong>Data Integration:</strong> Sample data is currently used; in production, this would connect to your existing controllers and models</li>
                            <li><strong>Responsive Design:</strong> All dashboards are fully responsive and mobile-friendly</li>
                            <li><strong>Navigation Updates:</strong> Role-specific navigation menus now include dashboard links</li>
                            <li><strong>Performance Metrics:</strong> Each dashboard includes relevant KPIs and performance indicators</li>
                        </ul>
                    </div>

                    <div class="alert alert-success">
                        <h5 class="alert-heading">
                            <i class="fas fa-check-circle mr-2"></i>
                            Features Implemented
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Role-based dashboard routing</li>
                                    <li>Custom layouts for each role</li>
                                    <li>Performance metrics display</li>
                                    <li>Quick action buttons</li>
                                    <li>Real-time status indicators</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Interactive charts and graphs</li>
                                    <li>Alert and notification systems</li>
                                    <li>Equipment status monitoring</li>
                                    <li>Stock level indicators</li>
                                    <li>Mobile-responsive design</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
