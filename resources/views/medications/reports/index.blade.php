@extends('layouts.app_main_layout')

@section('page_title', 'Reports Dashboard')

@section('main_content')
@include('layouts.medication-nav')

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Medication Reports Dashboard
                    </h1>
                    <p class="text-muted mb-0">Generate comprehensive reports for medication management</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="exportAllData()">
                        <i class="fas fa-file-excel me-2"></i>
                        Export All Data
                    </button>
                    <a href="{{ route('medications.reports.custom') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Custom Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Report Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-2x mb-3"></i>
                    <h5 class="card-title">Stock Report</h5>
                    <p class="card-text">Current inventory levels and stock movements</p>
                    <a href="{{ route('medications.reports.stock') }}" class="btn btn-light btn-sm">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                    <h5 class="card-title">Consumption Report</h5>
                    <p class="card-text">Usage patterns and consumption trends</p>
                    <a href="{{ route('medications.reports.consumption') }}" class="btn btn-light btn-sm">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-balance-scale fa-2x mb-3"></i>
                    <h5 class="card-title">Reconciliation Report</h5>
                    <p class="card-text">Discrepancies and corrections summary</p>
                    <a href="{{ route('medications.reports.reconciliation') }}" class="btn btn-light btn-sm">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-center">
                    <i class="fas fa-stethoscope fa-2x mb-3"></i>
                    <h5 class="card-title">Medical Services Report</h5>
                    <p class="card-text">Procedures and investigations analysis</p>
                    <a href="{{ route('medications.reports.medical-services') }}" class="btn btn-light btn-sm">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Builder Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-tools text-primary me-2"></i>
                        Quick Report Builder
                    </h5>
                </div>
                <div class="card-body">
                    <form id="quickReportForm">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Report Type</label>
                                <select class="form-select" name="report_type" id="reportType" required>
                                    <option value="">Select Report Type</option>
                                    <option value="stock">Stock Report</option>
                                    <option value="consumption">Consumption Report</option>
                                    <option value="reconciliation">Reconciliation Report</option>
                                    <option value="medical_services">Medical Services Report</option>
                                    <option value="financial">Financial Report</option>
                                    <option value="expiry">Expiry Report</option>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" id="dateFrom" required>
                            </div>

                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" id="dateTo" required>
                            </div>

                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">Format</label>
                                <select class="form-select" name="format" required>
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="pdf">PDF</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">Include Charts</label>
                                <select class="form-select" name="include_charts">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>

                            <div class="col-lg-1 col-md-6 d-flex align-items-end">
                                <div class="d-grid w-100">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Reports --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-primary me-2"></i>
                        Recent Reports
                    </h5>
                    <a href="{{ route('medications.reports.history') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Generated</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReports ?? [] as $report)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $report->report_name ?? 'Unknown Report' }}</div>
                                            <small class="text-muted">{{ $report->description ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($report->report_type ?? 'N/A') }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div>{{ $report->created_at ? $report->created_at->format('M d, Y') : 'N/A' }}</div>
                                            <small class="text-muted">{{ $report->created_at ? $report->created_at->format('H:i') : '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $status = $report->status ?? 'completed';
                                            $badgeClass = match($status) {
                                                'generating' => 'bg-warning text-dark',
                                                'completed' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>
                                        @if($report->status === 'completed')
                                        <a href="{{ route('medications.reports.download', $report->id) }}" 
                                           class="btn btn-sm btn-outline-success" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('medications.reports.view', $report->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-chart-line text-muted fa-2x mb-2"></i>
                                        <p class="text-muted mb-0">No reports generated yet</p>
                                        <small class="text-muted">Generate your first report above</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Report Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ $reportStats['total_reports'] ?? 0 }}</h4>
                                <small class="text-muted">Total Reports</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $reportStats['this_month'] ?? 0 }}</h4>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Report Types</h6>
                        @foreach(($reportStats['by_type'] ?? []) as $type => $count)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">{{ ucfirst($type) }}</span>
                            <span class="badge bg-light text-dark">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Most Popular</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small">{{ $reportStats['most_popular'] ?? 'Stock Report' }}</span>
                            <span class="badge bg-primary">{{ $reportStats['most_popular_count'] ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <a href="{{ route('medications.reports.analytics') }}" class="btn btn-outline-primary btn-sm">
                            View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scheduled Reports --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-primary me-2"></i>
                        Scheduled Reports
                    </h5>
                    <a href="{{ route('medications.reports.schedule.create') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus me-1"></i>
                        Schedule Report
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($scheduledReports ?? [] as $scheduled)
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">{{ $scheduled->name ?? 'Scheduled Report' }}</h6>
                                        <span class="badge {{ $scheduled->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $scheduled->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <p class="card-text text-muted small">{{ $scheduled->description ?? '' }}</p>
                                    <div class="small text-muted mb-2">
                                        <div><strong>Type:</strong> {{ ucfirst($scheduled->report_type ?? 'N/A') }}</div>
                                        <div><strong>Frequency:</strong> {{ ucfirst($scheduled->frequency ?? 'N/A') }}</div>
                                        <div><strong>Next Run:</strong> {{ $scheduled->next_run_at ? $scheduled->next_run_at->format('M d, Y H:i') : 'N/A' }}</div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('medications.reports.schedule.edit', $scheduled->id) }}" 
                                           class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="runScheduledReport({{ $scheduled->id }})" 
                                                class="btn btn-sm btn-outline-success flex-fill">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-4">
                            <i class="fas fa-clock text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-2">No scheduled reports</p>
                            <a href="{{ route('medications.reports.schedule.create') }}" class="btn btn-outline-primary btn-sm">
                                Schedule Your First Report
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Report Generation Modal --}}
<div class="modal fade" id="reportGenerationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generating Report</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Please wait while we generate your report...</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="reportProgress"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient {
    position: relative;
    overflow: hidden;
}

