# NHIF Patient Flow — Developer Reference

This document traces the complete lifecycle of an NHIF patient encounter, from the moment a patient presents at the desk with their NHIF card to the point where a claim is either submitted as a single folio or as part of a batch. For every step it names the exact controller method, service method, view file, and database table involved, so a developer can navigate directly to the right place when debugging.

---

## High-Level Flow

```
Patient presents card
        │
        ▼
[1] Card Verification ──────────────────── NhifController@verifyMember
        │                                  NhifService@getCardDetails
        │                                  → nhif_members table
        │
        ▼
[2] Patient Visit & Consultation ────────── PatientVisitController
        │                                   ConsultationController
        │                                   → patient_visits, consultations,
        │                                     icd_diagnoses, investigations,
        │                                     prescriptions
        │
        ▼
[3] Claim Preview ───────────────────────── NhifController@previewClaim
        │                                   NhifService@buildClaimData
        │
        ▼
[4] Claim Creation ──────────────────────── NhifController@createClaim
        │                                   NhifService@verifyClaimData
        │                                   NhifService@buildClaimData
        │                                   → nhif_claims, nhif_claim_items,
        │                                     nhif_claim_diseases,
        │                                     nhif_claim_batches
        │
        ▼
[5a] Submit Single Claim ─────────────────── NhifController@submitClaim
  OR                                         NhifService@submitSingleFolio
[5b] Submit Batch ────────────────────────── NhifController@submitBatch
                                             NhifService@submitSingleFolio (×N)
                                             → nhif_claim_feedback,
                                               nhif_claim_errors
```

---

## Stage 1 — Card Verification

**Entry point:** `/nhif/verify` page  
**View:** `resources/views/nhif/verify.blade.php`

The receptionist or nurse enters the patient's NHIF card number. The form POSTs to `/nhif/verify-member`.

### Controller: `NhifController@verifyMember`
**File:** `app/Http/Controllers/NhifController.php`

1. Validates `card_number` (required string).
2. Calls `$this->nhifService->getCardDetails($cardNumber)`.
3. **If the API call succeeds:**
   - Searches for an existing local `Patient` record by `card_number`.
   - **Patient not found:** Returns JSON with `patient_exists: false` and a `prefill` object containing the patient's name, DOB, gender, etc. from the NHIF response. The front-end opens the patient creation modal pre-filled with this data.
   - **Patient found:** Calls `NhifMember::updateOrCreate(['card_no' => ...], [...])` to save or refresh the NHIF member record linked to the patient. Returns `redirect_url` pointing to the patient profile.
4. The `authorization_status` field is stored via `formatAuthorizationStatus()` (private helper in NhifController, line ~30) which normalises the NHIF raw string into a consistent `"Facility: X; Date: YYYY-MM-DD; Status: Accepted;"` format.

### Service: `NhifService@getCardDetails`
**File:** `app/Services/NhifService.php`

- Makes a GET request to `config('nhif.url.verification.{mode})?CardNo={cardNumber}`.
- Tries Bearer token auth first (`getAuthHeader()`); falls back to Basic auth if no token is available.
- Returns `['success' => true/false, 'data' => [...NHIF response...]]`.

### Database written
| Table | Action |
|---|---|
| `nhif_members` | `updateOrCreate` — one row per card number, linked to `patients.id` |

### Key fields stored in `nhif_members`
`card_no`, `card_status`, `first_name`, `last_name`, `full_name`, `gender`, `date_of_birth`, `expiry_date`, `authorization_status`, `authorization_no`, `employer_no`, `scheme_id`, `product_code`, `patient_id`, `verification_date`, `verified_by`.

### Debugging verification problems
- Check `storage/logs/laravel.log` for `NHIF getCardDetails` log entries — the URL, auth type, and HTTP status are all logged.
- Confirm `.env` has correct `NHIF_USERNAME`, `NHIF_PASSWORD`, `NHIF_MODE` (test/production).
- If "No auth header available": the token endpoint is failing — check `config/nhif.php` `url.token.{mode}`.
- If patient is not auto-linked: check `patients.card_number` column — the lookup is `Patient::where('card_number', $nhifData['CardNo'])`.

