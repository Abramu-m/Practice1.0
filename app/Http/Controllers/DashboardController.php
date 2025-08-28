<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Doctor;
use App\Models\VisitType;
use App\Models\User;
use App\Models\Medication;
use App\Models\Investigation;
use App\Models\Prescription;
use App\Models\FinancialTransaction;
use App\Models\StoreRequisition;
use App\Models\StoreLocation;
use App\Models\MedicalService;
use App\Models\PatientCategory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Redirect to role-specific dashboards for non-admin users
        if (!($user->is_admin || $user->is_super)) {
            switch ($user->role) {
                case 'doctor':
                    return redirect()->route('dashboard.doctor');
                case 'nurse':
                    return redirect()->route('dashboard.nurse');
                case 'receptionist':
                    return redirect()->route('dashboard.receptionist');
                case 'lab_technician':
                    return redirect()->route('dashboard.lab_technician');
                case 'radiologist':
                    return redirect()->route('dashboard.radiologist');
                case 'pharmacist':
                    return redirect()->route('pharmacist.dashboard');
                default:
                    // Continue to general dashboard for unspecified roles
                    break;
            }
        }
        
        // Continue with existing dashboard logic for admin users
        // Basic statistics
        $totalPatients = Patient::count();
        $todaysVisits = PatientVisit::whereDate('visit_date', Carbon::today())->count();
        $activeDoctors = Doctor::where('status', 1)->count();
        $visitTypes = VisitType::count();
        
        // Get current user and check if they're a doctor
        $user = Auth::user();
        $currentDoctorId = null;
        
        if ($user->role === 'doctor' && $user->doctor) {
            $currentDoctorId = $user->doctor->doctor_id ?? null;
        }
        
        // Consultation statistics - filter by doctor if user is a doctor
        if ($currentDoctorId && !($user->is_admin || $user->is_super)) {
            $pendingConsultations = PatientVisit::where('visit_status', 0)
                                              ->where('doctor', $currentDoctorId)
                                              ->count();
            $activeConsultations = PatientVisit::where('visit_status', 1)
                                             ->where('doctor', $currentDoctorId)
                                             ->count();
            $recentPendingVisits = PatientVisit::with(['patientInfo', 'doctorInfo.user'])
                                              ->where('visit_status', 0)
                                              ->where('doctor', $currentDoctorId)
                                              ->orderBy('visit_date', 'desc')
                                              ->take(5)
                                              ->get();
        } else {
            // Admin users see all consultations
            $pendingConsultations = PatientVisit::where('visit_status', 0)->count();
            $activeConsultations = PatientVisit::where('visit_status', 1)->count();
            $recentPendingVisits = PatientVisit::with(['patientInfo', 'doctorInfo.user'])
                                              ->where('visit_status', 0)
                                              ->orderBy('visit_date', 'desc')
                                              ->take(5)
                                              ->get();
        }
        
        // Admin-specific stats
        $verifiedUsers = User::where('is_verified', true)->count();
        $pendingUsers = User::where('is_verified', false)->count();
        
        // Financial statistics
        $todaysRevenue = FinancialTransaction::where('transaction_type', 'income')
                                           ->whereDate('transaction_date', Carbon::today())
                                           ->sum('amount');
        $monthlyRevenue = FinancialTransaction::where('transaction_type', 'income')
                                            ->whereMonth('transaction_date', Carbon::now()->month)
                                            ->whereYear('transaction_date', Carbon::now()->year)
                                            ->sum('amount');
        $pendingPayments = Investigation::where('is_paid', false)->count() + 
                          Prescription::where('is_paid', false)->count();
        
        // Medication & Store statistics
        $totalMedications = Medication::count();
        $lowStockMedications = Medication::where('stock_quantity', '<=', DB::raw('reorder_level'))->count();
        $pendingRequisitions = StoreRequisition::whereIn('status', ['draft', 'submitted'])->count();
        $storeLocations = StoreLocation::where('is_active', true)->count();
        
        // Investigation statistics
        $totalInvestigations = Investigation::count();
        $pendingInvestigations = Investigation::whereIn('status', ['draft', 'ordered', 'collected'])->count();
        $todaysInvestigations = Investigation::whereDate('ordered_at', Carbon::today())->count();
        $completedInvestigations = Investigation::where('status', 'resulted')->count();
        
        // Medical Services statistics
        $totalMedicalServices = MedicalService::where('is_active', true)->count();
        $patientCategories = PatientCategory::where('is_active', true)->count();
        
        // Recent financial transactions for quick view
        $recentTransactions = FinancialTransaction::with(['patient', 'creator'])
                                                ->orderBy('created_at', 'desc')
                                                ->take(5)
                                                ->get();
        
        // System alerts
        $systemAlerts = [
            'low_stock' => $lowStockMedications,
            'pending_users' => $pendingUsers,
            'pending_requisitions' => $pendingRequisitions,
            'pending_payments' => $pendingPayments
        ];
        
        return view('dashboard', compact(
            // Basic stats
            'totalPatients', 
            'todaysVisits', 
            'activeDoctors', 
            'visitTypes',
            // Consultation stats
            'pendingConsultations',
            'activeConsultations', 
            'recentPendingVisits',
            // User management stats
            'verifiedUsers',
            'pendingUsers',
            // Financial stats
            'todaysRevenue',
            'monthlyRevenue',
            'pendingPayments',
            'recentTransactions',
            // Medication & Store stats
            'totalMedications',
            'lowStockMedications',
            'pendingRequisitions',
            'storeLocations',
            // Investigation stats
            'totalInvestigations',
            'pendingInvestigations',
            'todaysInvestigations',
            'completedInvestigations',
            // Medical services stats
            'totalMedicalServices',
            'patientCategories',
            // System alerts
            'systemAlerts'
        ));
    }
    
    /**
     * Doctor Dashboard
     */
    public function doctorDashboard()
    {
        $user = Auth::user();
        $doctorId = $user->doctor->doctor_id ?? null;
        
        // Doctor-specific statistics
        $todaysConsultations = PatientVisit::where('doctor', $doctorId)
                                          ->whereDate('visit_date', Carbon::today())
                                          ->count();
        
        $pendingProcedures = Investigation::where('ordered_by', $user->id)
                                         ->whereIn('status', ['draft', 'ordered', 'collected'])
                                         ->count();
        
        $activePatients = PatientVisit::where('doctor', $doctorId)
                                     ->where('visit_status', 1)
                                     ->count();
        
        // Count NHIF claims for this doctor
        $postedClaims = FinancialTransaction::where('created_by', $user->id)
                                          ->where('payment_method', 'nhif')
                                          ->whereDate('created_at', Carbon::today())
                                          ->count();
        
        // Today's appointments/visits
        $todaysAppointments = PatientVisit::with(['patientInfo'])
                                         ->where('doctor', $doctorId)
                                         ->whereDate('visit_date', Carbon::today())
                                         ->orderBy('visit_date', 'asc')
                                         ->get();
        
        // Weekly statistics
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        
        $weeklyConsultations = PatientVisit::where('doctor', $doctorId)
                                          ->whereBetween('visit_date', [$weekStart, $weekEnd])
                                          ->count();
        
        $weeklyProcedures = Investigation::where('ordered_by', $user->id)
                                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                                        ->count();
        
        $weeklyClaims = FinancialTransaction::where('created_by', $user->id)
                                          ->where('payment_method', 'nhif')
                                          ->whereBetween('created_at', [$weekStart, $weekEnd])
                                          ->count();
        
        // Patient satisfaction (placeholder - would need surveys)
        $satisfaction = '95%';
        
        $weeklyStats = [
            'consultations' => $weeklyConsultations,
            'procedures' => $weeklyProcedures,
            'claims' => $weeklyClaims,
            'satisfaction' => $satisfaction
        ];
        
        // System alerts
        $alerts = [];
        
        return view('dashboards.doctor', compact(
            'todaysConsultations',
            'pendingProcedures', 
            'activePatients',
            'postedClaims',
            'todaysAppointments',
            'weeklyStats',
            'alerts'
        ));
    }
    
    /**
     * Nurse Dashboard
     */
    public function nurseDashboard()
    {
        // Triage patients (patients waiting for vitals)
        $triagePatients = PatientVisit::whereDoesntHave('vitalSigns')
                                     ->where('visit_status', 0)
                                     ->whereDate('visit_date', Carbon::today())
                                     ->count();
        
        // Vitals recorded today
        $vitalsRecorded = DB::table('vital_signs')
                           ->whereDate('created_at', Carbon::today())
                           ->count();
        
        // Low supplies (medications below reorder level)
        $lowSupplies = Medication::where('stock_quantity', '<=', DB::raw('reorder_level'))
                                ->count();
        
        // CTC patients (assuming HIV/AIDS care)
        $ctcPatients = Patient::where('SchemeName', 'like', '%CTC%')
                             ->orWhere('SchemeName', 'like', '%HIV%')
                             ->orWhere('membership_number', 'like', 'CTC%')
                             ->count();
        
        // Priority patients by status
        $criticalPatients = PatientVisit::where('visit_status', 0)
                                       ->whereDate('visit_date', Carbon::today())
                                       ->whereHas('vitalSigns', function($q) {
                                           $q->where('temperature', '>', 38.5)
                                             ->orWhere('systolic_bp', '>', 180)
                                             ->orWhere('diastolic_bp', '>', 110);
                                       })->count();
        
        $urgentPatients = PatientVisit::where('visit_status', 0)
                                     ->whereDate('visit_date', Carbon::today())
                                     ->count() - $criticalPatients;
        
        $stablePatients = PatientVisit::where('visit_status', 1)
                                     ->whereDate('visit_date', Carbon::today())
                                     ->count();
        
        $priorityPatients = [
            'critical' => $criticalPatients,
            'urgent' => $urgentPatients,
            'stable' => $stablePatients
        ];
        
        // CTC Statistics
        $ctcScreenings = Investigation::whereHas('medicalService', function($q) {
                                      $q->where('name', 'like', '%HIV%')
                                        ->orWhere('name', 'like', '%CD4%');
                                   })
                                   ->whereDate('created_at', Carbon::today())
                                   ->count();
        
        $ctcDispensed = Prescription::whereHas('medication', function($q) {
                                   $q->where('generic_name', 'like', '%ARV%')
                                     ->orWhere('generic_name', 'like', '%Efavirenz%')
                                     ->orWhere('brand_name', 'like', '%ARV%')
                                     ->orWhere('brand_name', 'like', '%Efavirenz%');
                               })
                               ->whereDate('created_at', Carbon::today())
                               ->count();
        
        $cd4Results = Investigation::whereHas('medicalService', function($q) {
                                   $q->where('name', 'like', '%CD4%');
                               })
                               ->where('status', 'resulted')
                               ->whereDate('created_at', Carbon::today())
                               ->count();
        
        $ctcStats = [
            'screenings' => $ctcScreenings,
            'dispensed' => $ctcDispensed,
            'cd4_results' => $cd4Results
        ];
        
        // Performance metrics (percentages)
        $vitalsCompleted = 75; // Placeholder calculation
        $medicationRounds = 90; // Placeholder calculation
        $nursingCare = 85; // Placeholder calculation
        
        $suppliesStatus = $lowSupplies > 5 ? 'low' : ($lowSupplies > 0 ? 'medium' : 'good');
        $nextRounds = '14:00'; // Would be calculated from scheduling
        
        $recentCTCActivities = []; // Would be populated with recent CTC activities
        
        return view('dashboards.nurse', compact(
            'triagePatients',
            'vitalsRecorded',
            'lowSupplies',
            'ctcPatients',
            'priorityPatients',
            'ctcStats',
            'vitalsCompleted',
            'medicationRounds',
            'nursingCare',
            'suppliesStatus',
            'nextRounds',
            'recentCTCActivities'
        ));
    }
    
    /**
     * Receptionist Dashboard
     */
    public function receptionistDashboard()
    {
        // Today's registrations (new patients)
        $todaysRegistrations = Patient::whereDate('created_at', Carbon::today())->count();
        
        // Today's revenue
        $todaysRevenue = FinancialTransaction::where('transaction_type', 'income')
                                           ->whereDate('transaction_date', Carbon::today())
                                           ->sum('amount');
        
        // Pending visits (not yet started consultations)
        $pendingVisits = PatientVisit::where('visit_status', 0)->count();
        
        // Ready results (completed investigations)
        $readyResults = Investigation::where('status', 'resulted')
                                   ->whereNotNull('resulted_at')
                                   ->count();
        
        // Cashier statistics
        $dailyTransactions = FinancialTransaction::where('transaction_type', 'income')
                                               ->whereDate('transaction_date', Carbon::today())
                                               ->count();
        
        $receiptsIssued = DB::table('payment_receipts')
                           ->whereDate('receipt_date', Carbon::today())
                           ->where('status', '!=', 'cancelled')
                           ->count();
        
        // Daily percentage (placeholder calculation)
        $dailyPercentage = 75;
        
        $cashierStats = [
            'daily_total' => $todaysRevenue,
            'transactions' => $dailyTransactions,
            'receipts' => $receiptsIssued,
            'daily_percentage' => $dailyPercentage
        ];
        
        $recentActivities = []; // Would be populated with recent activities
        
        // Today's schedule
        $todaysSchedule = PatientVisit::with(['patientInfo'])
                                     ->whereDate('visit_date', Carbon::today())
                                     ->orderBy('visit_date', 'asc')
                                     ->take(10)
                                     ->get();
        
        // Schedule statistics
        $scheduledVisits = PatientVisit::whereDate('visit_date', Carbon::today())->count();
        $inProgressVisits = PatientVisit::where('visit_status', 1)->whereDate('visit_date', Carbon::today())->count();
        $completedVisits = PatientVisit::where('visit_status', 2)->whereDate('visit_date', Carbon::today())->count();
        
        $scheduleStats = [
            'scheduled' => $scheduledVisits,
            'in_progress' => $inProgressVisits,
            'completed' => $completedVisits
        ];
        
        // Operations statistics
        $newPatients = Patient::whereDate('created_at', Carbon::today())->count();
        $returnPatients = PatientVisit::whereDate('visit_date', Carbon::today())
                                     ->whereHas('patientInfo', function($q) {
                                         $q->whereDate('created_at', '<', Carbon::today());
                                     })->count();
        
        $cashPayments = FinancialTransaction::where('payment_method', 'cash')
                                          ->whereDate('transaction_date', Carbon::today())
                                          ->count();
        
        $insuranceClaims = FinancialTransaction::where('payment_method', 'nhif')
                                             ->whereDate('transaction_date', Carbon::today())
                                             ->count();
        
        $operationsStats = [
            'new_patients' => $newPatients,
            'return_patients' => $returnPatients,
            'cash_payments' => $cashPayments,
            'insurance_claims' => $insuranceClaims
        ];
        
        // Payment statistics (percentages)
        $totalPayments = $cashPayments + $insuranceClaims;
        $paymentStats = [
            'cash_percentage' => $totalPayments > 0 ? round(($cashPayments / $totalPayments) * 100) : 0,
            'insurance_percentage' => $totalPayments > 0 ? round(($insuranceClaims / $totalPayments) * 100) : 0,
            'other_percentage' => 5 // Placeholder for other payment methods
        ];
        
        return view('dashboards.receptionist', compact(
            'todaysRegistrations',
            'todaysRevenue',
            'pendingVisits',
            'readyResults',
            'cashierStats',
            'recentActivities',
            'todaysSchedule',
            'scheduleStats',
            'operationsStats',
            'paymentStats'
        ));
    }
    
    /**
     * Lab Technician Dashboard
     */
    public function labTechnicianDashboard()
    {
        // Pending tests
        $pendingTests = Investigation::whereIn('status', ['draft', 'ordered', 'collected'])->count();
        
        // Completed today
        $completedToday = Investigation::where('status', 'resulted')
                                     ->whereDate('updated_at', Carbon::today())
                                     ->count();
        
        // QC due (placeholder - would need QC system)
        $qcDue = 2; // Placeholder
        
        // Urgent tests (high priority)
        $urgentTests = Investigation::whereIn('status', ['draft', 'ordered', 'collected'])
                                   ->where('priority', 'urgent')
                                   ->count();
        
        // Test queue breakdown
        $criticalTests = Investigation::whereIn('status', ['draft', 'ordered', 'collected'])
                                     ->where('priority', 'stat')
                                     ->count();
        
        $routineTests = Investigation::whereIn('status', ['draft', 'ordered', 'collected'])
                                    ->where('priority', 'routine')
                                    ->count();
        
        $nonUrgentTests = Investigation::whereIn('status', ['draft', 'ordered', 'collected'])
                                      ->where('priority', 'routine')
                                      ->count();
        
        $testQueue = [
            'critical' => $criticalTests,
            'routine' => $routineTests,
            'non_urgent' => $nonUrgentTests
        ];
        
        // Equipment status (placeholder - would need equipment management)
        $equipmentStatus = [
            'hematology' => 'operational',
            'chemistry' => 'operational', 
            'serology' => 'maintenance',
            'microbiology' => 'operational'
        ];
        
        // Performance statistics
        $totalTests = Investigation::whereDate('created_at', Carbon::today())->count();
        $completionRate = $totalTests > 0 ? round(($completedToday / $totalTests) * 100) : 0;
        $avgTime = 15; // Placeholder for average processing time
        $accuracy = 98; // Placeholder for accuracy rate
        $efficiency = 92; // Placeholder for efficiency rate
        
        $performanceStats = [
            'completion_rate' => $completionRate,
            'total_tests' => $totalTests,
            'avg_time' => $avgTime,
            'accuracy' => $accuracy,
            'efficiency' => $efficiency
        ];
        
        // Test categories (would be calculated from medical services)
        $testCategories = [
            'hematology' => 35,
            'chemistry' => 40,
            'serology' => 15,
            'microbiology' => 10
        ];
        
        // QC Statistics (placeholder)
        $qcStats = [
            'passed' => 8,
            'failed' => 1
        ];
        
        $qcSchedule = []; // Would be populated with QC schedule
        
        // Specialized tests (TB, Leprosy)
        $tbTests = Investigation::whereHas('medicalService', function($q) {
                                $q->where('name', 'like', '%TB%')
                                  ->orWhere('name', 'like', '%Tuberculosis%');
                            })
                            ->whereDate('created_at', Carbon::today())
                            ->count();
        
        $leprosyTests = Investigation::whereHas('medicalService', function($q) {
                                    $q->where('name', 'like', '%Leprosy%');
                                })
                                ->whereDate('created_at', Carbon::today())
                                ->count();
        
        $specializedStats = [
            'tb_tests' => $tbTests,
            'leprosy_tests' => $leprosyTests
        ];
        
        $recentSpecializedTests = []; // Would be populated with recent specialized tests
        
        return view('dashboards.lab_technician', compact(
            'pendingTests',
            'completedToday',
            'qcDue',
            'urgentTests',
            'testQueue',
            'equipmentStatus',
            'performanceStats',
            'testCategories',
            'qcStats',
            'qcSchedule',
            'specializedStats',
            'recentSpecializedTests'
        ));
    }

    public function radiologistDashboard()
    {
        $user = Auth::user();
        
        // Today's statistics
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Radiology investigations statistics
        $todaysRadiologyOrders = Investigation::whereDate('created_at', $today)
            ->whereHas('medicalService.serviceCategory', function ($q) {
                $q->where('name', 'LIKE', '%Radiology%');
            })
            ->count();
            
        $pendingRadiologyReports = Investigation::where('status', 'pending')
            ->whereHas('medicalService.serviceCategory', function ($q) {
                $q->where('name', 'LIKE', '%Radiology%');
            })
            ->count();
            
        $completedToday = Investigation::whereDate('resulted_at', $today)
            ->whereHas('medicalService.serviceCategory', function ($q) {
                $q->where('name', 'LIKE', '%Radiology%');
            })
            ->count();
            
        $urgentStudies = Investigation::where('priority', 'urgent')
            ->where('status', 'pending')
            ->whereHas('medicalService.serviceCategory', function ($q) {
                $q->where('name', 'LIKE', '%Radiology%');
            })
            ->count();
        
        // Weekly performance stats
        $weeklyStats = [
            'total_studies' => Investigation::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->whereHas('medicalService.serviceCategory', function ($q) {
                    $q->where('name', 'LIKE', '%Radiology%');
                })
                ->count(),
            'completed_studies' => Investigation::whereBetween('resulted_at', [$startOfWeek, $endOfWeek])
                ->whereHas('medicalService.serviceCategory', function ($q) {
                    $q->where('name', 'LIKE', '%Radiology%');
                })
                ->count(),
            'average_turnaround' => '45 min', // This would be calculated from actual data
            'patient_satisfaction' => '94%'
        ];
        
        // Study categories breakdown
        $studyCategories = [
            'X-Ray' => Investigation::where('status', '!=', 'cancelled')
                ->whereHas('medicalService', function ($q) {
                    $q->where('name', 'LIKE', '%X-Ray%')
                      ->orWhere('name', 'LIKE', '%Radiograph%');
                })
                ->whereDate('created_at', $today)
                ->count(),
            'CT Scan' => Investigation::where('status', '!=', 'cancelled')
                ->whereHas('medicalService', function ($q) {
                    $q->where('name', 'LIKE', '%CT%')
                      ->orWhere('name', 'LIKE', '%Computed Tomography%');
                })
                ->whereDate('created_at', $today)
                ->count(),
            'MRI' => Investigation::where('status', '!=', 'cancelled')
                ->whereHas('medicalService', function ($q) {
                    $q->where('name', 'LIKE', '%MRI%')
                      ->orWhere('name', 'LIKE', '%Magnetic Resonance%');
                })
                ->whereDate('created_at', $today)
                ->count(),
            'Ultrasound' => Investigation::where('status', '!=', 'cancelled')
                ->whereHas('medicalService', function ($q) {
                    $q->where('name', 'LIKE', '%Ultrasound%')
                      ->orWhere('name', 'LIKE', '%US%');
                })
                ->whereDate('created_at', $today)
                ->count()
        ];
        
        // Recent urgent studies
        $urgentStudiesList = Investigation::with(['patient', 'medicalService', 'doctor'])
            ->where('priority', 'urgent')
            ->where('status', 'pending')
            ->whereHas('medicalService.serviceCategory', function ($q) {
                $q->where('name', 'LIKE', '%Radiology%');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Today's schedule/queue
        $todaysQueue = Investigation::with(['patient', 'medicalService', 'doctor'])
            ->whereDate('created_at', $today)
            ->where('status', 'pending')
            ->whereHas('medicalService.serviceCategory', function ($q) {
                $q->where('name', 'LIKE', '%Radiology%');
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(15)
            ->get();
        
        // Equipment status (mock data - would be integrated with actual equipment monitoring)
        $equipmentStatus = [
            'X-Ray Machine #1' => ['status' => 'operational', 'last_maintenance' => '2 days ago'],
            'CT Scanner #1' => ['status' => 'operational', 'last_maintenance' => '1 week ago'],
            'MRI #1' => ['status' => 'maintenance', 'last_maintenance' => 'Today'],
            'Ultrasound #1' => ['status' => 'operational', 'last_maintenance' => '3 days ago'],
            'Ultrasound #2' => ['status' => 'operational', 'last_maintenance' => '1 week ago']
        ];
        
        // Quality metrics
        $qualityMetrics = [
            'repeat_rate' => '2.1%', // Percentage of studies requiring repeat
            'critical_findings' => Investigation::where('notes', 'LIKE', '%critical%')
                ->whereHas('medicalService.serviceCategory', function ($q) {
                    $q->where('name', 'LIKE', '%Radiology%');
                })
                ->whereDate('resulted_at', $today)
                ->count(),
            'turnaround_target' => '85%', // Percentage meeting turnaround targets
            'radiologist_workload' => Investigation::where('resulted_by', $user->id)
                ->whereDate('resulted_at', $today)
                ->count()
        ];
        
        // Alerts and notifications
        $alerts = [];
        
        if ($urgentStudies > 5) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High Urgent Studies',
                'message' => "You have {$urgentStudies} urgent studies pending review."
            ];
        }
        
        if ($pendingRadiologyReports > 20) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Reports',
                'message' => "You have {$pendingRadiologyReports} pending radiology reports."
            ];
        }
        
        return view('dashboards.radiologist', compact(
            'todaysRadiologyOrders',
            'pendingRadiologyReports', 
            'completedToday',
            'urgentStudies',
            'weeklyStats',
            'studyCategories',
            'urgentStudiesList',
            'todaysQueue',
            'equipmentStatus',
            'qualityMetrics',
            'alerts'
        ));
    }
}
