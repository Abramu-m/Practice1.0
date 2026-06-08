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

**Status of remediation (Phase 0):** the example file has been sanitized to placeholders and git
history has been reset so the values no longer appear in any commit. **Rotation is still required**
— a secret that was ever committed must be considered exposed. Revoke the Gmail app password in the
Google account and change the NHIF account password; history scrubbing does not un-leak a credential
that others may already have seen.

---

## Scorecard

### 1. Regulatory & Compliance
| Item | Status | Notes |
|---|---|---|
| HFR / facility code | PARTIAL | NHIF `facility_code` exists, but no general MoH **HFR** code field on the `facilities` table used across modules. |
| PDPA — encryption at rest | **MISSING** | Only `NhifSetting.username/password` are encrypted. Patient PII (NIDA, card no., contact, address) and all clinical data are plaintext. No data-access audit log. |
| MoH certification readiness | MISSING | No audit trail of clinical/claim changes; no documented security posture. |

### 2. Clinical Data Standardization
| Item | Status | Notes |
|---|---|---|
| ICD-10 diagnosis coding | **READY** | Full `icd_10` library, `icd_diagnoses` (provisional/final), MTUHA category mapping. Diagnoses are coded, not free-text. Strongest area. |
| MSD item codes (pharmacy/inventory) | **MISSING** | `medications` has no `msd_code`/national item code. Tracked by generic name only. |
| LOINC / SNOMED CT (lab) | **MISSING** | `medical_services`/`investigations` carry no LOINC/SNOMED/national lab code; results stored as untyped JSON `form_data`. |

### 3. Government System Endpoints
| Item | Status | Notes |
|---|---|---|
| NHIF e-Claim | **PARTIAL (strong)** | Real live API integration: token/OAuth2, member verify, card authorize, tariff sync, claim build + `SubmitFolios`. See `app/Services/NhifService.php`, `config/nhif.php`. **Gaps:** no claim-status feedback loop (status stuck at `submitted`; `getSubmittedClaims` exists but no poller/UI), tariff sync is manual (no schedule), referral/pre-approval APIs wired but no UI, claim prices not re-validated against live tariffs. |
| DHIS2 (MTUHA push) | **MISSING (engine ready)** | `MtuhaReportService` produces correct monthly age/gender/financing aggregates locally, but **zero** DHIS2 code — no API client, data-element/orgUnit mapping, or push. Reports are local/PDF only. |
| GePG payment gateway | **MISSING** | No control-number or government-payment code at all. |
| National Client Registry / NIDA | **PARTIAL** | `patients.nida` field exists but no format validation, no uniqueness, no registry verification, not carried into encounters. |

### 4. Technical Architecture
| Item | Status | Notes |
|---|---|---|
| Async queues for gov calls | **MISSING** | `QUEUE_CONNECTION=database` configured but `app/Jobs/` is empty. All NHIF calls are synchronous/blocking on the request thread. |
| Offline-first / retry | **MISSING** | An `/offline` route exists but no persistent retry queue, no scheduled re-sync, no exponential backoff. `app/Console/Kernel.php` has no scheduled tasks. |
| API security / secrets | **PARTIAL** | Uses Laravel `Http` client and `.env`/encrypted settings correctly. Committed-secret leak addressed in Phase 0. Fixed 30s timeout; `retry_attempts=3` configured but unused except in `authorizeCard`. |
| AuthZ | PARTIAL | Laravel Breeze + custom role flags + verification middleware work; `spatie/laravel-permission` installed but **not used**. No 2FA, no auth audit log. |

---

## Overall status

- **Stack:** Laravel 12, PHP 8.2, MySQL. Modules are comprehensive and active: registration,
  visits/consultation, prescriptions, lab, pharmacy/store/inventory, billing, CDS rule engine,
  reporting, NHIF.
- **Strongest:** ICD-10 coding, MTUHA aggregation engine, NHIF live API (claim submission path).
- **Weakest / blocking for government integration:** no DHIS2 push, no MSD/LOINC codes, no async
  queue + retry layer, patient PII unencrypted.

---

## Remediation roadmap

- **Phase 0 — Security hygiene (DONE):** sanitize `.env.example`, reset git history. Manual rotation of the leaked credentials remains the owner's responsibility.
- **Phase 1 — Resilience layer (1–2 wk):** queued Jobs + retry/backoff for all gov calls; scheduler (daily tariff sync, daily claim-status poll); outbound-request retry table for offline-first.
- **Phase 2 — Close NHIF loop (1–2 wk):** claim-status polling → `claim_status`; tariff re-validation on claim build; referral & pre-approval UI.
- **Phase 3 — Coding standards (2–3 wk):** `msd_code` on medications + LOINC/SNOMED on lab services, with mapping UIs + seed import.
- **Phase 4 — DHIS2 push (2–3 wk):** DHIS2 client, data-element/orgUnit mapping, dataValueSet from `MtuhaReportService`, scheduled submission + audit log.
- **Phase 5 — Compliance hardening (ongoing):** encrypt patient PII at rest; data-access audit log; NIDA validation/uniqueness; HFR facility code; activate Spatie / 2FA.
- **GePG:** deferred — only if the facility collects official government fees.