---

## Stage 2 — Patient Visit & Consultation

This stage is handled by the general patient management controllers, not NHIF-specific ones. It is documented here only to clarify what data must exist before a claim can be created.

### Visit creation
**Controller:** `PatientVisitController@store`  
**Table:** `patient_visits`  
**Key fields:** `patient_id`, `doctor` (FK → `doctors.doctor_id`), `visit_date`, `created_at` (used as attendance date and claim month/year derivation), `authorization_no`, `nhif_reference_no`.

### Consultation
**Controller:** `ConsultationController`  
**Table:** `consultations` (one per visit)

During the consultation the doctor records:

| What | Controller | Table |
|---|---|---|
| ICD-10 diagnoses | ConsultationController | `icd_diagnoses` |
| Lab investigations | InvestigationController / OrderController | `investigations` |
| Prescriptions | PrescriptionController | `prescriptions` |

**All three must exist before a claim can be created.** `NhifService@verifyClaimData` will block claim creation if any are missing.

### Why visit date matters
`NhifService@buildClaimData` uses `$visit->created_at->year` and `$visit->created_at->month` to determine the claim period and to find/create the correct `nhif_claim_batches` row. If a claim ends up in the wrong batch, check the `created_at` timestamp on the visit row.

---

## Stage 3 — Claim Preview

**Entry point:** Claims Management page — `/nhif/claims`  
**View:** `resources/views/nhif/claims.blade.php`

The user selects a patient visit from the dropdown and clicks **Preview & Create Claim**. A JavaScript function `previewClaim(visitId)` fires an AJAX GET to `/nhif/preview-claim/{visitId}`.

### Controller: `NhifController@previewClaim`
**File:** `app/Http/Controllers/NhifController.php`

1. Calls `$this->nhifService->buildClaimData($visitId)`.
2. Reshapes the raw result into a simpler JSON structure for the modal (patient info, diagnoses array, items array, total amount, claim period).
3. Returns 422 + `message` if `buildClaimData` throws (e.g. missing NHIF membership, missing consultation).

### Service: `NhifService@buildClaimData`
**File:** `app/Services/NhifService.php`

Loads the visit with these eager-loaded relationships:
```
PatientVisit
  → patientInfo (Patient)
      → nhifMember (NhifMember)
  → doctorInfo (Doctor)
      → user (User)   ← for doctor name
  → consultation
      → icdDiagnoses  ← FolioDiseases
      → investigations
          → medicalService → pricing(2)   ← FolioItems (services)
      → prescriptions
          → medication → pricing(2)       ← FolioItems (medications)
```

Returns an array with keys: `FolioID`, `ClaimYear`, `ClaimMonth`, `FolioNo`, `SerialNo`, `CardNo`, `FirstName`, `LastName`, `Gender`, `DateOfBirth`, `TelephoneNo`, `PatientFileNo`, `AuthorizationNo`, `AttendanceDate`, `PatientTypeCode`, `PractitionerNo`, `DoctorName`, `FolioDiseases[]`, `FolioItems[]`, `total_amount`.

The `total_amount` is the sum of `AmountClaimed` across all items (previously this was always 0 — fixed).

### No database writes at this stage.
The preview is read-only. Nothing is persisted until the user clicks Confirm.

### Debugging preview problems
- "Patient does not have an NHIF membership record" → Stage 1 was skipped or the `nhif_members` row was not linked to this patient.
- "No consultation record found" → Consultation was not saved for this visit.
- Empty diagnoses / items → The consultation exists but `icd_diagnoses` / `investigations` / `prescriptions` tables have no rows for this `consultation_id`.
- Pricing returns 0 → `medicalService->pricing(2)` or `medication->pricing(2)` returned no `insurance_covered_amount`. Check the `nhif_tariffs` table and whether tariffs have been synced (NHIF → Tariffs page, `NhifController@syncTariffs`).

---

