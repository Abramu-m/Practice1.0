<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$facility = $argv[1] ?? config('nhif.facility_code') ?? '03747';
$service = app()->make(\App\Services\NhifService::class);

echo "Running syncTariffs for facility {$facility}\n";
$res = $service->syncTariffs($facility);
print_r($res);

// show count in DB
$count = \App\Models\NhifTariff::where('facility_code', $facility)->count();
echo "DB count for facility {$facility}: {$count}\n";
