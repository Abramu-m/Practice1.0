# Reports System Testing Guide

## Overview
This guide provides instructions for testing the newly implemented reports system in Practice1.0.

## Pre-Testing Checklist

### Database & Migrations
- ✅ Run migrations: `php artisan migrate`
- ✅ Verify age_groups table exists
- ✅ Verify idsr_categories table exists
- ✅ Check seed data loaded (5 age groups, 10 IDSR categories)

### Code & Configuration
- ✅ Models created: `AgeGroup`, `IdSRCategory`
- ✅ Services created: `MalariaReportService`, `IdSRReportService`, etc.
- ✅ Controller created: `AdminReportController`
- ✅ Routes registered: 7 admin report routes
- ✅ Middleware configured: `auth` + `admin` protection

## Testing Scenarios

### 1. Access Control Testing

#### 1.1 Unauthenticated User
```bash
# Test URL: http://localhost/admin/reports/
# Expected: Redirect to login
# Actual Result: [Test this]
```

#### 1.2 Authenticated Non-Admin User
```bash
# Requirements: Login as non-admin user
# Test URL: http://localhost/admin/reports/
# Expected: 403 Forbidden
# Actual Result: [Test this]
```

#### 1.3 Admin User Access
```bash
# Requirements: Login as admin user
# Test URL: http://localhost/admin/reports/
# Expected: Reports dashboard visible
# Actual Result: [Test this]
```

---

### 2. Dashboard Testing

**URL**: `http://localhost/admin/reports/`

#### 2.1 Page Elements
- [ ] Page title displayed
- [ ] 6 report cards visible (Malaria, MTUHA, STI, Medicines, Tracer, IDSR)
- [ ] Month selectors work
- [ ] Week selectors work
- [ ] "View" buttons functional
- [ ] "PDF" buttons functional
- [ ] "Back" button functional

#### 2.2 Report Card Functionality
For each report card:
- [ ] Month/Week selector loads correctly
- [ ] Default month/week is current
- [ ] View button navigates to report
- [ ] PDF button triggers download

---

### 3. Malaria Monthly Report Testing

**URL**: `http://localhost/admin/reports/malaria-monthly?year=2026&month=6`

#### 3.1 Data Display
- [ ] Facility name displayed
- [ ] Month/Year displayed
- [ ] Two tables shown (Clinical + Lab)
- [ ] Age group rows present (5 groups)
- [ ] Gender columns (Male, Female, Total)
- [ ] Total row with calculations

#### 3.2 Data Accuracy
```sql
-- Check test data exists
SELECT COUNT(*) FROM patients WHERE date_of_birth IS NOT NULL;
SELECT COUNT(*) FROM patient_visits WHERE visit_date >= '2026-06-01' AND visit_date <= '2026-06-30';
SELECT COUNT(*) FROM icd_diagnoses WHERE icd_code LIKE 'B5%';
```

#### 3.3 Filtering
- [ ] Change month in selector
- [ ] Report updates with different data
- [ ] Date range updates correctly

#### 3.4 Export
- [ ] PDF button downloads file
- [ ] PDF filename correct format: `malaria-monthly-2026-06-04.pdf`
- [ ] PDF renders without errors
- [ ] PDF shows all data tables

---

### 4. IDSR Weekly Report Testing

**URL**: `http://localhost/admin/reports/idsr-weekly?year=2026&week=23`

#### 4.1 Data Display
- [ ] Week information displayed
- [ ] Week range (start/end dates) shown
- [ ] Multiple disease tables displayed
- [ ] Each disease shows age/gender breakdown
- [ ] Totals calculated correctly

#### 4.2 Filtering
- [ ] Change week selector
- [ ] Report updates
- [ ] Different data shown for different weeks

#### 4.3 Edge Cases
- [ ] Empty week (no data) handled gracefully
- [ ] Week 1 works correctly
- [ ] Week 52/53 works correctly

---

### 5. STI/STD Monthly Report Testing

**URL**: `http://localhost/admin/reports/std-sti-monthly?year=2026&month=6`

#### 5.1 Data Display
- [ ] Overall summary table shown
- [ ] STI type breakdown cards displayed
- [ ] Each type shows age/gender data
- [ ] Case counts accurate

#### 5.2 Data Grouping
- [ ] Cases grouped by STI type
- [ ] Multiple types displayed in 2-column layout
- [ ] Totals calculated per type

---

### 6. Medicines Monthly Report Testing

**URL**: `http://localhost/admin/reports/medicines-monthly?year=2026&month=6`

#### 6.1 Data Display
- [ ] Three sections visible: Categories, Top 20, Investigations
- [ ] Category breakdown table shown
- [ ] Top medicines list displayed
- [ ] Lab/investigation services listed

#### 6.2 Data Calculations
- [ ] Total medications calculated
- [ ] Unique items counted
- [ ] Category totals accurate

---

### 7. Tracer Medicines Report Testing

**URL**: `http://localhost/admin/reports/tracer-medicines?year=2026&month=6`