## Stage 4 — Claim Creation

When the user clicks **Confirm & Create Claim** in the preview modal, the front-end POSTs to `/nhif/create-claim` with `patient_visit_id`.

### Controller: `NhifController@createClaim`
**File:** `app/Http/Controllers/NhifController.php`

1. Validates `patient_visit_id` exists in `patient_visits`.
2. Calls `$this->nhifService->verifyClaimData($visitId)` — returns early with 422 if validation fails.
3. Calls `$this->nhifService->buildClaimData($visitId)` to get the full claim array.
4. Derives `$claimMonth` and `$claimYear` from `$claimData['ClaimMonth']` / `$claimData['ClaimYear']` (i.e. from the visit date).
5. Calls `NhifClaimBatch::firstOrCreate(['claim_month' => ..., 'claim_year' => ...], ['claim_no' => 'NHIF/{facilityCode}/{MMM-YYYY}', ...])` — creates a batch if none exists for that month/year.
6. Creates an `NhifClaim` row with `claim_status = 'draft'`.
7. Iterates `FolioDiseases` → inserts `NhifClaimDisease` rows.
8. Iterates `FolioItems` → inserts `NhifClaimItem` rows.
9. Returns JSON with the created claim and any warnings from `verifyClaimData`.

### Service: `NhifService@verifyClaimData`
**File:** `app/Services/NhifService.php`

Blocking checks (return `is_valid: false` → claim not created):
- No consultation on visit
- No ICD diagnoses
- No investigations and no prescriptions
- `NhifClaim` row already exists for this `patient_visit_id` (duplicate guard)

Non-blocking warnings (claim is created anyway, warnings shown in UI):
- `authorization_no` is null/empty
- More than 10 diagnoses
- One or more items missing `nhif_auth_ref`

### Database written
| Table | Action |
|---|---|
| `nhif_claim_batches` | `firstOrCreate` — one batch per month/year |
| `nhif_claims` | `create` — one row, `claim_status = 'draft'` |
| `nhif_claim_diseases` | `create` (N rows, one per ICD diagnosis) |
| `nhif_claim_items` | `create` (N rows, one per service + one per medication) |

### Key fields on `nhif_claims`
`nhif_claim_batch_id`, `folio_id` (UUID), `folio_no` (= visit ID), `serial_no`, `card_no`, `patient_id`, `patient_visit_id`, `authorization_no`, `attendance_date`, `claim_year`, `claim_month`, `practitioner_no`, `total_amount_claimed`, `claim_status` (draft), `facility_code`.

> **Unique constraint:** `nhif_claims.patient_visit_id` is unique. Attempting to create a second claim for the same visit will be caught by `verifyClaimData` (application level) and by the database constraint (database level).

### Debugging creation problems
- 422 with `errors` array → check `verifyClaimData` output. Most likely: missing consultation, missing diagnoses, or duplicate visit.
- 500 error → check `storage/logs/laravel.log` for "NHIF Claim Creation Error".
- Claim created with `total_amount_claimed = 0` → `buildClaimData` is not finding pricing. Check `nhif_tariffs` is populated.
- Claim ends up in the wrong batch → visit `created_at` date is not what you expected; batch uses that date, not today's date.

---

## Stage 5a — Submit Single Claim

**Entry point:** Claims Management page → "Submit Single Claim" card  
The user selects a draft claim by folio and patient name, then clicks **Submit to NHIF**.

Route: `POST /nhif/submit-claim`

### Controller: `NhifController@submitClaim`
**File:** `app/Http/Controllers/NhifController.php`

1. Validates `claim_id` exists in `nhif_claims`.
2. Confirms `claim_status === 'draft'` — rejects with 422 if not.
3. Loads the claim with `claimDiseases` and `claimItems`.
4. Delegates to `$this->nhifService->submitSingleFolio($claim)`.
5. Returns the result JSON directly (200 on success, 400 on NHIF rejection).

### Service: `NhifService@submitSingleFolio`
**File:** `app/Services/NhifService.php`

