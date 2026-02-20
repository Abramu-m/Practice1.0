# Medications/Items Management System - Visual Guide

This document provides visual representations of the medications/items management system to help understand the architecture and workflows.

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MEDICATIONS/ITEMS MANAGEMENT SYSTEM               │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE (VIEWS)                       │
├─────────────────────────────────────────────────────────────────────┤
│  Medications Views         │        Store Views                     │
│  - List & CRUD            │        - Dashboard                      │
│  - Dashboard              │        - Requisitions                   │
│  - Stock Management       │        - Categories                     │
│  - Consumption            │                                         │
│  - Reconciliation         │    Supporting Views                     │
│  - Reports                │    - Location Stock                     │
│                           │    - Stock Movements                    │
│                           │    - Ledger                             │
└─────────────┬─────────────┴─────────────┬───────────────────────────┘
              │                           │
              │         HTTP REQUESTS     │
              │                           │
┌─────────────▼─────────────────────────────▼───────────────────────────┐
│                          CONTROLLERS LAYER                             │
├────────────────────────────────────────────────────────────────────────┤
│  Medication Controllers    │    Store Controllers                      │
│  ┌──────────────────────┐ │    ┌──────────────────────────────────┐  │
│  │ MedicationController │ │    │ GoodsReceivedNoteController      │  │
│  │ MedicationUnit       │ │    │ StoreRequisitionController       │  │
│  │ MedicationFormulation│ │    │ MedicationLedgerController       │  │
│  │ MedicationPricing    │ │    │ StoreLocationStockController     │  │
│  │ MedicationSearch     │ │    │ StoreStockMovementController     │  │
│  │ MedicationCashSale   │ │    │ StoreLocationController          │  │
│  └──────────────────────┘ │    │ StoreCategoryController          │  │
│                            │    │ StoreSupplierController          │  │
│  Operations Controllers    │    │ StoreReportController            │  │
│  ┌──────────────────────┐ │    └──────────────────────────────────┘  │
│  │ StockManagement      │ │                                            │
│  │ ConsumptionTracking  │ │                                            │
│  │ Reconciliation       │ │                                            │
│  │ Reporting            │ │                                            │
│  └──────────────────────┘ │                                            │
└────────────┬───────────────┴────────────┬───────────────────────────────┘
             │                            │
             │      BUSINESS LOGIC        │
             │                            │
┌────────────▼────────────────────────────▼───────────────────────────┐
│                           SERVICES LAYER                            │
├─────────────────────────────────────────────────────────────────────┤
│  StoreRequisitionService   │   Event Handlers & Observers           │
│  - Complex workflows       │   - MedicationDispensingObserver       │
│  - Multi-step processing   │   - MedicationPrescribed event         │
└────────────┬────────────────────────────┬───────────────────────────┘
             │                            │
             │      DATA ACCESS           │
             │                            │
┌────────────▼────────────────────────────▼───────────────────────────┐
│                            MODELS LAYER                              │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  MASTER DATA              INVENTORY DATA             AUDIT DATA      │
│  ┌────────────────┐     ┌─────────────────┐     ┌────────────────┐ │
│  │ Medication     │     │ MedicationLedger│     │ StoreStock     │ │
│  │ - Core entity  │     │ - Batch tracking│     │   Movement     │ │
│  │ - Stock qty    │     │ - Expiry dates  │     │ - Complete     │ │
│  │                │     │ - Unit costs    │     │   audit trail  │ │
│  │ StoreCategory  │     │                 │     │                │ │
│  │ MedicationUnit │     │ StoreLocation   │     │ GoodsReceived  │ │
│  │ Medication     │     │   Stock         │     │   Note         │ │
│  │   Formulation  │     │ - Qty per       │     │ StoreReq       │ │
│  │ MedicationPrice│     │   location      │     │   uisition     │ │
│  │ StoreSupplier  │     │   per batch     │     │                │ │
│  │ StoreLocation  │     │                 │     │                │ │
│  └────────────────┘     └─────────────────┘     └────────────────┘ │
│                                                                      │
└──────────────────────────────┬───────────────────────────────────────┘
                               │
                               │      PERSISTENCE
                               │
