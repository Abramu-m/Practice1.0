# CDS Testing, Assessment, and Debugging Guide (Phase 0–1)

This guide explains how to verify, test, and debug everything added in Phase 0 (plumbing) and Phase 1 (medication safety + lab highlighting).

## What’s covered
- Phase 0 status and validation
- Phase 1 status and validation
- End-to-end manual test flows (Allergy, Duplicate Therapy, Dose Range, Lab Highlighting)
- Observability (logging) and data inspection
- API checks (acknowledge/override/dismiss)
- Troubleshooting and common pitfalls
- Optional: quick automation ideas

---

## Current status snapshot

- Phase 0 (Plumbing)
  - Done: CdsEngine, events/listeners wiring, migrations for cds_alerts/cds_alert_actions/allergies, basic Blade components, CDS log channel, config.
  - Done: Alert persistence and UI drawer; acknowledgement/override/dismiss endpoint.
  - Pending: None blocking Phase 1. Allergies migration should be applied (see DB checks below).

- Phase 1 (Medication safety + lab highlighting)
  - Done: AllergyRule; DuplicateTherapyRule (same medication + ATC-class when mapping exists); DoseRangeRule (per-dose, pediatric mg/kg, daily max via frequency, optional renal caps if egfr provided in context); lab result highlighting standardized across views.
  - Pending: Expand ATC mappings; extend dose policies (hepatic, more meds); add stricter blocking modal.

---

## Environment & prerequisites

- Ensure feature flags are enabled in `config/cds.php`:
  - medication.allergy = true
  - medication.duplicate = true
  - medication.dose_range = true

- Migrations to have run:
  - 2025_09_05_120000_create_cds_alerts_table.php
  - 2025_09_05_120100_create_cds_alert_actions_table.php
  - 2025_09_05_121000_create_allergies_table.php
  - 2025_09_05_130000_create_atc_codes_table.php
  - 2025_09_05_130100_create_drug_atc_map_table.php

- Seeders (run targeted due to a legacy seeder error):
```powershell
php artisan db:seed --class=Database\Seeders\AtcCodesSeeder
php artisan db:seed --class=Database\Seeders\DrugAtcMapSeeder
```

- Logging channel `cds` is defined in `config/logging.php`; logs are written to `storage/logs/cds-*.log`.

---

## Quick validation checklist (Phase 0)

1) Database schema present
```powershell
php artisan migrate
php artisan tinker --execute "Schema::hasTable('cds_alerts');"
php artisan tinker --execute "Schema::hasTable('cds_alert_actions');"
php artisan tinker --execute "Schema::hasTable('allergies');"
```

2) Provider & listener registered
- `bootstrap/providers.php` includes `App\Providers\CdsServiceProvider::class`.
- `App\Providers\CdsServiceProvider` listens to `App\Events\MedicationPrescribed` → `App\Listeners\DispatchCdsChecks@handle`.

3) Logging available
```powershell
# after a prescription is saved (see tests below), check recent log lines
Get-ChildItem .\storage\logs | Sort-Object LastWriteTime -Descending | Select-Object -First 3
Get-Content .\storage\logs\cds-*.log -Tail 50
```

4) UI component present
- `resources/views/components/cds/drawer.blade.php` renders when there are open alerts.

---

## Quick validation checklist (Phase 1)

1) ATC mapping seeded
```powershell
php artisan tinker --execute "DB::table('atc_codes')->count();"
php artisan tinker --execute "DB::table('drug_atc_map')->count();"
```
Expect both to be ≥ 1 (we seeded Paracetamol/Acetaminophen and Ibuprofen).

2) Dose policies present
- `config/cds.php` includes `dose_policies` for `paracetamol` and `ibuprofen` with:
  - max_single_mg
  - max_daily_mg
  - peds_mg_per_kg_dose (structured)
  - renal caps (illustrative)

3) Lab highlighting visible
- Lab detail and modal/print views compute status from normal_range when missing; check after posting/selecting a result (see tests below).

---

## Manual test flows (end-to-end)

These use the standard prescribing flow in Consultation → Treatment/Prescriptions. After saving, the CDS drawer should update on the same page.