#### 7.1 Data Display
- [ ] Tracer medicines table shown
- [ ] Only essential medicines listed
- [ ] Availability badges displayed (green=available, red=unavailable)
- [ ] Quantity shown per medicine

#### 7.2 Calculations
- [ ] Availability rate calculated
- [ ] X of Y medicines available shown
- [ ] Percentage accuracy verified

---

### 8. Low Stock Alert Testing

**URL**: `http://localhost/admin/reports/low-stock-medicines`

#### 8.1 Data Display
- [ ] Low stock count card shown
- [ ] Out of stock count shown
- [ ] Medicines table displays
- [ ] Status badges (red/yellow) correct
- [ ] Reorder level displayed

#### 8.2 Real-time Updates
- [ ] Report shows current stock levels
- [ ] Stock levels reflect medication inventory
- [ ] Status colors match threshold

---

### 9. Date Handling Testing

#### 9.1 Valid Dates
```
- Current month/year
- Previous months
- Year 2025
- Edge months (January, December)
```

#### 9.2 Invalid Dates
```
- Month 0, 13, etc. → Should use current or gracefully handle
- Future dates → Should work or handle gracefully
- Leap years (Feb 29)
```

---

### 10. Performance Testing

#### 10.1 Load Time
- [ ] Report dashboard loads in < 2 seconds
- [ ] Monthly report loads in < 3 seconds
- [ ] Weekly report loads in < 3 seconds
- [ ] PDF generation completes in < 5 seconds

#### 10.2 Large Data Sets
```
-- For testing with larger datasets:
SELECT COUNT(*) FROM patient_visits WHERE YEAR(visit_date) = 2026;
SELECT COUNT(*) FROM consultations WHERE YEAR(created_at) = 2026;
```

---

## Running Automated Tests

```bash
# Run all report tests
php artisan test tests/Feature/AdminReportsTest.php

# Run specific test
php artisan test tests/Feature/AdminReportsTest.php::test_admin_can_access_reports_dashboard

# Run with verbose output
php artisan test tests/Feature/AdminReportsTest.php -v
```

---

## Manual Testing Steps

### Step 1: Login
1. Navigate to http://localhost/
2. Login with admin credentials

### Step 2: Access Reports
1. Navigate to `/admin/reports/`
2. Verify dashboard loads
3. Verify all 6 report cards visible

### Step 3: Generate Report
1. Click on "Malaria Monthly" > View
2. Verify data displays
3. Try different months
4. Try PDF export
5. Verify PDF downloads

### Step 4: Test All Reports
Repeat Step 3 for each report:
- Malaria Monthly
- IDSR Weekly
- STI/STD Monthly
- Medicines Monthly
- Tracer Medicines
- Low Stock Alert

---

## Troubleshooting

### Issue: "Class not found" error
**Solution**: Ensure controller is imported in routes/web.php
```php
use App\Http\Controllers\AdminReportController;
```

### Issue: Middleware error
**Solution**: Verify admin middleware exists
```bash
php artisan tinker
> Auth::user()->update(['is_admin' => true]);
```

### Issue: No data showing in reports
**Solution**: Insert test data
```bash
php artisan tinker
# Create test patients and visits
```

### Issue: PDF export fails
**Solution**: Verify DomPDF is installed
```bash
composer require barryvdh/laravel-dompdf
```

### Issue: Date filtering not working
**Solution**: Check query parameters
```
?year=2026&month=6
?year=2026&week=23
```

---

## Data Verification Queries

```sql
-- Check age groups seeded
SELECT * FROM age_groups WHERE is_active = true;

-- Check IDSR categories seeded
SELECT * FROM idsr_categories WHERE is_active = true;

-- Check patient data for reporting
SELECT COUNT(*) as total_patients FROM patients;
SELECT COUNT(*) as total_visits FROM patient_visits WHERE YEAR(visit_date) = 2026;
SELECT COUNT(*) as total_diagnoses FROM icd_diagnoses WHERE YEAR(created_at) = 2026;

-- Check medication data
SELECT COUNT(*) as total_meds FROM medications WHERE is_active = true;
SELECT COUNT(*) as total_prescriptions FROM prescriptions WHERE YEAR(created_at) = 2026;

-- Check investigations
SELECT COUNT(*) as total_investigations FROM investigations WHERE YEAR(created_at) = 2026;
```

---

## Sign-Off Checklist

- [ ] All 7 routes accessible
- [ ] Access control working
- [ ] All reports display correctly
- [ ] Data calculations accurate
- [ ] PDF exports working
- [ ] Date filtering working
- [ ] Performance acceptable
- [ ] No console errors
- [ ] No database errors
- [ ] Ready for production

---

## Next Steps

1. ✅ Verify all tests pass
2. ✅ Document any issues found
3. ✅ Create seed data for demo (optional)
4. ✅ Add to navigation menu (optional)
5. ✅ Deploy to production

---

**Testing Completed By**: _________________ **Date**: _________________
