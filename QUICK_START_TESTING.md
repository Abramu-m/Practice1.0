# Quick Start Testing Guide - Reports System

## 🚀 Ready to Test!

Your reports system is now fully integrated with navigation. Follow these steps to start testing.

---

## Step 1: Login

1. Navigate to: `http://localhost/`
2. Login with **admin credentials**
3. You should see the main dashboard

---

## Step 2: Access Reports Dashboard

**Option A - Via Sidebar Menu**:
1. Scroll down the left sidebar
2. Look for **"Reports & Analytics"** section
3. Click on **"Reports Dashboard"**

**Option B - Direct URL**:
- Visit: `http://localhost/admin/reports/`

---

## Step 3: Explore the Navigation

Once in Reports Dashboard, you'll see 6 report cards organized by category:

### 📊 **Disease Surveillance** (top section)
- **Malaria Monthly** - View malaria cases by age/gender
- **IDSR Weekly** - Integrated disease surveillance 
- **STI/STD Monthly** - Sexually transmitted infections

### 💊 **Pharmacy & Inventory** (bottom section)
- **Medicines Monthly** - Medication consumption
- **Tracer Medicines** - Essential medicine tracking
- **Low Stock Alert** - Real-time inventory status

---

## Step 4: Test Individual Reports

### For Monthly Reports (Malaria, STI, Medicines, Tracer):
1. Select a **month** from the dropdown
2. Click **"View"** button → See report
3. Click **"PDF"** button → Download PDF file
4. Try different months to test filtering

### For Weekly Reports (IDSR):
1. Select a **week** number (1-53)
2. Click **"View"** button → See report
3. Click **"PDF"** button → Download PDF file
4. Try different weeks to test filtering

### For On-Demand (Low Stock):
1. Click **"View"** or **"PDF"** directly
2. See current real-time inventory status

---

## Step 5: Sidebar Menu Access

You can also access reports from the sidebar:

```
Left Sidebar → Reports & Analytics (expanded) →
├── Reports Dashboard
├── Disease Surveillance
│   ├── Malaria Monthly
│   ├── IDSR Weekly
│   └── STI/STD Monthly
├── Pharmacy & Inventory
│   ├── Medicines Monthly
│   ├── Tracer Medicines
│   └── Low Stock Alert
└── MTUHA Monthly (Legacy)
```

---

## Step 6: Test PDF Exports

1. Generate any report
2. Click **"PDF"** button
3. File should download automatically
4. Verify PDF opens correctly
5. Check content is complete

**Expected filename format**: `{report-type}-{date}.pdf`

Example: `malaria-monthly-2026-06-04.pdf`

---

## Troubleshooting Quick Fixes

| Issue | Solution |
|-------|----------|
| "Page not found" error | Ensure you're logged in as admin |
| Reports show no data | Normal - add patient data to database |
| Navigation not visible | Try refreshing the page |
| PDF download fails | Check browser's download settings |
| Styles look broken | Clear browser cache & refresh |

---

## Data Requirements (Optional)

To see actual data in reports, you need:

1. **Patients** - Must have `date_of_birth` and `gender`
2. **Patient Visits** - Must have `visit_date` and `patient_id`
3. **Diagnoses** - Must have ICD codes in `icd_diagnoses` table
4. **Medications** - Must have dispensed prescriptions
5. **Investigations** - Must have lab test records

**For demo/testing**, empty reports are normal and expected!

---

## ✅ Testing Checklist

- [ ] Can access Reports Dashboard from sidebar
- [ ] Can see all 6 report cards
- [ ] Can view Malaria Monthly report
- [ ] Can view IDSR Weekly report
- [ ] Can view STI/STD Monthly report
- [ ] Can view Medicines Monthly report
- [ ] Can view Tracer Medicines report
- [ ] Can view Low Stock Alert report
- [ ] Can select different months for monthly reports
- [ ] Can select different weeks for weekly reports
- [ ] Can download at least one PDF
- [ ] PDF file has correct name and opens correctly
- [ ] Non-admin users cannot access reports (403 error)
- [ ] Sidebar menu collapses/expands correctly

---

## Navigation Hierarchy

```
Main Menu (Sidebar)
└── Reports & Analytics
    ├── Reports Dashboard ← Main hub
    │
    ├── Disease Surveillance ← Category header
    │   ├── Malaria Monthly
    │   ├── IDSR Weekly
    │   └── STI/STD Monthly
    │
    ├── Pharmacy & Inventory ← Category header
    │   ├── Medicines Monthly
    │   ├── Tracer Medicines
    │   └── Low Stock Alert
    │
    └── MTUHA Monthly (Legacy)
```

---

## Next Steps After Testing

1. ✅ Verify all reports are accessible
2. ✅ Test PDF downloads work
3. ✅ Check date filtering works
4. ✅ Verify access control (non-admins blocked)
5. ✅ Share feedback with development team
6. ✅ Plan data migration (if needed)
7. ✅ Deploy to production

---

## Support

- **Documentation**: `REPORTS_TESTING_GUIDE.md`
- **Implementation Details**: `REPORTS_IMPLEMENTATION_SUMMARY.md`
- **Routes**: `php artisan route:list | grep admin.reports`
- **Issues**: Check browser console (F12) for errors

---

**Happy Testing! 🎉**

All reports are ready to use. Enjoy your new analytics system!