┌──────────────────────────────▼───────────────────────────────────────┐
│                          DATABASE (MySQL)                            │
│  Tables: medications, medication_ledger, store_locations,            │
│          store_location_stock, store_stock_movements,                │
│          goods_received_notes, store_requisitions, etc.              │
└──────────────────────────────────────────────────────────────────────┘
```

## Data Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CORE DATA RELATIONSHIPS                       │
└─────────────────────────────────────────────────────────────────────┘

                    ┌──────────────────────┐
                    │    StoreCategory     │
                    │  (Classification)    │
                    └──────────┬───────────┘
                               │ 1
                               │
                               │ M
                    ┌──────────▼───────────┐
     ┌──────────────┤     Medication       ├──────────────┐
     │              │   (Master Data)      │              │
     │              └──────────┬───────────┘              │
     │ 1                       │ 1                        │ 1
     │                         │                          │
     │ M                       │ M                        │ M
┌────▼────────────┐   ┌────────▼──────────┐   ┌─────────▼──────────┐
│ MedicationLedger│   │ StoreLocationStock│   │ StoreStockMovement │
│ (Batch Tracking)│   │  (Stock by Loc)   │   │  (Audit Trail)     │
│                 │   │                   │   │                    │
│ - batch_number  │   │ - location_id     │   │ - movement_type    │
│ - expiry_date   │   │ - quantity        │   │ - transaction_type │
│ - unit_cost     │   │ - batch_number    │   │ - from_location    │
│ - qty_current   │   │                   │   │ - to_location      │
└────┬────────────┘   └────────┬──────────┘   │ - reference        │
     │                         │               └────────────────────┘
     │ M                       │ M
     │                         │
     │ 1                       │ 1
┌────▼─────────────────────────▼────────┐
│        StoreLocation                  │
│      (Storage Hierarchy)              │
│                                       │
│  - parent_id (self-referential)       │
│  - type (main_store, sub_store, etc.) │
└───────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                     PROCUREMENT RELATIONSHIP                         │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│  StoreSupplier   │
└────────┬─────────┘
         │ 1
         │
         │ M
┌────────▼──────────────┐
│ GoodsReceivedNote     │
│ (Procurement Doc)     │
│                       │
│ - invoice_number      │
│ - status              │
│ - total_amount        │
└────────┬──────────────┘
         │ 1
         │
         │ M
┌────────▼──────────────────────┐
│ GoodsReceivedNoteItem         │
│ (Line Items)                  │
│                               │
│ - medication_id ──────┐       │
│ - batch_number        │       │
│ - quantity_received   │       │
│ - unit_cost           │       │
└───────────────────────┘       │
                                │
         When GRN Posted:       │
         Creates ───────────────┼───────> MedicationLedger
         Updates ───────────────┼───────> Medication.stock_quantity
         Creates ───────────────┼───────> StoreLocationStock
         Logs ──────────────────┴───────> StoreStockMovement


┌─────────────────────────────────────────────────────────────────────┐
│                   REQUISITION RELATIONSHIP                           │
└─────────────────────────────────────────────────────────────────────┘

┌────────────────────┐           ┌────────────────────┐
│ StoreLocation      │           │ StoreLocation      │
│ (Requesting)       │           │ (Issuing)          │
└────────┬───────────┘           └────────┬───────────┘
         │ 1                              │ 1
         │                                │
         │ M                              │ M
         └────────────────┬───────────────┘
                          │
                ┌─────────▼────────────┐
                │ StoreRequisition     │
                │ (Transfer Request)   │
                │                      │
                │ - status             │
                │ - priority           │
                └─────────┬────────────┘
                          │ 1
                          │
                          │ M
                ┌─────────▼───────────────┐
                │ StoreRequisitionItem    │
                │ (Line Items)            │
                │                         │
                │ - medication_id         │
                │ - requested_quantity    │
                │ - approved_quantity     │
                │ - issued_quantity       │
                └─────────────────────────┘
                          │
         When Issued:     │
         Updates ─────────┼──────> StoreLocationStock (both locations)
         Logs ────────────┴──────> StoreStockMovement (2 entries)
```

