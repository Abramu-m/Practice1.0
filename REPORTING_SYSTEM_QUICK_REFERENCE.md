# Reporting System - Quick Reference Guide

## 📋 Quick Navigation

| Need | Find | File |
|------|------|------|
| Add new report | Step-by-step guide | REPORTING_SYSTEM_GUIDE.md |
| Fix undefined variable | Common Issues section | REPORTING_SYSTEM_GUIDE.md |
| Understand architecture | Architecture section | REPORTING_SYSTEM_GUIDE.md |
| View all routes | Routes table | REPORTING_SYSTEM_GUIDE.md |
| Check file locations | File Structure section | REPORTING_SYSTEM_GUIDE.md |

---

## 🔧 Essential File Locations

```
Controllers:    app/Http/Controllers/AdminReportController.php
Services:       app/Services/*ReportService.php
Views (HTML):   resources/views/admin/reports/*.blade.php
Views (PDF):    resources/views/admin/reports/pdfs/*.blade.php
Routes:         routes/web.php (line 178)
Navigation:     resources/views/layouts/role_specific/admin.blade.php
Models:         app/Models/{Patient,PatientVisit,Investigation,etc}.php
Database:       database/migrations/202x_xx_xx_*
```

---

## 🚀 Adding a New Report (TL;DR)

### 1. Service
```php
// app/Services/NewReportService.php
class NewReportService extends BaseReportService {
    public function buildReport(): array { ... }
}
```

### 2. Controller Method
```php
// Add to AdminReportController
public function newReport(Request $request) {
    $data = $this->newReportService->buildReport();
    $data['month_name'] = ...;  // ADD VARIABLES FIRST!
    if ($request->has('pdf')) return $this->downloadPdf('new-report', $data);
    return view('admin.reports.new-report', $data);
}
```

### 3. Views
```
resources/views/admin/reports/new-report.blade.php        (HTML)
resources/views/admin/reports/pdfs/new-report.blade.php   (PDF)
```

### 4. Route
```php
// routes/web.php (in admin/reports prefix group)
Route::get('/new-report', [AdminReportController::class, 'newReport'])->name('new-report');
```

### 5. Navigation
```blade
<!-- resources/views/layouts/role_specific/admin.blade.php -->
<li class="nav-item">
    <a href="{{ route('admin.reports.new-report') }}" class="nav-link">
        <i class="nav-icon bi bi-icon"></i>
        <p>New Report</p>
    </a>
</li>
```

---

## ⚡ Critical Things to Remember

### Variable Order (THE MOST COMMON ERROR!)
```php
// ✅ CORRECT
$data = $this->service->buildReport();
$data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');
if ($request->has('pdf')) {
    return $this->downloadPdf('report-name', $data);
}
```

```php
// ❌ WRONG - Will crash with "Undefined variable"
if ($request->has('pdf')) {
    return $this->downloadPdf('report-name', $data);
}
$data['month_name'] = ...;  // Too late!
```

### Always Extend BaseReportService
```php
class NewReportService extends BaseReportService {
    // Gets free access to:
    // - $this->date_from, $this->date_to
    // - setMonthlyDates(), setWeeklyDates()
    // - getFacilityInfo()
    // - buildAgeGenderMatrix()
}
```

### Use Joins for Performance
```php
// ❌ BAD - Will be slow
$visits = PatientVisit::all();
foreach ($visits as $v) {
    $patient = Patient::find($v->patient_id);  // N+1 queries!
}

// ✅ GOOD - One query
$visits = PatientVisit::with('patient')->get();
```

### Handle Empty Results
```blade
@forelse ($items as $item)
    <tr>{{ $item->name }}</tr>
@empty
    <tr><td colspan="5" class="text-center">No data found</td></tr>
@endforelse
```

---

## 📊 All Available Reports

### Disease Surveillance (4)
- Malaria Monthly → `/admin/reports/malaria-monthly`
- IDSR Weekly → `/admin/reports/idsr-weekly`
- STI/STD Monthly → `/admin/reports/std-sti-monthly`
- DTC Monthly → `/admin/reports/dtc-monthly`

### Pharmacy (3)
- Medicines Monthly → `/admin/reports/medicines-monthly`
- Tracer Medicines → `/admin/reports/tracer-medicines`
- Low Stock Medicines → `/admin/reports/low-stock-medicines`

### Laboratory (7)
- All Lab Tests → `/admin/reports/monthly-lab-reports`
- Hematology → `/admin/reports/lab-hematology`
- Blood Transfusion → `/admin/reports/lab-blood-transfusion`
- Clinical Chemistry → `/admin/reports/lab-clinical-chemistry`
- Microbiology → `/admin/reports/lab-microbiology`
- Serology → `/admin/reports/lab-serology`
- Parasitology → `/admin/reports/lab-parasitology`

---

## 🔍 Service Methods Reference

### BaseReportService Methods
```php
$this->setMonthlyDates($year, $month)              // Set date range
$this->setWeeklyDates($year, $week)                // Set week range
$this->getFacilityInfo()                           // Get facility data
$this->buildAgeGenderMatrix($data)                 // Group by age/gender
$this->getTotalVisits()                            // Count visits
$this->getTotalPatients()                          // Count unique patients
```

### Required in Child Services
```php
public function buildReport(): array {
    // Must return array with all report data
}
```

---

## 🎨 View Variable Checklist

**Every report needs:**
```php
$facility['name']        // From getFacilityInfo()
$facility['region']      // From getFacilityInfo()
$facility['district']    // From getFacilityInfo()
$year                    // Selected year
$month_name or $week     // For monthly/weekly reports
$generated_at            // Carbon timestamp
```

