# Medications/Items Management System - Quick Reference

This is a quick reference guide for the medications/items management system. For the complete architecture guide, see [MEDICATIONS_SYSTEM_ARCHITECTURE.md](./MEDICATIONS_SYSTEM_ARCHITECTURE.md).

## System Overview

The medications/items management system is split across two main areas:

### 1. **Store Management** (Operational Focus)
**Location:** `app/Http/Controllers/Store/` and `resources/views/store/`

**Handles:**
- ✅ Procurement (Goods Received Notes - GRN)
- ✅ Storage locations management
- ✅ Stock movements and transfers
- ✅ Batch tracking with expiry dates
- ✅ Requisitions (inter-location transfers)
- ✅ Stock adjustments and reconciliation
- ✅ Complete audit trail

**Key Controllers:**
- `GoodsReceivedNoteController` - Procurement from suppliers
- `StoreRequisitionController` - Inter-location transfers
- `MedicationLedgerController` - Batch tracking with expiry
- `StoreLocationStockController` - Stock per location
- `StoreStockMovementController` - Audit trail
- `StoreLocationController` - Manage storage locations
- `StoreCategoryController` - Item categories
- `StoreSupplierController` - Supplier management

### 2. **Medications Management** (Master Data Focus)
**Location:** `app/Http/Controllers/Medication*.php` and `resources/views/medications/`

**Handles:**
- ✅ Medication master data (name, strength, formulation)
- ✅ Units of measurement
- ✅ Medication formulations (tablet, liquid, injection, etc.)
- ✅ Pricing by patient category
- ✅ Medication search and lookup
- ✅ Reports and analytics

**Key Controllers:**
- `MedicationController` - Medication CRUD
- `MedicationUnitController` - Units (tablets, ml, boxes, etc.)
- `MedicationFormulationController` - Forms (tablet, capsule, liquid, etc.)
- `MedicationPricingController` - Pricing by patient category
- `MedicationSearchController` - Search/autocomplete

---

## Core Data Models

### Primary Models

| Model | Purpose | Key Feature |
|-------|---------|------------|
| **Medication** | Medication master data | Central entity, stock quantity, reorder levels |
| **MedicationLedger** | Batch tracking | Batch number, expiry date, unit cost |
| **StoreLocation** | Storage locations | Hierarchical structure (parent_id) |
| **StoreLocationStock** | Stock per location | Quantity at each location by batch |
| **StoreStockMovement** | Complete audit trail | Every stock transaction logged |
| **GoodsReceivedNote** | Procurement document | Purchase from supplier |
| **StoreRequisition** | Transfer request | Inter-location with approval workflow |

### Supporting Models

| Model | Purpose |
|-------|---------|
| **MedicationFormulation** | Medication forms (tablet, liquid, injection, etc.) |
| **MedicationUnit** | Units (tablets, ml, boxes, kg, etc.) |
| **StoreCategory** | Item classification (medications, consumables, etc.) |
| **StoreSupplier** | Supplier master data |
| **MedicationPricing** | Prices by patient category |
| **GoodsReceivedNoteItem** | Line items in GRN |
| **StoreRequisitionItem** | Line items in requisition |

---

## Key Workflows (Step-by-Step)

### 1. Procurement Workflow (Receiving Stock from Supplier)

**Route:** `/medications/goods-received-notes`

**Steps:**
1. **Create GRN** → Status: "draft"
2. **Add items** with batch details (batch number, expiry date, quantity, unit cost)
3. **Receive delivery** → Status: "received"
4. **Verify invoice** → Status: "verified"
5. **Post GRN** → Status: "posted"
   - ✅ Creates `MedicationLedger` entries (batches)
   - ✅ Updates `Medication.stock_quantity`
   - ✅ Creates `StoreLocationStock` entries
   - ✅ Logs `StoreStockMovement` (inward, purchase)

**Result:** Stock is now in the system and available for use.

---