## Complete Procurement Workflow

```
┌─────────────────────────────────────────────────────────────────────┐
│                   PROCUREMENT WORKFLOW (GRN)                         │
└─────────────────────────────────────────────────────────────────────┘

STEP 1: CREATE GRN
┌─────────────────────────┐
│ User creates GRN        │
│ - Select supplier       │
│ - Enter invoice details │
│ Status: DRAFT           │
└────────┬────────────────┘
         │
         ▼
STEP 2: ADD ITEMS
┌─────────────────────────────────┐
│ For each item:                  │
│ - Select medication             │
│ - Enter batch_number            │
│ - Enter expiry_date             │
│ - Enter quantity & unit_cost    │
│ - Select storage location       │
└────────┬────────────────────────┘
         │
         ▼
STEP 3: RECEIVE
┌─────────────────────────┐
│ Physical delivery       │
│ - Verify quantities     │
│ - Mark as received      │
│ Status: RECEIVED        │
└────────┬────────────────┘
         │
         ▼
STEP 4: VERIFY
┌─────────────────────────┐
│ Manager verifies        │
│ - Check invoice match   │
│ - Verify quantities     │
│ - Approve               │
│ Status: VERIFIED        │
└────────┬────────────────┘
         │
         ▼
STEP 5: POST (CRITICAL!)
┌─────────────────────────────────────────┐
│ System processes GRN                    │
│ Status: POSTED                          │
└────────┬────────────────────────────────┘
         │
         ├──────────────────┬──────────────────┬──────────────────┐
         │                  │                  │                  │
         ▼                  ▼                  ▼                  ▼
┌────────────────┐  ┌───────────────┐  ┌──────────────┐  ┌────────────┐
│ CREATE         │  │ UPDATE        │  │ CREATE       │  │ LOG        │
│ Medication     │  │ Medication    │  │ StoreLocation│  │ StoreStock │
│ Ledger         │  │ .stock_       │  │ Stock        │  │ Movement   │
│                │  │ quantity      │  │              │  │            │
│ - batch info   │  │ (+qty)        │  │ - qty at loc │  │ - inward   │
│ - expiry       │  │               │  │              │  │ - purchase │
│ - cost         │  │               │  │              │  │            │
└────────────────┘  └───────────────┘  └──────────────┘  └────────────┘

RESULT:
✅ Stock is in the system
✅ Available for dispensing/transfer
✅ Tracked by batch
✅ Expiry monitored
✅ Complete audit trail
```

## Complete Requisition Workflow

```
┌─────────────────────────────────────────────────────────────────────┐
│              REQUISITION WORKFLOW (Inter-Location Transfer)          │
└─────────────────────────────────────────────────────────────────────┘

SCENARIO: Ward 3 needs Paracetamol from Main Pharmacy

STEP 1: CREATE REQUISITION
┌─────────────────────────────────────┐
│ Ward 3 User                         │
│ - requesting_location: Ward 3       │
│ - issuing_location: Main Pharmacy   │
│ - Add items with quantities         │
│ Status: PENDING                     │
└────────┬────────────────────────────┘
         │
         ▼
STEP 2: SUBMIT
┌─────────────────────────┐
│ Submit for approval     │
│ → Notify manager        │
└────────┬────────────────┘
         │
         ▼
STEP 3: APPROVE
┌─────────────────────────────────┐
│ Main Pharmacy Manager           │
│ - Review request                │
│ - Check stock availability      │
│ - Set approved_quantity         │
│   (may be less if low stock)    │
│ Status: APPROVED                │
└────────┬────────────────────────┘
         │
         ▼
STEP 4: ISSUE
┌─────────────────────────────────────┐
│ Main Pharmacy Staff                 │
│ - Process issue                     │
│ - Select batches (FIFO/FEFO)        │
│ Status: ISSUED                      │
└────────┬────────────────────────────┘
         │
         ├──────────────┬─────────────────────┐
         │              │                     │
         ▼              ▼                     ▼
┌────────────────┐  ┌──────────────┐  ┌──────────────────┐
│ REDUCE Stock   │  │ INCREASE     │  │ LOG Movement     │
│ at Main        │  │ Stock at     │  │ (2 entries)      │
│ Pharmacy       │  │ Ward 3       │  │                  │
│                │  │              │  │ 1. Outward from  │
│ 1000 → 900     │  │ 0 → 100      │  │    Main Pharmacy │
│                │  │              │  │ 2. Inward to     │
│                │  │              │  │    Ward 3        │
└────────────────┘  └──────────────┘  └──────────────────┘

RESULT:
✅ Stock transferred between locations
✅ Both locations updated
✅ Complete audit trail with references
✅ Approved workflow documented
```

