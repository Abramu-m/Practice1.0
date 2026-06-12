import './bootstrap';

// jQuery, Bootstrap, OverlayScrollbars, ApexCharts, Chart.js, Select2,
// DataTables, Toastr, moment, daterangepicker and Alpine are loaded as
// classic vendor <script> tags in app_main_layout.blade.php (before this
// module) - see resources/build/copy-vendor.mjs. They run synchronously
// during HTML parsing, so window.$/jQuery/bootstrap/etc. are available to
// inline page scripts. This module (deferred) only needs to run after them.
