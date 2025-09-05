# Clinical Decision Support (CDS) Integration Plan

This document maps a practical, phased CDS strategy onto this Laravel application. It specifies where to hook into existing flows, the data structures needed, and how to deliver CDS to clinicians with minimal friction. For hands-on verification steps and troubleshooting, see the companion guide: `docs/CDS_TESTING_AND_DEBUGGING.md`.

## Scope and assumptions
- Laravel app with routes under `routes/`, controllers under `app/Http/Controllers`, services under `app/Services`, Blade views under `resources/views`, jobs/queues available.
- Existing clinical flows for visits, medications, labs, imaging, requisitions, NHIF/MTUHA/ICD tooling.
- CDS must be explainable, auditable, and safe-by-default (tiered severity, minimal alert fatigue).

## Architecture overview

- Core
  - `app/Services/CDS/CdsEngine.php` — Orchestrates CDS checks for a given trigger/context (e.g., medication prescribed, lab posted, vital recorded).
  - `app/Services/CDS/Rules/*` — Individual, versioned rules (synchronous blocking vs. asynchronous informational).
  - `app/Services/CDS/Integrations/*` — Optional clients (drug interaction API, formulary, external guidelines).

- Delivery
  - Blade components: `resources/views/components/cds/alert.blade.php`, `components/cds/badge.blade.php`, `components/cds/panel.blade.php`.
  - Middleware: `app/Http/Middleware/AttachCdsContext.php` to preload active alerts for patient/visit routes.
  - Notifications: `app/Notifications/CdsAlert.php` (channels: database/in-app; email/SMS optional).

- Events and jobs
  - Domain events: `MedicationPrescribed`, `MedicationUpdated`, `LabResultPosted`, `VisitOpened`, `ImagingOrdered`, `VitalRecorded`, `DischargePlanned`.
  - Job fan-out: `DispatchCdsChecks`, `ComputeEarlyWarningScores`, `RecomputeChronicDashboards`.

- Config/flags and logging
  - `config/cds.php` — feature toggles, thresholds, external API keys, severity policies.
  - Dedicated log channel `cds` in `config/logging.php` for rule execution/audit traces.

- Audit and explainability
  - Persist rule inputs, outputs, rationale, severity, and clinician response (accept/override/dismiss + reason).

## Minimal data model additions

Create migrations for the following tables (names may be adapted to existing conventions):

- cds_alerts
  - id, patient_id, visit_id, subject_type, subject_id, rule_key, rule_version
  - severity (critical/high/medium/low/info), message, rationale, payload (json)
  - status (open/accepted/overridden/dismissed), created_by, created_at, resolved_at

- cds_alert_actions
  - id, cds_alert_id, action (accept/override/dismiss), reason, user_id, created_at

- order_sets
  - id, key, name, version, content (json), specialty, active

- guideline_prompts
  - id, key, condition_icd10, trigger (event name), content (json), version, active

- dose_ranges
  - id, drug_key, indication, age_min/max, weight_min/max, egfr_min/max, hepatic_class
  - dose_min, dose_max, unit, route, frequency, notes

- atc_codes, drug_atc_map
  - atc_codes: code, name; drug_atc_map: drug_key, atc_code

- quality_measures
  - id, measure_key, label, period_start, period_end, denom, numer, exclusions, details (json)

- allergies (if not present)
  - id, patient_id, substance_key, substance_name, reaction, severity, recorded_at

- patient_conditions (problem list)
  - id, patient_id, icd10_code, label, status, onset_at, resolved_at

## Integration map by CDS category

For each feature, we list: Trigger/Hook → Logic/Data → UI/UX → Notes.

### 1) Medication-related safety

- Drug–drug interactions
  - Trigger/Hook: Medication create/update flow (`routes/medication.php` → controller `store/update`). Fire `MedicationPrescribed` before finalize; run `CdsEngine->check('medication_prescribe', $context)`.
  - Logic/Data: Local rules or external API lookup (cached). Needs active med list, `drug_atc_map`, interactions table/cache.
  - UI/UX: Modal for high/critical before commit (with override + reason), banner for info.
  - Notes: Cache by (patient_id, regimen hash) for 5–15 min; circuit-breaker if external API down.