## Stock Level Tracking (Three-Tier System)

```
┌─────────────────────────────────────────────────────────────────────┐
│                   THREE-TIER STOCK TRACKING                          │
└─────────────────────────────────────────────────────────────────────┘

TIER 1: TOTAL STOCK (Medication Level)
┌────────────────────────────────────────────┐
│ Medication.stock_quantity = 1000           │
│ (Sum of all batches at all locations)      │
└────────────────────────────────────────────┘
                    │
         ┌──────────┴──────────┐
         │                     │
         ▼                     ▼

TIER 2: BATCH STOCK (MedicationLedger Level)
┌──────────────────────────┐    ┌──────────────────────────┐
│ Batch: BATCH2024001      │    │ Batch: BATCH2024002      │
│ quantity_current: 600    │    │ quantity_current: 400    │
│ expiry: 2026-01-15       │    │ expiry: 2026-03-20       │
│ unit_cost: 2.50          │    │ unit_cost: 2.45          │
└──────────┬───────────────┘    └──────────┬───────────────┘
           │                               │
    ┌──────┴────────┐                ┌────┴──────┐
    │               │                │           │
    ▼               ▼                ▼           ▼

TIER 3: LOCATION STOCK (StoreLocationStock Level)
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│ Main Pharmacy    │  │ Ward 3           │  │ Main Pharmacy    │  │ Ward 1           │
│ BATCH2024001     │  │ BATCH2024001     │  │ BATCH2024002     │  │ BATCH2024002     │
│ quantity: 400    │  │ quantity: 200    │  │ quantity: 300    │  │ quantity: 100    │
└──────────────────┘  └──────────────────┘  └──────────────────┘  └──────────────────┘

ALL THREE MUST STAY SYNCHRONIZED!

Medication.stock_quantity = Sum of all MedicationLedger.quantity_current
MedicationLedger.quantity_current = Sum of StoreLocationStock.quantity for that batch
```

## Movement Type Decision Tree

```
┌─────────────────────────────────────────────────────────────────────┐
│                  WHICH MOVEMENT TYPE TO USE?                         │
└─────────────────────────────────────────────────────────────────────┘

                      Is stock entering the system?
                                 │
                ┌────────────────┴────────────────┐
                │ YES                             │ NO
                ▼                                 ▼
        ┌──────────────┐              Is it leaving the system?
        │   INWARD     │                         │
        │              │              ┌──────────┴──────────┐
        │ From where?  │              │ YES                 │ NO
        │              │              ▼                     ▼
        │ - Supplier   │      ┌──────────────┐    Is it between locations?
        │   (purchase) │      │   OUTWARD    │             │
        │ - Return     │      │              │    ┌────────┴────────┐
        │ - Found      │      │ To where?    │    │ YES             │ NO
        └──────────────┘      │              │    ▼                 ▼
                              │ - Patient    │  ┌───────────┐  ┌──────────┐
                              │   (dispensing)│ │ TRANSFER  │  │ADJUSTMENT│
                              │ - Disposal   │  │           │  │          │
                              │   (waste)    │  │ Via what? │  │ Reason?  │
                              │ - Expired    │  │           │  │          │
                              └──────────────┘  │-Requisition│ │- Physical│
                                                │- Direct   │  │  count   │
                                                │  transfer │  │- Error   │
                                                └───────────┘  │  correct.│
                                                               └──────────┘
```

