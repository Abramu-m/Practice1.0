# Investigation Workflow Report

## Scope
This report documents the process from investigation listing in the Investigation Management screen to result entry and transition to Results Available status.

Primary UI entry point:
- resources/views/investigations/index.blade.php

Primary backend handlers:
- app/Http/Controllers/InvestigationController.php
- app/Http/Controllers/LabController.php
- app/Models/Investigation.php
- routes/web.php

---

## 1) How an investigation appears in the list

1. User opens Investigation Management page.
2. The table is initialized as a server-side DataTable and calls investigations.index via AJAX.
3. Backend InvestigationController@index returns filtered laboratory investigations only.
4. Returned rows include:
   - Patient, doctor, service, priority, status, ordered time, price
   - Rendered actions menu from investigations/_actions.blade.php
5. Each row is marked with data-investigation-id during DataTable row creation for later stock checks.

Relevant logic:
- resources/views/investigations/index.blade.php (DataTable config and AJAX filter payload)
- app/Http/Controllers/InvestigationController.php (index method, DataTables response)
- routes/web.php (investigations resource route)

---

## 2) Actions available from the list

Action menu is generated per row and depends on status.

From ordered:
- Mark as Collected (stock-aware path) when service requires sample
- Check Stock
- Cancel Investigation

From collected:
- Mark as Processing
- Add Results
- Cancel Investigation

From processing:
- Add Results
- Cancel Investigation

From resulted:
- View Results

Relevant logic:
- resources/views/investigations/_actions.blade.php

---

## 3) Status update routing split (important)

The frontend intentionally chooses one of two PATCH endpoints:

A) Stock-sensitive collection:
- Endpoint: PATCH /lab/investigations/{id}/status
- Used when updateType is stock and newStatus is collected
- Controller: LabController@updateStatus

B) Simple status updates (paid, processing, resulted, cancelled, etc.):
- Endpoint: PATCH /investigations/{id}/status
- Controller: InvestigationController@updateStatus

Frontend decision logic:
- resources/views/investigations/index.blade.php (updateStatus function)

Route definitions:
- routes/web.php

---

## 4) What happens when status is changed to Collected

Flow for list page collection action:

1. User chooses Mark as Collected from actions menu.
2. JS sets form action to lab status endpoint.
3. On submit, frontend sends JSON PATCH with status and optional notes.
4. LabController@updateStatus performs stock gate:
   - checkStockAvailability(investigation)
   - If required consumables are insufficient, returns HTTP 422 with stock_details
5. If stock is sufficient:
   - deductConsumablesFromStock(investigation)
   - Creates consumption records in investigation_consumptions
   - Updates location stock quantities using FIFO by expiry/created date
   - Saves batches_used JSON on investigation
   - Sets status=collected, collected_at, collected_by
6. Frontend receives success and reloads page.
7. If 422, frontend closes status modal and opens stock error modal with item-by-item deficits.

Relevant logic:
- resources/views/investigations/index.blade.php (status form submit, stock error handling)
- app/Http/Controllers/LabController.php (updateStatus, checkStockAvailability, deductConsumablesFromStock)

---

## 5) Proactive stock checks while listing

On page load, frontend attempts to check stock for each listed investigation:
- GET /lab/investigations/{id}/check-stock
- If cannot proceed:
  - row is highlighted
  - Low Stock badge appended to status cell
  - collect and add-results actions are disabled in the row

Detailed stock modal can be opened to show requirements and available quantities.

Relevant logic:
- resources/views/investigations/index.blade.php (DOMContentLoaded stock sweep and stock details modal)
- app/Http/Controllers/LabController.php (checkInvestigationStock)
- routes/web.php (lab.investigations.check-stock)

---

## 6) Entering results (the step that makes status Results Available)

1. From collected or processing states, user clicks Add Results.
2. User is routed to:
   - GET /lab/investigations/{id}/results
3. LabController@showResultForm validates investigation belongs to Laboratory category.
4. Result template is selected from medical service template mapping with fallback.
5. User submits result form to:
   - POST /lab/investigations/{id}/results
6. LabController@storeResults:
   - Validates action in draft/preliminary/final
   - Extracts all template_ prefixed fields into form_data
   - Creates InvestigationTemplateResult record
   - Updates investigation status:
     - draft => processing
     - preliminary/final => resulted
   - Sets resulted_at and resulted_by for non-draft submissions
   - Redirects back (investigations.index when return_to=investigations.index)

This is the canonical path where result data and Results Available status are written together.

Relevant logic:
- resources/views/investigations/_actions.blade.php (Add Results link with return_to)
- resources/views/lab/results/form.blade.php (results submission form)
- app/Http/Controllers/LabController.php (showResultForm, storeResults)
- routes/web.php (lab.results.form, lab.results.store)

---

## 7) Viewing resulted investigations

From resulted status in the list:
- View Results action routes to lab.investigations.view-results
- Controller selects final result first; otherwise latest result
- Redirects to template result view page

Relevant logic:
- resources/views/investigations/_actions.blade.php
- app/Http/Controllers/LabController.php (viewInvestigationResults, viewTemplateResult)

---

## 8) Status model and labels

Investigation statuses defined in model:
- draft
- ordered
- collected
- processing
- resulted
- cancelled

Displayed label for resulted is Results Available.

Relevant logic:
- app/Models/Investigation.php

---

## 9) End-to-end sequence summary

1. Investigation is created with status ordered and ordered_at timestamp.
2. Investigation appears in DataTable list through investigations.index AJAX.
3. User triggers status updates from row actions.
4. Collection uses lab endpoint with stock validation and stock deduction side effects.
5. Add Results opens lab results form.
6. Results submission writes InvestigationTemplateResult and updates investigation status:
   - processing for draft
   - resulted (Results Available) for preliminary/final
7. Resulted row shows View Results action and timeline reflects resulted_at/resulted_by.

---

## 10) Notes and observed behavior

1. There is a dual status-update path by design:
   - LabController handles stock-sensitive transitions.
   - InvestigationController handles generic status updates.

2. Result entry is the reliable business path to Results Available because it persists both:
   - structured result payload
   - status/timestamp metadata

3. Frontend attempts stock checks from the list and can block collection/results actions when stock is insufficient.