- Drug–allergy checker
  - Trigger/Hook: Same as above.
  - Logic/Data: `allergies` table; match prescribed med by ingredient/ATC.
  - UI/UX: Critical modal; require override reason to proceed.

- Duplicate therapy (same ATC class)
  - Trigger/Hook: Same pre-commit check.
  - Logic/Data: Active meds grouped by ATC; if duplicate within lookback window → alert.
  - UI/UX: High severity modal; suggest de-duplication or schedule taper.

- Dose range checker (peds/renal/hepatic)
  - Trigger/Hook: Pre-commit; also recompute on new labs (`LabResultPosted`), vitals/weight updates (`VitalRecorded`).
  - Logic/Data: `dose_ranges`; calculators for eGFR, BSA; needs demographics (age/sex/weight) and labs (creatinine, LFTs).
  - UI/UX: Blocking when outside safe bounds; show recommended dose.

- Formulary guidance (in-stock alternatives)
  - Trigger/Hook: After safety checks, non-blocking hint.
  - Logic/Data: Inventory/requisitions service; alternative by ATC cost/stock ranking.
  - UI/UX: Inline suggestion with one-click swap.

- Predict ADR + adherence prediction (Phase 3+)
  - Trigger/Hook: Async after order; present as info chips.
  - Logic/Data: Historical ADRs, comorbidities; start with rules, optionally add ML.

### 2) Evidence-based guidelines & reminders

- Order sets/templates
  - Trigger/Hook: Visit/order pages — “Apply Order Set” button; controller `OrderSetController`.
  - Logic/Data: `order_sets` JSON: meds + labs + imaging + tasks; versioned.
  - UI/UX: Preview, select/deselect items, apply all.

- Basic guideline prompts (HTN, DM, immunizations)
  - Trigger/Hook: `VisitOpened`, `patient_conditions` updates.
  - Logic/Data: `guideline_prompts`; ICD-10 driven (use existing ICD tooling for mapping).
  - UI/UX: Right-rail prompt with quick actions.

- Preventive care reminders
  - Trigger/Hook: Nightly/weekly scheduler; banner on patient header and in lists.
  - Logic/Data: Last-done dates; age/sex/diagnosis rules.
  - UI/UX: Non-blocking, dismissible with snooze.

- Condition dashboards (e.g., diabetes → HbA1c/BP)
  - Trigger/Hook: Patient view → “Conditions”.
  - Logic/Data: Labs/vitals/meds trends; targets.
  - UI/UX: Cards with sparklines and goal status.

- Personalized guideline recommendations (Phase 3)
  - Trigger/Hook: Same prompts; modify by comorbidities/contraindications.

### 3) Diagnostics & risk stratification

- Built-in calculators (BMI, eGFR, CHA₂DS₂-VASc, Wells)
  - Trigger/Hook: Drawer on visit page; endpoint `/api/cds/calc`.
  - Logic/Data: `app/Services/CDS/Calculators/*`; uses vitals/demographics/labs if present.
  - UI/UX: Instant results; copy-to-note.

- Lab result highlighting (out-of-range, critical)
  - Trigger/Hook: `PatientVisitController::resultsDetails` and `printResults` flows shown in current routes.
  - Logic/Data: Reference ranges by test, possibly age/sex-specific.
  - UI/UX: Badges: low/high/critical; sticky critical summary.

- Imaging guidance prompts (appropriateness checks)
  - Trigger/Hook: `ImagingOrdered`; pre-commit advisory with override option.
  - Logic/Data: Rule tables for common indications.

- Diagnostic support & AI-driven diagnosis (Phase 3)
  - Trigger/Hook: Symptoms entry; background suggestions.

- Early-warning systems (e.g., sepsis)
  - Trigger/Hook: On `VitalRecorded` and `LabResultPosted` jobs.
  - Logic/Data: Compute NEWS/SIRS/qSOFA; thresholds send high-severity alert.
  - UI/UX: Header chip + ward dashboard panel; persistent until acknowledged.

### 4) Safety & quality monitoring

- Tiered alerts and fatigue control
  - Engine normalizes severity; modal only for critical/high; others as banners/chips.

- Audit trail of clinician responses
  - Override requires reason; stored in `cds_alert_actions` with user/time.

