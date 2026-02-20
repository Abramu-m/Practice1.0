# Medications/Items Management System - Complete Architecture Guide

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture Layers](#architecture-layers)
3. [Core Models & Relationships](#core-models--relationships)
4. [Controllers & Their Functions](#controllers--their-functions)
5. [Key Workflows](#key-workflows)
6. [Views Organization](#views-organization)
7. [Routes Structure](#routes-structure)
8. [Data Flow Diagrams](#data-flow-diagrams)

---

## System Overview

This is a **comprehensive inventory management system** for medications and medical consumables built with Laravel. The system provides:

- **Multi-tier inventory management** with location-based storage
- **Batch tracking** with expiry date management
- **Full audit trail** for all stock movements
- **Requisition workflows** for inter-location transfers
- **Procurement management** via Goods Received Notes (GRN)
- **Real-time stock monitoring** with alerts for low stock and expiring items
- **Cost tracking** at batch level for financial reporting
- **Flexible pricing** by patient categories

The system is split between two main areas:
1. **Store Management** (under `app/Http/Controllers/Store/`) - Handles procurement, locations, stock movements
2. **Medications Management** (under `app/Http/Controllers/Medication*`) - Handles medication master data, units, formulations

---

## Architecture Layers

### 1. Models Layer (Database/Business Logic)
**Location:** `app/Models/`

Core entities representing the data structure and business rules.

### 2. Controllers Layer (Request Handling)
**Location:** `app/Http/Controllers/` and `app/Http/Controllers/Store/`

Handles HTTP requests and coordinates between views and models.

### 3. Services Layer (Business Logic)
**Location:** `app/Services/`

- `StoreRequisitionService.php` - Complex requisition processing logic

### 4. Views Layer (Presentation)
**Location:** `resources/views/`

Blade templates organized by functionality:
- `resources/views/medications/` - Medication-related views
- `resources/views/store/` - Store management views
- `resources/views/medication-ledger/` - Batch tracking views
- `resources/views/store-locations-stock/` - Location stock views

### 5. Routes Layer (URL Mapping)
**Location:** `routes/`

- `web.php` - Main routes file
- `medication.php` - Medication-specific routes (included in web.php)
- `requisitions.php` - Store requisition routes (included in web.php)

---

## Core Models & Relationships

### 1. **Medication** (Central Master Data)
**File:** `app/Models/Medication.php`

**Purpose:** Defines all medications and consumables in the system

**Key Fields:**
- `generic_name` - Standard medication name
- `brand_name` - Commercial name
- `strength` - Dosage strength (e.g., "500mg")
- `formulation_id` - Form type (tablet, liquid, etc.)
- `dispensing_unit_id` - Unit of dispensing (tablets, ml, etc.)
- `stock_quantity` - Current total stock across all locations
- `minimum_stock_level` - Reorder threshold
- `reorder_level` - Recommended reorder point
- `maximum_stock_level` - Storage capacity limit
- `category_id` - Classification (medications, consumables, etc.)
- `is_controlled` - Requires special handling
- `requires_prescription` - Cannot be sold OTC
- `is_active` - Enabled for use

**Computed Attributes:**
- `stock_status` - "In Stock", "Low Stock", "Out of Stock", "Expiring Soon", "Expired"
- `is_in_stock` - Boolean check
- `is_low_stock` - Below reorder level
- `is_expired` - Has expired batches
- `is_expiring_soon` - Has batches expiring within 30 days

**Relationships:**
```php
storeCategory() → StoreCategory (1:1 - classification)
formulation() → MedicationFormulation (1:1 - form type)
dispensingUnit() → MedicationUnit (1:1 - unit)
ledgerEntries() → MedicationLedger (1:M - batch tracking)
pricing() → MedicationPricing (1:M - prices by patient category)
stockMovements() → StoreStockMovement (1:M - audit trail)
locationStocks() → StoreLocationStock (1:M - stock per location)
```

---

### 2. **MedicationLedger** (Batch Tracking)
**File:** `app/Models/MedicationLedger.php`

**Purpose:** Tracks individual batches of medications with expiry dates and costs

**Key Fields:**
- `medication_id` - Links to Medication
- `batch_number` - Unique batch identifier from supplier
- `manufacture_date` - When manufactured
- `expiry_date` - Expiration date (critical for FEFO/FIFO)
- `unit_cost` - Cost per unit for this batch
- `quantity_received` - Initial quantity
- `quantity_current` - Remaining quantity
- `status` - "active", "expired", "exhausted", "damaged"
- `location_id` - Primary storage location

**Computed Attributes:**
- `is_expired` - Past expiry date
- `is_expiring_soon` - Within 30 days of expiry
- `has_stock` - quantity_current > 0

**Key Methods:**
- `reduceQuantity($amount)` - Decrease stock (dispensing)
- `adjustQuantity($newQuantity, $reason)` - Manual adjustment
- `markDamaged($reason)` - Flag as damaged/unusable
- `updateExpiredBatches()` - Auto-mark expired batches (cron job)

**Scopes:**
```php
active() - Only active batches
expired() - Past expiry date
expiring() - Within 30 days
expiringSoon() - Within threshold
forMedication($id) - By medication
atLocation($id) - By location
```

**Relationships:**
```php
medication() → Medication (M:1)
location() → StoreLocation (M:1)
```

---

### 3. **StoreLocation** (Multi-Level Storage)
**File:** `app/Models/StoreLocation.php`

**Purpose:** Represents physical storage locations in hierarchical structure

**Key Fields:**
- `name` - Location name (e.g., "Main Pharmacy", "Ward 3 Store")
- `code` - Short identifier (e.g., "MP", "W3")
- `type` - "main_store", "sub_store", "department", "ward", "dispensary"
- `parent_id` - Hierarchical relationship (self-referential)
- `manager_name` - Person responsible
- `can_request` - Can create requisitions
- `can_issue` - Can fulfill requisitions
- `can_receive` - Can receive GRNs
- `requires_approval` - Requisitions need approval
- `is_active` - Enabled for use

**Relationships:**
```php
parent() → StoreLocation (M:1 - parent location)
children() → StoreLocation (1:M - sub-locations)
stockItems() → StoreLocationStock (1:M - inventory at this location)
outgoingRequisitions() → StoreRequisition (1:M - requests from here)
incomingRequisitions() → StoreRequisition (1:M - requests to here)
stockMovements() → StoreStockMovement (1:M - movements involving this location)
```

**Example Hierarchy:**
```
Main Store (main_store)
├── Pharmacy (sub_store)
│   ├── Outpatient Pharmacy (dispensary)
│   └── Inpatient Pharmacy (dispensary)
└── Departments
    ├── Ward 1 (ward)
    ├── Ward 2 (ward)
    └── ICU (department)
```

---

### 4. **StoreLocationStock** (Location-Based Inventory)
**File:** `app/Models/StoreLocationStock.php`

**Purpose:** Tracks quantity of each medication at each location (granular inventory)

**Key Fields:**
- `location_id` - Storage location
- `medication_id` - Medication item
- `batch_number` - Batch identifier
- `quantity` - Current stock level
- `unit_cost` - Cost per unit
- `status` - "active", "expired", "depleted"
- `expiry_date` - Batch expiry date (denormalized for quick filtering)

**Key Methods:**
- `updateQuantity($newQuantity)` - Set new quantity
- `reduceQuantity($amount)` - Decrease (dispensing/transfer)
- `isAvailable($amount)` - Check if sufficient stock
- `isExpiringWithin($days)` - Expiry check
- `getExpiryStatus()` - Get detailed expiry status

**Expiry Status Levels:**
- `no_expiry` - No expiry date set
- `expired` - Past expiry date
- `expiring_critical` - < 7 days
- `expiring_soon` - 7-14 days
- `expiring_warning` - 14-30 days
- `good` - > 30 days

**Scopes:**
```php
active() - Status = active
expired() - Past expiry
depleted() - quantity = 0
atLocation($id) - By location
forMedication($id) - By medication
expiringWithin($days) - Near expiry
```

**Relationships:**
```php
location() → StoreLocation (M:1)
medication() → Medication (M:1)
ledger() → MedicationLedger (M:1 via batch_number)
```

---

### 5. **StoreStockMovement** (Complete Audit Trail)
**File:** `app/Models/StoreStockMovement.php`

**Purpose:** Records every stock transaction for complete traceability and accountability

**Key Fields:**
- `medication_id` - Which medication
- `movement_type` - "inward", "outward", "transfer", "adjustment", "waste"
- `transaction_type` - "purchase", "dispensing", "requisition", "transfer", "adjustment", "waste", "return", "consumption", "disposal"
- `quantity` - Amount moved
- `from_location_id` - Source location (nullable for inward)
- `to_location_id` - Destination location (nullable for outward)
- `balance_before` - Stock before transaction
- `balance_after` - Stock after transaction
- `unit_cost` - Cost per unit
- `reference_type` - Related model (GoodsReceivedNote, StoreRequisition, etc.)
- `reference_id` - ID of related record
- `batch_number` - Batch involved
- `notes` - Additional information
- `created_by` - User who created this movement
- `movement_date` - When movement occurred

**Key Methods:**
- `getMovementHistory($medicationId)` - Full history for a medication
- `getTotalInward($medicationId, $startDate, $endDate)` - Total received
- `getTotalOutward($medicationId, $startDate, $endDate)` - Total issued
- `getNetBalance($medicationId)` - Calculate net stock from movements

**Movement Type Definitions:**
- **inward**: Stock entering the system (GRN, returns)
- **outward**: Stock leaving (dispensing, waste)
- **transfer**: Between locations
- **adjustment**: Manual corrections
- **waste**: Disposal/damage

**Transaction Type Definitions:**
- **purchase**: From supplier (GRN)
- **dispensing**: To patient
- **requisition**: Inter-location transfer via requisition
- **transfer**: Direct inter-location transfer
- **adjustment**: Stock count correction
- **waste**: Damaged/expired disposal
- **return**: Patient return, supplier return
- **consumption**: Department usage
- **disposal**: Formal disposal process

**Scopes:**
```php
inward() - Only inward movements
outward() - Only outward movements
transfer() - Only transfers
adjustment() - Only adjustments
byReference($type, $id) - By related record
byMedication($id) - By medication
byLocation($id) - Involving location
byDateRange($start, $end) - Date filter
```

**Relationships:**
```php
medication() → Medication (M:1)
fromLocation() → StoreLocation (M:1)
toLocation() → StoreLocation (M:1)
creator() → User (M:1)
reference() → Polymorphic (GRN, Requisition, etc.)
```

---

### 6. **GoodsReceivedNote (GRN)** (Procurement Document)
**File:** `app/Models/GoodsReceivedNote.php`

**Purpose:** Tracks procurement of medications from suppliers

**Key Fields:**
- `grn_number` - Unique GRN identifier
- `supplier_id` - Supplier providing items
- `invoice_number` - Supplier invoice reference
- `invoice_date` - Invoice date
- `delivery_date` - When received
- `total_amount` - Total value
- `status` - "draft", "received", "verified", "posted", "cancelled"
- `received_by` - User who received
- `received_at` - Timestamp
- `verified_by` - User who verified
- `verified_at` - Timestamp
- `posted_by` - User who posted to ledger
- `posted_at` - Timestamp
- `requires_approval` - Needs manager approval
- `notes` - Additional information

**Status Workflow:**
```
draft → received → verified → posted
                    ↓
                 cancelled
```

**Relationships:**
```php
supplier() → StoreSupplier (M:1)
items() → GoodsReceivedNoteItem (1:M - line items)
receivedBy() → User (M:1)
verifiedBy() → User (M:1)
postedBy() → User (M:1)
```

---

### 7. **GoodsReceivedNoteItem** (GRN Line Items)
**File:** `app/Models/GoodsReceivedNoteItem.php`

**Purpose:** Individual items in a GRN with batch details

**Key Fields:**
- `goods_received_note_id` - Parent GRN
- `medication_id` - Medication received
- `batch_number` - Batch from supplier
- `manufacture_date` - Manufacturing date
- `expiry_date` - Expiration date
- `quantity_ordered` - Expected quantity
- `quantity_received` - Actually received
- `unit_cost` - Cost per unit
- `store_unit_id` - Unit of purchase (boxes, bottles, etc.)
- `conversion_factor` - Boxes to individual units
- `location_id` - Where to store

**Post Processing (when GRN is posted):**
1. Creates `MedicationLedger` entry for each item
2. Updates `Medication.stock_quantity`
3. Creates `StoreLocationStock` entries if location assigned
4. Logs `StoreStockMovement` (inward, transaction_type: purchase)

**Relationships:**
```php
goodsReceivedNote() → GoodsReceivedNote (M:1)
medication() → Medication (M:1)
storeUnit() → StoreUnit (M:1)
location() → StoreLocation (M:1)
```

---

### 8. **StoreRequisition** (Inter-Location Transfer Request)
**File:** `app/Models/StoreRequisition.php`

**Purpose:** Request medications from one location to another with approval workflow

**Key Fields:**
- `requisition_number` - Unique identifier
- `requesting_location_id` - Location requesting
- `issuing_location_id` - Location that will issue
- `requested_by` - User creating request
- `status` - "pending", "approved", "partially_issued", "issued", "rejected", "cancelled"
- `priority` - "low", "normal", "high", "urgent"
- `request_date` - When created
- `required_date` - When needed
- `approved_by` - User who approved
- `approved_at` - Approval timestamp
- `issued_by` - User who issued
- `issued_at` - Issue timestamp
- `notes` - Request notes
- `rejection_reason` - Why rejected

**Status Workflow:**
```
pending → approved → partially_issued → issued
   ↓
rejected / cancelled
```

**Relationships:**
```php
requestingLocation() → StoreLocation (M:1)
issuingLocation() → StoreLocation (M:1)
items() → StoreRequisitionItem (1:M - requested items)
requestedBy() → User (M:1)
approvedBy() → User (M:1)
issuedBy() → User (M:1)
```

---

### 9. **StoreRequisitionItem** (Requisition Line Items)
**File:** `app/Models/StoreRequisitionItem.php`

**Purpose:** Individual medications requested in a requisition

**Key Fields:**
- `store_requisition_id` - Parent requisition
- `medication_id` - Medication requested
- `requested_quantity` - Amount requested
- `approved_quantity` - Amount approved (may differ)
- `issued_quantity` - Amount actually issued
- `unit_cost` - Cost per unit (for valuation)
- `batch_number` - Batch issued from
- `notes` - Item-specific notes

**Processing Flow:**
1. User requests `requested_quantity`
2. Approver sets `approved_quantity` (may be less if insufficient stock)
3. Issuer processes and sets `issued_quantity`
4. Creates `StoreStockMovement` (transfer type)
5. Updates `StoreLocationStock`:
   - Decreases at `issuing_location`
   - Increases at `requesting_location`

**Relationships:**
```php
requisition() → StoreRequisition (M:1)
medication() → Medication (M:1)
```

---

### Supporting Models

#### **MedicationFormulation**
**File:** `app/Models/MedicationFormulation.php`

Defines medication forms: tablet, capsule, liquid, injection, cream, ointment, syrup, suspension, powder, inhaler, suppository, drops, patch, implant

#### **MedicationUnit**
**File:** `app/Models/MedicationUnit.php`

Defines measurement units:
- **Dosage units**: tablets, capsules, doses
- **Volume units**: ml, liters
- **Weight units**: grams, kg, mg
- **Form units**: boxes, bottles, vials, ampoules, tubes

Fields:
- `unit_type`: dosage, volume, weight, form, other
- `base_conversion_factor`: For unit conversions (e.g., 1 box = 100 tablets)
- `display_order`: UI sorting

#### **StoreCategory**
**File:** `app/Models/StoreCategory.php`

Classification system: Medications, Consumables, Surgical Supplies, Laboratory Supplies, etc.

#### **StoreSupplier**
**File:** `app/Models/StoreSupplier.php`

Supplier master data: name, contact, address, payment terms

#### **MedicationPricing**
**File:** `app/Models/MedicationPricing.php`

Pricing by patient category and effective dates:
- `medication_id`
- `patient_category_id`
- `selling_price`
- `markup_percentage`
- `discount_percentage`
- `effective_from`
- `effective_to`

#### **StoreUnit**
**File:** `app/Models/StoreUnit.php`

Store-level units (similar to MedicationUnit but for procurement): boxes, cartons, pallets

---

## Controllers & Their Functions

### Store Management Controllers (app/Http/Controllers/Store/)

#### 1. **GoodsReceivedNoteController.php**
**Purpose:** Manage procurement from suppliers

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /medications/goods-received-notes | List all GRNs with filters |
| `create()` | GET /medications/goods-received-notes/create | Show GRN creation form |
| `store()` | POST /medications/goods-received-notes | Create new GRN (status: draft) |
| `show()` | GET /medications/goods-received-notes/{grn} | View GRN details |
| `edit()` | GET /medications/goods-received-notes/{grn}/edit | Edit draft GRN |
| `update()` | PUT /medications/goods-received-notes/{grn} | Update GRN details |
| `approve()` | POST /medications/goods-received-notes/{grn}/approve | Approve GRN → verified |
| `reject()` | POST /medications/goods-received-notes/{grn}/reject | Reject GRN → cancelled |
| `processGRN()` | POST /medications/goods-received-notes/{grn}/process | Post GRN to ledger (status: posted) |
| `addItem()` | POST /medications/goods-received-notes/{grn}/add-item | Add line item to GRN |
| `updateItem()` | PUT /medications/goods-received-notes/{grn}/update-item/{item} | Update line item |
| `removeItem()` | DELETE /medications/goods-received-notes/{grn}/remove-item/{item} | Remove line item |
| `getMedications()` | GET /medications/stock/items/medications | AJAX: Get medication list |
| `getItemsByType()` | GET /medications/stock/items/{type} | AJAX: Get items by category |

**GRN Processing Logic (processGRN):**
1. Validate GRN status (must be "verified")
2. For each GRN item:
   - Create `MedicationLedger` entry with batch details
   - Update `Medication.stock_quantity` (+quantity)
   - If location assigned, create/update `StoreLocationStock`
   - Log `StoreStockMovement` (inward, purchase)
3. Update GRN status to "posted"
4. Record `posted_by` and `posted_at`

**Workflow:**
```
1. Create GRN (draft) → Add items → Save
2. Receive delivery → Update quantities → Mark as received
3. Verify invoice matches → Verify GRN
4. Post GRN → Creates ledger entries, updates stock
```

---

#### 2. **StoreRequisitionController.php**
**Purpose:** Manage inter-location transfer requests

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /store/requisitions | List requisitions (filtered by user role/location) |
| `create()` | GET /store/requisitions/create | Requisition creation form |
| `store()` | POST /store/requisitions | Create new requisition (status: pending) |
| `show()` | GET /store/requisitions/{requisition} | View requisition details |
| `edit()` | GET /store/requisitions/{requisition}/edit | Edit pending requisition |
| `update()` | PUT /store/requisitions/{requisition} | Update requisition |
| `submit()` | PATCH /store/requisitions/{requisition}/submit | Submit for approval |
| `verify()` | PATCH /store/requisitions/{requisition}/verify | Verify requisition |
| `approve()` | PATCH /store/requisitions/{requisition}/approve | Approve requisition → approved |
| `reject()` | PATCH /store/requisitions/{requisition}/reject | Reject requisition |
| `issue()` | PATCH /store/requisitions/{requisition}/issue | Issue items (transfer stock) |
| `cancel()` | PATCH /store/requisitions/{requisition}/cancel | Cancel requisition |
| `getAvailableMedications()` | GET /store/requisitions/api/medications | AJAX: Available medications at issuing location |
| `getMedicationStock()` | GET /store/requisitions/api/medications/{id}/stock | AJAX: Stock details for medication |

**Issue Processing Logic:**
1. For each requisition item:
   - Check stock availability at issuing_location
   - Reduce `StoreLocationStock.quantity` at issuing_location
   - Increase `StoreLocationStock.quantity` at requesting_location
   - Update `issued_quantity`
   - Log `StoreStockMovement` (transfer, requisition)
2. Update requisition status:
   - If all items fully issued → "issued"
   - If some items partially issued → "partially_issued"
3. Record `issued_by` and `issued_at`

**Access Control:**
- Users see requisitions for their assigned locations
- Managers can approve/issue
- Regular users can only request

---

#### 3. **MedicationLedgerController.php**
**Purpose:** Manage batch-level inventory with expiry tracking

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /medications/stock/ledger | List all batches with filters (medication, location, expiry status) |
| `show()` | GET /medications/stock/ledger/{ledger} | View batch details |
| `stockSummary()` | GET /medications/stock/ledger/stock-summary | Aggregate stock by medication |
| `expiryReport()` | GET /medications/stock/ledger/expiry-report | Expired and expiring batches |
| `getBatchDetails()` | GET /medications/stock/ledger/medication/{id}/batches | AJAX: Batches for medication |
| `updateStatus()` | POST /medications/stock/ledger/{ledger}/update-status | Change batch status |
| `markAsUnfit()` | POST /medications/stock/ledger/{ledger}/mark-unfit | Mark batch as damaged/unfit |
| `export()` | GET /medications/stock/ledger/export | Export to Excel |

**Key Features:**
- Expiry alerts (30-day threshold)
- Batch status management
- FIFO/FEFO dispensing support
- Cost tracking per batch

---

#### 4. **StoreLocationStockController.php**
**Purpose:** Manage stock at individual locations

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /store-locations-stock | View stock at locations |
| `show()` | GET /store-locations-stock/{stock} | Stock item details |
| `history()` | GET /store-locations-stock/{stock}/history | Movement history for stock item |
| `adjust()` | POST /store-locations-stock/{stock}/adjust | Manual quantity adjustment |

**Adjustment Logic:**
1. Record reason for adjustment
2. Update `StoreLocationStock.quantity`
3. Log `StoreStockMovement` (adjustment)
4. Track `balance_before` and `balance_after`

**Use Cases:**
- Physical stock counts
- Correction of data entry errors
- Reconciliation discrepancies

---

#### 5. **StoreStockMovementController.php**
**Purpose:** Audit trail and reporting on all stock movements

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /store-stock-movements | List movements with comprehensive filters |
| `show()` | GET /store-stock-movements/{movement} | View movement details |
| `reverse()` | POST /store-stock-movements/{movement}/reverse | Reverse a movement (create opposite entry) |
| `export()` | GET /store-stock-movements/export | Export to Excel |

**Filter Options:**
- By medication
- By location (from/to)
- By movement type (inward/outward/transfer/adjustment)
- By transaction type (purchase/dispensing/requisition/etc.)
- By date range
- By user who created

**Statistics Provided:**
- Total movements
- Movements today
- Total value
- Pending transfers

---

#### 6. **StoreCategoryController.php**
**Purpose:** Manage medication categories

**Key Methods:**
- Standard CRUD operations (index, create, store, show, edit, update, destroy)
- Used for organizing medications and generating category-based reports

---

#### 7. **StoreLocationController.php**
**Purpose:** Manage storage locations

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /store-locations | List locations |
| `create/store()` | GET/POST /store-locations | Create location |
| `edit/update()` | GET/PUT /store-locations/{location} | Update location |
| `toggleStatus()` | POST /store-locations/{location}/toggle-status | Enable/disable |
| `apiList()` | GET /store-locations/api/list | AJAX: Flat list |
| `apiTree()` | GET /store-locations/api/tree | AJAX: Hierarchical tree |

**Hierarchy Features:**
- Self-referential parent_id
- Unlimited nesting levels
- Tree view for UI

---

#### 8. **StoreSupplierController.php**
**Purpose:** Manage supplier master data

**Key Methods:**
- Standard CRUD operations
- Track supplier contact information
- Link to GRNs for procurement history

---

#### 9. **StoreReportController.php**
**Purpose:** Generate inventory reports

**Key Reports:**
- Stock levels report
- Movement history report
- Expiry report
- Low stock report
- Supplier purchase history
- Location-wise stock breakdown

---

### Medication Management Controllers (app/Http/Controllers/)

#### 10. **MedicationController.php**
**Purpose:** Manage medication master data

**Key Methods:**

| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /medications | List medications with DataTables support |
| `create()` | GET /medications/create | Medication creation form |
| `store()` | POST /medications | Create new medication |
| `show()` | GET /medications/{medication} | View medication details |
| `edit()` | GET /medications/{medication}/edit | Edit medication |
| `update()` | PUT /medications/{medication} | Update medication |
| `destroy()` | DELETE /medications/{medication} | Soft delete medication |
| `toggleStatus()` | POST /medications/{medication}/toggle-status | Activate/deactivate |
| `apiList()` | GET /medications/api/list | AJAX: Medication list |
| `lowStock()` | GET /medications/reports/low-stock | Low stock report |
| `expired()` | GET /medications/reports/expired | Expired medications |
| `expiringSoon()` | GET /medications/reports/expiring-soon | Expiring soon |

**DataTables Features:**
- Server-side processing
- Column sorting
- Search/filtering
- Pagination
- Custom filters (category, status, stock_status)

**Validation Rules:**
- Unique combination of generic_name + strength + formulation
- Required: generic_name, dispensing_unit_id, formulation_id
- Optional but recommended: brand_name, category_id, stock levels

---

#### 11. **MedicationUnitController.php**
**Purpose:** Manage dosage and measurement units

**Key Methods:**
- Standard CRUD operations
- `toggleStatus()` - Enable/disable unit
- `getBaseUnits()` - API for base units (for conversions)
- `getDispensingUnits()` - API for dispensing units

**Unit Types:**
- dosage (tablets, capsules, doses)
- volume (ml, liters)
- weight (grams, kg, mg)
- form (boxes, bottles, vials)

---

#### 12. **MedicationFormulationController.php**
**Purpose:** Manage medication forms

**Key Methods:**
- Standard CRUD operations
- `getActiveFormulations()` - API for form selection

**Common Formulations:**
- Solid: tablet, capsule, powder
- Liquid: syrup, suspension, solution, drops
- Injectable: injection, infusion
- Topical: cream, ointment, gel, patch
- Inhalation: inhaler, nebulizer
- Other: suppository, implant

---

#### 13. **MedicationPricingController.php**
**Purpose:** Manage selling prices

**Key Methods:**
- Standard CRUD operations
- `bulkUpdate()` - Update multiple prices at once

**Pricing Features:**
- Multiple prices per medication (by patient category)
- Effective date ranges
- Markup/discount percentages
- Automatic price calculation

---

#### 14. **MedicationSearchController.php**
**Purpose:** Advanced medication search

**Key Methods:**
- `search()` - AJAX search with autocomplete
- Search by generic_name, brand_name, batch_number

---

#### 15. **MedicationCashSaleController.php**
**Purpose:** Over-the-counter medication sales

**Key Methods:**
- Create sale
- Add items
- Process payment
- Dispense items
- Cancel sale
- Get pricing

**Integration:**
- Links to financial transactions
- Updates stock via `StoreStockMovement`

---

### Additional Controllers

#### 16. **StockManagementController.php**
**Purpose:** Stock operations and dashboard

**Key Methods:**
- `dashboard()` - Overview with KPIs
- `transfersIndex/createTransfer/storeTransfer/processTransfer()` - Direct transfers
- `adjustmentsIndex/createAdjustment/storeAdjustment()` - Stock adjustments
- `disposalIndex/disposeUnfitMedications()` - Disposal management
- `stockLevels()` - Current stock across locations
- `stockAlerts()` - Low stock and expiry alerts

---

#### 17. **ConsumptionTrackingController.php**
**Purpose:** Track medication usage

**Key Methods:**
- `prescriptionsIndex()` - Pending prescriptions
- `dispensePrescription()` - Dispense to patient
- `recordInvestigationConsumption()` - Lab consumables
- `recordProcedureConsumption()` - Procedure consumables
- `consumptionAnalytics()` - Usage statistics

---

#### 18. **ReconciliationController.php**
**Purpose:** Stock reconciliation and integrity checks

**Key Methods:**
- `runIntegrityCheck()` - Verify data consistency
- `autoCorrectDiscrepancies()` - Auto-fix common issues
- `showDiscrepancyReport()` - List discrepancies
- `processStockCorrection()` - Manual corrections
- `showAuditTrail()` - Full audit history

---

#### 19. **ReportingController.php**
**Purpose:** Comprehensive reporting

**Key Reports:**
- Stock level reports
- Movement reports
- Expiry reports
- Consumption reports
- ABC analysis
- Custom reports (user-defined filters)

---

## Key Workflows

### Workflow 1: Medication Setup (Initial Configuration)

**Steps:**
1. **Create Categories** (`StoreCategoryController`)
   - Navigate to `/store-categories/create`
   - Add categories: Medications, Consumables, Surgical Supplies, etc.

2. **Create Formulations** (`MedicationFormulationController`)
   - Navigate to `/medications/formulations/create`
   - Add forms: Tablet, Capsule, Liquid, Injection, etc.

3. **Create Units** (`MedicationUnitController`)
   - Navigate to `/medication-units/create`
   - Add units: tablets, ml, boxes, etc.

4. **Create Locations** (`StoreLocationController`)
   - Navigate to `/store-locations/create`
   - Add main store, sub-stores, departments, wards

5. **Create Medication** (`MedicationController`)
   - Navigate to `/medications/create`
   - Fill in details:
     - Generic name: "Paracetamol"
     - Strength: "500mg"
     - Formulation: Tablet
     - Dispensing unit: Tablets
     - Category: Medications
     - Stock levels: min=100, reorder=200, max=1000

6. **Set Pricing** (`MedicationPricingController`)
   - Navigate to `/medication-pricing/create`
   - Set prices by patient category

**Result:** Medication is ready to receive stock but has 0 quantity.

---

### Workflow 2: Procurement (Goods Received Note)

**Steps:**
1. **Create GRN** (`GoodsReceivedNoteController`)
   - Navigate to `/medications/goods-received-notes/create`
   - Select supplier
   - Enter invoice details (invoice_number, invoice_date, delivery_date)
   - Status: "draft"

2. **Add Items to GRN**
   - Click "Add Item" on GRN
   - For each item:
     - Select medication: "Paracetamol 500mg Tablet"
     - Enter batch_number: "BATCH2024001"
     - Enter manufacture_date: "2024-01-15"
     - Enter expiry_date: "2026-01-15" (2 years shelf life)
     - Enter quantity_ordered: 1000
     - Enter quantity_received: 1000 (if matches delivery)
     - Enter unit_cost: 2.50 (per tablet)
     - Select store_unit: Boxes
     - Enter conversion_factor: 100 (1 box = 100 tablets)
     - Select location: "Main Pharmacy"

3. **Receive Delivery**
   - When items arrive, verify quantities
   - Update GRN status to "received"
   - Record received_by and received_at

4. **Verify GRN**
   - Manager reviews GRN
   - Verifies invoice matches delivery
   - Clicks "Verify" button
   - Status changes to "verified"

5. **Post GRN** (Critical Step)
   - Click "Process GRN" button
   - System performs:
     - **Creates MedicationLedger entry:**
       ```php
       medication_id: Paracetamol ID
       batch_number: "BATCH2024001"
       manufacture_date: "2024-01-15"
       expiry_date: "2026-01-15"
       unit_cost: 2.50
       quantity_received: 1000
       quantity_current: 1000
       status: "active"
       location_id: Main Pharmacy ID
       ```
     - **Updates Medication record:**
       ```php
       stock_quantity: 0 + 1000 = 1000
       ```
     - **Creates StoreLocationStock entry:**
       ```php
       location_id: Main Pharmacy ID
       medication_id: Paracetamol ID
       batch_number: "BATCH2024001"
       quantity: 1000
       unit_cost: 2.50
       expiry_date: "2026-01-15"
       status: "active"
       ```
     - **Logs StoreStockMovement:**
       ```php
       medication_id: Paracetamol ID
       movement_type: "inward"
       transaction_type: "purchase"
       quantity: 1000
       to_location_id: Main Pharmacy ID
       balance_before: 0
       balance_after: 1000
       unit_cost: 2.50
       reference_type: "GoodsReceivedNote"
       reference_id: GRN ID
       batch_number: "BATCH2024001"
       ```
   - GRN status changes to "posted"

6. **Verify Stock Updated**
   - Navigate to `/medications/stock/ledger`
   - See new batch for Paracetamol
   - Navigate to `/store-locations-stock`
   - Filter by "Main Pharmacy"
   - See Paracetamol with quantity 1000

**Result:** Medication is now in stock and available for dispensing or transfer.

---

### Workflow 3: Inter-Location Transfer (Requisition)

**Scenario:** Ward 3 needs Paracetamol from Main Pharmacy

**Steps:**

1. **Create Requisition** (Ward 3 user)
   - Navigate to `/store/requisitions/create`
   - Select:
     - requesting_location: "Ward 3"
     - issuing_location: "Main Pharmacy"
     - priority: "normal"
     - required_date: "2024-06-25"
   - Click "Create"
   - Status: "pending"

2. **Add Items**
   - Select medication: "Paracetamol 500mg Tablet"
   - Enter requested_quantity: 100
   - Click "Add Item"

3. **Submit for Approval**
   - Click "Submit" button
   - Notification sent to Main Pharmacy manager

4. **Approve Requisition** (Main Pharmacy manager)
   - Navigate to `/store/requisitions` (filtered by issuing_location)
   - Click on requisition
   - Review items
   - For each item, set approved_quantity:
     - If sufficient stock: approved_quantity = requested_quantity (100)
     - If insufficient: approved_quantity < requested_quantity (e.g., 50)
   - Click "Approve"
   - Status: "approved"

5. **Issue Items** (Main Pharmacy staff)
   - Open requisition
   - Click "Issue" button
   - System performs:
     - **Reduce StoreLocationStock at Main Pharmacy:**
       ```php
       Main Pharmacy Paracetamol: 1000 - 100 = 900
       ```
     - **Create/Update StoreLocationStock at Ward 3:**
       ```php
       Ward 3 Paracetamol: 0 + 100 = 100
       ```
     - **Log StoreStockMovement (2 entries):**
       ```php
       // Outward from Main Pharmacy
       movement_type: "transfer"
       transaction_type: "requisition"
       quantity: 100
       from_location_id: Main Pharmacy ID
       balance_before: 1000
       balance_after: 900
       reference_type: "StoreRequisition"
       
       // Inward to Ward 3
       movement_type: "transfer"
       transaction_type: "requisition"
       quantity: 100
       to_location_id: Ward 3 ID
       balance_before: 0
       balance_after: 100
       reference_type: "StoreRequisition"
       ```
     - **Update requisition item:**
       ```php
       issued_quantity: 100
       ```
   - Status: "issued"
   - Record issued_by and issued_at

6. **Verify Transfer**
   - Navigate to `/store-locations-stock`
   - Filter by "Main Pharmacy": Paracetamol quantity = 900
   - Filter by "Ward 3": Paracetamol quantity = 100
   - Navigate to `/store-stock-movements`
   - See transfer entries

**Result:** Stock successfully transferred between locations with full audit trail.

---

### Workflow 4: Dispensing to Patient

**Scenario:** Dispense Paracetamol from Ward 3 to a patient

**Steps:**

1. **Prescription Created** (Doctor)
   - Doctor creates consultation
   - Adds prescription items including Paracetamol
   - Prescription status: "pending"

2. **Dispense Prescription** (Nurse/Pharmacist at Ward 3)
   - Navigate to `/medications/consumption/prescriptions`
   - Select pending prescription
   - For each medication:
     - Verify availability at Ward 3
     - Select batch (FIFO/FEFO)
     - Enter quantity dispensed
   - Click "Dispense"

3. **System Processing:**
   - **Reduce StoreLocationStock at Ward 3:**
     ```php
     Ward 3 Paracetamol: 100 - 10 = 90
     ```
   - **Reduce MedicationLedger quantity_current:**
     ```php
     BATCH2024001: 1000 - 10 = 990
     ```
   - **Update Medication.stock_quantity:**
     ```php
     Paracetamol total: 1000 - 10 = 990
     ```
   - **Log StoreStockMovement:**
     ```php
     movement_type: "outward"
     transaction_type: "dispensing"
     quantity: 10
     from_location_id: Ward 3 ID
     balance_before: 100
     balance_after: 90
     reference_type: "PrescriptionItem"
     reference_id: Prescription Item ID
     ```
   - **Update prescription status:** "dispensed"

4. **Patient Receives Medication**
   - Print medication label
   - Hand to patient with instructions

**Result:** Stock reduced, patient receives medication, full audit trail recorded.

---

### Workflow 5: Stock Adjustment (Physical Count Reconciliation)

**Scenario:** Physical count at Main Pharmacy shows 890 tablets but system shows 900

**Steps:**

1. **Perform Physical Count**
   - Staff counts actual stock: 890 tablets

2. **Create Adjustment** (`StoreLocationStockController`)
   - Navigate to `/store-locations-stock`
   - Find Paracetamol at Main Pharmacy
   - Click "Adjust"
   - Enter:
     - Current system quantity: 900
     - Actual physical quantity: 890
     - Difference: -10 (shortage)
     - Reason: "Physical count reconciliation - minor spillage"
   - Click "Submit Adjustment"

3. **System Processing:**
   - **Update StoreLocationStock:**
     ```php
     Main Pharmacy Paracetamol: 900 → 890
     ```
   - **Update Medication.stock_quantity:**
     ```php
     Paracetamol total: 990 - 10 = 980
     ```
   - **Log StoreStockMovement:**
     ```php
     movement_type: "adjustment"
     transaction_type: "adjustment"
     quantity: -10 (negative for reduction)
     from_location_id: Main Pharmacy ID
     balance_before: 900
     balance_after: 890
     notes: "Physical count reconciliation - minor spillage"
     ```

4. **Review Adjustment**
   - Manager reviews adjustments in `/store-stock-movements`
   - Filter by transaction_type: "adjustment"
   - Investigate if discrepancy is significant

**Result:** System stock matches physical count, discrepancy recorded for audit.

---

### Workflow 6: Expiry Management

**Scenario:** Batch BATCH2024001 is expiring in 20 days

**Steps:**

1. **System Detects Expiring Batch** (Automated)
   - Cron job runs `MedicationLedger::updateExpiredBatches()`
   - Checks all batches for expiry_date
   - Flags batches expiring within 30 days

2. **View Expiry Report** (Store Manager)
   - Navigate to `/medications/stock/ledger/expiry-report`
   - See:
     - Expired batches (past expiry_date)
     - Expiring critical (< 7 days)
     - Expiring soon (7-30 days)
   - Paracetamol BATCH2024001 appears in "Expiring Soon"

3. **Decision Making:**
   - If quantity is small: Dispose
   - If quantity is large and expiry > 7 days: Fast-track dispensing
   - If already expired: Mark as unfit and dispose

4. **Mark Batch as Unfit** (`MedicationLedgerController`)
   - Navigate to `/medications/stock/ledger`
   - Find BATCH2024001
   - Click "Mark as Unfit"
   - Enter reason: "Expired on 2026-01-15"
   - Status changes to "damaged"

5. **Dispose Unfit Medications** (`StockManagementController`)
   - Navigate to `/medications/stock/disposal`
   - System lists all unfit batches
   - Create disposal record:
     - Select batches to dispose
     - Enter disposal method (incineration, return to supplier, etc.)
     - Record witness information
   - Click "Complete Disposal"

6. **System Processing:**
   - **Update MedicationLedger:**
     ```php
     BATCH2024001 status: "damaged" → "disposed"
     quantity_current: 0
     ```
   - **Update StoreLocationStock:**
     ```php
     Remove or set quantity to 0
     ```
   - **Log StoreStockMovement:**
     ```php
     movement_type: "waste"
     transaction_type: "disposal"
     quantity: (remaining quantity)
     from_location_id: Main Pharmacy ID
     balance_after: 0
     notes: "Expired batch disposal"
     ```

**Result:** Expired medication properly disposed with audit trail, stock updated.

---

### Workflow 7: Reporting & Analytics

**Common Reports:**

1. **Stock Level Report** (`/medications/reports/stock-levels`)
   - Current stock by medication
   - By location
   - Shows: quantity, unit_cost, total_value, expiry_date

2. **Movement Report** (`/medications/reports/movements`)
   - All stock movements
   - Filters: date range, location, medication, movement type
   - Shows: inward, outward, transfers, adjustments

3. **Low Stock Report** (`/medications/reports/low-stock`)
   - Medications below reorder_level
   - Sorted by urgency (stock_quantity vs minimum_stock_level)
   - Action: Create purchase order

4. **Expiry Report** (`/medications/reports/expiry`)
   - Expired batches
   - Expiring within 30/60/90 days
   - Action: Dispose or fast-track

5. **ABC Analysis** (`/medications/reports/abc-analysis`)
   - Classify medications by value/consumption
   - A: High value (70-80% of spend)
   - B: Medium value (15-20% of spend)
   - C: Low value (5-10% of spend)
   - Action: Focus inventory control on A items

6. **Consumption Report** (`/medications/reports/consumption`)
   - Usage by medication
   - By date range
   - By department
   - Average monthly consumption
   - Forecasting data

7. **Custom Reports** (`/medications/reports/custom`)
   - User-defined filters
   - Export to Excel/PDF

---

## Views Organization

### Medication Views (`resources/views/medications/`)

```
medications/
├── index.blade.php                    # List medications (DataTables)
├── create.blade.php                   # Create medication form
├── edit.blade.php                     # Edit medication form
├── show.blade.php                     # View medication details
├── dashboard.blade.php                # Medication dashboard (KPIs, alerts)
├── _actions.blade.php                 # Action buttons partial
│
├── formulations/                      # Formulation management
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
│
├── stock/                             # Stock management views
│   ├── levels.blade.php               # Stock levels overview
│   ├── alerts.blade.php               # Low stock & expiry alerts
│   ├── grn/                           # Goods Received Notes
│   │   ├── index.blade.php            # List GRNs
│   │   ├── grn-create.blade.php       # Create GRN
│   │   ├── show.blade.php             # View GRN
│   │   ├── edit.blade.php             # Edit GRN
│   │   └── grn_items/                 # GRN items
│   │       ├── index.blade.php
│   │       ├── create.blade.php
│   │       ├── add-modal.blade.php
│   │       ├── edit-modal.blade.php
│   │       └── items-section.blade.php
│   ├── ledger/                        # Batch tracking
│   │   ├── index.blade.php            # List batches
│   │   ├── show.blade.php             # Batch details
│   │   ├── stock-summary.blade.php    # Aggregate summary
│   │   └── expiry-report.blade.php    # Expiry alerts
│   ├── suppliers/                     # Supplier management
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── transfers/                     # Direct transfers
│   │   ├── index.blade.php
│   │   └── create.blade.php
│   ├── adjustments/                   # Stock adjustments
│   │   ├── index.blade.php
│   │   └── create.blade.php
│   └── disposal/                      # Disposal management
│       └── index.blade.php
│
├── consumption/                       # Usage tracking
│   ├── index.blade.php                # Consumption dashboard
│   ├── analytics.blade.php            # Consumption analytics
│   └── prescriptions/                 # Prescription dispensing
│       └── index.blade.php
│
├── reconciliation/                    # Data integrity
│   ├── index.blade.php                # Reconciliation dashboard
│   ├── medication-validation.blade.php
│   ├── discrepancy-report.blade.php
│   ├── stock-correction.blade.php
│   └── audit-trail.blade.php
│
└── reports/                           # Reporting
    └── index.blade.php                # Reports menu
```

### Store Views (`resources/views/store/`)

```
store/
├── dashboard.blade.php                # Store dashboard
├── categories/                        # Category management
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── consumables/                       # Consumables (deprecated?)
└── requisitions/                      # Requisition management
    ├── index.blade.php                # List requisitions
    ├── create.blade.php               # Create requisition
    ├── show.blade.php                 # View requisition
    └── edit.blade.php                 # Edit requisition
```

### Other Related Views

```
medication-ledger/                     # Alternative ledger views
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php

store-locations-stock/                 # Location stock views
└── index.blade.php

store-stock-movements/                 # Movement views
└── (views likely in main controller)

medication-pricing/                    # Pricing views
└── (views likely in main controller)

medication-units/                      # Unit views
└── (views likely in main controller)

medication-frequencies/                # Frequency views
└── (views likely in main controller)

medication_cash_sales/                 # Cash sales views
└── (views likely in main controller)

layouts/
└── medication-nav.blade.php           # Medication navigation menu
```

---

## Routes Structure

### Route Files

1. **routes/web.php** - Main routes file
2. **routes/medication.php** - Medication-specific routes (included in web.php at line 662)
3. **routes/requisitions.php** - Requisition routes (included in web.php at line 712)

### Key Route Groups

#### 1. Medication Management Routes (`routes/medication.php`)

**Prefix:** `/medications`  
**Name:** `medications.*`

```php
/medications                                    # Dashboard
/medications/dashboard                          # Dashboard (alternative)

// Goods Received Notes (Procurement)
/medications/goods-received-notes               # List GRNs
/medications/goods-received-notes/create        # Create GRN
/medications/goods-received-notes/{grn}         # View GRN
/medications/goods-received-notes/{grn}/approve # Approve GRN
/medications/goods-received-notes/{grn}/process # Post to ledger

// Medication Ledger (Batch Tracking)
/medications/stock/ledger                       # List batches
/medications/stock/ledger/stock-summary         # Summary
/medications/stock/ledger/expiry-report         # Expiry report
/medications/stock/ledger/{ledger}              # Batch details

// Suppliers
/medications/suppliers                          # List suppliers
/medications/suppliers/create                   # Create supplier

// Stock Management
/medications/stock/transfers                    # Transfers
/medications/stock/adjustments                  # Adjustments
/medications/stock/disposal                     # Disposal
/medications/stock/levels                       # Stock levels
/medications/stock/alerts                       # Alerts

// Consumption Tracking
/medications/consumption                        # Consumption dashboard
/medications/consumption/prescriptions          # Prescriptions
/medications/consumption/analytics              # Analytics

// Reconciliation
/medications/reconciliation                     # Reconciliation dashboard
/medications/reconciliation/integrity-check     # Run check
/medications/reconciliation/discrepancies       # Discrepancy report

// Reporting
/medications/reports                            # Reports menu
/medications/reports/stock-levels               # Stock report
/medications/reports/movements                  # Movement report
/medications/reports/expiry                     # Expiry report

// Formulations
/medications/formulations                       # List formulations
/medications/formulations/create                # Create formulation
```

#### 2. Store Requisition Routes (`routes/requisitions.php`)

**Prefix:** `/store/requisitions`  
**Name:** `store.requisitions.*`

```php
/store/requisitions                             # List requisitions
/store/requisitions/create                      # Create requisition
/store/requisitions/{requisition}               # View requisition
/store/requisitions/{requisition}/edit          # Edit requisition
/store/requisitions/{requisition}/approve       # Approve
/store/requisitions/{requisition}/reject        # Reject
/store/requisitions/{requisition}/issue         # Issue items
/store/requisitions/{requisition}/cancel        # Cancel
```

#### 3. Core Medication Routes (`routes/web.php`)

```php
// Medication CRUD
/medications                                    # List
/medications/create                             # Create
/medications/{medication}                       # View
/medications/{medication}/edit                  # Edit
/medications/{medication}/toggle-status         # Activate/deactivate

// Medication Units
/medication-units                               # List
/medication-units/create                        # Create

// Medication Pricing
/medication-pricing                             # List
/medication-pricing/create                      # Create
/medication-pricing/bulk-update                 # Bulk update

// Store Categories
/store-categories                               # List
/store-categories/create                        # Create

// Store Locations
/store-locations                                # List
/store-locations/create                         # Create
/store-locations/api/list                       # AJAX list
/store-locations/api/tree                       # AJAX tree

// Store Location Stock
/store-locations-stock                          # List
/store-locations-stock/{stock}/adjust           # Adjust
/store-locations-stock/{stock}/history          # History

// Store Stock Movements
/store-stock-movements                          # List
/store-stock-movements/{movement}               # View
/store-stock-movements/export                   # Export
```

#### 4. API Routes (AJAX endpoints)

```php
// Medication search
/api/medications/search                         # Autocomplete

// Medication list
/medications/api/list                           # DataTables

// Location APIs
/store-locations/api/list                       # Flat list
/store-locations/api/tree                       # Hierarchical

// Unit APIs
/medication-units/api/base-units                # Base units
/medication-units/api/dispensing-units          # Dispensing units

// Stock APIs
/medications/stock/items/medications            # Available medications
/medications/stock/units/store                  # Store units
/medications/stock/units/dispensing             # Dispensing units

// Requisition APIs
/store/requisitions/api/medications             # Available at location
/store/requisitions/api/medications/{id}/stock  # Stock details

// GRN APIs
/medications/goods-received-notes/api/pending   # Pending GRNs
/medications/goods-received-notes/api/approved  # Approved GRNs
```

---

## Data Flow Diagrams

### 1. Procurement Flow (GRN → Stock)

```
┌─────────────┐
│  Supplier   │
│   Delivers  │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Create GoodsReceivedNote (draft)   │
│  - Invoice details                  │
│  - Supplier                         │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Add GoodsReceivedNoteItems         │
│  - Medication                       │
│  - Batch number                     │
│  - Expiry date                      │
│  - Quantity                         │
│  - Unit cost                        │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Verify GRN (Manager)                │
│  - Check quantities                 │
│  - Verify invoice                   │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  Post GRN (Process)                  │
└──────┬──────────────────────────────┘
       │
       ├──────────────┐
       │              │
       ▼              ▼
┌──────────────┐  ┌──────────────────┐
│ Create       │  │ Update           │
│ Medication   │  │ Medication       │
│ Ledger       │  │ .stock_quantity  │
│ (Batch)      │  │ (+quantity)      │
└──────────────┘  └──────────────────┘
       │              │
       │              │
       ▼              ▼
┌────────────────────────────────────┐
│ Create/Update StoreLocationStock   │
│ at receiving location               │
└──────┬─────────────────────────────┘
       │
       ▼
┌────────────────────────────────────┐
│ Log StoreStockMovement              │
│ (inward, purchase)                  │
└────────────────────────────────────┘
```

### 2. Requisition Flow (Location → Location)

```
┌───────────────────┐
│  Requesting       │
│  Location         │
│  (Ward 3)         │
└────────┬──────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Create StoreRequisition (pending)  │
│  - From: Main Pharmacy              │
│  - To: Ward 3                       │
│  - Items: [Paracetamol x100]        │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Approve (Manager)                   │
│  - Verify stock availability        │
│  - Set approved_quantity            │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Issue (Pharmacy Staff)              │
└────────┬────────────────────────────┘
         │
         ├───────────────┐
         │               │
         ▼               ▼
┌──────────────────┐  ┌──────────────────┐
│ Reduce Stock     │  │ Increase Stock   │
│ at Issuing       │  │ at Requesting    │
│ Location         │  │ Location         │
│ (Main Pharmacy)  │  │ (Ward 3)         │
└────────┬─────────┘  └────────┬─────────┘
         │                     │
         │                     │
         ▼                     ▼
┌────────────────────────────────────┐
│ Update StoreLocationStock          │
│ - Main Pharmacy: -100              │
│ - Ward 3: +100                     │
└────────┬───────────────────────────┘
         │
         ▼
┌────────────────────────────────────┐
│ Log StoreStockMovement (2 entries) │
│ 1. Outward from Main Pharmacy      │
│ 2. Inward to Ward 3                │
└────────────────────────────────────┘
```

### 3. Dispensing Flow (Stock → Patient)

```
┌───────────────────┐
│  Doctor creates   │
│  Prescription     │
└────────┬──────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Pharmacist/Nurse views pending     │
│  prescriptions                      │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Select prescription                 │
│  - Check stock availability         │
│  - Select batch (FIFO/FEFO)         │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Dispense items                      │
└────────┬────────────────────────────┘
         │
         ├───────────────┬──────────────┐
         │               │              │
         ▼               ▼              ▼
┌─────────────┐  ┌──────────────┐  ┌──────────────┐
│ Reduce      │  │ Reduce       │  │ Reduce       │
│ Store       │  │ Medication   │  │ Medication   │
│ Location    │  │ Ledger       │  │ .stock_      │
│ Stock       │  │ .quantity_   │  │ quantity     │
│             │  │ current      │  │              │
└─────────────┘  └──────────────┘  └──────────────┘
         │               │              │
         └───────────────┴──────────────┘
                         │
                         ▼
         ┌───────────────────────────────┐
         │ Log StoreStockMovement        │
         │ (outward, dispensing)         │
         └───────────┬───────────────────┘
                     │
                     ▼
         ┌───────────────────────────────┐
         │ Patient receives medication   │
         └───────────────────────────────┘
```

### 4. Stock Reconciliation Flow

```
┌───────────────────┐
│  Physical Count   │
│  (Actual: 890)    │
└────────┬──────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Compare with System                 │
│  (System: 900)                       │
│  (Difference: -10)                   │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  Create Adjustment                   │
│  - Reason: Physical count            │
│  - Quantity: -10                     │
└────────┬────────────────────────────┘
         │
         ├───────────────┬
         │               │
         ▼               ▼
┌─────────────────┐  ┌──────────────────┐
│ Update          │  │ Update           │
│ StoreLocation   │  │ Medication       │
│ Stock           │  │ .stock_quantity  │
│ (900 → 890)     │  │ (-10)            │
└─────────────────┘  └──────────────────┘
         │               │
         └───────────────┘
                 │
                 ▼
┌────────────────────────────────────┐
│ Log StoreStockMovement              │
│ (adjustment)                        │
│ - balance_before: 900              │
│ - balance_after: 890               │
└────────────────────────────────────┘
```

---

## System Architecture Summary

### Layer Responsibilities

**1. Models (Data & Business Logic)**
- Define database schema
- Relationships between entities
- Business rules (e.g., reduce quantity, check expiry)
- Computed attributes (e.g., is_expired, stock_status)
- Scopes for common queries

**2. Controllers (Request Handling)**
- Process HTTP requests
- Validate input
- Call model methods
- Return views or JSON
- Authorization checks

**3. Services (Complex Business Logic)**
- Multi-step processes
- Transaction management
- External API calls
- Used for requisition processing, reconciliation

**4. Observers (Event Handling)**
- Automatic actions on model events
- Example: MedicationDispensingObserver

**5. Views (Presentation)**
- Blade templates
- DataTables for listing
- Forms for input
- Modals for quick actions
- Charts for dashboards

**6. Routes (URL Structure)**
- RESTful resource routes
- Custom action routes
- API routes for AJAX
- Route grouping by feature

---

## Key Design Patterns

### 1. **Batch Tracking Pattern**
- Every medication purchase creates a MedicationLedger entry
- Batch number is unique identifier
- Expiry date tracked at batch level
- FIFO/FEFO dispensing supported

### 2. **Location-Based Inventory Pattern**
- StoreLocationStock tracks qty at each location
- Hierarchical location structure (parent_id)
- Enables multi-tier distribution

### 3. **Audit Trail Pattern**
- StoreStockMovement logs every transaction
- Immutable records (no deletion)
- balance_before and balance_after for verification
- Reference to source document (polymorphic)

### 4. **Workflow Pattern**
- GRN: draft → received → verified → posted
- Requisition: pending → approved → issued
- Status transitions controlled by specific methods
- Approval gates for accountability

### 5. **Stock Status Computation Pattern**
- stock_status computed from multiple factors:
  - quantity vs thresholds
  - expiry dates
  - batch availability
- Real-time calculation (not stored)
- Caching for performance

### 6. **Separation of Concerns**
- Medications (master data) separate from Store (operations)
- Models focused on data
- Controllers handle HTTP
- Services handle complex business logic
- Views purely presentational

---

## Conclusion

This medications/items management system is a **comprehensive, enterprise-grade inventory management solution** with:

✅ **Complete traceability** via batch tracking and movement logs  
✅ **Multi-location support** with hierarchical storage  
✅ **Approval workflows** for procurement and transfers  
✅ **Expiry management** with automated alerts  
✅ **Cost tracking** at batch level  
✅ **Flexible pricing** by patient category  
✅ **Reconciliation** with integrity checks  
✅ **Comprehensive reporting** with exports  

The system is well-structured with clear separation between:
- **Store controllers** (operational processes)
- **Medication controllers** (master data management)
- **Models** (data and business rules)
- **Views** (user interface)

All workflows maintain complete audit trails and support role-based access control for accountability and compliance.
