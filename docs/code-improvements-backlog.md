# Code Improvements Backlog

Running list of issues/improvements noticed while working on other tasks but not
fixed at the time. Add an entry whenever something is spotted in passing. Move
items to "Done" (with date) once addressed, don't delete them.

## Open

_None currently._

## Done

- **`FinancialTransaction::scopeIncome`/`scopeExpense` didn't exclude
  non-completed statuses (2026-06-14)** — `scopeIncome()`/`scopeExpense()`
  only filtered `transaction_type`, so transactions marked `refunded` (set by
  `ConsultationFeeObserver`, `MedicationDispensingObserver`,
  `InvestigationFinancialObserver::deleted()`, and
  `MedicationCashSaleController::cancelPaid()`) or `pending` still counted
  toward `getTodayIncome()`/`getMonthlyIncome()`/`getTodayExpenses()`/
  `getMonthlyExpenses()` on the financial dashboard. Fixed by adding
  `->where('status', 'completed')` to both scopes, matching the convention
  already used in `ReceiptController::viewDailySummary()`.

- **email/index.blade.php double pagination (2026-06-14)** — IMAP pagination
  (`webklex/php-imap`'s `->paginate(25)`) is correct/efficient: it does one
  cheap IMAP `SEARCH` for all UIDs, then fetches headers only for the current
  page's UIDs, and reports the real mailbox total via a custom `imap_page`
  paginator. Unlike the DB-backed tables, this can't switch to `->get()`.
  Fixed by disabling DataTables' own `paging`/`info`/`searching` (which only
  ever saw the current 25-row page and showed a misleading "Showing 1 to N of
  N" + 1-page pager), keeping only `responsive` + column sorting, and leaving
  Laravel's `imap_page` pager as the sole pagination control.
  Follow-up idea (not done): wire DataTables' search box to an IMAP-level
  `SEARCH` query if full-mailbox search is wanted later.

- **icd10/index.blade.php double pagination (2026-06-14)** — same fix as email:
  `.DataTable()` was wrapping the current 25-row server page (Laravel
  `->paginate(25)`), showing its own misleading "Showing 1 to 25 of 25" +
  1-page pager on top of Laravel's real `?page=` pager. Fixed by setting
  `paging: false, info: false, searching: false` in the DataTable init,
  keeping `responsive` + column sort, with Laravel's pager as the sole
  pagination control. (Full search/sort across all rows is tracked separately
  above as an enhancement, not a bug.)

- **NHIF tariffs table (2026-06-14, verified — no bug found)** — checked
  `resources/views/nhif/tariffs.blade.php`: it uses plain Laravel
  `->paginate()` with a manual search form and deliberately has **no**
  `.DataTable()` init (there's even a comment: "Table is server-side
  paginated; skip DataTable client-side pagination to avoid conflicts").
  No double-pagination issue here.

- **icd10 full-table search/sort (2026-06-14)** — converted `icd10.index` to a
  server-side Yajra DataTable. `Icd10Controller::index()` now branches on
  `$request->ajax()`: returns `DataTables::of($query)->addColumn('mtuha_display', ...)
  ->addColumn('actions', ...)->rawColumns([...])->make(true)`, applying
  `Icd10::search($term)` and a `mtuha_diagnosis` filter from the request.
  The view (`icd10/index.blade.php`) now has an empty `<tbody>` and initializes
  `.table` with `serverSide: true, ajax: {url: route('icd10.index'), data: ...}`,
  passing the `#term_select`/`#mtuha_select` filter values on every request;
  changing either select or submitting `#icd10-filter-form` calls `table.draw()`.
  The per-row "assign mtuha" form is now a partial
  (`icd10/partials/assign-form.blade.php`) submitted via a delegated AJAX
  handler (`$('.table tbody').on('submit', '.icd10-assign-form', ...)`) that
  POSTs to `icd10.update` (PATCH-spoofed) and shows a toastr message, so the
  current page/filters are preserved instead of a full-page redirect.

- **nhif/claims.blade.php `#claimsTable` ordering (2026-06-14)** — added
  `columnDefs: [{ orderable: false, targets: [-1] }]` to the `#claimsTable`
  DataTable init so the Actions column is no longer sortable.

- **NhifController `$batches` dropdown (2026-06-14)** — changed
  `NhifClaimBatch::orderBy(...)->paginate(12)` to `->get()` so all batches are
  available in the "Submit Batch" select, not just the first 12.