## Transaction Type Usage Guide

```
┌─────────────────────────────────────────────────────────────────────┐
│              TRANSACTION TYPES - WHEN TO USE EACH                    │
└─────────────────────────────────────────────────────────────────────┘

INWARD TRANSACTIONS (Stock Entering):
├─ purchase        : GRN posted, receiving from supplier
├─ return          : Patient returns unused medication
├─ transfer        : Direct transfer IN from another location
└─ adjustment      : Physical count found extra stock

OUTWARD TRANSACTIONS (Stock Leaving):
├─ dispensing      : Patient receives medication (prescription)
├─ consumption     : Department uses consumables
├─ waste           : Damaged items, spillage
├─ disposal        : Formal disposal (expired, contaminated)
├─ transfer        : Direct transfer OUT to another location
└─ adjustment      : Physical count found missing stock

TRANSFER TRANSACTIONS (Between Locations):
├─ requisition     : Via requisition workflow (approved)
└─ transfer        : Direct transfer (may not need approval)

ADJUSTMENT TRANSACTIONS (Corrections):
├─ adjustment      : Manual correction with documented reason
└─ reconciliation  : System vs physical count differences
```

## User Role Access Map

```
┌─────────────────────────────────────────────────────────────────────┐
│                    USER ROLES & ACCESS RIGHTS                        │
└─────────────────────────────────────────────────────────────────────┘

ADMINISTRATOR
├─ Manage medications (create, edit, delete)
├─ Manage locations, categories, units, formulations
├─ Manage suppliers
├─ Configure pricing
├─ View all reports
├─ Access reconciliation tools
├─ Manage users and permissions
└─ Full system access

STORE MANAGER
├─ Create and approve GRNs
├─ Approve requisitions
├─ Manage location stock
├─ Perform stock adjustments
├─ Manage expiry and disposal
├─ View reports for managed locations
└─ Monitor stock alerts

PHARMACY STAFF
├─ Create requisitions (request stock)
├─ Dispense medications to patients
├─ View stock at assigned location
├─ Record consumption
├─ Mark items for disposal
└─ Basic reporting

WARD/DEPARTMENT STAFF
├─ Create requisitions (request stock)
├─ View stock at assigned location
├─ Record consumption (basic)
└─ Limited reports

DOCTOR/CLINICIAN
├─ Create prescriptions
├─ View medication availability
└─ Read-only access to formulary

CASHIER
├─ Process medication cash sales
├─ View medication pricing
└─ Record OTC sales
```

## System Status Indicators

```
┌─────────────────────────────────────────────────────────────────────┐
│                      STATUS INDICATORS GUIDE                         │
└─────────────────────────────────────────────────────────────────────┘

MEDICATION STOCK STATUS:
🟢 In Stock       : quantity > reorder_level
🟡 Low Stock      : quantity ≤ reorder_level BUT > minimum_stock_level
🔴 Out of Stock   : quantity = 0
⚠️  Expiring Soon : Has batches expiring within 30 days
❌ Expired        : Has expired batches

GRN STATUS:
📝 Draft          : Being created, can be edited
📦 Received       : Physically received, awaiting verification
✓  Verified       : Verified by manager, ready to post
✅ Posted         : Posted to ledger, stock updated
🚫 Cancelled      : Cancelled, no stock impact

REQUISITION STATUS:
📝 Pending        : Created, awaiting approval
✓  Approved       : Approved by manager, ready to issue
📤 Partially Issued: Some items issued, some pending
✅ Issued         : All items issued, transfer complete
❌ Rejected       : Rejected by manager
🚫 Cancelled      : Cancelled by requester

BATCH STATUS:
🟢 Active         : Good stock, not expired, available
⏰ Expiring Soon  : < 30 days to expiry
❌ Expired        : Past expiry date
💀 Exhausted      : quantity_current = 0
🔧 Damaged        : Marked as unfit for use

EXPIRY ALERT LEVELS:
🔴 Expired        : Past expiry_date (0 days)
🟠 Critical       : < 7 days to expiry
🟡 Soon           : 7-14 days to expiry
🟢 Warning        : 14-30 days to expiry
⚪ Good           : > 30 days to expiry
```

