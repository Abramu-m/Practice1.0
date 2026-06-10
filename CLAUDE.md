# Janet Healthcare — AI Instructions

## Stack
- **Framework**: Laravel 12 (uses `bootstrap/app.php`, no `Kernel.php`)
- **Admin UI**: AdminLTE 4 + Bootstrap 5
- **Database**: MySQL via XAMPP
- **Auth**: Laravel Breeze

## Tables — always use DataTables

Any table that may contain more than a handful of rows **must** use DataTables. Never build a plain HTML table with manual search, pagination, or sorting.

**What's globally loaded** (in `resources/views/layouts/app_main_layout.blade.php`):
- DataTables 2.0.0 — Bootstrap 5 styled
- DataTables Responsive 3.0.0 — Bootstrap 5 styled

**Minimum required init:**
```js
$(document).ready(function () {
    $('#my-table').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [-1] } // disable sort on action column
        ]
    });
});
```

**Rules:**
- Always set `responsive: true`
- Always disable ordering on action/input columns via `columnDefs`
- Put the init inside `@section('scripts')` using `$(document).ready`
- Do **not** add any extra JS for search, pagination, or sorting — DataTables handles all of that
- Only add JS beyond the DataTables init when the feature genuinely requires it (e.g. AJAX save on a pricing table)
- For server-side/Ajax tables, use the Yajra DataTables package (already installed) — the controller returns `DataTables::of($query)->...->make(true)` and the view calls `ajax: { url: '...' }`

**Buttons/AJAX inside DataTables rows — MUST use delegated events:**
```js
// WRONG — direct listeners break with DataTables DOM management
document.querySelectorAll('.my-btn').forEach(btn => btn.addEventListener('click', ...));

// CORRECT — delegated via jQuery, survives any DataTables redraw/reorder
$('#my-table tbody').on('click', '.my-btn', function () { ... });
```
Always put the action URL in a `data-url="{{ route(...) }}"` attribute on the button rather than constructing it in JS.

## Blade/Laravel over JavaScript

**Prefer server-side Blade and Laravel over client-side JavaScript. Use JS only when there is no server-side equivalent.**

| Need | Prefer | Not |
|---|---|---|
| Auto-submit filter on change | `onchange="this.form.submit()"` on the input | JS event listener + fetch |
| Conditional rendering | `@if` / `@unless` in Blade | JS DOM manipulation |
| Loops and lists | `@foreach` / `@forelse` | JS rendering |
| Flash messages | `session()->flash()` + Blade | JS alerts |
| Redirects after actions | `redirect()->route(...)` | JS `window.location` |
| Debounced text search | 3-line `setTimeout` → `form.submit()` | Full AJAX + partial rendering |
| Live filter (select/date) | `onchange="this.form.submit()"` | AJAX |

JavaScript is appropriate for: modals, AJAX that avoids a full page reload for a genuinely better UX (e.g. loading a modal body), DataTables init, and interactions that have no server-side equivalent (e.g. clipboard copy, print trigger).

## Middleware registration (Laravel 12)
Aliases go in `bootstrap/app.php`, not in any `Kernel.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'my_alias' => \App\Http\Middleware\MyMiddleware::class,
    ]);
})
```

## Routes
- Specific routes (e.g. `/medications/pricing`) must be declared **before** `Route::resource()` to avoid being matched as a resource parameter
- Auth routes are in `routes/auth.php`; app routes in `routes/web.php`

## Nav (AdminLTE)
- Use `nav_menu_open_class([...])` on the parent `<li>` for sidebar expand state
- Use `nav_active_class([...])` on links for active highlight
- Use `nav_display_style([...])` on the `<ul class="nav nav-treeview">` to control open/closed
- Keep route pattern arrays specific — avoid broad wildcards like `model.*` that bleed into sibling sections; use `model.index`, `model.show`, etc. where needed

## Database indexes — add them proactively

Whenever you touch a controller method and notice a column used in `WHERE`, `ORDER BY`, or a `JOIN ON` that has no index, **add the index in the same task** via a migration. Do not leave it for later.

**Columns that almost always need an index:**
- Any `*_at` timestamp used in date-range filters (`created_at`, `visit_date`, `dispensed_at`, `updated_at`)
- Composite `(status, created_at)` / `(status, dispensed_at)` when queries filter by both
- Foreign-key columns that Laravel does not automatically index (check with `SHOW INDEX FROM table`)
- Any column used in a `whereHas` nested-EXISTS that is the join key inside the subquery

**Critical rules:**
- Never use `whereDate('col', ...)` — it wraps the column in `DATE()` which defeats the index. Use `where('col', '>=', $date.' 00:00:00')` or `whereBetween('col', [$start, $end])` instead.
- Start big queries from the smallest table and work outward (e.g. filter on `prescriptions` first, then join to `patient_visits`), rather than scanning `patient_visits` and nesting EXISTS.
- Create indexes with a descriptive name: `table_col1_col2_index`.

**Migration template:**
```php
Schema::table('my_table', function (Blueprint $table) {
    $table->index(['status', 'created_at'], 'my_table_status_created_at_index');
});
```

## Models / Fillable
Always update `$fillable` and `$casts` when adding new columns. Decimal columns should cast as `'decimal:2'`.

## Facility
Facility details live in the `facilities` table (single row, id=1). Access via `Facility::current()` or the globally shared `$facility` Blade variable. Do not use `config('app.clinic_*')` — those are legacy.

## Browser print (window.print pages)

The layout uses `@yield('styles')` — **not** `@stack('styles')`. This means `@push('styles')` is silently ignored and the CSS never reaches the page. Always use `@section('styles')` / `@endsection` for page-specific CSS.

Every page that calls `window.print()` must include this print block in its `@section('styles')`:

```css
@media print {
    .app-header,
    .app-sidebar,
    .app-footer,
    .no-print { display: none !important; }

    .app-wrapper, .app-main, .app-content, .container-fluid {
        margin: 0 !important; padding: 0 !important;
        width: 100% !important; background: #fff !important;
    }

    @page { margin: 10mm 12mm; }
}
```

Add `class="no-print"` to any toolbar, breadcrumb, or action-button container that should not appear on the printed page. Never use `@push('styles')` — it has no matching `@stack` in the layout and the CSS will be silently dropped.

## PDF reports (dompdf via barryvdh/laravel-dompdf)

**dompdf does not support `display: flex`, `display: grid`, or most modern CSS layout.** Use HTML `<table>` elements for every multi-column layout — headers, facility fields, legend sections, signature rows, everything.

**Rules:**
- All layout must be `<table>`-based. Never use flexbox or grid in PDF views.
- Images require the PHP **GD extension** (`extension=gd` in `C:\xampp\php\php.ini`, then restart Apache). Wrap image tags in a `@if(function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng'))` guard so the PDF still generates without GD.
- PDF views live in `resources/views/admin/reports/pdfs/`.

**Official form reproduction:**
When a report corresponds to an official government or MoH form (e.g. the Tracer Medicines form, NHIF claim forms), the PDF **must reproduce the exact structure** of that form — same heading layout, same field labels, same table columns, same legend/footnote text. Content (facility name, data values) is filled dynamically; the visual structure is fixed. Never redesign an official form's layout.

## Proactive improvement suggestions

When working on any task and you notice code that could be improved — route ordering, controller logic, accessor bugs, view structure — **mention it inline** in your response. Keep suggestions short (one line). Don't implement unless asked. Preferred improvements: fixing wrong accessors, consolidating redundant logic, correcting misplaced routes, simplifying Blade/controller split. Avoid suggesting excessive JS, new abstractions, or changes unrelated to the file you're touching.