### 2. Requisition Workflow (Transfer Between Locations)

**Route:** `/store/requisitions`

**Steps:**
1. **Create requisition** (e.g., Ward 3 requests from Main Pharmacy) → Status: "pending"
2. **Add items** with requested quantities
3. **Approve** (Manager reviews and sets approved quantities) → Status: "approved"
4. **Issue** (Pharmacy staff processes) → Status: "issued"
   - ✅ Reduces `StoreLocationStock` at issuing location
   - ✅ Increases `StoreLocationStock` at requesting location
   - ✅ Logs `StoreStockMovement` (transfer, requisition)

**Result:** Stock transferred between locations with audit trail.

---

### 3. Dispensing Workflow (Issuing to Patient)

**Route:** `/medications/consumption/prescriptions`

**Steps:**
1. Doctor creates prescription
2. Pharmacist/Nurse views pending prescriptions
3. Selects prescription and dispenses items
   - ✅ Reduces `StoreLocationStock` at dispensing location
   - ✅ Reduces `MedicationLedger.quantity_current` (batch level)
   - ✅ Reduces `Medication.stock_quantity` (total)
   - ✅ Logs `StoreStockMovement` (outward, dispensing)

**Result:** Patient receives medication, stock updated, audit trail created.

---

### 4. Stock Adjustment Workflow (Physical Count)

**Route:** `/store-locations-stock/{stock}/adjust`

**Steps:**
1. Perform physical count
2. Create adjustment with reason
   - ✅ Updates `StoreLocationStock.quantity`
   - ✅ Updates `Medication.stock_quantity`
   - ✅ Logs `StoreStockMovement` (adjustment)

**Result:** System matches physical count with documented discrepancy.

---

### 5. Expiry Management Workflow

**Route:** `/medications/stock/ledger/expiry-report`

**Steps:**
1. View expiry report (expired, expiring critical, expiring soon)
2. Mark batch as unfit (if expired)
3. Create disposal record
   - ✅ Updates `MedicationLedger` status
   - ✅ Reduces stock to 0
   - ✅ Logs `StoreStockMovement` (waste, disposal)

**Result:** Expired medication properly disposed with audit trail.

---

## Key Routes (Quick Reference)

### Medication Management Routes

| Route | Purpose |
|-------|---------|
| `/medications` | List medications |
| `/medications/create` | Create medication |
| `/medications/dashboard` | Medication dashboard |
| `/medications/goods-received-notes` | Procurement (GRN) |
| `/medications/stock/ledger` | Batch tracking |
| `/medications/stock/ledger/expiry-report` | Expiry alerts |
| `/medications/suppliers` | Supplier management |
| `/medications/stock/transfers` | Direct transfers |
| `/medications/stock/adjustments` | Stock adjustments |
| `/medications/stock/disposal` | Disposal management |
| `/medications/consumption` | Consumption tracking |
| `/medications/reconciliation` | Data integrity checks |
| `/medications/reports` | Reports menu |
| `/medications/formulations` | Formulation management |

### Store Management Routes

| Route | Purpose |
|-------|---------|
| `/store/requisitions` | Inter-location transfers |
| `/store-categories` | Category management |
| `/store-locations` | Location management |
| `/store-locations-stock` | Stock per location |
| `/store-stock-movements` | Movement audit trail |

### Supporting Routes

| Route | Purpose |
|-------|---------|
| `/medication-units` | Unit management |
| `/medication-pricing` | Pricing management |
| `/medication-frequencies` | Dosing frequencies |
| `/medication-cash-sales` | OTC sales |

---

## Quick Access to Common Tasks

### For Store Manager

**View Stock Levels:**
- `/medications/stock/levels` - Current stock overview
- `/store-locations-stock` - Stock by location

**Receive Stock:**
- `/medications/goods-received-notes/create` - Create GRN
- Process → Verify → Post

**Check Expiring Items:**
- `/medications/stock/ledger/expiry-report` - Expiry alerts

