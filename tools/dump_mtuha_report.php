<?php
// Dump MtuhaReportService::buildReport output for debugging
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\MtuhaReportService();
$year = 2025;
$month = 8;
$payload = $service->buildReport($year, $month, false);

echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