1. Calls `$claim->loadMissing('claimDiseases', 'claimItems')`.
2. Builds the NHIF payload array from the stored claim data (folio fields + diseases array + items array).
3. Calls `$this->submitClaimToNHIF($payload)` — POSTs to `config('nhif.url.claim')`.
4. **On success:**
   - Creates a `NhifClaimFeedback` row with submission details and the NHIF response.
   - Updates `nhif_claims`: `claim_status = 'submitted'`, `submission_date`, `response_data`, `submitted_by`.
5. **On failure:**
   - Creates a `NhifClaimError` row with `error_message` and `status = 'unresolved'`.
   - Leaves the claim as `draft` (retryable).

### Service: `NhifService@submitClaimToNHIF`
**File:** `app/Services/NhifService.php`

Low-level HTTP method. POSTs the payload to `config('nhif.url.claim')` with a Bearer token header. Returns `['success' => bool, 'data' => ..., 'error' => ...]`.

### Database written
| Table | Action | Condition |
|---|---|---|
| `nhif_claims` | update `claim_status`, `submission_date`, `response_data`, `submitted_by` | success |
| `nhif_claim_feedback` | insert | success |
| `nhif_claim_errors` | insert | failure |

### Debugging single submission problems
- "Only draft claims can be submitted" → the claim is already `submitted`/`approved`/`rejected`. Check `nhif_claims.claim_status`.
- NHIF rejects the claim (400 from NHIF API) → check `nhif_claim_errors.error_message` for this `nhif_claim_id`. Also check `storage/logs/laravel.log` for "NHIF Claim Submission Error".
- Connection error → check NHIF API URL in `config/nhif.php` (`url.claim`) and whether the facility is on test or production mode (`NHIF_MODE`).

---

## Stage 5b — Submit Batch

**Entry point:** Claims Management page → "Submit Batch" card  
The user selects a batch by its `claim_no` (e.g. `NHIF/03747/JUN-2026`) and clicks **Submit Batch**.

Route: `POST /nhif/submit-batch`

### Controller: `NhifController@submitBatch`
**File:** `app/Http/Controllers/NhifController.php`

1. Validates `batch_id` exists in `nhif_claim_batches`.
2. Loads the batch with all claims where `claim_status = 'draft'`, eager-loading `claimDiseases` and `claimItems`.
3. Returns 422 if no draft claims exist in the batch.
4. Loops through each draft claim, calling `$this->nhifService->submitSingleFolio($claim)`.
5. Counts successes and failures.
6. If at least one claim was submitted successfully, updates `nhif_claim_batches.status = 'Submitted'`.
7. Returns a summary: `{ submitted: N, failed: M, errors: [{folio_no, message}] }`.

Each individual claim in the loop follows the same path as Stage 5a (`submitSingleFolio`), writing to `nhif_claim_feedback` on success and `nhif_claim_errors` on failure.

### Batch status values (`nhif_claim_batches.status`)
| Value | Meaning |
|---|---|
| `Open` | Batch exists, claims are being added, not yet submitted |
| `Submitted` | At least one claim in the batch has been submitted |

### Debugging batch submission problems
- "No draft claims found in this batch" → all claims in this batch are already submitted or were deleted. Query: `SELECT claim_status, COUNT(*) FROM nhif_claims WHERE nhif_claim_batch_id = ? GROUP BY claim_status`.
- Some claims submitted, some failed → check `nhif_claim_errors` filtered by the failing claim IDs. The batch `status` is still set to `Submitted` if any succeeded.
- Batch appears in wrong month → the batch is keyed on `claim_month`/`claim_year` from the visit's `created_at` date, not today.

---

## Batch Management View

**Route:** `GET /nhif/claim-batches`  
**Controller:** `NhifClaimBatchController@index`  
**View:** `resources/views/nhif/claim-batches/index.blade.php`

Lists all batches with claim counts and status. Clicking a batch loads `NhifClaimBatchController@show` which renders `resources/views/nhif/claim-batches/show.blade.php` (or returns JSON for the inline modal via `nhif/claim-batches/modal.blade.php`).

