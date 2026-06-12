# Phase 6.2 — Bidirectional Sync Design (practice.local ↔ janet-healthcare.com)

> Design-only document. No code in this repo implements this yet. Companion to
> `docs/system-assessment.md` § "5. Offline / Local-First Operation" and Phase 6.2 of the
> remediation roadmap — that doc remains the source of truth for overall project status; this
> file is the detailed architecture for Phase 6.2 specifically.

## 1. Problem statement

`practice.local` (LAN-only, CloudPanel, offline-capable) and `janet-healthcare.com`
(internet-facing) run **two independent MySQL instances** holding data for **the same facility**
(`facilities` id=1 on each). Staff may use either access point depending on connectivity. Today
nothing reconciles the two — a patient registered on one is invisible on the other.

Goal: when both instances have connectivity, periodically exchange changes so that
inserts/updates made on either side converge on both, with auto-increment PK collisions resolved
and same-record edits on both sides reconciled or flagged for review.

**Out of scope for this document:** anything that requires constant connectivity (that's just
"online mode" and already works); a general-purpose multi-tenant sync framework — this is
deliberately a two-node, single-facility design.

## 2. Scope — which tables sync (v1)

Reference/lookup tables (`icd_10`, `msd_codes`, `lab_codes`, `medication_units`,
`medication_frequencies`, `drug_classes`, `administration_routes`, `visit_types`,
`patient_categories`, `service_categories`, `store_categories`, `result_templates`,
`sample_types`, `age_groups`, `designations`, `cds_*`, `*_tariff`, `nhif_tariffs`,
`referral_hospitals`, `referral_departments`, `system_settings`, `facilities`) change rarely and
are edited by the same small admin group. **Not in scope for the outbox/conflict machinery below**
— see § 9 "Config-table sync (lightweight, separate mechanism)".

### v1 syncable tables (the registration → clinical → billing chain)

| Table | Notes |
|---|---|
| `patients` | core identity |
| `patient_visits` | FKs: `patient`, `visit_type`, `doctor`, `visit_category`, `informed_by`, `created_by` |
| `consultations` | FKs: `patient_id`, `doctor_id`, `visit_id` |
| `vital_signs` | FKs: `consultation_id`, `visit_id`, `patient_id`, `recorded_by`, `updated_by` |
| `investigations` | FKs: `patient_id`, `consultation_id`, `doctor_id`, `medical_service_id`, `visit_id`, plus several `*_by` |
| `prescriptions` | FKs: `patient_id`, `consultation_id`, `doctor_id`, `medication_id`, `visit_id`, plus several `*_by` |
| `allergies`, `past_medical_history`, `patient_referrals` | patient-linked clinical history |
| `financial_transactions`, `payment_receipts` | billing |
| `medication_cash_sales`, `medication_cash_sale_items` | over-the-counter sales |
| `users` | staff created at either site (rare, but must not collide) |

### Deferred to v2 (Phase 6.2b — flagged, not designed in detail here)

- `medication_ledger`, `store_stock_movements`, `goods_received_notes`/`*_items`,
  `store_requisitions`/`*_items`, `unfit_medications`, `stock_corrections`,
  `daily_cash_reconciliation`, `reconciliation_runs` — stock/cash ledgers are the same physical
  inventory/till viewed from two access points, so they genuinely need to merge, but ledger
  integrity under merge (running balances, sequence numbers) is materially harder and riskier
  than the clinical chain. Needs its own pass once v1 is proven.
- `nhif_claims`/`nhif_claim_*` — claim *records* should sync like any other table (so a claim
  built locally is visible online), but **submission to NHIF stays online-only** (already true
  today — no change needed there). Include the tables in the outbox once v1 infrastructure
  exists; no special-casing beyond "submission requires connectivity" which is already enforced.

## 3. Identity strategy — UUID + local-ID remapping

All syncable tables keep their existing `bigint unsigned AUTO_INCREMENT` PK (`id`) — **no
Eloquent relationship, foreign key, or query in the app changes**. Two new instances will
independently assign `id=1,2,3...` to new rows, so `id` is **local-only** and never sent
cross-instance.

Add a **global identity column** to every v1 syncable table:

```php
$table->uuid('uuid')->nullable()->unique()->after('id');
```