**Approve Requisitions:**
- `/store/requisitions` - Pending requisitions (filtered by your location)

**Review Movements:**
- `/store-stock-movements` - Complete audit trail

### For Pharmacy Staff

**Dispense Medications:**
- `/medications/consumption/prescriptions` - Pending prescriptions

**Create Requisition:**
- `/store/requisitions/create` - Request items from another location

**Check Stock:**
- `/store-locations-stock` - Your location's stock

**Adjust Stock:**
- `/store-locations-stock/{stock}/adjust` - Physical count correction

### For Administrator

**Manage Medications:**
- `/medications` - Medication list
- `/medications/create` - Add medication

**Manage Locations:**
- `/store-locations` - Location hierarchy

**Manage Categories:**
- `/store-categories` - Item categories

**Manage Units:**
- `/medication-units` - Units of measurement

**Manage Formulations:**
- `/medications/formulations` - Medication forms

**Run Reports:**
- `/medications/reports` - Various reports
- `/medications/reconciliation` - Data integrity

---

## Important Concepts

### Stock Quantity Tracking

The system maintains stock at **three levels**:

1. **Medication.stock_quantity** (Total across all locations and batches)
2. **MedicationLedger.quantity_current** (Per batch)
3. **StoreLocationStock.quantity** (Per location per batch)

**All three must stay synchronized!**

### Movement Types

| Type | Direction | Example |
|------|-----------|---------|
| **inward** | Into system | GRN from supplier |
| **outward** | Out of system | Dispensing to patient |
| **transfer** | Between locations | Requisition |
| **adjustment** | Correction | Physical count |
| **waste** | Disposal | Expired items |

### Transaction Types

| Type | Context | Logged As |
|------|---------|-----------|
| **purchase** | GRN posted | inward |
| **dispensing** | Patient receives | outward |
| **requisition** | Location transfer | transfer |
| **transfer** | Direct transfer | transfer |
| **adjustment** | Manual correction | adjustment |
| **waste** | Damaged items | waste |
| **disposal** | Formal disposal | waste |
| **return** | Patient/supplier return | inward |

### Batch Tracking

Every procurement creates a **MedicationLedger** entry with:
- ✅ Unique batch_number (from supplier)
- ✅ manufacture_date
- ✅ expiry_date (critical for FEFO/FIFO)
- ✅ unit_cost (for financial tracking)
- ✅ quantity_current (remaining stock)

**FIFO (First In, First Out):** Dispense oldest batches first  
**FEFO (First Expired, First Out):** Dispense batches expiring soonest first

### Location Hierarchy

Locations can be nested:

```
Main Store
├── Pharmacy
│   ├── Outpatient Pharmacy
│   └── Inpatient Pharmacy
└── Departments
    ├── Ward 1
    ├── Ward 2
    └── ICU
```

This enables:
- Cascading permissions
- Hierarchical reporting
- Multi-tier distribution

### Approval Workflows

**GRN Workflow:**
```
draft → received → verified → posted
```

**Requisition Workflow:**
```
pending → approved → issued
```

Status transitions are controlled and logged for accountability.

---

## Key Files to Understand

### Controllers (Business Logic)

**Store Operations:**
- `app/Http/Controllers/Store/GoodsReceivedNoteController.php` - 35KB, procurement
- `app/Http/Controllers/Store/StoreRequisitionController.php` - 18KB, transfers
- `app/Http/Controllers/Store/MedicationLedgerController.php` - 20KB, batch tracking
- `app/Http/Controllers/Store/StoreStockMovementController.php` - 11KB, audit trail

**Medication Master Data:**
- `app/Http/Controllers/MedicationController.php` - Medication CRUD
- `app/Http/Controllers/MedicationUnitController.php` - Units
- `app/Http/Controllers/MedicationFormulationController.php` - Formulations

### Models (Data Structure)

