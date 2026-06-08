<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Allergy;

$allergies = Allergy::whereIn('patient_id', [24, 25, 26])
    ->orderBy('patient_id')->orderBy('substance_name')->get();

foreach ($allergies as $a) {
    echo "[Patient {$a->patient_id}] {$a->substance_name} ({$a->severity})\n";
}