## Key Performance Indicators (KPIs)

```
┌─────────────────────────────────────────────────────────────────────┐
│                     DASHBOARD KPIs EXPLAINED                         │
└─────────────────────────────────────────────────────────────────────┘

STOCK METRICS:
┌──────────────────────────────┐
│ Total Stock Value            │  Sum of (quantity × unit_cost) across all
│ $125,000                     │  locations and batches
└──────────────────────────────┘

┌──────────────────────────────┐
│ Active Medications           │  Count of is_active = true medications
│ 450                          │
└──────────────────────────────┘

┌──────────────────────────────┐
│ Stock Items                  │  Count of unique medication-location-batch
│ 1,250                        │  combinations with quantity > 0
└──────────────────────────────┘

ALERT METRICS:
┌──────────────────────────────┐
│ Low Stock Items              │  Medications with quantity ≤ reorder_level
│ ⚠️  23                       │  Action: Create purchase orders
└──────────────────────────────┘

┌──────────────────────────────┐
│ Expired Batches              │  Batches past expiry_date
│ ❌ 8                         │  Action: Dispose immediately
└──────────────────────────────┘

┌──────────────────────────────┐
│ Expiring Soon (30 days)      │  Batches expiring within 30 days
│ ⏰ 15                        │  Action: Fast-track usage or transfer
└──────────────────────────────┘

WORKFLOW METRICS:
┌──────────────────────────────┐
│ Pending GRNs                 │  GRNs in draft/received/verified status
│ 5                            │  Action: Review and post
└──────────────────────────────┘

┌──────────────────────────────┐
│ Pending Requisitions         │  Requisitions awaiting approval/issue
│ 12                           │  Action: Approve and issue
└──────────────────────────────┘

┌──────────────────────────────┐
│ Today's Movements            │  Stock movements created today
│ 47                           │  Monitor for unusual activity
└──────────────────────────────┘

CONSUMPTION METRICS:
┌──────────────────────────────┐
│ Monthly Consumption          │  Total quantity dispensed this month
│ 15,250 units                 │  Use for forecasting
└──────────────────────────────┘

┌──────────────────────────────┐
│ Average Daily Consumption    │  Monthly consumption ÷ 30
│ 508 units/day                │  Use for reorder calculations
└──────────────────────────────┘
```

## Report Types Quick Reference