- Clinical quality measure tracking
  - Nightly job computes measures into `quality_measures`; export under `tools/`.

- Safety dashboards
  - Aggregate open alerts by ward/doctor/patient group; filter by severity/category.

- Predictive safety models / custom risk (Phase 3)
  - Async predictions; display as risk chips with trend.

### 5) Chronic disease management

- Condition dashboards (DM, HTN, HF)
  - Components for goals/trends; tie to guideline prompts and order sets.

- Medication adherence reminders
  - Non-urgent alerts/notifications; later SMS/app integration.

- Integrated care pathways
  - Pathway state machine per condition; tasks/follow-ups auto-created.

- Population-level monitoring
  - Cohort views with filters; export CSV.

- Disease progression prediction (Phase 3)
  - Background risk scoring; registry stratification.

### 6) Patient-centered tools

- Education materials
  - Trigger/Hook: On diagnosis or discharge; generate PDFs via dompdf (already present in project).
  - Data: Markdown/Blade templates mapped by ICD-10; multilingual.

- Lifestyle recommendations
  - Auto-inserted into discharge summary and visit notes.

- Multilingual instructions
  - `resources/lang/{locale}/education.php`; switch by patient preference.

- Patient portal integration (later)
  - Expose selected alerts/prompts via API guarded by Sanctum.

- Adherence risk & chatbot (later)
  - Optional integrations; behind feature flags.

### 7) Workflow & operational support

- Clinical pathway guidance (e.g., chest pain)
  - Sequential prompts with “Next step” actions; criteria-driven branching.

- Discharge checklist generator
  - Compile pending items: meds reconciliation, education, follow-up appointments, referrals.

- Referral prompts
  - When indications met, prefill referral note; allow one-click send.

- Care coordination reminders
  - Create tasks assigned to roles with due dates/notifications.

- Adaptive pathways & network integration (Phase 3)
  - Learn from outcomes; integrate external networks for referrals/transfers.

## Events and code hooks (by layer)

- Controllers/services fire events:
  - Medication saved → `MedicationPrescribed`
  - Lab result posted → `LabResultPosted`
  - Visit opened → `VisitOpened`
  - Imaging order created → `ImagingOrdered`
  - Vitals recorded → `VitalRecorded`
  - Discharge started → `DischargePlanned`

- Listener `DispatchCdsChecks` resolves `CdsEngine` and runs relevant rules for the context; critical alerts can interrupt the transaction with an override modal; informational alerts are queued and rendered in the CDS drawer.

## UI/UX delivery pattern

- Patient header chip showing count and severity of open CDS alerts.
- Right-rail “CDS Drawer” panel listing alerts, prompts, and calculators.
- Modal for high/critical alerts at commit time with explicit override reason.
- Inline lab result badges in `resultsDetails` and printable outputs.
- “Why am I seeing this?” link with rule rationale and references.

## Security, performance, and operations

- Feature flags in `config/cds.php` per module; default to conservative settings.
- Cache reference data and API results; add rate limiting and circuit breakers.
- Queue all async checks; time-bound sync checks to keep UI responsive (< 1.5s worst-case, < 300ms cached).
- Structured logging to `cds` channel; redact PHI from external calls.
- Seed minimal reference data (ATC, calculators, dose ranges) for immediate value.

## Rollout roadmap (phased)

- Phase 0 — Plumbing
  - CdsEngine skeleton, events, migrations (`cds_alerts`, `cds_alert_actions`), basic Blade components, config/logging, seeders.

- Phase 1 — Medication safety + lab highlighting
  - Allergy check, duplicate therapy, dose range checker, formulary hints; lab result highlighting in `resultsDetails` and print flows.

- Phase 2 — Guidelines + calculators
  - Order sets, preventive reminders, condition dashboards, common calculators; imaging appropriateness prompts.

- Phase 3 — Early warning + quality + discharge/referrals
  - Sepsis/NEWS scoring, quality measure computation and dashboards, discharge checklist, referral prompts.

- Phase 4 — Advanced/AI
  - ADR/adherence predictions, differential diagnosis support, personalized guidelines, adaptive pathways, portal surfacing.

## Success metrics

