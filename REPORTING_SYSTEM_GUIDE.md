# Practice 1.0 Reporting System - Developer Guide

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [System Components](#system-components)
4. [File Structure](#file-structure)
5. [Database Models](#database-models)
6. [Services](#services)
7. [Controllers](#controllers)
8. [Views](#views)
9. [Routes](#routes)
10. [Navigation](#navigation)
11. [Adding New Reports](#adding-new-reports)
12. [Best Practices](#best-practices)
13. [Common Issues & Solutions](#common-issues--solutions)

---

## Overview

The Practice 1.0 Reporting System is a comprehensive module for generating disease surveillance, pharmaceutical, and laboratory reports. It follows a **service-based architecture** with clear separation of concerns between data aggregation (Services), request handling (Controllers), data presentation (Views), and access control (Routes).

### Key Features
- ✅ Monthly and weekly report generation
- ✅ Age/gender demographic breakdown
- ✅ PDF export functionality
- ✅ ICD-10 code mapping for disease surveillance
- ✅ Pharmacy and inventory tracking
- ✅ Laboratory test management
- ✅ Admin-only access with middleware protection

### Report Categories

**Disease Surveillance:**
- Malaria Monthly Report
- IDSR Weekly Report
- STI/STD Monthly Report
- DTC (Diarrhea Treatment Center) Monthly Report

**Pharmacy & Inventory:**
- Medicines Monthly Report
- Tracer Medicines Report
- Low Stock Medicines Alert
- Monthly Lab Reports (8 sub-types):
  - Hematology
  - Blood Transfusion
  - Clinical Chemistry
  - Microbiology
  - Serology
  - Parasitology

---

## Architecture

### High-Level Flow Diagram

```
HTTP Request (GET /admin/reports/[report-type])
         ↓
    Routes (web.php)
         ↓
    Middleware (auth + admin)
         ↓
    Controller Method
         ↓
    Service Layer (Data Aggregation)
         ↓
    Database Queries
         ↓
    Data Processing
         ↓
    View Rendering (HTML)
    or PDF Generation
         ↓
    HTTP Response
```

### Design Patterns Used

1. **Service Layer Pattern**
   - Separates business logic from controllers
   - Reusable across multiple controllers
   - Testable in isolation
   - Clean code organization

2. **Inheritance/Base Classes**
   - `BaseReportService` - Common functionality
   - All report services extend this base

3. **Middleware Pattern**
   - Route-level authentication (`auth`)
   - Admin authorization (`EnsureUserIsAdmin`)
   - Applied at route group level for security

4. **MVC Pattern**
   - Models for database access
   - Controllers for request handling
   - Views for presentation

---

## System Components

### 1. Database/Models

**Core Models:**
- `AgeGroup` - Age demographic grouping
- `IdSRCategory` - IDSR disease categories
- `MedicalService` - Laboratory services
- `ServiceCategory` - Service grouping
- `Investigation` - Lab test records
- `Patient` - Patient information
- `PatientVisit` - Clinic visit records
- `IcdDiagnosis` - Disease diagnoses with ICD-10 codes
- `Medication` - Pharmacy items
- `Prescription` - Medication prescriptions

### 2. Services (Business Logic Layer)

**Disease Surveillance Services:**
- `MalariaReportService` - Malaria case aggregation
- `IdSRReportService` - IDSR disease surveillance
- `STDSTIReportService` - STI/STD case tracking
- `DTCReportService` - Diarrhea cases
- `BaseReportService` - Abstract base with common methods

**Pharmacy Services:**
- `MedicineReportService` - Medication consumption and inventory

**Laboratory Services:**
- `LabReportService` - All lab tests summary
- `LabHematologyReportService` - Hematology tests
- `LabBloodTransfusionReportService` - Blood transfusion tests
- `LabClinicalChemistryReportService` - Chemistry tests
- `LabMicrobiologyReportService` - Microbiology tests
- `LabSerologyReportService` - Serology tests
- `LabParasitologyReportService` - Parasitology tests

### 3. Controller

**File:** `app/Http/Controllers/AdminReportController.php`

**Methods:**
- `index()` - Reports dashboard
- `malariaMonthly()` - Malaria report
- `idsrWeekly()` - IDSR report
- `stdStiMonthly()` - STI/STD report
- `medicinesMonthly()` - Medicines report
- `tracerMedicines()` - Tracer medicines report
- `lowStockMedicines()` - Low stock alert report
- `dtcMonthly()` - DTC report
- `monthlyLabReports()` - All lab tests summary
- `labHematology()` - Hematology report
- `labBloodTransfusion()` - Blood transfusion report
- `labClinicalChemistry()` - Clinical chemistry report
- `labMicrobiology()` - Microbiology report
- `labSerology()` - Serology report
- `labParasitology()` - Parasitology report
- `downloadPdf()` - PDF generation helper

### 4. Views (Presentation Layer)

**HTML Views:** `resources/views/admin/reports/*.blade.php`

**PDF Views:** `resources/views/admin/reports/pdfs/*.blade.php`

**Shared Elements:**
- Month/year selection dropdowns
- Summary statistics cards
- Detailed data tables
- Filter buttons
- PDF download buttons
- Navigation links

---

## File Structure

```
Practice1.0/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AdminReportController.php         [14 methods]
│   │   └── Middleware/
│   │       └── EnsureUserIsAdmin.php             [Admin authorization]
│   │
│   ├── Models/
│   │   ├── AgeGroup.php
│   │   ├── IdSRCategory.php
│   │   ├── MedicalService.php
│   │   ├── ServiceCategory.php
│   │   ├── Investigation.php
│   │   ├── Patient.php
│   │   ├── PatientVisit.php
│   │   └── IcdDiagnosis.php
│   │
│   ├── Services/
│   │   ├── BaseReportService.php                 [Abstract base]
│   │   ├── MalariaReportService.php              [Disease surveillance]
│   │   ├── IdSRReportService.php
│   │   ├── STDSTIReportService.php
│   │   ├── DTCReportService.php
│   │   ├── MedicineReportService.php             [Pharmacy]
│   │   ├── LabReportService.php                  [Laboratory]
│   │   ├── LabHematologyReportService.php        [Lab sub-types]
│   │   ├── LabBloodTransfusionReportService.php
│   │   ├── LabClinicalChemistryReportService.php
│   │   ├── LabMicrobiologyReportService.php
│   │   ├── LabSerologyReportService.php
│   │   └── LabParasitologyReportService.php
│   │
│   └── Traits/
│       └── AgeCalculatorTrait.php                [Age calculation helpers]
│
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── reports/
│       │   │   ├── index.blade.php               [Dashboard]
│       │   │   ├── malaria-monthly.blade.php     [HTML views]
│       │   │   ├── idsr-weekly.blade.php
│       │   │   ├── std-sti-monthly.blade.php
│       │   │   ├── medicines-monthly.blade.php
│       │   │   ├── tracer-medicines.blade.php
│       │   │   ├── low-stock-medicines.blade.php
│       │   │   ├── dtc-monthly.blade.php
│       │   │   ├── monthly-lab-reports.blade.php
│       │   │   ├── lab-hematology.blade.php
│       │   │   ├── lab-blood-transfusion.blade.php
│       │   │   ├── lab-clinical-chemistry.blade.php
│       │   │   ├── lab-microbiology.blade.php
│       │   │   ├── lab-serology.blade.php
│       │   │   ├── lab-parasitology.blade.php
│       │   │   │
│       │   │   └── pdfs/                         [PDF views]
│       │   │       ├── malaria-monthly.blade.php
│       │   │       ├── idsr-weekly.blade.php
│       │   │       ├── std-sti-monthly.blade.php
│       │   │       ├── medicines-monthly.blade.php
│       │   │       ├── tracer-medicines.blade.php
│       │   │       ├── low-stock-medicines.blade.php
│       │   │       ├── dtc-monthly.blade.php
│       │   │       ├── monthly-lab-reports.blade.php
│       │   │       ├── lab-hematology.blade.php
│       │   │       ├── lab-blood-transfusion.blade.php
│       │   │       ├── lab-clinical-chemistry.blade.php
│       │   │       ├── lab-microbiology.blade.php
│       │   │       ├── lab-serology.blade.php
│       │   │       └── lab-parasitology.blade.php
│       │   │
│       │   └── layouts/
│       │       └── role_specific/
│       │           └── admin.blade.php            [Navigation menu]
│       │
│       └── layouts/
│           └── app_main_layout.blade.php          [Main layout]
│
├── routes/
│   └── web.php                                    [15 routes defined]
│
└── database/
    └── migrations/
        ├── 2026_06_04_100000_create_age_groups_table.php
        └── 2026_06_04_100001_create_idsr_categories_table.php

```

---

## Database Models

### AgeGroup Model
```php
// Properties
id, name (string), min_age_days (int), max_age_days (int), 
sort_order (int), is_active (boolean)

// Key Methods
findByDateOfBirth($dob) - Determine age group from DOB
containsAgeInDays($days) - Check if age falls in group
containsAgeInYears($years) - Check if age falls in group
containsAgeInMonths($months) - Check if age falls in group
```

### IdSRCategory Model
```php
// Properties
id, name (string), description (text), icd_codes (json), is_active (boolean)

// Key Methods
getIcdCodesArray() - Parse ICD codes from JSON
```

### MedicalService Model
```php
// Properties
id, name (string), service_category_id (FK), min_value (decimal), 
max_value (decimal), unit (string), is_active (boolean)

// Key Relationships
serviceCategory() - BelongsTo ServiceCategory
investigations() - HasMany Investigation
```

### Investigation Model
```php
// Properties
id, visit_id (FK), medical_service_id (FK), result_value (string),
result_unit (string), status (string), cancelled_at (timestamp)

// Key Relationships
visit() - BelongsTo PatientVisit
medicalService() - BelongsTo MedicalService
```

---

## Services

### BaseReportService (Abstract)

**Purpose:** Provide common functionality for all report services

**Key Properties:**
```php
protected $date_from;     // Start date for report period
protected $date_to;       // End date for report period
protected $facility_id;   // Current facility
protected $user;          // Authenticated user
```

**Key Methods:**
```php
// Date range setters
setMonthlyDates($year, $month)  // Set date range for a month
setWeeklyDates($year, $week)    // Set date range for a week

// Common operations
getFacilityInfo()               // Get current facility details
buildAgeGenderMatrix()          // Create age/gender breakdown
getTotalVisits()                // Count total patient visits
getTotalPatients()              // Count unique patients

// Must be implemented by child classes
abstract public function buildReport() : array
```

### Service Implementation Example

```php
class MalariaReportService extends BaseReportService
{
    /**
     * Build malaria report data
     */
    public function buildReport(): array
    {
        $clinical_cases = $this->getClinicalCases();
        $lab_cases = $this->getLabConfirmedCases();
        
        return [
            'facility' => $this->getFacilityInfo(),
            'month_year' => $this->date_from->format('M Y'),
            'by_age_gender' => $this->aggregateByAgeGender($clinical_cases),
            'total_clinical' => $clinical_cases->count(),
            'total_lab_confirmed' => $lab_cases->count(),
            'generated_at' => Carbon::now(),
        ];
    }

    private function getClinicalCases()
    {
        // Query ICD codes for malaria (B50-B53)
        // Filter by date range
        // Group by patient demographics
    }
}
```

---

## Controllers

### AdminReportController Pattern

**Constructor Dependency Injection:**
```php
public function __construct(
    MalariaReportService $malariaService,
    IdSRReportService $idsrService,
    // ... other services ...
)
```

**Typical Method Pattern:**
```php
/**
 * Generate malaria report
 */
public function malariaMonthly(Request $request)
{
    // 1. Get user input
    $year = (int) ($request->input('year') ?? date('Y'));
    $month = (int) ($request->input('month') ?? date('n'));

    // 2. Set date range in service
    $this->malariaService->setMonthlyDates($year, $month);

    // 3. Build report data
    $data = $this->malariaService->buildReport();

    // 4. Add presentation variables BEFORE PDF check
    $data['year'] = $year;
    $data['month'] = $month;
    $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

    // 5. Check if PDF requested
    if ($request->has('pdf')) {
        return $this->downloadPdf('malaria-monthly', $data);
    }

    // 6. Render HTML view
    return view('admin.reports.malaria-monthly', $data);
}
```

### Important: Variable Order

⚠️ **Critical:** Add presentation variables to `$data` array BEFORE checking for PDF:

```php
// ✅ CORRECT
$data = $this->service->buildReport();
$data['month_name'] = ...;      // Add variables first
if ($request->has('pdf')) {
    return $this->downloadPdf(..., $data);
}
```

```php
// ❌ WRONG
$data = $this->service->buildReport();
if ($request->has('pdf')) {
    return $this->downloadPdf(..., $data);  // month_name not set yet!
}
$data['month_name'] = ...;
```

### PDF Download Helper
```php
protected function downloadPdf($reportType, $data)
{
    $viewPath = "admin.reports.pdfs.{$reportType}";
    $html = view($viewPath, $data)->render();
    $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
    
    $fileName = $reportType . '-' . now()->format('Y-m-d') . '.pdf';
    return $pdf->download($fileName);
}
```

---

## Views

### HTML View Structure

**Location:** `resources/views/admin/reports/*.blade.php`

**Standard Layout:**
```blade
@extends('layouts.app_main_layout')

@section('page_title', 'Report Title')

@section('main_content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-icon"></i> Report Title
            </h1>
            <p class="text-muted">Facility | Month Year</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.report-name') }}" class="form-inline">
                        <label class="mr-2">Select Month:</label>
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Summary statistics here -->
    </div>

    <!-- Data Tables -->
    <div class="row">
        <!-- Detailed data here -->
    </div>
</div>

<style>
    .page-title { font-weight: 600; color: #333; }
</style>
@endsection
```

### PDF View Structure

**Location:** `resources/views/admin/reports/pdfs/*.blade.php`

**Standard Layout:**
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Title</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #1f3a93; color: white; padding: 10px; }
        td { padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Report Title</h1>
        <p>Subtitle</p>
    </div>

    <div class="facility-info">
        <p><strong>Facility:</strong> {{ $facility['name'] ?? 'N/A' }}</p>
        <p><strong>Period:</strong> {{ $month_name }} {{ $year }}</p>
        <p><strong>Generated:</strong> {{ $generated_at->format('d M Y H:i') }}</p>
    </div>

    <!-- Data tables and content -->

    <div class="footer">
        <p>Generated by Practice 1.0</p>
    </div>
</body>
</html>
```

### Available Variables in Views

**All Reports:**
```php
$facility['name']        // Facility name
$facility['region']      // Region
$facility['district']    // District
$year                    // Selected year
$generated_at            // Carbon timestamp
```

**Monthly Reports:**
```php
$month                   // Selected month (1-12)
$month_name              // Month name (January-December)
```

**Weekly Reports:**
```php
$week                    // ISO week number (1-53)
```

---

## Routes

**Location:** `routes/web.php`

**Route Group:**
```php
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->group(function () {
        Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
            // All report routes
        });
    });
```

**All Routes:**

| HTTP | Path | Controller Method | Route Name |
|------|------|------------------|-----------|
| GET | `/admin/reports` | `index()` | `admin.reports.index` |
| GET | `/admin/reports/malaria-monthly` | `malariaMonthly()` | `admin.reports.malaria-monthly` |
| GET | `/admin/reports/idsr-weekly` | `idsrWeekly()` | `admin.reports.idsr-weekly` |
| GET | `/admin/reports/std-sti-monthly` | `stdStiMonthly()` | `admin.reports.std-sti-monthly` |
| GET | `/admin/reports/medicines-monthly` | `medicinesMonthly()` | `admin.reports.medicines-monthly` |
| GET | `/admin/reports/tracer-medicines` | `tracerMedicines()` | `admin.reports.tracer-medicines` |
| GET | `/admin/reports/low-stock-medicines` | `lowStockMedicines()` | `admin.reports.low-stock-medicines` |
| GET | `/admin/reports/dtc-monthly` | `dtcMonthly()` | `admin.reports.dtc-monthly` |
| GET | `/admin/reports/monthly-lab-reports` | `monthlyLabReports()` | `admin.reports.monthly-lab-reports` |
| GET | `/admin/reports/lab-hematology` | `labHematology()` | `admin.reports.lab-hematology` |
| GET | `/admin/reports/lab-blood-transfusion` | `labBloodTransfusion()` | `admin.reports.lab-blood-transfusion` |
| GET | `/admin/reports/lab-clinical-chemistry` | `labClinicalChemistry()` | `admin.reports.lab-clinical-chemistry` |
| GET | `/admin/reports/lab-microbiology` | `labMicrobiology()` | `admin.reports.lab-microbiology` |
| GET | `/admin/reports/lab-serology` | `labSerology()` | `admin.reports.lab-serology` |
| GET | `/admin/reports/lab-parasitology` | `labParasitology()` | `admin.reports.lab-parasitology` |

**URL Generation in Views:**
```blade
<!-- Navigate to report -->
<a href="{{ route('admin.reports.malaria-monthly') }}">Malaria Report</a>

<!-- With parameters -->
<a href="{{ route('admin.reports.malaria-monthly', ['month' => 6, 'year' => 2026]) }}">
    June Report
</a>

<!-- PDF download (form) -->
<form method="GET" action="{{ route('admin.reports.malaria-monthly') }}" class="form-inline">
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">
    <button type="submit" name="pdf" value="1" class="btn btn-danger">
        Download PDF
    </button>
</form>
```

---

## Navigation

**File:** `resources/views/layouts/role_specific/admin.blade.php`

**Menu Structure:**
```
Reports & Analytics
├── Reports Dashboard
├── Disease Surveillance
│   ├── Malaria Monthly
│   ├── IDSR Weekly
│   ├── STI/STD Monthly
│   └── DTC Monthly
└── Pharmacy & Inventory
    ├── Medicines Monthly
    ├── Tracer Medicines
    ├── Low Stock Alert
    └── Monthly Lab Reports
        ├── All Lab Tests
        ├── Hematology
        ├── Blood Transfusion
        ├── Clinical Chemistry
        ├── Microbiology
        ├── Serology
        └── Parasitology
```

**Navigation Code Pattern:**
```blade
<!-- Parent Menu Item -->
<li class="nav-item has-treeview {{ nav_menu_open_class(['admin.reports.*']) }}">
    <a href="#" class="nav-link nav-header {{ nav_active_class(['admin.reports.*']) }}">
        <i class="nav-icon bi bi-file-earmark-text-fill text-info"></i>
        <p class="text-bold">
            Reports & Analytics
            <i class="nav-arrow bi bi-chevron-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview" 
        style="{{ nav_display_style(['admin.reports.*']) }}">
        
        <!-- Child Menu Item -->
        <li class="nav-item">
            <a href="{{ route('admin.reports.malaria-monthly') }}" 
               class="nav-link nav-sub-item {{ nav_active_class(['admin.reports.malaria-monthly']) }}">
                <i class="nav-icon bi bi-bar-chart text-danger"></i>
                <p>Malaria Monthly</p>
            </a>
        </li>
        
        <!-- Nested Parent -->
        <li class="nav-item has-treeview 
            {{ nav_menu_open_class(['admin.reports.lab-*']) }}">
            <a href="#" class="nav-link nav-sub-header 
                {{ nav_active_class(['admin.reports.lab-*']) }}">
                <i class="nav-icon bi bi-flask text-primary"></i>
                <p>
                    Monthly Lab Reports
                    <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview" 
                style="{{ nav_display_style(['admin.reports.lab-*']) }}">
                <li class="nav-item">
                    <a href="{{ route('admin.reports.lab-hematology') }}" 
                       class="nav-link nav-sub-sub-item">
                        <i class="nav-icon bi bi-droplet text-danger"></i>
                        <p>Hematology</p>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</li>
```

---

## Adding New Reports

### Step-by-Step Guide

#### 1. Create the Service

**File:** `app/Services/NewReportService.php`

```php
<?php
namespace App\Services;

use App\Models\YourModel;
use Carbon\Carbon;

class NewReportService extends BaseReportService
{
    /**
     * Build report data
     */
    public function buildReport(): array
    {
        $data = $this->aggregateData();
        
        return [
            'facility' => $this->getFacilityInfo(),
            'total_items' => $data->count(),
            'by_category' => $this->groupByCategory($data),
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * Aggregate data from database
     */
    private function aggregateData()
    {
        return YourModel::whereBetween('created_at', [$this->date_from, $this->date_to])
            ->with('relationships')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Group data by category
     */
    private function groupByCategory($data)
    {
        return $data->groupBy('category')->map(function ($group) {
            return [
                'category' => $group->first()->category,
                'count' => $group->count(),
            ];
        })->values();
    }
}
```

#### 2. Add Controller Method

**File:** `app/Http/Controllers/AdminReportController.php`

```php
// Add to constructor
protected $newReportService;

public function __construct(
    // ... existing services ...
    NewReportService $newReportService
)
{
    // ... existing assignments ...
    $this->newReportService = $newReportService;
}

// Add controller method
/**
 * New report
 */
public function newReport(Request $request)
{
    $year = (int) ($request->input('year') ?? date('Y'));
    $month = (int) ($request->input('month') ?? date('n'));

    $this->newReportService->setMonthlyDates($year, $month);
    $data = $this->newReportService->buildReport();

    // Add presentation variables FIRST
    $data['year'] = $year;
    $data['month'] = $month;
    $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

    // Check for PDF
    if ($request->has('pdf')) {
        return $this->downloadPdf('new-report', $data);
    }

    return view('admin.reports.new-report', $data);
}
```

#### 3. Create HTML View

**File:** `resources/views/admin/reports/new-report.blade.php`

```blade
@extends('layouts.app_main_layout')

@section('page_title', 'New Report')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">
                <i class="fas fa-icon"></i> New Report
            </h1>
            <p class="text-muted">
                {{ $facility['name'] ?? 'Facility' }} |
                {{ $month_name }} {{ $year }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.new-report') }}" 
                          class="form-inline">
                        <label class="mr-2">Select Month:</label>
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" 
                                    {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body">
                    <!-- Data here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### 4. Create PDF View

**File:** `resources/views/admin/reports/pdfs/new-report.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>
    <h1>New Report</h1>
    <!-- Content here -->
</body>
</html>
```

#### 5. Add Routes

**File:** `routes/web.php`

```php
Route::get('/new-report', [AdminReportController::class, 'newReport'])
    ->name('new-report');
```

#### 6. Update Navigation

**File:** `resources/views/layouts/role_specific/admin.blade.php`

```blade
<li class="nav-item">
    <a href="{{ route('admin.reports.new-report') }}" 
       class="nav-link nav-sub-item {{ nav_active_class(['admin.reports.new-report']) }}">
        <i class="nav-icon bi bi-icon text-color"></i>
        <p>New Report</p>
    </a>
</li>
```

#### 7. Register Service in Container (if using dependency injection)

**File:** `app/Providers/AppServiceProvider.php` (optional, auto-injection usually works)

```php
$this->app->bind('NewReportService', function ($app) {
    return new NewReportService();
});
```

---

## Best Practices

### 1. Service Layer

✅ **DO:**
- Keep business logic in services, not controllers
- Extend `BaseReportService` for consistency
- Use meaningful method names that describe operations
- Return arrays from services for flexibility
- Handle all database queries in services

❌ **DON'T:**
- Query database directly in controllers
- Mix business logic with request handling
- Return views/responses from services

### 2. Data Variables Order

✅ **DO:** Add variables to $data array BEFORE PDF check
```php
$data = $this->service->buildReport();
$data['month_name'] = ...;     // Add first
if ($request->has('pdf')) {
    return $this->downloadPdf(..., $data);
}
```

❌ **DON'T:** Add variables AFTER PDF check
```php
if ($request->has('pdf')) {
    return $this->downloadPdf(..., $data);  // month_name missing!
}
$data['month_name'] = ...;
```

### 3. Query Optimization

✅ **DO:**
- Use joins instead of separate queries
- Filter in database, not PHP
- Use `with()` for eager loading
- Use `whereBetween()` for date ranges
- Add indexes on frequently queried columns

```php
return Investigation::join('patient_visits', 'investigations.visit_id', '=', 'patient_visits.id')
    ->whereBetween('patient_visits.created_at', [$from, $to])
    ->with('medicalService')
    ->get();
```

❌ **DON'T:**
- N+1 queries (load related data in loops)
- Use complex WHERE clauses with many ORs in large datasets
- Filter data after fetching from database

### 4. View Structure

✅ **DO:**
- Use consistent layouts and patterns
- Include month/year selectors for easy navigation
- Add summary statistics prominently
- Include facility information
- Provide PDF download options
- Show timestamps

❌ **DON'T:**
- Hardcode facility information
- Skip date/time information
- Create complex custom layouts
- Overload views with business logic

### 5. Security

✅ **DO:**
- Use middleware to protect routes
- Validate user authorization
- Check that users can only see their facility's data
- Escape output in views (Blade does this by default)
- Validate user input

```php
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->group(function () { ... });
```

❌ **DON'T:**
- Allow unauthenticated access
- Skip authorization checks
- Display sensitive data without filtering
- Trust user input

### 6. Date Handling

✅ **DO:**
- Use Carbon for date operations
- Store UTC times in database
- Use consistent date formats in views
- Provide month/year/week selectors

```php
$this->malariaService->setMonthlyDates($year, $month);
$month_name = Carbon::createFromDate($year, $month, 1)->format('F');
```

❌ **DON'T:**
- Use raw date strings
- Forget timezone conversions
- Hardcode date ranges

### 7. Error Handling

✅ **DO:**
- Handle missing data gracefully
- Provide meaningful error messages
- Log errors for debugging
- Check if relationships exist

```blade
@forelse ($investigations as $inv)
    <tr>...</tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted">
            No investigations found
        </td>
    </tr>
@endforelse
```

❌ **DON'T:**
- Assume data always exists
- Show raw exception messages to users
- Ignore missing relationships

### 8. Code Reusability

✅ **DO:**
- Use traits for shared logic
- Create helper methods for common operations
- Extract common patterns into base classes
- Reuse services across multiple controllers

```php
// Use trait for age calculations
use AgeCalculatorTrait;

$age_days = $this->getAgeInDays($dob);
$age_group = AgeGroup::findByDateOfBirth($dob);
```

❌ **DON'T:**
- Duplicate code across services
- Copy-paste view templates
- Create monolithic services

---

## Common Issues & Solutions

### Issue 1: "Undefined variable $month_name"

**Cause:** Variable added to $data AFTER PDF check

**Solution:** Move variable assignment BEFORE PDF check

```php
// Move this BEFORE if ($request->has('pdf'))
$data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');
```

### Issue 2: Slow Report Generation

**Cause:** Too many database queries or poor query structure

**Solution:**
- Use joins instead of separate queries
- Add indexes to frequently queried columns
- Use eager loading with `with()`
- Limit result sets if possible

```php
// BAD: N+1 query problem
$visits = PatientVisit::all();
foreach ($visits as $visit) {
    $patient = Patient::find($visit->patient_id);  // Query per visit!
}

// GOOD: Eager loading
$visits = PatientVisit::with('patient')->get();
```

### Issue 3: PDF Not Generating

**Causes:**
- View file not found in `pdfs/` directory
- Missing variable in PDF template
- Invalid HTML in view

**Solution:**
1. Verify view file exists: `resources/views/admin/reports/pdfs/report-name.blade.php`
2. Check all variables are passed in $data array
3. Test HTML rendering before PDF generation
4. Check DomPDF library is installed: `composer require barryvdh/laravel-dompdf`

### Issue 4: Reports Show No Data

**Causes:**
- Date range doesn't match any records
- Database filters too restrictive
- ICD codes don't match records

**Solution:**
- Check date format in database vs. query
- Verify data exists in database for date range
- Check ICD code mappings
- Add logging to debug queries

```php
// Add logging to debug
\Log::info('Report filter', [
    'from' => $this->date_from,
    'to' => $this->date_to,
    'count' => $query->count()
]);
```

### Issue 5: Navigation Menu Not Showing

**Causes:**
- Route name mismatch
- Missing route definition
- Navigation code not updated

**Solution:**
1. Verify route exists: `php artisan route:list | grep admin.reports`
2. Check route name matches navigation: `route('admin.reports.new-report')`
3. Verify route is registered in `routes/web.php`
4. Clear view cache: `php artisan view:clear`

### Issue 6: Authorization Issues

**Causes:**
- Middleware not applied
- User not authorized
- Session issues

**Solution:**
```php
// Verify middleware is applied to routes
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function () { ... });

// Test authorization in code
if (auth()->user()->cannot('viewReports')) {
    abort(403);
}
```

---

## Testing Reports

### Manual Testing Checklist

- [ ] View report in HTML format
- [ ] Select different month/year
- [ ] Click "Filter" button
- [ ] Click "Download PDF"
- [ ] Verify PDF opens/downloads
- [ ] Check all data displays correctly
- [ ] Test with no data (empty result set)
- [ ] Check responsive design on mobile
- [ ] Verify facility information shows
- [ ] Confirm timestamps are accurate

### Unit Testing Services

```php
// Test service
public function test_malaria_report_builds_correctly()
{
    $service = new MalariaReportService();
    $service->setMonthlyDates(2026, 6);
    
    $report = $service->buildReport();
    
    $this->assertIsArray($report);
    $this->assertArrayHasKey('facility', $report);
    $this->assertArrayHasKey('by_age_gender', $report);
}
```

---

## Useful Commands

```bash
# List all routes
php artisan route:list | grep admin.reports

# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Check database
php artisan tinker
> DB::select("SELECT COUNT(*) FROM investigations WHERE created_at BETWEEN '2026-06-01' AND '2026-06-30'")

# Generate test data
php artisan tinker
> factory(PatientVisit::class, 100)->create();
```

---

## Related Documentation

- [Laravel Service Container](https://laravel.com/docs/services#service-container)
- [Laravel Eloquent ORM](https://laravel.com/docs/eloquent)
- [Laravel Blade Templates](https://laravel.com/docs/blade)
- [DomPDF Documentation](https://github.com/barryvdh/laravel-dompdf)
- [Carbon Date Library](https://carbon.nesbot.com/)

---

## Support & Questions

For questions or issues with the reporting system:
1. Check this guide first
2. Review the code comments
3. Check database schema
4. Test with sample data
5. Review error logs in `storage/logs/`

---

**Last Updated:** June 2026  
**Version:** 1.0  
**Maintained By:** Development Team
