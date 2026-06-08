# System Status Assessment — Government-Integration Readiness

> Generated 2026-06-08. A gap analysis of the Janet Healthcare EMR against the requirements for
> connecting a private Tanzanian EMR to the official/government health ecosystem (regulatory,
> clinical-coding standards, government endpoints, technical architecture). Each item is rated
> **READY** / **PARTIAL** / **MISSING**.
>
> Audit basis: three parallel code explorations (NHIF/insurance, clinical-coding/reporting,
> architecture/security) plus direct verification of the highest-stakes findings.

---

## 🔴 URGENT — committed secrets

`.env.example` (tracked in git) contained live credentials:

- A real Gmail address + a real 16-char **Gmail app password** (`MAIL_PASSWORD`).
- Real-looking NHIF **production** credentials and facility code (`NHIF_MODE=production`).

**Status of remediation (Phase 0 — DONE):** the example file has been sanitized to placeholders,
git history has been reset so the values no longer appear in any commit, and **the Gmail app
password and NHIF account password have been rotated** (2026-06-08). This item is fully closed.

---

## Scorecard

### 1. Regulatory & Compliance
| Item | Status | Notes |
|---|---|---|
| HFR / facility code | **READY** | `facilities.hfr_code` added (2026-06-08, separate from `nhif_facility_code`), editable via Settings → Facility Details. |
| PDPA — encryption at rest | **MISSING** | Only `NhifSetting.username/password` are encrypted. Patient PII (NIDA, card no., contact, address) and all clinical data are plaintext. No data-access audit log. |
| MoH certification readiness | MISSING | No audit trail of clinical/claim changes; no documented security posture. |

### 2. Clinical Data Standardization
| Item | Status | Notes |
|---|---|---|
| ICD-10 diagnosis coding | **READY** | Full `icd_10` library, `icd_diagnoses` (provisional/final), MTUHA category mapping. Diagnoses are coded, not free-text. Strongest area. |
| MSD item codes (pharmacy/inventory) | **PARTIAL** | `msd_codes` reference library + `medications.msd_code_id` mapping, library browser, Select2 attach-picker on the medication form, and `codes:import msd <file>` bulk import now exist. Library is empty until a real MSD code list is sourced and imported — structure is ready, data is the user's responsibility. |
| LOINC / SNOMED CT (lab) | **PARTIAL** | Unified `lab_codes` library (`coding_system` discriminator) + `medical_services.loinc_code_id`/`snomed_code_id` mapping, library browser with system filter, independent Select2 attach-pickers on the service form, and `codes:import loinc\|snomed <file>` bulk import now exist. Library is empty until real LOINC/SNOMED CT lists are sourced and imported. |

### 3. Government System Endpoints
| Item | Status | Notes |
|---|---|---|
| NHIF e-Claim | **PARTIAL (strong)** | Real live API integration: token/OAuth2, member verify, card authorize, tariff sync, claim build + `SubmitFolios`. See `app/Services/NhifService.php`, `config/nhif.php`. Submission/sync now run as queued, retried jobs (Phase 1) and a daily `PollNhifClaimStatusJob` exists — **but it only logs/normalizes status; nothing in the UI surfaces `claim_status` changes or `NhifClaimFeedback`/`NhifClaimError` rows yet, and its folio-matching logic is unverified against a live response.** Remaining gaps: tariff re-validation on claim build, referral/pre-approval UI. |
| DHIS2 (MTUHA push) | **MISSING (engine ready)** | `MtuhaReportService` produces correct monthly age/gender/financing aggregates locally, but **zero** DHIS2 code — no API client, data-element/orgUnit mapping, or push. Reports are local/PDF only. |
| GePG payment gateway | **MISSING** | No control-number or government-payment code at all. |
| National Client Registry / NIDA | **PARTIAL** | `patients.nida` field exists but no format validation, no uniqueness, no registry verification, not carried into encounters. |

