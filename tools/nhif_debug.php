<?php
// One-off debug script to run NHIF tariffs download using the app service
require __DIR__ . '/../vendor/autoload.php';

// Boot up minimal Laravel app context
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/** @var \App\Services\NhifService $service */
$service = app()->make(\App\Services\NhifService::class);
$facility = $argv[1] ?? config('nhif.facility_code') ?? '03747';

echo "Requesting tariffs for facility: {$facility}\n";
$result = $service->downloadTariffsWithoutExcludedService($facility);
print_r($result);

echo "\nRequesting tariffs (with excluded services)\n";
$result2 = $service->downloadTariffsWithExcludedService($facility);
print_r($result2);

echo "\nDone\n";