- Generated in a `creating` model event (`$model->uuid ??= (string) Str::uuid()`), via a shared
  `Syncable` trait (see § 4).
- `nullable()` so existing rows can be backfilled by a one-off migration command
  (`php artisan sync:backfill-uuids`) without downtime, then the column can be made `NOT NULL` in
  a follow-up migration once backfilled on both instances.
- Precedent: `nhif_claims.folio_id` is already a `char(36)` UUID-shaped column in this schema —
  same pattern, nothing novel for this codebase.

**Foreign keys travel as UUIDs over the wire.** E.g. a `patient_visits` outbox payload contains
`"patient_uuid": "..."` instead of `"patient": 7`. When the receiving instance applies the
record, it resolves `patient_uuid` → its **local** `patients.id` via `WHERE uuid = ?`, and writes
that local id into `patient_visits.patient`. This is the only place "remapping" happens — it's a
lookup keyed on an indexed unique column, not a rewrite of the schema.

A static map in `config/sync.php` declares, per syncable table, which columns are FK columns and
which table/uuid-column they resolve against:

```php
'foreign_keys' => [
    'patient_visits' => [
        'patient' => 'patients',
        'visit_type' => 'visit_types',     // reference table -> resolved by uuid too (see §9)
        'doctor' => 'users',
        'created_by' => 'users',
        'informed_by' => 'users',
    ],
    'prescriptions' => [
        'patient_id' => 'patients',
        'consultation_id' => 'consultations',
        'doctor_id' => 'users',
        'medication_id' => 'medications',   // reference table
        'visit_id' => 'patient_visits',
        // ...*_by columns -> users
    ],
    // ...
],
```

## 4. Change tracking — `sync_outbox` + `Syncable` trait

New table `sync_outbox`:

```php
Schema::create('sync_outbox', function (Blueprint $table) {
    $table->id();
    $table->string('table_name', 64);
    $table->uuid('record_uuid');
    $table->enum('operation', ['insert', 'update', 'delete']);
    $table->json('payload');           // full row snapshot, FK columns expressed as *_uuid
    $table->string('origin_site', 32); // config('sync.site_id') at write time
    $table->timestamp('created_at');
    $table->timestamp('synced_at')->nullable();
    $table->index(['synced_at', 'created_at'], 'sync_outbox_synced_at_created_at_index');
});
```

A `Syncable` trait (`app/Models/Concerns/Syncable.php`) applied to each v1 model:

- `creating`: assign `uuid` if absent.
- `created`/`updated`/`deleted`: write a `sync_outbox` row with the model's current attributes
  (FK columns rewritten to `<col>_uuid` via the `config/sync.php` map, dropping the raw local FK
  ids from the payload).