**Core Models:**
- `app/Models/Medication.php` - Central entity
- `app/Models/MedicationLedger.php` - Batch tracking
- `app/Models/StoreLocation.php` - Storage locations
- `app/Models/StoreLocationStock.php` - Stock per location
- `app/Models/StoreStockMovement.php` - Audit trail
- `app/Models/GoodsReceivedNote.php` - Procurement
- `app/Models/StoreRequisition.php` - Transfers

### Routes (URL Structure)

- `routes/web.php` - Main routes (line 662 includes medication.php, line 712 includes requisitions.php)
- `routes/medication.php` - Medication-specific routes
- `routes/requisitions.php` - Requisition routes

### Views (User Interface)

**Medication Views:**
- `resources/views/medications/` - Medication UI
  - `index.blade.php` - List medications
  - `create.blade.php` - Create form
  - `dashboard.blade.php` - Dashboard
  - `stock/` - Stock management views
  - `consumption/` - Dispensing views
  - `reconciliation/` - Integrity checks
  - `reports/` - Reports

**Store Views:**
- `resources/views/store/` - Store UI
  - `dashboard.blade.php` - Store dashboard
  - `requisitions/` - Requisition views

---

## Troubleshooting Common Issues

### Discrepancy Between System and Physical Count

**Solution:** Use stock adjustment
- Route: `/store-locations-stock/{stock}/adjust`
- Enter actual quantity and reason
- System logs adjustment in audit trail

### Stock Not Updating After GRN

**Check:**
1. Is GRN status "posted"? (Must post to update stock)
2. Are items added to GRN?
3. Check `store-stock-movements` for inward entries

**Solution:** Post the GRN if still in "verified" status

### Requisition Not Issuing

**Check:**
1. Is requisition "approved"?
2. Is stock available at issuing location?
3. Check user permissions

**Solution:** Ensure approval and sufficient stock

### Expired Batches Not Showing

**Check:**
1. Is expiry_date set in MedicationLedger?
2. Run expiry report: `/medications/stock/ledger/expiry-report`

**Solution:** Set expiry dates when receiving stock (GRN)

---

## System Strengths

✅ **Complete Traceability** - Every transaction logged with reference  
✅ **Batch Tracking** - Full expiry date and cost tracking  
✅ **Multi-Location Support** - Hierarchical storage structure  
✅ **Approval Workflows** - Accountability for procurement and transfers  
✅ **Expiry Management** - Automated alerts and disposal tracking  
✅ **Cost Tracking** - Unit costs per batch for financial reporting  
✅ **Flexible Pricing** - Multiple prices by patient category  
✅ **Real-time Stock Status** - Dynamic calculations (in stock, low stock, expiring)  
✅ **Comprehensive Reporting** - Stock, movement, expiry, consumption reports  
✅ **Data Integrity** - Reconciliation and integrity checks  

---

## Next Steps

1. **For New Users:**
   - Review this summary
   - Read relevant sections in [MEDICATIONS_SYSTEM_ARCHITECTURE.md](./MEDICATIONS_SYSTEM_ARCHITECTURE.md)
   - Follow the workflows for your role

2. **For Developers:**
   - Read the complete architecture guide
   - Study the models and their relationships
   - Understand the data flow diagrams
   - Review controller methods for business logic

3. **For Administrators:**
   - Set up locations, categories, units, formulations
   - Create medications
   - Configure pricing
   - Train users on workflows

---

## Support

For detailed information on any aspect of the system, refer to:
- **[MEDICATIONS_SYSTEM_ARCHITECTURE.md](./MEDICATIONS_SYSTEM_ARCHITECTURE.md)** - Complete architecture guide
- **Code comments** - Inline documentation in controllers and models
- **Route files** - `routes/web.php`, `routes/medication.php`, `routes/requisitions.php`

---

**System Version:** Laravel-based Inventory Management System  
**Last Updated:** 2026-02-19  
**Documentation Author:** System Analysis Agent