### 4. Technical Architecture
| Item | Status | Notes |
|---|---|---|
| Async queues for gov calls | **READY** (Phase 1 done) | `SyncNhifTariffsJob`, `SubmitNhifClaimJob`, `PollNhifClaimStatusJob` in `app/Jobs/` — all `ShouldQueue` with `$tries=6`, exponential `backoff()`, `retryUntil(+24h)`. Controller methods (`syncTariffs`/`submitClaim`/`submitBatch`) now dispatch and return immediately; batches use `Bus::batch()`. |
| Offline-first / retry | **PARTIAL → mostly addressed** | Native Laravel retry/backoff now covers all NHIF calls (up to 24h, landing in `failed_jobs` on exhaustion, replayable via `queue:retry`). Daily tariff-sync + claim-status-poll schedules registered in `routes/console.php` (note: **not** `app/Console/Kernel.php` — that class is unbound/dead in this app, see project memory). Still missing: a generic outbound-request retry table for non-NHIF integrations, and `schedule:run` is not yet wired to an OS-level cron/Task Scheduler. |
| API security / secrets | **PARTIAL** | Uses Laravel `Http` client and `.env`/encrypted settings correctly. Committed-secret leak addressed in Phase 0. Fixed 30s timeout; `retry_attempts=3` configured but unused except in `authorizeCard`. |
| AuthZ | PARTIAL | Laravel Breeze + custom role flags + verification middleware work; `spatie/laravel-permission` installed but **not used**. No 2FA, no auth audit log. |

---

## Overall status

- **Stack:** Laravel 12, PHP 8.2, MySQL. Modules are comprehensive and active: registration,
  visits/consultation, prescriptions, lab, pharmacy/store/inventory, billing, CDS rule engine,
  reporting, NHIF.
- **Strongest:** ICD-10 coding, MTUHA aggregation engine, NHIF live API (claim submission path).
- **Weakest / blocking for government integration:** no DHIS2 push, MSD/LOINC/SNOMED coding
  structure now in place but libraries are unseeded (real code lists still need sourcing), no async
  queue + retry layer, patient PII unencrypted.

---

## Remediation roadmap

- **Phase 0 — Security hygiene (DONE):** sanitized `.env.example`, reset git history, **and rotated
  both leaked credentials** (Gmail app password + NHIF account password, 2026-06-08). Fully closed.
- **Phase 1 — Resilience layer (DONE):** `SyncNhifTariffsJob`/`SubmitNhifClaimJob`/`PollNhifClaimStatusJob`
  with native retry/backoff + 24h `retryUntil`; controllers dispatch instead of blocking; daily
  schedules registered in `routes/console.php`; verified end-to-end via `queue:work`. Remaining ops
  step: wire `php artisan schedule:run` to Windows Task Scheduler so the schedules actually fire.
- **Phase 2 — Close NHIF loop (1–2 wk) — RECOMMENDED NEXT:** `PollNhifClaimStatusJob` exists but its
  folio-matching/status-normalization is unverified against a live NHIF response — run it against
  the test environment and confirm `claim_status` updates correctly on a known claim. Then surface
  the result in the UI (claim list should show `approved`/`rejected`/`pending`/`queued` distinctly,
  with `NhifClaimFeedback`/`NhifClaimError` visible to staff). Also: tariff re-validation on claim
  build, referral & pre-approval UI.
- **Phase 3 — Coding standards (2–3 wk):** `msd_code` on medications + LOINC/SNOMED on lab services, with mapping UIs + seed import.
- **Phase 4 — DHIS2 push (2–3 wk):** DHIS2 client, data-element/orgUnit mapping, dataValueSet from `MtuhaReportService`, scheduled submission + audit log.
- **Phase 5 — Compliance hardening (ongoing):** encrypt patient PII at rest; data-access audit log;
  NIDA format validation (uniqueness already enforced — `PatientController.php:156,311`); activate
  Spatie / 2FA. ~~HFR facility code~~ — **done** (`facilities.hfr_code`, 2026-06-08).
- **GePG:** deferred — only if the facility collects official government fees.