- A static `Syncable::withoutSyncTracking(fn() => ...)` guard (simple static counter, like
  Eloquent's existing `withoutEvents`) — **the sync-apply code wraps every write in this** so
  applying an incoming change doesn't re-enqueue it back to the outbox (no ping-pong loops).

This mirrors how `CdsAlert`/audit-style observers already work in this codebase — an observer
pattern, not new infrastructure.

## 5. New tables (migrations)

| Table | Purpose |
|---|---|
| `sync_outbox` | pending/sent change log (§4) |
| `sync_conflicts` | records needing manual review (§7) |
| `sync_state` | single-row cursor/bookkeeping per remote |
| `<table>.uuid` columns | added to each v1 syncable table (§3) |

```php
Schema::create('sync_state', function (Blueprint $table) {
    $table->id();
    $table->string('remote_site', 32)->unique(); // e.g. 'janet-healthcare'
    $table->timestamp('last_push_at')->nullable();
    $table->timestamp('last_pull_at')->nullable();
    $table->unsignedBigInteger('last_pull_outbox_id')->default(0); // remote outbox cursor
    $table->timestamps();
});

Schema::create('sync_conflicts', function (Blueprint $table) {
    $table->id();
    $table->string('table_name', 64);
    $table->uuid('record_uuid');
    $table->json('local_payload');
    $table->json('incoming_payload');
    $table->timestamp('detected_at');
    $table->timestamp('resolved_at')->nullable();
    $table->unsignedBigInteger('resolved_by')->nullable();
    $table->enum('resolution', ['kept_local', 'kept_incoming', 'merged'])->nullable();
    $table->index(['resolved_at'], 'sync_conflicts_resolved_at_index');
});
```

## 6. Sync protocol

### 6.1 Config (`.env` / `config/sync.php`)

```
SYNC_SITE_ID=practice-local        # or janet-healthcare
SYNC_REMOTE_URL=https://janet-healthcare.com   # set on practice.local
SYNC_SECRET=<shared HMAC secret>   # same secret on both ends
SYNC_ENABLED=true
```

Each instance only needs to know about **one** counterpart (two-node design) — `SYNC_REMOTE_URL`
points the other way on each instance.

### 6.2 Scheduled command

```php
// routes/console.php
Schedule::command('sync:run')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->when(fn () => config('sync.enabled'));
```

`app/Console/Commands/RunSync.php` (`sync:run`):

1. **Connectivity check** — `Http::timeout(3)->get(rtrim($remote, '/').'/api/sync/ping')`. On
   failure, log "remote unreachable, skipping" at debug level and exit 0 (this is an *expected*
   state for an offline clinic, not an error — must not spam `failed_jobs` or pile up like a
   queued job would; a scheduled command that simply no-ops is the right shape here, matching how
   `PollNhifClaimStatusJob` already tolerates NHIF being unreachable).
2. **Push** — `SELECT * FROM sync_outbox WHERE synced_at IS NULL ORDER BY id LIMIT 500`. POST to
   `{remote}/api/sync/receive` as `{ "site": SYNC_SITE_ID, "changes": [...] }`, HMAC-SHA256 signed
   exactly like `webhook-deploy.php`/`deploy-practice.ps1` already do (`X-Sync-Signature` header
   = `hash_hmac('sha256', $rawBody, $secret)`). On 200, mark those `sync_outbox` rows
   `synced_at = now()`.
3. **Pull** — `GET {remote}/api/sync/changes?since={sync_state.last_pull_outbox_id}`, HMAC-signed
   query (signature over the querystring). Response is the remote's outbox rows with
   `id > since`, same shape as what we push. Apply each (§7), update
   `sync_state.last_pull_outbox_id` to the max remote outbox id received, `last_pull_at = now()`.

### 6.3 Receiving endpoint

New `routes/sync.php` (loaded like `routes/medication.php`, or appended to `routes/api.php` — the
`api` middleware group has no CSRF, which is required here since this is machine-to-machine):

```php
Route::post('/api/sync/receive', [SyncController::class, 'receive']);
Route::get('/api/sync/changes', [SyncController::class, 'changes']);
Route::get('/api/sync/ping', fn () => response()->json(['ok' => true]));
```

`SyncController` verifies `X-Sync-Signature` against `SYNC_SECRET` before doing anything else
(constant-time `hash_equals`), returning 403 on mismatch — identical posture to
`webhook-deploy.php`.

## 7. Applying incoming changes

For each incoming `{table_name, record_uuid, operation, payload, origin_site}` (processed within
`Syncable::withoutSyncTracking()`):

1. **Dependency order** — `config/sync.php` declares a fixed table order (`patients` →
   `patient_visits`/`users` → `consultations` → `vital_signs`/`investigations`/`prescriptions` →
   ...). The receive endpoint groups the incoming batch by table and processes in that order, so a
   `patient_visits` row's `patient_uuid` is already resolvable if the `patients` insert was in the
   same batch.
2. **FK resolution** — for each `<col>_uuid` in the payload, `SELECT id FROM <table> WHERE uuid =
   ?`. If found, write the local `id` into `<col>`. **If not found** (out-of-order delivery across
   batches — e.g. the visit arrived before the patient due to a prior partial sync), insert into a
   small `sync_deferred` holding area (or simply re-queue the record at the end of the current
   batch, retrying once more before deferring to the next `sync:run` invocation) rather than
   failing the whole batch.
3. **Existing-row lookup** — `SELECT * FROM <table> WHERE uuid = ?`.
   - Not found → `operation` must be `insert`; create the row (Eloquent `create()`, UUID
     preserved as sent, **not regenerated**).
   - Found → this is an `update` (or `delete`) for a row that exists on both sides. Go to
     conflict check.
4. **Conflict check** (only when local row exists and was *also* modified locally since the last
   successful sync — i.e. `local.updated_at > sync_state.last_pull_at` for an incoming
   `update`/`delete`):
   - **Last-write-wins by `updated_at`**: if `incoming.updated_at > local.updated_at`, apply the
     incoming change (overwrite local).
   - If `local.updated_at >= incoming.updated_at` (local edit is newer or simultaneous), **do
     not** overwrite — instead insert a `sync_conflicts` row with both payloads for manual
     review, and keep the local version live.
   - If local was **not** modified since the last sync (the common case — only one side touched
     the record), apply the incoming change with no conflict check at all.
5. **Deletes** — most "deletion" in this app is a status flag (`prescriptions.status =
   'cancelled'`, `investigations.status = 'cancelled'`, `patient_visits.visit_status`), which is
   just an `update` and flows through normally. True hard deletes are rare in the v1 table set;
   where they occur, `operation = 'delete'` performs a normal delete by `uuid` lookup (no
   tombstone table needed for v1 — if this becomes an issue in practice, a `deleted_uuids`
   tombstone table is the natural v2 addition).

## 8. Conflict review UI

Minimal admin page, `Sync → Conflicts` (new `SyncConflictController`, following existing
AdminLTE/DataTables conventions per `CLAUDE.md`):

- DataTables list of `sync_conflicts` where `resolved_at IS NULL`, columns: table, record (link to
  the record where a `show` route exists), detected_at, side-by-side diff of changed fields.
- Two buttons per row: "Keep local" / "Keep incoming" → applies the chosen payload, sets
  `resolved_at`/`resolved_by`/`resolution`.
- Given the v1 table set (registration/clinical/billing for one small clinic, edited mostly by
  one user at a time), conflicts should be rare — this UI is a safety net, not a daily workflow.

## 9. Config-table sync (lightweight, separate mechanism)

Reference/lookup tables (§2) are edited rarely, by admins, and a "last edit wins, full row
replace" policy is acceptable — no per-row conflict tracking needed. Simplest approach: **the
same `sync_outbox`/`Syncable` mechanism, minus conflict detection** — apply incoming changes
unconditionally (overwrite). This re-uses all of §4–§7's plumbing with step 4 (conflict check)
skipped for these tables, rather than building a second mechanism. `config/sync.php` flags each
table with `'conflict_check' => true|false`.

## 10. Rollout plan

- **6.2a** — infrastructure: `sync_outbox`/`sync_conflicts`/`sync_state` migrations, `uuid`
  columns + backfill command, `Syncable` trait, `config/sync.php`, `SyncController` +
  `routes/sync.php`, `sync:run` command + schedule, HMAC signing (reuse the
  `deploy-practice.ps1`/`webhook-deploy.php` secret-handling lessons — sign raw bytes, not
  re-encoded strings, per the known gotcha in that pipeline).
- **6.2b** — apply `Syncable` to the v1 table list (§2), conflict review UI, end-to-end test:
  register a patient on `practice.local` while `janet-healthcare.com` is "offline" (toggle
  `SYNC_ENABLED=false` there), bring it back, confirm the patient appears on both with matching
  `uuid` and correctly-remapped local FK ids.
- **6.2c** — extend to ledger/stock/cash tables (§2 "deferred to v2") once 6.2b is proven stable.

## 11. Risks / open questions

- **Clock skew** between the two servers affects last-write-wins correctness — both should run
  NTP-synced clocks (verify on CloudPanel box).
- **Batch size / backlog** — if a site is offline for weeks, the outbox could grow large; 500-row
  pages (§6.2) plus `everyFiveMinutes` should drain a multi-day backlog within an hour of
  reconnecting, but very large backlogs (months offline) may need a one-off larger initial sync.
- **`users` table sync** — staff accounts created on one side need to sync to the other
  (`Syncable` covers it like any table), but `password`/`remember_token`/session data should
  probably NOT trigger conflict surprises — recommend excluding `remember_token` and
  `email_verified_at` from the synced payload (cosmetic fields, not facility data).
- **Reference-table FK targets** (e.g. `medications`, `visit_types`, `users`) must themselves have
  `uuid` columns even though they're "reference tables" for §9 purposes, since v1 syncable tables
  have FKs into them — i.e. the `uuid` column rollout is broader than just the v1 "syncable" list;
  it covers every table that's either directly synced (§2) or is an FK target from a synced table.