### A) Allergy conflict (critical)

Setup test data
1) Create an active allergy for a patient (matching a known medication name substring).
```powershell
php artisan tinker --execute "App\Models\Allergy::create(['patient_id'=>1,'substance_name'=>'paracetamol','reaction'=>'rash','severity'=>'severe','is_active'=>true,'recorded_at'=>now()]);"
```

Trigger and verify
2) Prescribe the same medication (e.g., Paracetamol 500 mg BD) for that patient in the consultation UI.
3) Expect: A critical CDS alert appears in the CDS drawer. Logs contain an entry under cds channel. DB has a new row in `cds_alerts` with rule_key = drug_allergy_conflict.

Acknowledge/override/dismiss (API)
4) Use the UI buttons or call the endpoint directly (replace {id}).
```powershell
# Example PowerShell Invoke-WebRequest (assuming session cookie or Sanctum is handled in app)
# Postman/curl via browser session is simpler; here’s the route to call:
# POST /cds-alerts/{alert}/ack with JSON { action: 'accept' | 'override' | 'dismiss', reason?: '...' }
```
5) Verify `cds_alert_actions` has the recorded action and `cds_alerts.status` is updated to resolved.

### B) Duplicate therapy (same medication)

Setup
1) Ensure the patient already has an active prescription of the target drug (e.g., Paracetamol) with status prescribed or prepared.

Trigger
2) Prescribe the same medication again.

Expect
- High severity alert: rule_key = duplicate_therapy_same_medication. Drawer shows alert; logs have an entry.

### C) Duplicate therapy (ATC class via mapping)

Setup
1) Confirm seeders created aliases under the same ATC: paracetamol and acetaminophen map to N02BE01.
```powershell
php artisan tinker --execute "DB::table('drug_atc_map')->join('atc_codes','atc_code_id','=','atc_codes.id')->select('medication_name','code')->get();"
```
2) Create an active prescription for Acetaminophen (medication generic or brand must match an alias in your medications table). If your medication catalog uses generic_name='Paracetamol' only, you can add another medication record with brand_name='Acetaminophen' for testing.

Trigger
3) Prescribe Paracetamol for the same patient.

Expect
- High severity alert: rule_key = duplicate_therapy_atc_class.
- Drawer shows alert; logs contain ATC class note.

Note: Class matching uses a join on prescriptions → medications and checks if the medication’s generic_name or brand_name matches any alias mapped to the same ATC code (case-insensitive).

### D) Dose range checks

Preconditions
- `config/cds.php` has dose_policies for medication you test (paracetamol/ibuprofen).
- Prescribing form must include a parseable dosage (e.g., "1500 mg").
- Daily max uses the selected `MedicationFrequency` (times_per_day or interval_hours).

Tests
1) Per-dose maximum
   - Prescribe Paracetamol 1500 mg OD.
   - Expect: High/critical dose_range alert for per-dose max.

2) Daily maximum
   - Prescribe Paracetamol 1000 mg QID (4x/day) if available in your MedicationFrequency table (times_per_day=4). If not present, test with interval_hours=6 (≈4/day).
   - Expect: High/critical dose_range_daily alert.

3) Pediatric mg/kg
   - Use a patient with age < 12 and a weight recorded in `vital_signs` for the visit (e.g., 20 kg). Prescribe a per-dose amount exceeding ~15 mg/kg (e.g., 400 mg for 20 kg → recommended ~300 mg).
   - Expect: High/critical dose_range alert with pediatric rationale.

4) Renal caps (optional)
   - Renal adjustment triggers only if `egfr` is passed in the CDS context. Current synchronous path does not compute egfr yet; plan to wire labs → egfr next.
   - You can simulate via a direct engine call in tinker (see Advanced/Debugging → Manual engine run).

### E) Lab result highlighting (consistency across views)

- Open a lab result in detail and the modal/print view. Ensure a parameter has a numeric normal_range like "3.5-5.1" and a value outside range.
- Expect: Status computed as low/high and badge rendered; matches the consultation results tab behavior.

---

## Observability & data inspection

Logs
```powershell
Get-Content .\storage\logs\cds-*.log -Tail 100
```
- Look for entries like "CDS alert" with rule_key and payload.

