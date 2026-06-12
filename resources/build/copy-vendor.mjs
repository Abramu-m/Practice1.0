// Copies pre-built vendor JS bundles from node_modules into public/vendor/.
//
// These are loaded as classic, render-blocking <script> tags in
// resources/views/layouts/app_main_layout.blade.php (BEFORE @vite), so that
// window.jQuery/$/bootstrap/moment/etc. are available synchronously to
// inline page scripts (which run during HTML parsing, before any deferred
// or type="module" script - including the @vite bundle - executes).
import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const dest = join(root, 'public', 'vendor');

mkdirSync(dest, { recursive: true });

const files = [
    ['node_modules/jquery/dist/jquery.min.js', 'jquery.min.js'],
    ['node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'bootstrap.bundle.min.js'],
    ['node_modules/moment/min/moment.min.js', 'moment.min.js'],
    ['node_modules/daterangepicker/daterangepicker.js', 'daterangepicker.js'],
    ['node_modules/select2/dist/js/select2.min.js', 'select2.min.js'],
    ['node_modules/datatables.net/js/dataTables.min.js', 'dataTables.min.js'],
    ['node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js', 'dataTables.bootstrap5.min.js'],
    ['node_modules/datatables.net-responsive/js/dataTables.responsive.min.js', 'dataTables.responsive.min.js'],
    ['node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js', 'responsive.bootstrap5.min.js'],
    ['node_modules/toastr/build/toastr.min.js', 'toastr.min.js'],
    ['node_modules/chart.js/dist/chart.umd.min.js', 'chart.umd.min.js'],
    ['node_modules/apexcharts/dist/apexcharts.min.js', 'apexcharts.min.js'],
    ['node_modules/overlayscrollbars/browser/overlayscrollbars.browser.es5.min.js', 'overlayscrollbars.browser.es5.min.js'],
    ['node_modules/alpinejs/dist/cdn.min.js', 'alpine.min.js'],
];

for (const [src, name] of files) {
    copyFileSync(join(root, src), join(dest, name));
}

console.log(`Copied ${files.length} vendor JS files to public/vendor/`);