---

## Database Quick Reference

```
nhif_members
  id, card_no (unique), card_status, patient_id (FK → patients),
  authorization_no, authorization_status, verification_date, verified_by

nhif_claims
  id, nhif_claim_batch_id (FK → nhif_claim_batches),
  patient_visit_id (unique FK → patient_visits),
  patient_id (FK → patients),
  folio_id (UUID), folio_no, serial_no, card_no, authorization_no,
  attendance_date, claim_year, claim_month,
  total_amount_claimed, claim_status, submission_date,
  response_data (JSON), submitted_by, facility_code, practitioner_no

nhif_claim_batches
  id, claim_no (unique, e.g. NHIF/03747/JUN-2026),
  claim_year, claim_month, status (Open/Submitted),
  number_of_folios, amount_claimed, facility_code

nhif_claim_items
  id, nhif_claim_id (FK), folio_item_id,
  item_code, item_name, other_details,
  item_quantity, unit_price, amount_claimed,
  approval_ref_no, medical_service_id, medication_id

nhif_claim_diseases
  id, nhif_claim_id (FK), folio_disease_id,
  disease_code, disease_name, remarks (Provisional/Final)

nhif_claim_feedback
  id, nhif_claim_id (FK), submission_no, date_submitted,
  claim_year, claim_month, folio_no, card_no,
  authorization_no, amount_claimed, remarks, nhif_response (JSON)

nhif_claim_errors
  id, nhif_claim_id (FK), visit_id,
  error_message, status (unresolved/resolved), resolution_notes

nhif_tariffs
  id, item_code, item_name, unit_price, scheme_id, package_id,
  is_restricted, is_excluded, excluded_for_products, last_updated
```

---

## NHIF API Authentication

All NHIF API calls are handled by `NhifService`. Authentication is managed by `getAuthHeader()` (private method):

1. Checks if a cached Bearer token is stored in-memory and not expired.
2. If expired or absent, calls `obtainToken()` which POSTs to `config('nhif.url.token.{mode}')` with `grant_type=password`.
3. Stores the token and its expiry in object properties (`$this->token`, `$this->tokenExpiresAt`).
4. Falls back to Basic auth if the token endpoint fails.

> **Note:** The token is cached per request cycle only (object property, not Redis/cache). A long-running queue job or a batch with many claims will re-authenticate on the next service instantiation.

### Relevant `.env` keys
```
NHIF_USERNAME=
NHIF_PASSWORD=
NHIF_MODE=test              # test | production
NHIF_FACILITY_CODE=03747
NHIF_PRACTITIONER_NO=12345
NHIF_TIMEOUT=30             # seconds
```

Config file: `config/nhif.php`

---

## File Map

| Concern | File |
|---|---|
| NHIF controller (verification, claims, tariffs) | `app/Http/Controllers/NhifController.php` |
| Batch listing/viewing controller | `app/Http/Controllers/NhifClaimBatchController.php` |
| All NHIF API calls + claim building | `app/Services/NhifService.php` |
| NHIF member model | `app/Models/NhifMember.php` |
| Claim model | `app/Models/NhifClaim.php` |
| Claim batch model | `app/Models/NhifClaimBatch.php` |
| Claim item model | `app/Models/NhifClaimItem.php` |
| Claim disease model | `app/Models/NhifClaimDisease.php` |
| Claim feedback model | `app/Models/NhifClaimFeedback.php` |
| Claim error model | `app/Models/NhifClaimError.php` |
| Claims management page view | `resources/views/nhif/claims.blade.php` |
| Verification page view | `resources/views/nhif/verify.blade.php` |
| Batch list view | `resources/views/nhif/claim-batches/index.blade.php` |
| Batch detail view | `resources/views/nhif/claim-batches/show.blade.php` |
| Batch modal partial | `resources/views/nhif/claim-batches/modal.blade.php` |
| NHIF config | `config/nhif.php` |
| NHIF routes | `routes/web.php` (search for `prefix('nhif')`) |
