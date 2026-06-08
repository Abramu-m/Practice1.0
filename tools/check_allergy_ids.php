<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('allergies')
    ->whereIn('patient_id', [24, 25, 26])
    ->orderBy('patient_id')
    ->orderBy('substance_name')
    ->select('patient_id', 'medication_id', 'substance_name', 'severity')
    ->get();

foreach ($rows as $r) {
    echo "[Patient {$r->patient_id}] medication_id={$r->medication_id} | {$r->substance_name} ({$r->severity})\n";
}