```
┌─────────────────────────────────────────────────────────────────────┐
│                       REPORTS AVAILABLE                              │
└─────────────────────────────────────────────────────────────────────┘

STOCK REPORTS:
├─ Stock Levels Report
│  └─ Current stock by medication/location
│      Shows: quantity, unit_cost, value, expiry_date
│
├─ Low Stock Report
│  └─ Medications below reorder_level
│      Sorted by urgency (days until stockout)
│
├─ Stock Valuation Report
│  └─ Total inventory value by category/location
│      Shows: quantity, unit_cost, total_value
│
└─ Location Stock Report
   └─ Stock breakdown by storage location
       Shows: stock at each location, value, space utilization

MOVEMENT REPORTS:
├─ Movement History Report
│  └─ All stock movements with filters
│      By: date_range, medication, location, type
│
├─ Inward Movement Report
│  └─ All stock received (purchases, returns)
│      Shows: supplier, quantity, cost, date
│
├─ Outward Movement Report
│  └─ All stock issued (dispensing, waste)
│      Shows: destination, quantity, reason, date
│
└─ Transfer Report
   └─ Inter-location movements
       Shows: from_location, to_location, quantity, date

EXPIRY REPORTS:
├─ Expired Batches Report
│  └─ All expired stock requiring disposal
│      Shows: batch, expiry_date, quantity, value
│
├─ Expiring Soon Report
│  └─ Batches expiring within threshold (7/30/60/90 days)
│      Shows: batch, days_to_expiry, quantity, action_required
│
└─ Expiry Calendar
   └─ Monthly view of upcoming expiries
       Visual calendar showing expiry timeline

CONSUMPTION REPORTS:
├─ Consumption by Medication
│  └─ Usage statistics per medication
│      Shows: quantity_used, frequency, average_per_day
│
├─ Consumption by Department
│  └─ Usage by ward/department
│      Shows: top_medications, total_consumption, cost
│
├─ Patient Consumption History
│  └─ Medications dispensed to specific patient
│      Shows: date, medication, quantity, prescriber
│
└─ Consumption Trend Analysis
   └─ Usage patterns over time
       Shows: trends, seasonality, forecasts

FINANCIAL REPORTS:
├─ Purchase Report
│  └─ All purchases from suppliers
│      Shows: supplier, invoice, amount, date
│
├─ Stock Valuation by Category
│  └─ Inventory value breakdown
│      Shows: category, quantity, value, percentage
│
└─ Cost Analysis Report
   └─ Cost trends and variance analysis
       Shows: unit_cost_trends, supplier_comparison

ANALYTICS REPORTS:
├─ ABC Analysis
│  └─ Classify medications by value/usage
│      A: 70-80% of value (tight control)
│      B: 15-20% of value (moderate control)
│      C: 5-10% of value (basic control)
│
├─ Fast/Slow Moving Analysis
│  └─ Identify frequently/rarely used items
│      Shows: turnover_rate, days_to_consume
│
└─ Stock-out Analysis
   └─ Frequency and duration of stockouts
       Shows: medication, stockout_days, lost_opportunities

COMPLIANCE REPORTS:
├─ Controlled Substances Report
│  └─ Tracking of controlled medications
│      Shows: usage, remaining_stock, discrepancies
│
├─ Disposal Report
│  └─ All disposed medications with reasons
│      Shows: medication, quantity, reason, witnesses
│
└─ Audit Trail Report
   └─ Complete transaction history for audit
       Shows: all_movements, users, timestamps, references
```

---

## Quick Tips for Users

### For Efficient Stock Management

1. **Always post GRNs promptly** after verification to update stock
2. **Use batch numbers consistently** for accurate FIFO/FEFO
3. **Set expiry dates** when receiving stock to enable alerts
4. **Perform regular physical counts** and adjust discrepancies
5. **Monitor low stock alerts** to prevent stockouts
6. **Process requisitions promptly** to maintain service levels
7. **Dispose expired items** immediately to prevent use

### For Accurate Data

1. **Use correct movement types** (inward/outward/transfer/adjustment)
2. **Always provide reasons** for adjustments
3. **Select appropriate transaction types** (purchase/dispensing/waste/etc.)
4. **Reference source documents** when logging movements
5. **Verify quantities** before posting GRNs
6. **Use batch selection** (FIFO/FEFO) when dispensing

### For Better Reporting

1. **Maintain consistent categories** for medications
2. **Use locations correctly** in the hierarchy
3. **Record unit costs accurately** for valuation
4. **Set reorder levels** based on consumption patterns
5. **Use filters effectively** when viewing reports
6. **Export reports regularly** for external analysis

---

**For complete details, refer to:**
- [Quick Reference Guide](../MEDICATIONS_SYSTEM_SUMMARY.md)
- [Complete Architecture Guide](../MEDICATIONS_SYSTEM_ARCHITECTURE.md)