Database quick looks (use tinker)
```powershell
php artisan tinker --execute "App\Models\CdsAlert::orderByDesc('id')->take(5)->get(['id','visit_id','rule_key','severity','status']);"
php artisan tinker --execute "App\Models\CdsAlertAction::orderByDesc('id')->take(5)->get(['id','cds_alert_id','action','reason','user_id']);"
```

Open alerts for a visit (replace 123)
```powershell
php artisan tinker --execute "app(\\App\\Services\\CDS\\CdsAlertService::class)->forVisit(123)->map->only(['id','rule_key','severity','status']);"
```

---

## API checks (ack/override/dismiss)

Route
- POST `/cds-alerts/{alert}/ack`

Body (JSON)
- action: accept | override | dismiss
- reason: optional string (used with override)

Example (using browser or Postman recommended)
- With cookies/session auth from the app, send a POST to the route above. The response returns success, alert_id, status. Verify DB rows as shown earlier.

---

## Troubleshooting & common pitfalls

- Seeder error in DatabaseSeeder (MtuhaDiagnosesSeeder)
  - Symptom: Column not found (catname). Workaround: run only the ATC seeders shown earlier.

- No renal dose alerts
  - Reason: Current path doesn’t pass `egfr` to the engine. Plan: compute egfr from recent creatinine and demographics, then include in context.

- No ATC-class duplicate alert
  - Ensure: `atc_codes` and `drug_atc_map` exist and are populated; medication’s generic_name or brand_name matches any `drug_atc_map.medication_name` under the same ATC code.
  - Case-insensitive matching is used; expand seeders to cover local formulary and brand variants.

- Dose checks not triggering
  - Confirm: medication.dose_range flag is true; dosage string parseable (e.g., "500 mg" or "1 g"); a frequency with times_per_day/interval_hours is selected for daily checks.

- Drawer not appearing
  - Only renders when there are open alerts. Check `cds_alerts` for status=open and the view `resources/views/consultations/show.blade.php` includes `<x-cds.drawer :alerts=\"$cdsAlerts\" />`.

- Logs empty
  - Confirm logging channel `cds` exists and storage permissions allow writing to `storage/logs`.

- Allergies migration says "Nothing to migrate"
  - Ensure the migration filename is present and autoloaded. Try clearing caches:
```powershell
php artisan optimize:clear
php artisan migrate
```

---

## Advanced / debugging helpers

Manual engine run via tinker (simulate renal egfr)
```powershell
php artisan tinker

$ctx = [
  'patient_id' => 1,
  'visit_id' => 1,
  'egfr' => 25, # simulate renal impairment
  'order' => [
    'prescription_id' => null,
    'medication_id' => 1, # ensure this ID exists in medications
    'medication_name' => 'paracetamol',
    'dosage' => '1000 mg',
    'frequency_id' => 5  # ensure times_per_day=4 for this frequency
  ],
];
app(\\App\\Services\\CDS\\CdsEngine::class)->check('medication_prescribe', $ctx);
```
Then inspect `cds_alerts`.

Reset test data quickly
```powershell
php artisan tinker --execute "App\Models\CdsAlertAction::query()->delete(); App\Models\CdsAlert::query()->delete();"
```

---

## Optional automation ideas (Pest)

- Feature tests
  - Create a patient + medication; seed frequency; POST to your prescriptions endpoint; assert `cds_alerts` row created for Allergy/Duplicate/Dose cases.
- Unit tests
  - Call rule classes directly with mocked context to assert severity and messages.
- Snapshot tests for drawer HTML
  - Render `components.cds.drawer` with fake alerts and compare output.

---

## Acceptance criteria summary

- Phase 0
  - Events dispatch and engine runs without errors; alerts persist; drawer renders; actions recorded; `cds` logs written.
- Phase 1
  - Allergy, duplicate (same-med + ATC-class with mapping), and dose range alerts trigger with realistic inputs.
  - Lab result highlighting consistent across consultation results, detail, and modal/print.

If anything above doesn’t work as expected, check Troubleshooting and the logs first; then use the manual engine run to isolate the rule behavior.