**Optional based on report:**
```php
$total_items             // Total count
$by_age_gender          // Age/gender breakdown
$by_category            // Grouped by category
$completion_rate        // Percentage complete
```

---

## 🚦 Status Codes & Badge Colors

**Use in views:**
```blade
<!-- Status badges -->
<span class="badge badge-success">Completed</span>      <!-- Green -->
<span class="badge badge-warning">Pending</span>        <!-- Yellow -->
<span class="badge badge-danger">Out of Stock</span>    <!-- Red -->
<span class="badge badge-info">Information</span>       <!-- Blue -->

<!-- Alert styling -->
<div class="alert alert-success">Success message</div>
<div class="alert alert-warning">Warning message</div>
<div class="alert alert-danger">Danger message</div>
```

---

## 🐛 Debugging Commands

```bash
# List all report routes
php artisan route:list | grep admin.reports

# Check if service is registered
php artisan tinker
> app(NewReportService::class)

# Check database data
php artisan tinker
> Investigation::where('created_at', '>=', '2026-06-01')->count()

# Clear caches if routes/views not showing
php artisan view:clear
php artisan cache:clear

# Check error logs
tail -f storage/logs/laravel.log
```

---

## 📋 Validation Checklist Before Going Live

- [ ] Service extends BaseReportService
- [ ] Controller method adds variables BEFORE PDF check
- [ ] HTML view created in `resources/views/admin/reports/`
- [ ] PDF view created in `resources/views/admin/reports/pdfs/`
- [ ] Route added to `routes/web.php` with `admin.reports.` prefix
- [ ] Navigation menu item added to `admin.blade.php`
- [ ] View tests with sample data
- [ ] PDF downloads successfully
- [ ] Responsive design works on mobile
- [ ] Facility information displays
- [ ] Timestamps show correctly
- [ ] Empty result handling works

---

## 🔐 Security Checklist

- [ ] Routes protected with `auth` middleware
- [ ] Routes protected with `EnsureUserIsAdmin` middleware
- [ ] No raw SQL queries (use Eloquent)
- [ ] User input validated
- [ ] Output escaped in Blade templates
- [ ] Sensitive data not exposed
- [ ] Facility filtering applied (only own facility data)

---

## 📞 Quick Help

### Report Not Showing?
1. Check route exists: `php artisan route:list | grep report-name`
2. Check view file exists
3. Clear view cache: `php artisan view:clear`
4. Check middleware allows access

### PDF Not Generating?
1. Check PDF view file exists in `pdfs/` folder
2. Check all variables are in $data array
3. Check DomPDF installed: `composer require barryvdh/laravel-dompdf`
4. Check HTML in view is valid

### Data Not Showing?
1. Check date range has data: `php artisan tinker`
2. Check filters/where clauses
3. Check joins are correct
4. Check relationships are loaded with `with()`

### Variable Undefined Error?
1. Move variable assignment BEFORE PDF check
2. Verify variable added to $data array
3. Check variable spelled correctly
4. Check it's used in correct view

---

## 🎯 Performance Tips

1. **Use Indexes**
   ```sql
   ALTER TABLE investigations ADD INDEX idx_visit_date (visit_date);
   ALTER TABLE patient_visits ADD INDEX idx_created_at (created_at);
   ```

2. **Use Eager Loading**
   ```php
   Investigation::with('medicalService', 'visit', 'visit.patient')->get()
   ```

3. **Limit Results**
   ```php
   ->limit(1000)->get()  // Don't load entire table
   ```

4. **Cache if Repeated**
   ```php
   \Cache::remember('report_key', 3600, function() {
       return $this->buildReport();
   });
   ```

---

## 📚 Files to Read

**For understanding:**
1. `REPORTING_SYSTEM_GUIDE.md` - Full documentation
2. `app/Services/BaseReportService.php` - Base class methods
3. `app/Services/MalariaReportService.php` - Example implementation
4. `resources/views/admin/reports/malaria-monthly.blade.php` - Example view

**For reference:**
1. `routes/web.php` - Route definitions
2. `AdminReportController.php` - Controller patterns
3. `admin.blade.php` - Navigation structure

---

## 💡 Pro Tips

1. **Copy existing reports** - Easier than starting from scratch
2. **Use route names** - `route('admin.reports.name')` instead of hard-coded URLs
3. **Group similar data** - Use `groupBy()` in services
4. **Add margins** - Use `mb-4` Bootstrap classes between sections
5. **Test empty results** - Always handle `@empty` case in views
6. **Use icons** - Font Awesome icons make reports prettier
7. **Color code** - Different colors for different status/categories
8. **Show timestamps** - Always include `$generated_at->format()`

---

## 📈 View Template Quick Copy

```blade
@extends('layouts.app_main_layout')
@section('page_title', 'Report Name')

@section('main_content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title"><i class="fas fa-icon"></i> Report Name</h1>
            <p class="text-muted">{{ $facility['name'] ?? 'N/A' }} | {{ $month_name }} {{ $year }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.report-name') }}" class="form-inline">
                        <select name="month" class="form-control form-control-sm mr-2" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary mr-2">Filter</button>
                        <button type="submit" name="pdf" value="1" class="btn btn-sm btn-danger">PDF</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Data Table</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Column 1</th>
                                <th class="text-center">Column 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $row)
                                <tr>
                                    <td>{{ $row['field'] }}</td>
                                    <td class="text-center">{{ $row['value'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

**Happy coding! 🚀**

For detailed documentation, see: `REPORTING_SYSTEM_GUIDE.md`
