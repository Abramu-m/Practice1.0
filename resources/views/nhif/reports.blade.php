@extends('layouts.app_main_layout')

@section('page_title', 'NHIF Reports & Analytics')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">NHIF Reports & Analytics</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">NHIF</li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-primary">{{ number_format($stats['total_members'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Total Members</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-success">{{ number_format($stats['active_members'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Active Members</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-info">{{ number_format($stats['total_claims'] ?? 0) }}</h3>
                            <p class="text-muted mb-0">Total Claims</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-medical text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="text-warning">{{ number_format($stats['claims_value'] ?? 0, 0) }}</h3>
                            <p class="text-muted mb-0">Claims Value (TSH)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Report Filters -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter text-primary me-2"></i>
                        Report Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form id="reportFilters">
                        <div class="mb-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ date('Y-m-01') }}">
                        </div>

                        <div class="mb-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-control" id="report_type" name="report_type">
                                <option value="summary">Summary Report</option>
                                <option value="claims">Claims Report</option>
                                <option value="members">Members Report</option>
                                <option value="tariffs">Tariffs Report</option>
                                <option value="financial">Financial Report</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="format" class="form-label">Export Format</label>
                            <select class="form-control" id="format" name="format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-chart-bar me-1"></i> Generate Report
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Reports -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Reports</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="generateQuickReport('daily')">
                            <i class="fas fa-calendar-day me-1"></i> Daily Report
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="generateQuickReport('weekly')">
                            <i class="fas fa-calendar-week me-1"></i> Weekly Report
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="generateQuickReport('monthly')">
                            <i class="fas fa-calendar-alt me-1"></i> Monthly Report
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="generateQuickReport('pending-claims')">
                            <i class="fas fa-clock me-1"></i> Pending Claims
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="col-lg-9">
            <!-- Claims Trend Chart -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Claims Trend (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="claimsTrendChart" height="100"></canvas>
                </div>
            </div>

            <div class="row">
                <!-- Claims Status Distribution -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Claims Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="claimsStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Member Verification Trend -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Member Verifications (Last 7 Days)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="verificationTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent NHIF Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Activity</th>
                                    <th>Patient/Member</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentVerifications as $verification)
                                <tr>
                                    <td>{{ $verification->verification_date?->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-info">Member Verification</span>
                                    </td>
                                    <td>{{ $verification->patient->full_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ method_exists($verification, 'isActive') && $verification->isActive() ? 'success' : 'danger' }}">
                                            {{ $verification->card_status }}
                                        </span>
                                    </td>
                                    <td>-</td>
                                    <td>{{ $verification->verifiedBy->name ?? 'System' }}</td>
                                </tr>
                                @endforeach

                                @foreach($recentClaims as $claim)
                                <tr>
                                    <td>{{ $claim->submission_date?->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-primary">Claim Submission</span>
                                    </td>
                                    <td>{{ $claim->patient->full_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $claim->claim_status === 'submitted' ? 'success' : 'warning' }}">
                                            {{ ucfirst($claim->claim_status) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($claim->total_amount_claimed, 0) }} TSH</td>
                                    <td>{{ $claim->submittedBy->name ?? 'System' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generated Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reportContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadReport">
                    <i class="fas fa-download me-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    initializeCharts();

    // Report generation form
    $('#reportFilters').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
});

function initializeCharts() {
    // Claims Trend Chart
    const claimsTrendCtx = document.getElementById('claimsTrendChart').getContext('2d');
            new Chart(claimsTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($claimsTrendLabels ?? []) !!},
            datasets: [{
                label: 'Claims Submitted',
                data: {!! json_encode($claimsTrend ?? []) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Claims Approved',
                data: [],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Claims Status Chart
    const claimsStatusCtx = document.getElementById('claimsStatusChart').getContext('2d');
            new Chart(claimsStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Draft', 'Submitted', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $statusCounts['draft'] ?? 0 }},
                    {{ $statusCounts['submitted'] ?? 0 }},
                    {{ $statusCounts['approved'] ?? 0 }},
                    {{ $statusCounts['rejected'] ?? 0 }}
                ],
                backgroundColor: [
                    '#ffc107',
                    '#28a745',
                    '#007bff',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });

    // Verification Trend Chart
    const verificationTrendCtx = document.getElementById('verificationTrendChart').getContext('2d');
            new Chart(verificationTrendCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($verificationLabels ?? []) !!},
            datasets: [{
                label: 'Verifications',
                data: {!! json_encode($verificationData ?? []) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function generateReport() {
    const formData = new FormData(document.getElementById('reportFilters'));
    const submitBtn = $('#reportFilters button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Generating...').prop('disabled', true);
    
    $.ajax({
        url: '/nhif/generate-report',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showReport(response);
            } else {
                alert('Failed to generate report: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error generating report. Please try again.');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
}

function generateQuickReport(type) {
    const reportData = {
        type: type,
        format: 'pdf'
    };
    
    $.ajax({
        url: '/nhif/quick-report',
        type: 'POST',
        data: reportData,
        success: function(response) {
            if (response.success) {
                showReport(response);
            } else {
                alert('Failed to generate quick report: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error generating quick report. Please try again.');
        }
    });
}

function showReport(response) {
    $('#reportContent').html(response.content || 'Report generated successfully!');
    $('#reportModal').modal('show');
    
    // Set download link
    $('#downloadReport').off('click').on('click', function() {
        if (response.download_url) {
            window.open(response.download_url, '_blank');
        }
    });
}
</script>
@endsection