.bg-gradient::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
}

.table td {
    vertical-align: middle;
}

.progress {
    height: 8px;
}
</style>

<script>
// Set default dates (last 30 days)
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const lastMonth = new Date(today);
    lastMonth.setDate(lastMonth.getDate() - 30);
    
    document.getElementById('dateFrom').value = lastMonth.toISOString().split('T')[0];
    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
});

// Handle quick report form submission
document.getElementById('quickReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const reportType = formData.get('report_type');
    
    if (!reportType) {
        alert('Please select a report type');
        return;
    }
    
    generateReport(formData);
});

function generateReport(formData) {
    // Show loading modal
    new bootstrap.Modal(document.getElementById('reportGenerationModal')).show();
    
    // Simulate progress
    let progress = 0;
    const progressBar = document.getElementById('reportProgress');
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 95) {
            progress = 95;
        }
        progressBar.style.width = progress + '%';
    }, 500);
    
    // Submit the form
    fetch('/medications/reports/generate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(interval);
        progressBar.style.width = '100%';
        
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('reportGenerationModal')).hide();
            
            if (data.success) {
                // Download the report
                if (data.download_url) {
                    window.location.href = data.download_url;
                }
                
                // Show success message
                showToast('Report generated successfully!', 'success');
                
                // Reload page to show new report in history
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showToast('Error generating report: ' + (data.message || 'Unknown error'), 'error');
            }
        }, 1000);
    })
    .catch(error => {
        clearInterval(interval);
        bootstrap.Modal.getInstance(document.getElementById('reportGenerationModal')).hide();
        console.error('Error:', error);
        showToast('Error generating report', 'error');
    });
}

function runScheduledReport(scheduledId) {
    if (confirm('Are you sure you want to run this scheduled report now?')) {
        fetch(`/medications/reports/schedule/${scheduledId}/run`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Scheduled report started successfully!', 'success');
            } else {
                showToast('Error running scheduled report: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error running scheduled report', 'error');
        });
    }
}

function exportAllData() {
    if (confirm('This will export all medication data. This may take a while. Continue?')) {
        window.location.href = '/medications/reports/export-all';
    }
}

function showToast(message, type) {
    // Create and show a toast notification
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to page and show
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}
</script>
@endsection
