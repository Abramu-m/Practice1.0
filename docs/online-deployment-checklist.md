# Online deployment checklist — janet-healthcare.com

Janet Healthcare is **one facility, two access points**: `practice.local` (LAN-only,
offline-capable, this dev box pushes to it via the local webhook — see
`deploy-practice.ps1`/`public/webhook-deploy.php`) and `janet-healthcare.com`
(public, Bluehost shared hosting, auto-deployed on every push to `master` via
`public/webhook.php`, a real GitHub webhook).

This file lists everything that **works on `practice.local` but needs extra setup, or
behaves differently, on `janet-healthcare.com`**. Update it whenever new work is added
that has an online/offline difference — see the standing rule at the bottom.

## 1. Already automated by `public/webhook.php` (verify only)

On every push to `master`, janet-healthcare.com's webhook automatically:
- `git fetch` + `git reset --hard FETCH_HEAD` (with self-healing for git corruption)
- `composer install --no-dev --optimize-autoloader --ignore-platform-reqs`
- `artisan config:clear`, `cache:clear`, `route:clear`, `view:clear`
- `artisan config:cache`, `route:cache`, `view:cache`
- `artisan migrate --force`

**Multi-site (2026-06-12):** the same single GitHub webhook call now also
deploys `brigita-clinic.com` (`/home2/yyfcolmy/brigita`, its own `.env`/DB —
separate facility/tenant) via the same pipeline, run independently — a
failure on one site doesn't skip the other. No second GitHub webhook/secret
is needed.

Logs: `storage/logs/webhook-*.log` (channel `webhook`), running on
janet-healthcare.com's instance. Each site's steps are prefixed
`[janet-healthcare.com]` / `[brigita-clinic.com]` so they can be grepped
separately even though brigita's deploy is driven from janet's log file.

## 2. NOT automated — action required

### 2.1 Frontend assets (Phase 6.1 vendoring) — DONE, verified working

`public/webhook.php` now runs `npm install` and `npm run build` (which runs
`resources/build/copy-vendor.mjs` then `vite build`) right after `composer install`.
`public/build/` (Vite manifest) and `public/vendor/*.min.js` are gitignored generated
artifacts, so this step must succeed for `@vite()` and the Phase 6.1 vendored CSS/JS
(jQuery, Bootstrap, DataTables, Select2, ApexCharts, Toastr, Font Awesome, Bootstrap
Icons, OverlayScrollbars, Alpine, Chart.js, moment/daterangepicker) to load.

PHP-FPM's `exec()` environment on Bluehost has no `node`/`npm` on `PATH` (confirmed:
"sh: line 1: npm: command not found", exit 127). Fixed in commit `50f2938`:
`public/webhook.php` discovers `npm` at deploy time (`$npmSetup` shell snippet) by
trying `command -v npm`, then globbing cPanel Node.js Selector venvs
(`~/nodevenv/*/*/bin/npm`, `~/nodevenv/*/*/*/bin/npm`), EasyApache Node
(`/opt/cpanel/ea-nodejs*/bin/npm`), and nvm (`~/.nvm/versions/node/*/bin/npm`), then
prepends the resolved bin dir to `PATH`. Verified 2026-06-12: `/vendor/jquery.min.js`,
`/vendor/bootstrap.bundle.min.js`, `/vendor/dataTables.min.js`,
`/vendor/dataTables.bootstrap5.min.js`, and `/build/manifest.json` all return 200 with
real built content on janet-healthcare.com.

### 2.2 `php artisan storage:link` — DONE