- P0: Events fire, alerts persist, UI components render; override reasons captured.
- P1: ≥90% of medication orders evaluated; false-positive rate <10%; median sync check latency <300 ms (cached) / <1.5 s (uncached).
- P2: Order sets reduce clicks by ≥30% for target conditions; calculators respond <100 ms.
- P3: Early-warning alerts within 1 minute of new data; monthly quality measures reproducible.
- P4: Advanced features gated; no regression in core workflows.

## Immediate next steps (suggested sprint)

1) Scaffolding
- Add migrations for `cds_alerts` and `cds_alert_actions`.
- Create `app/Services/CDS/CdsEngine.php`, a sample `Rules/AllergyRule.php`, `Rules/DuplicateTherapyRule.php`, and `Calculators/EgfrCalculator.php`.
- Add `config/cds.php` with feature flags and severity policy.
- Blade component `x-cds.alert` and a minimal CDS drawer layout.

2) Wire first checks
- In medication create/update flow, fire `MedicationPrescribed` and intercept responses to show modal for critical/high alerts.
- In `PatientVisitController::resultsDetails`, add reference-range highlighting.

3) Seed reference data
- Minimal ATC map for top 50 meds; example dose ranges for pediatrics/renal dosing; example order set (e.g., uncomplicated UTI).

4) Observability
- Add `cds` log channel; dashboard card for open alerts per patient.

---

References: Incorporate local/national guidelines and formularies; prefer conservative defaults. Map diagnoses via existing ICD tooling in `tools/`.

## Progress log

- 2025-09-05: Phase 0 scaffolding started
  - Added provider: `app/Providers/CdsServiceProvider.php` and registered in `bootstrap/providers.php`.
  - Events/Listeners: `App\Events\MedicationPrescribed`, `App\Listeners\DispatchCdsChecks`.
  - Services: `App\Services\CDS\CdsEngine` plus placeholder rules `AllergyRule`, `DuplicateTherapyRule`, `DoseRangeRule`.
  - Calculator: `App\Services\CDS\Calculators\EgfrCalculator`.
  - Config: `config/cds.php`; Logging: `config/logging.php` added `cds` channel.
  - Migrations: `create_cds_alerts_table`, `create_cds_alert_actions_table`.
  - UI: Blade component `resources/views/components/cds/alert.blade.php`.

Progress updates:
- 2025-09-05: Wired `MedicationPrescribed` in `ConsultationController@storePrescription` and listener path verified.
- 2025-09-05: Added reference-range highlighting for simple lab parameters in consultations Results tab (`consultations/show.blade.php`). Supports both `parameter` and `parameter_name` schemas; computes status from ranges when missing.
- 2025-09-05: Added persistence for CDS alerts (`App/Models/CdsAlert`, `CdsAlertAction`) and `CdsAlertService` to store and fetch alerts. `CdsEngine` now persists rule results.
- 2025-09-05: Implemented initial Duplicate Therapy rule (same medication) and surfaced alerts via a minimal CDS drawer component on the consultation page.
 - 2025-09-05: Implemented Allergy rule (critical severity) with a basic allergies table/model; added buttons to accept/override/dismiss alerts and an endpoint to capture clinician responses.
 - 2025-09-05: Extended lab result highlighting (status computed from reference ranges when missing) to lab result detail and modal/print views for consistency with consultation results.
 - 2025-09-05: Added baseline Dose Range rule using configurable per-dose max and pediatric mg/kg checks (uses patient age/weight when available). Configured examples for paracetamol and ibuprofen in `config/cds.php`.
 - 2025-09-05: Enhanced Dose Range rule with daily maximum checking using `MedicationFrequency.times_per_day` when available and optional renal eGFR-based caps from `config/cds.php`.
 - 2025-09-05: Added ATC mapping foundation — migrations for `atc_codes` and `drug_atc_map`, plus seeders `AtcCodesSeeder` and `DrugAtcMapSeeder`; wired into `DatabaseSeeder`.

Next up:
- Extend ATC mappings beyond initial set to cover local formulary and combination products.
- Add hepatic adjustments and more medications to `dose_policies`; consider route-specific dosing where relevant.
- Implement stricter blocking modal for high/critical alerts at commit with improved drawer refresh post-ack.
