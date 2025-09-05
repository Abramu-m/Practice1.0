<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Debugbar Configuration (overrides for barryvdh/laravel-debugbar)
    |--------------------------------------------------------------------------
    |
    | This file is created to override the package defaults and ensure the
    | Debugbar does not call jQuery.noConflict(true) which would break
    | components (like Select2) that rely on the global jQuery/$ variable.
    |
    */

    'enabled' => env('DEBUGBAR_ENABLED', null),

    // Theme: auto|light|dark
    'theme' => 'auto',

    // Don't let Debugbar call jQuery.noConflict(true)
    // The package option is named 'enable_jquery_noconflict'. Set it false so
    // the renderer will not emit jQuery.noConflict(true).
    'enable_jquery_noconflict' => false,

    // Keep default vendor include behavior (the package's JavascriptRenderer
    // uses internal flags). We're only setting the explicit override here.

];