`public/storage` is gitignored (it's a symlink). If missing, anything served via
`Storage::disk('public')` 404s — currently used for **profile signatures**
(`ProfileController.php`) and receipt assets (`ReceiptGenerationService.php`).

`public/webhook.php` now runs `artisan storage:link` every deploy (idempotent — safe
to re-run, no-ops if the link already exists).

### 2.3 Queue worker (`QUEUE_CONNECTION=database`)

Jobs sit in the `jobs` table until a worker drains them — currently:
`SyncNhifTariffsJob`, `PollNhifClaimStatusJob`, NHIF claim submit/poll jobs, and
(once Phase 6.2 sync is enabled) any `sync:run` push/pull retries.

Action: confirm a worker runs on janet-healthcare.com. On Bluehost-style shared
hosting this is usually a cPanel cron entry running
`php artisan queue:work --stop-when-empty` every minute (long-running daemons are
typically not allowed). If nothing currently does this, queued jobs never execute.

### 2.4 Scheduler (`schedule:run` via cron)

`routes/console.php` schedules:
- `SyncNhifTariffsJob` — daily 02:00
- `PollNhifClaimStatusJob` — daily 06:00
- `sync:run` — every 5 minutes, only when `SYNC_ENABLED=true` (Phase 6.2)

Action: confirm cPanel cron runs `php artisan schedule:run` every minute on
janet-healthcare.com. Without it, NHIF tariffs go stale, claim statuses never poll,
and (once enabled) bidirectional sync never fires.

### 2.5 GD extension (dompdf images)

`composer install` uses `--ignore-platform-reqs`, so a missing `gd` extension won't
fail the deploy — but PDF reports (receipts, prescriptions, Tracer Medicines form,
NHIF claim forms) will render **without facility logos/images** (the views guard
image tags with `@if(function_exists('imagecreatefromjpeg') || ...))`).

Action: confirm `gd` is enabled for this domain in Bluehost's PHP config
(MultiPHP INI Editor or equivalent).

## 3. `.env` differences

| Key | practice.local (offline) | janet-healthcare.com (online) |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://practice.local` | `https://janet-healthcare.com` |
| `LOG_LEVEL` | `debug` | `error` (or `warning`) |
| `MAIL_MAILER` | `log` | `smtp` (real SMTP credentials) |
| `NHIF_MODE` | `test` | `production` (+ real `NHIF_USERNAME`/`NHIF_PASSWORD`/`NHIF_FACILITY_CODE`) |
| `SYNC_ENABLED` | `false` until Phase 6.2 go-live | `false` until Phase 6.2 go-live |
| `SYNC_SITE_ID` | e.g. `practice-local` | e.g. `janet-healthcare` |
| `SYNC_REMOTE_SITE_ID` | `janet-healthcare` | `practice-local` |
| `SYNC_REMOTE_URL` | `https://janet-healthcare.com` | practice.local's reachable address (see §4) |
| `SYNC_SECRET` | shared value | same shared value, both ends |

`.env` itself is gitignored — must already exist on the server with real secrets and
`APP_KEY` set (`php artisan key:generate` if empty).

## 4. Phase 6.2 bidirectional sync — go-live checklist

Not yet enabled anywhere (`SYNC_ENABLED=false` on both `.env` and `.env.example`).
When ready to turn on:

1. Run `php artisan sync:backfill-uuids` on janet-healthcare.com once (mirrors the
   6.2a step already done on practice.local) so existing rows have `uuid`s.
2. Set identical `SYNC_SECRET` on both instances; confirm each instance's
   `SYNC_SITE_ID` equals the other's `SYNC_REMOTE_SITE_ID`.
3. **Networking topology**: `practice.local` is LAN-only — janet-healthcare.com
   cannot reach it to push changes. `sync:run` already does both push (POST
   `/api/sync/receive` on the counterpart) and pull (GET `/api/sync/changes` from the
   counterpart) in one invocation, so only `practice.local`'s scheduled `sync:run`
   needs to be the active driver: it pushes local changes to janet and pulls janet's
   pending outbox. janet-healthcare.com's own `sync:run` can stay disabled
   (`SYNC_ENABLED=false`) — it only needs its `/api/sync/*` endpoints reachable,
   which they already are (public site).
4. Enable `SYNC_ENABLED=true` on `practice.local` first. Watch
   `/admin/sync/conflicts` on both sides after the first few `sync:run` cycles for
   unexpected `sync_conflicts` rows before trusting it with real patient data.

## 5. File permissions

`storage/` and `bootstrap/cache/` must be writable by the web server's PHP user
(usually already correct on shared hosting after `git pull`, but verify after the
first deploy of a new repo clone).

---

## Standing rule

**Any future change in this codebase that behaves differently — or needs additional
one-time setup — online (`janet-healthcare.com`) vs. offline (`practice.local`) must
be added as a new item to this file as part of the same task.** This includes new
CDN/vendored assets, new queue jobs or scheduled commands, new `Storage::disk()`
usage, new `.env` keys, new PHP extension requirements, etc.
