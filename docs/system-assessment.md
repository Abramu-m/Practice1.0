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

### 5. Offline / Local-First Operation
| Item | Status | Notes |
|---|---|---|
| Local data layer | **READY** | MySQL only (`config/database.php`, no SQLite fallback active); session/cache/queue all use the `database` driver (`config/session.php`, `config/cache.php`, `config/queue.php`, `QUEUE_CONNECTION=database`) — no Redis/Memcached. File storage is local disk (`FILESYSTEM_DISK=local`; S3 configured but unused). Mail is `MAIL_MAILER=log`. Core clinical workflows (registration, consultation, prescriptions, dispensing, billing — `PatientController.php:141-273`, `ConsultationController.php:38-100`, `PrescriptionController.php:34-97`, `PharmacistController.php`, `CashierController.php:14-150`) make zero network calls. |
| NHIF sync isolation | **PARTIAL** | `syncTariffs`/`submitClaim`/`submitBatch` (`NhifController.php:780,809,854-858`) already dispatch queued, retried jobs (Phase 1, DONE) — never block. `verifyMember` (L516), `getCardDetails` (L633), `authorize` (L687) call `NhifService` synchronously and will block/timeout if offline — acceptable since these are explicit, user-initiated NHIF actions, not core workflow blockers. Future UX nicety: clearer "NHIF unreachable" messaging on timeout. |
| Frontend asset dependency | **DONE (Phase 6.1)** | All ~20 CDN-hosted CSS/JS assets (fonts, Bootstrap, jQuery, Select2, DataTables, ApexCharts, Chart.js, OverlayScrollbars, Bootstrap Icons, Font Awesome, Toastr, moment, daterangepicker, Alpine) are now self-hosted via npm/Vite — zero `cdn.jsdelivr.net`/`cdnjs.cloudflare.com`/`code.jquery.com` requests on `/patient_visits`, `/financial/dashboard`, etc. Interactive libraries are copied from `node_modules/*/dist` to `public/vendor/*.min.js` by `resources/build/copy-vendor.mjs` (run via `npm run build`/`dev`) and loaded as classic, render-blocking `<script>` tags in `<head>` BEFORE `@vite()` — see Phase 6.1 below for why. AdminLTE's own CSS/JS (`dist/css/adminlte.css`, `dist/js/adminlte.js`, bundles Bootstrap) remains local as before. A basic PWA service worker (`public/sw.js`, `/offline` route, `public/manifest.json`) still exists as a secondary cache layer. Residual: `medications/consumption/analytics.blade.php` still loads moment.js/daterangepicker from CDN (separate from the vendored set, not yet migrated). |
| Backup/DR to remote site | **MISSING** | No bidirectional sync exists between `practice.local` and `janet-healthcare.com` (same facility, two access points). See Phase 6.2. |

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
- **Phase 6 — Offline/local-first hardening:**
  - **6.1 Vendor CDN assets locally — DONE (2026-06-11/12):** all ~20 CDN-hosted CSS/JS includes
    in `resources/views/layouts/app_main_layout.blade.php` (fonts, Bootstrap, jQuery, Select2,
    DataTables, ApexCharts, Chart.js, OverlayScrollbars, Bootstrap Icons, Font Awesome, Toastr),
    plus per-page Chart.js CDN includes (`financial/dashboard`, `nhif/reports`, `store/dashboard`,
    `medications/stock/ledger/stock-summary`, `medications/consumption/analytics`), replaced with
    npm packages bundled via the existing Vite pipeline (`resources/css/app.css` `@import`s the
    vendored CSS; `resources/js/app.js` and `resources/build/copy-vendor.mjs` handle the JS — see
    bugs below). Closes the offline-blocking gap in the core EMR.
    - **Bugs encountered & fixed during implementation:**
      1. **App-wide `$ is not a function` regression.** The first pass bundled jQuery/Bootstrap/
         Select2/DataTables/Toastr/moment/daterangepicker/Chart.js/ApexCharts/OverlayScrollbars/
         Alpine into `resources/js/app.js` and assigned them to `window.*` there, loaded via
         `@vite()`. But `@vite()` emits a `type="module"` script — like `defer`, it executes
         *after* the HTML is parsed, i.e. **after** the many inline `$(document).ready(...)`
         page scripts (this doc's own mandated DataTables-init pattern) that run synchronously
         during parsing. Result: `$`/`jQuery`/`bootstrap` were `undefined` on nearly every page
         (`Uncaught TypeError: $ is not a function`), not just `/patient_visits`.
         **Fix:** added `resources/build/copy-vendor.mjs`, which copies the pre-built UMD/IIFE
         "dist" bundles of these libraries from `node_modules/*/dist` into `public/vendor/`
         (wired into `npm run build` / `npm run dev`). `app_main_layout.blade.php` loads these as
         14 classic, render-blocking `<script src="...">` tags in `<head>`, **before**
         `@vite()`. `resources/js/app.js` is now just `import './bootstrap'` — the vendored
         libraries are no longer ESM imports.
      2. **500 error: "Too few arguments to function `Illuminate\Foundation\Vite::__invoke()`,
         0 passed... at least 1 expected".** A code-comment in `app_main_layout.blade.php`
         describing fix #1 contained the literal text `@vite` (no parentheses) inside an HTML
         `<!-- comment -->`. Blade's directive parser matches `@vite` regardless of HTML-comment
         context and rewrote it to a zero-argument `Vite::__invoke()` call. **Fix:** reworded the
         comment to avoid the bare `@vite` token, then `php artisan view:clear`.
    - Verified (curl, authenticated): `/patient_visits` and `/financial/dashboard` render 200,
      script order is vendor globals → `@vite` module → deferred page scripts → inline
      `$(document).ready(...)` blocks, and zero CDN `<script>`/`<link>` tags remain.
    - **Residual, out of scope:** `medications/consumption/analytics.blade.php` still loads
      moment.js + daterangepicker from CDN (separate libraries from the vendored set). That same
      view's `@push('styles')` is silently dropped — the layout only has `@stack('scripts')`, not
      `@stack('styles')` (pre-existing bug, see this doc's print-page `@section('styles')`
      guidance) — flagged, not fixed.
  - **6.2 Bidirectional sync between practice.local and janet-healthcare.com (DESIGN ONLY —
    future phase, same facility/dataset):** the two deployments represent the same facility's
    data and must converge — changes made offline propagate online once connectivity returns, and
    vice versa. Needs its own design pass covering: UUID/identity strategy for key clinical tables
    (patients, patient_visits, prescriptions, etc.) so independently-created rows never collide;
    an outbox/change-log per syncable table; a connectivity-aware scheduled sync job exchanging
    change-logs via an HMAC-authenticated endpoint (mirroring the existing
    `public/webhook-deploy.php` HMAC pattern); and conflict resolution (start with last-write-wins
    by `updated_at` + a `sync_conflicts` review log). Multi-week effort — not started.
