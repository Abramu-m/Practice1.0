@extends('layouts.app_main_layout')

@section('page_title', 'Clinical Dashboard')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Clinical Dashboard</h4>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <!-- Dashboard Stats -->
                    <div class="row mb-4" id="dashboard-stats">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="active-consultations">-</h4>
                                            <p class="mb-0">Active Consultations</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-stethoscope fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="pending-prescriptions">-</h4>
                                            <p class="mb-0">Pending Prescriptions</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-pills fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="pending-investigations">-</h4>
                                            <p class="mb-0">Pending Investigations</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-flask fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0" id="patients-today">-</h4>
                                            <p class="mb-0">Patients Today</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-primary btn-block" onclick="startNewConsultation()">
                                                <i class="fas fa-plus"></i> New Consultation
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-success btn-block" onclick="recordVitalSigns()">
                                                <i class="fas fa-heartbeat"></i> Record Vitals
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-warning btn-block" onclick="createPrescription()">
                                                <i class="fas fa-prescription-bottle"></i> Prescribe
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-info btn-block" onclick="orderInvestigation()">
                                                <i class="fas fa-microscope"></i> Order Test
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Consultations -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Consultations</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="consultations-table">
                                            <thead>
                                                <tr>
                                                    <th>Patient</th>
                                                    <th>Date</th>
                                                    <th>Chief Complaint</th>
                                                    <th>Vitals</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="consultations-tbody">
                                                <!-- Dynamic content -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Low Stock Medications -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Low Stock Medications</h6>
                                </div>
                                <div class="card-body">
                                    <div id="low-stock-medications">
                                        <!-- Dynamic content -->
                                    </div>
                                </div>
                            </div>

                            <!-- Urgent Investigations -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Urgent Investigations</h6>
                                </div>
                                <div class="card-body">
                                    <div id="urgent-investigations">
                                        <!-- Dynamic content -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start Consultation Modal -->
<div class="modal fade" id="consultationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Consultation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="consultationForm">
                    <div class="mb-3">
                        <label class="form-label">Patient MR Number</label>
                        <input type="text" class="form-control" name="patient_registration_number" placeholder="e.g., MR-2025-000001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Doctor</label>
                        <select class="form-control" name="doctor_id" required>
                            <option value="">Select Doctor</option>
                            <!-- Dynamic options -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chief Complaint</label>
                        <textarea class="form-control" name="chief_complaint" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitConsultation()">Start Consultation</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadDashboard();
    
    // Auto refresh every 5 minutes
    setInterval(loadDashboard, 300000);
});

function loadDashboard() {
    $.ajax({
        url: '/api/clinical/dashboard',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + getAuthToken(),
            'Accept': 'application/json'
        },
        success: function(response) {
            updateDashboardStats(response);
            updateRecentConsultations(response.recent_consultations);
            updateLowStockMedications(response.low_stock_medications);
            updateUrgentInvestigations(response.urgent_investigations);
        },
        error: function(xhr) {
            console.error('Failed to load dashboard:', xhr);
            showAlert('Failed to load dashboard data', 'danger');
        }
    });
}

function updateDashboardStats(data) {
    $('#active-consultations').text(data.active_consultations || 0);
    $('#pending-prescriptions').text(data.pending_prescriptions || 0);
    $('#pending-investigations').text(data.pending_investigations || 0);
    $('#patients-today').text(data.patients_today || 0);
}

function updateRecentConsultations(consultations) {
    const tbody = $('#consultations-tbody');
    tbody.empty();
    
    if (!consultations || consultations.length === 0) {
        tbody.append('<tr><td colspan="6" class="text-center">No recent consultations</td></tr>');
        return;
    }
    
    consultations.forEach(consultation => {
        const vitals = consultation.vital_signs && consultation.vital_signs.length > 0 
            ? consultation.vital_signs[0] : null;
        
        const vitalsInfo = vitals 
            ? `T: ${vitals.temperature}°C, P: ${vitals.pulse_rate}, BP: ${vitals.blood_pressure || 'N/A'}`
            : 'No vitals';
            
        const row = `
            <tr>
                <td>${consultation.patient ? consultation.patient.first_name + ' ' + consultation.patient.last_name : 'Unknown'}</td>
                <td>${new Date(consultation.consultation_date).toLocaleDateString()}</td>
                <td>${consultation.chief_complaint || 'N/A'}</td>
                <td><small>${vitalsInfo}</small></td>
                <td><span class="badge bg-${consultation.status === 'active' ? 'success' : 'secondary'}">${consultation.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewConsultation(${consultation.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateLowStockMedications(medications) {
    const container = $('#low-stock-medications');
    container.empty();
    
    if (!medications || medications.length === 0) {
        container.append('<p class="text-muted">No low stock medications</p>');
        return;
    }
    
    medications.forEach(medication => {
        const item = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>${medication.name}</strong><br>
                    <small class="text-muted">Stock: ${medication.stock_quantity}</small>
                </div>
                <span class="badge ${medication.stock_badge_class || 'bg-warning'}">${medication.stock_status}</span>
            </div>
        `;
        container.append(item);
    });
}

function updateUrgentInvestigations(investigations) {
    const container = $('#urgent-investigations');
    container.empty();
    
    if (!investigations || investigations.length === 0) {
        container.append('<p class="text-muted">No urgent investigations</p>');
        return;
    }
    
    investigations.forEach(investigation => {
        const item = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>${investigation.medical_service ? investigation.medical_service.name : 'Unknown'}</strong><br>
                    <small class="text-muted">${investigation.patient ? investigation.patient.first_name + ' ' + investigation.patient.last_name : 'Unknown'}</small>
                </div>
                <span class="badge bg-danger">${investigation.priority}</span>
            </div>
        `;
        container.append(item);
    });
}

function refreshDashboard() {
    loadDashboard();
    showAlert('Dashboard refreshed', 'success');
}

function startNewConsultation() {
    $('#consultationModal').modal('show');
}

function submitConsultation() {
    const formData = new FormData(document.getElementById('consultationForm'));
    const data = Object.fromEntries(formData.entries());
    
    $.ajax({
        url: '/api/clinical/consultations',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + getAuthToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(data),
        success: function(response) {
            $('#consultationModal').modal('hide');
            showAlert('Consultation started successfully', 'success');
            loadDashboard();
        },
        error: function(xhr) {
            showAlert('Failed to start consultation: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

function viewConsultation(consultationId) {
    window.location.href = `/consultations/${consultationId}`;
}

function recordVitalSigns() {
    showAlert('Vital signs recording feature coming soon', 'info');
}

function createPrescription() {
    showAlert('Prescription feature coming soon', 'info');
}

function orderInvestigation() {
    showAlert('Investigation ordering feature coming soon', 'info');
}

function getAuthToken() {
    // For now, return empty - in real app, get from localStorage or auth system
    return '';
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert to top of container
    $('.container-fluid').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection
