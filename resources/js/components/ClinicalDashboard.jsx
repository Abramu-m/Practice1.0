import React, { useState, useEffect } from 'react';
import axios from 'axios';

const ClinicalDashboard = () => {
    const [dashboard, setDashboard] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchDashboard();
    }, []);

    const fetchDashboard = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/clinical/dashboard');
            setDashboard(response.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load dashboard');
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div className="text-center p-4">Loading dashboard...</div>;
    if (error) return <div className="alert alert-danger">{error}</div>;

    return (
        <div className="clinical-dashboard">
            <div className="row mb-4">
                <div className="col-md-12">
                    <h2 className="mb-3">Clinical Dashboard</h2>
                </div>
            </div>

            {/* Summary Cards */}
            <div className="row mb-4">
                <div className="col-md-3">
                    <div className="card bg-primary text-white">
                        <div className="card-body">
                            <h5 className="card-title">Active Consultations</h5>
                            <h2 className="mb-0">{dashboard?.active_consultations || 0}</h2>
                        </div>
                    </div>
                </div>
                <div className="col-md-3">
                    <div className="card bg-warning text-white">
                        <div className="card-body">
                            <h5 className="card-title">Pending Prescriptions</h5>
                            <h2 className="mb-0">{dashboard?.pending_prescriptions || 0}</h2>
                        </div>
                    </div>
                </div>
                <div className="col-md-3">
                    <div className="card bg-info text-white">
                        <div className="card-body">
                            <h5 className="card-title">Pending Investigations</h5>
                            <h2 className="mb-0">{dashboard?.pending_investigations || 0}</h2>
                        </div>
                    </div>
                </div>
                <div className="col-md-3">
                    <div className="card bg-success text-white">
                        <div className="card-body">
                            <h5 className="card-title">Patients Today</h5>
                            <h2 className="mb-0">{dashboard?.patients_today || 0}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {/* Recent Consultations */}
            <div className="row mb-4">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-header">
                            <h5 className="mb-0">Recent Consultations</h5>
                        </div>
                        <div className="card-body">
                            {dashboard?.recent_consultations?.length > 0 ? (
                                <div className="table-responsive">
                                    <table className="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date</th>
                                                <th>Chief Complaint</th>
                                                <th>Vitals</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {dashboard.recent_consultations.map((consultation, index) => (
                                                <tr key={index}>
                                                    <td>
                                                        <strong>{consultation.patient?.full_name}</strong><br />
                                                        <small className="text-muted">{consultation.patient?.registration_number}</small>
                                                    </td>
                                                    <td>
                                                        {new Date(consultation.consultation_date).toLocaleDateString()}
                                                    </td>
                                                    <td>
                                                        {consultation.chief_complaint ? 
                                                            <span className="text-truncate" style={{maxWidth: '150px', display: 'inline-block'}}>
                                                                {consultation.chief_complaint}
                                                            </span> : 
                                                            <span className="text-muted">-</span>
                                                        }
                                                    </td>
                                                    <td>
                                                        {consultation.vital_signs?.length > 0 ? (
                                                            <div>
                                                                <small>
                                                                    Temp: {consultation.vital_signs[0].temperature}°C<br />
                                                                    BP: {consultation.vital_signs[0].blood_pressure}
                                                                </small>
                                                            </div>
                                                        ) : (
                                                            <span className="text-warning">No vitals</span>
                                                        )}
                                                    </td>
                                                    <td>
                                                        <button className="btn btn-sm btn-primary">
                                                            View
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-muted mb-0">No recent consultations</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Alerts */}
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-header">
                            <h5 className="mb-0">Alerts</h5>
                        </div>
                        <div className="card-body">
                            {/* Low Stock Medications */}
                            {dashboard?.low_stock_medications?.length > 0 && (
                                <div className="mb-3">
                                    <h6 className="text-warning">
                                        <i className="fas fa-exclamation-triangle"></i> Low Stock Medications
                                    </h6>
                                    {dashboard.low_stock_medications.map((medication, index) => (
                                        <div key={index} className="d-flex justify-content-between align-items-center mb-1">
                                            <small>{medication.name}</small>
                                            <span className="badge badge-warning">{medication.stock_quantity}</span>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Urgent Investigations */}
                            {dashboard?.urgent_investigations?.length > 0 && (
                                <div className="mb-3">
                                    <h6 className="text-danger">
                                        <i className="fas fa-clock"></i> Urgent Investigations
                                    </h6>
                                    {dashboard.urgent_investigations.map((investigation, index) => (
                                        <div key={index} className="mb-2">
                                            <div className="d-flex justify-content-between">
                                                <small><strong>{investigation.patient?.full_name}</strong></small>
                                                <span className="badge badge-danger">URGENT</span>
                                            </div>
                                            <small className="text-muted">{investigation.medical_service?.name}</small>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {(!dashboard?.low_stock_medications?.length && !dashboard?.urgent_investigations?.length) && (
                                <p className="text-muted mb-0">No alerts at this time</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ClinicalDashboard;
